<?php
require_once "../App/autoload.php";
require_once '../App/Controllers/LiveChatController.php';
echo $_SESSION['users_id'];print_r($globalServer->all_user_info);print_r($globalServer->allUsers);print_r($globalServer->allToken);
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
            <input type="hidden" value="<?php echo $_SESSION['token']?>" id="token"/>
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
<script src="js/socket.js"></script>
</body>
</html>
