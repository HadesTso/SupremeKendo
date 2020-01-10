<?php

namespace App\Libray;

class RequestTool
{
    static public function send_post($url, $params) {

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
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    static public function ChineseConversion($chinese)
    {
        $str = strlen($chinese);
        $strConversion = '';
        for ($i=0; $i < $str ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $chinese[$i])){
                $strConversion .= urlencode($chinese[$i]);
            }else{
                $strConversion .= $chinese[$i];
            }
        }

        return $strConversion;
    }

    static public function conversion($chinese)
    {
        $str = strlen($chinese);
        $strConversion = '';
        for ($i=0; $i < $str ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $chinese[$i])){
                $strConversion .= urlencode($chinese[$i]);
            }else{
                if ($chinese[$i] == '='){
                    $strConversion .= urlencode($chinese[$i]);
                }else{
                    $strConversion .= $chinese[$i];
                }
            }
        }

        return $strConversion;
    }
}