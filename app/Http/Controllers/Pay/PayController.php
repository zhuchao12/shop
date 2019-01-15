<?php

namespace App\Http\Controllers\Pay;


use App\Http\Controllers\Controller;

use App\Model\OrderModel;

class PayController extends Controller
{
    public function pay($order_id){
        //查看订单
        $where = [
            'order_id'=>$order_id,
        ];
        $info = OrderModel::where($where)->first();
        //判断订单存不存在
        if(!$info){
            echo '订单不存在';
        }
        //查看订单状态
        if($info->pay_time>0){
            echo '订单已被支付';
            exit;
        }
        //支付成功 修改支付时间
        OrderModel::where(['order_id'=>$order_id])->update(['pay_time'=>time(),'pay_amount'=>rand(1111,9999),'is_pay'=>1]);
        header('Refresh:2;url=/order/list');
        echo '支付成功，正在跳转';
    }


    public function pay2($order_id){
        $res = OrderModel::where(['order_id'=>$order_id])->first();

        return view('order.pay',$res);
    }
}