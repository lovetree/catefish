<?php

namespace Dao;

use \JsonSerializable;
use \Exception;

class Varien implements JsonSerializable {

    private $_value;
    private $_data;

    public function __construct($value = null, $data = array()) {
        $this->setValue($value);
        $this->setData($data);
    }

    /**
     * set object value
     * @param mixed $val
     * @return Varien
     */
    public function setValue($val) {
        $this->_value = $val;
        return $this;
    }

    /**
     * get object value
     * @return mixed
     */
    public function getValue() {
        return $this->_value;
    }

    /**
     * set data
     * @param string|array $key
     * @param mixed $val
     * @return Varien
     */
    public function setData($key, $val = null) {
        if (is_array($key)) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $val;
        }
        return $this;
    }

    /**
     * get data
     * @param string|array $key
     * @return mixed
     */
    public function getData($key = null, $default = null) {
        if (is_null($key)) {
            $ret = $this->_data;
        } else if (is_string($key) || is_numeric($key)) {
            $ret = isset($this->_data[$key]) ? $this->_data[$key] : $default;
        } else if (is_array($key)) {
            $ret = array();
            foreach ($key as $oneK) {
                $ret[$oneK] = isset($this->_data[$oneK]) ? $this->_data[$oneK] : $default;
            }
        }
        return $ret ?? $default;
    }

    /**
     * delete data
     * @param string $key
     * @return Varien
     */
    public function removeData($key) {
        if (is_null($key)) {
            $this->setData(array());
        } else {
            unset($this->_data[$key]);
        }
        return $this;
    }

    /**
     * whether object has data
     * @param string $key
     */
    public function hasData($key) {
        return is_null($this->getData($key)) ? false : true;
    }

    public function __set($varname, $val) {
        $this->setData($varname, $val);
    }

    public function __get($varname) {
        return $this->getData($varname);
    }

    public function __toString() {
        return strval($this->getValue());
    }

    public function jsonSerialize() {
        return $this->getData();
    }

    public function __setter($var, $val) {
        $this->$var = $val;
    }

    public function __getter($var) {
        return $this->$var;
    }

    public function __call($method, $params) {
        $status = preg_match('/^([s|g]et)(\w+)$/', $method, $match);
        $class = get_called_class();
        if (!$status) {
            die("<b>Fatal Error: </b> Call to undefined method {$class}::{$method}()");
        }
        $varName = $match[2];
        $varName{0} = strtolower($match[2]{0}); //首字符小写
        $varName = '_' . $varName;
        try {
            if (!property_exists($this, $varName)) {
                throw new Exception();
            }
            if ($match[1] == 'set') {
                $this->__setter($varName, array_pop($params));
            } else {
                return $this->__getter($varName);
            }
        } catch (Exception $e) {
            die("<b>Fatal Error: </b> Undefined property: {$class}::\${$varName}");
        }
    }

}
