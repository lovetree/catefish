<?php

/**
 * 配置信息快捷获取
 * @param string $key
 * @param string $default
 * @return string
 */
function config_item(string $key, $default = null) {
    static $cfg = NULL;
    if (is_null($cfg)) {
        $cfg = Yaf\Application::app()->getConfig();
    }
    return $cfg->get($key) ?? $default;
}

/**
 * 系统日志记录
 * @param string $msg 日志内容
 * @param int $level 记录等级
 */
function logMessage($msg, $level = LOG_DEBUG) {
    $priority = config_item('log.priority');
    $log_file = config_item('log.logfile');
    if ($level > $priority) {
        return;
    }
    $logmsg = sprintf("[%s] %s\n", date('Y/m/d H:i:s'), $msg);
    error_log($logmsg, 3, $log_file);
}

/**
 * 短信发送函数
 * @param array $params 一个包含短信接口相关参数的数组
 * @return mixed 因网络问题发送失败的返回false，其他情况返回一个数组对象
 */
function sms($params) {
    $apikey = config_item('sms.apikey');
    list($mobile, $content) = $params;
    $ch = curl_init();
    $url = 'http://apis.baidu.com/kingtto_media/106sms/106sms';
    $query_string = http_build_query(array(
        'mobile' => $mobile,
        'content' => $content,
        'tag' => 2
    ));
    $header = array(
        "apikey: {$apikey}",
    );
    try {
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);   //超时时间为1秒
        // 执行HTTP请求
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $query_string);
        $res = curl_exec($ch);
    } catch (Exception $ex) {
        logMessage($ex->getMessage(), LOG_ERR);
        return false;
    }
    return $res;
}

/**
 * 取出数组中的某些列组成一个新的数组
 * @param array $array
 * @param mixed $keys
 * @return array
 */
function array_fetch(array $array, ...$keys): array {
    is_array($keys[0]) && ($keys = $keys[0]);
    return array_intersect_key($array, array_flip($keys));
}

/**
 * 更换数组键名
 * @param array $array
 * @param array $keys
 * @return array
 */
function array_change_keys(array $array, array $keys, $bAll = false) {
    if(!$bAll){
        $array = array_fetch($array, array_keys($keys));
    }
    $ret = [];
    foreach ($array as $k => $v) {
        if(isset($keys[$k])){
            $ret[$keys[$k]] = $v;
        }else if($bAll){
            $ret[$k] = $v;
        }
    }
    return $ret;
}

/**
 * 生成一个唯一标识
 */
function unique_id() {
    return md5(getenv('PATH') . time() . uniqid());
}

/**
 * 判断json解析是否正常
 * @return boolean
 */
function json_error() {
    if (json_last_error() == JSON_ERROR_NONE) {
        //json 解析错误
        return false;
    }
    return true;
}

/**
 * 多维数组按键排序
 * @param array $array
 * @param int $sort_flags
 * @return boolean
 */
function array_ksort(array &$array, int $sort_flags = SORT_REGULAR) {
    $status = true;
    if (ksort($array, $sort_flags)) {
        array_walk($array, function(&$item) use($status) {
            if (is_array($item)) {
                $status &= array_ksort($item);
            }
        });
        return true;
    }
    return false;
}

/**
 * 数字转耗时
 * @param int $duration
 * @return string
 */
function int2duration(int $duration): string {
    $h = intval($duration % 86400 / 3600);
    $m = intval($duration % 3600 / 60);
    $s = intval($duration % 60);
    return empty($h) ? sprintf('%s:%s', $m, $s) : sprintf('%s:%s:%s', $h, $m, $s);
}

/**
 *
 * @param $userId
 * @param $data
 */
function realTimePush($userId, $data){
    return file_get_contents(config_item('push.ip').'/phppushtouser?userid='. $userId . '&phpdata=' . $data);
}