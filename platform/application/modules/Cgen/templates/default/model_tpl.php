<?php

$tpl = "<?php\n";

if($this->config['module'] != 'Index'){
    $tpl .= "namespace ".$this->config['module'].";\n";
}


$tpl .= "class $modelName extends \BaseModel {\n";//class begin

$selectStr = '$db->newSelect(\'' . $tableName . '\')';
foreach ($columns as $column){
    $selectStr .= "\n\t\t\t->select('" . $column['column_name'] . "')";
}
$tpl .= "\t".'/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */';

$tpl .= "\n";

$tpl .= "\t".'public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = '.$selectStr.';
        $select = $select->whereNot(\'status\', -1);

        if (is_array($args) && isset($args[\'filters\'])) {
            //wehre
            foreach($args[\'filters\'] as $filter=>$val){
                $select->whereLike($filter, \'%\' . $val . \'%\');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data[\'list\'] = $data[\'list\']->toArray();
        return $data;
    }';

$tpl .= "\n\n";

$keysStr = '';
foreach ($columns as $column){
    $keysStr .= "'" . $column['column_name'] . "',";
}
$tpl .= "\t".'/**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, ' . rtrim($keysStr, ',') . ');
        $model = $this->DB()->getTable(\''.$tableName.'\');
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }';

$tpl .= "\n\n";

$tpl .=
    '/**
     * 修改
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function edit(int $id, array $params = []) {
        $params = array_fetch($params, '. rtrim($keysStr, ',') .');
        $model = $this->DB()->getTable(\''.$tableName.'\');
        if (!$model->load($id)) {
            return false;
        }
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }';


$tpl .= '
    /**
     * 删除记录
     * @param int|array $id
     * @return boolean
     */
    public function delete($id) {
        if (!is_array($id)) {
            $id = [$id];
        }
        
        $select = $this->DB()->newSelect(\''.$tableName.'\');
        $select->whereIn(\'id\', $id);
        $select->setData(\'status\', -1);
        
        if($this->DB()->exec($select->updateSql())){
            return true;
        }else{
            return false;
        }
    }';

$tpl .= "\n}";

return $tpl;
