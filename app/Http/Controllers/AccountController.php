<?php
namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Account;
use App\Models\Games;
use Illuminate\Http\Request;

class AccountController extends Controller
{

    public function store(Request $request, Account $account)
    {
        $data = $request->all();

        if ($account->where(['account_name' => $data['account_name']])->first()){
            return response(Response::Error(trans('ResponseMsg.USER_HAS_EXISTED'), 20005));
        }

        $account->account_name = $data['account_name'];
        $account->real_name    = $data['real_name'];
        $account->password     = password_hash($data['password'], PASSWORD_DEFAULT);
        $account->manager_id   = empty($data['manager_id']) ? NULL : json_encode($data['manager_id']);
        $account->channel      = empty($data['channel']) ? NULL : json_encode($data['channel']);
        $account->game         = empty($data['game']) ? NULL : json_encode($data['game']);
        $account->status       = 1;
        $account->ip           = $request->getClientIp();

        $result = $account->save();

        if ($result){
            return response(Response::Success());
        }

        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    public function modification(Request $request, Account $account)
    {
        $id         = $request->input('id');
        $real_name  = $request->input('real_name');
        $password   = $request->input('password', null);
        $manager_id = $request->input('manager_id');
        $channel    = $request->input('channel', null);
        $game       = $request->input('game', null);

        $orm = $account->where(['id' => $id])->first();

        if ($password){
            $orm->password = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($channel){
            $orm->channel = json_encode($channel);
        }

        if ($game){
            $orm->game = json_encode($game);
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

    public function accountInfo(Account $account)
    {
        $info = $account->where(['id' => UID])->first();

        return response(Response::Success($info));
    }

    public function account(Request $request, Account $account)
    {
        $account_name = $request->input('name');
        $manager_id   = $request->input('manager_id');

        $orm = $account->with(['manager' => function ($query){
            $query->select('id', 'manager_name');
        }])->select('id', 'account_name', 'real_name', 'manager_id', 'channel', 'created_at', 'status', 'game');

        if ($account_name){
            $orm->where(['account_name' => $account_name]);
        }

        if ($manager_id){
            $orm->where(['manager_id' => $manager_id]);
        }
        $list = $orm->paginate(5);

        $games = Games::all()->keyBy('id')->toArray();

        foreach ($list as &$value){
            $gameVer = json_decode($value['game'], true);

            if ($gameVer){
                $gameArray = array();
                foreach ($gameVer as $key=>$val){
                    $gameArray[$key]['id'] = $val;
                    $gameArray[$key]['game_name'] = $games[$val]['game_name'];
                }
                $value['game'] = $gameArray;
            }
        }

        return response(Response::Success($list));
    }
}