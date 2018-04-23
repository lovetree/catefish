<?php

namespace Dao;

class Select extends Varien {

    protected $_table;
    protected $_filter = array();
    protected $_limit;
    protected $_groupBy;
    protected $_order = array();
    protected $_column = array();
    protected $_rawColumn = array();
    protected $_defaultColumn = '*';
    protected $_join = array();

    public function __construct(Table $table, $filter = array()) {
        $this->_table = $table;
        $this->_filter = $filter;
    }

    /**
     * @return Table
     */
    public function getTable() {
        return $this->_table;
    }

    /**
     * @param string $column
     * @return Select
     */
    public function defaultColumn($column) {
        $this->_defaultColumn = $column;
        return $this;
    }

    /**
     * @param string|array $column
     * @return Select
     */
    public function addColumn($column) {
        return $this->select($column);
    }

    /**
     * @param string|array $column
     * @return Select
     */
    public function select($column, $raw = false) {
        if ($raw) {
            $this->_rawColumn[] = $column;
        } else {
            if (is_string($column)) {
                $column = explode(',', $column);
            }
            if (is_array($column)) {
                foreach ($column as $v) {
                    $this->_column[] = trim($v);
                }
            } else {
                $this->_column[] = $column;
            }
        }
        return $this;
    }

    /**
     * @param string $key 字段名
     * @param string $val
     * @return Select
     */
    public function addFilter($key, $val) {
        return $this->where($key, $val);
    }

    /**
     * @param string $key 字段名
     * @param string $val
     * @return Select
     */
    public function where($key, $val, $symbol = '=') {
        if(!is_array($val) && $symbol !== '='){
            $val = [$symbol => $this->getConnection()->getHandle()->quote($val)];
        }

        if(isset($this->_filter[$key])){
            $this->_filter['#and#'.$key] = $val;
        }else{
            $this->_filter[$key] = $val;
        }

        return $this;
    }
    public function isnotnull($key,$symbol = 'is not null') {

            $val = [$symbol];


        if(isset($this->_filter[$key])){
            $this->_filter['#and#'.$key] = $val;
        }else{
            $this->_filter[$key] = $val;
        }

        return $this;
    }

    /**
     * @param string $key 字段名
     * @param string $val
     * @return Select
     */
    public function orWhere($key, $val, $symbol = '=') {
        if(!is_array($val) && $symbol !== '='){
            $val = [$symbol => $this->getConnection()->getHandle()->quote($val)];
        }

        $this->_filter['#or#' . $key] = $val;

        return $this;
    }

    /**
     * @param string $key 字段名
     * @param string $val
     * @return Select
     */
    public function whereIn($key, $val) {
        return $this->where($key, array('in' => $val));
    }

    /**
     * @param string $key 字段名
     * @param string $val
     * @return Select
     */
    public function whereNot($key, $val) {
        return $this->where($key, array('!=' => $val));
    }
    
    /**
     * @param string $key 字段名
     * @param string $val
     * @return Select
     */
    public function whereLike($key, $val){
        return $this->where($key, array('like' => '"' . $val . '"'));
    }

    /**
     * @param string $key 字段名
     * @param string $val
     * @return Select
     */
    public function orWhereLike($key, $val){
        return $this->orWhere($key, array('like' => '"' . $val . '"'));
    }

    /**
     * @param int $offset
     * @param int $len
     * @return Select
     */
    public function limit($offset, $len = null) {
        $this->_limit = array(
            'offset' => $offset,
            'len' => $len
        );
        return $this;
    }

    /**
     * @return Select
     */
    public function group() {
        $this->_groupBy = func_get_args();
        return $this;
    }

    /**
     * @param string $field
     * @param string $type
     * @return Select
     */
    public function order($field, $type = 'asc') {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->_order[$k] = $v;
            }
        } else {
            $this->_order[$field] = $type;
        }
        return $this;
    }

    public function __toString() {
        return $this->toString();
    }

    /**
     * 
     * @param type $type
     * @param type $table
     * @param type $on
     * @param type $column
     * @return Select
     */
    protected function _join($type, $table, $on, $column = array()) {
        $item = array();
        $item['type'] = $type;
        $item['table'] = $table;
        $item['on'] = $on;
        $item['column'] = $column;
        $this->_join[] = $item;
        return $this;
    }

    /**
     * 内连接
     * @param array|string $table
     * @param array|string $on
     * @param array|string $column
     * @return Select
     */
    public function join($table, $on, $column = array()) {
        return $this->_join('inner', $table, $on, $column);
    }

    /**
     * 左连接
     * @param array|string $table
     * @param array|string $on
     * @param array|string $column
     * @return Select
     */
    public function joinLeft($table, $on, $column = array()) {
        return $this->_join('left', $table, $on, $column);
    }

    /**
     * 右连接
     * @param array|string $table
     * @param array|string $on
     * @param array|string $search
     * @return Select
     */
    public function joinRight($table, $on, $column = array()) {
        return $this->_join('right', $table, $on, $column);
    }

    /**
     * 获取数据库链接
     * @return Connection
     */
    public function getConnection() {
        return $this->getTable()->getConnection();
    }

    /**
     * 构造SQL语句
     * @return null|string
     */
    private function prepareFilter() {
        $where = array();
        $handle = $this->getTable()->getConnection()->getHandle();
        foreach ($this->getFilter() as $field => $item) {
            $field = '`' . implode('`.`', explode('.', $field)) . '`';
            $cond = '';
            if (is_array($item)) {
                $size = count($item);
                $count = 0;
                foreach ($item as $key => $val) {
                    $key = strtolower($key);
                    if (in_array($key, array('in', 'not in', 'exists', 'not exists'))) {
                        $cond .= "({$field} {$key}" . '("' . implode('","', $val) . '"))';
                    } else if (is_numeric($key)) {
                        $cond .= "({$field} {$val})";
                    } else {
                        $cond .= "({$field} {$key} {$val})";
                    }
                    $count++;

                    if(stripos($field, '#or#') !== false){
                        $cond = ' or ' .$cond;
                    }elseif($count != $size) {
                        $cond .= ' and ';
                    }
                }
            } else {
                $cond = "({$field} = " . $handle->quote($item) . ')';
            }
            $where[] = ' ' . $cond;
        }
        if (!count($where)) {
            return null;
        }
        $sql = ' where' . implode(' and', $where);
        return $sql;
    }

    private function prepareLimit() {
        if (!$this->getLimit()) {
            return null;
        }
        $sql = ' limit ' . $this->_limit['offset'];
        if ($this->_limit['len']) {
            $sql .= ',' . $this->_limit['len'];
        }
        return $sql;
    }

    private function prepareColumn() {
        $sql = array();
        $use_Default = false;
        
        if (count($this->getColumn()) || count($this->getRawColumn())) {
            $columns = $this->getColumn();
            foreach ($columns as $column) {
                $column = explode(' as ', $column);
                $str = '`' . implode('`.`', explode('.', $column[0])) . '`';
                if (isset($column[1])) {
                    $str .= ' as ' . $column[1];
                }
                $sql[] = $str;
            }
            $columns = $this->getRawColumn();
            foreach ($columns as $column) {
                $sql[] = $column;
            }
        } else if ($this->getDefaultColumn()) {
            $use_Default = true;
            $sql[] = '`main_table`.' . $this->getDefaultColumn();
        }
        if ((!$use_Default || $this->getDefaultColumn() !== '*') && !in_array($this->getTable()->getPrimaryKey(), $this->getColumn())) {
            $sql[] = '`main_table`.`' . $this->getTable()->getPrimaryKey() . '`';
        }
        $joins = $this->getJoin();
        foreach ($joins as $item) {
            if (is_array($item['table'])) {
                $table = array_keys($item['table']);
                $table = array_shift($table);
            } else {
                $table = $item['table'];
            }
            if (is_array($item['column'])) {
                foreach ($item['column'] as $column) {
                    $sql[] = "`{$table}`.`{$column}`";
                }
            } else {
                $sql[] = "`{$table}`.`{$item['column']}`";
            }
        }
        return implode(',', $sql);
    }

    private function prepareJoin() {
        $ret = array();
        $joins = $this->getJoin();
        foreach ($joins as $item) {
            if (is_array($item['table'])) {
                $asName = array_keys($item['table']);
                $asName = array_shift($asName);
                $table = array_shift($item['table']) . ' as ' . $asName;
            } else {
                $table = $item['table'];
            }
            if (is_array($item['on'])) {
                $on = implode(' and ', $item['on']);
            } else {
                $on = $item['on'];
            }
            $sql = " {$item['type']} join {$table}";
            if ($on) {
                $sql .= ' on ' . $on;
            }
            $ret[] = $sql;
        }
        $joinSql = implode('', $ret);
        return $joinSql;
    }

    private function prepareOrder() {
        $sql = null;
        $count = count($this->_order);
        if ($count) {
            $sql = ' order by ';
            foreach ($this->_order as $field => $type) {
                $sql .= ' ' . $field . ' ' . $type;
                if (--$count > 0) {
                    $sql .= ',';
                }
            }
        }
        return $sql;
    }

    /**
     * @return string
     */
    public function toString() {
        $sql = 'select ' . $this->prepareColumn() . ' from `' . $this->getTable()->getRealName() . '` as main_table';
        $sql .= $this->prepareJoin();
        $sql .= $this->prepareFilter();
        $sql .= $this->getGroupBy() ? ' group by ' . implode(',', $this->getGroupBy()) : null;
        $sql .= $this->prepareOrder();
        $sql .= $this->prepareLimit();
        $sql = str_replace('#and#', '', $sql);
        $sql = str_replace('#or#', '', $sql);
        $sql = str_replace('and  or', 'or', $sql);
        return $sql;
    }

    public function deleteSql() {
        $sql = 'delete from `' . $this->getTable()->getRealName() . '`' . $this->prepareFilter();
        return $sql;
    }

    public function insertSql() {
        $columns = $this->getTable()->getColumns();
        $data = array();
        $fields = array();
        foreach ($columns as $column) {
            if (!is_null($this->getData($column))) {
                $fields[] = '`' . $column . '`';
                $data[] = $this->getTable()->getConnection()->getHandle()->quote($this->getData($column));
            }
        }
        $fieldString = implode(',', $fields);
        $dataString = implode(',', $data);
        $sql = 'insert into `' . $this->getTable()->getRealName() . "` ({$fieldString}) values ({$dataString})";
        return $sql;
    }

    public function updateSql() {
        $columns = $this->getTable()->getColumns();
        $fields = array();
        foreach ($columns as $column) {
            $field_data = $this->getData($column);
            if (!is_null($field_data)) {
                if(is_array($field_data)){
                    $fields[] = '`' . $column . '` = ' . $field_data[0];
                }else{
                    $fields[] = '`' . $column . '` = ' . $this->getTable()->getConnection()->getHandle()->quote($field_data);
                }
            }
        }
        if (!count($fields)) {
            return false;
        }
        $fieldString = implode(',', $fields);
        $sql = 'update `' . $this->getTable()->getRealName() . "` set {$fieldString}" . $this->prepareFilter();
        return $sql;
    }

    /**
     * 批量插入记录
     * @param array<array>|array<Table> $arr 记录
     */
    public function insert($columns, $arr) {
        if (!is_array($arr)) {
            return false;
        }
        $dataString = '';
        $arr_count = count($arr);
        foreach ($arr as $obj) {
            $val_list = array();
            if (is_array($obj)) {
                $val_list = $obj;
            } else if ($obj instanceof Table) {
                $val_list = $obj->getData($columns);
            }
            array_walk($val_list, function(&$item) {
                $item = $this->getTable()->getConnection()->getHandle()->quote(trim($item));
            });
            $dataString .= '(' . implode(',', $val_list) . ')';
            if (--$arr_count > 0) {
                $dataString .= ',';
            }
        }
        $fieldString = '`' . implode('`,`', $columns) . '`';
        $sql = 'insert into `' . $this->getTable()->getRealName() . "` ({$fieldString}) values {$dataString}";
        return $sql;
    }
    
    /**
     * 批量插入记录
     * @param array<array>|array<Table> $arr 记录
     */
    public function insertUpdate($columns, $insert_arr, $update_arr) {
        $sql = $this->insert($columns, $insert_arr);
        if($sql){
            $ups = [];
            foreach($update_arr as $k => $v){
                $ups[] = '`' . $k . '` = ' . $v;
            }
            $str = implode(',', $ups);
            $sql .= ' ON DUPLICATE KEY UPDATE ' . $str;
        }
        return $sql;
    }

}
