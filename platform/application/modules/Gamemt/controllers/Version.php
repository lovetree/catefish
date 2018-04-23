<?php 
class VersionController	extends BaseController{
	public function indexAction(){
		$redis = PRedis::instance();
	    $redis->select(R_GAME_DB);
	    $version = $redis->hget(RK_SYS_CONFIG, 'login_plat_ver');
	    //$version =  intval(str_replace('.', '', $redis->hget(RK_SYS_CONFIG, 'login_plat_ver')));
		$this->getView()->assign('version',$version);
        $this->getView()->assign('h_title', '设置版本号');
		$this->display('index');
	}
	public function saveAction(){
		$request = $this->input()->request();
		$redis = PRedis::instance();
	    $redis->select(R_GAME_DB);
	    $res = $redis->hset(RK_SYS_CONFIG,'login_plat_ver',$request['version']);
	    if($res!==false){
	    	return $this->succ();
	    }else{
	    	return $this->faild('修改失敗');
	    }
	}
}