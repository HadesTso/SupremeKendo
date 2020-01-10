<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Carte;
use Illuminate\Http\Request;

class CarteController extends Controller
{
    public function carteList(Carte $carte)
    {
        $arr = array();

        $list = $carte::GetAllMenuTree(0, $arr);

        return response(Response::Success($list));
    }

    public function store(Request $request, Carte $carte)
    {
        $pid  = $request->input('pid');
        $name = $request->input('name');

        $carte->pid = $pid;
        $carte->carte_name = $name;
        $carte->status = 1;
        $result = $carte->save();

        if ($result){
            return response(Response::Success());
        }else{
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function update(Request $request, Carte $carte)
    {
        $id   = $request->input('id');
        $name = $request->input('name');

        $result = $carte->where(['id' => $id])->update(['name' => $name]);

        if ($result){
            return response(Response::Success());
        }else{
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }
}