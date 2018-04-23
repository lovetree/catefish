<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/13 0013
 * Time: 17:04
 */
namespace Cgen;

class ControllerGenModel extends GeneratorModel{
    public function gen(){
        $columns = $this->config['columns'];
        $controllerName = ucfirst($this->config['controller']) . 'Controller';
        if('Index' == $this->config['module']){
            $modelName = ucfirst($this->config['model']) . 'Model';
        }else{
            $modelName = '\\' . ucfirst($this->config['module']) .'\\'. ucfirst($this->config['model']) . 'Model';
        }
        $tplContent = include $this->config['tpl_path'] . 'controller_tpl.php';

        $saveDir = $this->config['save_path_pre'] . '/controllers/';
        if(!is_dir($saveDir)){
            mkdir($saveDir, 0755, true);
        }
        $savePath =  $saveDir . ucfirst($this->config['controller']) . '.php';

        file_put_contents($savePath , $tplContent);
    }
}
