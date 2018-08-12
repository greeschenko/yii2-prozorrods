<?php

namespace greeschenko\prozorrods\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use greeschenko\prozorrods\helpers\DSDriver;
use greeschenko\file\models\Attachments;

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
    public $module;
    public $dsapi;

    public $typesbyclass = [
        'greeschenko\prozorrotender\models\TenderBid' => '/bids/',
    ];

    public function init()
    {
        parent::init();

        //$this->module = Yii::$app->getModule('ds');
        $this->module = Yii::$app->controller->module;

        if (isset($this->module->dsurl)) {
            $this->dsapi = new DSDriver(
                $this->module->dsurl,
                $this->module->dsname,
                $this->module->dskey
            );
        }
    }

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

    public function beforeValidate()
    {
        $this->main_proid = (string) $this->main_proid;

        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['created_at', 'updated_at'], 'required'],
            [['child_proid', 'created_at', 'updated_at'], 'integer'],
            [['main_proid', 'main_class', 'child_class'], 'string', 'max' => 255],
            [['groupstoupload'], 'string'],
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

    /**
     * register and upload doc and update doc attributes.
     */
    public function send()
    {
        foreach (json_decode($this->groupstoupload) as $i => $group) {
            $data = Attachments::find()
                ->where(['group' => $group])
                ->all();

            foreach ($data as $one) {
                $req = [];
                $tempreq = [];
                $filemodel = $one->file->getData();

                if (isset($filemodel['url'])) {
                    $file = realpath('.'.$filemodel['url']);
                } else {
                    $file = realpath('.'.$filemodel['big']);
                }

                $data = [
                    'data' => [
                        'hash' => $one->hash,
                    ],
                ];

                $req = $this->dsapi->registerDoc($data);

                //echo '<pre>';
                //print_r($req);
                //echo '</pre>';

                if (isset($req->data) and isset($req->upload_url)) {
                    $req = $this->dsapi->uploadDoc(
                        $req->upload_url,
                        $file
                    );
                    if (isset($req->data) and isset($req->data->url)) {
                        $main = $this->main_class;
                        $main = $main::findOne($this->main_proid);
                        if ($main == null) {
                            $main = $this->main_class;
                            $main = $main::find()->where(['id' => $this->main_proid])->one();
                        }

                        $data = [
                                'data' => [
                                    'url' => $req->data->url,
                                    'title' => ($one->title == '')
                                        ? $filemodel['name'].$filemodel['ext']
                                        : $one->title,
                                    'description' => '',
                                    'format' => mime_content_type($file),
                                    'hash' => $one->hash,
                                    'index' => $one->index,
                                ],
                            ];

                        if ($i != '') {
                            $data['data']['documentType'] = $i;
                        }

                        if ($one->description != '') {
                            $data['data']['description'] = $one->description;
                        }

                        if ($main->pubDocs($one, $data)) {
                            $this->delete();
                        }
                    } else {
                        throw new \yii\web\HttpException(501, 'Помилка завантаження файлу до DS:'.json_encode($req));
                    }
                } else {
                    throw new \yii\web\HttpException(501, 'Помилка реестарації файлу в DS:'.json_encode($req));
                }
            }
        }

        return true;
    }
}
