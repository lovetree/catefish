<?php
/**
 * Created by PhpStorm.
 * User: grace
 * Date: 2017/6/2
 * Time: 10:57
 */
require_once __DIR__ . '/init.php';
//数据库
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
echo  '统计用户存量';
var_dump(estate());
exit;

function estate(){
    global $db;
    $sql_safe = 'SELECT
Sum(ms_safe.safe_gold) as gold
FROM
ms_safe';
    $safe = $db->search($sql_safe)[0]['gold'];
    $sql_estate = 'SELECT
Sum(ms_user_estate.gold) AS gold,
Sum(ms_user_estate.credit) AS credit,
Sum(ms_user_estate.emerald) AS emerald
FROM
ms_user_estate 
LEFT JOIN ms_user on ms_user.id = ms_user_estate.user_id
WHERE ms_user.username IS NOT NULL ';
    $result = $db->search($sql_estate)[0];
    $sql_pop  = 'SELECT
Sum(ms_gift_popularity.popularity) AS popularity
FROM
ms_gift_popularity';
    $popularity = $db->search($sql_pop)[0]['popularity'];
    $date_time = strtotime('today')-86400;

    $data = array($result['gold']+$safe,$result['gold'],$safe,$popularity,$result['credit'],$result['emerald'],$date_time);
    $data = implode(',',$data);
    $insert = 'insert into ms_estate_stat (gold,estate_gold,safe_gold,popularity,credit,emerald,stat_date) VALUES ('.$data.')';
    $db->exec($insert);
    return $data;


}