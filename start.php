<?php
/**
 * run with command
 * php start.php start
 */

ini_set('display_errors', 'off');
error_reporting(E_ERROR);
ini_set('date.timezone', 'Asia/Shanghai');
require_once __DIR__ . '/Applications/Chat/App/Controllers/System/config.php';
use Workerman\Worker;

if (strpos(strtolower(PHP_OS), 'win') === 0) {
    exit("start.php not support windows, please use start_for_win.bat\n");
}

// 检查扩展
if (!extension_loaded('pcntl')) {
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if (!extension_loaded('posix')) {
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 标记是全局启动
define('GLOBAL_START', 1);

require_once __DIR__ . '/vendor/autoload.php';

// 加载所有Applications/*/start.php，以便启动所有服务
foreach (glob(__DIR__ . '/Applications/*/start*.php') as $start_file) {
    require_once $start_file;
}
//开启进程之间数据共享服务
$worker = new GlobalData\Server();
//配置log目录
Worker::$logFile = __DIR__ . '/logs/workerman.log';
Worker::$pidFile = __DIR__ . '/logs/workerman.pid';
// 运行所有服务
Worker::runAll();
