<?php
namespace Player;
class BankModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_bank_record')
	->select('id')
	->select('trade_type')
	->select('cash_before')
	->select('cash_after')
	->select('bank_before')
	->select('bank_after')
	->select('gold_trade')
	->select('service_fee')
	->select('operate_address')
	->select('game_id')
	->select('room_id')
	->select('remark')
	->select('created_at')
	->select('updated_at');

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
        $params = array_fetch($params, 'id','trade_type','cash_before','cash_after','bank_before','bank_after','gold_trade','service_fee','operate_address','game_id','room_id','remark','created_at','updated_at');
        $model = $this->DB()->getTable('ms_bank_record');
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
        $params = array_fetch($params, 'id','trade_type','cash_before','cash_after','bank_before','bank_after','gold_trade','service_fee','operate_address','game_id','room_id','remark','created_at','updated_at');
        $model = $this->DB()->getTable('ms_bank_record');
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
            $select = $db->newSelect('ms_bank_record');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}