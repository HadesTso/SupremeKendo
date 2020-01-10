<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::any('login', 'LoginController@index');

Route::any('get/cast', 'AjaxController@getCast');
Route::any('role/gift', 'AjaxController@giftUseCheck');
Route::any('new/role', 'GameController@giftRoleGift');
Route::any('white/ip/check', 'AjaxController@whiteIpCheck');
Route::any('gift/info/excel', 'Upload\ExcelController@giftInfoExcel');
Route::any('exclude/repeat', 'AjaxController@ExcludeRepeat');
Route::any('device/activation', 'AjaxController@DeviceActivation');
Route::any('check/activation', 'AjaxController@checkActivation');
Route::any('callback', 'AjaxController@callback');
Route::any('role/data', 'DataController@roleData');

Route::post('time/tack1', 'GameController@timeTack');

Route::group(['middleware' => 'AuthToken', 'prefix' => 'auth'], function (){

    Route::post('manager/add', 'ManagerController@store');
    Route::patch('manager/{id}', 'ManagerController@update');
    Route::any('manager/list', 'ManagerController@managerList');
    Route::post('manager/{id}', 'ManagerController@save');

    Route::get('manager/getList', 'DedicineController@getManagerList');
    Route::get('channel/getList', 'DedicineController@getChannelList');
    Route::get('server/getList', 'DedicineController@getServerList');
    Route::get('goods/getList', 'DedicineController@getGoodsList');
    Route::get('codebox/getList', 'DedicineController@getGiftDeployList');
    Route::get('codebatch/getList', 'DedicineController@getCodeBatchList');
    Route::get('carte/getList', 'DedicineController@getCarteList');
    Route::get('menu/getList', 'DedicineController@getMenuList');

    Route::any('carte/list', 'CarteController@carteList');
    Route::post('carte/add', 'CarteController@store');

    Route::post('account/add', 'AccountController@store');
    Route::any('account/list', 'AccountController@accountList');
    Route::patch('account/{id}', 'AccountController@update');
    Route::post('account/{id}', 'AccountController@save');
    Route::get('info', 'AccountController@accountInfo');

    Route::post('send/mail', 'GMController@sendMail');
    Route::post('send/solo/mail', 'GMController@sendSoloMail');
    Route::any('send/mail/list', 'GMController@sendMailList');

    Route::post('roles/gift/store', 'GMController@newRolesGiftStore');
    Route::any('roles/gift/list', 'GMController@newRolesGiftList');
    Route::patch('roles/gift/{id}', 'GMController@newRolesGiftUpdate');

    Route::post('ban/login', 'GMController@banLogin');

    Route::any('login/notice/list', 'GMController@loginBulletinList');
    Route::post('login/notice', 'GMController@loginBulletinStore');
    Route::post('login/bulletin/{id}', 'GMController@loginBulletinUpdate');

    Route::any('gift/deploy/list', 'GMController@GiftConfigurationList');
    /*Route::any('gift/configuration/list', 'GMController@GiftConfigurationList');*/
    Route::post('gift/deploy', 'GMController@GiftConfigurationStore');
    Route::post('gift/deploy/{id}', 'GMController@GiftConfigurationUpdate');

    Route::post('gift/info/excel', 'Upload/ExcelController@giftInfoExcel');


    Route::post('gift/code/batch', 'GMController@codeBatchStore');
    Route::any('code/batch/list', 'GMController@codeBatchList');
    Route::post('code/batch/{id}', 'GMController@codeBatchUpdate');

    Route::post('gift/code', 'GMController@giftCodeStore');
    Route::any('code/list', 'GMController@giftCodeList');

    Route::post('white/ip', 'GMController@whiteIpStore');
    Route::any('white/ip/list', 'GMController@whiteIpList');
    Route::post('white/ip/{id}', 'GMController@whiteIpUpdate');
    Route::any('broadcast/list', 'GMController@BroadcastList');
    Route::post('broadcast', 'GMController@BroadcastStore');
    Route::post('broadcast/{id}', 'GMController@BroadcastUpdate');
    Route::any('closure/ip/list', 'GMController@ClosureIpList');
    Route::any('announcement/list', 'GMController@AnnouncementList');
    Route::post('announcement', 'GMController@AnnouncementStore');

    Route::any('role/list', 'DataController@roleList');
    Route::any('wife/list', 'DataController@wifeList');
    Route::any('child/list', 'DataController@childList');
    Route::any('role/stream/list', 'DataController@roleStreamList');
    Route::any('resource/list', 'DataController@resourceList');
    Route::any('chat/list', 'DataController@chatList');
    Route::any('real/time/chat', 'DataController@RealTimeChat');
    Route::any('order/list', 'DataController@OrderList');
    Route::any('item/list', 'DataController@itemList');

    Route::post('ban/chat', 'GameController@banChat');
    Route::post('recharge', 'GameController@recharge');
    Route::post('time/tack', 'GameController@timeTack');
    Route::post('send/prop', 'GameController@sendProp');
    Route::post('unlock/ip', 'GameController@unlockIp');
    Route::post('closure/ip', 'GameController@closureIp');
    Route::post('open/suit', 'GameController@openSuit');
    Route::post('close/suit', 'GameController@closeSuit');
    Route::post('server/data', 'GameController@ServerData');
    Route::post('send/marquee', 'GameController@sendMarquee');
    Route::post('cancel/marquee', 'GameController@cancelMarquee');
    Route::post('chat/announcement', 'GameController@chatAnnouncement');

    Route::any('server/list', 'ServerController@index');
    Route::post('server/{id}', 'ServerController@update');
    Route::post('server/id', 'ServerController@edit');
    Route::post('server', 'ServerController@store');


    Route::any('channel/list', 'ChannelController@index');
    Route::post('channel', 'ChannelController@store');
    Route::post('channel/{id}', 'ChannelController@update');

});