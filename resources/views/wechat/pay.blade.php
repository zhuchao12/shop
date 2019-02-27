@extends('layout.main')

@section('content')
    <h2 align="center">订单支付</h2>
    <input type="hidden" value="{{$code_url}}" id="code">
    <div id="qrcode" align="center"></div>
@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('/js/qrcode.js')}}"></script>
    <script>
        var code=$('#code').val()
        // 设置参数方式
        var qrcode = new QRCode('qrcode', {
            text:code ,
            width: 200,
            height: 200,
            colorDark : '#000000',
            colorLight : '#ffffff',
            correctLevel : QRCode.CorrectLevel.H
        });
        setInterval(function () {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url:     '/wechat/pay/wxsuccess?order_id='+"{{$order_id}}",
                type:    'get',
                dataType: 'json',
                success:   function (d) {
                    if(d.error == 0){
                        alert(d.msg);
                        location.href = '/order/list'
                    }
                }
            });
        },5000)
        // 使用 API
        qrcode.clear();
        qrcode.makeCode(code);
    </script>
@endsection

