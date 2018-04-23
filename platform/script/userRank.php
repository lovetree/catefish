<?php

/*=============================================================
 * 实现功能：排行
 =============================================================*/

require_once __DIR__ . '/init.php';

define('R_DB_USER', 0);
define('RK_RANK_GOLD', 'rank_gold'); //玩家金币
define('PK_RANK_GOLD_COUNT', 100); //排行总数
define('PK_RANK_GOLD_DATE', 'rank_gold_date'); //金币排行榜更新日期

define('RK_RANK_CREDIT', 'rank_credit'); //玩家钻石
define('PK_RANK_CREDIT_COUNT', 100); //排行总数
define('PK_RANK_CREDIT_DATE', 'rank_credit_date'); //钻石排行榜更新日期

define('RK_RANK_EMERALD', 'rank_emerald'); //玩家钻石
define('PK_RANK_EMERALD_COUNT', 100); //排行总数
//define('PK_RANK_EMERALD_DATE', 'rank_emerald_date'); //钻石排行榜更新日期

//define('RK_RANK_POINT', 'rank_point'); //玩家积分
define('PK_RANK_POINT_COUNT', 100); //排行总数
//define('PK_RANK_POINT_DATE', 'rank_point_date'); //积分排行榜更新日期

define('RK_RANK_WINNER', 'rank_winner'); //玩家战绩
define('RK_RANK_WINNER_COUNT', 100); //排行总数

$redis = PRedis::instance();
$redis->select(R_DB_USER);

$log_db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));//Yaf\Registry::get('__DB_GAMELOG__');
#$log_db->newSelect('ms_user_estate');
$main_db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
#$main_db->newSelect('ms_user_info');

//通用查找
function select(string $cmd, int $count, string $date_key, string $rank_key) {
    global $redis;
    global $log_db;
    global $main_db;
    $rank_date = $redis->get($date_key);
    if (!$rank_date || $rank_date != date('Y-m-d')){
        $redis->del($rank_key);
        if ($redis->exists($rank_key)){
            echo $rank_key . "键删除失败";exit;
        }
        //开始同步
        $sql = <<<SQL
            select user_id,$cmd from ms_user_estate where $cmd >= 0 order by $cmd DESC limit 0, $count
SQL;
        $data = $log_db->query($sql);
        var_dump($data);
        if ($data){
            $user_ids = [];
            $rank = [];
            foreach ($data as $v){
                $user_ids[] = $v['user_id'];
                $rank[] = [
                    'user_id' => $v['user_id'],
                    $cmd => $v[$cmd]
                ];
            }
            $user_ids = implode(',', $user_ids);
            $sql = <<<SQL
                    select user_id,nickname,avatar from ms_user_info where user_id in($user_ids);
SQL;
            $list = $main_db->query($sql);
            var_dump($list);
            if ($list){
                $user = [];
                foreach ($list as $v){
                    $user[$v['user_id']] = $v;
                }
                $re_rank = array_reverse($rank);
                foreach ($re_rank as $v){
                    if (!isset($user[$v['user_id']])){
                        continue;
                    }
                    $rank = [];
                    $rank['user_id'] = $v['user_id'];
                    $rank['nickname'] = $user[$v['user_id']]['nickname'];
                    $rank['amount'] = $v[$cmd];
                    $rank['avatar'] = $user[$v['user_id']]['avatar'];
                    $redis->lPush($rank_key, json_encode($rank));
                }
                $redis->set($date_key, date('Y-m-d'));
            }
        }
    }
}

//--------------------------------------------------------------------------
//金币排行
//--------------------------------------------------------------------------
select('gold', PK_RANK_GOLD_COUNT, PK_RANK_GOLD_DATE, RK_RANK_GOLD);

//--------------------------------------------------------------------------
//砖石排行
//--------------------------------------------------------------------------
select('credit', PK_RANK_CREDIT_COUNT, PK_RANK_CREDIT_DATE, RK_RANK_CREDIT);

//--------------------------------------------------------------------------
//绿宝石排行
//--------------------------------------------------------------------------
select('emerald', PK_RANK_EMERALD_COUNT, PK_RANK_EMERALD_DATE, RK_RANK_EMERALD);

//--------------------------------------------------------------------------
//积分排行
//--------------------------------------------------------------------------
select('point', PK_RANK_POINT_COUNT, PK_RANK_POINT_DATE, RK_RANK_POINT);

//--------------------------------------------------------------------------
//战绩排行
//--------------------------------------------------------------------------