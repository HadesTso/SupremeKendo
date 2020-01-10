<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Channel;
use App\Models\Code;
use App\Models\CodeUse;
use App\Models\Content;
use App\Models\Idfa;
use App\Models\Server;
use App\Models\WhiteIp;
use Illuminate\Http\Request;
use DB;

class AjaxController extends Controller
{
    private $key = '51Game@123.com&%#';

    /**
     * 白名单验证
     * @param Request $request
     * @param WhiteIp $white_ip
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function whiteIpCheck(Request $request, WhiteIp $white_ip, Server $server)
    {
        $ip   = $request->input('ip');
        $sid  = $request->input('sid');
        $sign = $request->input('sign');

        if ($server->where(['id' => $sid, 'ip_status' => 0])->first()){
            return response(Response::Error('正常登录', 20000));
        }

        if ($sid < 1000){
            return response(Response::Error('内网测试账号可以正常登录', 20000));
        }

        if($sign !== md5($ip.$sid.$this->key)){
            return response(Response::Error('不在白名单内禁止登录', 1));
        }

        $result = $white_ip->where(['ip' => $ip, 'server_id' => $sid, 'status' => 1])->first();

        if($result){
            return response(Response::Error('登录成功', 20001));
        }

        return response(Response::Error('不在白名单内禁止登录', 1));
    }

    /**
     * 礼包码验证接口
     * @param Request $request
     * @param CodeUse $code_use
     * @param Code $codeModel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function giftUseCheck(Request $request, CodeUse $code_use, Code $codeModel)
    {
        $rid  = $request->input('roleId');
        $code = $request->input('code');
        $cid  = $request->input('cid');
        $sid  = $request->input('serverId');
        $sign = $request->input('sign');

        if (!$rid || !$code  || !$sid || !$sign){
            return response(Response::RequestError(137001));
        }

        if ($sign !== md5($rid.$cid.$code.$sid.$this->key)){
            return response(Response::RequestError(137002));
        }

        $res = $codeModel
            ->with([
            'codeBox', 'codeBatch'
            ])
            ->where(['code' => $code, 'status' => 0])
            ->first();

        if (!$res){
            return response(Response::RequestError(137004));
        }

        if (strtotime($res->start_time) > time() || strtotime($res->end_time) < time()){
            return response(Response::RequestError(137004));
        }

        if ($res['codeBatch']['channel_id'] != 0) {
            if ($res['codeBatch']['channel_id'] != $cid) {
                return response(Response::RequestError(137003));
            }
        }

        if ($res['codeBatch']['server_id'] != 0){
            if ($sid != $res['codeBatch']['server_id']){
                return response(Response::RequestError(137005));
            }
        }

        $role = $code_use->where(['role_id' => $rid, 'code_box_id' => $res->code_box_id])->first();

        if ($role){
            return response(Response::RequestError(137006));
        }

        if ($res->remain_count <= 0){
            return response(Response::RequestError(137004));
        }

        DB::beginTransaction();
        try{
            $code_use->code_id     = $res->id;
            $code_use->code        = $res->code;
            $code_use->role_id     = $rid;
            $code_use->code_box_id = $res->code_box_id;
            $code_use->save();
            $count = $res->remain_count - 1;
            $status = 0;
            if ($count == 0){
                $status = 1;
            }
            $codeModel->where(['code' => $code])->update(['remain_count' => $count, 'status' => $status]);
            DB::commit();
            return response(Response::RequestSuccess(137007, $res->codeBox['box_item_list']));
        }catch (\Exception $exception){
            DB::rollBack();
            return response(Response::RequestError(137001));
        }

    }


    /**
     * 获取区服登录公告
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCast(Request $request, Content $content)
    {
        $sdk_name = $request->input('sdk_name');
        $c_id = $request->input('c_id');

        if (!$sdk_name || !$c_id){
            return response(Response::Error('必要参数缺失', 404));
        }

        $result = $content
            ->where('channel_id', '=', $c_id)
            ->select('content')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($result){
            return response(Response::Success($result));
        }else{
            return response(Response::RequestMsgSuccess($result));
        }
    }


    /**
     * 积分墙的排重接口
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function ExcludeRepeat(Request $request)
    {
        $idfa     = $request->input('idfa');
        $apple_id = $request->input('appid');
        $sign     = $request->input('sign');

        if (!$idfa || !$apple_id || !$sign) {
            return response(Response::Error('参数缺失', 0));
        }

        if ($sign != md5($apple_id.$idfa.$this->key)) {
            return response(Response::Error('签名失败', 0));
        }

        $result = Idfa::where(['apple_id' => $apple_id, 'idfa' => $idfa])->first();

        if ($result) {
            return response(Response::Error('该游戏设备已存在', 0));
        } else {
            return response(Response::Success('该游戏设备不存在'));
        }
    }

    /**
     * 积分墙的激活接口
     * @param Request $request
     * @param Idfa $idfaModel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function DeviceActivation(Request $request, Idfa $idfaModel)
    {
        $idfa     = $request->input('idfa');
        $apple_id = $request->input('appid');
        $sign     = $request->input('sign');

        if (!$idfa || !$apple_id || !$sign) {
            return response(Response::Error('参数缺失', 0));
        }

        if ($sign != md5($apple_id.$idfa.$this->key)) {
            return response(Response::Error('签名失败', 0));
        }

        $idfaModel->idfa = $idfa;
        $idfaModel->apple_id = $apple_id;
        $result = $idfaModel->save();

        $data = array(
            'callback' => urlencode("http://106.75.176.152:8081/api/callback")
        );

        if ($result) {
            return response(Response::Success($data));
        } else {
            return response(Response::Error('激活失败', 0));
        }

    }

    public function checkActivation(Request $request, Idfa $idfaModel)
    {
        $idfa     = $request->input('idfa');
        $apple_id = $request->input('appid');


        if (!$idfa || !$apple_id) {
            return response(Response::Error('参数缺失', 0));
        }

        $idfas = explode(',',$idfa);

        $data = array();

        foreach ($idfas as $value) {
            $result = $idfaModel->where(['apple_id' => $apple_id, 'idfa' => $value])->first();

            if ($result) {
                $data[$value] = 1;
            } else {
                $data[$value] = 0;
            }
        }

        return response(Response::Success($data));
    }

    public function callback(Request $request, Idfa $idfaModel)
    {
        $idfa     = $request->input('idfa');
        $apple_id = $request->input('appid');
        $sign     = $request->input('sign');

        if (!$idfa || !$apple_id || !$sign) {
            return response(Response::ErrorCallback('参数缺失', 0));
        }

        if ($sign != md5($apple_id.$idfa.$this->key)) {
            return response(Response::ErrorCallback('签名失败', 0));
        }

        $result = $idfaModel->where(['apple_id' => $apple_id, 'idfa' => $idfa])->first();

        if ($result) {
            if (!$result->count){
                return response(Response::ErrorCallback('重复激活', 0));
            }
            return response(Response::SuccessCallback());
        } else {
            return response(Response::ErrorCallback('激活失败', 0));
        }
    }

}