<?php

/*=============================================================
 * 实现功能：将redis中存储的金币变化的数据持久化到数据库ms_user_estate中
 =============================================================*/

require_once __DIR__ . '/init.php';

define('R_DB_USER', 0);
define('RK_MODIFY_GOLD', 'modify_gold'); //金币变化数据
define('RK_MODIFY_CREDIT', 'modify_credit'); //钻石变化数据
define('RK_MODIFY_POINT', 'modify_point'); //积分变化数据
define('RK_MODIFY_WINNUM', 'modify_winnum'); //赢局变化数据
define('RK_MODIFY_LOSENUM', 'modify_losenum'); //输局变化数据

/**********捕鱼专用数据*************/
define('RK_MODIFY_EMERALD', 'modify_emerald'); //捕鱼绿宝石变化数据
define('RK_MODIFY_FROZEN', 'modify_frozen'); //道具冰封变化数据
define('RK_MODIFY_EAGLEEYE', 'modify_eagleeye'); //道具鹰眼石变化数据
define('RK_MODIFY_MAXBULLETMUL', 'modify_maxbulletmul'); //捕鱼炮台等级
define('RK_MODIFY_BULLETUNLOCKSITUATION', 'modify_bulletunlocksituation'); //捕鱼炮台解锁情况
define('RK_MODIFY_BULLETUPSUCCESSRATE', 'modify_bulletupsuccessrate'); //捕鱼当前炮台升级成功率
define('RK_MODIFY_BULLETLV', 'modify_bulletlv'); //捕鱼炮台等级
define('RK_MODIFY_DOUBLEBULLETUNLOCKED', 'modify_doublebulletunlocked'); //捕鱼双倍炮是否解锁

$lua_stpop = __DIR__ . '/lua/sortset_pop.lua';

$cmd = intval(time() / 60) % 2 == 0 ? 'zrevrange' : 'zrange';

$redis = PRedis::instance();
$redis->select(R_DB_USER);

$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));//Yaf\Registry::get('__DB_GAMELOG__');
$table = $db->newSelect('ms_user_estate');
$sha = $redis->script('load', file_get_contents($lua_stpop));
$fishTable = $db->newSelect('ms_fish_estate');
//--------------------------------------------------------------------------
//金币变化
//--------------------------------------------------------------------------

//获取金币总数
$count = $redis->zCard(RK_MODIFY_GOLD);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_GOLD, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'gold' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $table->insertUpdate(array('user_id', 'gold'), $insert_data, array(
        'gold' => 'values(gold)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//钻石变化
//--------------------------------------------------------------------------
//获取钻石总数
$count = $redis->zCard(RK_MODIFY_CREDIT);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_CREDIT, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'credit' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $table->insertUpdate(array('user_id', 'credit'), $insert_data, array(
        'credit' => 'values(credit)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//一指赢积分变化
//--------------------------------------------------------------------------
//获取一指赢总数
$count = $redis->zCard(RK_MODIFY_POINT);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_POINT, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'point' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $table->insertUpdate(array('user_id', 'point'), $insert_data, array(
        'point' => 'values(point)'
    ));
    $db->exec($sql);
}


//--------------------------------------------------------------------------
//绿宝石变化
//--------------------------------------------------------------------------
//获取绿宝石总数
$count = $redis->zCard(RK_MODIFY_EMERALD);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_EMERALD, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'emerald' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $table->insertUpdate(array('user_id', 'emerald'), $insert_data, array(
        'emerald' => 'values(emerald)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//赢局变化
//--------------------------------------------------------------------------
//获取赢局总数
$count = $redis->zCard(RK_MODIFY_WINNUM);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_WINNUM, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'win_num' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $table->insertUpdate(array('user_id', 'win_num'), $insert_data, array(
        'win_num' => 'values(win_num)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//输局变化
//--------------------------------------------------------------------------
//获取输局总数
$count = $redis->zCard(RK_MODIFY_LOSENUM);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_LOSENUM, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'lose_num' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $table->insertUpdate(array('user_id', 'lose_num'), $insert_data, array(
        'lose_num' => 'values(lose_num)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//道具冰封变化
//--------------------------------------------------------------------------
//获取冰封总数
$count = $redis->zCard(RK_MODIFY_FROZEN);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_FROZEN, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'frozen' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $fishTable->insertUpdate(array('user_id', 'frozen'), $insert_data, array(
        'frozen' => 'values(frozen)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//道具鹰眼变化
//--------------------------------------------------------------------------
//获取鹰眼总数
$count = $redis->zCard(RK_MODIFY_EAGLEEYE);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_EAGLEEYE, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'eagleeye' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $fishTable->insertUpdate(array('user_id', 'eagleeye'), $insert_data, array(
        'eagleeye' => 'values(eagleeye)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//捕鱼炮台等级
//--------------------------------------------------------------------------
$count = $redis->zCard(RK_MODIFY_MAXBULLETMUL);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_MAXBULLETMUL, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'MaxBulletMul' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $fishTable->insertUpdate(array('user_id', 'MaxBulletMul'), $insert_data, array(
        'MaxBulletMul' => 'values(MaxBulletMul)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//捕鱼炮台解锁情况
//--------------------------------------------------------------------------
$count = $redis->zCard(RK_MODIFY_BULLETUNLOCKSITUATION);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_BULLETUNLOCKSITUATION, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'BulletUnlockSituation' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $fishTable->insertUpdate(array('user_id', 'BulletUnlockSituation'), $insert_data, array(
        'BulletUnlockSituation' => 'values(BulletUnlockSituation)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//捕鱼当前炮台升级成功率
//--------------------------------------------------------------------------
$count = $redis->zCard(RK_MODIFY_BULLETUPSUCCESSRATE);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_BULLETUPSUCCESSRATE, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'BulletUpSuccessRate' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $fishTable->insertUpdate(array('user_id', 'BulletUpSuccessRate'), $insert_data, array(
        'BulletUpSuccessRate' => 'values(BulletUpSuccessRate)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//捕鱼当前炮台升级成功率
//--------------------------------------------------------------------------
$count = $redis->zCard(RK_MODIFY_BULLETLV);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_BULLETLV, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'BulletLv' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $fishTable->insertUpdate(array('user_id', 'BulletLv'), $insert_data, array(
        'BulletLv' => 'values(BulletLv)'
    ));
    $db->exec($sql);
}

//--------------------------------------------------------------------------
//捕鱼双倍炮是否解锁
//--------------------------------------------------------------------------
$count = $redis->zCard(RK_MODIFY_DOUBLEBULLETUNLOCKED);
$per = 1000; //每次处理数量
$times = ceil($count / $per);
for ($i = 0; $i != $times; $i++) {
    $data = $redis->evaluateSha($sha, [RK_MODIFY_DOUBLEBULLETUNLOCKED, $cmd, 0, $per], 2);
    $insert_data = [];
    for ($n = 0; $n <= count($data) - 1; $n += 2) {
        $tmp = [ 'user_id' => $data[$n], 'DoubleBulletUnlocked' => $data[$n + 1] ?? 0 ];
        $insert_data[] = $tmp;
    }
    $sql = $fishTable->insertUpdate(array('user_id', 'DoubleBulletUnlocked'), $insert_data, array(
        'DoubleBulletUnlocked' => 'values(DoubleBulletUnlocked)'
    ));
    $db->exec($sql);
}