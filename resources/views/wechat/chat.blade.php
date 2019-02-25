@extends('layout.main')

@section('title')    @endsection

@section('header')
    @parent
@endsection

@section('content')
    <h1>客服聊天:<i style="color:red">openid:{{$openid}}</i></h1>
    <div style="border:6px yellow solid; width: 600px;" id="chat_div"></div>
    <br>
    <form action="">
        <input type="hidden" value="{{$openid}}" id="openid">

        <input type="hidden" value="1" id="msg_pos">                <!--上次聊天位置-->
        <input type="text" id="send_msg">
        <input type="submit" value="发送" id="send_msg_btn">
    </form>
@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('/js/wechat/chat.js')}}"></script>
@endsection