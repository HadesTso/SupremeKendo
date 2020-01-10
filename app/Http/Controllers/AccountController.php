<?php
namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function store(Request $request, Account $account)
    {
        $data = $request->all();
        $data = $request->getClientIp();

        if ($account->where(['account_name' => $data['name']])->first()){
            return response(Response::Error(trans('ResponseMsg.USER_HAS_EXISTED'), 20005));
        }

        $account->account_name = $data['name'];
        $account->real_name    = $data['real_name'];
        $account->password     = password_hash($data['password'], PASSWORD_DEFAULT);
        $account->manager_id   = $data['manager_id'];
        $account->channel      = empty($data['channel']) ? '' : json_encode($data['channel']);

        $result = $account->save();

        if ($result){
            return response(Response::Success());
        }

        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    public function save(Request $request, Account $account)
    {
        $id         = $request->input('id');
        $real_name  = $request->input('real_name');
        $password   = $request->input('password', null);
        $manager_id = $request->input('manager_id');
        $channel    = $request->input('channel', null);

        $orm = $account->where(['id' => $id])->first();

        if ($password){
            $orm->password = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($channel){
            $orm->channel    = json_encode($channel);
        }

        $orm->real_name  = $real_name;
        $orm->manager_id = $manager_id;
        $result = $orm->save();

        if (!$result){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }

    public function update(Request $request, Account $account)
    {
        $data = $request->all();

        if (!$account->where(['id' => $data['id']])->update(['status' => $data['status']])){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }

    public function change(Request $request, Account $account)
    {
        $data = $request->all();

        if (!$account->where(['id' => $data['id']])->update(['status' => $data['status']])){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }

    public function accountInfo(Account $account)
    {
        $info = $account->where(['id' => UID])->first();

        return response(Response::Success($info));
    }

    public function accountList(Request $request, Account $account)
    {
        $account_name = $request->input('name');
        $manager_id   = $request->input('manager_id');

        $orm = $account->with(['manager' => function ($query){
            $query->select('id', 'manager_name');
        }])->select('id', 'account_name', 'real_name', 'manager_id', 'channel', 'created_at', 'status');

        if ($account_name){
            $orm->where(['account_name' => $account_name]);
        }

        if ($manager_id){
            $orm->where(['manager_id' => $manager_id]);
        }
        $list = $orm->paginate(5);

        foreach ($list as &$value){
            $channelVar = json_decode($value['channel'], true);

            if ($channelVar){
                $channelArray = array();
                foreach ($channelVar as $key=>$val){
                    $channel_data = json_decode($val, true);
                    $channelArray[$key]['id'] = $channel_data['id'];
                    $channelArray[$key]['channel_name'] = $channel_data['channel_name'];
                }
                $value['channel'] = $channelArray;
            }
        }

        return response(Response::Success($list));
    }
}