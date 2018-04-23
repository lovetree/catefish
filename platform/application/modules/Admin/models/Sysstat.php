<?php
namespace admin;
class SysstatModel extends \BaseModel {
	/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
	public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_stat_sys')
			->select('id')
			->select('gold_self')
			->select('gold_safe')
			->select('gold_total')
			->select('gold_present')
			->select('sign')
			->select('promotion')
			->select('register')
			->select('admin_present')
			->select('received_phone_bind')
			->select('prize_for_recharge')
			->select('game_tax')
			->select('sys_losewin')
			->select('tool_buy')
			->select('gift_buy')
			->select('market')
			->select('day_sum');

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
        $params = array_fetch($params, 'id','gold_self','gold_safe','gold_total','gold_present','sign','promotion','register','admin_present','received_phone_bind','prize_for_recharge','game_tax','sys_losewin','tool_buy','gift_buy','market','day_sum');
        $model = $this->DB()->getTable('ms_stat_sys');
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
        $params = array_fetch($params, 'id','gold_self','gold_safe','gold_total','gold_present','sign','promotion','register','admin_present','received_phone_bind','prize_for_recharge','game_tax','sys_losewin','tool_buy','gift_buy','market','day_sum');
        $model = $this->DB()->getTable('ms_stat_sys');
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
            $select = $db->newSelect('ms_stat_sys');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}