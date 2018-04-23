<?php

/*=============================================================
 * 实现功能：捕鱼桌子情况
 =============================================================*/

require_once __DIR__ . '/init.php';

define('R_DB_USER', 0);
define('PK_FISH_TABLE_STOCK', 'fish_table_stock');    //键格式 - 捕鱼桌子情况(hash表)

$redis = PRedis::instance();
$redis->select(R_DB_USER);
$data = $redis->hGetAll(PK_FISH_TABLE_STOCK);
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
if (!$data) return;

$beans = [];
$beans['data'] = json_encode($data);
$beans['c_time'] = time();
$select = $db->newSelect('ms_fish_table_stock');
$select->setData($beans);
$db->exec($select->insertSql());