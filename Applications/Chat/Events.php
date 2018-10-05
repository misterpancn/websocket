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
require_once __DIR__.'/App/autoload.php';
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);
use \Config\worker\handleMessageClass;
use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{

    /**
     * 进程启动后实例化连接数据库，每个进程都会实例化一个连接
     * 这里的方法固定是静态方法，所以调用属性也必须是静态属性
     *
     * @param $businessWorker
     */
    public static function onWorkerStart($businessWorker)
    {

    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        Gateway::joinGroup($client_id,'allUsers');
    }

    /**
     * 当客户端发来消息时触发
     *
     * @param int   $client_id 连接id
     * @param mixed $message   具体消息
     */
    public static function onMessage($client_id, $message)
    {
        $handle = new handleMessageClass();
        $handle::Message($client_id,$message);
    }

    /**
     * 当用户断开连接时触发
     *
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        $handle = new handleMessageClass();
        $handle::closeConnect();
    }

}
