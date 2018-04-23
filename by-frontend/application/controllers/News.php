<?php


class NewsController extends BaseController {

    public function listAction() {
         $input = $this->input()->post();

        $news = new NewsModel();
        $list = $news->getList($input);

        echo json_encode($list);
    }

    public function detailAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->get(), array(
            'id' => 'number|min:1'
        ))) {
            return false;
        }

        $news = new NewsModel();
        $detail = $news->getDetail($input->get('id'));

        return $this->succ($detail);
    }
}
