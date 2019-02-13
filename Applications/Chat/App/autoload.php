<?php
use GatewayClient\Gateway;

ini_set('date.timezone', 'Asia/Shanghai');
ini_set('display_errors', 'on');
error_reporting(E_ERROR);
// 设置跨域请求
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: x-requested-with,Content-Type,X-CSRF-Token");
header('Access-Control-Allow-Methods: POST,GET,DELETE,OPTIONS');
//header("Access-Control-Allow-Credentials:true");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit(200);

session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

// 加载所有Applications/Chat/App/Controllers/System所有文件
foreach (glob(__DIR__ . '/Controllers/System/*.php') as $start_file) {
    require_once $start_file;
}
if (!extension_loaded('pcntl')) {
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if (!extension_loaded('posix')) {
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}
Gateway::$registerAddress = REGISTER_SERVER;
$globalDB = new Workerman\MySQL\Connection(MYSQL_HOST, MYSQL_PORTS, MYSQL_USER, MYSQL_PASS, DB_NAME);
/**
 * 全局变量服务，用于进程间变量共享
 */
$globalServer = new GlobalData\Client(GLOBAL_SERVER);
$redisService = new \Predis\Client(['host' => '127.0.0.1', 'port' => '6379']);

/**
 * 设置登录的token   防止非法登录
 */
if (isset($_SESSION['token']) && $_SESSION['token'] && $_SESSION['users_id']) {
    $all_Token = $globalServer->allToken;
    if (empty($all_Token) || sizeof($all_Token) == 0 || !in_array($_SESSION['token'], $all_Token)) {
        $_SESSION['token'] = md5(time() . $_SESSION['users_id']);
        $overflow = 0;
        do {
            $old_value = $new_value = $all_Token;
            $new_value[] = $_SESSION['token'];
            $overflow++;
        } while (!$globalServer->cas('allToken', $old_value, $new_value) && $overflow < 10);
    }
}
/**
 * 用户未登录且不是在login页面  重定向至登录页
 */
$pageName = substr($_SERVER['SCRIPT_NAME'], 1, (strpos($_SERVER['SCRIPT_NAME'], '.') - 1));
$authorizations = [
    'login', 'register', 'StartCaptchaServlet', 'VerifyLoginServlet', 'api',
];
if (empty($_SESSION['users_id']) && !in_array($pageName, $authorizations) && strtolower(PHP_SAPI) != 'cli') {
    redirect('login.php');
}
