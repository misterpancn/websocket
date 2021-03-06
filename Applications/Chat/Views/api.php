<?php
require_once "../App/autoload.php";
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
    case 'get_emoji':
        $res = $globalDB->select(['path','phrase','common'])->from('emoji')->query();
        foreach ($res as $k=>$v){
            $res[$k]['url'] = "https://chat.misterpan.cn/".$v['path'];
            $res[$k]['icon'] = "https://chat.misterpan.cn/".$v['path'];
        }
        echo json_encode($res);
        exit;
        break;
}
