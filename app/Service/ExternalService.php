<?php

namespace App\Service;

class ExternalService
{
    public function post($url, $params) {

        $post_data = http_build_query($params);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $post_data,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);

        $res =  file_get_contents($url, false, $context);

        return json_decode($res, true);
    }

    public function parameter($url_args, $fun, $mod, $time, $serverId, $key)
    {
        $sign_args = json_encode($url_args);

        $sign = md5("args={$sign_args}&fun={$fun}&mod={$mod}&sid={$serverId}&time={$time}&key={$key}");

        //组装内容
        return array(
            'args'      => $sign_args,
            'fun'       => $fun,
            'mod'       => $mod,
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );
    }
}
