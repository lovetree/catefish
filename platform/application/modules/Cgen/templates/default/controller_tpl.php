<?php
$tpl = "<?php\n";

$tpl .= "class $controllerName extends BaseController {\n";//class begin

//获取keys
$keysArrStr = '';
foreach ($columns as $column){
    $keysArrStr .= "\n\t\t'" . $column['column_name'] . "'" . ' => ' . "'" . $column['column_name'] . "'" . ',' . "\n";
}
$keysArrStr = rtrim($keysArrStr, ',');
$tpl .= "\t".'/*** 查询列表***/';//function list begin
$tpl .= "\n";
$tpl .= "\t".'public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request[\'query\'] ?? false;
        $query_type = $request[\'query_type\'] ?? false;
        if(!empty($query) && !empty($query_type)){
            switch ($query_type){
                case \'id\':
                    $args[\'filters\'][\'id\'] = $query;
                    break;
                default:
                    $args[\'filters\'][\'id\'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request[\'pagenum\'] ?? 1;
        $pagesize = $request[\'pagesize\'] ?? 10;

        //查询数据
        $model = new '.$modelName.'();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data[\'list\'];
        unset($data[\'list\']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, ['.$keysArrStr.
            '], true);
            $ret_list[] = $new;
        }
        $data[\'data\'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }';
$tpl .= "\n\n";

$validateStr = '';
foreach ($columns as $column){
    if($column['column_name'] == 'id'){
        continue;
    }
    
    $validateStr .= "\n\t\t\t\t\t'" . $column['column_name'] . "'" . '=>' . "'required',\n";
}
$validateStr = rtrim($validateStr, ',');
$tpl .= "\t".'/*** 创建/修改***/';//function save begin;
$tpl .= "\n";
$tpl .= "\t".'public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, ['.$validateStr.
        '])) {
            return false;
        }

        $object_id = $request[\'id\'] ?? false;
        $model = new '.$modelName.'();
        if (empty($object_id)) {
            //新建
            $status = $model->create($request);
        } else {
            //修改
            $status = $model->edit($object_id, $request);
        }
        if (false === $status) {
            return $this->failed(\'保存失败\');
        }
        return $this->succ();
    }';
$tpl .= "\n\n";

$tpl .= "\t".'/*** 删除商品的状态***/';
$tpl .= "\n";
$tpl .= "\t".'public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
        \'id\' => \'required\'
        ])) {
        return false;
        }
        $model = new '.$modelName.'();
        if (!$model->delete($request[\'id\'])) {
        return $this->failed(\'操作失败\');
        }
        return $this->succ();
    }';

$tpl .= "\n\n}";//class end

return $tpl;

