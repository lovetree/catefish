<?php
namespace Recharge;
class RclogModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_recharge_log')
	->select('id')
	->select('recharge_time')
	->select('service_type')
	->select('username')
	->select('game_id')
	->select('order_flow')
	->select('order_amount')
	->select('payed_amount')
	->select('recharge_type')
	->select('gold_recharge')
	->select('gold_present')
	->select('gold_before')
	->select('gold_after')
	->select('ip')
	->select('operator')
	->select('created_at');

        if (is_array($args) && isset($args['filters'])) {
            //wehre
            foreach($args['filters'] as $filter=>$val){
                $select->whereLike($filter, '%' . $val . '%');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data['list'] = $data['list']->toArray();
        return $data;
    }

/**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'id','recharge_time','service_type','username','game_id','order_flow','order_amount','payed_amount','recharge_type','gold_recharge','gold_present','gold_before','gold_after','ip','operator','created_at');
        $model = $this->DB()->getTable('ms_recharge_log');
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }

/**
     * 修改
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function edit(int $id, array $params = []) {
        $params = array_fetch($params, 'id','recharge_time','service_type','username','game_id','order_flow','order_amount','payed_amount','recharge_type','gold_recharge','gold_present','gold_before','gold_after','ip','operator','created_at');
        $model = $this->DB()->getTable('ms_recharge_log');
        if (!$model->load($id)) {
            return false;
        }
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }/**
     * 删除记录
     * @param int|array $id
     * @return boolean
     */
    public function delete($id) {
        if (!is_array($id)) {
            $id = [$id];
        }
        $this->DB()->trancation(function($db, &$rollback) use($id) {
            $select = $db->newSelect('ms_recharge_log');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}