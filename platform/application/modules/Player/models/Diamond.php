<?php
namespace Player;
class DiamondModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {

        $db =  $this->DB();
        $select = $db->newSelect('ms_user_estate')
            ->select('user_id')
            ->select('credit')
            ->select('update_date')
            ->order('update_date', 'desc');

        if (is_array($args)) {
            if (isset($args['filters']['user_id'])) {
                $select->where('user_id', $args['filters']['user_id']);
            }
            if (isset($args['filters']['gold_above'])) {
                $select->where('credit', $args['filters']['gold_above'], '>=');
            }
            if (isset($args['filters']['gold_below'])) {
                $select->where('credit', $args['filters']['gold_below'], '<=');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);
        //获取用户帐号和用户昵称
        $userModel = new \Player\UserModel();
        $collection = $userModel->getUserData($data['list']->getColumnValue('user_id'), ['username', 'nickname']);
        if($collection){
            $data['list']->flip('user_id');
            $collection->each(function($userTable, $user_id) use($data){
                $item = $data['list']->getItem($user_id);
                if($item){
                    $item->setData('username' , $userTable->getData('username'));
                    $item->setData('nickname' , $userTable->getData('nickname'));
                }
            });
        }
        $data['list'] = $data['list']->toArray();
        return $data;
    }

/**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'id','game_id','username','diamonds_before','trade_event','nickname','diamonds_after','dismonds_trade','operate_address','created_at','updated_at','remark');
        $model = $this->DB()->getTable('ms_diamond');
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
        $params = array_fetch($params, 'id','game_id','username','diamonds_before','trade_event','nickname','diamonds_after','dismonds_trade','operate_address','created_at','updated_at','remark');
        $model = $this->DB()->getTable('ms_diamond');
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
            $select = $db->newSelect('ms_diamond');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}