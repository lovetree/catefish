<?php
namespace Player;
class LoginlogModel extends \BaseModel {
    /**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_login_log')
            ->select('id')
            ->select('game_id')
            ->select('usrename')
            ->select('nickname')
            ->select('login_time')
            ->select('login_ip')
            ->select('address')
            ->select('login_type');

        if (is_array($args) && isset($args['filters'])) {
            //wehre
            if (isset($args['filters']['username'])) {
                $select->whereLike('usrename', '%' . $args['filters']['username'] . '%');
            }
            if (isset($args['filters']['nickname'])) {
                $select->whereLike('nickname', '%' . $args['filters']['nickname'] . '%');
            }
            if (isset($args['filters']['start_time'])) {
                $select->where('login_time', $args['filters']['start_time'], '>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('login_time', $args['filters']['end_time'], '<=');
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
        $params = array_fetch($params, 'id','game_id','usrename','nickname','login_time','login_ip','address','login_type','create_time');
        $model = $this->DB()->getTable('ms_login_log');
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
        $params = array_fetch($params, 'id','game_id','usrename','nickname','login_time','login_ip','address','login_type','create_time');
        $model = $this->DB()->getTable('ms_login_log');
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
            $select = $db->newSelect('ms_login_log');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}