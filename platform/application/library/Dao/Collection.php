<?php

namespace Dao;

class Collection extends Varien {

    public function __construct($items = array()) {
        $this->setItems($items);
    }

    /**
     * @param mixed $item
     * @param mixed $key
     * @return Collection
     */
    public function addItem($key, $item) {
        if (is_null($key)) {
            $key = $this->count();
        }
        return $this->setData($key, $item);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function getItem($key) {
        return $this->getData($key);
    }

    /**
     * @param array $items
     * @return Collection
     */
    public function setItems($items) {
        return $this->setData($items);
    }

    /**
     * @return mixed
     */
    public function getItems() {
        return $this->getData();
    }

    /**
     * @param mixed $key
     * @return Collection
     */
    public function removeItem($key) {
        return $this->removeData($key);
    }

    /**
     * whether collection has item
     * @param mixed $key
     * @return mixed
     */
    public function hasItem($key) {
        return $this->hasData($key);
    }

    /**
     * get items count
     * @return int
     */
    public function count() {
        return count($this->getItems());
    }

    /**
     * reset the collection
     * @return Collection
     */
    public function reset() {
        return $this->setItems(array());
    }

    public function toArray() {
        $data = array();
        foreach ($this->getItems() as $key => $item) {
            $data[] = $item->getData();
        }
        return $data;
    }

    public function each($func) {
        if (is_callable($func)) {
            foreach ($this->getItems() as $k => &$v) {
                call_user_func($func, $v, $k);
            }
        }
    }

    public function jsonSerialize() {
        return $this->toArray();
    }

    /**
     * 获取集合中的所有数据
     * @return array
     */
    public function getItemsData() {
        $data = array();
        foreach ($this->getItems() as $key => $item) {
            $data[$key] = $item->getData();
        }
        return $data;
    }

    /**
     * 获取集合里指定列的所有数据
     * @param string $column
     * @return array
     */
    public function getColumnValue($column) {
        $ret = array();
        foreach ($this->getItems() as $item) {
            $ret[] = $item->getData($column);
        }
        return $ret;
    }

    /**
     * 获取集合里所有数据主键的值
     * @return array
     */
    public function getAllIds() {
        $ret = array();
        foreach ($this->getItems() as $item) {
            $ret[] = $item->getPrimary();
        }
        return $ret;
    }

    /**
     * 更换键名
     * @param string $key
     */
    public function flip($key) {
        $items = $this->getItems();
        $this->reset();
        foreach ($items as $item) {
            $this->addItem($item->getData($key), $item);
        }
        return $this;
    }

}
