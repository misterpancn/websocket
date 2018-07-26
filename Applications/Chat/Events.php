<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    buck<misterpancn@Gmail.com>
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    protected static $db;
    public static $mysqlHost = '127.0.0.1';
    public static $mysqlPort = '3306';
    public static $mysqlUser = 'test';
    public static $mysqlPas = '123456';
    public static $DBName = 'mychat';
    protected static $global_data;

    /**
     * 进程启动后实例化连接数据库，每个进程都会实例化一个连接
     * 这里的方法固定是静态方法，所以调用属性也必须是静态属性
     *
     * @param $businessWorker
     */
    public static function onWorkerStart($businessWorker)
    {
        self::$db = new Workerman\MySQL\Connection(self::$mysqlHost, self::$mysqlPort, self::$mysqlUser, self::$mysqlPas, self::$DBName);
        self::$global_data = new GlobalData\Client('127.0.0.1:2207');
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        // 向当前client_id发送数据 
        /* Gateway::sendToClient($client_id, "Hello $client_id user_id {$_SESSION['user_id']}\n");
         // 向所有人发送
         Gateway::sendToAll("$client_id login\n");*/

        if (Gateway::isUidOnline($_SESSION['uid'])) {  // 用户已登录
            //   return Gateway::closeClient($client_id);
        }
        /*if (isset(self::$global_data->currentlyUserId)) {
            $_SESSION['uid'] = self::$global_data->currentlyUserId;
            Gateway::bindUid($client_id, self::$global_data->currentlyUserId);
            unset(self::$global_data->currentlyUserId);
        } elseif ($_SESSION['uid']) {
            Gateway::bindUid($client_id, $_SESSION['uid']);
        } else {
           // Gateway::sendToClient($client_id,'debug');
           // return Gateway::closeClient($client_id);
        }*/
    }

    /**
     * 当客户端发来消息时触发
     *
     * @param int   $client_id 连接id
     * @param mixed $message   具体消息
     */
    public static function onMessage($client_id, $message)
    {
        // data={"type":"login", "uid":"666"}
        $data = json_decode($message, true);
        // 如果没有$_SESSION['uid']说明客户端没有登录
        if (!isset($_SESSION['uid']) && $data['type'] == 'login') {
            // 消息类型不是登录视为非法请求，关闭连接
            $all_token = self::$global_data->allToken;
            if ($data['type'] !== 'login' || empty($data['uid']) || empty($data['token']) || !in_array($data['token'], $all_token)) {
                return Gateway::closeClient($client_id);
            }
            // 设置session，标记该客户端已经登录
            $_SESSION['uid'] = $data['uid'];
            Gateway::bindUid($client_id, $data['uid']);
            foreach ($all_token as $k => $v) {
                if ($data['token'] == $v) {
                    unset($all_token[$k]);
                }
            }
            self::$global_data->allToken = $all_token;
            if (!in_array($data['uid'], self::$global_data->allUsers)) {
                if (isset(self::$global_data->allUsers) && sizeof(self::$global_data->allUsers) > 0) {
                    $overflow = 0;
                    do {
                        $old_value = $new_value = self::$global_data->allUsers;
                        $new_value[] = $data['uid'];
                        $overflow++;
                    } while (!self::$global_data->cas('allUsers', $old_value, $new_value) && $overflow < 10);
                } else {
                    self::$global_data->allUsers = array($data['uid']);
                }
            }
        }
        switch ($data['type']) {
            case 'message':
                Gateway::sendToAll(self::messagePack('msg', $data['content'], $_SESSION['uid']));
                break;
            case 'login':
                Gateway::sendToAll(self::messagePack('login', '', $_SESSION['uid']));
                break;
        }
        // 向所有人发送 
        // Gateway::sendToAll("$client_id said {$_SESSION['uid']}");
    }

    /**
     * 当用户断开连接时触发
     *
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        self::clearUser($_SESSION['uid']);
        $user = self::$db->select('user_name')->from('users')->where('user_id=' . $_SESSION['uid'])->row();
        // 向所有人发送
        GateWay::sendToAll(self::messagePack('logout', $user['user_name'] . '已退出聊天室'));
        unset($_SESSION['uid']);
    }

    protected static function messagePack($type, $mes = '', $uid = 0, $send_user = '')
    {
        $user_info = self::$global_data->all_user_info;
        if ($uid) $user = $user_info[$uid] ? $user_info[$uid] : self::$db->select('user_name')->from('users')->where('user_id=' . $uid)->row();
        $data = [
            'type'      => $type,
            'content'   => $type == 'login' ? $user['user_name'] . '加入聊天室' : $mes,
            'user_name' => $send_user ? $send_user : $user['user_name'],
            'time'      => date('Y-m-d H:i:s'),
            'uid'       => $_SESSION['uid'],
            'all_user'  => self::$global_data->all_user_info,
            'server'    => 'Gateway'
        ];
        return json_encode($data);
    }

    public static function getCityFromIP($ip, $get_address = false)
    {
        $url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
        $ip = json_decode(file_get_contents($url));
        if ((string)$ip->code == '1') {
            return false;
        }
        $data = (array)$ip->data;
        if ($get_address) {
            return $data['country'] . ' ' . $data['area'] . ' ' . $data['region'] . ' ' . $data['city'];
        } else {
            return $data;
        }
    }

    protected function clearUser($uid)
    {
        $all_user = self::$global_data->allUsers;
        $all_user_info = self::$global_data->all_user_info;
        if ($all_user) {
            foreach ($all_user as $k => $v) {
                if ($v == $uid) {
                    unset($all_user[$k]);
                }
            }
            self::$global_data->allUsers = $all_user;
        }
        if ($all_user_info) {
            foreach ($all_user_info as $k => $v) {
                if ($k == $uid) {
                    unset($all_user_info[$k]);
                }
            }
            self::$global_data->all_user_info = $all_user_info;
        }
    }
}
