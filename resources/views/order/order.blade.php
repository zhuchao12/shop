@extends('layout.main')

@section('content')
    <table class="table table-hover" >
        @foreach($list as $k=>$v)
            <tr>
                <td class="active">{{$v['order_id']}}</td>
                <td class="success">{{$v['order_sn']}}</td>
                <td class="warning">¥ {{$v['order_amount']}}</td>
                <td class="danger">{{date('Y-m-d H:i:s',$v['add_time'])}}</td>

                <td class="info"> <li class="btn"> <a href="/cart/del2/{{$v['goods_id']}}" class="del_goods">删除</a></li></td>
            </tr>
        @endforeach
        <hr>
        <a href="/order" id="submit_order" class="btn btn-info "> 删除订单 </a>
    </table>

@endsection

@section('footer')
    @parent
@endsection