@extends('layout.main')

@section('content')
    <div class="container">
        <h2>微信登录</h2>
        <h3>
            <a href="{{$url}}">Login</a>
            <a href="http://socket.com">聊天室</a>
        </h3>
    </div>
@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('/js/wechat/chat.js')}}"></script>
@endsection