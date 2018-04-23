<?php
require_once __DIR__ . '/init.php';

//redis
$redis = PRedis::instance();
//db
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));

//同步用户
$tempArr = [];
while ($user = $redis->lPop(RK_AL_REG_USER)){
    $tempArr[] = json_decode($user, true);
    


}

echo "[".date('Y-m-d H:i:s')."]完成".count($tempArr) . "个用户同步\n";

//同步代理
$tempArr = [];
while ($agent = $redis->lPop(RK_AL_REG_AGENT)){
    $tempArr[] = json_decode($agent, true);
}
$db->exec($db->newSelect('ms_agent')->insert([
    'id',
    'a_username',
    'a_password',
    'a_pid',
    'a_percent',
    'a_pro_aurl',
    'a_pro_purl',
    'a_pro_abarcode',
    'a_pro_pbarcode',
    'a_ctime',
    'updated_at'], array_merge($tempArr, [time(), time()])));
echo "[".date('Y-m-d H:i:s')."]完成".count($tempArr) . "个代理同步\n";
