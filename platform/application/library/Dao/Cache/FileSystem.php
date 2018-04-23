<?php

namespace Dao\Cache;

class FileSystem implements Handler {

    /**
     * 
     * @param type $key
     */
    public function getCacheKey($key) {
        $filename = 'MC-' . $key . '@' . md5($key);
        return $filename;
    }

    /**
     * 设置过期时间
     * @param type $key
     * @param type $time
     */
    public function expire($key, $time) {
        return false;
    }

    /**
     * 获取数据
     * @param type $key
     * @return type
     * @throws Exception
     */
    public function load($key) {
        $filename = $this->getCacheKey($key);
        if (!file_exists($filename)) {
            return false;
        }
        if (!is_readable($filename)) {
            throw new Exception("can not read the file ({$filename})");
        }
        $string = file_get_contents($filename);
        $data = $this->unserialize($string);
        return $data;
    }

    /**
     * 保存数据
     * @param type $key
     * @param type $val
     * @throws Exception
     */
    public function save($key, $val) {
        $filename = $this->getCacheKey($key);
        if (file_exists($filename) && !is_writable($filename)) {
            throw new Exception("can not write to the file ({$filename})");
        }
        $string = $this->serialize($val);
        file_put_contents($filename, $string);
        return true;
    }

    /**
     * 序列化数据
     * @param type $val
     * @return type
     */
    protected function serialize($val) {
        return serialize($val);
    }

    /**
     * 反序列化数据
     * @param type $serialize
     * @return type
     */
    protected function unserialize($serialize) {
        return unserialize($serialize);
    }

}
