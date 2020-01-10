<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Account;
use App\Models\Announcement;
use App\Models\Ban;
use App\Models\Broadcast;
use App\Models\Code;
use App\Models\CodeBatch;
use App\Models\CodeBox;
use App\Models\Content;
use App\Models\Gmmail;
use App\Models\Good;
use App\Models\IpOperation;
use App\Models\NewRole;
use App\Models\WhiteIp;
use Illuminate\Http\Request;
use DB;
use Monolog\Handler\IFTTTHandler;

class GMController extends Controller
{
    private $key = 'rJYgMdja4KXMqwFbAibOM7jhls';

    public function banLogin(Request $request)
    {
        $uid       = $request->input('role_id');
        $oper      = $request->input('oper');
        $long_line  = $request->input('time', null);
        $server_id = intval($request->input('server_id'));

        if ($long_line){
            $day_year = time() + 3600*24*365;
            $url_args = array(
                "uid"   => intval($uid),
                "oper"  => intval($oper),
                "time"  => intval($day_year),
            );
        } else {
            $url_args = array(
                "uid"   => intval($uid),
                "oper"  => intval($oper),
            );
        }

        $time = time();
        $sign_args = json_encode($url_args);
        $sign = md5("args={$sign_args}&fun=web_op_sys_suspend&mod=login_api&sid={$server_id}&time={$time}&key={$this->key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => 'web_op_sys_suspend',
            'mod'       => 'login_api',
            'sid'       => $server_id,
            'time'      => $time,
            'sign'      => $sign,
        );

        //发送内容
        $res = $this->send_post(env('WXURL'), $info);


        if ($oper == 1) {
            $ban = array(
                'role_id' => $uid,
                'serverId' => $server_id,
                'status' => 1,
                'type' => 2,
                'reason' => '',
            );

            $banResult = $this->addBan($ban);
        }else{
            $banResult = Ban::where(['role_id' => $uid, 'type' => 2])->update(['status' => 0]);
        }

        $res = json_decode($res, true);

        if ($res['res'] == "1") {
            if ($banResult){
                return response(Response::Success());
            }
            return response(Response::Error(trans('ResponseMsg.SPECIFIED_QUESTIONED_USER_NOT_EXIST'), 30001));

        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function sendMail(Request $request)
    {
        $server    = $request->input('server');
        $title     = $request->input('title');
        $role_list = $request->input('role', null);
        $item_id   = $request->input('item_id');
        $content   = $request->input('content');
        $channel   = $request->input('channel', null);
        $level     = $request->input('level', null);

        $serverId = intval($server);

        $item = array();
        if ($item_id) {
            foreach ($item_id as $item_key => $item_value) {
                $item_val = json_decode($item_value, true);
                if (!empty($item_val)) {
                    $item[$item_val['selectVal']] = intval($item_val['num']);
                }
            }
        }

        if (!empty($level[0]) && !empty($level[1])){
            if ($level[0] == $level[1]){
                $orm = DB::connection('wxfyl')
                    ->table('user')
                    ->where(['renown_lv' => $level[0]])
                    ->select('uid', 'renown_lv')
                    ->get();
            }else{
                $orm = DB::connection('wxfyl')
                    ->table('user')
                    ->whereBetween('renown_lv', [$level[0], $level[1]])
                    ->select('uid', 'renown_lv')
                    ->get();
            }
            $roleInt = array();
            foreach ($orm as $orm_val){
                array_push($roleInt, intval($orm_val->uid));
            }
        }else{
            if (empty($role_list)) {
                $role = array();
            } else {
                $role = explode("|", $role_list);
            }

            $roleInt = array();
            foreach ($role as $role_val){
                array_push($roleInt, intval($role_val));
            }
        }

        $str_long_title = strlen($title);
        $titles = '';
        for ($i=0; $i < $str_long_title ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $title[$i])){
                $titles .= urlencode($title[$i]);
            }else{
                $titles .= $title[$i];
            }
        }

        $str_long_content = strlen($content);
        $contents = '';
        for ($i=0; $i < $str_long_content ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $content[$i])){
                $contents .= urlencode($content[$i]);
            }else{
                $contents .= $content[$i];
            }
        }

        $url_args = array(
            "objects"     => $channel ? intval($channel) : $roleInt,
            "title"       => strtolower($title),
            "content"     => strtolower($contents),
            "items"       => json_encode($item),
        );

        $time = time();
        $sign_args = json_encode($url_args);
        $sign = md5("args={$sign_args}&fun=web_op_sys_mail&mod=mail_api&sid={$serverId}&time={$time}&key={$this->key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => 'web_op_sys_mail',
            'mod'       => 'mail_api',
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );

        //发送内容
        $res = $this->send_post(env('WXURL'), $info);

        $result = Gmmail::create([
            'role_list'  => $role_list,
            'server_id'  => $serverId,
            'channel_id' => $channel,
            'account_id' => UID,
            'title'      => $title,
            'content'    => $content,
            'attach_s'   => json_encode($item),
        ]);

        $res = json_decode($res, true);

        if ($res['res'] == "1") {
            if ($result){
                return response(Response::Success());
            }
            return response(Response::Error(trans('ResponseMsg.SPECIFIED_QUESTIONED_USER_NOT_EXIST'), 30001));

        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function sendSoloMail(Request $request)
    {
        $server    = $request->input('server');
        $title     = $request->input('title');
        $role_list = $request->input('role', null);
        $item_id   = $request->input('item_id');
        $content   = $request->input('content');

        $serverId = intval($server);

        $item = array();
        foreach ($item_id as $item_key => $item_value){
            $item_val = json_decode($item_value, true);
            if (!empty($item_val)){
                $item[$item_val['selectVal']] = intval($item_val['num']);
            }
        }

        $role = explode("|", $role_list);

        $roleInt = array();
        foreach ($role as $role_val){
            array_push($roleInt, intval($role_val));
        }

        $str_long_title = strlen($title);
        $titles = '';
        for ($i=0; $i < $str_long_title ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $title[$i])){
                $titles .= urlencode($title[$i]);
            }else{
                $titles .= $title[$i];
            }
        }

        $str_long_content = strlen($content);
        $contents = '';
        for ($i=0; $i < $str_long_content ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $content[$i])){
                $contents .= urlencode($content[$i]);
            }else{
                $contents .= $content[$i];
            }
        }

        $url_args = array(
            "objects"     => $roleInt,
            "title"       => strtolower($title),
            "content"     => strtolower($contents),
            "items"       => json_encode($item),
        );

        $time = time();
        $sign_args = json_encode($url_args);
        $sign = md5("args={$sign_args}&fun=web_op_sys_mail&mod=mail_api&sid={$serverId}&time={$time}&key={$this->key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => 'web_op_sys_mail',
            'mod'       => 'mail_api',
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );

        //发送内容
        $res = $this->send_post(env('WXURL'), $info);

        $result = Gmmail::create([
            'role_list'  => $role_list,
            'server_id'  => $serverId,
            'account_id' => UID,
            'title'      => $title,
            'content'    => $content,
            'attach_s'   => json_encode($item),
        ]);

        $res = json_decode($res, true);

        if ($res['res'] == "1") {
            if ($result){
                return response(Response::Success());
            }
            return response(Response::Error(trans('ResponseMsg.SPECIFIED_QUESTIONED_USER_NOT_EXIST'), 30001));

        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function sendMailList(Gmmail $gmmail, Request $request)
    {
        $server_id = $request->input('server_id');
        $role_id = $request->input('role_id');

        $orm = $gmmail->with([
                'account' => function($query) {
                    $query->select('id', 'real_name');
                },
                'server' => function($query) {
                    $query->select('id', 'server_name');
                },
                'channel' => function($query) {
                    $query->select('id', 'channel_name');
                }
            ])->orderBy('created_at', 'DESC');

        if ($server_id){
            $orm->where(['server_id' => $server_id]);
        }

        if ($role_id){
            $orm->where(['role_list' => $role_id]);
        }

        $list = $orm->paginate(20);

        $goods = Good::all()->keyBy('id')->toArray();

        if ($list) {
            foreach ($list as $key => $value) {
                $attach = json_decode($value["attach_s"], true);
                $goodsInfo = array();
                if ($attach) {
                    foreach ($attach as $k => $val) {
                        if (isset($goods[$k])) {
                            $goodsInfo[] = $goods[$k]["good_name"] . "x" . $val;
                        }

                    }
                }
                $value['attach'] = $goodsInfo ? implode(";", $goodsInfo) : "（无）";

                if ($value['role_list']) {
                    $role = explode("|", $value['role_list']);
                    $role_name = '';
                    foreach ($role as $k => $v) {
                        $roleName = DB::connection('wxfyl_s2002')
                            ->table('user')
                            ->where(['uid' => $v])
                            ->select('uid', 'uname')
                            ->first();

                        if ($roleName) {
                            $role_name .= $roleName->uname . '、';
                        }
                    }
                    $value['role_name'] = substr($role_name, 0, strrpos($role_name, "、"));
                } else {
                    $value['role_name'] = '全区服';
                    $value['role_list'] = '全区服';
                }

            }
        }

        return response(Response::Success($list));
    }

    public function newRolesGiftList(NewRole $newRole)
    {
        $orm = $newRole->with([
                    'account' => function($query) {
                        $query->select('id', 'real_name');
                    }
                ]);

        $list = $orm->paginate(20);

        $goods = Good::all()->keyBy('id')->toArray();

        foreach ($list as $key => $value){
            $attach = json_decode($value["attach_s"], true);
            $goodsInfo    = array();
            if ($attach) {
                foreach ($attach as $k=>$val) {
                    if (isset($goods[$k])) {
                        $goodsInfo[] = $goods[$k]["good_name"] . "x" . $val;
                    }

                }
            }
            $value['attach'] = $goodsInfo ? implode(";", $goodsInfo) : "（无）";
        }

        return response(Response::Success($list));
    }

    public function newRolesGiftStore(Request $request)
    {
        $content = $request->input('content');
        $title   = $request->input('title');
        $item_id = $request->input('item_id');

        $item = array();
        foreach ($item_id as $item_key => $item_value){
            $item_val = json_decode($item_value, true);
            if (!empty($item_val)){
                $item[$item_val['selectVal']] = intval($item_val['num']);
            }
        }

        $result = NewRole::create([
            'account_id' => UID,
            'title'      => $title,
            'content'    => $content,
            'status'     => 1,
            'attach_s'   => json_encode($item),
        ]);

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function newRolesGiftUpdate(Request $request, NewRole $newRole)
    {
        $data = $request->all();

        $res = $newRole->where(['id' => $data['id']])
            ->update(['status' => $data['status']]);

        if ($res) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 新增登录公告
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function loginBulletinStore(Request $request, Content $content)
    {
        $details    = $request->input('details');
        $server_id  = $request->input('server_id');
        $channel_id = $request->input('channel_id');
        $title      = $request->input('title');

        if (!$details){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        $content->content    = $details;
        $content->server_id  = $server_id;
        $content->channel_id = $channel_id;
        $content->title      = $title;
        $result = $content->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    /**
     * 修改登录公告
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function loginBulletinUpdate(Request $request, Content $content)
    {
        $id         = $request->input('id');
        $server_id  = $request->input('server_id');
        $channel_id = $request->input('channel_id');
        $details    = $request->input('details');
        $title      = $request->input('title');


        $result = $content
            ->where(['id' => $id])
            ->update([
                'server_id'  => $server_id,
                'channel_id' => $channel_id,
                'content'    => $details,
                'title'      => $title,
                'updated_at' => date('Y-m-d H:i:s', time()),
            ]);

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    /**
     * 登录公告列表
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function loginBulletinList(Request $request, Content $content)
    {
        $title     = $request->input('title');
        $server_id = $request->input('server_id');

        $orm = $content->with([
            'server' => function($query){
                $query->select('id', 'server_name');
            },
            'channel' => function($query){
                $query->select('id', 'channel_name', 'channel_abbr');
            }
        ])->select('id', 'server_id', 'title', 'content', 'channel_id', 'status', 'note', 'created_at');

        if ($title){
            $orm->where(['title' => $title]);
        }

        if ($server_id){
            $orm->where(['server_id' => $server_id]);
        }

        $list = $orm->paginate(10);

        return response(Response::Success($list));
    }

    /**
     * 礼包内容配置列表
     * @param Request $request
     * @param CodeBox $codeBox
     * @param Good $good
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function GiftConfigurationList(Request $request, CodeBox $codeBox, Good $good)
    {
        $box_name = $request->input('box_name');
        $account_id = $request->input('account_id');

        $orm = $codeBox->with(['account' => function($query){
            $query->select('id', 'real_name');
        }])->select('id', 'box_name', 'box_item_list', 'account_id', 'created_at');

        if ($box_name){
            $orm->where(['box_name' => $box_name]);
        }

        if ($account_id){
            $orm->where(['account_id' => $account_id]);
        }

        $list = $orm->paginate(10);

        $goodsList = $good->get();
        $goods = $this->convert_arr_key($goodsList, 'id', 'good_name');

        foreach ($list as $key => $value) {
            if ($value['box_item_list']) {
                $itemList = json_decode($value['box_item_list'], true);
                $items = '';
                foreach ($itemList as $k => $val) {
                    $items .= $goods[$k] . ":" . $val . ";";
                }
                $value['box_item_content'] = $items;
            }
        }

        return response(Response::Success($list));
    }

    /**
     * 新增礼包内容配置
     * @param Request $request
     * @param CodeBox $code_box
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function GiftConfigurationStore(Request $request, CodeBox $code_box)
    {
        $box_name = $request->input('box_name');
        $box_item = $request->input('box_item');

        if ($code_box->where(['box_name' => $box_name])->first()){
            return response(Response::Error(trans('ResponseMsg.GIFT_HAS_EXISTED'), 90002));
        }

        $item = array();
        foreach ($box_item as $item_key => $item_value){
            $item_val = json_decode($item_value, true);
            if (!empty($item_val)){
                $item[$item_val['selectVal']] = intval($item_val['num']);
            }
        }

        $code_box->box_name      = $box_name;
        $code_box->box_item_list = json_encode($item);
        $code_box->account_id    = UID;
        $result = $code_box->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    /**
     * 更新礼包内容配置
     * @param Request $request
     * @param CodeBox $code_box
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function GiftConfigurationUpdate(Request $request, CodeBox $code_box)
    {
        $id       = $request->input('id');
        $box_name = $request->input('box_name');
        $box_item = $request->input('box_item');

        $code = $code_box->where(['box_name' => $box_name])->first();

        if ($code){
            if ($code->id != $id){
                return response(Response::Error(trans('ResponseMsg.GIFT_HAS_EXISTED'), 90002));
            }
        }

        $item = array();
        foreach ($box_item as $item_key => $item_value){
            $item_val = json_decode($item_value, true);
            if (!empty($item_val)){
                $item[$item_val['selectVal']] = intval($item_val['num']);
            }
        }

        $orm = $code_box->where(['id' => $id])->first();

        $orm->box_name      = $box_name;
        $orm->box_item_list = json_encode($item);
        $result = $orm->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    /**
     * 新增礼包配置
     * @param Request $request
     * @param CodeBatch $code_batch
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function codeBatchStore(Request $request, CodeBatch $code_batch)
    {
        $batch_name   = $request->input('batch_name');
        $batch_detail = $request->input('batch_detail');
        $code_box_id  = $request->input('code_box_id');
        $code_prefix  = $request->input('code_prefix');
        $code_length  = $request->input('code_length');
        $platform     = $request->input('platform');
        $channel_id   = $request->input('channel');
        $server_id    = $request->input('serverid');
        $use_count    = $request->input('use_count');
        $start_time   = $request->input('start_time');
        $end_time     = $request->input('end_time');

        if (is_numeric(substr($code_prefix, 0, 1))){
            return response(Response::Error('礼包码前缀不能为数字！', 90003));
        }

        if (strlen($code_prefix) > 5){
            return response(Response::Error('礼包码前缀长度不能超过5位', 90003));
        }

        if ($code_length > 15){
            return response(Response::Error('礼包码长度不能超过15位！', 90003));
        }

        if ($code_batch->where(['batch_name' => $batch_name])->first()){
            return response(Response::Error(trans('ResponseMsg.GIFT_CODE_BATCH_HAS_EXISTED'), 90003));
        }

        $code_batch->batch_name    = $batch_name;
        $code_batch->batch_detail  = $batch_detail;
        $code_batch->code_box_id   = $code_box_id;
        $code_batch->code_prefix   = $code_prefix;
        $code_batch->code_length   = $code_length;
        $code_batch->platform      = $platform;
        $code_batch->channel_id    = $channel_id;
        $code_batch->server_id     = $server_id;
        $code_batch->use_count     = $use_count;
        $code_batch->start_time    = $start_time;
        $code_batch->end_time      = $end_time;
        $code_batch->account_id    = UID;
        $result = $code_batch->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    /**
     * 更新礼包配置
     * @param Request $request
     * @param CodeBatch $code_batch
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function codeBatchUpdate(Request $request, CodeBatch $code_batch)
    {
        $id           = $request->input('id');
        $batch_name   = $request->input('batch_name');
        $batch_detail = $request->input('batch_detail');
        $code_box_id  = $request->input('code_box_id');
        $code_prefix  = $request->input('code_prefix');
        $code_length  = $request->input('code_length');
        $platform     = $request->input('platform');
        $channel_id   = $request->input('channel');
        $server_id    = $request->input('serverid');
        $use_count    = $request->input('use_count');
        $start_time   = $request->input('start_time');
        $end_time     = $request->input('end_time');

        $codeBatch = $code_batch->where(['batch_name' => $batch_name])->first();

        if ($codeBatch){
            if ($codeBatch->id != $id){
                return response(Response::Error(trans('ResponseMsg.GIFT_CODE_BATCH_HAS_EXISTED'), 90003));
            }
        }

        $orm = $code_batch->where(['id' => $id])->first();

        $orm->batch_name    = $batch_name;
        $orm->batch_detail  = $batch_detail;
        $orm->code_box_id   = $code_box_id;
        $orm->code_prefix   = $code_prefix;
        $orm->code_length   = $code_length;
        $orm->platform      = $platform;
        $orm->channel_id    = $channel_id;
        $orm->server_id     = $server_id;
        $orm->use_count     = $use_count;
        $orm->start_time    = $start_time;
        $orm->end_time      = $end_time;
        $orm->account_id    = UID;
        $result = $orm->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    /**
     * 礼包配置列表
     * @param Request $request
     * @param CodeBatch $code_batch
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function codeBatchList(Request $request, CodeBatch $code_batch)
    {
        $batch_name = $request->input('batch_name');

        $orm = $code_batch->with([
            'account' => function($query){
                $query->select('id', 'real_name');
            },
            'channel' => function($query){
                $query->select('id', 'channel_name');
            },
            'server' => function($query){
                $query->select('id', 'server_name');
            },
            'codeBox' => function($query){
                $query->select('id', 'box_name');
            }]);

        if ($batch_name){
            $orm->where(['batch_name' => $batch_name]);
        }

        $list = $orm->paginate(20);

        foreach ($list as $value){
            if ($value['platform'] == '0'){
                $value['platform'] = '0';
            }
            $value['time'] = $value['start_time'].' 到 '.$value['end_time'];
        }

        return response(Response::Success($list));
    }

    /**
     * 新增礼包码
     * @param Request $request
     * @param CodeBatch $code_bacth
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function giftCodeStore(Request $request, CodeBatch $code_bacth)
    {
        $number = $request->input('number');
        $batch_id = $request->input('batch_id');

        if ($number < 0){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        $codeBatch = $code_bacth->where(['id' => $batch_id])->first();

        if (!$codeBatch){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
        $codeList = array();

        for ($i = 0; $i < $number; $i++){
            $code_number = $codeBatch['code_prefix'] . $this->randomCodes($codeBatch['code_length']);
            if (in_array($code_number, $codeList)) {
                $number++;
            }else{
                $insert = array(
                    'code_box_id'   => $codeBatch['code_box_id'],
                    'code_batch_id' => $codeBatch['id'],
                    'code'          => $code_number,
                    'code_prefix'   => $codeBatch['code_prefix'],
                    'remain_count'  => $codeBatch['use_count'],
                    'status'        => 0,
                    'start_time'    => $codeBatch['start_time'],
                    'end_time'      => $codeBatch['end_time'],
                );
                $res = Code::create($insert);
            }
        }
        if ($res) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 礼包码列表
     * @param Request $request
     * @param Code $code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function giftCodeList(Request $request, Code $code)
    {
        $batch_id = $request->input('batch_name');
        $box_id = $request->input('box_name');
        $code_number = $request->input('code');
        $status = $request->input('status');

        $orm = $code->with([
            'codeBatch' => function($query){
                $query->select('id', 'batch_name');
            },
            'codeBox' => function($query){
                $query->select('id', 'box_name');
            }
        ]);

        if ($code_number){
            $orm->where(['code' => $code_number]);
        }

        if ($status){
            $orm->where(['status' => $status]);
        }

        if ($batch_id){
            $orm->whereIn('code_batch_id', $batch_id);
        }

        if ($box_id){
            $orm->whereIn('code_box_id', $box_id);
        }

        $list = $orm->paginate(20);

        foreach ($list as $value){
            $value['time'] = $value['start_time'].' 到 '.$value['end_time'];
        }

        return response(Response::Success($list));
    }

    /**
     * 新增白名单
     * @param Request $request
     * @param WhiteIp $white_ip
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function whiteIpStore(Request $request, WhiteIp $white_ip)
    {
        $ip        = $request->input('ip');
        $server_id = $request->input('server_id');

        $white_ip->ip         = $ip;
        $white_ip->server_id  = $server_id;
        $white_ip->status     = 1;
        $white_ip->account_id = UID;

        $result = $white_ip->save();

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 删除白名单
     * @param Request $request
     * @param WhiteIp $white_ip
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function whiteIpUpdate(Request $request, WhiteIp $white_ip)
    {
        $id = $request->input('id');

        $result = $white_ip->where(['id' => $id])->update(['status' => 0]);

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

    }

    /**
     * 白名单列表
     * @param Request $request
     * @param WhiteIp $white_ip
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function whiteIpList(Request $request, WhiteIp $white_ip)
    {
        $ip  = $request->input('ip');

        $orm = $white_ip->with([
            'account' => function($query){
                $query->select('id', 'real_name');
            }, 'server'=> function($query){
                $query->select('id', 'server_name');
            },
        ])->where(['status' => 1]);

        if ($ip){
            $orm->where(['ip' => $ip]);
        }

        $list = $orm->paginate(10);

        return response(Response::Success($list));

    }

    /**
     * 跑马灯列表
     * @param Request $request
     * @param Broadcast $broadcast
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function BroadcastList(Request $request, Broadcast $broadcast)
    {
        $account_id  = $request->input('account_id');

        $orm = $broadcast->with([
            'account' => function($query){
                $query->select('id', 'real_name');
            },
            'server' => function($query){
                $query->select('id', 'server_name');
            },
        ]);

        if ($account_id){
            $orm->where(['account_id' => $account_id]);
        }

        $list = $orm->paginate(10);

        return response(Response::Success($list));

    }

    /**
     * 新增跑马灯
     * @param Request $request
     * @param Broadcast $broadcast
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function BroadcastStore(Request $request, Broadcast $broadcast)
    {
        $interval  = $request->input('interval');
        $times     = $request->input('times');
        $content   = $request->input('content');
        $server_id = $request->input('server_id');

        $broadcast->interval   = $interval;
        $broadcast->times      = $times;
        $broadcast->content    = $content;
        $broadcast->server_id  = $server_id;
        $broadcast->account_id = UID;

        $result = $broadcast->save();

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

    }

    /**
     * 更新跑马灯
     * @param Request $request
     * @param Broadcast $broadcast
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function BroadcastUpdate(Request $request, Broadcast $broadcast)
    {
        $id        = $request->input('id');
        $interval  = $request->input('interval');
        $times     = $request->input('times');
        $content   = $request->input('content');
        $server_id = $request->input('server_id');

        $result = $broadcast
            ->where(['id' => $id])
            ->update([
                'interval'   => $interval,
                'times'      => $times,
                'content'    => $content,
                'server_id'  => $server_id,
                'updated_at' => date('Y-m-d H:i:s', time()),
            ]);

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

    }

    /**
     * 聊天公告列表
     * @param Request $request
     * @param Announcement $announcement
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function AnnouncementList(Request $request, Announcement $announcement)
    {
        $account_name  = $request->input('account_name', null);
        $server_id  = $request->input('server_id');

        $orm = $announcement->with([
            'account' => function($query){
                $query->select('id', 'real_name');
            },
            'server' => function($query){
                $query->select('id', 'server_name');
            },
        ]);

        if ($account_name) {
            $account = Account::where(['account_name' => $account_name])->first();

            if ($account){
                $orm->where(['account_id' => $account->id]);
            } else {
                $orm->where(['account_id' => '']);
            }
        }

        if ($server_id) {
            $orm->where(['server_id' => $server_id]);
        }

        $list = $orm->paginate(10);

        return response(Response::Success($list));

    }

    /**
     * 新增跑马灯
     * @param Request $request
     * @param Announcement $announcement
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function AnnouncementStore(Request $request, Announcement $announcement)
    {
        $comment  = $request->input('comment');
        $server_id = $request->input('server_id');

        $announcement->comment    = $comment;
        $announcement->status     = 1;
        $announcement->server_id  = $server_id;
        $announcement->account_id = UID;

        $result = $announcement->save();

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

    }

    /**
     * 封停IP列表
     * @param Request $request
     * @param IpOperation $ip_operation
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function ClosureIpList(Request $request, IpOperation $ip_operation)
    {
        $orm = $ip_operation->with([
            'account' => function($query){
                $query->select('id', 'real_name');
            },
            'server' => function($query){
                $query->select('id', 'server_name');
            },
        ])->where(['status' => 1]);

        $list = $orm->paginate(10);

        foreach ($list as $key=>$value){
            $value->cloureTime = date('Y-m-d H:i:s',intval($value->time*86400 + strtotime($value->created_at)));
        }

        return response(Response::Success($list));
    }

    protected function convert_arr_key($arr, $key_name, $val_name)
    {
        $arr2 = array();
        foreach ($arr as $key => $val) {
            $arr2[$val[$key_name]] = $val[$val_name];
        }
        return $arr2;
    }

    protected function randomCodes($length)
    {
        $pattern = env('CODE_RANDOM');
        $code = '';
        for($i=0;$i<$length;$i++) {
            $code .= $pattern[mt_rand(0,1377)];
        }
        return $code;
    }

    protected function sendMailAll($server, $item, $title, $role_list, $content, $channel)
    {
        $serverId = intval($server);

        if (empty($role_list)) {
            $role = array();
        } else {
            $role = explode("|", $role_list);
        }

        $roleInt = array();
        foreach ($role as $role_val){
            array_push($roleInt, intval($role_val));
        }

        $str_long_title = strlen($title);
        $titles = '';
        for ($i=0; $i < $str_long_title ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $title[$i])){
                $titles .= urlencode($title[$i]);
            }else{
                $titles .= $title[$i];
            }
        }

        $str_long_content = strlen($content);
        $contents = '';
        for ($i=0; $i < $str_long_content ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $content[$i])){
                $contents .= urlencode($content[$i]);
            }else{
                $contents .= $content[$i];
            }
        }

        $url_args = array(
            "objects"     => $channel ? intval($channel) : $roleInt,
            "title"       => strtolower($title),
            "content"     => strtolower($contents),
            "items"       => $item,
        );

        $time = time();
        $sign_args = json_encode($url_args);
        $sign = md5("args={$sign_args}&fun=web_op_sys_mail&mod=mail_api&sid={$serverId}&time={$time}&key={$this->key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => 'web_op_sys_mail',
            'mod'       => 'mail_api',
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );

        //发送内容
        $res = $this->send_post(env('WXURL'), $info);

        return $res;
    }

    protected function addBan($ban)
    {
        $result = Ban::create($ban);

        return $result;
    }

    protected function send_post($url, $params) {

        $post_data = http_build_query($params);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $post_data,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

}