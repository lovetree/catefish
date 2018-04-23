<?php
/**
 * 计算代理流水
 */
require_once __DIR__ . '/init.php';
$COUNT = 100;

echo "[".date('YmdHis')."]流水入库开始\n";

//redis
$redis = PRedis::instance();
//数据库
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));

//从队列获取金币流水
$insertArr = [];
$cnt = 0;
while(true){
    $redis->select(R_GAME_DB);
    $temp = $redis->rPop(RK_GAME_GOLD_FLOW);

    if($temp === false){
        break;
    }

    $cnt ++;
    $tempArr = explode(':', $temp);
    if(count($tempArr) < 2) continue;

    $userId = $tempArr[0];
    //代理 redis不存在数据则默认为官方
    $redis->select(R_AGENT_DB);
    if($redis->exists(RK_AL_USER_AGENT.$userId)){
        $agentInfo = $redis->hGetAll(RK_AL_USER_AGENT.$userId);
    }else{
        $agentInfo = [
            'agent_id' => 0,
            'agent_pid' => 0,
            'percent' => 0
        ];
    }

    $insertArr[] = [
        $userId,
        $agentInfo['agent_id'],
        $agentInfo['agent_pid'],
        $agentInfo['percent'],
        $tempArr[1],
        time(),
        1
    ];
    //上级代理 如果是官方则跳过
    if($agentInfo['agent_pid'] == 0) continue;
    $agentList = unserialize($redis->get(RK_AS_AGENT_PARENT.$agentInfo['agent_id']));
    foreach ($agentList as $agent) {
        $insertArr[] = [
            $userId,
            $agent['agent_id'],
            $agent['agent_pid'],
            $agent['percent'],
            $tempArr[1],
            time(),
            0
        ];
    }

    //入库
    if(count($insertArr) == $COUNT){
        importFlowData($db, $insertArr);

        $insertArr = [];
    }
}

//队列结束后入库（未满$COUNT的情况）
if(!empty($insertArr)) importFlowData($db, $insertArr);

echo "[".date('YmdHis')."]流水入库结束，插入数据".$cnt."\n";

function importFlowData($db, $insertArr){
    $select = $db->newSelect('ms_income_flow')->insert([
        'user_id',
        'agent_id',
        'agent_pid',
        'percent',
        'gold',
        'created_at',
        'is_my_player'
    ], $insertArr);
    $db->exec($select);
}





