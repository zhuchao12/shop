@extends('layout.main')

@section('content')
    <div class="container">
        <h2>聊天室</h2>
        <h3>
            <a href="http://socket.com">聊天室</a>
        </h3>
    </div>
@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('/js/wechat/chat.js')}}"></script>
@endsection