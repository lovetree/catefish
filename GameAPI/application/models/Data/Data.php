<?php

namespace Data;

abstract class DataModel {

    abstract public function get($name);

    abstract public function save(array $data);

    abstract public function all();

    abstract public function getKey();

    /**
     * @return \PRedis
     */
    protected function redis() {
        return \PRedis::instance();
    }

    public function __get($name) {
        return $this->get($name);
    }

    /**
     * @return bool
     */
    public function exists() {
        return $this->redis()->exists($this->getKey());
    }



}
