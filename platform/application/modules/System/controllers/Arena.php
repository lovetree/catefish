<?php

class ArenaController extends BaseController
{

    /**
     * 查询竞技场列表
     */
    public function listAction()
    {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        !empty($request['query']) && ($args['filters']['name'] = $request['query']);

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new System\ArenaModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        //根据时间区间确定竞技场当前的状态
        foreach ($list as $item) {
            isset($item['create_time']) && ($item['create_time'] = date('Y-m-d H:i:s', $item['create_time']));
            $new = array_change_keys($item, array(
                'id' => 'id'
            ), true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;
        //反馈数据
        return $this->succ($data, false);
    }

    /**
     * 创建/修改
     */
    public function saveAction()
    {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'match_start_time' => 'required',
            'match_end_time' => 'required',
            'name' => 'required',
            'award' => 'required',
            'effect' => 'required',
            'rematch_times' => 'required',
            'match_number'=>'required|number',
            'first_match_fee'=>'required|number',
            'bullet_number'=>'required|number',
            'game_mode'=>'required',
        ])
        ) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['effect']=$request['effect']==''?'0':$request['effect'];
        $model = new System\ArenaModel();

       $args['filters']['gamemode']=$request['game_mode'];
       $data = $model->lists(1, 10, $args);
        $list = $data['list'];
       if(count($list)>0)
       {
           return $this->failed('已存在相同的游戏模式，请删除后重新新增！');
       }
        if (empty($object_id)) {
            //新建
            $status = $model->create($request);
        } else {
            //修改
            $status = $model->edit($object_id, $request);
        }
        if (false === $status) {
            return $this->failed('保存失败');
        }
        return $this->succ();
    }

    /**
     * 设置竞技场的状态
     */
    public function showAction()
    {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id' => 'required|positive_number',
            'effect' => 'required|inside:0:1',
        ])
        ) {
            return false;
        }
        $model = new System\ArenaModel();
        if (!$model->setStatus($request['id'], $request['isshow'])) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /**
     * 删除竞技场
     */
    public function deleteAction()
    {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id' => 'required'
        ])
        ) {
            return false;
        }
        $model = new System\ArenaModel();
        if (!$model->delete($request['id'])) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

}
