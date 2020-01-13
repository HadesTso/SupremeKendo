<?php

namespace App\Service;

use App\Models\ManagerMenu;
use App\Models\Menu;

class MenuService
{
    public function getAdministratorMenu($manager_id, $game_id)
    {
        $manager_menu = ManagerMenu::where(['manager_id' => $manager_id, 'game_id' => $game_id])->select('menu_id')->get()->toArray();

        $menu = Menu::whereIn('id', $manager_menu)->select('id', 'name', 'pid')->get()->toArray();

        return $this->arr2tree($menu);
    }

    private function arr2tree($tree, $rootId = 0,$level=1) {
        $return = array();
        foreach($tree as $leaf) {
            if($leaf['pid'] == $rootId) {
                $leaf["level"] = $level;
                foreach($tree as $subleaf) {
                    if($subleaf['pid'] == $leaf['id']) {
                        $leaf['children'] = $this->arr2tree($tree, $leaf['id'],$level+1);
                        break;
                    }
                }
                $return[] = $leaf;
            }
        }
        return $return;
    }
}