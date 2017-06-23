<?php
/**
 * Created by PhpStorm.
 * User: jijg
 * Date: 2017-06-23
 * Time: 15:44
 */
# 强制将页面输出成UTF-8
header('Content-Type:text/html;charset=UTF-8');

# 不限制PHP运行时间
set_time_limit(0);
# 时间格式
date_default_timezone_set('PRC');
define('__TIME__', microtime(true));
# 输出缓充
define('__OB__', ob_start());

# 登入信息
define('__SESS__', session_start());

# 定义根目录
define('__ROOT__', str_replace("\\", "/", __DIR__) . '/');
include_once __DIR__ . '/lib/function.php';
/**
 * 语音合成
 * App ID: 9797280
 * API Key: 21Iq24TyXSa6bPHgzmcjRCoro
 * Secret Key: 717c38f6b86bf39bc5d756196cdcbf2bf
 */

$url = "http://tsn.baidu.com/text2audio";
$token_url = "https://openapi.baidu.com/oauth/2.0/token";
$ini_path = __ROOT__ . 'api.env';
if (!is_file($ini_path))
    exit('ini file not find');

$ini_info = parse_ini_file($ini_path);

$appKey = isset($ini_info['API_KEY']) ? trim($ini_info['API_KEY']) : false;
$SecretKey = isset($ini_info['Secret_Key']) ? trim($ini_info['Secret_Key']) : false;
if (!$appKey)
    exit('no appKey');
if (!$SecretKey)
    exit('no SecretKey');

##获取服务器地址
$mac = new GetMacAddr(PHP_OS);
$mac_addr = $mac->mac_addr;

//token
$token_file = __ROOT__ . 'tok.php';

$token_info = is_file($token_file) ? include($token_file) : false;
if (!$token_info) {
    //重新请求 获取token 并保存到文件
    $post_data = array('grant_type' => 'client_credentials', 'client_id' => $appKey, 'client_secret' => $SecretKey);
    $token_info = curl($token_url, false, 'UTF-8', $post_data);
    $token_info = !empty($token_info) ? json_decode($token_info, true) : array();
    $token_info['expires'] = $token_info['expires_in'] + time();//有效期截止
    $cache = '<?php return ' . var_export($token_info, true) . ';';
    file_put_contents($token_file, $cache);
}

$token_info['url'] = $url;
$token_info['mac'] = $mac_addr;

echo json_encode($token_info);