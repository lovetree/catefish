<?php


class CarouselController extends BaseController {

    public function listAction() {
        $carousel = new CarouselModel();
        $list = $carousel->getList();

        return $this->succ($list);
    }

}
