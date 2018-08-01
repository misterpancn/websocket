<?php
ini_set('date.timezone','Asia/Shanghai');
ini_set('display_errors','on');
error_reporting(E_COMPILE_ERROR);
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

use GatewayClient\Gateway;
// 加载所有Applications/Chat/App/Controllers/System所有文件
foreach(glob(__DIR__.'/Controllers/System/*.php') as $start_file)
{
    require_once $start_file;
}

if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if(!extension_loaded('posix'))
{
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}
Gateway::$registerAddress = REGISTER_SERVER;
$global_db = new Workerman\MySQL\Connection(MYSQL_HOST, MYSQL_PORTS, MYSQL_USER, MYSQL_PASS, DB_NAME);
/**
 * 全局变量服务，用于进程间变量共享
 */
$global_server = new GlobalData\Client(GLOBAL_SERVER);

/**
 * 设置登录的token   防止非法登录
 */
if(isset($_SESSION['token']) && $_SESSION['token'] && $_SESSION['users_id']){
    $all_Token = $global_server->allToken;
    if(empty($all_Token) || sizeof($all_Token) == 0 || !in_array($_SESSION['token'],$all_Token)){
        $_SESSION['token'] = md5(time().$_SESSION['users_id']);
        $overflow = 0;
        do
        {
            $old_value = $new_value = $all_Token;
            $new_value[] = $_SESSION['token'];
            $overflow++;
        }
        while(!$global_server->cas('allToken', $old_value, $new_value) && $overflow < 10);
    }
}
/**
 * 用户未登录且不是在login页面  重定向至登录页
 */
if(empty($_SESSION['users_id']) && Gateway::isUidOnline($_SESSION['users_id'])==0 && !preg_match('/login.php/',$_SERVER['REQUEST_URI']) && !preg_match('/register.php/',$_SERVER['REQUEST_URI']) && !preg_match('/StartCaptchaServlet.php/',$_SERVER['REQUEST_URI']) && !preg_match('/VerifyLoginServlet.php/',$_SERVER['REQUEST_URI'])){
    redirect('login.php');
}
