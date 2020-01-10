<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Ban;
use App\Models\Broadcast;
use App\Models\IpOperation;
use App\Models\Item;
use App\Models\NewRole;
use App\Models\Server;
use Illuminate\Http\Request;
use App\Libray\RequestTool;
use App\Libray\Response;

class GameController extends Controller
{
    private $key = 'rJYgMdja4KXMqwFbAibOM7jhls';
    private $ajax_key = '51Game@123.com&%#';

    /**
     * 禁言解禁
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function banChat(Request $request, Ban $ban)
    {
        $uid       = $request->input('role_id');
        $oper      = $request->input('oper');
        $server_id = intval($request->input('server_id'));

        $url_args = array(
            "uid"   => intval($uid),
            "oper"  => intval($oper),
        );

        $time      = time();
        $fun       = 'web_op_sys_ban';
        $mod       = 'chat_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $server_id, $this->key);

        if ($oper == 1) {
            $ban->role_id  = $uid;
            $ban->serverId = $server_id;
            $ban->status   = 1;
            $ban->type     = 1;
            $ban->reason   = '';
            $banResult     = $ban->save();
        }else{
            $banResult = Ban::where(['role_id' => $uid, 'type' => 1])->update(['status' => 0]);
        }

        if ($result['res'] == "1") {
            if ($banResult){
                return response(Response::Success());
            }
            return response(Response::Error(trans('ResponseMsg.SPECIFIED_QUESTIONED_USER_NOT_EXIST'), 30001));

        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 封停ip
     */
    public function closureIp(Request $request, IpOperation $ip_operation)
    {
        $ip       = $request->input('ip');
        $times    = $request->input('times');
        $serverId = intval($request->input('server_id'));

        $url_args = array(
            "ip"    => $ip,
            "oper"  => 1,
            "time"  => intval($times*86400 + time())
        );

        $time      = time();
        $fun       = 'web_op_sys_ip_suspend';
        $mod       = 'login_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            $ip_operation->ip         = $ip;
            $ip_operation->status     = 1;
            $ip_operation->time       = $times;
            $ip_operation->account_id = UID;
            $ip_operation->server_id  = $serverId;
            $ip_operation->save();

            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 解封ip
     */
    public function unlockIp(Request $request, IpOperation $ip_operation)
    {
        $id       = $request->input('id');

        $res = $ip_operation->where(['id' => $id])->first();

        if (!$res){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        $url_args = array(
            "ip"    => $res->ip,
            "oper"  => 0,
        );

        $time      = time();
        $fun       = 'web_op_sys_ip_suspend';
        $mod       = 'login_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, intval($res->server_id), $this->key);

        if ($result['res'] == "1") {
            $ip_operation->where(['id' => $id])->update(['status' => 0]);
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 充值
     */
    public function recharge(Request $request)
    {
        $uid      = $request->input('uid');
        $serverId = $request->input('server_id');
        $goods_id = $request->input('goods_id');

        $url_args = array(
            "uid"      => $uid,
            "goods_id" => $goods_id,
        );

        $time      = time();
        $fun       = 'web_op_sys_pay_rmb';
        $mod       = 'pay_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 开服
     */
    public function openSuit(Request $request, Server $server)
    {
        $serverId = $request->input('id');

        $url_args = array(
            "is_open" => 1,
        );

        $time      = time();
        $fun       = 'web_op_node';
        $mod       = 'global';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            $server->where(['id' => $serverId])->update(['server_status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 关服
     */
    public function closeSuit(Request $request, Server $server)
    {
        $serverId = $request->input('id');

        $url_args = array(
            "is_open" => 0,
        );

        $time      = time();
        $fun       = 'web_op_node';
        $mod       = 'global';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            $server->where(['id' => $serverId])->update(['server_status' => 0, 'updated_at' => date('Y-m-d H:i:s', time())]);
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 发送道具
     */
    public function sendProp(Request $request, Item $item)
    {
        $serverId = $request->input('server_id');
        $uid      = $request->input('uid');
        $item_id  = $request->input('item_id');
        $count    = $request->input('count');

        $url_args = array(
            "uid"     => $uid,
            "item_id" => $item_id,
            "count"   => $count,
        );

        $time = time();
        $fun  = 'web_op_sys_send_item';
        $mod  = 'pay_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            $item->uid       = $uid;
            $item->item_id   = $item_id;
            $item->count     = $count;
            $item->server_id = $serverId;
            $itemResult      = $item->save();
            if ($itemResult){
                return response(Response::Success());
            }
            return response(Response::Error(trans('ResponseMsg.SPECIFIED_QUESTIONED_USER_NOT_EXIST'), 30001));
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 聊天公告
     */
    public function chatAnnouncement(Request $request, Announcement $announcement)
    {
        $id = $request->input('id');

        $res = $announcement->where(['id' => $id])->first();

        if (!$res){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        $url_args = array(
            "comment"      => strtolower(RequestTool::ChineseConversion($res->comment)),
        );

        $url = array(
            "comment"      => strtolower(RequestTool::conversion($res->comment)),
        );

        $time      = time();
        $fun       = 'web_op_sys_chat';
        $mod       = 'chat_api';

        $result = $this->requestWXs($url_args, $url, $fun, $mod, $time, intval($res->server_id), $this->key);

        if ($result['res'] == "1") {
            $announcement->where(['id' => $id])->update(['status' => 0]);
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 发送跑马灯
     */
    public function sendMarquee(Request $request, Broadcast $broadcast)
    {
        $id = $request->input('id');

        $res = $broadcast->where(['id' => $id])->first();

        if (!$res){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        $str_long_content = strlen($res->content);
        $contents = '';
        for ($i=0; $i < $str_long_content ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $res->content[$i])){
                $contents .= urlencode($res->content[$i]);
            }else{
                $contents .= $res->content[$i];
            }
        }

        $serverId = intval($res->server_id);

        $url_args = array(
            "id"       => intval($id),
            "interval" => intval($res->interval),
            "times"    => intval($res->times),
            "content"  => strtolower($contents),
        );

        $time      = time();
        $fun       = 'web_op_sys_broadcast';
        $mod       = 'chat_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            $broadcast->where(['id' => $id])->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s', time())]);
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 取消跑马灯
     */
    public function cancelMarquee(Request $request, Broadcast $broadcast)
    {
        $id = $request->input('id');

        $res = $broadcast->where(['id' => $id])->first();

        $serverId = intval($res->server_id);

        $url_args = array(
            "id"       => intval($id),
        );

        $time      = time();
        $fun       = 'web_op_sys_broadcast_undo';
        $mod       = 'chat_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            $broadcast->where(['id' => $id])->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 定时开服
     */
    public function timeTack(Request $request, Server $server)
    {
        $activity_at = $request->input('work_time');

        $serverId = intval($request->input('server_id'));

        $work_time = strtotime($activity_at);

        $url_args = array(
            "work_time" => $work_time,
        );

        $time      = time();
        $fun       = 'web_op_work_day';
        $mod       = 'global';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            $server->where(['id' => $serverId])->update(['activity_at' => $activity_at, 'updated_at' => date('Y-m-d H:i:s', time())]);
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 服务器实时数据
     */
    public function ServerData(Request $request)
    {
        $sid = array(1,2,3,4,10,11,12,13,15,1001,2001,2002,10001,11001,20001,20002);

        $sid_list = json_encode($sid);

        $url_args = array(
            "work_time" => $sid_list,
        );

        $time      = time();
        $fun       = 'web_op_sync_data';
        $mod       = 'global';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $sid_list, $this->key);

        return response(Response::Success($result));

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 创角邮件接口
     * @param Request $request
     * @param NewRole $newRoleModel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function giftRoleGift(Request $request, NewRole $newRoleModel)
    {
        $rid  = $request->input('rid');
        $sid  = $request->input('sid');
        $sign = $request->input('sign');

        if (!$rid || !$sid || !$sign){
            return response(Response::RequestError(137001));
        }

        if ($sign !== md5($rid.$sid.$this->ajax_key)){
            return response(Response::RequestError(137002));
        }

        $newRole = $newRoleModel->where(['status' => 1])->orderBy('id', 'DESC')->first();

        $serverId = intval($sid);

        $url_args = array(
            "objects"     => array(intval($rid)),
            "title"       => strtolower(RequestTool::ChineseConversion($newRole->title)),
            "content"     => strtolower(RequestTool::ChineseConversion($newRole->content)),
            "items"       => $newRole->attach_s,
        );

        $time      = time();
        $fun       = 'web_op_sys_mail';
        $mod       = 'mail_api';

        $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

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

    /**
     * 公共请求方法
     *
     * @param $url_args
     * @param $fun
     * @param $mod
     * @param $time
     * @param $serverId
     * @param $key
     * @return mixed
     */
    protected function requestWX($url_args, $fun, $mod, $time, $serverId, $key)
    {
        $sign_args = json_encode($url_args);

        $sign = md5("args={$sign_args}&fun={$fun}&mod={$mod}&sid={$serverId}&time={$time}&key={$key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => $fun,
            'mod'       => $mod,
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );

        $res = RequestTool::send_post(env('WXURL'), $info);

        $result = json_decode($res, true);

        return $result;
    }

    protected function requestWXs($url_args, $url, $fun, $mod, $time, $serverId, $key)
    {
        $sign_args = json_encode($url_args);

        $sign = md5("args={$sign_args}&fun={$fun}&mod={$mod}&sid={$serverId}&time={$time}&key={$key}");

        $sign_url = json_encode($url);

        //组装内容
        $info = array(
            'args'      => $sign_url,
            'fun'       => $fun,
            'mod'       => $mod,
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );

        $res = RequestTool::send_post(env('WXURL'), $info);

        $result = json_decode($res, true);

        return $result;
    }
}
