<?php

/**
 * Created by PhpStorm.
 * User: buck
 * Date: 2018/9/27
 * Time: 15:15
 */

namespace Config\worker;

use \GatewayWorker\Lib\Gateway;

class handleMessageClass
{
    protected static $db;
    public static $mysqlHost = MYSQL_HOST;
    public static $mysqlPort = MYSQL_PORTS;
    public static $mysqlUser = MYSQL_USER;
    public static $mysqlPas = MYSQL_PASS;
    public static $DBName = DB_NAME;
    protected static $globalData;
    protected static $redisService;

    public function __construct()
    {
        self::$db = new \Workerman\MySQL\Connection(self::$mysqlHost, self::$mysqlPort, self::$mysqlUser, self::$mysqlPas, self::$DBName);
        self::$globalData = new \GlobalData\Client(GLOBAL_SERVER);
        self::$redisService = new \Predis\Client(['host' => '127.0.0.1', 'port' => '6379']);
    }

    public static function Message($client_id, $message)
    {
        $data = json_decode($message, true);
        echo $message . "\n";
        switch ($data['type']) {
            case 'message':
                if ($data['send_to_uid']) {
                    Gateway::sendToUid([$data['send_to_uid'], $data['uid']], self::messagePack('msg', $data, $_SESSION['uid']));
                } elseif ($data['group']) {
                    Gateway::sendToGroup($data['group'], self::messagePack('msg', $data, $_SESSION['uid']));
                }
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
                Gateway::sendToGroup('allUsers', self::messagePack('login', [], $_SESSION['uid']));
                break;
            case 'logout':
                self::clearUser($_SESSION['uid']);
                break;
        }
    }

    protected static function messagePack($type, $cont = [], $uid = 0, $send_user = '')
    {
        $user = self::getGlobalUserInfo($uid);
        $mes = $cont['content'];
        if ($uid) $user = $user ? $user : self::$db->select('user_name')->from('users')->where('user_id=' . $uid)->row();
        if ($mes) {
            $mes = preg_replace('/[\\n\\r]/i', '<br>', $mes);
        }
        $allgroup = Gateway::getAllGroupIdList();
        @sort($allgroup);
        $data = [
            'type'           => $type,
            'content'        => $type == 'login' ? $user['user_name'] . '加入聊天室' : $mes,
            'user_name'      => $send_user ? $send_user : $user['user_name'],
            'time'           => date('Y-m-d H:i:s'),
            'from_uid'       => $_SESSION['uid'],
            'from_client'    => $_SESSION['uid'] ? Gateway::getClientIdByUid($_SESSION['uid']) : '',
            'all_user'       => self::$globalData->all_user_info,
            'all_group'      => $allgroup,
            'send_to_group'  => $cont['group'] ? $cont['group'] : 'allUsers',
            'send_to_uid'    => $cont['send_to_uid'],
            'send_to_client' => $cont['send_to_uid'] ? Gateway::getClientIdByUid($cont['send_to_uid']) : '',
            'server'         => 'Gateway'
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

    public static function clearUser($uid)
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

    public static function CasSet($key, $newValue, $moreArray = false)
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

    public static function closeConnect()
    {
        $user = self::$db->select('user_name')->from('users')->where('user_id=' . (int)$_SESSION['uid'])->row();
        // 向所有人发送
        GateWay::sendToAll(self::messagePack('logout', $user['user_name'] . '已退出聊天室'));
        unset($_SESSION['uid']);
    }
}