<?php

namespace Dao;

class Cache extends Cache\FileSystem {

    private $_obj = NULL;

    public function __construct($obj = NULL) {
        $this->_obj = $obj;
    }

    public function getCacheKey($key) {
        if (!empty($this->_obj)) {
            $key = str_replace('\\', '_', get_class($this->_obj)) . '-' . $key;
        }
        $filename = parent::getCacheKey($key);
        $path = MIRACLE_CACHE_DIR . $filename;
        return $path;
    }

    public function expire($key, $time) {
        if (!MIRACLE_CACHE) {
            return false;
        }
        return parent::expire($key, $time);
    }

    public function load($key) {
        if (!MIRACLE_CACHE) {
            return false;
        }
        return parent::load($key);
    }

    public function save($key, $val) {
        if (!MIRACLE_CACHE) {
            return false;
        }
        parent::save($key, $val);
    }

}
