<?php

class StatController extends BaseController {
    /**
     * 玩家统计
     */
    public function playerAction(){
        $model = new \System\StatModel();
        $todayOnlineNums = $model->getTodayOnlinePlayers();
        $todayNewPlayers = $model->getTodayNewPlayers();
        $todayPlaynum = $model->getTodayPlayNum();

        return $this->succ([
            'today_new_players'=>$todayNewPlayers,
            'today_new_online_players'=>$todayOnlineNums,
            'today_play_nums'=>$todayPlaynum]);
    }

    /**
     * 房间统计
     */
    public function roomAction(){
        $start_date = $this->input()->get('start_date');
        $end_date = $this->input()->get('end_date');

        //默认今天
        if(!$start_date){
            $start_date = date('Y-m-d');
        }
        //默认明天
        if(!$end_date){
            $end_date = date('Y-m-d', strtotime('+1 day'));
        }

        $model = new \System\StatModel();
        $result = $model->getRoomStat($start_date, $end_date);

        return $this->succ(['pageCount'=>1, 'total'=>count($result),'data'=>$result], false);
    }

    /**
     *  综合统计
     */
    public function generalStatAction(){
        $model = new \System\StatModel();
        $start_date = strtotime('today');
        $end_date = strtotime('today +1 day');

        $regNum = $model->getRegUserCnt();
        $curRegNum = $model->getTodayNewPlayers();
        $carryGold = $model->getTotalCarryGold();
        $safeGold = $model->getTotalSafeGold();
        $serviceFee = $model->getServiceFee();
        $curServiceFee = $model->getServiceFee($start_date, $end_date);
        $recharge = $model->getRecharge();
        $curRecharge = $model->getRecharge($start_date, $end_date);
        $curOnlineNum = $model->getOnlinePlayers();

        return $this->succ([
            'reg_num' => $regNum,
            'cur_reg_num' => $curRegNum,
            'carry_gold' => $carryGold,
            'safe_gold' => $safeGold,
            'service_fee' => $serviceFee,
            'cur_service_fee' => $curServiceFee,
            'recharge' => $recharge/100,
            'cur_recharge' => $curRecharge/100,
            'cur_player_num' => $curOnlineNum
        ], false);
    }

    /**
     * 存量统计
     */
    public function estateAction(){
        $start_date = $this->input()->get('start_date');
        $end_date = $this->input()->get('end_date');

        //默认昨天
        if(!$start_date){
            $start_date = date('Y-m-d', strtotime('-1 day'));
        }
        //默认明天
        if(!$end_date){
            $end_date = date('Y-m-d', strtotime('+1 day'));
        }

        $model = new \System\StatModel();
        $result = $model->estateStat($start_date, $end_date);
        foreach ($result as &$item){
            $item['stat_date'] = date('Y-m-d ',$item['stat_date']);
        }
        return $this->succ(['pageCount'=>1, 'total'=>count($result),'data'=>$result], false);
    }

    /**
     * 在线统计
     */
    public function onlineAction(){
        $start_date = $this->input()->get('start_date');
        $end_date = $this->input()->get('end_date');
        $type = $this->input()->get('type');
        $mod = $this->input()->get('mod');
        $by = $this->input()->get('sortby');
        $sorttype = $this->input()->get('sorttype');
        $page = $this->input()->get('pagenum');

//        //默认昨天
//        if(!$start_date){
//            $start_date = date('Y-m-d', strtotime('-1 day'));
//
//        }
//        //默认明天
//        if(!$end_date){
//            $end_date = date('Y-m-d', strtotime('+1 day'));
//
//        }
//        $search['start'] = $start_date;
//        $search['end'] = $end_date;
        $search['by'] = $by;
        $search['sorttype'] = $sorttype;
        $search['type'] = $type?$type:'';
        $search['mod'] = $mod?$mod:'';
        $model = new \System\StatModel();
        $result = $model->online($page,10,$search);
//        return $this->succ(['pageCount'=>1, 'total'=>2,'data'=>$result], false);
        $arr = &$result['list'] ;

        $result['data'] = $arr;
        unset($result['list']);
        return $this->succ($result, false);
    }
    /**
     * 在线玩家列表
     */
    public function onlinelistAction(){
        $type = $this->input()->get('type');
        $mod = $this->input()->get('mod');
        $page = $this->input()->get('pagenum');
        $model = new \System\StatModel();
        $result = $model->onlinelist($page,10,$type,$mod);

        return $this->succ($result, false);

    }


}
