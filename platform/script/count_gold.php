<?php 
require_once __DIR__.'/init.php';
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
$start = strtotime(date('Y-m-d',time()) ."-1 days");
$end = $start+60*60*24-1;
$res = $db->search("select gold_change,type,create_time from ms_gold_log where create_time between ".$start." and ".$end);
$pre_time = $start-60*60*24;
$pre_gold_array = $db->search("select * from ms_count_gold_day where create_time = ".$pre_time." limit 1");
$pre_gold = 0;
foreach ($pre_gold_array as $v) {
	$pre_gold = $v['credit_gold']+$v['admin_gold']+$v['sys_gold']+$v['gift_gold']+$v['sys_produce']+$v['renqi_gold']+$v['other']+$v['sys_recover']+$v['pre_gold'];
}
$credit_gold = 0;//钻石兑换金币
$admin_gold = 0;//后台添加金币
$sys_gold = 0;//系统赠送金币
$renqi_gold = 0;//人气兑换金币
$gift_gold = 0;//礼物赠送金币
$other = 0;//其他
$sys_produce = 0;//系统生产金币
$sys_recover = 0;//系统回收金币
if($res){
	foreach ($res as $k => $v) {
		if($v['type']==2){ //钻石兑换金币
			$credit_gold+=$v['gold_change'];
		}elseif($v['type']==4){ //后台添加金币
			$admin_gold+=$v['gold_change'];
		}elseif(in_array($v['type'], ['3,5,6,8'])){ //系统赠送金币
			$sys_gold+=$v['gold_change'];
		}elseif($v['type']==10){
			$gift_gold+=$v['gold_change'];
		}elseif($v['type']==8){
			$renqi_gold+=$v['gold_change'];
		}elseif($v['type']==0){
			$other+=$v['gold_change'];
		}
	}
}
$data = [
	'credit_gold'=>$credit_gold,
	'admin_gold'=>$admin_gold,
	'sys_gold'=>$sys_gold,
	'renqi_gold'=>$renqi_gold,
	'gift_gold'=>$gift_gold,
	'other'=>$other,
	'sys_produce'=>$sys_produce,
	'sys_recover'=>$sys_recover,
	'pre_gold'=>$pre_gold,
	'create_time'=>$start
];
$sql = "insert into ms_count_gold_day (".implode(',',array_keys($data)).") VALUES (".implode(',',$data).")";
$db->exec($sql);