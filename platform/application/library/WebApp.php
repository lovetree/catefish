<?php

class WebApp{
    
    const AFTER_LOGIN = 'AFTER_LOGIN';
    
    private static $_hooks = [];
    
    public static function hook(string $name, callable $callable){
        if(!is_callable($callable)){
            return false;
        }
        self::$_hooks[$name][] = $callable;
        return true;
    }
    
    public static function inform(string $name, $params = []){
        if(isset(self::$_hooks[$name])){
            foreach (self::$_hooks[$name] as $callable){
                call_user_func_array($callable, $params);
            }
        }
    }
    
}