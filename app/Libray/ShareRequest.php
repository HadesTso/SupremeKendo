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

    public static function curl_post($url , $data=array()){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据

        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;

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

        return $strCode;
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