<?php
require_once "../App/autoload.php";
require_once '../App/Controllers/LoginController.php';
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
<div class="am-g">
    <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered login-box">
        <h3><a class="am-fl am-btn am-btn-success am-btn-sm login">登录</a><a class="am-fr am-btn am-btn-sm register" href="register.php">注册</a></h3>
        <div style="clear: both;"></div>
        <hr>
        <?php if($_SESSION['warn_mess']){?>
        <div class="am-alert <?=$_SESSION['warn_class']?>" data-am-alert>
            <button type="button" class="am-close am-cs-close-warn">&times;</button>
            <p><?=$_SESSION['warn_mess']?><input type="hidden" name="is_warn" value="1" /></p>
        </div>
        <?php }?>
        <form method="post" class="am-form login-form" action="?action=login">
            <label for="email">邮箱:</label>
            <input class="am-form-field am-round js-ajax-validate" type="email" name="email" value="" data-validate-async required/>
            <br>
            <label for="password">密码:</label>
            <input class="am-form-field am-round" type="password" name="password" value="" required/>
            <input class="am-form-field am-round" type="hidden" name="client_id" value="" />
            <br>
            <div id="embed-captcha" class="am-u-lg-6 am-u-sm-centered"></div>
            <p id="wait" class="">正在加载验证码......</p>
            <p id="notice" class="am-hide">请先完成验证</p>
            <label for="remember-me">
                <input id="remember-me" type="checkbox">
                记住密码
            </label>
            <br />
            <div class="am-cf">
                <input type="submit" name="" value="登 录" class="am-btn am-btn-primary am-btn-sm am-fl check-submit">
                <input type="button" name="" value="忘记密码 ^_^? " class="am-btn am-btn-default am-btn-sm am-fr">
            </div>
        </form>
        <hr>
        <p>© 2017 Buck Gateway Live Chat </p>
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
<script src="js/gt.js"></script>
<script src="js/login.js"></script>
</body>
</html>