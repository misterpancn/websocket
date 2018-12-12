<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/11/2
 * Time: 18:36
 */

/**
 * 过滤字符
 *
 * @param $content
 * @return array|string
 */
function str_tirm($content)
{
    $pattern = "/(select[\s])|(insert[\s])|(update[\s])|(delete[\s])|(from[\s])|(where[\s])|(drop[\s])/i";
    if (is_array($content)) {
        foreach ($content as $key => $value) {
            $content[$key] = htmlspecialchars(trim($value));
            if (preg_match($pattern, $content[$key])) {
                $content[$key] = '';
            }
        }
    } else {
        $content = htmlspecialchars(trim($content));
        if (preg_match($pattern, $content)) {
            $content = '';
        }
    }
    return $content;
}

/**
 * 重定向
 *
 * @param $url
 */
function redirect($url)
{
    header('location:' . $url);
    exit;
}

/**
 * 加密字符
 *
 * @param        $str
 * @param string $salt
 * @return string
 */
function encryption($str, $salt = 'pw')
{
    $crypt = crypt($str, $salt);
    $md5 = md5($crypt);
    return sha1($md5);
}


/**
 * 获取ip
 *
 * @return array|false|string
 */
function get_client_ip()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return ($ip);
}

function get_ip_city($ip, $get_address = false)
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

function cas_set($key, $newValue, $moreArray = false)
{
    global $globalServer;
    if (sizeof($globalServer->__get($key)) > 0) {
        $overflow = 0;
        do {
            $old_value = $new_value = $globalServer->__get($key);
            $new_value[] = $newValue;
            $overflow++;
        } while (!$globalServer->cas($key, $old_value, $new_value) && $overflow < 10);
    } else {
        if (!$moreArray) {
            $globalServer->__set($key, array($newValue));
        } else {
            $globalServer->__set($key, $newValue);
        }
    }
}

function warn($mess, $isApp = false, $level = "danger", $data = [])
{
    if (!$isApp) {
        $_SESSION['warn_mess'] = $mess;
        if (in_array($level, ['success', 'warning', 'danger', 'secondary'])) {
            $_SESSION['warn_class'] = 'am-alert-' . $level;
        } else {
            $_SESSION['warn_class'] = '';
        }
    } else {
        $res = ['mess' => $mess, 'level' => $level];
        if (sizeof($data)) {
            foreach ($data as $k => $v) {
                $res[$k] = $v;
            }
        }
        echo json_encode($res);
        die;
    }
}

function clear_warn()
{
    $_SESSION['warn_mess'] = '';
    $_SESSION['warn_class'] = '';
}