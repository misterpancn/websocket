<?php
require_once "../App/autoload.php";
require_once '../App/Controllers/LiveChatController.php';
if(IS_BETA) {
    echo $_SESSION['users_id'];
    print_r($globalServer->all_user_info);
    print_r($globalServer->allUsers);
    print_r($globalServer->allToken);
}
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
        body {
            font-family: "Segoe UI", "Lucida Grande", Helvetica, Arial, "Microsoft YaHei", FreeSans, Arimo, "Droid Sans","wenquanyi micro hei","Hiragino Sans GB", "Hiragino Sans GB W3", Arial, sans-serif;
            font-size: 1.4rem;
        }
        .am-cs-mess-con{
            height: 65%;
            max-height: 720px;
            min-height: 300px;
        }
        .am-cs-mess{
            height: 70%;
            overflow-y: auto;
        }
        .am-cs-user{
            position: absolute;/*top: 50%;transform: translate(-50%, 0);*/
        }
    </style>
</head>
<body class="am-g">
<div class="am-fl am-cs-user am-hide-md-up">
    <span class="am-icon-sm am-icon-chevron-circle-right am-cs-show-user" data-rel="open"></span>
</div>
<div class="am-u-sm-centered am-u-md-11 am-u-md-centered am-u-sm-12 am-u-lg-7 am-u-lg-centered">
    <div id="am-cs-user" class="am-offcanvas">
        <div class="am-offcanvas-bar">
            <!-- 你的内容 -->
            <div class="am-offcanvas-content">
                <p>在线用户</p>
                <ul class="am-list am-list-static am-list-border am-list-striped user-list">

                </ul>
            </div>
        </div>
    </div>
    <div class="am-u-md-4 am-u-lg-3 am-fl am-show-md-up">
        <label>在线用户</label>
        <ul class="am-list am-list-static am-list-border am-list-striped user-list">
            
        </ul>
    </div>
    <div class="am-u-sm-12 am-u-md-8 am-u-lg-9 am-fl am-cs-mess-con">
        <div class="am-cs-mess">
            <input type="hidden" value="<?php echo $_SESSION['users_id']?>" id="user_id"/>
            <input type="hidden" value="<?php echo $_SESSION['token']?>" id="token"/>
            <ul class="am-comments-list am-comments-list-flip chat-content">
                <!-- 聊天室消息 -->
            </ul>
        </div>
        <div class="am-form">
            <div class="am-form-group">
                <label for="doc-ta-1">文本域</label>
                <textarea class="am-accordion-content" rows="5" id="message"></textarea>
            </div>
            <button class="am-btn am-btn-primary am-btn-sm logout am-fl">退出</button>
            <button class="am-btn am-btn-primary am-btn-sm send-message am-fr" data-doc-animation="shake">发送</button>
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
<script src="js/socket.js"></script>
</body>
</html>
