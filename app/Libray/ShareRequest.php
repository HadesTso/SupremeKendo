<?php

namespace App\Libray;

class ShareRequest
{
    static public function http_post($url, $params) {

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

    static public function codeTransform($code)
    {
        $str = strlen($code);
        $strCode = '';
        for ($i=0; $i < $str ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $code[$i])){
                $strCode .= urlencode($code[$i]);
            }else{
                $strCode .= $code[$i];
            }
        }

        return $strConversion;
    }

    static public function Transform($code)
    {
        $str = strlen($code);
        $strCode = '';
        for ($i=0; $i < $str ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $code[$i])){
                $strCode .= urlencode($code[$i]);
            }else{
                if ($code[$i] == '='){
                    $strCode .= urlencode($code[$i]);
                }else{
                    $strCode .= $code[$i];
                }
            }
        }

        return $strCode;
    }
}