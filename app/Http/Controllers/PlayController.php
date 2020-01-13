<?php
namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Account;
use App\Models\Games;
use Illuminate\Http\Request;

class PlayController extends Controller
{
    /**
     * @param Request $request
     * @param Games $games
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request, Games $games)
    {
        $data = $request->all();

        if ($games->where(['game_name' => $data['game_name']])->first()){
            return response(Response::Error(trans('ResponseMsg.USER_HAS_EXISTED'), 20005));
        }

        $games->game_name    = $data['game_name'];
        $games->request_link = $data['request_link'];

        $result = $games->save();

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
}