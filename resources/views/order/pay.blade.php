@extends('layout.main')

@section('content')
    <table class="table table-hover" >
        <tr class="active">
            <td class="info">订单id</td>
            <td class="info"> 订单总价</td>
            <td class="info"> 订单时间</td>

        </tr>

            <tr>
                <td class="success">{{$order_id}}</td>
                <td class="success">{{$order_amount}}</td>
                <td class="danger">{{date('Y-m-d H:i:s',$pay_time)}}</td>
            </tr>
        <hr>
    </table>
    <a href="/wechat/pay/test/{{$order_id}}" class="btn btn-primary btn-lg" style="width:940px; ">去支付</a><br><br>

@endsection

@section('footer')
    @parent
@endsection