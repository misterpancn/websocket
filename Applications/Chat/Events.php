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
    public static $mysqlHost = MYSQL_HOST;
    public static $mysqlPort = MYSQL_PORTS;
    public static $mysqlUser = MYSQL_USER;
    public static $mysqlPas = MYSQL_PASS;
    public static $DBName = DB_NAME;
    protected static $globalData;
    protected static $redisService;

    /**
     * 进程启动后实例化连接数据库，每个进程都会实例化一个连接
     * 这里的方法固定是静态方法，所以调用属性也必须是静态属性
     *
     * @param $businessWorker
     */
    public static function onWorkerStart($businessWorker)
    {
        self::$db = new Workerman\MySQL\Connection(self::$mysqlHost, self::$mysqlPort, self::$mysqlUser, self::$mysqlPas, self::$DBName);
        self::$globalData = new GlobalData\Client(GLOBAL_SERVER);
        self::$redisService = new \Predis\Client(['host' => '127.0.0.1', 'port' => '6379']);
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
        echo $message . "\n";
        switch ($data['type']) {
            case 'message':
                Gateway::sendToAll(self::messagePack('msg', $data['content'], $_SESSION['uid']));
                break;
            case 'login':
                // 消息类型不是登录视为非法请求，关闭连接
                if (empty($data['uid']) || empty($data['token'])) {
                    return Gateway::closeClient($client_id);
                }
                // 设置session，标记该客户端已经登录
                $_SESSION['uid'] = $data['uid'];
                Gateway::bindUid($client_id, $data['uid']);
                if (!self::$redisService->exists("cuid:" . $data['uid'])) { // 登录的时候
                    //添加登录用户到全局变量和redis
                    self::CasSet("allUsers", $data['uid']);
                    self::$redisService->set("cuid:" . $data['uid'], $data['uid']);
                } elseif (sizeof(self::$globalData->allUsers) == 0) {   // 服务器重启  原始在线用户数据恢复
                    $allKeys = self::$redisService->keys("cuid*");
                    $allUserInfo = self::$globalData->all_user_info;
                    if (sizeof($allKeys)) {
                        foreach ($allKeys as $key) {
                            $userID = self::$redisService->get($key);
                            if ($userID) {
                                self::CasSet("allUsers", $userID);
                                $res = self::$db->select('user_name,login_ip')->from('users')->where("user_id={$userID}")->row();
                                if (sizeof($allUserInfo) == 0) {
                                    $userInfo = array('id' => $userID, 'user_name' => $res['user_name'], 'city' => self::getCityFromIP($res['login_ip'], true), 'ip' => $res['login_ip']);
                                    self::CasSet("all_user_info", $userInfo);
                                }
                            }
                        }
                    }
                }
                Gateway::sendToAll(self::messagePack('login', '', $_SESSION['uid']));
                break;
            case 'logout':
                self::clearUser($_SESSION['uid']);
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
        $user = self::$db->select('user_name')->from('users')->where('user_id=' . (int)$_SESSION['uid'])->row();
        // 向所有人发送
        GateWay::sendToAll(self::messagePack('logout', $user['user_name'] . '已退出聊天室'));
        unset($_SESSION['uid']);
    }

    protected static function messagePack($type, $mes = '', $uid = 0, $send_user = '')
    {
        $user_info = self::$globalData->all_user_info;
        $user = self::getGlobalUserInfo($uid);
        if ($uid) $user = $user ? $user : self::$db->select('user_name')->from('users')->where('user_id=' . $uid)->row();
        if($mes){
            $mes = preg_replace('/[\\n\\r]/i','<br>',$mes);
        }
        $data = [
            'type'      => $type,
            'content'   => $type == 'login' ? $user['user_name'] . '加入聊天室' : $mes,
            'user_name' => $send_user ? $send_user : $user['user_name'],
            'time'      => date('Y-m-d H:i:s'),
            'uid'       => $_SESSION['uid'],
            'all_user'  => self::$globalData->all_user_info,
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

    protected static function clearUser($uid)
    {
        $all_user = self::$globalData->allUsers;
        $all_user_info = self::$globalData->all_user_info;
        if ($all_user) {
            foreach ($all_user as $k => $v) {
                if ($v == $uid) {
                    unset($all_user[$k]);
                }
            }
            self::$globalData->allUsers = $all_user;
        }
        if ($all_user_info) {
            foreach ($all_user_info as $k => $v) {
                if ($v['id'] == $uid) {
                    unset($all_user_info[$k]);
                }
            }
            self::$globalData->all_user_info = $all_user_info;
        }
    }

    protected static function CasSet($key, $newValue, $moreArray = false)
    {
        $overflow = 0;
        if (sizeof(self::$globalData->__get($key)) > 0) {
            do {
                $old_value = $new_value = self::$globalData->__get($key);
                $new_value[] = $newValue;
                $overflow++;
            } while (!self::$globalData->cas($key, $old_value, $new_value) && $overflow < 10);
        } else {
            if (!$moreArray) {
                self::$globalData->__set($key, array($newValue));
            } else {
                self::$globalData->__set($key, $newValue);
            }
        }
        echo json_encode(self::$globalData->__get($key));
        echo date("Y/m/d H:i:s") . "\n";
    }

    public static function getGlobalUserInfo($uid)
    {
        $userData = self::$globalData->all_user_info;
        $res = null;
        if (sizeof($userData)) {
            foreach ($userData as $v) {
                if ($v['id'] == $uid) {
                    $res = $v;
                }
            }
        }
        return $res;
    }
}
