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
<div class="am-g">
    <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered register-box">
        <h3><a class="am-fl am-btn am-btn-sm login" href="login.php">登录</a><a class="am-fr am-btn am-btn-success am-btn-sm register">注册</a></h3>
        <div style="clear: both;"></div>
        <hr>

        <form method="post" class="am-form register-form" action="login.php?action=register">
            <div class="am-form-group am-form-icon am-form-feedback">
                <label for="user-name">用户名:</label>
                <input class="am-form-field am-round" type="text" name="name" value="" required/>
                <span class=""></span>
            </div>
            <div class="am-form-group am-form-icon am-form-feedback">
                <label for="email">邮箱:</label>
                <input class="am-form-field am-round" type="email" name="email" value="" required/>
                <span class=""></span>
            </div>
            <div class="am-form-group am-form-icon am-form-feedback">
                <label for="password">密码:</label>
                <input class="am-form-field am-round" type="password" id="register-pwd" name="password" value="" required/>
                <span class=""></span>
            </div>
            <div class="am-form-group am-form-icon am-form-feedback">
                <label for="password">确认密码:</label>
                <input class="am-form-field am-round" type="password" name="verify_password" value="" data-foolish-msg="密码请与上面保持一致" data-equal-to="#register-pwd" required>
                <span class=""></span>
            </div>
            <div id="embed-captcha" class="am-u-lg-6 am-u-sm-centered"></div>
            <p id="wait" class="">正在加载验证码......</p>
            <p id="notice" class="am-hide">请先完成验证</p>
            <div class="am-cf">
                <input type="submit" name="" value="注 册" class="am-btn am-btn-primary am-btn-sm am-fl check-submit">
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
<script type="text/javascript">
    $(function() {
        var client_id = null;
        var $form = $('.register-form');
        var $tooltip = $('<div id="vld-tooltip">提示信息！</div>');
        $tooltip.appendTo(document.body);

        $form.validator();

        var validator = $form.data('amui.validator');

        $form.on('focusin focusout', '.am-form-error input', function(e) {
            if (e.type === 'focusin') {
                var $this = $(this);
                var offset = $this.offset();
                var msg = $this.data('foolishMsg') || validator.getValidationMessage($this.data('validity'));

                $tooltip.text(msg).show().css({
                    left: offset.left + 10,
                    top: offset.top + $(this).outerHeight() + 10
                });
            } else {
                $tooltip.hide();
            }
        });
    });
</script>
<script src="js/gt.js"></script>
<script>
    var handlerEmbed = function (captchaObj) {
        $(".check-submit").click(function (e) {
            var validate = captchaObj.getValidate();
            if (!validate) {
                $("#notice")[0].className = "am-show-lg";
                setTimeout(function () {
                    $("#notice")[0].className = "am-hide";
                }, 2000);
                e.preventDefault();
            }
        });
        // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
        captchaObj.appendTo("#embed-captcha");
        captchaObj.onReady(function () {
            $("#wait")[0].className = "am-hide";
        });
        // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
    };
    $.ajax({
        // 获取id，challenge，success（是否启用failback）
        url: "StartCaptchaServlet.php?t=" + (new Date()).getTime(), // 加随机数防止缓存
        type: "get",
        dataType: "json",
        success: function (data) {
            console.log(data);
            // 使用initGeetest接口
            // 参数1：配置参数
            // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
                // 更多配置参数请参见：http://www.geetest.com/install/sections/idx-client-sdk.html#config
            }, handlerEmbed);
        }
    });
</script>
</body>
</html>
