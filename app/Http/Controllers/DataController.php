<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Libray\RSA;
use App\Models\Ban;
use App\Models\Channel;
use App\Models\Good;
use App\Models\Item;
use App\Models\Server;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Excel;

class DataController extends Controller
{
    protected $database = array(
        '20003' => array('user' => 'jyzj', 'chat' => 'jyzj_chat'),
        '20004' => array('user' => 'bmsg', 'chat' => 'bmsg_chat'),
        '20005' => array('user' => 'bhgr', 'chat' => 'bhgr_chat'),
        '20006' => array('user' => 'twzx', 'chat' => 'twzx_chat'),
    );


    public function roleData(Request $request, Ban $ban)
	{
	    $server_id  = 20003;

        $orm = DB::connection($this->database[$server_id]['user'])
            ->table('user')
            ->select('uid', 'uuid', 'sid', 'cid', 'uname', 'pay_gold', 'reg_time', 'login_times', 'data_json');

        $list = $orm->get();

        $server  = Server::all()->keyBy('id')->toArray();
	    $channel = Channel::all()->keyBy('id')->toArray();

	    $username = DB::connection('account')
            ->table('account')
            ->select('account', 'uuid')
            ->get()->toArray();

        $userCount = array_column($username, null, 'uuid');

	    foreach ($list as $key=>$value) {

	        $json = json_decode($value->data_json, true);

	        if (isset($json['temp_data'])) {
	            $value->temp_data = $json['temp_data'];
            }else {
	            $value->temp_data = [];
            }
	        unset($value->data_json);

            $value->cid = $channel[$value->cid]['channel_name'];
            $value->server_name = $server[$server_id]['server_name'];
            $value->reg_time   = date('Y-m-d H:i:s', $value->reg_time);

            $value->username = substr($userCount[$value->uuid]->account, 21);

            switch (true)
            {
                case $this->number_segment_between($value->pay_gold, 0 , 5):
                    $value->vip = 0;
                    break;
                case ($value->pay_gold >= 6 && $value->pay_gold <= 41):
                    $value->vip = 1;
                    break;
                case ($value->pay_gold >= 42 && $value->pay_gold < 98):
                    $value->vip = 2;
                    break;
                case ($value->pay_gold >= 98 && $value->pay_gold < 198):
                    $value->vip = 3;
                    break;
                case ($value->pay_gold >= 198 && $value->pay_gold < 498):
                    $value->vip = 4;
                    break;
                case ($value->pay_gold >= 498 && $value->pay_gold < 998):
                    $value->vip = 5;
                    break;
                case ($value->pay_gold >= 998 && $value->pay_gold < 2000):
                    $value->vip = 6;
                    break;
                case ($value->pay_gold >= 2000 && $value->pay_gold < 3600):
                    $value->vip = 7;
                    break;
                case ($value->pay_gold >= 3600 && $value->pay_gold < 5800):
                    $value->vip = 8;
                    break;
                case ($value->pay_gold >= 5800 && $value->pay_gold < 9800):
                    $value->vip = 9;
                    break;
                case ($value->pay_gold >= 9800 && $value->pay_gold < 15000):
                    $value->vip = 10;
                    break;
                case ($value->pay_gold >= 15000 && $value->pay_gold < 21000):
                    $value->vip = 11;
                    break;
                default:
                    $value->vip = 12;
            }
        }

	    $cellData = [
            ['uid', 'uuid', '渠道', '区服', '角色名', 'vip等级', '注册时间', '登录次数', '侠客无双数据', '武林秘籍数据', '神兵利器数据', '名动江湖数据', '扫荡通关数据', '一代宗师', '武林至尊数据'],
        ];

        foreach ($list as $key=>$value) {
            if (!empty($value->temp_data)) {
                $cellData[] = array(
                    $value->uid,
                    $value->uuid,
                    $value->cid,
                    $value->server_name,
                    $value->uname,
                    $value->vip,
                    $value->reg_time,
                    $value->login_times,
                    $value->temp_data[0],
                    $value->temp_data[1],
                    $value->temp_data[2],
                    $value->temp_data[3],
                    $value->temp_data[4],
                    $value->temp_data[5],
                    $value->temp_data[6],
                );
            } else {
                $cellData[] = array(
                    $value->uid,
                    $value->uuid,
                    $value->cid,
                    $value->server_name,
                    $value->uname,
                    $value->vip,
                    $value->reg_time,
                    $value->login_times,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                );
            }
        }

        Excel::create('角色信息',function($excel) use ($cellData){
            $excel->sheet('role', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');

        //return response(Response::Success($list));
	}


    /**
     * 角色列表
     * @param Request $request
     * @param Ban $ban
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function roleList(Request $request, Ban $ban)
	{
	    $role_id    = $request->input('role_id');
	    $role_name  = $request->input('role_name');
	    $channel_id = $request->input('channel_id');
	    $server_id  = $request->input('server_id');
	    $order      = $request->input('order');
	    $by         = $request->input('by');
	    $time       = $request->input('time');

        $orm = DB::connection($this->database[$server_id]['user'])
            ->table('user')
            ->select('uid', 'uuid', 'sid', 'cid', 'uname', 'sex', 'renown_lv', 'pay_gold', 'renown', 'gold', 'silver', 'reg_time', 'reg_ip', 'login_times','login_time', 'last_time', 'last_ip', 'data_json');

        if ($role_id){
           $orm->where(['uid' => $role_id]);
        }

        if ($role_name){
           $orm->where('uname', 'like', '%'.$role_name.'%');
        }

        if ($channel_id){
           $orm->where(['cid' => $channel_id]);
        }

        if ($time[0] && $time[1]){
           $orm->whereBetween('reg_time', array(strtotime($time[0]), strtotime($time[1])));
        }

        if ($order && $by){
            $orm->orderBy($order, $by);
        }else{
            $orm->orderBy('reg_time', 'ASC');
        }

        $list = $orm->paginate(20);

        $server  = Server::all()->keyBy('id')->toArray();
	    $channel = Channel::all()->keyBy('id')->toArray();

	    $username = DB::connection('account')
            ->table('account')
            ->select('account', 'uuid')
            ->get()->toArray();

        $userCount = array_column($username, null, 'uuid');

	    foreach ($list as $key=>$value) {
	        $json = json_decode($value->data_json, true);

	        if (isset($json['temp_data'])) {
	            $value->chivalrousMan = $json['temp_data'][0];
	            $value->wulinSecret   = $json['temp_data'][1];
	            $value->magicWeapon   = $json['temp_data'][2];
	            $value->dynamic       = $json['temp_data'][3];
	            $value->sweeping      = $json['temp_data'][4];
	            $value->grandMaster   = $json['temp_data'][5];
	            $value->wulinSupreme  = $json['temp_data'][6];
            }else {
	            $value->chivalrousMan = 0;
	            $value->wulinSecret   = 0;
	            $value->magicWeapon   = 0;
	            $value->dynamic       = 0;
	            $value->sweeping      = 0;
	            $value->grandMaster   = 0;
	            $value->wulinSupreme  = 0;
            }
	        unset($value->data_json);

            $value->cid = $channel[$value->cid]['channel_name'];
            $value->server_name = $server[$value->sid]['server_name'];
            $value->login_time = date('Y-m-d H:i:s', $value->login_time);
            $value->reg_time   = date('Y-m-d H:i:s', $value->reg_time);
            $value->last_time  = date('Y-m-d H:i:s', $value->last_time);

            $value->username = substr($userCount[$value->uuid]->account, 21);

            $status = $ban->where(['role_id' => $value->uid, 'serverId' => $value->sid, 'status' => 1])->select('type')->get();
            $value->status = '';

            if ($status) {
                foreach ($status as $k => $v) {
                    if ($v->type == 1) {
                        $value->status .= '禁言';
                        $value->chat = 1;
                    }
                    if ($v->type == 2) {
                        $value->status .= '禁登';
                        $value->login = 1;
                    }
                }
            }

            if (!$value->status){
                $value->status = '正常';
            }

            switch (true)
            {
                case $this->number_segment_between($value->pay_gold, 0 , 5):
                    $value->vip = 0;
                    break;
                case ($value->pay_gold >= 6 && $value->pay_gold <= 41):
                    $value->vip = 1;
                    break;
                case ($value->pay_gold >= 42 && $value->pay_gold < 98):
                    $value->vip = 2;
                    break;
                case ($value->pay_gold >= 98 && $value->pay_gold < 198):
                    $value->vip = 3;
                    break;
                case ($value->pay_gold >= 198 && $value->pay_gold < 498):
                    $value->vip = 4;
                    break;
                case ($value->pay_gold >= 498 && $value->pay_gold < 998):
                    $value->vip = 5;
                    break;
                case ($value->pay_gold >= 998 && $value->pay_gold < 2000):
                    $value->vip = 6;
                    break;
                case ($value->pay_gold >= 2000 && $value->pay_gold < 3600):
                    $value->vip = 7;
                    break;
                case ($value->pay_gold >= 3600 && $value->pay_gold < 5800):
                    $value->vip = 8;
                    break;
                case ($value->pay_gold >= 5800 && $value->pay_gold < 9800):
                    $value->vip = 9;
                    break;
                case ($value->pay_gold >= 9800 && $value->pay_gold < 15000):
                    $value->vip = 10;
                    break;
                case ($value->pay_gold >= 15000 && $value->pay_gold < 21000):
                    $value->vip = 11;
                    break;
                default:
                    $value->vip = 12;
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 宠妻列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function wifeList(Request $request)
	{
	    $uid             = $request->input('uid');
	    $channel         = $request->input('channel');
	    $wife_id         = $request->input('wife_id');
	    $intimacy        = $request->input('intimacy');
	    $wife_event_desc = $request->input('wife_event_desc');
	    $time            = $request->input('time');

        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_wife')
            ->select('id', 'uid', 'channel', 'wife_id', 'intimacy', 'child_count', 'wife_event_type', 'wife_event_desc', 'old_value', 'add_value', 'new_value', 'time');

        $list = $orm->paginate(20);

        $role = DB::connection('wxfyl_s2002')
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->time        = date('Y-m-d H:i:s', $value->time);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 子女列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function childList(Request $request)
	{
        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_child')
            ->select('id', 'uid', 'cid', 'child_idx', 'child_sex', 'child_name', 'child_lv', 'child_event_type', 'child_event_desc', 'time');

        $list = $orm->paginate(20);

        $role = DB::connection('wxfyl_s2002')
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->time        = date('Y-m-d H:i:s', $value->time);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 道具变化列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function resourceList(Request $request)
	{
	    $role_id   = $request->input('role_id');
	    $server_id = $request->input('server_id');
	    $goods_id  = $request->input('goods_id');
	    $details   = $request->input('details');
	    $time      = $request->input('time');

        $orm = DB::connection($this->database[$server_id]['chat'])
            ->table('lg_resource')
            ->select('id', 'server_id', 'role_id', 'action_id', 'action_desc', 'item_id', 'init_value', 'add_value', 'result_value', 'channel', 'role_name', 'user_code', 'time');

        if ($role_id) {
            $orm->where(['role_id' => $role_id]);
        }

        if ($goods_id) {
            $orm->where(['item_id' => $goods_id]);
        }

        if ($details) {
            $orm->where('action_desc', 'like', '%' . $details . '%');
        }

        if ($time[0] && $time[1]) {
            $orm->whereBetween('time', array(strtotime($time[0]), strtotime($time[1])));
        }

        $list = $orm->paginate(20);

        $server = Server::all()->keyBy('id')->toArray();
        $good   = Good::all()->keyBy('id')->toArray();

        foreach ($list as $key=>$value) {
            $value->server_name = $server[$value->server_id]['server_name'];
            $value->item_name   = $good[$value->item_id]['good_name'];
            $value->time        = date('Y-m-d H:i:s', $value->time);
        }

        return response(Response::Success($list));
	}

	/**
     * 角色登录列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function roleStreamList(Request $request)
	{
	    $role_id = $request->input('role_id', null);
	    $server_id = $request->input('server_id', null);

	    $time = $request->input('time', null);

        $orm = DB::connection($this->database[$server_id]['chat'])
            ->table('lg_role_stream')
            ->select('id', 'channel', 'userCode', 'serverId', 'roleId', 'loginTime', 'loginOutTime', 'onlineTime', 'createTime');

        if ($role_id){
            $orm->where(['roleId' => $role_id]);
        }

        if ($time){
            $orm->whereBetween('loginTime', [strtotime($time[0]), strtotime($time[1])]);
            $orm->whereBetween('loginOutTime', [strtotime($time[0]), strtotime($time[1])]);
        }

        $server  = Server::all()->keyBy('id')->toArray();

        $role = DB::connection($this->database[$server_id]['user'])
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $list = $orm->paginate(20);

        $nameCount = array_column($role, null, 'uid');

	    foreach ($list as $key=>$value) {
            $value->server_name  = $server[$value->serverId]['server_name'];
            $value->loginTime    = date('Y-m-d H:i:s', $value->loginTime);
            $value->loginOutTime = date('Y-m-d H:i:s', $value->loginOutTime);
            $value->createTime   = date('Y-m-d H:i:s', $value->createTime);

            if (isset($nameCount[$value->roleId])){
                $value->role_name = $nameCount[$value->roleId]->uname;
            }else{
                $value->role_name = '-';
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 聊天列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function chatList(Request $request, Ban $ban)
    {
        $server_id = $request->input('server_id');

        $orm = DB::connection($this->database[$server_id]['chat'])
            ->table('lg_chat')
            ->select('id', 'uid', 'chatTime', 'chatChannel', 'chatText')
            ->orderBy('id', 'asc');

        $list = $orm->paginate(20);

        $role = DB::connection($this->database[$server_id]['user'])
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->chatTime   = date('Y-m-d H:i:s', $value->chatTime);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }

            $status = $ban->where(['role_id' => $value->uid, 'serverId' => $server_id, 'status' => 1, 'type' => 1])->select('type')->first();
            $value->status = '';

            if ($status) {
                if ($status->type == 1) {
                    $value->status .= '禁言';
                }
            }
            if (!$value->status){
                $value->status = '正常';
            }
        }

        return response(Response::Success($list));
    }

    /**
     * 实时聊天监控
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function RealTimeChat(Request $request, Ban $ban)
    {
        $id = $request->input('id');
        $server_id = $request->input('server_id');

        if (!Redis::get('real_time')){
            $time = time();
            Redis::set('real_time', $time);
            Redis::expire('real_time', 600);
        }

        $orm = DB::connection($this->database[$server_id]['chat'])
            ->table('lg_chat')
            ->select('id', 'uid', 'chatTime', 'chatChannel', 'chatText');

        if ($id){
            Redis::set('real_time', null);
            $orm->where('id', '>', $id);
        }else{
            $orm->where('chatTime', '>', Redis::get('real_time'));
        }

        $list = $orm->get();

	    $role = DB::connection($this->database[$server_id]['user'])
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->chatTime   = date('Y-m-d H:i:s', $value->chatTime);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }

            $status = $ban->where(['role_id' => $value->uid, 'serverId' => $server_id, 'status' => 1, 'type' => 1])->select('type')->first();
            $value->status = '';

            if ($status) {
                if ($status->type == 1) {
                    $value->status .= '禁言';
                }
            }
            if (!$value->status){
                $value->status = '正常';
            }
        }

        return response(Response::Success($list));
    }

    /**
     * 订单列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function OrderList(Request $request)
    {
        $role_id    = $request->input('role_id');
        $tranId     = $request->input('tran_id');
        $orderId    = $request->input('order_id');
	    $channel_id = $request->input('channel_id');
	    $server_id  = $request->input('server_id');
	    $time       = $request->input('time');

        $orm = DB::connection('order')
            ->table('lg_pay')
            ->orderBy('time', 'DESC')
            ->select('orderId', 'tranId', 'goodsId', 'uid', 'time', 'sid', 'cid', 'amount');

        if ($role_id) {
            $orm->where(['uid' => $role_id]);
        }

        if ($server_id) {
            $orm->where(['sid' => $server_id]);
        }

        if ($channel_id) {
            $orm->where(['cid' => $channel_id]);
        }

        if ($tranId) {
            $orm->where(['tranId' => $tranId]);
        }

        if ($orderId) {
            $orm->where(['orderId' => $orderId]);
        }

        if ($time[0] && $time[1]) {
            $orm->whereBetween('time', array(strtotime($time[0]), strtotime($time[1])));
        }

        $list = $orm->paginate(20);

	    $role = DB::connection($this->database[$server_id]['user'])
            ->table('user')
            ->select('uid', 'uname', 'uuid')
            ->get()->toArray();

	    $username = DB::connection('account')
            ->table('account')
            ->select('account', 'uuid')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        $userCount = array_column($username, null, 'uuid');

        $server = Server::all()->keyBy('id')->toArray();
        foreach ($list as $key=>$value) {
            $value->time   = date('Y-m-d H:i:s', $value->time);
            $value->server_name = $server[$value->sid]['server_name'];

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
                if (isset($userCount[$nameCount[$value->uid]->uuid])) {
                    $value->username = substr($userCount[$nameCount[$value->uid]->uuid]->account, 21);
                } else {
                    $value->username = '-';
                }
            } else {
                $value->role_name = '-';
                $value->username = '-';
            }
        }

        return response(Response::Success($list));
    }

    /**
     * 道具列表
     * @param Request $request
     * @param Item $item
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function itemList(Request $request, Item $item)
    {
        $orm = $item->paginate(20);

        $goods   = Good::all()->keyBy('id')->toArray();
        $servers = Server::all()->keyBy('id')->toArray();

        foreach ($orm as $value) {
            $value['item_id']   = $goods[$value->item_id]['good_name'];
            $value['server_id'] = $servers[$value->server_id]['server_name'];
        }

        return response(Response::Success($orm));
    }

    /**
     * 数据区间
     * @param $str_num
     * @param $min
     * @param $max
     * @return bool
     */
	protected function number_segment_between($str_num, $min, $max)
    {
        return version_compare($str_num, $min, '>=') and version_compare($str_num, $max, '<=');
    }
}
