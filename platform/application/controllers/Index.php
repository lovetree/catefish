<?php


class IndexController extends BaseController {

    public function indexAction() {
        RedisFreshModel::refreshActivity();
        return false;
    }

}
