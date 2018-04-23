<?php

namespace Dao;

use \Exception;

class Table extends Varien {

    private $_originData = array();
    private $_isModify = false;

    /**
     * 数据库操作对象
     * @var Dao/Connection
     */
    private $_connection;

    /**
     * 数据表名
     * @var string
     */
    private $_name;

    /**
     * 主键列名
     * @var string 
     */
    private $_primaryKey;

    /**
     * 主键的值
     * @var mixed
     */
    private $_primaryValue;

    /**
     * 数据表的所有列名
     * @var type 
     */
    private $_column = array();

    /**
     * 表名前缀
     * @var string 
     */
    private $_prefix = '';

    /**
     * @param Dao/Connection $conn 数据库操作对象
     * @param string $name 数据表名称
     */
    public function __construct($conn, $name, $column, $prefix = '') {
        $this->_connection = $conn;
        $this->_name = $name;
        $this->_column = $column;
        $this->_prefix = $prefix;
        $this->init();
    }

    /**
     * 初始化
     */
    protected function init() {
        if (isset($this->_column['PRI'])) {
            $this->_primaryKey = $this->_column['PRI'];
        }
    }

    public function setData($key, $val = null) {
        if (!$this->_isModify) {
            $this->_isModify = true;
        }
        parent::setData($key, $val);
    }

    /**
     * set data
     * @param string|array $key
     * @param mixed $val
     * @return Varien
     */
    public function setOriginData($key, $val = null) {
        if (is_array($key)) {
            $this->_originData = $key;
        } else {
            $this->_originData[$key] = $val;
        }
        return $this;
    }

    /**
     * get data
     * @param string $key
     * @return mixed
     */
    public function getOriginData($key = null, $default = null) {
        if (is_null($key)) {
            $ret = $this->_originData;
        } else {
            $ret = isset($this->_originData[$key]) ? $this->_originData[$key] : $default;
        }
        return $ret;
    }

    /**
     * 判断当前数据与原始数据是否相同
     * @param string $field
     */
    public function compare($field) {
        return $this->getOriginData($field) === $this->getData($field);
    }

    /**
     * @return bool
     */
    protected function isModify() {
        return $this->_isModify;
    }

    /**
     * 获取数据表名称
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * 获取真实的数据表名称
     * @return string
     */
    public function getRealName() {
        return $this->_prefix . $this->_name;
    }

    /**
     * 获取主键名
     * @return string
     */
    public function getPrimaryKey() {
        return $this->_primaryKey;
    }

    /**
     * 获取主键值
     * @return mixed
     */
    public function getPrimary() {
        return $this->_primaryValue;
    }

    /**
     * 设置主键值
     * @param mixed $val
     */
    public function setPrimary($val) {
        $this->_primaryValue = $val;
    }

    /**
     * 获取所有列名
     * @return array
     */
    public function getColumns() {
        return $this->_column;
    }

    /**
     * 获取数据库操作对象
     * @return Connection
     */
    public function getConnection() {
        return $this->_connection;
    }

    public function unsetConnection() {
        $this->_connection = null;
    }

    /**
     * 清空所数据
     * @return Table
     */
    public function reset() {
        $this->setData(array());
        $this->setPrimary(null);
        return $this;
    }

    /**
     * 加载数据
     * @param mixed $val 值
     * @param string $field 列名，默认为主键列名
     * @return Table
     */
    public function load($val, $field = null) {
        if (is_array($val)) {
            $filter = $val;
        } else {
            if (!$field) {
                $field = $this->getPrimaryKey();
            }
            $filter = array($field => $val);
        }
        $select = $this->getConnection()->newSelect($this, $filter);
        $select->addColumn($this->getColumns());
        $table = $this->getConnection()->fetch($select);
        if ($table) {
            $this->_isModify = false;
        } else {
            return false;
        }
        return $this;
    }

    /**
     * @param array $filter
     * @return Table
     */
    public function loadFirst($filter = array()) {
        $select = $this->getConnection()->newSelect($this->getName(), $filter);
        $select->addColumn($this->getColumns());
        return $this->getConnection()->fetch($select);
    }

    /**
     * @param array $filter
     * @return Collection
     */
    public function loadAll($filter = array()) {
        $select = $this->getConnection()->newSelect($this->getName(), $filter);
        $select->addColumn($this->getColumns());
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * 保存数据,并返回执行结果
     * @return string
     */
    public function save() {
        try {
            if ($this->isModify()) {
                $this->_isModify = false;
                $select = $this->getConnection()->newSelect($this);
                if ($this->getPrimary()) {//更新
                    foreach ($this->getColumns() as $column) {
                        if ($column !== $this->getPrimaryKey() && !$this->compare($column)) {
                            $select->setData($column, $this->getData($column));
                        }
                    }
                    $select->addFilter($this->getPrimaryKey(), $this->getPrimary());
                    $sql = $select->updateSql();
                    if (!$sql) {
                        return $this->getPrimary();
                    }
                    $status = $this->getConnection()->exec($sql);
                    if ($status) {
                        $this->setOriginData($this->getData());
                    }
                    return $this->getPrimary();
                } else {//插入
                    $select->setData($this->getData());
                    $this->getConnection()->exec($select->insertSql());
                    $this->load($this->getConnection()->getLastInsertId());
                    return $this->getPrimary();
                }
            }
        } catch (Exception $e) {
            //die($e->getMessage());
            throw $e;
        }
        return false;
    }

    /**
     * 删除数据，并返回执行结果
     * @return int
     */
    public function remove() {
        if (is_null($this->getPrimary())) {
            return false;
        }
        $select = $this->getConnection()->newSelect($this);
        $select->addFilter($this->getPrimaryKey(), $this->getPrimary());
        return $this->getConnection()->exec($select->deleteSql());
    }

}
