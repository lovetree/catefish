<?php

namespace Recharge;

class OrderModel extends \BaseModel {

    /**
     * 获取订单列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $this->DB()->newSelect('ms_order')
                ->joinLeft('ms_user as user', 'user.id = main_table.user_id')
                //->joinLeft('ms_user_info as info', 'info.user_id = main_table.user_id')
                ->joinLeft('ms_order_thirdparty as ot', 'ot.order_id = main_table.order_id')
                ->select('main_table.*')
                ->select('user.username')
                ->select('user.wx_unionid')

                ->select(['ot.cash_fee as t_cash_fee', 'ot.cash_fee_type as t_cash_fee_type', 'ot.total_fee as t_total_fee', 'ot.channel as pay_channel', 'ot.finish_date as pay_time', 'ot.coupon_detail as t_coupon_detail'])
                //->select('info.nickname')
                ->where('main_table.transaction_id', 0, '>')
                ->order('created_date', 'desc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['order_id'])) {
                $select->where('main_table.order_id', $args['filters']['order_id']);
            }
            if (isset($args['filters']['user_id'])) {
                $select->where('main_table.user_id', $args['filters']['user_id']);
            }
            if (isset($args['filters']['goods_id'])) {
                $select->where('main_table.goods_id', $args['filters']['goods_id']);
            }
            if (isset($args['filters']['start_time'])) {
                $select->where('main_table.created_date', $args['filters']['start_time'], '>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('main_table.created_date', $args['filters']['end_time'], '<');
            }
            if(isset($args['filters']['amount_up'])){
                $select->where('main_table.actually_fee', $args['filters']['amount_up']*100, '<');
            }
            if(isset($args['filters']['amount_down'])){
                $select->where('main_table.actually_fee', $args['filters']['amount_down']*100, '>=');
            }
            if(isset($args['filters']['status'])){
                $select->where('main_table.status', $args['filters']['status']);
            }
            if(isset($args['filters']['wx_unionid'])){
                $select->where('user.wx_unionid', $args['filters']['wx_unionid']);
            }

        }

        $data = $db->fetchAllPage($select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }

    /**
     * 充值日报
     */
    public function rechargeLog(){
        $db = $this->DB();
        $select = 'select sum(totalf_fee) as total,sum(actually_fee) as actually,sum(coupon_fee) as coupon ,pay_type FROM ms_order  where (transaction_id >= 0) and (update_date <= '.strtotime('today').') and (update_date >= '.(strtotime('today')-86400).') AND status = 1 group by pay_type';
        $data = $db->search($select);
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
            foreach ($data as &$item){
                switch ($item['pay_type']){
                    case 1:
                        $chongzhi+=$item['total'];
                        break;
                    case 2:
                        $dianka+=$item['total'];
                        break;
                    case 4:
                        $bank+=$item['total'];
                        break;
                    case 401:
                        $ali+=$item['total'];
                        break;
                    case 402:
                        $qq+=$item['total'];
                        break;
                    case 403:
                        $wx+=$item['total'];
                        break;
                    default :
                        $other+= $item['total'];
                }
                $total+=$item['total'];
                $actually+= $item['actually'];
                $coupon+=$item['coupon'];
            }
        }
        $create_at = strtotime('today')-86400;

        return [$total,$actually,$coupon,$chongzhi,$dianka,$bank,$ali,$qq,$wx,$other,$create_at];
    }

}
