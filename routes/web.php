<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::any('/adduser','User\UserController@add');

//路由跳转
Route::redirect('/hello1','/world1',301);
Route::any('/world1','Test\TestController@world1');

Route::any('hello2','Test\TestController@hello2');
Route::any('world2','Test\TestController@world2');


//路由参数
Route::any('/user/test','User\UserController@test');
Route::any('/user/{uid}','User\UserController@user');
Route::any('/month/{m}/date/{d}','Test\TestController@md');
Route::any('/name/{str?}','Test\TestController@showName');



// View视图路由
Route::view('/mvc','mvc');
Route::view('/error','error',['code'=>40300]);


// Query Builder
Route::any('/query/get','Test\TestController@query1');
Route::any('/query/where','Test\TestController@query2');


//Route::match(['get','post'],'/test/abc','Test\TestController@abc');
Route::any('/test/abc','Test\TestController@abc');


Route::any('/view/test1','Test\TestController@viewTest1');
Route::any('/view/test2','Test\TestController@viewTest2');
Route::any('/check_cookie','Test\TestController@checkCookie')->middleware('check.cookie');//中间价测试


//用户注册
Route::any('/userreg','User\UserController@reg');
Route::any('/regadd','User\UserController@regadd');


//用户登录
Route::any('/login','User\UserController@login');
Route::any('/loginadd','User\UserController@loginadd');

//个人中心
Route::any('/center','User\UserController@center');
//购物车
Route::any('/cart','Cart\CartController@index');
Route::any('/cart/add/{goods_id}','Cart\CartController@add');  //商品添加
Route::any('/cart/add2/','Cart\CartController@add2');      //添加商品
Route::any('/cart/del/{goods_id}','Cart\CartController@del');  //删除商品
Route::any('/cart/del2/{goods_id}','Cart\CartController@del2');  //删除商品

//商品
Route::any('/goods/{goods_id}','Goods\GoodsController@goods') ;  //商品详情

Route::any('/goods2/list','Goods\GoodsController@goods2');  //商品展示

//订单
Route::any('/order','Order\OrderController@add')  ;  //订单
Route::any('/order/list','Order\OrderController@order')  ;  //订单列表

//支付
Route::any('/pay/{order_id}','Pay\PayController@pay');
Route::any('/pay/list/{order_id}','Pay\PayController@pay2');
Route::any('/alipay',' Alipay\alipayController@alipay');

Route::any('/alipay/{order_id}','Pay\AlipayController@pay');
Route::post('/alipay2/notify','Pay\AlipayController@aliNotify');        //支付宝支付 异步通知回调
Route::get('/alipay2/return','Pay\AlipayController@aliReturn');        //支付宝支付 同步通知回调

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/upload','Upload\UploadController@upload');
Route::post('/upload/pdf','Upload\UploadController@uploads');

//在线订座
Route::any('/movie','Movie\MovieController@movie');
Route::any('/movie/buy/{pos}','Movie\MovieController@buy');




//考试登录
Route::any('/klogin','Login\LoginController@klogin');  //考试登录
Route::any('/kloginadd','Login\LoginController@kloginadd');
Route::any('/pwd','Login\LoginController@pwd');
Route::any('/pwd2','Login\LoginController@pwd2');



//微信
Route::get('/wechat/valid','Wechat\WechatController@wechatOne');
Route::get('/wechat/test','Wechat\WechatController@test');
Route::get('/wechat/valid1','Wechat\WechatController@validToken1');
Route::post('/wechat/valid1','Wechat\WechatController@wxEvent');        //接收微信服务器事件推送
Route::post('/wechat/valid','Wechat\WechatController@validToken');
Route::get('/wechat/qun','Wechat\WechatController@all');
Route::get('','Wechat\WechatController@createMenu');     //创建菜单/wechat/create_menu


Route::get('/form/show','Wechat\WechatController@formShow');     //表单测试
Route::post('/form/test','Wechat\WechatController@formTest');     //表单测试


Route::get('/Wechat/material/list','Wechat\WechatController@materialList');     //获取永久素材列表
Route::get('/Wechat/material/upload','Wechat\WechatController@upMaterial');     //上传永久素材
Route::post('/Wechat/material','Wechat\WechatController@materialTest');     //创建菜单

//微信聊天
Route::get('/Wechat/kefu/chat/{id}','Wechat\WechatController@chatView');     //客服聊天
Route::get('/weixin/chat/get_msg','Wechat\WechatController@getChatMsg');     //获取用户聊天信息
Route::get('/weixin/chat/get_msgs','Wechat\WechatController@getChatMsgs');     //获取用户聊天信息


//微信支付
Route::get('/wechat/pay/test/{order_sn}','Wechat\PayController@test');     //微信支付测试
Route::post('/wechat/pay/notice','Wechat\PayController@notice');     //微信支付通知回调
Route::get('/wechat/pay/wxsuccess','Wechat\PayController@WxSuccess');     //微信支付通知回调

Route::get('/wechat/login','Wechat\WechatController@login');     //微信支付通知回调
Route::get('/wechat/getcode','Wechat\WechatController@getCode');        //接收code

Route::get('/wechat/jssdk','Wechat\WechatController@jssdk');        //接收code


Route::get('/chat','Wechat\WechatController@chat');        //接收code


Route::any('/test/ccc','Test\TestController@ccc');   //加密测试

Route::any('/test/ddd','Test\TestController@ddd');   //加密测试


Route::any('/test/hb','Test\TestController@hb');   //HB测试








