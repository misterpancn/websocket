/**
 * Created by buck on 2018/9/14.
 */
var socket;
var user_id = $('#user_id').val();
var token = $("#token").val();
var first = true;
var E = window.wangEditor;
var editor = new E("#editor");
// 自定义菜单配置
editor.customConfig.menus = [
    'emoticon',  // 表情
//    'head',  // 标题
    'bold',  // 粗体
    'fontSize',  // 字号
    'fontName',  // 字体
    'italic',  // 斜体
//    'underline',  // 下划线
//    'strikeThrough',  // 删除线
    'foreColor',  // 文字颜色
//    'backColor',  // 背景颜色
//    'link',  // 插入链接
//    'list',  // 列表
//    'justify',  // 对齐方式
//    'quote',  // 引用
    'image',  // 插入图片
//    'table',  // 表格
//    'video',  // 插入视频
//    'code',  // 插入代码
//    'undo',  // 撤销
//    'redo'  // 重复
];
//  获取新浪的表情包
var sina_emoji = new Array();
$.ajax({
    type: 'GET',
    url: 'https://api.weibo.com/2/emotions.json?source=1362404091',
    dataType: "jsonp",
    jsonp: "callback",
    success: function (mes) {
        if (mes.data.length > 0) {
            for (i = 0; i < mes.data.length; i++) {
                var obj = {alt: mes.data[i].phrase, src: mes.data[i].icon};
                sina_emoji.push(obj);
            }
        }
    }
})
// 表情面板可以有多个 tab ，因此要配置成一个数组。数组每个元素代表一个 tab 的配置
editor.customConfig.emotions = [
    /*{
     // tab 的标题
     title: '默认',
     // type -> 'emoji' / 'image'
     type: 'image',
     // content -> 数组
     content: [
     {
     alt: '[坏笑]',
     src: 'http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/50/pcmoren_huaixiao_org.png'
     },
     {
     alt: '[舔屏]',
     src: 'http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/40/pcmoren_tian_org.png'
     }
     ]
     },*/
    {
        // tab 的标题
        title: '新浪',
        // type -> 'emoji' / 'image'
        type: 'image',
        // content -> 数组
        content: sina_emoji
    }
]
editor.create();
$("#editor").children('.w-e-text-container').css({'height': '104px', 'max-height': '150px'});
function connectWS() {
    socket = new WebSocket('ws://' + document.domain + ':9526');
    socket.onopen = onopensocket;
    socket.onmessage = onmessage;
    socket.onerror = socket_error;
    socket.onclose = socket_close;
}
function onopensocket() {
    var send = '{"type":"login","uid":"' + user_id + '","token":"' + token + '"}';
    console.log('连接服务器成功');
    socket.send(send);
}
function onmessage(mes) {
    if (mes.data.length == 0 || mes.data == '') {
        return false;
    }
    var data = eval("(" + mes.data + ")");
    switch (data['type']) {
        case 'msg':
            if (data.from_uid == user_id) {
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
            renderer_data(data); // 刷新用户列表
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
                            renderer_data(data); // 刷新用户列表
                        } else {
                            renderer_data(data); // 刷新用户列表
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
if (first) {
    connectWS();
    first = false;
}
function sendMessage(mes) {
    if (mes) {
        mes = mes.replace(/[\r\n]/i, "<br>");
        mes = mes.replace(/"/g, '\\"');
        var group = $('#group_id').val();
        var uid = $("#chat_id").val();
        var data = '{"type":"message","content":"' + mes + '","group":"' + group + '","send_to_uid":"' + uid + '","uid":"' + user_id + '"}';
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
    $('#logout-confirm').modal({
        relatedTarget: this,
        onConfirm: function (options) {
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
        },
        // closeOnConfirm: false,
        onCancel: function () {

        }
    });
})

function say(content, type) {
    var classes = ' am-cs-friend';
    var attr = 'data-id="' + content.from_uid + '" data-un="' + content.user_name + '"';
    var at = attr;
    var fnc = 'onclick="select_user(this)"';
    if (type == 1) {
        classes = ' am-comment-flip am-cs-self';
        fnc = '';
        attr += ' data-am-modal="{target: \'#my-alert\'}"';
    }
    if (content.send_to_uid > 0) {
        attr = fnc = '';
    }
    var self_dom = $('.chat-content-' + (content.send_to_uid != "" && content.send_to_uid != null && content.send_to_uid > 0 ? content.send_to_uid : content.send_to_group));
    var friend_dom = $('.chat-content-' + content.from_uid);
    var lasttime = self_dom.children('li:last').find('time').attr('datetime');
    var fri_lasttime = friend_dom.children('li:last').find('time').attr('datetime');
    var unix_lasttime = new Date(lasttime).getTime();
    var unix_currtime = new Date(content.time).getTime();
    var fri_unix_lasttime = new Date(fri_lasttime).getTime();
    var self_is_create = true;
    var fri_is_create = true;
    if (unix_currtime - unix_lasttime <= 1000 * 120 && self_dom.children('li:last').attr('data-id') == content.from_uid && self_dom.children('li:last') != undefined) {
        self_is_create = false;
    }
    if (unix_currtime - fri_unix_lasttime <= 1000 * 120 && friend_dom.children('li:last').attr('data-id') == content.from_uid && friend_dom.children('li:last') != undefined) {
        fri_is_create = false;
    }
    var html = '<li class="am-comment am-cs-mess-list' + classes + '" ' + at + '>' +
        '<a href="#link-to-user-home" ' + attr + fnc + '>' +
        '<img src="img/Starry.jpg" alt="" class="am-comment-avatar" width="48" height="48"/>' +
        '</a>' +
        '<div class="am-comment-main">' +
        '<header class="am-comment-hd">' +
        '<div class="am-comment-meta">' +
        '<a href="#link-to-user" class="am-comment-author" ' + attr + fnc + '>' + (content.user_name ? content.user_name : '系统消息') + '</a>' +
        '   <time datetime="' + content.time + '" title="' + content.time + '">' + content.time + '</time>' +
        '</div>' +
        '</header>' +
        '<div class="am-comment-bd am-cs-mess-list-cont">' + content.content + '</div></div></li>';
    // 私聊时  自身记录的对方的uid 作为内容的class    因此  send to uid 是发送方显示   from_uid在收信方显示
    if (self_is_create) {
        self_dom.append(html);// 自己也要看到自己发送的消息
    } else {
        self_dom.children('li:last').find('.am-cs-mess-list-cont').append(content.content);
    }
    if (friend_dom.html() == undefined && content.send_to_uid == user_id) {
        var style = 'style="display: none;"';
        if ($('#chat_id').val() == content.from_uid) {
            style = '';
        }
        $(".am-cs-mess").append('<ul class="am-comments-list am-comments-list-flip chat-content chat-content-' + content.from_uid + '" ' + style + '></ul>');
        friend_dom = $('.chat-content-' + content.from_uid);
    }
    if (content.from_uid && content.send_to_uid > 0) {   //  私聊时  添加消息到对方显示
        if (fri_is_create) {
            friend_dom.append(html);
        } else {
            friend_dom.children('li:last').find('.am-cs-mess-list-cont').append(content.content);
        }
    }
    $(".am-cs-mess").smoothScroll({position: $(".am-cs-mess")[0].scrollHeight});
    editor.txt.clear();
}
$('.send-message').on('click', function () {
    var text = editor.txt.text();
    var msg = editor.txt.html();
    if (/img/i.test(msg)) {
        text = 1;
    }
    if (text != '' && text != null) {
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
function keySend(event) {
    if (event.ctrlKey && event.keyCode == 13) {
        var text = editor.txt.text();
        var msg = editor.txt.html();
        if (/img/i.test(msg)) {
            text = 1;
        }
        if (text != '' && text != null) {
            sendMessage(msg);
        }
    }
}
function renderer_data(cont) {
    var html = '';
    var data = cont.all_user;
    for (i = 0; i < cont.all_group.length; i++) {
        html += '<li class="am-text-truncate" data-id="' + cont.all_group[i] + '" data-type="group" onclick="select_user(this)">在线群聊</li>';
    }
    for (i = 0; i < data.length; i++) {
        if (user_id == data[i].id) {
            html += '<li class="am-text-truncate" data-id="' + data[i].id + '" data-un="' + data[i].user_name + '" data-am-modal="{target: \'#my-alert\'}">' + data[i].user_name + ' ' + data[i].city + '</li>';
        } else {
            html += '<li class="am-text-truncate" data-id="' + data[i].id + '" data-un="' + data[i].user_name + '" onclick="select_user(this)">' + data[i].user_name + ' ' + data[i].city + '</li>';
        }
    }
    /*if (cont.type == 'login') {
     $('.chat-content').addClass('chat-content-' + cont.send_to_group)
     }
     if (cont.send_to_group != '' && (cont.send_to_uid == '' || cont.send_to_uid == null)) {
     $('#group_id').val(cont.send_to_group);
     $('.chat-title').text('在线群聊');
     }*/
    $('.user-list').html(html);
}
$(".am-cs-show-user").on('click', function () {
    $("#am-cs-user").offCanvas($(this).data('rel'));
})
function select_user(event) {
    var uid = $(event).attr('data-id');
    var data_type = $(event).attr('data-type');
    if (data_type == 'group') {
        $('#group_id').val(uid);
        $('.chat-title').text('在线群聊');
        $('#chat_id').val(0);
    } else {
        $('#chat_id').val(uid);
        $('.chat-title').text($(event).attr('data-un'));
    }
    $('.chat-content').hide();
    if ($('.chat-content-' + uid).html() == undefined) {
        $(".am-cs-mess").append('<ul class="am-comments-list am-comments-list-flip chat-content chat-content-' + uid + '"></ul>');
    } else {
        $('.chat-content-' + uid).show()
    }
    $("#am-cs-user").offCanvas('close');
    $(".am-cs-mess").smoothScroll({position: $(".am-cs-mess")[0].scrollHeight});
}
console.log('load end');