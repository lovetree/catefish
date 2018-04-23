<?php

namespace Player;

class UserModel extends \BaseModel {
    /**
     * 获取用户列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();

        $select = $this->DB()->newSelect('ms_user')
                ->joinLeft('ms_user_info as ui', 'ui.user_id = main_table.id')
                ->joinLeft('ms_user_estate as ue', 'ue.user_id = main_table.id')
                ->joinLeft('ms_safe as sa', 'sa.user_id = main_table.id')
                ->select('main_table.username')
                ->select('ui.nickname')
                ->select('main_table.wx_unionid')
                ->select('ui.gender')
                ->select('ui.realname')
                ->select('ui.idcard')
                ->select('main_table.agent_id')
                ->select('main_table.created_at')
                ->select('ue.gold')
                ->select('sa.gold as safe_gold')
                ->select('FROM_UNIXTIME(main_table.created_at) as created_at', true)
                ->select('ui.reg_ip')
                ->select('main_table.status')
                ->select('main_table.comment')
                ->select('FROM_UNIXTIME(ui.last_time) as last_time', true)
                ->select('ui.last_ip')
                ->select('ui.login_times')
                ->whereNot('main_table.status', -1);

        if (is_array($args)) {
            if(isset($args['agent_pid'])){
                $select->where('main_table.agent_pid', $args['agent_pid']);
            }
            if(isset($args['filters']['userlastip'])){
                $select->where('ui.last_ip', $args['filters']['userlastip']);
            }

            if(isset($args['agent_id'])){
                $select->where('main_table.agent_id', $args['agent_id']);
            }
            if(isset($args['start_date'])){
                $select->where('main_table.created_at', strtotime($args['start_date']), '>=');
            }
            if(isset($args['end_date'])){
                $select->where('main_table.created_at', strtotime($args['end_date'] .' +1 day'), '<');
            }
            if (isset($args['filters']['userid'])) {
                $select->whereLike('main_table.id', '%'.$args['filters']['userid'] . '%');
            }
            if (isset($args['filters']['wx_unionid'])) {
                $select->whereLike('main_table.wx_unionid', '%'.$args['filters']['wx_unionid'] . '%');
            }
            if (isset($args['filters']['username'])) {
                $select->whereLike('main_table.username', '%'.$args['filters']['username'].'%');
            }
            if (isset($args['filters']['phone'])) {
                $select->whereLike('main_table.phone', '%'.$args['filters']['phone'].'%');
            }
            if (isset($args['filters']['lock'])&&$args['filters']['lock']==1) {
                $select->where('main_table.status', '0');
            }
            if (isset($args['filters']['nickname'])) {
                $select->whereLike('ui.nickname',  '%' . $args['filters']['nickname'] . '%');
            }

            //order
            if (isset($args['order'])) {
                if (isset($args['order']['created_at'])) {
                    $select->order('created_at', $args['order']['created_at']);
                }
            } else {
                $select->order('created_at', 'desc');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);
        return $data;
    }

    /**
     * 删除用户
     * @param int|array $user_id
     * @param string $comment 操作说明
     */
    public function delete($user_id, $comment = '') {
        if (!is_array($user_id)) {
            $user_id = array($user_id);
        }
        $select = $this->DB()->newSelect('ms_user');
        $select->whereIn('id', $user_id);
        $select->whereNot('status', -1);
        $select->setData('status', -1);
        $select->setData('comment', $comment ?? '');
        return $this->DB()->exec($select->updateSql());
    }

    /**
     * 冻结/解冻用户
     * @param int|array $user_id
     * @param boolean $status true = 冻结；false = 解冻
     * @param string $comment
     * @return boolean
     */
    public function freeze($user_id, bool $status, $comment) {
        if (!is_array($user_id)) {
            $user_id = array($user_id);
        }
        $select = $this->DB()->newSelect('ms_user');
        $select->whereIn('id', $user_id);
        $select->whereNot('status', -1);
        if ($status) {
            $select->whereNot('status', 0);
        } else {
            $select->where('status', 0);
        }
        $select->setData('status', $status ? 0 : 1);
        $select->setData('comment', $comment ?? '');
        return $this->DB()->exec($select->updateSql());
    }

    public function freezeOne($user_id, $status, $comment){
        $select = $this->DB()->newSelect('ms_user');
        $select->where('id', $user_id);
        $select->setData('status', $status);
        $select->setData('comment', $comment);
        return $this->DB()->exec($select->updateSql());
    }
    
    /**
     * 获取用户数据
     * @param int|array $user_id 用户ID, 可以是一个数组
     * @param array $fields 获取想要的字段信息
     * @return \Dao\Collection
     */
    public function getUserData($user_id, array $fields = []) {
        $all_fields = [
            'username', 'nickname', 'gender', 'qq', 'mobile', 'zipcode','wx_unionid',
            'address', 'idcard', 'signature', 'avatar', 'hardcode', 'last_time',
            'last_ip', 'login_times', 'reg_ip', 'hardcode', 'created_at', 'comment'
        ];
        if (empty($fields)) {
            $fields = $all_fields;
            $fields = array_flip($fields);
        } else {
            $fields = array_flip($fields);
            $fields = array_fetch($fields, $all_fields);
            if (empty($fields)) {
                return false;
            }
        }
        $fields = array_keys(array_change_keys($fields, array(
            'username' => 'main_table.username',
            'hardcode' => 'main_table.hardcode',
            'created_at' => 'main_table.created_at',
            'comment' => 'main_table.comment',
            'nickname' => 'ui.nickname',
            'gender' => 'ui.gender',
            'mobile' => 'ui.mobile',
            'zipcode' => 'ui.zipcode',
            'address' => 'ui.address',
            'idcard' => 'ui.idcard',
            'signature' => 'ui.signature',
            'avatar' => 'ui.avatar',
            'last_time' => 'ui.last_time',
            'last_ip' => 'ui.last_ip',
            'login_times' => 'ui.login_times',
            'reg_ip' => 'ui.reg_ip'
        )));
        $select = $this->DB()->newSelect('ms_user');
        $select->joinLeft('ms_user_info as ui', 'ui.user_id = main_table.id')
                ->select($fields)
                ->whereNot('main_table.status', -1);

        //条件
        if (is_array($user_id)) {
            $select->whereIn('main_table.id', $user_id);
        } else {
            $select->where('main_table.id', $user_id);
        }

        $data = $this->DB()->fetchAll($select);
        return $data;
    }

    /**
     * 保存用户信息
     * @param type $user_id
     * @param array $fields
     */
    public function save($user_id, array $fields) {
        if (empty($fields)) {
            return false;
        }
        try {
            //user_info
            $user_info = $this->DB()->getTable('ms_user_info');
            if(!$user_info->load($user_id, 'user_id')){
                return false;
            }
            $user_info->setData($fields);
            $user_info->save();
            //user
            $user = $this->DB()->getTable('ms_user');
            if(!$user->load($user_id)){
                return false;
            }
            $user->setData($fields);
            $user->save();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 设置/取消测试员
     * @param int|array $user_id
     * @param boolean $status true = 设置；false = 取消
     * @return boolean
     */
    public function setTester($user_id, $isTester) {
        if (!is_array($user_id)) {
            $user_id = array($user_id);
        }
        $select = $this->DB()->newSelect('ms_user');
        $select->whereIn('id', $user_id);

        if ($isTester) {
            $select->whereNot('is_tester', 1);
        } else {
            $select->where('is_tester', 1);
        }
        $select->setData('is_tester', $isTester);

        return $this->DB()->exec($select->updateSql());
    }

    /**
     * 设置/取消测试员
     * @param int|array $user_id
     * @param boolean $status true = 设置；false = 取消
     * @return boolean
     */
    public function clearEscape($user_id) {
        if (!is_array($user_id)) {
            $user_id = array($user_id);
        }
        $select = $this->DB()->newSelect('ms_user');
        $select->whereIn('id', $user_id);

        $select->setData('escape_num', 0);

        return $this->DB()->exec($select->updateSql());
    }
    
    public function _getUser(array $params) {
        $select = $this->DB()->newSelect('ms_user');
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $info = $this->DB()->fetch($select);
        if (!$info) return false;
        return $info->getData();
    }
    
    public function _getUserInfo(array $params) {
        $select = $this->DB()->newSelect('ms_user_info');
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $info = $this->DB()->fetch($select);
        if (!$info) return false;
        return $info->getData();
    }
    
    public function _getUserEstate(array $params) {
        $select = $this->DB()->newSelect('ms_user_estate');
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $info = $this->DB()->fetch($select);
        if (!$info) return false;
        return $info->getData();
    }
    public function _getUserSafe(array $params) {
        $select = $this->DB()->newSelect('ms_safe');
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $info = $this->DB()->fetch($select);
        if (!$info) return false;
        return $info->getData();
    }

    /**
     * 根据时间获取用户数
     * @param $agentId
     * @param $date
     */
    public function getUserCnt($agentId, $start_date=null, $end_date=null){
        $select = $this->DB()->newSelect('ms_user')
            ->select('count(id) as sum', true)
            ->where('agent_id', $agentId);

        if($start_date){
            $select->where('created_at', $start_date, '>=');
        }
        if($end_date){
            $select->where('created_at', $end_date, '<');
        }

        $result = $this->DB()->fetch($select);

        return $result ? $result->getData('sum') : 0;
    }
    /**
     * 充值金币记录
     */
    public function saveGoldLog($params){
        $select =   $this->DB()->newSelect('ms_user_estate');
        $select->where('user_id',$params['id']);
        $old = $this->DB()->search($select->toString())[0];
        $data = [
            'user_id'=>$params['id'],
            'gold_change'=>$params['gold'],
            'gold_after'=>$old['gold']+$params['gold'],
            'create_time'=>time(),
            'type'=>4
        ];
        $insert = $this->DB()->newSelect('ms_gold_log');
        $insert->setData($data);
        return $this->DB()->exec($insert->insertSql());
    }
    public function saveCreditLog($params){
        $select =   $this->DB()->newSelect('ms_user_estate');
        $select->where('user_id',$params['id']);
        $old = $this->DB()->search($select->toString())[0];
        $data = [
            'user_id'=>$params['id'],
            'credit_change'=>$params['credit'],
            'credit_after'=>$old['credit']+$params['credit'],
            'create_time'=>time(),
            'type'=>4
        ];
        $insert = $this->DB()->newSelect('ms_credit_log');
        $insert->setData($data);
        return $this->DB()->exec($insert->insertSql());
    }
    public function saveEmeraldLog($params){
        $select =   $this->DB()->newSelect('ms_user_estate');
        $select->where('user_id',$params['id']);
        $old = $this->DB()->search($select->toString())[0];
        $data = [
            'user_id'=>$params['id'],
            'emerald_change'=>$params['emerald'],
            'emerald_after'=>$old['emerald']+$params['emerald'],
            'create_time'=>time(),
            'type'=>4
        ];
        $insert = $this->DB()->newSelect('ms_emerald_log');
        $insert->setData($data);
        return $this->DB()->exec($insert->insertSql());
    }
    public function saveSafeLog($params){
        $select =   $this->DB()->newSelect('ms_safe');
        $select->where('user_id',$params['id']);
        $old = $this->DB()->search($select->toString())[0];
        $data = [
            'user_id'=>$params['id'],
            'number'=>$params['safe_gold'],
            'cmd'=>'safe_gold',
            'before_modify_count'=>$old['safe_gold'],
            'after_modify_count'=>$old['safe_gold']+$params['safe_gold'],
            'c_time'=>time(),
            'type'=>4
        ];
        $insert = $this->DB()->newSelect('ms_safe_log');
        $insert->setData($data);
        return $this->DB()->exec($insert->insertSql());
    }
    public function saveUserLevel($params){
        $select =   $this->DB()->newSelect('ms_user_info');
        $select->where('user_id',$params['id']);
        $select->setData(['user_level'=>$params['user_level']]);
        return $this->DB()->exec($select->updateSql());
    }
}
