<?php

class BaseModel {

    use EasyBack;

    /**
     * 用户登出
     * @return bool
     */
    public function logout() {
        if ($this->isLogin()) {
            session_destroy();
        }
        return true;
    }

    /**
     * 检测登录状态
     * @return bool
     */
    public function isLogin(): bool {
        if (!isset($_SESSION[SES_LOGIN])) {
            return false;
        }
        return true;
    }

    /**
     * 检测是否管理员
     * @return bool
     */
    public function isAdmin(): bool {
        return $_SESSION[SES_LOGIN]['status'] == -1;
    }

    /**
     * 获取当前的登录用户的ID
     */
    public function getLoginUserID() {
        if (!$this->isLogin()) {
            return false;
        }
        return $_SESSION[SES_LOGIN]['id'];
    }

    /**
     * 获取用户信息
     * @return array
     */
    public function getInfo() {
        return $_SESSION[SES_LOGIN];
    }

    /**
     * 更新数据
     * @param $tableName
     * @param $dataArr
     * @return bool|int
     * @throws Exception
     */
    public function update($tableName, $id, $dataArr){
        if(!is_array($dataArr) || !$id){
            return false;
        }
        if(!is_array($id)){
            $id = [$id];
        }


        $select = $this->DB()->newSelect($tableName);
        $select->whereIn('id', $id);
        $select->setData($dataArr);
        return $this->DB()->exec($select->updateSql());
    }

    /**
     * 插入数据
     * @param $tableName
     * @param $dataArr
     * @return bool|int
     * @throws Exception
     */
    public function insert($tableName, $dataArr){
        if(!is_array($dataArr)){
            return false;
        }

        $select = $this->DB()->newSelect($tableName);
        $select->setData($dataArr);

        return $this->DB()->exec($select->insertSql());
    }

    /**
     * 批量插入数据
     * @param $tableName
     * @param $dataArr
     * @return bool|int
     * @throws Exception
     */
    public function batchInsert($tableName, $columnArr, $dataArr){
        if(!is_array($dataArr) || !is_array($columnArr)){
            return false;
        }

        $select = $this->DB()->newSelect($tableName);

        return $this->DB()->exec($select->insert($columnArr, $dataArr));
    }
    
    /*初始化插入参数*/
    public function initC(&$params, &$keys = []){
        $params['c_time'] = time();
        $params['created_uid'] = $this->getLoginUserID();
        $params['u_time'] = time();
        $params['updated_uid'] = $this->getLoginUserID();
        if ($keys){
            $keys['u_time'] = time();
            $keys['updated_uid'] = $this->getLoginUserID();
            $keys['c_time'] = time();
            $keys['created_uid'] = $this->getLoginUserID();
        }
    }
    
    /*初始化更新参数*/
    public function initU(&$params, &$keys = []){
        $params['u_time'] = time();
        $params['updated_uid'] = $this->getLoginUserID();
        if ($keys){
            $keys['u_time'] = time();
            $keys['updated_uid'] = $this->getLoginUserID();
        }
    }
    
}
