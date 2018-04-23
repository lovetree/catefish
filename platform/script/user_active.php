<?php
/**
 * 用户活跃人数
 */
require_once __DIR__ . '/init.php';
//数据库
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
echo  '统计活跃人数'.censusUser().'人';
//echo  censusUser();

function censusUser(){
    global $db;
    $start = strtotime('today')-86400;
    $end = strtotime('today');
    $sql = 'select count(id) as nums from ms_user_info WHERE last_time >='.$start.' and last_time <'.$end;
    $nums = $db->search($sql);
    $nums = $nums[0]['nums']?$nums[0]['nums']:0;
    $insert = 'insert into ms_user_active (nums,stat_time) VALUES ('.$nums.','.$start.')';
    $db->exec($insert);
    return $nums;

}
