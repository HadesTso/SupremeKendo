<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Manager;
use App\Models\ManagerMenu;
use App\Service\MenuService;
use Illuminate\Http\Request;
use DB;

class ManagerController extends Controller
{
    public function store(Request $request, Manager $manager)
    {
        $name    = $request->input('name');
        $remark  = $request->input('remark');
        $status  = $request->input('status');
        $menu    = $request->input('menuList', null);

        $manager->manager_name = $name;
        $manager->remark       = $remark;
        $manager->status       = $status;
        $manager->menu         = json_encode($menu);
        $res = $manager->save();

        if ($res) {
            return response(Response::Success('新增成功'));
        } else {
            return response(Response::Error(trans("新增失败"), 400));
        }

    }

    public function update(Request $request, Manager $managerModel)
    {
        $id    = $request->input('id');
        $status  = $request->input('status');

        if (!$managerModel->where(['id' => $id])->update(['status' => $status])){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }

    public function modification(Request $request, Manager $manager)
    {
        $id      = $request->input('id');
        $name    = $request->input('name');
        $remark  = $request->input('remark');
        $status  = $request->input('status');
        $menu    = $request->input('menuList', null);


        $result = $manager->where(['id' => $id])
            ->update([
                'manager_name'  => $name,
                'remark'        => $remark,
                'status'        => $status,
                'menu'          => json_encode($menu),
                'updated_at'    => date('Y-m-d H:i:s', time()),
            ]);

        if ($result) {
            return response(Response::Success('修改成功'));
        } else {
            return response(Response::Error(trans('修改失败'), 400));
        }
    }

    public function list(Manager $managerModel)
    {
        $list = $managerModel->select('id', 'manager_name', 'remark', 'status', 'menu', 'created_at')->paginate(10);

        foreach ($list as $value) {
            $value['menu'] = json_decode($value['menu'], true);
        }

        return response(Response::Success($list));
    }

    public function information(Manager $managerModel, MenuService $menuService)
    {
        $list = $managerModel->paginate(10);

        foreach ($list as $value) {
            $value->menu = $menuService->getAdministratorMenu($value->id, $this);
            $value->createTime = date('Y-m-d H:i:s', $value->createTime);
            $value->updateTime = date('Y-m-d H:i:s', $value->updateTime);
        }

        return response(Response::Success($list));
    }
}