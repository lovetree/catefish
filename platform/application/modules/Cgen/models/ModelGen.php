<?php
namespace Cgen;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/13 0013
 * Time: 19:59
 */
class ModelGenModel extends \Cgen\GeneratorModel{
    public function gen()
    {
        $columns = $this->config['columns'];
        $tableName = $this->config['table_name'];
        $modelName = ucfirst($this->config['model']) . 'Model';
        $tplContent = include $this->config['tpl_path'] . 'model_tpl.php';

        $saveDir = $this->config['save_path_pre'] . '/models/';
        if(!is_dir($saveDir)){
            mkdir($saveDir, 0755, true);
        }
        $savePath =  $saveDir . ucfirst($this->config['model']) . '.php';

        file_put_contents($savePath , $tplContent);
    }
}