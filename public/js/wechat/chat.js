var openid = $("#openid").val();

setInterval(function(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url     :   '/weixin/chat/get_msg?openid=' + openid + '&pos=' + $("#msg_pos").val(),
        type    :   'get',
        dataType:   'json',
        success :   function(d){
            if(d.errno==0){     //服务器响应正常
                //数据填充
                var msg_str = '<blockquote>' + d.data.add_time +
                    '<p>' + d.data.msg + '</p>' +
                    '</blockquote>';

                $("#chat_div").append(msg_str);
                $("#msg_pos").val(d.data.id)
            }else{

            }
        }
    });
},1000);

//客服发送消息
$('#send_msg_btn').click(function (e) {
    e.preventDefault();
    var send_msg = $('#send_msg').val().trim();
    console.log(send_msg);
    //console.log(message);
    //$("#chat_div").append(msg_str);
    $('#send_msg').val('');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '/chat/msg',
        type: 'post',
        data: {openid: openid, msg: send_msg},
        dataType: 'json',
        success: function (a) {
            if (a.errcode == 0) {
                alert('发送成功');
            } else {
                alert('发送失败');
            }
        }
    })
})
