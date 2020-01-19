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

        $manager->manager_name = $name;
        $manager->remark = $remark;
        $manager->status = $status;
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
        $game_id = $request->input('game');
        $menu    = $request->input('menuList', null);

        DB::beginTransaction();
        try{
            $manager->where(['id' => $id])
                ->update([
                    'manager_name'  => $name,
                    'remark'        => $remark,
                    'status'        => $status,
                    'updated_at'    => date('Y-m-d H:i:s', time()),
                ]);

            ManagerMenu::where(['manager_id' => $id, 'game_id' => $game_id])->delete();

            if ($menu && $game_id) {
                $menuArray = array();
                foreach ($menu as $value) {
                    $valueBase = json_decode($value, true);
                    $menuArray[] = $valueBase['id'];
                    if (isset($valueBase['children'])) {
                        foreach ($valueBase['children'] as $val) {
                            $menuArray[] = $val['id'];
                            if (isset($val['children'])) {
                                foreach ($val['children'] as $v) {
                                    $menuArray[] = $v['id'];
                                }
                            }
                        }
                    }
                }

                foreach ($menuArray as $value) {
                    ManagerMenu::create(['manager_id' => $id, 'menu_id' => $value, 'game_id' => $game_id]);
                }
            }
            DB::commit();
            return response(Response::Success('修改成功'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return response(Response::Error(trans("修改失败"), 400));
        }
    }

    public function list(Manager $managerModel)
    {
        $list = $managerModel->select('id', 'manager_name', 'remark', 'status', 'created_at')->paginate(10);

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