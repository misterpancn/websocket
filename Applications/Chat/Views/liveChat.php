<?php
require_once "../App/autoload.php";
require_once '../App/Controllers/LiveChatController.php';
?>
<!DOCTYPE HTML>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Live Chat</title>

    <!-- Set render engine for 360 browser -->
    <meta name="renderer" content="webkit">

    <!-- No Baidu Siteapp-->
    <meta http-equiv="Cache-Control" content="no-siteapp"/>

    <link rel="icon" type="image/png" href="img/favicon.png">

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Gateway Chat"/>

    <link rel="stylesheet" href="css/amazeui.min.css">
    <link rel="stylesheet" href="css/app.css">
    <style>
        #vld-tooltip {
            position: absolute;
            z-index: 1000;
            padding: 5px 10px;
            background: #F37B1D;
            min-width: 150px;
            color: #fff;
            transition: all 0.15s;
            box-shadow: 0 0 5px rgba(0,0,0,.15);
            display: none;
        }

        #vld-tooltip:before {
            position: absolute;
            top: -8px;
            left: 50%;
            width: 0;
            height: 0;
            margin-left: -8px;
            content: "";
            border-width: 0 8px 8px;
            border-color: transparent transparent #F37B1D;
            border-style: none inset solid;
        }
    </style>
</head>
<body>
<div class="am-g doc-am-g am-u-sm-centered" style="width: 60%">
    <div class="am-u-sm-6 am-u-md-4 am-u-lg-3">
        <h3>在线用户</h3>
        <ul class="am-list am-list-static am-list-border am-list-striped user-list">
            
        </ul>
    </div>
    <div class="am-u-sm-6 am-u-md-8 am-u-lg-9">
        <div class="">
            <input type="hidden" value="<?php echo $_SESSION['users_id']?>" id="user_id"/>
            <ul class="am-comments-list am-comments-list-flip chat-content">
                <!-- 聊天室消息 -->
            </ul>
            <div class="am-form">
                <div class="am-form-group">
                    <label for="doc-ta-1">文本域</label>
                    <textarea class="am-accordion-content" rows="5" id="message"></textarea>
                </div>
            </div>
            <button class="am-btn am-btn-primary am-btn-sm logout am-fl">退出</button>
            <button class="am-btn am-btn-primary am-btn-sm send-message am-fr">发送</button>
        </div>
    </div>
</div>

<!--在这里编写你的代码-->

<!--[if (gte IE 9)|!(IE)]><!-->
<script src="js/jquery-1.9.1.min.js"></script>
<!--<![endif]-->
<!--[if lte IE 8 ]>
<script src="http://libs.baidu.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<script src="js/amazeui.ie8polyfill.min.js"></script>
<![endif]-->
<script src="js/amazeui.min.js"></script>
<script type="text/javascript">
    var socket;
    var user_id = $('#user_id').val();
    function connectWS() {
        socket = new WebSocket('ws://'+document.domain+':9526');
        socket.onopen = onopen;
        socket.onmessage = onmessage;
        socket.onerror = socket_error;
        socket.onclose = socket_close;
    }
    function onopen(){
        var send = '{"type":"login","uid":"<?php echo $_SESSION['users_id']?>","token":"<?php echo $_SESSION['token']?>"}';
        console.log('连接服务器成功');
        socket.send(send);
    }
    function onmessage(mes){
        var data = eval("("+mes.data+")");
        switch (data['type']){
             case 'msg':
                if(data.uid == user_id){
                    say(data,1);
                }else{
                    say(data,0);
                }
                console.log(data);
             break;
            case 'ping':
                break;
            case 'login':
               // say(data,0);
                console.log(data);
                renderer_data(data.all_user); // 刷新用户列表
                break;
            case 'logout':
                var timer = setTimeout(function(){say(data,0)},5000);
                setTimeout(function(){$.ajax({
                    type:'post',
                    url:'?action=refresh_close',
                    data:{'uid':data.uid},
                    success:function(msg){
                        if(msg=='is_online'){
                            clearTimeout(timer);
                        }else{
                            renderer_data(data.all_user); // 刷新用户列表
                        }
                        console.log(msg);
                    }
                })},1000)
                break;
             default :
             console.log(data);
             break;
        }
    }
    connectWS();
    function sendMessage(mes){
        if(mes){
            var data = '{"type":"message","content":"'+mes+'"}';
            socket.send(data);
        }
    }
    function socket_error(){
        console.log('服务器连接出错，定时重连......');
        setTimeout(connectWS,5000);
    }
    function socket_close() {
        console.log('服务器连接已断开，定时重连......');
        setTimeout(connectWS,5000);
    }
    $('.logout').click(function(){
        $.ajax({
            type:'post',
            url:'login.php?action=logout',
            data:{'uid':"<?php echo $_SESSION['users_id']?>"},
            success:function (msg) {
                if(msg == 'success') {
                    socket.close();
                    location.reload();
                }
            }
        })
    })
    function getCookie(name)
    {
        var cookie_name=document.cookie.split(";");
        var cookie_val ;
        for (i=0;i<cookie_name.length;i++){
            var splits = cookie_name[i].split("=");
            if(name==splits[0]){
                cookie_val = splits[1];
            }
        }
        return cookie_val;
    }

    function say(content,type){
        var classes = '';
        if(type==1){
            classes = ' am-comment-flip am-comment-success';
        }
        var html = '<li class="am-comment '+classes+'">'+
            '<a href="#link-to-user-home">'+
            '<img src="img/Starry.jpg" alt="" class="am-comment-avatar" width="48" height="48"/>'+
            '</a>'+
            '<div class="am-comment-main">'+
            '<header class="am-comment-hd">'+
            '<div class="am-comment-meta">'+
            '<a href="#link-to-user" class="am-comment-author">'+(content.user_name ? content.user_name : '系统消息')+'</a>'+
            '   <time datetime="'+content.time+'" title="'+content.time+'">'+content.time+'</time>'+
        '</div>'+
        '</header>'+
        '<div class="am-comment-bd">'+content.content+'</div></div></li>';
        $('.chat-content').append(html);
        $('#message').val('');
    }
    $('.send-message').click(function(){
        var msg = $('#message').val();
        //alert(msg);
        if(msg!='' && msg !=null){
            sendMessage(msg);
        }
    })
    function renderer_data(data){
        var html = '';
        for(i=0;i<data.length;i++){
            html += '<li>'+data[i].user_name+' '+data[i].city+'</li>';
        }
        $('.user-list').html(html);
    }
</script>
</body>
</html>
