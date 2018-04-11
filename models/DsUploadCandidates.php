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

        $this->module = Yii::$app->getModule('ds');

        $this->dsapi = new DSDriver(
            $this->module->dsurl,
            $this->module->dsname,
            $this->module->dskey
        );
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['created_at', 'updated_at'], 'required'],
            [['main_proid', 'child_proid', 'created_at', 'updated_at'], 'integer'],
            [['main_class', 'child_class'], 'string', 'max' => 255],
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

                if (isset($req->data) and isset($req->upload_url)) {
                    $req = $this->dsapi->uploadDoc(
                        $req->upload_url,
                        $file
                    );
                    if (isset($req->data) and isset($req->data->url)) {
                        $main = $this->main_class;
                        $main = $main::findOne($this->main_proid);
                        if ($this->child_class != '') {
                            $child = $this->child_class;
                            $child = $child::findOne($this->child_proid);
                        }
                        if ($filemodel['type'] == 3) {
                            //$data = [
                                //'data' => [
                                    //'url' => trim($filemodel['url']),
                                    //'title' => ($one->title == '')
                                        //? $filemodel['name'].$filemodel['ext']
                                        //: $one->title,
                                    //'description' => $one->description,
                                    //'documentType' => $i,
                                    //'index' => $one->index,
                                //],
                            //];

                            //if ($one->bind != '') {
                                //$req = $this->api->updateLink(
                                    //$this->id,
                                    //$one->bind,
                                    //$data,
                                    //$this->ownerElement->el_token
                                //);
                            //} else {
                                //$req = $this->api->addLink(
                                    //$this->id,
                                    //$data,
                                    //$this->ownerElement->el_token
                                //);
                            //}

                            //if (!isset($req->data) and $req->status_code != 200) {
                                //echo '<h1>Помилка збереження посилання '.$filemodel['url'].'</h1>';
                                //echo '<p>Перевірте коректність формату посилання, або передайте код нижче адміністратору</p>';
                                //echo '<hr>';
                                //echo '<pre>';
                                //print_r($req);
                                //die;
                            //} else {
                                //$tempreq = $req;
                            //}
                        } else {
                            $data = [
                                    'data' => [
                                        'url' => $req->data->url,
                                        'title' => ($one->title == '')
                                            ? $filemodel['name'].$filemodel['ext']
                                            : $one->title,
                                        'description' => 'ddddjk',
                                        'format' => mime_content_type($file),
                                        'hash' => $one->hash,
                                        //'index' => $one->index,
                                    ],
                                ];

                            if ($i != '') {
                                $data['data']['documentType'] = $i;
                            }

                            if ($one->description != '') {
                                $data['data']['description'] = $one->description;
                            }

                            if ($one->bind != '') {
                                $req = $main->api->getDocData(
                                        $main->id,
                                        $this->typesbyclass[$this->child_class],
                                        $child->id,
                                        $one->bind
                                    );
                                if (!isset($req->data)) {
                                    throw new \yii\web\HttpException(501, 'Помилка завантаження информації про файл:'.json_encode($req));
                                } elseif ($req->data->hash != $one->hash) {
                                    $req = $main->api->updateDocData(
                                            $main->id,
                                            $this->typesbyclass[$this->child_class],
                                            $child->id,
                                            $one->bind,
                                            $data,
                                            $child->token
                                        );
                                }
                            } else {
                                $req = $main->api->sendDocData(
                                        $main->id,
                                        $this->typesbyclass[$this->child_class],
                                        $child->id,
                                        $data,
                                        $child->token
                                    );
                            }

                            if (!isset($req->data)) {
                                throw new \yii\web\HttpException(501, 'Помилка відправки даних файлу до цбд:'.json_encode($req));
                            } else {
                                $tempreq = $req;
                            }

                            if (isset($tempreq->data)) {
                                $one->bind = $tempreq->data->id;
                            }
                            if (!$one->save()) {
                                print_r($one->errors);

                                return false;
                            }

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
