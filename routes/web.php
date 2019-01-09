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

Route::get('/adduser','User\UserController@add');

//路由跳转
Route::redirect('/hello1','/world1',301);
Route::get('/world1','Test\TestController@world1');

Route::get('hello2','Test\TestController@hello2');
Route::get('world2','Test\TestController@world2');


//路由参数
Route::get('/user/test','User\UserController@test');
Route::get('/user/{uid}','User\UserController@user');
Route::get('/month/{m}/date/{d}','Test\TestController@md');
Route::get('/name/{str?}','Test\TestController@showName');



// View视图路由
Route::view('/mvc','mvc');
Route::view('/error','error',['code'=>40300]);


// Query Builder
Route::get('/query/get','Test\TestController@query1');
Route::get('/query/where','Test\TestController@query2');


//Route::match(['get','post'],'/test/abc','Test\TestController@abc');
Route::any('/test/abc','Test\TestController@abc');


Route::get('/view/test1','Test\TestController@viewTest1');
Route::get('/view/test2','Test\TestController@viewTest2');
Route::get('/check_cookie','Test\TestController@checkCookie')->middleware('check.cookie');//中间价测试


//用户注册
Route::get('/userreg','User\UserController@reg');
Route::post('/regadd','User\UserController@regadd');


//用户登录
Route::get('/login','User\UserController@login');
Route::post('/loginadd','User\UserController@loginadd');

//个人中心
Route::get('/center','User\UserController@center');
//购物车
Route::get('/cart','Cart\CartController@index')->middleware('check.login.token');
Route::get('/cart/add/{goods_id}','Cart\CartController@add')->middleware('check.login.token');  //商品添加
Route::post('/cart/add2','Cart\IndexController@add2')->middleware('check.login.token');      //添加商品
Route::get('/cart/del/{goods_id}','Cart\CartController@del')->middleware('check.login.token');  //商品添加

//商品
Route::get('/goods/{goods_id}','Goods\GoodsController@goods');  //商品详情



