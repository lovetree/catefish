<?php

trait EasyBack {

    /**
     * @return \Input
     */
    protected function input(): Input {
        return Yaf\Registry::get('__INPUT__');
    }

    /**
     * 
     * @return \Dao\Connection
     */
    protected function DB(): \Dao\Connection {
        return Yaf\Registry::get('__DB__')();
    }

    /**
     * 
     * @return \Dao\Connection
     */
    protected function DB_GameLog(): \Dao\Connection {
        return Yaf\Registry::get('__DB_GAMELOG__')();
    }

    /**
     * 
     * @return Yaf\Config_Abstract
     */
    protected function config() {
        return Yaf\Application::app()->getConfig();
    }
    /**
     * post请求
     */
    protected function http_post_request($url, $param = NULL, $header = NULL) {
        if(is_array($param)){
            $param = http_build_query($param);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(is_array($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

        $res = curl_exec( $ch );
        curl_close( $ch );
        return $res;
    }

    /**
     * 接口数据数据返回
     * @param int $result
     * @param string $msg
     * @param array $data
     */
    protected function jsonReturn($result = RCODE_SUCC, string $msg = '', array $data = null) {
        if ($result == RCODE_SUCC) {
            $ret = array(
                'result' => $result,
                'msg' => $msg,
                'data' => $data
            );
        } else {
            //返回数据
            $ret = array(
                'result' => $result,
                'msg' => $msg,
                'data' => null
            );
        }
        //返回数据
        $json = json_encode($ret, JSON_UNESCAPED_UNICODE);
        echo $json;

        //钩子
        WebApp::inform('Feedback', [$result, $msg]);
    }

    /**ip*/
    protected function get_location(string $ip){
        ob_start();
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$ip);
        curl_exec($curl);
        $location = ob_get_contents();
        ob_end_clean();
        if($location===FALSE) return "";
        $location = json_decode($location, TRUE);
        if (!isset($location['ret']) || $location['ret'] != 1) return "";
        return $location['country'] . '-' . $location['province'] . '-' . $location['city'];
    }


    /**
     * 重构返回值，updated_uid,created_uid,c_time,u_time
     */
    protected function walkData(array &$data) {
        if (!$data){
            return;
        }
        if (count($data) == count($data, 1)){
            if (isset($data['updated_uid']) && $data['updated_uid']){
            }
            if (isset($data['u_time']) && is_numeric($data['u_time'])){
                $data['u_time'] = date('Y-m-d H:i:s', $data['u_time']);
            }
            if (isset($data['c_time']) && is_numeric($data['c_time'])){
                $data['c_time'] = date('Y-m-d H:i:s', $data['c_time']);
            }
            if (isset($data['ip']) && is_numeric($data['pi'])){
                $data['ip'] = long2ip($data['ip']);
                $data['address'] = $this->get_location($data['ip']);
            }
        }else{
            $ids = [];
            foreach ($data as $k=>$v){
                if (isset($v['u_time']) && is_numeric($v['u_time'])){
                    $data[$k]['u_time'] = date('Y-m-d H:i:s', $v['u_time']);
                }
                if (isset($v['c_time']) && is_numeric($v['c_time'])){
                    $data[$k]['c_time'] = date('Y-m-d H:i:s', $v['c_time']);
                }
                if (isset($v['updated_uid']) && $v['updated_uid']){
                    $ids[] = $v['updated_uid'];
                }
                if (isset($v['created_uid']) && $v['created_uid']){
                    $ids[] = $v['created_uid'];
                }
                if (isset($v['ip']) && is_numeric($v['ip'])){
                    $data[$k]['ip'] = long2ip($data[$k]['ip']);
                    $data[$k]['address'] = $this->get_location($data[$k]['ip']);
                }
            }
            if ($ids){
                //$ids = implode(',', array_unique($ids));
                $select = $this->DB()->newSelect('ms_admin');
                $select->select('id');
                $select->select('username');
                $select->whereIn('id', $ids);
                $adminList = $this->DB()->fetchAll($select)->toArray();
                foreach ($adminList as $k=>$v){
                    $adminList[$v['id']] = $v;
                    unset($adminList[$k]);
                }
                foreach ($data as $k=>$v){
                    if (isset($v['updated_uid']) && $v['updated_uid']){
                        $data[$k]['updated_uid'] = $adminList[$v['updated_uid']]['username'];
                    }
                    if (isset($v['created_uid']) && $v['created_uid']){
                        $data[$k]['created_uid'] = $adminList[$v['created_uid']]['username'];
                    }
                }
            }
        }
    }
    
    /**
     * 成功返回数据
     * @param array $data
     * @return boolean
     */
    protected function succ(array $data = array(), bool $struct = true) {
        if ($struct) {
            $this->jsonReturn(RCODE_SUCC, '', $data);
        } else {
            echo json_encode($data);
        }
    }

    /**
     * 失败返回
     * @param int $code
     * @param string $msg
     * @return boolean
     */
    protected function failed(string $msg = '', int $code = RCODE_FAIL) {
        //返回数据
        $this->jsonReturn($code, $msg);
    }

    /**
     * 参数校验
     * @param type $rules
     */
    protected function validation(array $args, array $rules, array $pattern = array()): bool {
        $valid = new Validation($args);
        $valid->rule($rules);
        $valid->pattern($pattern);
        if (!$valid->run()) {
            $this->failed("参数错误", RCODE_ARG_ILLEGAL);
            logMessage($valid->getErrorMsg());
            logMessage(json_encode($args));
            return false;
        }
        return true;
    }

    /**
     * 记录系统操作日志
     */
    private function addSysLog($result, $msg) {
        //非登录状态不记录日志
        if (!isset($_SESSION[SES_LOGIN])) {
            return false;
        }
        $request = Yaf\Application::app()->getDispatcher()->getRequest();
        $action = strtolower(implode('/', [$request->getModuleName(), $request->getControllerName(), $request->getActionName()]));

        //检测是否需要记录
        $check_cfg = include APP_CFG_ACTLOG;
        if (!in_array($action, array_keys($check_cfg))) {
            return false;
        }

        $input = $this->input();
        $session = $_SESSION[SES_LOGIN] ?? array();
        $req_data = $input->request();
        $action_desc = $check_cfg[$action][0];
        $action_func = $check_cfg[$action][1] ?? null;
        $log = $this->DB()->getTable('ms_admin_log');
        if ($action_func && is_callable($action_func)) {
            $target_id = call_user_func_array($action_func, [&$action_desc, $req_data]);
            if ($target_id) {
                $log->setData('target_id', $target_id);
            }
        }
        $log->setData('operator_id', $session['id'] ?? null);
        $log->setData('operator_name', $session['username'] ?? null);
        $log->setData('action', $action);
        $log->setData('action_desc', $action_desc);
        $log->setData('result_code', $result);
        $log->setData('result_text', $msg);
        $log->setData('detail', $req_data ? json_encode($req_data, JSON_UNESCAPED_UNICODE) : null);
        $log->setData('ip', $input->ip_address());
        return $log->save();
    }
    
    /**
     * 通用删除redis
     */
    protected function baseDelRedis(string $db, string $key) : bool{
        $redis = PRedis::instance();
        $redis->select($db);
        $redis->del($key);
        if ($redis->exists($key)){
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * 通用删除有序集合，根据score
     */
    protected function baseZdelRedis(string $db, string $key, $score) : bool{
        $redis = PRedis::instance();
        $redis->select($db);
        if (is_array($score)){
            $redis->multi();
            foreach ($score as $v){
                $redis->zRemRangeByScore($key, intval($v), intval($v));
            }
            $redis->exec();
            //检验是否删除了
            $count = 0;
            foreach ($score as $v){
                $count += $redis->zCount($key, $v, $v);
            }
            if ($count){
                return false;
            }
        }else{
            $redis->zRemRangeByScore($key, intval($score), intval($score));
            $count = $redis->zCount($key, $score, $score);
            if ($count){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 通用添加有序集合
     */
    protected function baseZaddRedis(string $db, string $key, $score, $list) {
        $redis = PRedis::instance();
        $redis->select($db);
        if (is_array($score)){
            $redis->multi();
            foreach ($list as $v){
                $v_score = $v['score'];
                unset($v['score']);
                $redis->zRemRangeByScore($key, intval($v_score), intval($v_score));
                $redis->zAdd($key, intval($v_score), json_encode($v));
            }
            $redis->exec();
            //检验是否增加了
            $count = 0;
            foreach ($score as $v){
                $count += $redis->zCount($key, $v, $v);
            }
            if ($count != count($score)){
                return false;
            }
        }else{
            if($redis->zAdd($key, intval($score), json_encode($list)) == false){
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 通用添加有序集合
     */
    protected function baseZadd(string $db, string $key, $list) {
        $redis = PRedis::instance();
        $redis->select($db);
        $redis->multi();
        foreach ($list as $v){
            $v_score = $v['score'];
            unset($v['score']);
            $redis->zAdd($key, intval($v_score), json_encode($v));
        }
        $redis->exec();
    }
    
}
