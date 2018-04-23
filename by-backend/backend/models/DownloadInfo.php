<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "download_info".
 *
 * @property integer $id
 * @property string $platform
 * @property string $version
 * @property integer $packagesize
 * @property string $lan
 * @property string $MD5
 * @property string $download1
 * @property string $download2
 * @property integer $uptime
 */
class DownloadInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'download_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform', 'version', 'lan', 'MD5', 'download1', 'download2'], 'required'],
            [['packagesize', 'uptime'], 'integer'],
            [['platform', 'version', 'lan', 'MD5'], 'string', 'max' => 32],
            [['download1', 'download2'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform' => 'Platform',
            'version' => 'Version',
            'packagesize' => 'Packagesize',
            'lan' => 'Lan',
            'MD5' => 'Md5',
            'download1' => 'Download1',
            'download2' => 'Download2',
            'uptime' => 'Uptime',
        ];
    }
}
