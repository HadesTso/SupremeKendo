<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Menu;
use App\Service\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function list(MenuService $menuService)
    {
        $list = $menuService->getAdministratorMenu(0, 0);

        return response(Response::Success($list));
    }

    public function getMenus(Request $request, MenuService $menuService)
    {
        $game_id    = $request->input('game_id');
        $manager_id = $request->input('manager_id');

        $menu = $menuService->getAdministratorMenu($manager_id, $game_id);

        return response(Response::Success($menu));
    }
}