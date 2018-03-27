<?php

namespace greeschenko\prozorrods\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%ds_upload_candidates}}".
 *
 * @property int $id
 * @property int $main_proid
 * @property string $main_class
 * @property int $child_proid
 * @property string $child_class
 * @property string $groupstoupload
 * @property int $created_at
 * @property int $updated_at
 */
class DsUploadCandidates extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ds_upload_candidates}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['created_at', 'updated_at'], 'required'],
            [['main_proid', 'child_proid', 'created_at', 'updated_at'], 'integer'],
            [['main_class', 'child_class', 'groupstoupload'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'main_proid' => 'Main Proid',
            'main_class' => 'Main Class',
            'child_proid' => 'Child Proid',
            'child_class' => 'Child Class',
            'groupstoupload' => 'Groupstoupload',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
