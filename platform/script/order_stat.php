<?php
/**
 * Created by PhpStorm.
 * User: grace
 * Date: 2017/5/31
 * Time: 18:06
 */

/**
 * 充值日报
 */
require_once __DIR__ . '/init.php';
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
$result = rechargeLog();
var_dump( $result);
exit;
function rechargeLog(){
    global $db;
    $select = 'select sum(totalf_fee) as total,sum(actually_fee) as actually,sum(coupon_fee) as coupon ,paytype FROM ms_order  where (transaction_id is NOT NULL ) and (update_date <= '.strtotime('today').') and (update_date >= '.(strtotime('today')-86400).') AND status = 1 group by paytype';

    $data = $db->search($select);
    $create_at = strtotime('today')-86400;
    if(is_array($data)&&$data){
        $chongzhi = 0;
        $dianka = 0;
        $bank = 0;
        $ali = 0;
        $qq = 0;
        $wx = 0;
        $other = 0;
        $total = 0;
        $actually =0 ;
        $coupon =0;
        foreach ($data as &$item) {
            switch ($item['paytype']) {
                case 1:
                    $chongzhi += $item['total'];
                    break;
                case 2:
                    $dianka += $item['total'];
                    break;
                case 4:
                    $bank += $item['total'];
                    break;
                case 401:
                    $ali += $item['total'];
                    break;
                case 402:
                    $qq += $item['total'];
                    break;
                case 403:
                    $wx += $item['total'];
                    break;
                default :
                    $other += $item['total'];
            }
            $total += $item['total'];
            $actually += $item['actually'];
            $coupon += $item['coupon'];
        }
        $result = [$total,$actually,$coupon,$chongzhi,$dianka,$bank,$ali,$qq,$wx,$other,$create_at];

    }else{
        $result = [0,0,0,0,0,0,0,0,0,0,$create_at];
    }

    $res = addRechargeData($result);

    echo  $res;

}

//插数据
function addRechargeData( $dataArr){
    global $db;
    $select = "insert into ms_order_stat (total,actually,coupon,chongzhi,dianka,bank,ali,qq,wx,other,stat_date) VALUES (".implode(',',$dataArr).")";

    return $db->exec($select);
}
