<?php

class FeedbackController extends BaseController {

    /**
     * 查询用户反馈列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        !empty($request['query']) && ($args['filters']['wx_unionid'] = $request['query']);

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new System\FeedbackModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            isset($item['created_date']) && ($item['created_date'] = date('Y-m-d H:i:s', $item['created_date']));
            $new = array_change_keys($item, array(
                'id' => 'fb_id'
                    ), true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

}
