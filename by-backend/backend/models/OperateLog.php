<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%operate_log}}".
 *
 * @property integer $id
 * @property string $module
 * @property string $controller
 * @property string $action
 * @property string $params
 * @property string $operator
 * @property string $remote_ip
 * @property string $created_at
 * @property string $updated_at
 */
class OperateLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%operate_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['module', 'controller', 'action', 'operator'], 'string', 'max' => 20],
            [['params'], 'string', 'max' => 1000],
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
            'module' => '模块',
            'controller' => '控制器',
            'action' => '动作',
            'params' => '参数',
            'operator' => '操作人员',
            'remote_ip' => '操作ip',
            'created_at' => '操作时间',
            'updated_at' => 'Updated At',
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
