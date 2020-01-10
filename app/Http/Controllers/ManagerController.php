<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Account;
use App\Models\Manager;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function managerList(Request $request, Manager $manager)
    {
        $manager_name = $request->input('name');

        $orm = $manager->select('id', 'manager_name', 'remark', 'status', 'menu');

        if ($manager_name){
            $orm->where(['manager_name' => $manager_name]);
        }

        $list = $orm->paginate(5);
        foreach ($list as $key=>$val) {
            $menu = json_decode($val['menu'], true);
            if ($menu){
                foreach ($menu as $k=>$val) {
                    $menu[$k] = json_decode($val, true);
                }
                $list[$key]['menu'] = $menu;
            }
        }

        return response(Response::Success($list));
    }

    public function store(Request $request, Manager $manager)
    {
        $manager_name = $request->input('name');
        $status       = $request->input('status');
        $remark       = $request->input('remark');
        $menu         = $request->input('menuList');

        if ($manager->where(['manager_name' => $manager_name])->first()){
            return response(Response::Error(trans('ResponseMsg.ROLE_HAS_EXISTED'), 90001));
        }

        $manager->manager_name = $manager_name;
        $manager->status       = $status;
        $manager->remark       = $remark;
        $manager->menu         = json_encode($menu);

        $result = $manager->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));

    }

    public function update(Request $request, Manager $manager)
    {
        $data = $request->all();

        if (!$manager->where(['id' => $data['id']])->update(['status' => $data['status']])){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }

    public function save(Request $request, Manager $manager)
    {
        $id           = $request->input('id');
        $manager_name = $request->input('name');
        $status       = $request->input('status');
        $remark       = $request->input('remark');
        $menu         = $request->input('menuList');

        $orm = $manager->where(['id' => $id])->first();

        $orm->manager_name = $manager_name;
        $orm->status       = $status;
        $orm->remark       = $remark;
        $orm->menu         = json_encode($menu);
        $orm->updated_at   = date('Y-m-d H:i:s', time());
        $result = $orm->save();

        if (!$result){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }
}