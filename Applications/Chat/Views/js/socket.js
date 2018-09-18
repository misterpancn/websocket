/**
 * Created by buck on 2018/9/14.
 */
var socket;
var user_id = $('#user_id').val();
var token = $("#token").val();
function connectWS() {
    socket = new WebSocket('ws://' + document.domain + ':9526');
    socket.onopen = onopen;
    socket.onmessage = onmessage;
    socket.onerror = socket_error;
    socket.onclose = socket_close;
}
function onopen() {
    var send = '{"type":"login","uid":"' + user_id + '","token":"' + token + '"}';
    console.log('连接服务器成功');
    socket.send(send);
}
function onmessage(mes) {
    var data = eval("(" + mes.data + ")");
    switch (data['type']) {
        case 'msg':
            if (data.uid == user_id) {
                say(data, 1);
            } else {
                say(data, 0);
            }
            console.log(data);
            break;
        case 'ping':
            break;
        case 'login':
            //say(data,0);
            console.log(data);
            renderer_data(data.all_user); // 刷新用户列表
            break;
        case 'logout':
            var timer = setTimeout(function () {
                say(data, 0)
            }, 5000);
            setTimeout(function () {
                $.ajax({
                    type: 'post',
                    url: '?action=refresh_close',
                    data: {'uid': data.uid},
                    success: function (msg) {
                        if (msg == 'is_online') {
                            clearTimeout(timer);
                            renderer_data(data.all_user); // 刷新用户列表
                        } else {
                            renderer_data(data.all_user); // 刷新用户列表
                        }
                        console.log(msg);
                    }
                })
            }, 1000)
            break;
        default :
            console.log(data);
            break;
    }
}
connectWS();
function sendMessage(mes) {
    if (mes) {
        mes=mes.replace(/[\r\n]/i,"<br>");
        var data = '{"type":"message","content":"' + mes + '"}';
        socket.send(data);
    }
}
function socket_error() {
    console.log('服务器连接出错，定时重连......');
    setTimeout(connectWS, 5000);
}
function socket_close() {
    console.log('服务器连接已断开，定时重连......');
    setTimeout(connectWS, 5000);
}
$('.logout').on('click', function () {
    $.ajax({
        type: 'post',
        url: 'login.php?action=logout',
        data: {'uid': user_id},
        success: function (msg) {
            if (msg == 'success') {
                socket.send('{"type":"logout"}')
                location.reload();
            }
        }
    })
})

function say(content, type) {
    var classes = '';
    if (type == 1) {
        classes = ' am-comment-flip am-comment-success';
    }
    var html = '<li class="am-comment ' + classes + '">' +
        '<a href="#link-to-user-home">' +
        '<img src="img/Starry.jpg" alt="" class="am-comment-avatar" width="48" height="48"/>' +
        '</a>' +
        '<div class="am-comment-main">' +
        '<header class="am-comment-hd">' +
        '<div class="am-comment-meta">' +
        '<a href="#link-to-user" class="am-comment-author">' + (content.user_name ? content.user_name : '系统消息') + '</a>' +
        '   <time datetime="' + content.time + '" title="' + content.time + '">' + content.time + '</time>' +
        '</div>' +
        '</header>' +
        '<div class="am-comment-bd">' + content.content + '</div></div></li>';
    $('.chat-content').append(html);
    $(".am-cs-mess").smoothScroll({position: $(".am-cs-mess")[0].scrollHeight});
    $('#message').val('');
}
$('.send-message').on('click', function () {
    var msg = $('#message').val();
    //alert(msg);
    if (msg != '' && msg != null) {
        sendMessage(msg);
    } else {
        var a = $(this), i = "am-animation-" + a.data("docAnimation");
        a.data("animation-idle") && clearTimeout(a.data("animation-idle")), a.removeClass(i), setTimeout(function () {
            a.addClass(i), a.data("animation-idle", setTimeout(function () {
                a.removeClass(i), a.data("animation-idle", !1)
            }, 500))
        }, 50)
    }
})

function renderer_data(data) {
    var html = '';
    for (i = 0; i < data.length; i++) {
        html += '<li class="am-text-truncate">' + data[i].user_name + ' ' + data[i].city + '</li>';
    }
    $('.user-list').html(html);
}
$(".am-cs-show-user").on('click', function () {
    $("#am-cs-user").offCanvas($(this).data('rel'));
})