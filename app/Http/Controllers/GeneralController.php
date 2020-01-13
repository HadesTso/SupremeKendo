<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Account;
use App\Models\Carte;
use App\Models\Channel;
use App\Models\CodeBatch;
use App\Models\CodeBox;
use App\Models\Games;
use App\Models\Good;
use App\Models\Manager;
use App\Models\Server;

class GeneralController extends Controller
{
    public function getGame(Games $games)
    {
        $list = $games->select('id', 'game_name')->get();

        return response(Response::Success($list));
    }
}