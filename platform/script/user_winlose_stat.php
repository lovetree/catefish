<?php
/**
 * 玩家输赢统计
 */
require_once __DIR__ . '/init.php';
$COUNT = 100;

echo "[".date('YmdHis')."]玩家输赢统计计算开始\n";

//数据库
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db_log'));

$start_date = strtotime(date('Y-m-d', strtotime('-1 day')));
$end_date = strtotime(date('Y-m-d'));

//赢钱
calWinGold();

//输钱
calLoseGold();

//服务费
callServiceFee();

echo "[".date('YmdHis')."]统计结束\n";


function calWinGold(){
    global $db;
    $select = getSql('wingold', '>');
    echo "嬴钱统计:".$select->toString() ."\n";
    $result = $db->fetchAll($select)->toArray();

    if(empty($result)) return;

    $insertDataArr = [];
    foreach ($result as  $item){
        $user = $db->fetch($db->newSelect('ms_user_losewin_stat')
            ->select('main_table.id')
            ->where('user_id', $item['user_id'])
            ->limit(0, 1));
        //新用户新增数据
        if(empty($user)){
            $insertDataArr[] = [
                $item['user_id'],
                $item['wingold']
            ];
        }else{
            updateWinloseData($item['user_id'], 'win_gold', $item['wingold']);
        }
    }

    if(!empty($insertDataArr)){
        addWinloseData(['user_id', 'win_gold'], $insertDataArr);
    }

    echo "嬴钱统计结果：". count($result) . "条记录\n";
}


function calLoseGold(){
    global $db;
    $select = getSql('losegold', '<');
    echo "输钱统计:".$select->toString() ."\n";
    $result = $db->fetchAll($select)->toArray();

    $dataArr = [];
    if(empty($result)) return;

    foreach ($result as  $item){
        $user = $db->fetch($db->newSelect('ms_user_losewin_stat')
            ->select('id')
            ->where('user_id', $item['user_id'])
            ->limit(0, 1));
        //新用户新增数据
        if(empty($user)){
            $insertDataArr[] = [
                $item['user_id'],
                $item['losegold']
            ];
        }else{
            updateWinloseData($item['user_id'], 'lose_gold', $item['losegold']);
        }
    }

    if(!empty($insertDataArr)){
        addWinloseData(['user_id', 'lose_gold'], $insertDataArr);
    }

    echo "输钱统计结果：". count($result) . "条记录\n";
}

function callServiceFee(){
    global $db;
    $select = getSql('fee', '<');
    echo "服务费统计:".$select->toString() ."\n";
    $result = $db->fetchAll($select)->toArray();

    $dataArr = [];
    if(empty($result)) return;

    foreach ($result as  $item){
        $user = $db->fetch($db->newSelect('ms_user_losewin_stat')
            ->select('id')
            ->where('user_id', $item['user_id'])
            ->limit(0, 1));
        //新用户新增数据
        if(empty($user)){
            $insertDataArr[] = [
                $item['user_id'],
                $item['fee'],
                time()
            ];
        }else{
            updateWinloseData($item['user_id'], 'service_fee', $item['fee']);
        }
    }

    if(!empty($insertDataArr)){
        addWinloseData(['user_id', 'service_fee', 'created_at'], $insertDataArr);
    }

    echo "服务费统计结果：". count($result) . "条记录\n";
}

function getSql($column, $compareSym){
    global $db, $start_date, $end_date;
    $selectCol = 'count(1)';
    if(stripos($column, 'gold') !== false){
        $selectCol = 'abs(sum(win_gold))';
    }

    $select = $db->newSelect('ms_user_log')
        ->select('user_id')
        ->select($selectCol . ' as '. $column, true)
        ->where('created_date', $start_date, '>=')
        ->where('created_date', $end_date, '<')
        ->where('win_gold', 0, $compareSym)
        ->group('user_id');

    return $select;
}

//插数据
function addWinloseData($fieldArr, $dataArr){
    global $db;

    $select = $db->newSelect('ms_user_losewin_stat')->insert($fieldArr,  $dataArr);

    return $db->exec($select);
}

//更新数据
function updateWinloseData($userId, $column, $val){
    global $db;

    $setStr = $column . '=' . $column . '+' . intval($val);
    $sql = 'update ms_user_losewin_stat set '.$setStr
        .'  where user_id=' . $userId;

    return $db->exec($sql);
}





