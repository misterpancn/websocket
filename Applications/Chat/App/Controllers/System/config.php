<?php
/**
 * Created by PhpStorm.
 * User: buck
 * Date: 2018/7/31
 * Time: 18:14
 */

/**
 *  Geetest配置文件
 */
define("CAPTCHA_ID", "3f4efa9562848d96c4bad358e6faf57b");  // geetest ID
define("PRIVATE_KEY", "929c3ee70dbd989ae8a39a4fde0d76a8");  // geetest KEY
define("MYSQL_HOST", '127.0.0.1');
define("MYSQL_PORTS", '3306');
define("MYSQL_USER", 'test');
define("MYSQL_PASS", '123456');
define("DB_NAME", 'chat');
define("GLOBAL_SERVER", '0.0.0.0:2207');   // 内部通讯超全局变量服务
define("REGISTER_SERVER", '0.0.0.0:1238');  // 注册服务器
define("GATEWAY_SERVER", '0.0.0.0:9526');  // gateway服务
define('LOCAL_CRT', '/download/ssl/Nginx/1_chat.misterpan.cn_bundle.crt');   // ssl crt
define('LOCAL_KEY', '/download/ssl/Nginx/2_chat.misterpan.cn.key');  // ssl key
define('LAN_IP', '0.0.0.0');   // 本机ip，分布式部署时使用内网ip
define('START_PORT', '2900');// 内部通讯起始端口，假如$gateway->count=4，起始端口为2900-2903
define('GATEWAY_PROCESS_COUNT',4);// gateway进程数
define('BUSINESS_PROCESS_COUNT',4);// business worker进程数
define('GATEWAY_NAME','ChatGateway');// gateway名称，status方便查看
define('BUSINESS_WORKER_NAME','ChatBusinessWorker');// business worker名称，status方便查看
define('HEARTBEAT_TIME',20);  // 心跳间隔
define('PING_DATA','{"type":"ping"}');  // 心跳数据