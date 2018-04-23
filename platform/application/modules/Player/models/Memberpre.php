<?php
namespace Player;
class MemberpreModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_present_member')
            ->select('id')
            ->select('address')
            ->select('type')
            ->select('effected_days')
            ->select('operator')
            ->select('created_at')
            ->select('reason');

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
        $params = array_fetch($params, 'id','address','type','effected_days','operator','created_at','reason');
        $model = $this->DB()->getTable('ms_present_member');
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
        $params = array_fetch($params, 'id','address','type','effected_days','operator','created_at','reason');
        $model = $this->DB()->getTable('ms_present_member');
        if (!$model->load($id)) {
            return false;
        }
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }

    /**
     * 删除记录
     * @param int|array $id
     * @return boolean
     */
    public function delete($id) {
        if (!is_array($id)) {
            $id = [$id];
        }
        $this->DB()->trancation(function($db, &$rollback) use($id) {
            $select = $db->newSelect('ms_present_member');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }

    /**
     * 批量添加记录
     * @param $user_id
     * @param $type
     * @param $effected_days
     * @param reason $
     */
    public function batchAdd($user_id, $type, $effected_days, $reason){
        $columnArr = ['user_id', 'type', 'effected_days', 'reason', 'operator', 'created_at'];
        $dataArr = [];

        if(!is_array($user_id)){
            $user_id = [$user_id];
        }

        foreach ($user_id as $uid){
            $dataArr[] = [$uid, $type, $effected_days, $reason, $_SESSION[SES_LOGIN]['username'], time()];
        }

        $this->batchInsert('ms_present_member', $columnArr, $dataArr);
    }
}