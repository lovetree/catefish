<?php

class PaymentModel{
    const REMIT_STATUS_WAIT = 'commited';
    const REMIT_STATUS_CHECKED = 'checked';
    const REMIT_STATUS_ARRIVED = 'arrived';

    protected static function DB(): DB {
        return Yaf\Registry::get('__DB__')();
    }

    /**
     * 汇款充值
     *
     * @param $amount
     * @param $payee
     * @param $receivingBank
     * @param $branch_bank
     */
    public static function remit($userId, $amount, $payee,$cardno, $depoBank, $remitter, $remitTime, $remitAddr){
        self::DB()->insert('ms_remit_log', [
            'amount' => $amount,
            'payee' => $payee,
            'cardno' => $cardno,
            'depo_bank' => $depoBank,
            'remitter' => $remitter,
            'remit_time' => $remitTime,
            'remit_addr' =>$remitAddr,
            'remit_status' => self::REMIT_STATUS_WAIT,
            'flowno' =>$userId . date("YmdHis"),
            'user_id' =>$userId,
            'created_at'=> time()
        ]);
    }

    /**提现
     * @param $userId
     * @param $dataArr
     */
    public static function cash($userId, $dataArr){
        //密码验证
        $safe = new SafeModel();
        $safe_info = $safe->getOne($userId);
        if (!$safe_info){
            throw new \Exception(_1000401);
        }

        if (!$safe_info['status'] === 0){
            throw new \Exception(_1000402);
        }

        if (md5($dataArr['pass']) !== $safe_info['password']){
            throw new \Exception(_1000403);
        }

        self::DB()->insert('ms_exchange_log', [
            'e_amount' => $dataArr['amount'],
            'e_depo_bank' => $dataArr['depo_bank'],
            'e_account_name' => $dataArr['account_name'],
            'e_branch_bank' => $dataArr['branch_bank'],
            'e_cardno' => $dataArr['cardno'],
            'e_flowno' =>$userId . date("YmdHis"),
            'user_id' =>$userId,
            'created_at'=> time()
        ]);
    }

    /**
     * 充值记录
     * @param $userId
     * @return Array
     */
    public static function getRechargeLog($page = 1, $pageSize = 10, $userId,  $start_date = null, $end_date = null){
        $paramArr = [];
        $offset = ($page - 1) * $pageSize;
        $sql = "(select 
                    amount,
                    flowno,
                    created_at as r_date,
                    '银行转账' as r_type,
                    remit_status as r_status
                from ms_remit_log
                where user_id = ?
                ";
        $paramArr[] = $userId;

        if($start_date){
            $sql .= " and created_at >= ?";
            $paramArr[] = $start_date;
        }
        if($end_date){
            $sql .= " and created_at < ?";
            $paramArr[] = $end_date;
        }

        $sql .= " order by created_at desc
                limit $offset, $pageSize)";

        $sql .= "union all
                (select 
                    gold_recharge as amount,
                    order_flow as flowno,
                    recharge_time as r_date,
                    recharge_type as r_type,
                    '已完成' as r_status
                from ms_recharge_log
                where user_id = ?";
        $paramArr[] = $userId;

        if($start_date){
            $sql .= " and recharge_time >= ?";
            $paramArr[] = $start_date;
        }
        if($end_date){
            $sql .= " and recharge_time < ?";
            $paramArr[] = $end_date;
        }

        $sql .=" order by recharge_time desc
                limit $offset, $pageSize)";

        return self::DB()->query($sql, $paramArr);
    }

    public static function getExchangeLog($page, $pageSize, $userId){
        $offset = ($page-1)*$pageSize;
        $sql = "select 
                    e_date,
                    e_amount
               from ms_exchange_log
               where user_id = ?
               limit $offset , $pageSize";

        return self::DB()->query($sql, [$userId]);
    }

    /**
     * 计算可用金币
     */
    public static function getAvailGold(){
        
        return 20;
    }
}
