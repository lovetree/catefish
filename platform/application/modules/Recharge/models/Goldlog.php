<?php
namespace Recharge;
class GoldlogModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_gold_log')
	->select('id')
	->select('trade_flow')
	->select('trade_at')
	->select('channel_id')
	->select('presentor_id')
	->select('gold_present')
	->select('receiver_type')
	->select('reveiver_id')
	->select('trade_type')
	->select('status')
	->select('create_at');

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
        $params = array_fetch($params, 'id','trade_flow','trade_at','channel_id','presentor_id','gold_present','receiver_type','reveiver_id','trade_type','status','create_at');
        $model = $this->DB()->getTable('ms_gold_log');
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
        $params = array_fetch($params, 'id','trade_flow','trade_at','channel_id','presentor_id','gold_present','receiver_type','reveiver_id','trade_type','status','create_at');
        $model = $this->DB()->getTable('ms_gold_log');
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
            $select = $db->newSelect('ms_gold_log');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}