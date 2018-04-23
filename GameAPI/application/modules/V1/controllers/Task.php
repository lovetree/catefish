<?php
/**
 * task
 */

class TaskController extends \BaseController {
    
    /**
     * task任务列表
     */
    public function listAction() {
        $task = new TaskModel();
        $this->succ($task->task());
    }
    
    public function countAction() {
        $task = new TaskModel();
        $list = $task->task();
        $return = [];
        $return['count'] = 0;
        if ($list){
            foreach ($list as $v){
                if ($v['status']){
                    $return['count'] ++;
                }
            }
        }
        $this->succ($return);
    }
    
    /**
     * 每日任务奖励
     */
    public function dayAction() {
        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
                    'id' => 'required|positive_number'
                ))){
            return false;
        }
        
        $task = new TaskModel();
        $res = $task->day($request['id']);
        if ($res === TRUE){
            //获取金币数
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold', 'emerald', 'credit'));
            return $this->succ($user_info);
        }
        return $this->failed($res);
    }
    
}
