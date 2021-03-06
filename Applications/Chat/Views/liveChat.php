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
    <title>Demo</title>

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
    <link rel="stylesheet" href="css/chat.css">
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
    <div class="am-u-sm-12 am-u-md-8 am-u-lg-9 am-fl am-cs-mess-con am-panel am-panel-secondary">
        <div class="am-panel-hd am-text-center chat-title">在线群聊</div>
        <div class="am-cs-mess am-panel-bd">
            <input type="hidden" value="<?php echo $_SESSION['users_id']?>" id="user_id"/>
            <input type="hidden" value="<?php echo $_SESSION['token']?>" id="token"/>
            <input type="hidden" id="chat_id" />
            <input type="hidden" id="group_id" value="allUsers" />
            <ul class="am-comments-list am-comments-list-flip chat-content chat-content-allUsers">
                <!-- 聊天室消息 -->
            </ul>
        </div>
        <hr data-am-widget="divider" style="" class="am-divider am-divider-dashed" />
        <div class="am-form">
            <div class="am-form-group">
                <label for="doc-ta-1">输入框</label>
                <div id="editor" onkeydown="keySend(event);" title="按ctrl+enter直接发送" placeholder="按ctrl+enter直接发送"></div>
                <!--<textarea class="am-accordion-content" rows="5" id="message" onkeydown="keySend(event);" title="按ctrl+enter直接发送" placeholder="按ctrl+enter直接发送"></textarea>-->
            </div>
            <button class="am-btn am-btn-primary am-btn-sm logout am-fl">退出</button>
            <button class="am-btn am-btn-primary am-btn-sm send-message am-fr" data-doc-animation="shake">发送</button>
        </div>
    </div>
    <div class="am-modal am-modal-confirm" tabindex="-1" id="logout-confirm">
        <div class="am-modal-dialog">
            <div class="am-modal-hd">提示</div>
            <div class="am-modal-bd">
                你，确定要退出吗？
            </div>
            <div class="am-modal-footer">
                <span class="am-modal-btn" data-am-modal-cancel>取消</span>
                <span class="am-modal-btn" data-am-modal-confirm>确定</span>
            </div>
        </div>
    </div>
    <div class="am-modal am-modal-alert" tabindex="-1" id="my-alert">
        <div class="am-modal-dialog">
            <div class="am-modal-hd alert-title">警告</div>
            <div class="am-modal-bd alert-cont">不能和自己聊天</div>
            <div class="am-modal-footer">
                <span class="am-modal-btn">确定</span>
            </div>
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
<script src="js/wangEditor.min.js"></script>
<script src="js/socket.js"></script>
</body>
</html>
