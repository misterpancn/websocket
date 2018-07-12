<?php
use GatewayClient\Gateway;
$action = htmlspecialchars($_GET['action']);
switch ($action) {
    case 'register':
        $email = str_tirm($_POST['email']);
        $password = str_tirm($_POST['password']);
        $verify_password = str_tirm($_POST['verify_password']);
        $name = str_tirm($_POST['name']);
        if($password != $verify_password){
            die('密码不一致');
        }
        $res = $global_db->select('user_id')->from('users')->where('user_email="'.$email.'"')->row();
        if($res['user_id']){
            die('该邮箱已注册');
        }

        $global_db->insert('users')->cols(array
        ('user_email' => $email, 'user_password' => encryption($password), 'create_time' => date('Y-m-d H:i:s'), 'user_name' => $name))->query();
        redirect('login.php');
        break;

    case 'login':
        $email = str_tirm($_POST['email']);
        $pwd =str_tirm($_POST['password']);
        $res = $global_db->select('user_password')->from('users')->where("user_email='{$email}'")->row();
        $user = $global_db->select('user_id')->from('users')->where("user_email='{$email}'")->row();
        if(empty($res['user_password'])){
            die('用户不存在');
        }
        if($res['user_password'] == encryption($pwd)){
            if(Gateway::isUidOnline($user['user_id'])) {
                die('你已在别处登录');
            }
            if(!in_array($user['user_id'],$global_server->allUsers)) {
                if (isset($global_server->allUsers) && sizeof($global_server->allUsers) > 0) {
                    $overflow = 0;
                    do {
                        $old_value = $new_value = $global_server->allUsers;
                        $new_value[] = $user['user_id'];
                        $overflow++;
                    } while (!$global_server->cas('allUsers', $old_value, $new_value) && $overflow < 10);
                } else {
                    $global_server->allUsers = array($user['user_id']);
                }
            }
            $global_server->currentlyUserId = $user['user_id'];
            $_SESSION['users_id'] = $user['user_id'];
            $_SESSION['token'] = md5(time().$user['user_id']);
            if(isset($global_server->allToken)){
                $overflow = 0;
                do
                {
                    $old_value = $new_value = $global_server->allToken;
                    $new_value[] = $_SESSION['token'];
                    $overflow++;
                }
                while(!$global_server->cas('allToken', $old_value, $new_value) && $overflow < 10);
            }else {
                $global_server->allToken = array($_SESSION['token']);
            }
            $global_db->update('users')
                ->cols(array('login_time'=>date('Y-m-d H:i:s'),'login_ip'=>get_client_ip()))
                ->where('user_id='.$user['user_id'])->query();
            if(!array_key_exists($user['user_id'],$global_server->all_user_info)) {
                $users_res = $global_db->select('user_name,login_ip')->from('users')->where("user_id={$user['user_id']}")->row();
                $user_info = array('id'=>$user['user_id'], 'user_name'=>$users_res['user_name'], 'city'=>get_ip_city($users_res['login_ip'], true), 'ip'=>$users_res['login_ip']);
                if (isset($global_server->all_user_info) && sizeof($global_server->all_user_info) > 0) {
                    $overflow = 0;
                    do {
                        $old_value = $new_value = $global_server->all_user_info;
                        $new_value[$user['user_id']] = $user_info;
                        $overflow++;
                    } while (!$global_server->cas('all_user_info', $old_value, $new_value) && $overflow < 10);
                } else {
                    $arr[$user['user_id']] = $user_info;
                    $global_server->all_user_info = $arr;
                }
            }
            redirect('liveChat.php');
        }else{
            die('密码错误');
        }
        break;

    case 'logout':
        $uid = str_tirm($_POST['uid']);
        if($uid){
            $all_token = $global_server->allToken;
            foreach ($all_token as $k => $v) {
                if ($_SESSION['token'] == $v) {
                    unset($all_token[$k]);
                }
            }
            $global_server->allToken = $all_token;
            unset($_SESSION['users_id']);
            unset($_SESSION['token']);
            unset($_SESSION['all_online_user']);
            echo 'success';
        }
        exit;
        break;
}
