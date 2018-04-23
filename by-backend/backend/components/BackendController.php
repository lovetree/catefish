<?php

namespace backend\components;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BannerController implements the CRUD actions for Banner model.
 */
class BackendController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'upload' => [
                'class' => 'kucha\ueditor\UEditorAction',
                'config' => [
                    'imageUrlPrefix' => \Yii::getAlias('@static').'/', //图片访问路径前缀
                    'imagePathFormat' => 'upload/image/{yyyy}{mm}{dd}/{time}{rand:6}', //上传保存路径
                ],
            ],
            'webupload' => \yidashi\webuploader\WebuploaderAction::className(),
        ];
    }
}
