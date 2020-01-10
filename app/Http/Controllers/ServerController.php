<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    private $key = 'rJYgMdja4KXMqwFbAibOM7jhls';

    public function index(Request $request, Server $server)
    {
        $orm = $server->select('id', 'server_name', 'logo', 'type', 'channel_id', 'note', 'ip_status', 'server_status', 'activity_at');

        $list = $orm->paginate(20);

        return response(Response::Success($list));
    }

    public function update(Request $request, Server $server)
    {
        $id = $request->input('id');
        $status = $request->input('ip_status');

        $result = $server->where(['id' => $id])->update(['ip_status' => $status]);

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function edit(Request $request, Server $server)
    {
        $id          = $request->input('id');
        $logo        = $request->input('logo');
        $note        = $request->input('note');
        $server_name = $request->input('server_name');

        $result = $server->where(['id' => $id])
            ->update([
                'server_name' => $server_name,
                'note' => $note,
                'logo' => $logo
            ]);

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function store(Request $request, Server $server)
    {
        $id            = $request->input('id');
        $server_name   = $request->input('server_name');
        $logo          = $request->input('logo');
        $type          = $request->input('type', 0);
        $ip_status     = $request->input('ip_status', 0);
        $server_status = $request->input('server_status', 0);
        $note          = $request->input('note');

        $server->id            = $id;
        $server->server_name   = $server_name;
        $server->logo          = $logo;
        $server->type          = $type;
        $server->ip_status     = $ip_status;
        $server->server_status = $server_status;
        $server->note          = $note;
        $result = $server->save();

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

}