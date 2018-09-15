<?php
use GatewayClient\Gateway;
$action = str_tirm($_GET['action']);
switch ($action){
    case 'refresh_close':
        $uid = str_tirm($_POST['uid']);
        $all_user = $globalServer->allUsers;
        if(in_array($uid,$all_user)){
            echo 'is_online';
        }else{
            echo 'is_offline';
        }
        exit;
        break;
}
