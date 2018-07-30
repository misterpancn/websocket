<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/11/2
 * Time: 14:24
 */

namespace App\Controllers\System;


use GatewayClient\Gateway;

class BindUsers extends Gateway
{
    public function __construct()
    {
        self::$registerAddress = '127.0.0.1:1238';
    }

}