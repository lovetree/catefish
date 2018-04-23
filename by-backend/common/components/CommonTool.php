<?php
namespace common\components;

class CommonTool{
    static $counryCurrencyArr = [
        'us'=>'$',
        'fr'=>'€',
        'de'=>'€',
        'it'=>'€',
        'uk'=>'￡'
    ];
    
    /**
     * 计算时间差
     * @param unknown $begin_time
     * @param unknown $end_time
     * 
     * @demo
     * $timediff = timediff( strtotime( "2016-10-28" ), strtotime( "2016-10-29" ) );
     *  print_r( $timediff );
     * 
     */
    static function timediff( $begin_time, $end_time )
    {
        if ( $begin_time < $end_time ) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        
        $timediff = $endtime - $starttime;
        $days = intval( $timediff / 86400 );
        $remain = $timediff % 86400;
        $hours = intval( $remain / 3600 );
        $remain = $remain % 3600;
        $mins = intval( $remain / 60 );
        $secs = $remain % 60;
        $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
        return $res;
    }
    
    /**
     * 根据url提取国家代码
     * @param unknown $url
     * @return string
     */
    static function getCountryFromUrl($url){
        $url = str_ireplace('http://', '' , $url);
        $str = substr($url, 0 ,strpos($url, '/')) ;
        $site = substr($str, strrpos($str, '.')+1);
        return $site == 'com' ? 'US' : $site;
    }
}