<?php
namespace Gamemt;
class StatisticsModel extends \BaseModel {
    
    public function user($t){
        $db = $this->DB();
        $totalArr = array();
        //获取用户的总数
        $sql = <<<SQL
                select count(*) as count from ms_user
SQL;
        $count = $db->search($sql);
        $totalArr['total_count'] = $count[0]['count'];
        
        //获取用户条件的总数
        $where = '';

        $this->whereUserTime($t, 'created_at', $where);

        $sql = <<<SQL
                select count(*) as count from ms_user where 1=1 $where
SQL;
        $count = $db->search($sql);
        $totalArr['new_count'] = $count[0]['count'];
        
        //获取用户条件的列表
        $sql = <<<SQL
                select id,created_at from ms_user where 1=1 $where
SQL;
        $list = $db->search($sql);

        $timeDiff = array();
        $day = null;
        $line_data = array();
        $totalList = array();
        $step = 0;
        if(strpos($t, '@')){
            $timeArr = explode('@', $t);
            $day = $this->timediff(strtotime($timeArr[0]), strtotime($timeArr[1]));
        }

        if($t=='' || $t=='today' || $t=='yesterday' || $day===0){
            $temp = '';
            for($i=0; $i<=23; $i++) {

                $key = $i < 10 ? '0'.$i : $i;
                $line_data[$i] = array('number'=>0,'date'=>$key.':00');
                
                if(!empty($list)){
                    foreach ($list as $k => $report) {
                        if(empty($temp)){
                           $temp = date('Y-m-d', $report['created_at']);
                        }
                        if($i == date('H', $report['created_at'])){
                            $line_data[$i]['number']++;
                        }
                    }
                }
            }
            if(!empty($temp)){
                $totalList = array(
                    array(
                        'date' => $temp,
                        'number' => $totalArr['new_count'],
                    )
                );
            }
            
            $step = 1;
        }else{

            $num = $t=='seven' ? 6 : ($t=='month' ? 29 : $day);
            $start_time = $t=='seven' || $t=='month' ? date('Y-m-d',strtotime("-".$num." day")) : '';
            
            if(!empty($timeArr)){
                if($timeArr[0] <= $timeArr[1]){
                    $start_time = date('Y-m-d',strtotime($timeArr[0]));
                }else{
                    $start_time = date('Y-m-d',strtotime($timeArr[1]));
                }
            }
                
            for($i=0; $i<($num+1); $i++) {

                $curDate = date('Y-m-d', strtotime($start_time)+86400*$i);
                $line_data[$i] = array('number'=>0,'date'=>$curDate);

                if(!empty($list)){
                    foreach ($list as $k => $report) {
                        if($curDate == date('Y-m-d',$report['created_at'])){
                            $line_data[$i]['number']++;
                        }
                    }
                }
            }

            $totalList = array_reverse($line_data);
            $step = $num < 10 ? 1 : ($num < 20 ? 2 : ($num < 30 ? 3 : ($num < 60 ? 6 : 10)));
        }
        $return = [];
        $return['line_data'] = $line_data;
        $return['step'] = $step;
        $return['totalArr'] = $totalArr;
        $return['totalList'] = $totalList;
        return $return;
    }

    public function active($t){
        $db = $this->DB();
        $totalArr = array();
        //获取用户的总数
        $sql = <<<SQL
                select count(*) as count from ms_user
SQL;
        $count = $db->search($sql);
        $totalArr['total_count'] = $count[0]['count'];

        //获取用户条件的总数
        $where_stat = '';
        $where_info = '';

        $this->whereUserTime($t, 'stat_time', $where_stat);
        $this->whereUserTime($t, 'last_time', $where_info);
        $sql = <<<SQL
                select count(*) as count from ms_user_info where 1=1 $where_info
SQL;
        $count = $db->search($sql);
        $totalArr['new_count'] = $count[0]['count'];

        //获取用户条件的列表
        $sql = <<<SQL
                select id,last_time from ms_user_info where 1=1 $where_info
SQL;
        $list = $db->search($sql);
        $sql_date = <<<SQL
                select * from ms_user_active where 1=1 $where_stat
SQL;
        $stat_list = $db->search($sql_date);
//        var_dump($sql_date);
//        exit;
        $timeDiff = array();
        $day = null;
        $line_data = array();
        $totalList = array();
        $step = 0;
        if(strpos($t, '@')){
            $timeArr = explode('@', $t);
            $day = $this->timediff(strtotime($timeArr[0]), strtotime($timeArr[1]));
        }

        if($t=='' || $t=='today'  || $day===0){
            $temp = '';
            for($i=0; $i<=23; $i++) {

                $key = $i < 10 ? '0'.$i : $i;
                $line_data[$i] = array('number'=>0,'date'=>$key.':00');

                if(!empty($list)){
                    foreach ($list as $k => $report) {
                        if(empty($temp)){
                            $temp = date('Y-m-d', $report['last_time']);
                        }
                        if($i == date('H', $report['last_time'])){
                            $line_data[$i]['number']++;
                        }
                    }
                }
            }
            if(!empty($temp)){
                $totalList = array(
                    array(
                        'date' => $temp,
                        'number' => $totalArr['new_count'],
                    )
                );
            }

            $step = 1;
        }else{

            $num = $t=='seven' ? 6 : ($t=='month' ? 29 : $day);
            $start_time = $t=='seven' || $t=='month' ? date('Y-m-d',strtotime("-".$num." day")) : '';

            if(!empty($timeArr)){
                if($timeArr[0] <= $timeArr[1]){
                    $start_time = date('Y-m-d',strtotime($timeArr[0]));
                }else{
                    $start_time = date('Y-m-d',strtotime($timeArr[1]));
                }
            }

            for($i=0; $i<($num+1); $i++) {

                $curDate = date('Y-m-d', strtotime($start_time)+86400*$i);
                $line_data[$i] = array('number'=>0,'date'=>$curDate);

                if(!empty($stat_list)){
                    foreach ($stat_list as $k => $report) {
                        if($curDate == date('Y-m-d',$report['stat_time'])){
                            $line_data[$i]['number']=$report['nums'];
                        }
                    }
                }
            }

            $totalList = array_reverse($line_data);
            $step = $num < 10 ? 1 : ($num < 20 ? 2 : ($num < 30 ? 3 : ($num < 60 ? 6 : 10)));
        }

        $return = [];
        $return['line_data'] = $line_data;
        $return['step'] = $step;
        $return['totalArr'] = $totalArr;
        $return['totalList'] = $totalList;
        return $return;
    }

    /**
     * 获取存量统计信息
     */
    function estateStat($t){
        $db = $this->DB();
        //获取用户条件的列表
        $where = '';
        $this->whereUserTime($t, 'stat_date', $where);
        $sql = <<<SQL
                select * from ms_estate_stat where 1=1 $where
SQL;
        $list = $db->search($sql);;
        $day = null;
        $line_data = array();
        if(strpos($t, '@')){
            $timeArr = explode('@', $t);
            $day = $this->timediff(strtotime($timeArr[0]), strtotime($timeArr[1]));
        }



            $num = $t=='seven' ? 6 : ($t=='month' ? 29 : $day);
            $start_time = $t=='seven' || $t=='month' ? date('Y-m-d',strtotime("-".$num." day")) : '';

            if(!empty($timeArr)){
                if($timeArr[0] <= $timeArr[1]){
                    $start_time = date('Y-m-d',strtotime($timeArr[0]));
                }else{
                    $start_time = date('Y-m-d',strtotime($timeArr[1]));
                }
            }

            for($i=0; $i<($num+1); $i++) {

                $curDate = date('Y-m-d', strtotime($start_time)+86400*$i);
                $line_data[$i] = array('gold'=>0,'safe_gold'=>0,'credit'=>0,'estate_gold'=>0,'popularity'=>0,'emerald'=>0,'date'=>$curDate);

                if(!empty($list)){
                    foreach ($list as $k => &$report) {
                        if($curDate == date('Y-m-d',$report['stat_date'])){
                            $report['date'] = date('Y-m-d',$report['stat_date']);
                            $line_data[$i]=$report;
                        }

                    }
                }

            }
            foreach ($list as &$item){
                $item['stat_date'] = date('Y-m-d',$item['stat_date']);
            }
            $totalList = array_reverse($line_data);
            $step = $num < 10 ? 1 : ($num < 20 ? 2 : ($num < 30 ? 3 : ($num < 60 ? 6 : 10)));
            $total = $list;
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

        $now = array('gold'=>$result['gold']+$safe,'estate_gold'=>$result['gold'],'safe_gold'=>$safe,'popularity'=>$popularity,'credit'=>$result['credit'],'emerald'=>$result['emerald'],'stat_date'=>date('Y-m-d'));
        $total[] = $now;
        $total = array_reverse($total);
        $return = [];
        $return['line_data'] = $line_data;
        $return['step'] = $step;
        $return['totalArr'] = $list;
        $return['totalList'] = $total;
        return $return;
//        $select = $db->newSelect('ms_estate_stat')
//            ->select('gold')
//            ->select('estate_gold')
//            ->select('safe_gold')
//            ->select('popularity')
//            ->select('credit')
//            ->select('emerald')
//            ->select('stat_date')
//            ->where('stat_date',$startDate,'>=')
//            ->where('stat_date',$endDate,'<');
//        $data = $db->fetchAll($select)->toArray();
//        foreach ($data as  &$item){
//            $item['stat_date'] = date('Y/m/d ',$item['stat_date']);
//        }
//        return $data;


    }

    /**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function _list(int $page = 1, int $pagesize = 15, array $args = []): array {
        if (is_array($args) && isset($args['filters'])) {
            //wehre
            foreach($args['filters'] as $filter=>$val){
                $this->select->whereLike($filter, '%' . $val . '%');
            }
        }
        $this->select->whereNot('status', -1);
        $this->select->order('kind', 'DESC');
        $data = $this->DB()->fetchAllPage($this->select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
    
    public function _get(array $params) {
        $select = $this->DB()->newSelect($this->tb);
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $select->whereNot('status', -1);
        $data = $this->DB()->fetch($select);
        if (!$data) return [];
        return $data->getData();
    }
    
    //日期计算 精确到秒
    private function whereTimeHis($t, $field , &$where) {
        switch($t) {
            case '':
            case 'today':
                $start = date('Y-m-d 00:00:00', strtotime('today'));
                $end = date('Y-m-d 23:59:59', strtotime('today'));
                break;
            case 'yesterday':
                $start = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $end = date('Y-m-d 23:59:59', strtotime('-1 day'));
                break;
            case 'seven':
                $start = date('Y-m-d 00:00:00', strtotime('-6 day'));
                $end = date('Y-m-d 23:59:59', strtotime('today'));
                break;
            case 'month' :
                $start = date('Y-m-d 00:00:00', strtotime('-30 day'));
                $end = date('Y-m-d 23:59:59', strtotime('today'));
                break;
            default :
                $reg = '/^\d{4}-\d{2}-\d{2}@\d{4}-\d{2}-\d{2}$/';
                if(preg_match($reg, $t)) {
                    $timeArr = explode('@', $t);
                    $start_time = strtotime($timeArr[0]);
                    $end_time = strtotime($timeArr[1]);

                    if($start_time <= $end_time){
                        $start = date('Y-m-d 00:00:00', $start_time);
                        $end = date('Y-m-d 23:59:59', $end_time);
                    }else{
                        $start = date('Y-m-d 00:00:00', $end_time);
                        $end = date('Y-m-d 23:59:59', $start_time);
                    }

                } else {
                    exit('时间格式错误');
                }
                break;
        }
        $where .= " AND $field >= '$start' AND $field <= '$end'";
    }

    //日期计算 精确到天
    private function whereTime($t, $field , &$where) {
        switch($t) {
            case '':
            case 'today':
                $start = date('Y-m-d',time());
                $where .= " AND $field = '$start'";
                break;
            case 'yesterday':
                $start = date('Y-m-d',time()-86400);
                $where .= " AND $field = '$start'";
                break;
            case 'seven':
                $start = date('Y-m-d',strtotime("-6 day"));
                $end = date('Y-m-d',time());
                $where .= " AND $field >= '$start' AND $field <= '$end'";
                break;
            case 'month' :
                $start = date('Y-m-d',strtotime("-30 day"));
                $end = date('Y-m-d',time());
                $where .= " AND $field >= '$start' AND $field <= '$end'";
                break;
            default :
                $reg = '/^\d{4}-\d{2}-\d{2}@\d{4}-\d{2}-\d{2}$/';
                if(preg_match($reg, $t)) {
                    $timeArr = explode('@', $t);
                    if($timeArr[0] <= $timeArr[1]){
                        $start = $timeArr[0];
                        $end = $timeArr[1];
                    }else{
                        $start = $timeArr[1];
                        $end = $timeArr[0];
                    }
                } else {
                    exit('时间格式错误');
                }
                $where .= " AND $field >= '$start' AND '$field' <= '$end'";
                break;
        }
    }
    
    //时间戳计算
    private function whereUserTime($t, $field, &$where) {
        switch($t) {
            case '' :
            case 'today' :
                $start = strtotime("today");
                $end = strtotime("today")+86400;
                break;
            case 'yesterday':
                $end = strtotime("today");
                $start = strtotime(date('Y-m-d', time()-86400));
                break;
            case 'seven':
                $end = strtotime(date('Y-m-d',time()))+86400;
                $start = strtotime(date('Y-m-d',strtotime("-7 day")));
                break;
            case 'month' :
                $end = strtotime(date('Y-m-d',time()))+86400;
                $start = strtotime(date('Y-m-d',strtotime("-30 day")));
                break;
            default :
                $reg = '/^\d{4}-\d{2}-\d{2}@\d{4}-\d{2}-\d{2}$/';
                if(preg_match($reg, $t)) {

                    $timeArr = explode('@', $t);
                    $start_time = strtotime($timeArr[0]);
                    $end_time = strtotime($timeArr[1]);

                    if($start_time <= $end_time){
                        $start = $start_time;
                        $end = $end_time;
                    }else{
                        $start = $end_time;
                        $end = $start_time ;
                    }

                }else{
                    exit('时间格式错误');
                }
                break;
        }
        $where .= " AND $field >= '$start' AND $field <= '$end'";
    }

    //计算时间差
    private function timediff($begin_time, $end_time) {

        if ($begin_time < $end_time) {
            $timeDiff = $end_time - $begin_time;
        } else {
            $timeDiff = $begin_time - $end_time;
        }

        return intval($timeDiff/86400);
    }
    public function countGold(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_count_gold_day');
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('create_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('create_time', $args['filters']['end_time'],'<=');
            }
        }

        $select->order('id', 'desc');

        $data = $db->fetchAllPage($select, $page, $pagesize);
        $arr = $data['list']->toArray();
        $data['data'] = $arr;
        return $data;
    }
    private function getNickname($arr){
        $new = [];
        if(empty($arr)) return $arr;
        foreach ($arr as $k => $v) {
            $sql = "select nickname from ms_user where id = ".$v['user_id'].' limit 1';
            $v['nickname'] = $this->DB()->search($sql)[0]['nickname'];
            $new[] = $v;
        }
        return $new;
    }
    public function renqi(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_renqi_log');
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('create_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('create_time', $args['filters']['end_time'],'<=');
            }
            if (isset($args['filters']['query'])){
                $select->where('user_id', $args['filters']['query'],'=');
            }
            if (isset($args['filters']['user_id'])){
                $select->where('user_id', $args['filters']['user_id'],'=');
            }
        }else{
            $select->where('create_time',strtotime(date('Y-m')),'>=');
        }
        $select->order('id','desc');
        $data = $db->fetchAllPage($select,$page,$pagesize);
        $arr = $data['list']->toArray();
        $data['data'] = $this->getNickname($arr);
        
        return $data;
    }
    public function goldStrem(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_gold_log')
            ->joinLeft('ms_user as u', 'u.id = main_table.user_id')
            ->select('main_table.*',true)
            ->select('u.wx_unionid');
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('create_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('create_time', $args['filters']['end_time'],'<=');
            }
            if (isset($args['filters']['query'])){
                $select->where('u.wx_unionid', $args['filters']['query'],'=');
            }
            if (isset($args['filters']['type'])){
                $select->where('type', $args['filters']['type'],'=');
            }
            if (isset($args['filters']['user_id'])){
                $select->where('user_id', $args['filters']['user_id'],'=');
            }
        }else{
            $select->where('create_time',strtotime(date('Y-m')),'>=');
        }
        $select->order('main_table.id','desc');
        $data = $db->fetchAllPage($select,$page,$pagesize);
        $arr = $data['list']->toArray();
        $data['data'] = $this->getNickname($arr);
        
        return $data;
    }
    public function safelog(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_safe_log')
            ->joinLeft('ms_user as u', 'u.id = main_table.user_id')
            ->select('main_table.*',true)
            ->select('u.wx_unionid');
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('c_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('c_time', $args['filters']['end_time'],'<=');
            }
            if (isset($args['filters']['query'])){
                $select->where('u.wx_unionid', $args['filters']['query'],'=');
            }
            if (isset($args['filters']['type'])){
                $select->where('type', $args['filters']['type'],'=');
            }
            if (isset($args['filters']['user_id'])){
                $select->where('user_id', $args['filters']['user_id'],'=');
            }
        }else{
            $select->where('c_time',strtotime(date('Y-m')),'>=');
        }
        $select->order('main_table.id','desc');
        $data = $db->fetchAllPage($select,$page,$pagesize);
        $arr = $data['list']->toArray();
        $data['data'] = $this->getNickname($arr);
        return $data;
    }
    public function creditlog(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_credit_log')
            ->joinLeft('ms_user as u', 'u.id = main_table.user_id')
            ->select('main_table.*',true)
            ->select('u.wx_unionid');
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('create_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('create_time', $args['filters']['end_time'],'<=');
            }
            if (isset($args['filters']['query'])){
                $select->where('u.wx_unionid', $args['filters']['query'],'=');
            }
            if (isset($args['filters']['type'])){
                $select->where('type', $args['filters']['type'],'=');
            }
            if (isset($args['filters']['user_id'])){
                $select->where('user_id', $args['filters']['user_id'],'=');
            }
        }else{
            $select->where('create_time',strtotime(date('Y-m')),'>=');
        }
        $select->order('main_table.id','desc');
        $data = $db->fetchAllPage($select,$page,$pagesize);
        $arr = $data['list']->toArray();
        $data['data'] = $this->getNickname($arr);
        return $data;
    }
    public function emeraldlog(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_emerald_log')
            ->joinLeft('ms_user as u', 'u.id = main_table.user_id')
            ->select('main_table.*',true)
            ->select('u.wx_unionid');
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('create_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('create_time', $args['filters']['end_time'],'<=');
            }
            if (isset($args['filters']['query'])){
                $select->where('u.wx_unionid', $args['filters']['query'],'=');
            }
            if (isset($args['filters']['type'])){
                $select->where('type', $args['filters']['type'],'=');
            }
            if (isset($args['filters']['user_id'])){
                $select->where('user_id', $args['filters']['user_id'],'=');
            }
        }else{
            $select->where('create_time',strtotime(date('Y-m')),'>=');
        }
        $select->order('main_table.id','desc');
        $data = $db->fetchAllPage($select,$page,$pagesize);
        $arr = $data['list']->toArray();
        $data['data'] = $this->getNickname($arr);
        return $data;
    }
    public function loginlog(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_login_reset_log')
            ->joinLeft('ms_user as u', 'u.id = main_table.user_id')
            ->select('main_table.*',true)
            ->select('u.wx_unionid');
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('create_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('create_time', $args['filters']['end_time'],'<=');
            }
            if (isset($args['filters']['query'])){
                $select->where('u.wx_unionid', $args['filters']['query'],'=');
            }
            if (isset($args['filters']['user_id'])){
                $select->where('user_id', $args['filters']['user_id'],'=');
            }
        }else{
            $select->where('create_time',strtotime(date('Y-m')),'>=');
        }
        $select->order('main_table.id','desc');
        $data = $db->fetchAllPage($select,$page,$pagesize);
        $arr = $data['list']->toArray();
        $data['data'] = $this->getNickname($arr);
        return $data;
    }
    public function pointlog(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_point_exchange_log');
        $select->joinLeft('ms_user as u',' main_table.userid=u.id');
        $select->select('main_table.*,u.nickname,u.wx_unionid',true);

        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['start_time'])) {
                $select->where('main_table.exchangetime', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('main_table.exchangetime', $args['filters']['end_time'],'<=');
            }
            if (isset($args['filters']['query'])){
                $select->where('u.wx_unionid', $args['filters']['query'],'=');
            }
            if (isset($args['filters']['exchtype'])){
                $select->where('main_table.exchtype', $args['filters']['exchtype'],'=');
            }
            if (isset($args['filters']['userid'])){
                $select->where('main_table.userid', $args['filters']['userid'],'=');
            }
        }

        $select->order('main_table.id','desc');
        $data = $db->fetchAllPage($select,$page,$pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
}