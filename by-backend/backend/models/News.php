<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%news}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $type
 * @property string $publish_time
 * @property string $status
 * @property string $publish_admin
 * @property string $created_at
 * @property string $updated_at
 */
class News extends \common\components\CommonAR
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%news}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['publish_time', 'created_at', 'updated_at', 'intended_time'], 'safe'],
            [['title'], 'string', 'max' => 100],
            [['content'], 'string', 'max' => 400000],
            [['type', 'status', 'publish_admin'], 'string', 'max' => 15]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '文章标题',
            'content' => '文章内容',
            'type' => '文章类型',
            'publish_time' => '发布时间',
            'intended_time' => '预发布时间',
            'status' => '状态',
            'publish_admin' => '发布人',
            'created_at' => '创建时间',
            'updated_at' => '最近修改',
        ];
    }

    /**
     * @inheritdoc
     * @return ByQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ByQuery(get_called_class());
    }
}
