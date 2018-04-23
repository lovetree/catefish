<?php

namespace Dao\Cache;

/**
 * 缓存操作接口
 */
interface Handler {

    public function getCacheKey($key);

    public function save($key, $val);

    public function load($key);

    public function expire($key, $time);
}
