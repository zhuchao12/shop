{{-- 购物车 --}}
@extends('layout.main')

@section('content')
    <table class="table table-hover" >
        @foreach($list as $k=>$v)
            <tr>
                <td class="active">{{$v['goods_id']}}</td>
                <td class="success">{{$v['goods_name']}}</td>
                <td class="warning">¥ {{$v['price'] / 100}}</td>
                <td class="danger">{{$v['created_at']}}</td>
                <td class="info"> <li class="btn"> <a href="/cart/del2/{{$v['goods_id']}}" class="del_goods">删除</a></li></td>
            </tr>
        @endforeach
        <hr>
        <a href="/order" id="submit_order" class="btn btn-info "> 提交订单 </a>
    </table>

@endsection

@section('footer')
    @parent
@endsection