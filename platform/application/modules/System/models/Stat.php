<?php

namespace System;

class StatModel extends \BaseModel {
    /**
     * 新增会员数
     */
    function getTodayNewPlayers(){
        $start_date = strtotime('today');
        $end_date = strtotime('today +1 day');

        $sql = $this->DB()->newSelect('ms_user')
            ->select('count(1) as cnt', true)
            ->where('created_at', $start_date, '>=')
            ->where('created_at', $end_date, '<');

        return $this->DB()->fetch($sql)->cnt;
    }

    //注册总人数
    function  getRegUserCnt(){
        $db = $this->DB();

        return  $db->fetch($db->newSelect('ms_user')
            ->select('count(1) as cnt', true)
            ->where('type', 100, '<>'))
            ->cnt;
    }

    /**
     * 携带金币数量
     */
    function getTotalCarryGold(){
        $db = $this->DB();

        return $db->fetch($db->newSelect('ms_user_estate')
            ->joinLeft('ms_user as uu','uu.id = main_table.user_id')
            ->select('sum(gold) as total' , true)
            ->isnotnull('uu.username'))
            ->total;
    }

    /**
     * 保险柜金币数量
     */
    function getTotalSafeGold(){
        $db = $this->DB();

        return $db->fetch($db->newSelect('ms_safe')
            ->select('sum(safe_gold) as total' , true))
            ->total;
    }

    /**
     * 服务费
     */
    function getServiceFee($start_date=null, $end_date=null){
        $db = $this->DB_GameLog();

        $select = $db->newSelect('ms_user_log')
            ->select('sum(gold_tax) as total', true);

        if($start_date){
            $select->where('created_date', $start_date, '>=');
        }
        if($end_date){
            $select->where('created_date', $end_date, '<');
        }

        return $db->fetch($select)->total;
    }

    /**
     * 网银充值汇总
     * @param null $start_date
     * @param null $end_date
     */
    function getRecharge($start_date=null, $end_date=null){
        $db = $this->DB();

        $select = $db->newSelect('ms_order')
            ->select('sum(actually_fee) as total', true)
            ->where('status', '1')
            ->where('transaction_id', 0, '>');

        if($start_date){
            $select->where('created_date', $start_date, '>=');
        }
        if($end_date){
            $select->where('created_date', $end_date, '<');
        }

        return $db->fetch($select)->total;
    }

    /**
     * 今日在线会员数
     */
    function getTodayOnlinePlayers(){
        $start_date = strtotime(date('Y-m-d', strtotime('-1 day')));
        $end_date = strtotime(date('Y-m-d'));

        $sql = $this->DB_GameLog()->newSelect('ms_user_online')
            ->select('count(1) as cnt', true)
            ->where('update_time', $start_date, '>=')
            ->where('update_time', $end_date, '<');

        return $this->DB_GameLog()->fetch($sql)->cnt;
    }

    /**
     * 今日对战局数
     */
    function getTodayPlayNum(){
        $start_date = strtotime(date('Y-m-d', strtotime('-1 day')));
        $end_date = strtotime(date('Y-m-d'));

        $sql = $this->DB_GameLog()->newSelect('ms_game_log')
            ->select('count(1) as cnt', true)
            ->where('start_time', $start_date, '>=')
            ->where('start_time', $end_date, '<');

        return $this->DB_GameLog()->fetch($sql)->cnt;
    }
    function getGameType($id){
        $db = $this->DB();

        $select = $db->newSelect('ms_gm_gametype')
            ->select('game_name')
        ->where('id',$id);
        return $db->fetch($select)->game_name;
    }
    function getGameMode($id){
        $db = $this->DB();

        $select = $db->newSelect('ms_gm_gamemode')
            ->select('mode_name')
            ->where('id',$id);
        return $db->fetch($select)->mode_name;
    }
    /**
     * 获取房间输赢统计信息
     */
    function getRoomStat($startDate, $endDate){
        //起始时间
        $startDate = strtotime($startDate);
        //结束时间
        $endDate = strtotime($endDate);

        //统计赢金币
        $wingoldStat = $this->_roomStatByCate($startDate, $endDate, 'win');
        //统计输金币
        $losegoldStat = $this->_roomStatByCate($startDate, $endDate, 'lose');
        //统计税收
        $taxStat = $this->_roomStatByCate($startDate, $endDate, 'tax');


        //合并数据
        $dataStat = [];
        foreach ($wingoldStat as $item){
            $key = $item['game_id'].$item['game_mode'];
            $dataStat[$key] = [
                'game_type' => $this->getGameType($item['game_id']),
                'game_mode' => $this->getGameMode($item['game_mode']),
                'win_gold' => $item['total'],
                'lose_gold' => 0,
                'service_fee' =>0
            ];
        }

        //合并数据
        foreach ($losegoldStat as $item){
            $key = $item['game_id'].$item['game_mode'];
            if(isset($dataStat[$key])){
                $dataStat[$key]['lose_gold'] = $item['total'];
            }else{
                $dataStat[$key] = [
                    'game_type' => $this->getGameType($item['game_id']),
                    'game_mode' => $this->getGameMode($item['game_mode']),
                    'win_gold'  => 0,
                    'lose_gold' => $item['total'],
                    'service_fee' =>0
                ];
            }
        }

        //合并数据
        foreach ($taxStat as $item){
            $key = $item['game_id'].$item['game_mode'];
            if(isset($dataStat[$key])){
                $dataStat[$key]['service_fee'] = $item['total'];
            }else{
                $dataStat[$key] = [
                    'game_type' => $this->getGameType($item['game_id']),
                    'game_mode' => $this->getGameMode($item['game_mode']),
                    'win_gold'  => 0,
                    'lose_gold' => 0,
                    'service_fee' =>$item['total']
                ];
            }
        }

        return array_values($dataStat);
    }
    /**
     * 获取存量统计信息
     */
    function estateStat($startDate, $endDate){
        //起始时间
        $startDate = strtotime($startDate);
        //结束时间
        $endDate = strtotime($endDate);
        $db = $this->DB();
//        return [11];
        $select = $db->newSelect('ms_estate_stat')
            ->select('gold')
            ->select('estate_gold')
            ->select('safe_gold')
            ->select('popularity')
            ->select('credit')
            ->select('emerald')
            ->select('stat_date')
            ->where('stat_date',$startDate,'>=')
            ->where('stat_date',$endDate,'<');
        return $db->fetchAll($select)->toArray();


    }
    /**
     * 获取在线统计信息
     */
    function online($page = 1, $pagesize = 10,$search){
//        //起始时间
//        $startDate = strtotime($search['start']);
//        //结束时间
//        $endDate = strtotime($search['end']);

        $db = $this->DB_GameLog();
//        return [11];
        $select = $db->newSelect('online_log')
            ->joinLeft('ms_db_main.ms_gm_gametype ggt', 'ggt.id = main_table.gametype')
            ->joinLeft('ms_db_main.ms_gm_gamemode ggm', 'ggm.id = main_table.gamemod')
            ->joinLeft('ms_user as u', 'u.id = main_table.user_id')
            ->select('ggt.game_name ')
            ->select('ggm.mode_name ')
            ->select('gamecount')
            ->select('onlinecount')
            ->select('u.wx_unionid')
            ->select('FROM_UNIXTIME(time, \'%Y-%m-%d %H:%i:%s\')as time' , true)
            ->where('time',time()-300,'>=');

        if(isset($search['type'])&&$search['type']){
            $select->where('main_table.gametype',$search['type']);
        }
        if(isset($search['mod'])&&$search['mod']){
            $select->where('main_table.gamemod',$search['mod']);
        }
//            $select->order('main_table.'.$search['by'],$search['sorttype']);
        $select->order('main_table.id', 'desc');
//        return $select->toString();
        $data = $db->fetchAllPage($select,$page,$pagesize);

        return $data;


    }

    /**
     * 房间数据统计
     * @param $startDate
     * @param $endDate
     * @param $type
     * @return \Dao\Collection
     */
    private function _roomStatByCate($startDate, $endDate, $type){
        $statCol = '';
        $sym = '';
        switch ($type){
            case 'win':
                $statCol = 'sum(win_gold)';
                $sym = '>';
                break;
            case 'lose':
                $statCol = 'abs(sum(win_gold))';
                $sym = '<';
                break;
            case 'tax':
                $statCol = 'sum(gold_tax)';
                $sym = '>';
                break;
        }

        $sql = $this->DB_GameLog()->newSelect('ms_user_log')
            ->select('game_id')
            ->select('game_mode')
            ->select($statCol . ' as total', true)
            ->where('win_gold', 0, $sym)
            ->where('created_date', $startDate, '>=')
            ->where('created_date', $endDate, '<')
            ->group('game_id', 'game_mode');

        return $this->DB_GameLog()->fetchAll($sql)->toArray();
    }

    /**
     * 在线会员数
     */
    function getOnlinePlayers(){
        $online = $this->DB_GameLog()->fetchAll($this->DB_GameLog()->newSelect('online_log')
            ->select('onlinecount', true)
            ->order('id', 'desc')
            ->limit(0, 23)
        )->toArray();

        $total = 0;
        foreach ($online as $item){
            $total += $item['onlinecount'];
        }

        return $total;
    }

    /**
     * 在线会员列表
     */
    function onlinelist($page=1,$limit=10,$type=-1,$mod=-1){

        $url = $this->config()->get('push.ip'). "/index?cmd=onlinelist&gametype=".$type."&gamemod=".$mod;
        $res = $this->http_get_request($url);
//        $res = '[{"uid":338124,"tid":0,"ltime":106,"gt":0,"gm":0},{"uid":338124,"tid":0,"ltime":106,"gt":0,"gm":0}]';

        if($res){
            $data = json_decode($res,true);
            if(count($data)>0){
                $nums = $page*$limit>count($data)?count($data):$page*$limit;
                for ($i=($page-1)*$limit;$i<$nums;$i++){

                    $user = $this->getuser($data[$i]['uid']);
                    $datas[]= array(
                        'ltime'=>$this->changertime($data[$i]['ltime']),
                        'nickname'=>$user->getData('nickname'),
                        'username'=>$user->getData('username'),
                        'region'=>$this->getregion($data[$i]['gt'],$data[$i]['gm']),
                        'uid'=>$data[$i]['uid'],
                        'wx_unionid'=>$user->getData('wx_unionid')
                        );

                    // $data[$i]['ltime'] = $this->changertime($data[$i]['ltime']);
                    // $data[$i]['nickname'] = $user->getData('nickname');
                    // $data[$i]['username'] = $user->getData('username');
                    // $data[$i]['region'] = $this->getregion($data[$i]['gt'],$data[$i]['gm']);
                }

                $result = array(
                    'pageCount'=>ceil(count($data)/$limit),
                    'total'=>count($data),
                    'data'=>$datas
                );
            }else{
                $result =['pageCount'=>1, 'total'=>0,'data'=>[]];
            }
        }else{
            $result =['pageCount'=>1, 'total'=>0,'data'=>[]];
        }
        return $result;

    }
    public function changertime($t){
        if($t<60){
            $t= $t.'秒';
        }elseif ($t<3600){
            $m = floor($t/60);
            $i = $t%60;
            $t = $m.'分'.$i.'秒';
        }else{
            $h = floor($t/3600);
            $m = floor(($t%3600)/60);
            $i = ($t%3600)%60;
            $t = $h.'小时'.$m.'分'.$i.'秒';
        }
        return $t;
    }
    public function getuser($id){
        $select = $this->DB()->newSelect('ms_user')
            ->select('username')
            ->select('nickname')
            ->select('wx_unionid')
            ->where('id',$id);
        return $this->DB()->fetch($select);
    }
    public function getregion($type,$mod){
        $str = '';
        if ($type==0){
            return '游戏大厅';
        }else{
            $sqlt = $this->DB()->newSelect('ms_gm_gametype')
                ->select('game_name')
                ->where('id',$type);
            $str = $this->DB()->fetch($sqlt)->getData('game_name');
            if($mod==0){
                $str.='-大厅';
            }else{
                $sqlm = $this->DB()->newSelect('ms_gm_gamemode')
                    ->select('mode_name')
                    ->where('id',$mod);
                $str.= '-'.$this->DB()->fetch($sqlm)->getData('mode_name');
            }
            return $str;
        }
    }
    /**
     * get请求
     */
 public function http_get_request($url, $param = NULL, $header = NULL) {
        if(!empty($param)) {
            if(strpos($url, "?") ===false) $url .= "?".http_build_query($param);
            else $url .= "&".http_build_query($param);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(is_array($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $res = curl_exec( $ch );
        curl_close( $ch );
        return $res;
    }
}
