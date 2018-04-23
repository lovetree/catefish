<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "by_account".
 *
 * @property integer $id
 * @property string $account
 * @property string $nickname
 * @property string $mobile_phone
 * @property string $email
 * @property string $real_name
 * @property string $id_card
 * @property string $status
 * @property integer $diamond
 * @property string $created_at
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'by_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account'], 'required'],
            [['diamond'], 'integer'],
            [['created_at'], 'safe'],
            [['account', 'nickname', 'real_name'], 'string', 'max' => 30],
            [['mobile_phone'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 50],
            [['id_card'], 'string', 'max' => 18],
            [['status'], 'string', 'max' => 15]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account' => '帐号',
            'nickname' => '昵称',
            'mobile_phone' => '绑定手机',
            'email' => '绑定邮箱',
            'real_name' => '真实姓名',
            'id_card' => '身份证',
            'status' => '帐号状态',
            'diamond' => '钻石数量',
            'created_at' => '注册时间',
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
