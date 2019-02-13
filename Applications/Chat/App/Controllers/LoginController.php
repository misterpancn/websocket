<?php
use GatewayClient\Gateway;

$action = htmlspecialchars($_GET['action']);
switch ($action) {
    case 'register':
        $email = str_tirm($_POST['email']);
        $password = str_tirm($_POST['password']);
        $verify_password = str_tirm($_POST['verify_password']);
        $name = str_tirm($_POST['name']);
        if (empty($name)) {
            warn('请填写正确的用户名', $_POST['is_app']);
            redirect('register.php');
        }
        if ($password != $verify_password) {
            warn('密码不一致', $_POST['is_app']);
            redirect('register.php');
        }
        $res = $globalDB->select('user_id')->from('users')->where('user_email="' . $email . '"')->row();
        if ($res['user_id']) {
            warn('该邮箱已注册', $_POST['is_app']);
            redirect('register.php');
        }

        try {
            $globalDB->insert('users')->cols(array
            ('user_email' => $email, 'user_password' => encryption($password), 'phone_prefix' => '', 'create_time' => date('Y-m-d H:i:s'), 'user_name' => $name))->query();
        } catch (Exception $e) {
            warn('注册失败请联系管理员', $_POST['is_app'], 'danger',
                ['error' => $e->getMessage()]
            );
        }
        if ($_POST['is_app']) {
            warn('success', $_POST['is_app']);
        }
        redirect('login.php');
        break;

    case 'login':
        $email = str_tirm($_POST['email']);
        $pwd = str_tirm($_POST['password']);
        $res = $globalDB->select('user_password')->from('users')->where("user_email='{$email}'")->row();
        $user = $globalDB->select('user_id')->from('users')->where("user_email='{$email}'")->row();
        if (empty($res['user_password'])) {
            warn('用户不存在', $_POST['is_app']);
            redirect('login.php');
        }
        if ($res['user_password'] == encryption($pwd)) {
            if (Gateway::isUidOnline($user['user_id'])) {
                warn('你已在别处登录', $_POST['is_app']);
                redirect('login.php');
            }
            $_SESSION['users_id'] = $user['user_id'];
            $_SESSION['token'] = md5(time() . $user['user_id']);
            cas_set("allToken", $_SESSION['token']);
            $globalDB->update('users')
                ->cols(array('login_time' => date('Y-m-d H:i:s'), 'login_ip' => get_client_ip()))
                ->where('user_id=' . $user['user_id'])->query();
            $users_res = $globalDB->select('user_name,login_ip')->from('users')->where("user_id={$user['user_id']}")->row();
            $user_info = array('id' => $user['user_id'], 'user_name' => $users_res['user_name'], 'city' => get_ip_city($users_res['login_ip'], true), 'ip' => $users_res['login_ip']);
            cas_set("all_user_info", $user_info);
            if ($_POST['is_app']) {
                warn('success', $_POST['is_app'], '',
                    [
                        'user_id' => $user['user_id'],
                        'name' => $users_res['user_name'],
                        'img' => 'img/touxiang.png',
                        'all_user' => $globalServer->all_user_info
                    ]
                );
            }
            redirect('liveChat.php');
        } else {
            warn('密码错误', $_POST['is_app']);
            redirect('login.php');
        }
        break;

    case 'logout':
        $uid = str_tirm($_POST['uid']);
        if ($uid) {
            $all_token = $globalServer->allToken;
            foreach ($all_token as $k => $v) {
                if ($_SESSION['token'] == $v) {
                    unset($all_token[$k]);
                }
            }
            $globalServer->allToken = $all_token;
            $redisService->del("cuid:" . $uid);
            unset($_SESSION['users_id']);
            unset($_SESSION['token']);
            unset($_SESSION['all_online_user']);
            echo 'success';
        }
        exit;
        break;

    case 'clear_warn':
        clear_warn();
        exit('success');
        break;
}
