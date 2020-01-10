<?php

namespace App\Libray;

class Response
{
    const SUCCESS                = 1;
    const PARAM_IS_INVALID       = 10001;
    const PARAM_IS_BLANK         = 10002;
    const PARAM_TYPE_BIND_ERROR  = 10003;
    const PARAM_NOT_COMPLETE     = 10004;
    const USER_NOT_LOGGED_IN     = 20001;
    const USER_LOGIN_ERROR       = 20002;
    const USER_ACCOUNT_FORBIDDEN = 20003;
    const USER_NOT_EXIST         = 20004;
    const USER_HAS_EXISTED       = 20005;
    const SPECIFIED_QUESTIONED_USER_NOT_EXIST = 30001;
    const SYSTEM_INNER_ERROR     = 40001;
    const RESULE_DATA_NONE       = 50001;
    const DATA_IS_WRONG          = 50002;
    const DATA_ALREADY_EXISTED   = 50003;
    const INTERFACE_INNER_INVOKE_ERROR  = 60001;
    const INTERFACE_OUTTER_INVOKE_ERROR = 60002;
    const INTERFACE_FORBID_VISIT        = 60003;
    const INTERFACE_ADDRESS_INVALID     = 60004;
    const INTERFACE_REQUEST_TIMEOUT     = 60005;
    const INTERFACE_EXCEED_LOAD         = 60006;
    const PERMISSION_NO_ACCESS   = 70001;
    const AUTH_BAD_ACCESS_TOKEN  = 80001;
    const CORRUPT_ACCESS_TOKEN   = 80002;
    const EXPIRED_ACCESS_TOKEN   = 80003;
    const ROLE_HAS_EXISTED       = 90001;
    const GIFT_HAS_EXISTED       = 90002;
    const GIFT_CODE_BATCH_HAS_EXISTED       = 90003;
    const SIGN_ERROR             = 90004;
    const WHITE_IP_NOT_FOUND     = 90005;

    static public function Success($Data = []){
        $Res = [
            "Code" => self::SUCCESS,
            "Msg"  => trans("ResponseMsg.SUCCESS"),
            "Data" => $Data
        ];
        return $Res;
    }

    static public function SuccessCallback(){
        $Res = [
            "Code"    => self::SUCCESS,
            "Success" => true,
            "errno"   => 0,
            "Msg"     => '回调成功',
        ];
        return $Res;
    }

    static Public function Error($msg, $code){
        $Res = [
            "Code" => $code,
            "Msg"  => $msg
        ];

        return $Res;
    }

    static Public function ErrorCallback($msg, $code){
        $Res = [
            "Code"  => $code,
            "erron" => 1,
            "Msg"   => $msg
        ];

        return $Res;
    }

    static Public function RequestError($error_code){
        $Res = [
            "code" => 0,
            "error_code"  => $error_code
        ];

        return $Res;
    }

    static Public function RequestMsgError($msg){
        $Res = [
            "code" => 0,
            "msg"  => $msg
        ];

        return $Res;
    }

    static Public function RequestSuccess($error_code, $item){
        $Res = [
            "code"       => 1,
            "error_code" => $error_code,
            "item"       => $item
        ];

        return $Res;
    }

    static Public function RequestMsgSuccess($Data = []){
        $Res = [
            "Code" => 2,
            "Msg"  => '数据为空',
            "Data" => $Data
        ];

        return $Res;
    }

    static Public function DeviceBindError($Msg,$Data=[]){
        $Res = [
            "Code" => self::DeviceBindErrorType,
            "Msg" => $Msg?$Msg:trans("ResponseMsg.Error"),
            "Data" => $Data
        ];

        return json_encode($Res);
    }

    static Public function TokenError($Msg,$Data=[]){
        $Res = [
            "Code" => self::TokenErrorType,
            "Msg" => $Msg?$Msg:trans("ResponseMsg.Error"),
            "Data" => $Data
        ];

        return json_encode($Res);
    }
}