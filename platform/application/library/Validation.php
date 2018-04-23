<?php

class Validation {

    private $_errors = array();
    private $_args = null;
    private $_rules = array();
    private $_patterns = array();

    public function __construct($args) {
        $this->_args = $args;
    }

    public function rule($varname, $rule = null) {
        if (is_array($varname)) {
            foreach ($varname as $k => $r) {
                $this->_rules[$k] = explode('|', $r);
            }
        } else {
            $this->_rules[$varname] = explode('|', $rule);
        }
    }

    public function pattern($key, $val = null) {
        if (is_array($key)) {
            foreach ($key as $k => $r) {
                $this->_patterns[$k] = $r;
            }
        } else {
            $this->_patterns[$key] = $val;
        }
    }

    public function run() {
        $ret = true;
        foreach ($this->_rules as $varname => $rules) {
            $val = isset($this->_args[$varname]) ? $this->_args[$varname] : null;
            foreach ($rules as $rule) {
                $rule = explode(':', $rule, 2);
                $method_name = $rule[0] . 'Vaild';
                if (($val === '' || is_null($val)) && $rule[0] != 'required') {
                    continue;
                }
                if (method_exists($this, $method_name)) {
                    list($status, $errmsg) = $this->$method_name($val, isset($rule[1]) ? explode(':', $rule[1]) : null);
                    if (!$status) {
                        $ret = false;
                        $this->addErrors($varname, $errmsg);
                    }
                }
            }
        }
        return $ret;
    }

    protected function addErrors($varname, $error) {
        !isset($this->_errors[$varname]) && ($this->_errors[$varname] = array());
        $this->_errors[$varname][] = $error;
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function getErrorMsg($compel = false) {
        $msg = '';
        array_walk($this->_errors, function($v, $k) use (&$msg) {
            $msg .= sprintf("%s: %s;\n", $k, implode(', ', $v));
        });
        if (!$compel && !DEBUG) {
            $input = App::input();
            logMessage($input->server('REQUEST_URI'), LOG_WARNING);
            logMessage(RCODE_ARG_ILLEGAL . "\r\n" . $msg, LOG_WARNING);
            return null;
        }
        return $msg;
    }

    protected function requiredVaild($val) {
        if (is_null($val) || $val == '') {
            return array(false, 'required');
        }
        return array(true, NULL);
    }

    protected function numberVaild($val, $args = null) {
        if (is_numeric($val)) {
            if (empty($args)) {
                return array(true, NULL);
            }
            $len = strlen($val);
            $min = $args[0];
            $max = isset($args[1]) ? $args[1] : $min;
            if ($len >= $min && $len <= $max) {
                return array(true, NULL);
            } else {
                return array(false, 'number length overflow');
            }
        }
        return array(false, 'not a number');
    }

    protected function maxVaild($val, $args = null) {
        if (is_numeric($val)) {
            $max = $args[0];
            if ($val <= $max) {
                return array(true, NULL);
            }
        }
        return array(false, 'greater than presuppose');
    }

    protected function minVaild($val, $args = null) {
        if (is_numeric($val)) {
            $max = $args[0];
            if ($val >= $max) {
                return array(true, NULL);
            }
        }
        return array(false, 'less than presuppose');
    }

    protected function positive_numberVaild($val) {
        if (is_numeric($val) && $val >= 0) {
            return array(true, NULL);
        }
        return array(false, 'not a positive_number');
    }

    protected function phoneVaild($val) {
        if (preg_match('/^((?:13[0-9]{1}|15[0-9]{1}|18[0-9]{1})+\d{8})$/', $val)) {
            return array(true, NULL);
        }
        return array(false, 'not a phone number');
    }

    protected function emailVaild($val) {
        if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
            return array(true, NULL);
        }
        return array(false, 'not a email');
    }

    protected function insideVaild($val, $args = null) {
        if (is_array($args) && in_array($val, $args)) {
            return array(true, NULL);
        }
        return array(false, 'nonsupport');
    }

    protected function lengthVaild($val, $args = null) {
        $len = strlen($val);
        if (is_array($args)) {
            if (isset($args[1])) {
                if ($len >= $args[0] && $len <= $args[1]) {
                    return array(true, NULL);
                }
            } else {
                if ($len >= $args[0]) {
                    return array(true, NULL);
                }
            }
        }
        return array(false, 'length overflow');
    }

    protected function dateVaild($val) {
        if (strtotime($val) !== false) {
            return array(true, null);
        }
        return array(false, 'not a format date');
    }

    protected function stringVaild($val) {
        if (is_string($val)) {
            return array(true, null);
        }
        return array(false, 'not a string');
    }

    protected function patternVaild($val, $args = null) {
        $key = $args[0];
        if (isset($this->_patterns[$key])) {
            if (preg_match($this->_patterns[$key], $val)) {
                return array(true, null);
            }
        }
        return array(false, 'pattern faild');
    }

}
