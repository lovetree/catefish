<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "by_feedback".
 *
 * @property integer $id
 * @property string $content
 * @property string $account
 * @property string $remote_ip
 * @property string $created_at
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'by_feedback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['created_at'], 'safe'],
            [['content'], 'string', 'max' => 2000],
            [['account'], 'string', 'max' => 30],
            [['remote_ip'], 'string', 'max' => 15]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => '反馈内容',
            'account' => '反馈用户',
            'remote_ip' => '用户ip',
            'created_at' => '反馈时间',
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
