<?php

namespace App\Http\Middleware;

use App\Libray\Encryption;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Libray\Response;

class AuthenticationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('token');
        $token = $token?$token:$request->input('token','');

        if(!$token){
            return response(Response::Error(trans('ResponseMsg.CORRUPT_ACCESS_TOKEN'), 80002));
        }

        if (strlen($token) <= 7){
            return response(Response::Error(trans('ResponseMsg.AUTH_BAD_ACCESS_TOKEN'), 80001));
        }

        $Encryption = new Encryption();
        $TokenData = $Encryption->decode($token);
        $TokenData = json_decode($TokenData,true);
        if(!$TokenData['user_id']){
            return response(Response::Error(trans('ResponseMsg.AUTH_BAD_ACCESS_TOKEN'), 80001));
        }

        $TokenTime = Redis::get($token);
        if(!$TokenTime){
            return response(Response::Error(trans('ResponseMsg.EXPIRED_ACCESS_TOKEN'), 80003));
        }

        if($TokenTime != $TokenData['time']){
            return response(Response::Error(trans('ResponseMsg.EXPIRED_ACCESS_TOKEN'), 80003));
        }

        Redis::expire($token,604800);
        define('UID',$TokenData['user_id']);
        return $next($request);
    }
}