<?php
define("APP_PATH", realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */

define('MIRACLE_CACHE', false);
define('MIRACLE_CACHE_DIR', APP_PATH . '/dao_runtime/');
if (!file_exists(MIRACLE_CACHE_DIR)) {
    mkdir(MIRACLE_CACHE_DIR);
    chmod(MIRACLE_CACHE_DIR, 0777);
}

require_once APP_PATH . '/application/constant.php';
require_once APP_PATH . '/application/function/function.php';
require_once 'redis_key.php';

$app = new Yaf\Application(APP_PATH . "/conf/application.ini");

define('DEBUG', boolval($app->getConfig()->get('application.debug')));

if(!DEBUG){
    error_reporting('E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED');
    ini_set('display_errors', 'Off');
}

$app->bootstrap()->run();
