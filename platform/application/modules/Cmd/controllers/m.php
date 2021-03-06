<?php
$m_tpl = '<?php
namespace ' . $modules . ';
class ' . $model .'Model extends \BaseModel {
    
    private $tb = "' . $tb . '";
    private $p_l = ' . json_encode($p_l) . ';
    private $p_s = ' . json_encode($p_s) . ';
    private $model = [];
    private $select = [];
    public $keys = [];
    public $valid = [];
    
    public function __construct() {
        if (!$this->model){
            $this->model = $this->DB()->getTable($this->tb);
        }
        
        if (!$this->select){
            $this->select = $select = $this->DB()->newSelect($this->tb);
        }
        
        if ($this->p_l){
            foreach ($this->p_l as $v){
                $this->select->select($v);
                $this->keys[$v] = $v;
            }
        }
        
        if ($this->p_s){
            foreach ($this->p_s as $v){
                $this->valid[$v] = \'required\';
            }
        }
    }
    
    /**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function _list(int $page = 1, int $pagesize = 15, array $args = []): array {
        if (is_array($args) && isset($args[\'filters\'])) {
            //wehre
            foreach($args[\'filters\'] as $filter=>$val){
                $this->select->whereLike($filter, \'%\' . $val . \'%\');
            }
        }
        $this->select->whereNot(\'status\', -1);
        $data = $this->DB()->fetchAllPage($this->select, $page, $pagesize);
        $data[\'list\'] = $data[\'list\']->toArray();
        return $data;
    }

    /**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _create(array $params = []) {
        $params = array_fetch($params, $this->p_s);
        $this->initC($params, $this->p_s);
        $model = $this->DB()->getTable($this->tb);
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
    public function _edit(int $id, array $params = []) {
        $params = array_fetch($params, $this->p_s);
        $this->initU($params, $this->p_s);
        $model = $this->DB()->getTable($this->tb);
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
    public function _delete($id) {
        if (!is_array($id)) {
            $id = [$id];
        }
        
        $params = [];
        $params[\'status\'] = -1;
        $this->initU($params);
        return $this->update($this->tb, $id, $params);
    }
    
    /**
     * 更新上下架
     * @param int|array $id
     * @return boolean
     */
    public function _update($id, $params){
        if (!is_array($id)) {
            $id = [$id];
        }
        $this->initU($params);
        return $this->update($this->tb, $id, $params);
    }
    
}';