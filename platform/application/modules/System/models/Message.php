<?php

namespace System;

class MessageModel extends \BaseModel {

    /**
     * 获取管理员列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_game_message')
                ->select('(select username from ms_admin as am where am.id = main_table.created_uid) as created_user', true)
                ->select('(select username from ms_admin as am where am.id = main_table.updated_uid) as updated_user', true)
                ->select('*')
                ->whereNot('status', -1)
                ->order('created_date', 'desc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['content'])) {
                $select->whereLike('main_table.content', '%' . $args['filters']['content'] . '%');
            }
        }
        
        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data['list'] = $data['list']->toArray();
        return $data;
    }

    /**
     * 创建系统消息
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'content', 'start_time', 'end_time', 'comment');
        $userModel = new \UserModel();
        $model = $this->DB()->getTable('ms_game_message');
        $model->setData($params);
        $model->setData('created_uid', $userModel->getLoginUserID());
        $model->setData('updated_uid', $userModel->getLoginUserID());
        return $model->save();
    }

    /**
     * 创建系统消息
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function edit(int $id, array $params = []) {
        $params = array_fetch($params, 'content', 'start_time', 'end_time', 'comment');
        $userModel = new \UserModel();
        $model = $this->DB()->getTable('ms_game_message');
        if(!$model->load($id)){
            return false;
        }
        $model->setData($params);
        $model->setData('updated_uid', $userModel->getLoginUserID());
        return $model->save();
    }
    
    /**
     * 设置消息状态
     */
    public function setStatus(int $msg_id, $status){
        if(!in_array($status, [-1, 0, 1])){
            return false;
        }
        $table = $this->DB()->getTable('ms_game_message');
        if(!$table->load($msg_id)){
            return false;
        }
        $table->setData('status', $status);
        return $table->save();
    }
    
    public function delete($msg_id){
        if(!is_array($msg_id)){
            $msg_id = [$msg_id];
        }
        $select = $this->DB()->newSelect('ms_game_message');
        $select->whereIn('id', $msg_id);
        $select->setData('status', -1);
        $this->DB()->exec($select->updateSql());
        return true;
    }

}
