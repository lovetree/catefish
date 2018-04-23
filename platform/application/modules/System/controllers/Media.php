<?php

class MediaController extends BaseController {

    //获取所有图片
    public function listAction() {
        $page = $_POST['page'];
        $pagesize = !empty($_POST['pageSize']) && $_POST['pageSize'] > 0 ? $_POST['pageSize'] : 18;
        
        $model = new System\MediaModel();
        $imageList = $model->_list($page, $pagesize);
        $count = $model->_count();
        exit(json_encode(array('status' => '1', 'count' => $count, 'list' => $imageList)));
    }

    //上传图片
    public function saveAction() {
        //上传图片
        $upload = new UploadFile();
        if (!$upload->upload()) {
            //捕获上传异常
            exit(json_encode(array('status' => '-1', 'message' => $upload->getErrorMsg())));
        } else {
            $media = $upload->getUploadFileInfo();
            $bean = array(
                'type' => $media[0]['extension'],
                'size' => $media[0]['size'],
                'name' => $media[0]['savename'],
                'url' => '/files' . "/" . date('Y-m-d') . "/" . $media[0]['savename'],
                'create_time' => date('Y-m-d H:i:s')
            );
            
            $model = new System\MediaModel();
            //插入
            if ($model->_create($bean)) {
                if (!empty($_GET['editorid'])) {
                    echo json_encode(array('url' => 'files' . date('Y-m-d') . "/" . $media[0]['savename'], 'state' => 'SUCCESS'));
                } else {
                    exit(json_encode(array('status' => '1', 'message' => $bean['url'])));
                }
            } else {
                if (!empty($_GET['editorid'])) {
                    echo json_encode(array('state' => 'UNKNOWN'));
                } else {
                    exit(json_encode(array('status' => '-1', 'message' => '上传失败')));
                }
            }
        }
    }

}
