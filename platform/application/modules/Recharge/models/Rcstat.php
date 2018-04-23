<?php
namespace Recharge;
class RcstatModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_order_stat')
	->select('id')
	->select('wx')
	->select('chongzhi')
	->select('dianka')
	->select('bank')
	->select('stat_date')
	->select('qq')
    ->select('ali')
    ->select('total')
    ->select('actually')
    ->select('coupon')
    ->select('other');

    if (is_array($args)) {
        //wehre

        if (isset($args['filters']['start_time'])) {
            $select->where('stat_date', $args['filters']['start_time'], '>=');
        }
        if (isset($args['filters']['end_time'])) {
            $select->where('stat_date', $args['filters']['end_time'], '<');
        }

    }
        $select->order('id','desc');
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
        $params = array_fetch($params, 'id','wx_amount','zfb_amount','wy_amount','card_amount','stat_date','create_time');
        $model = $this->DB()->getTable('ms_recharge_stat');
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
        $params = array_fetch($params, 'id','wx_amount','zfb_amount','wy_amount','card_amount','stat_date','create_time');
        $model = $this->DB()->getTable('ms_recharge_stat');
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
            $select = $db->newSelect('ms_recharge_stat');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}