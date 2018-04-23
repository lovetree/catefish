<?php

class PlayerController extends BaseController {

    /**
     * 加载用户数据列表
     */
    public function listAction() {
        $input = $this->input();
        $args = $input->get();
        $page = $input->request('pagenum') ?? 1;
        $pagesize = $input->request('pagesize') ?? 10;

        $user = new Player\UserModel();
        $data = $user->lists($page, $pagesize, $args);

        $list = $data['list']->toArray();

        return $this->succ($list);
    }

    /**
     * 按时间统计用户数
     */
    public function userCntAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'agent_id'=>'required',
        ])) {
            return false;
        }

        $user = new Player\UserModel();
        $data = $user->getUserCnt($request['agent_id'], isset($request['start_date']) ? $request['start_date'] : '',
            isset($request['end_date']) ? $request['end_date'] : '');

        return $this->succ(['cnt'=>$data]);
    }

    /**
     * 计算用户活跃度
     */
    public function getUserActAction(){
        return $this->succ([[
            'new_num'=>100,
            'active_num'=>45,
            'stat_date'=>'2017-04-12'
            ],[
            'new_num'=>90,
            'active_num'=>23,
            'stat_date'=>'2017-04-13'
            ]
        ]);
    }
}
