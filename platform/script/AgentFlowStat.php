<?php
/**
 * 代理统计 * 1 * * *
 */
require_once __DIR__ . '/init.php';

echo '[' . date("YmdHis") . "]开始统计数据\n";

//数据库
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
//查当天
$start_date = strtotime(date('Y-m-d', strtotime('-1 day')));
$end_date = strtotime(date('Y-m-d'));
$select = $db->newSelect('ms_income_flow')
    ->select('sum(abs(gold)) as total_gold, SUM(abs(gold) * percent)/1000 AS income, agent_id, is_my_player', true)
    ->where('created_at', $start_date, '>=')
    ->where('created_at', $end_date, '<')
    ->group('agent_id','is_my_player');
$result = $db->fetchAll($select)->toArray();
//写入统计表
$insertArr = [];
$tempArr = [];

if(empty($result)) {
    echo '[' . date("YmdHis") . "]统计结束，没有数据\n";
    return;
}

//区分直属玩家、代理玩家
foreach ($result as $item) {
    if($item['is_my_player'] == 1){
        $tempArr[$item['agent_id']]['my_player_flow'] = $item['total_gold'];
        $tempArr[$item['agent_id']]['my_player_income'] = $item['income'];
    }else{
        $tempArr[$item['agent_id']]['agent_player_flow'] = $item['total_gold'];
        $tempArr[$item['agent_id']]['agent_player_income'] = $item['income'];
    }
}
//整理插入数据
$stat_date = strtotime(date('Y-m-d', $start_date));
foreach ($tempArr as $agentId=>$temp){
    $insertArr[] = [
        isset($temp['my_player_flow']) ? $temp['my_player_flow'] : 0,
        isset($temp['my_player_income']) ? $temp['my_player_income'] : 0,
        isset($temp['agent_player_flow']) ? $temp['agent_player_flow'] : 0,
        isset($temp['agent_player_income']) ? $temp['agent_player_income'] : 0,
        $stat_date,
        $agentId,
        time()
    ];

    //更新代理余额
    $changeGold = (isset($temp['my_player_income']) ? $temp['my_player_income'] : 0)
        + (isset($temp['agent_player_income']) ? $temp['agent_player_income'] : 0);
    $sql = "update ms_db_agent.px_agent set a_gold = a_gold +" . $changeGold . " where id=$agentId";
    $db->exec($sql);
}

$select = $db->newSelect('ms_income_stat_by_date')->insert([
    'my_player_flow',
    'my_player_income',
    'agent_player_flow',
    'agent_player_income',
    'stat_date',
    'agent_id',
    'created_at'], $insertArr);

$db->exec($select);

echo '[' . date("YmdHis") . "]统计结束，新增数据" .count($insertArr). "条\n";