<?php

namespace Dao;

use \PDO;
use \ReflectionClass;
use \Exception;

/**
 * 数据库操作对象
 */
class Connection {

    const DRIVER = 'mysql:host=%s;dbname=%s;port=%s';
    const HOST = 'host';
    const PORT = 'port';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const DBNAME = 'dbname';
    const CHARSET = 'charset';
    const HANDLE = 'handle';
    const PREFIX = 'prefix';

    /**
     * 数据库名称
     * @var string
     */
    private $_dbname;

    /**
     * 字符集
     * @var string 
     */
    private $_charset = 'utf8';

    /**
     * 表前缀
     * @var string
     */
    private $_prefix = '';

    /**
     * 句柄
     * @var mixed 
     */
    private $_handle;

    /**
     * 日志记录
     * @var type 
     */
    private $_query_logs = array();

    public function __construct() {
        $args = func_get_args();
        $args = $args[0];
        $this->_dbname = isset($args[self::DBNAME]) ? $args[self::DBNAME] : false;
        $this->_charset = isset($args[self::CHARSET]) ? $args[self::CHARSET] : false;
        $this->_prefix = isset($args[self::PREFIX]) ? $args[self::PREFIX] : '';
        $host = isset($args[self::HOST]) ? $args[self::HOST] : '127.0.0.1';
        $port = isset($args[self::PORT]) ? $args[self::PORT] : '3306';
        $username = isset($args[self::USERNAME]) ? $args[self::USERNAME] : false;
        $password = isset($args[self::PASSWORD]) ? $args[self::PASSWORD] : false;
        $handle = isset($args[self::HANDLE]) ? $args[self::HANDLE] : NULL;
        if (!$this->_dbname || !$this->_charset || !$username) {
            die('[Database Connection] Missing arguments');
        }
        $this->_handle = $this->connect($host, $port, $username, $password, $handle);
    }

    /**
     * 返回字符集
     * @return string
     */
    public function getCharset() {
        return $this->_charset;
    }

    /**
     * 返回数据库名称
     * @return string
     */
    public function getDbName() {
        return $this->_dbname;
    }

    /**
     * 返回数据库连接的句柄
     * @return \PDO
     */
    public function getHandle() {
        return $this->_handle;
    }

    /**
     * 获取数据表模型
     * @param string $name
     * @return Table 失败则返回false
     */
    public function getTable($name) {
        try {
            $className = 'Dao\Table';
            $column = $this->getTableColumns($this->realName($name));
            if (!$column) {
                return false;
            }
            $ref = new ReflectionClass($className);
            $model = $ref->newInstance($this, $name, $column, $this->_prefix);
        } catch (Exception $e) {
            throw $e;
        }
        return $model;
    }

    /**
     * 
     * @param string $name
     * @return string
     */
    public function realName($name) {
        return $this->_prefix . $name;
    }

    /**
     * @param string $table
     * @param array $filter
     * @return Select
     */
    public function newSelect($table, $filter = array()) {
        if (is_string($table)) {
            $table = $this->getTable($table);
        } elseif (!$table instanceof Table) {
            die('class \'' . get_class($table) . '\' must be instanceof Class \'Select\'');
        }
        $selectClass = 'Dao\Select';
        $select = new $selectClass($table, $filter);
        return $select;
    }

    /**
     * 连接数据库并返回该连接
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @return mixed 数据库连接对象
     */
    protected function connect($host, $port, $username, $password, $handle = NULL) {
        if (!is_null($handle) && $handle instanceof PDO) {
            return $handle;
        }
        $dsn = sprintf(self::DRIVER, $host, $this->getDbName(), $port);
        $pdo = new PDO($dsn, $username, $password, array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $this->getCharset() . '\''
        ));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /**
     * 返回错误信息
     * @return string
     */
    public function getErrorInfo() {
        $error = $this->getHandle()->errorInfo();
        return array_pop($error);
    }

    /**
     * 获取数据表的所有列明
     * @param string $name 表名
     */
    protected function getTableColumns($name) {
        static $table_columns = array();
        if (isset($table_columns[$name])) {
            $column = $table_columns[$name];
        } else {
            $cache = new Cache($this);
            $column = $cache->load($name);
            if (!$column) { //缓存中没有
                $sql = 'describe `' . $name . '`';
                $data = $this->search($sql);
                if (!$data) {
                    return false;
                }
                $column = array();
                foreach ($data as $item) {
                    if ($item['Key'] === 'PRI') {
                        $column['PRI'] = $item['Field'];
                    } else {
                        $column[] = $item['Field'];
                    }
                }
                $cache->save($name, $column);
            }
            $table_columns[$name] = $column;
        }
        return $column;
    }

    /**
     * 执行SQL
     * @param type $sql
     * @return int 返回影响的行数
     */
    public function exec($sql) {
        $this->addQueryLog($sql);
        try {
            $status = $this->getHandle()->exec($sql);
        } catch (Exception $e) {
            logMessage($sql);
            logMessage($e->getMessage() . ';' . $sql);
            throw new Exception($e->getMessage() . ';' . $sql);
        }
        return $status;
    }

    /**
     * 执行SQL，返回一个结果集对象
     * @param string $sql
     * @return PDOStatement
     */
    public function query($sql) {
        $this->addQueryLog($sql);
        try {
            $statement = $this->getHandle()->query($sql);
        } catch (Exception $e) {
            logMessage($sql);
            logMessage($e->getMessage() . ';' . $sql);
            throw new Exception($e->getMessage() . ';' . $sql);
        }
        return $statement;
    }

    /**
     * 执行SQL，返回对象数组
     * @param string $sql
     * @return array
     */
    public function search($sql) {
        try {
            $statement = $this->query($sql);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            $statement->closeCursor();
        } catch (Exception $e) {
            return false;
        }
        return $data;
    }

    /**
     * 开启/关闭自动提交
     * @param boolean $type
     */
    public function setAutoCommit($type) {
        $this->getHandle()->setAttribute(PDO::ATTR_AUTOCOMMIT, $type);
    }

    /**
     * 开始一个新的事务
     * @return bool
     */
    public function beginTransaction() {
        return $this->getHandle()->beginTransaction();
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit() {
        $status = $this->getHandle()->commit();
        return $status;
    }

    /**
     * 事务回滚
     * @return bool
     */
    public function rollBack() {
        $status = $this->getHandle()->rollBack();
        return $status;
    }

    public function trancation($func) {
        $ret = false;
        if (is_callable($func)) {
            $rb = false;
            $this->beginTransaction();
            try {
                if(is_callable($func)){
                    $ret = $func($this, $rb);
                    $rb ? $this->rollback() : $this->commit();
                }
            } catch (Exception $e) {
                $this->rollBack();
                throw new Exception($e->getMessage() . ';' . $sql);
            }
        }
        return $ret;
    }

    /**
     * 分页
     * @param int $page
     * @param int $pageCount
     * @param int $showPageCount
     * @return array
     */
    public function getPages($page, $pageCount, $showPageCount = 10) {
        $midNum = floor($showPageCount / 2);
        if ($pageCount > $showPageCount) {
            if ($page <= $showPageCount / 2) {
                $pages = range(1, $showPageCount);
            } elseif ($page >= $pageCount - $midNum) {
                $pages = range($pageCount - $showPageCount + 1, $pageCount);
            } else {
                $pages = range($page - $midNum + 1, $page + $midNum);
            }
        } else {
            if ($pageCount > 1) {
                $pages = range(1, $pageCount);
            } else {
                $pages = array(1);
            }
        }
        return $pages;
    }

    /**
     * 获取单条信息
     * @param Select $select
     * @return Table
     */
    public function fetch(Select $select) {
        $new_select = clone $select;
        $new_select->limit(0, 1);
        $statement = $this->query($new_select->toString());
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $data = $statement->fetch();
        if ($data) {
            $table = $new_select->getTable();
            $table->setPrimary($data[$table->getPrimaryKey()]);
            $table->setOriginData($data);
            $table->setData($data);
            return $table;
        }
        return false;
    }

    /**
     * 获取多条数据
     * @param Select $select
     * @return Collection
     */
    public function fetchAll(Select $select) {
        $collection = new Collection();
        try {
            $data = $this->search($select->toString());
            if(!$data){
                return $collection;
            }
            $ret = array();
            foreach ($data as $item) {
                $temp = clone $select->getTable();
                $temp->setPrimary($item[$temp->getPrimaryKey()]);
                $temp->setOriginData($item);
                $temp->setData($item);
                $collection->addItem($temp->getPrimary(), $temp);
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
        return $collection;
    }

    /**
     * 获取多条数据并对数据进行分页处理
     * @param Select $select
     * @param int $page
     * @param int $pageSize
     * @param int $showPageCount
     * @return array
     */
    public function fetchAllPage(Select $select, $page = 1, $pageSize = 10, $showPageCount = 10) {
        $rowCount = intval($this->rowCount($select));
        $pageCount = ceil($rowCount / $pageSize);
        $page = $page > $pageCount ? $pageCount : $page;
        $page = $page < 1 ? 1 : $page;
        $startRow = ($page - 1) * $pageSize;
        $select->limit($startRow, $pageSize);
        $data = $this->fetchAll($select);
        $returnArray = array();
        //$returnArray["page"] = $page;
        //$returnArray["pageSize"] = $pageSize;
        $returnArray['pageCount'] = $pageCount == 0 ? 1 : $pageCount;
        //$returnArray["pages"] = $this->getPages($page, $pageCount, $showPageCount);
        $returnArray['total'] = $rowCount;
        //$returnArray["dataCount"] = $data->count();
        $returnArray['list'] = $data;
        return $returnArray;
    }

    /**
     * 获取数据总数
     * @param Select $select
     * @return int
     */
    public function rowCount(Select $select) {
        try {
            $dc = $select->getDefaultColumn();
            $select->defaultColumn(0);
            $data = $this->search(preg_replace('/select .*? from/', 'select count(1) as cnt from ', $select->toString(), 1));
            $count = $data[0]['cnt'];
            $select->defaultColumn($dc);
            return $count;
        } catch (Exception $e) {
            throw new Exception($e);
            return 0;
        }
    }

    public function getLastInsertId() {
        return $this->getHandle()->lastInsertId();
    }

    /**
     * 添加日志
     */
    protected function addQueryLog($sql) {
        array_push($this->_query_logs, $sql);
    }

    /**
     * 获取日志
     */
    public function getQueryLog() {
        return $this->_query_logs;
    }

    /**
     * 清除日志
     */
    public function clearQueryLog() {
        $this->_query_logs = array();
    }

    /**
     * 获取数据表的所有列详细信息
     * @param string $name 表名
     */
    public function getTableColumnsDetail($name) {
        static $table_columns = array();
        if (isset($table_columns[$name])) {
            $data = $table_columns[$name];
        } else {
            $cache = new Cache($this);
            $data = $cache->load($name . 'column');
            if (!$data) { //缓存中没有
                $sql = 'select 
                        column_name, 
                        column_comment,
                        is_nullable,
                        character_maximum_length
                        from information_schema.columns 
                        where table_name = "'.$name . '"';
                $data = $this->search($sql);
                if (!$data) {
                    return false;
                }
                
                $cache->save($name . 'column', $data);
            }
            $table_columns[$name] = $data;
        }
        return $data;
    }
}
