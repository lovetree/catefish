<?php
/**
 * Created by PhpStorm.
 * User: grace
 * Date: 2017/6/1
 * Time: 15:26
 */

require_once __DIR__ . '/init.php';
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
;
echo do_deal();

function do_deal(){
    global $db;
    $end = date('Y-m-d H:i:s',time()-10*86400);
    $select = 'update ms_game_email set status = -1  WHERE created_time <= "'.$end.'"';
    return $db->search($select);
}