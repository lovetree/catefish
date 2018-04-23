<?php
include_once "Payment.php";
class GamelogModel{
    protected static function DB(): DB {
        return Yaf\Registry::get('__DB_GAMELOG__')();
    }

    public static function getWinloseLog($userId, $page=1, $pageSize=10){
        $offset = ($page-1)*$pageSize;
        $sql = "select 
                    game_id, 
                    game_mode,
                    win_gold,
                    gold,
                    gold_tax,
                    created_date
                 from ms_user_log 
                 where user_id = ?
                 order by created_date desc
                 limit $offset, $pageSize";

        return self::DB()->query($sql, [$userId]);
    }

    public static function getGameCount($userId){
        $sql = 'select count(1) as cnt from ms_user_log where user_id = ?';

        return self::DB()->query_fetch($sql, [$userId])['cnt'];
    }

    /**
     * 金币流水=游戏流水+充值+系统奖励（新手奖励、分享奖励）
     * 2017-05-09
     * @param $userId
     * @param $startDate
     * @param $endDate
     */
    public static function getGoldflow($page=1, $pageSize=50, $userId, $startDate, $endDate, $type){
        //流水数据
        $dataArr = [];

        switch ($type){
            case 1:
                //游戏流水
                $dataArr= self::getGameLog($page, $pageSize, $userId, $startDate, $endDate);
                break;
            case 2:
                //充值
                $rechargeLog = PaymentModel::getRechargeLog($page, $pageSize, $userId, $startDate, $endDate);
                $flowArr = [];
                if(!empty($rechargeLog)){
                    foreach ($rechargeLog as $item){
                        $flowArr[] = [
                            'desc' => '充值成功获得金币',
                            'created_at' => $item['r_date'],
                            'change_gold' => $item['amount'],
                            'total_gold' => 0
                        ];
                    }
                }
                $dataArr[] = $flowArr;
                $dataArr[] = 1;
                break;
            case 3:
                //提款
                $exchangeLog = PaymentModel::getExchangeLog($page, $pageSize, $userId);
                $flowArr = [];
                if(!empty($exchangeLog)){
                    foreach ($exchangeLog as $item){
                        $flowArr[] = [
                            'desc' => '提现成功',
                            'created_at' => $item['e_date'],
                            'change_gold' => $item['e_amount'],
                            'total_gold' => 0
                        ];
                    }
                }
                $dataArr[] = $flowArr;
                $dataArr[] = 1;
                break;
            case 4:
                //分享奖励
                $sharmodel = new ShareModel();
                $newRewardLog = $sharmodel->getNewrewardLog($page, 25, $userId, $startDate, $endDate);
                $shareRewardLog = $sharmodel->getSharerewardLog($page, 25, $userId, $startDate, $endDate);
                $flowArr = array_merge($newRewardLog, $shareRewardLog);

                $dataArr[] = $flowArr;
                $dataArr[] = 1;
                break;
        }

        return $dataArr;
    }

    /**
     * 游戏流水
     */
    public static function getGameLog($page, $pageSize, $userId, $startDate, $endDate){
        $offset = ($page-1)*$pageSize;
        $fileds = 't2.game_name, 
                    t3.mode_name,
                    win_gold,
                    gold_tax,
                    gold,
                    created_date';

        $sql = "select 
                    count(1)
                from ms_db_log.ms_user_log t1
                join ms_db_main.ms_gm_gametype t2 on t1.game_id = t2.id
                join ms_db_main.ms_gm_gamemode t3 on t1.game_mode = t3.id
                where t1.user_id = ?
                and t1.created_date >= ?
                and t1.created_date < ?
                order by created_date desc
                ";

        //总页数
        $total_page = ceil(self::DB()->query_fetch($sql, [$userId, $startDate, $endDate])['count(1)']/$pageSize);

        //获取数据
        $sql = str_replace('count(1)', $fileds, $sql);

        $sql .= " limit $offset , $pageSize";
        $gameLog = self::DB()->query($sql, [$userId, $startDate, $endDate]);

        $flowArr = [];
        if(!empty($gameLog)){
            foreach ($gameLog as $item){
                $flowArr[] = [
                    'desc' => $item['game_name'] . $item['mode_name'],
                    'created_at' => $item['created_date'],
                    'change_gold' => $item['win_gold'] - $item['gold_tax'],
                    'total_gold' => $item['gold'] - $item['gold_tax'] + $item['win_gold']
                ];
            }
        }

        return [$flowArr, $total_page];
    }

}
