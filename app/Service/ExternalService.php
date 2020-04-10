<?php

namespace App\Service;

class ExternalService
{
    public function post($url , $data=array()){

        $ch = curl_init();

        $post_data = http_build_query($data);//重点

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));//重点

        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $output = curl_exec($ch);

        curl_close($ch);

        return json_decode($output, true);

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

    public function sinogram($text)
    {
        $strLength = strlen($text);
        $sinogram = '';
        for ($i=0; $i < $strLength ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $text[$i])){
                $sinogram .= urlencode($text[$i]);
            }else{
                $sinogram .= $text[$i];
            }
        }
        return $sinogram;
    }
}
