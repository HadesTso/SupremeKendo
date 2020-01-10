<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Channel;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * 列表显示
     * @param Request $request
     * @param Channel $channel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(Request $request, Channel $channel)
    {
        $orm = $channel->with([
            'account' => function($query){
                $query->select('id', 'account_name');
            }
        ]);

        $list = $orm->paginate(10);

        return response(Response::SUCCESS($list));
    }

    /**
     * 新增功能
     * @param Request $request
     * @param Channel $channel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request, Channel $channel)
    {
        $id = $request->input('id');
        $channel_name = $request->input('channel_name');
        $channel_abbr = $request->input('channel_abbr');

        $channel->id           = $id;
        $channel->channel_name = $channel_name;
        $channel->channel_abbr = $channel_abbr;
        $channel->principal_id = UID;
        $channel->status       = 0;

        $result = $channel->save();

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 更新功能
     * @param Request $request
     * @param Channel $channel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request, Channel $channel)
    {
        $id = $request->input('id');
        $channel_name = $request->input('channel_name');
        $channel_abbr = $request->input('channel_abbr');

        $result = $channel
            ->where(['id' => $id])
            ->update(['channel_name' => $channel_name, 'channel_abbr' => $channel_abbr, 'updated_at' => date('Y-m-d H:i:s', time())]);

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }
}