@extends('layout.main')

@section('content')
    <table class="table table-hover" >
        <tr class="active">
            <td class="info">订单id</td>
            <td class="info"> 订单号</td>
            <td class="info">价格</td>
            <td class="info">下单时间</td>
            <td class="info">订单状态</td>
            <td class="info">操作</td>
        </tr>
        @foreach($list as $k=>$v)
            <tr>
                <td class="success">{{$v['order_id']}}</td>
                <td class="warning">{{$v['order_sn']}}</td>
                <td class="success">¥ {{$v['order_amount']}}</td>
                <td class="danger">{{date('Y-m-d H:i:s',$v['add_time'])}}</td>
                <td class="success">
                    <li class="btn">
                        @if($v['is_pay']==1)
                        <a href="/pay/list/{{$v['order_id']}}">付款</a>
                            @elseif($v['is_pay']==2)
                            已付款
                            @endif
                    </li>
                </td>
                <td class="danger">删除</td>

            </tr>
        @endforeach
        <hr>
    </table>
   <!--<a href="/pay{{$v['oid']}}" class="btn btn-primary btn-lg" style="width:940px; ">去支付</a><br><br> -->

@endsection

@section('footer')
    @parent
@endsection