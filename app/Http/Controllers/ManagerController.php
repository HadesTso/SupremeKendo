<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Manager;
use App\Service\MenuService;
use Illuminate\Http\Request;
use DB;

class ManagerController extends Controller
{
    public function store(Request $request)
    {
        $name    = $request->input('name');
        $remark  = $request->input('remark');
        $game_id = $request->input('game_id');
        $menu    = $request->input('menuList', null);

        DB::beginTransaction();
        try{
            $managerArr = array(
                'name'       => $name,
                'remark'     => $remark,
                'status'     => 0,
                'createTime' => time(),
                'updateTime' => time(),
            );
            $id = Manager::insertGetId($managerArr);

            if ($menu) {
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
                    ManagerMenu::create(['manager_id' => $id, 'menu_id' => $value]);
                }
            }
            DB::commit();
            return response(Response::Success('新增成功'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return response(Response::Error(trans("新增失败")));
        }

    }

    public function update(Request $request, Manager $managerModel)
    {
        $data = $request->all();

        if (!$managerModel->where(['id' => $data['id']])->update(['status' => $data['status']])){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }

    public function modification(Request $request)
    {
        $id      = $request->input('id');
        $name    = $request->input('name');
        $remark  = $request->input('remark');
        $status  = $request->input('status');
        $game_id = $request->input('game_id');
        $menu    = $request->input('menuList', null);

        DB::beginTransaction();
        try{
            $managerModel->where(['id' => $id])
                ->update([
                    'name'       => $name,
                    'remark'     => $remark,
                    'status'     => $status,
                    'updateTime' => time(),
                ]);
            ManagerMenu::where(['manager_id' => $id, 'game_id' => $game_id])->delete();

            if ($menu) {
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
            return response(Response::Error(trans("修改失败")));
        }
    }

    public function list(Manager $managerModel)
    {
        $list = $managerModel->select('id', 'name', 'remark', 'status', 'created_time')->paginate(10);

        foreach ($list as $value) {
            $value->createTime = date('Y-m-d H:i:s', $value->createTime);
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