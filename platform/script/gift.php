<?php

/*=============================================================
 * 实现功能：排行
 =============================================================*/

require_once __DIR__ . '/init.php';

define('R_DB_USER', 0);
define('RK_USER_INFO', 'info@');    //键格式 - 用户基本数据(hash表)
define('GIFT_GIVE', 'gift_give'); //礼物赠送
define('RANK_POPULARITY', 'rank_popularity');  //人气排行
define('GIFT_LIST', 'gift_list');  //礼物，(hash)
define('GIFT_RECORD', 'gift_record');  //礼物记录，(hash)

$redis = PRedis::instance();
$redis->select(R_DB_USER);

$main_db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));

$lua_stpop = __DIR__ . '/lua/sortset_pop.lua';

$cmd = 'zrange';

$redis = PRedis::instance();
$redis->select(R_DB_USER);

$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
$sha = $redis->script('load', file_get_contents($lua_stpop));
//--------------------------------------------------------------------------
//金币变化
//--------------------------------------------------------------------------

//获取金币总数
$count = $redis->zCard(GIFT_GIVE);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [GIFT_GIVE, $cmd, 0, $per], 2);
    $insert_data = [];
    $popularity_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $info = json_decode($data[$n], TRUE);
        $info['ip'] = $info['ip'] ? ip2long($info['ip']) : "";
        $user_id = $info['user_id'];
        $touid = $info['to_user_id'];
        
        $user_info = $redis->hMget(RK_USER_INFO . $user_id, array('nickname'));
        $touid_info = $redis->hMget(RK_USER_INFO . $touid, array('nickname', 'popularity'));
        
        //同步排行
        $redis->zRem(RANK_POPULARITY, $touid);
        $redis->zAdd(RANK_POPULARITY, $touid_info['popularity'], $touid);
        //插入记录
        $record = [];
        $record['unit'] = $info['unit'];
        $record['g_type'] = $info['g_type'];
        $record['name'] = $info['name'];
        $record['time'] = $info['c_time'];
        $record['to_nickname'] = $touid_info['nickname'];
        $record['nickname'] = $user_info['nickname'];
        $record['count'] = $info['count'];
        $record['popularity'] = $info['popularity'];
        
        $gift_record = $redis->hMget(GIFT_RECORD, array($user_id, $touid));
        $record['type'] = 1; //赠送者
        $u_redis = [];
        $u_redis[] = $record;
        if ($gift_record[$user_id]){
            $u_record = json_decode($gift_record[$user_id], TRUE);
            $u_redis = array_merge($u_record, $u_redis);
        }
        $redis->hMset(GIFT_RECORD, array($user_id => json_encode($u_redis)));

        $record['type'] = 2; //被赠送者
        $t_redis = [];
        $t_redis[] = $record;
        if ($gift_record[$touid]){
            $t_record = json_decode($gift_record[$touid], TRUE);
            $t_redis = array_merge($t_record, $t_redis);
        }
        $redis->hMset(GIFT_RECORD, array($touid => json_encode($t_redis)));
        
        $insert_data[] = [
            'user_id' => $info['user_id'],
            'to_user_id' => $info['to_user_id'],
            'g_type' => $info['g_type'],
            'popularity' => $info['popularity'],
            'gold' => $info['gold'],
            'count' => $info['count'],
            'ip' => $info['ip'],
            'c_time' => $info['c_time']
        ];
        $popularity_data[$info['to_user_id']] = $touid_info['popularity'];
    }
    
    //批量插入日志
    $log = $db->newSelect('ms_gift_give_log');
    $sql = $log->insert(array('user_id', 'to_user_id', 'g_type', 'popularity', 'gold', 'count', 'ip', 'c_time'), $insert_data);
    $db->exec($sql);
    
    $insert_data = [];
    foreach ($popularity_data as $k => $v){
        $insert_data[] = [
            'user_id' => $k,
            'popularity' => $v,
            'u_time' => time()
        ];
    }
    
    $table = $db->newSelect('ms_gift_popularity');
    $sql = $table->insertUpdate(array('user_id', 'popularity', 'u_time'), $insert_data, array(
        'popularity' => 'values(popularity)', 'u_time' => 'values(u_time)'
    ));
    $db->exec($sql);
}