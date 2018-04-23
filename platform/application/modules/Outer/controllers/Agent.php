<?php

class AgentController extends BaseController {

    /**
     * 添加代理
     */
    public function addAgentAction() {
        $req = json_decode(file_get_contents("php://input"), true);
        $db = $this->DB();
        $db->exec($db->newSelect('ms_agent')->insert(
            ['id',
                'a_username',
                'a_password',
                'a_pid',
                'a_percent',
                'a_pro_aurl',
                'a_pro_purl',
                'a_pro_abarcode',
                'a_pro_pbarcode',
                'a_ctime',
                'updated_at'], [array_merge($req, [time(), time()])]
        ));

        return $this->succ();
    }

    /**
     * 充值记录
     */
    public function reLogAction(){
        $input = $this->input()->request();

        $db = $this->DB();
        $select = $db->newSelect('ms_recharge_log');
        if(isset($input['agent_id'])){
            $select->where('user_id', $input['agent_id']);
            $select->where('service_type', $input['type']);
        }
        if(isset($input['start_date'])){
            $select->where('recharge_time', strtotime($input['start_date']), '>=');
        }
        if(isset($input['end_date'])){
            $select->where('recharge_time', strtotime($input['end_date']), '<');
        }

        try{
            $logList =  $db->fetchAll($select);
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ($logList->toArray());

    }
}
