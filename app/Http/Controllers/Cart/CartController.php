<?php

namespace App\Http\Controllers\Cart;

use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{

    public function __construct()
    {
/*
        $this->middleware(function($request,$next){
            //验证是否有登录token
            if(!session()->exists('token')){
                header('Refresh:2;url=/login');
                echo '请先登录';
                exit();
            }
            return $next($request);
        });
*/

    }

    public function index(Request $request)
    {
        $goods = session()->get('cart_goods');
     //   var_dump($goods);exit;
        if(empty($goods)){
            echo '购物车是空的';
        }else{
            foreach($goods as $k=>$v){
                echo 'Goods ID: '.$v;echo '</br>';
                $detail = GoodsModel::where(['goods_id'=>$v])->first()->toArray();
                echo '<pre>';print_r($detail);echo '</pre>';
            }
        }
    }

    /**
     * 添加商品
     */
    public function add($goods_id){
        $goods = session()->get('cart_goods');
        //商品是否已存在
        if(!empty($cart_goods)){
            if(in_array($cart_goods,$goods_id)){
                echo '该商品已存在';
                exit;
            }
           // var_dump($goods_id);
       //     exit;
        }

        //减库存
        session()->push('cart_goods',$goods_id);
        $where = [
            'goods_id'=>$goods_id
        ];
        $res = GoodsModel::where($where)->value('goods_store');
        if($res<=0){
            echo '商品库存不足';
            exit;
        }
        $result = GoodsModel::where($where)->decrement('goods_store');
        if($result){
                echo '添加成功';
        }
    }

    public function  del($goods_id){
        $goods = session()->get('cart_goods');
        if(in_array($goods_id,$goods)){
                //删除
            foreach($goods as $k=>$v){
                if($goods_id == $v){
                    session()->pull('cart_goods.'.$k);
                }
            }
            }else{
                exit('商品不在购物车中');
        }
    }

    /**
     * 添加到购物车
     */
    public function add2(Request $request){
            $goods = $request->input('goods_id');
            $num = $request->input('num');
            $where = [
                'goods_id'=>$goods,
            ];
            //检查库存
            $res = GoodsModel::where($where)->value('store');
            if($res<=0){
                $response = [
                    'errno'=>5001,
                    'msg'=>'库存不足',
                ];
                return $response;
            }

            //购物车添加
            $data = [
                'goods_id'=>$goods,
                'num'=>$num,
                'add_time'=>time(),
                'uid'=>session()->get('uid'),
                'session_token'=>session()->get('u_token'),
            ];

    }
}

