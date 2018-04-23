<?php

class FlowController extends BaseController {

    /**
     * 总流水
     */
    public function totalFlowAction() {
        //验证参数
        $input = $this->input()->get();
        if (!$this->validation($input, [
            'agent_id'=>'required',
            'start_date'=>'required',
            'end_date'=>'required'
        ])) {
            return false;
        }
        $page = $input['pagenum'] ?? 1;
        $pagesize = $input['pagesize'] ?? 10;

        $select = $this->DB()->newSelect('ms_income_stat_by_date')
            ->select("FROM_UNIXTIME(stat_date, '%Y-%m-%d') as stat_date", true)
            ->select('sum(my_player_income) as my_income, sum(agent_player_income) as agent_income', true)
            ->where('agent_id', $input['agent_id'])
            ->where('stat_date', $input['start_date'], '>=')
            ->where('stat_date', $input['end_date'], '<')
            ->group('stat_date')
            ->order('stat_date', 'desc')
            ->limit($pagesize * ($page - 1), $pagesize);

        try{
            $result = $this->DB()->fetchAll($select);
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ($result->toArray());
    }

    /**
     * 直属玩家流水
     */
    public function myPlayerFlowAction() {
        //验证参数
        $input = $this->input()->get();
        if (!$this->validation($input, [
            'agent_id'=>'required',
            'start_date'=>'required',
            'end_date'=>'required'
        ])) {
            return false;
        }
        $page = $input['pagenum'] ?? 1;
        $pagesize = $input['pagesize'] ?? 10;

        $select = $this->DB()->newSelect('ms_income_stat_by_date')
            ->select("FROM_UNIXTIME(stat_date, '%Y-%m-%d') as stat_date", true)
            ->select('my_player_income,my_player_flow, stat_date')
            ->where('agent_id', $input['agent_id'])
            ->where('stat_date', $input['start_date'], '>=')
            ->where('stat_date', $input['end_date'], '<')
            ->order('stat_date', 'desc')
            ->limit($pagesize * ($page - 1), $pagesize);

        try{
            $result = $this->DB()->fetchAll($select);
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ($result->toArray());
    }


    /**
     * 流水明细
     *
     * @return bool
     * @throws Exception
     */
    public function detailFlowAction(){
        $input = $this->input()->get();
        if (!$this->validation($input, [
            'agent_id'=>'required',
            'start_date'=>'required',
            'end_date'=>'required'
        ])) {
            return false;
        }

        //今日流水
        $select = $this->DB()->newSelect('ms_income_flow')
            ->select('user_id')
            ->select('gold')
            ->select('percent')
            ->where('agent_id', $input['agent_id'])
            ->where('created_at', $input['start_date'], '>=')
            ->where('created_at', $input['end_date'], '<');
        try{
            $detailFlow = $this->DB()->fetchAll($select);
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ($detailFlow->toArray());
    }

    /**
     * 代理玩家流水
     */
    public function agentPlayerFlowAction() {
        //验证参数
        $input = $this->input()->get();
        if (!$this->validation($input, [
            'agent_id'=>'required',
            'start_date'=>'required',
            'end_date'=>'required'
        ])) {
            return false;
        }
        $page = $input['pagenum'] ?? 1;
        $pagesize = $input['pagesize'] ?? 10;

        $select = $this->DB()->newSelect('ms_income_stat_by_date')
            ->select("FROM_UNIXTIME(stat_date, '%Y-%m-%d') as stat_date", true)
            ->select('agent_player_income,agent_player_flow, stat_date')
            ->where('agent_id', $input['agent_id'])
            ->where('stat_date', $input['start_date'], '>=')
            ->where('stat_date', $input['end_date'], '<')
            ->order('stat_date', 'desc')
            ->limit($pagesize * ($page - 1), $pagesize);

        try{
            $result = $this->DB()->fetchAll($select);
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ($result->toArray());
    }

    /**
     * 统计总流水及今日流水
     *
     * @return bool
     * @throws Exception
     */
    public function statAction(){
        $input = $this->input()->get();
        if (!$this->validation($input, [
            'agent_id'=>'required',
            'start_date'=>'required',
            'end_date'=>'required'
        ])) {
            return false;
        }

        try{
            //今日流水
            $select = $this->DB()->newSelect('ms_income_flow')
                ->select('sum(abs(gold) * percent)/1000 as today_income', true)
                ->where('agent_id', $input['agent_id'])
                ->where('created_at', $input['start_date'], '>=')
                ->where('created_at', $input['end_date'], '<');
            $todayIncome = $this->DB()->fetch($select)->today_income;

            //总流水
            $select = $this->DB()->newSelect('ms_income_stat_by_date')
                ->select('sum(my_player_income+agent_player_income) as total_income', true)
                ->where('agent_id', $input['agent_id']);
            $totalIncome = $this->DB()->fetch($select)->total_income + ($todayIncome ? $todayIncome : 0);
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ([
            'total_income' => $totalIncome ? $totalIncome : 0,
            'today_income' => $todayIncome ? $todayIncome : 0
        ]);
    }
}
