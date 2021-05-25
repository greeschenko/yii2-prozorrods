<?php

namespace greeschenko\prozorrods\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use greeschenko\prozorrods\helpers\DSDriver;
use greeschenko\prozorrods\models\DsUploadCandidates;
use greeschenko\file\models\Attachments;

class DocsController extends Controller
{
    public $dsapi;

    public function init()
    {
        parent::init();

        //$this->module = Yii::$app->getModule('ds');

        $this->dsapi = new DSDriver(
            $this->module->dsurl,
            $this->module->dsname,
            $this->module->dskey
        );
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['send','send-one'],
                        //'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    public function actionSend($id)
    {
        $res = [];
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->request->isAjax) {
            foreach (explode(',', $id) as $one) {
                $candidate = DsUploadCandidates::findOne($one);
                if ($candidate != null) {
                    $tmplist = [];
                    $tmplist = $candidate->getList();
                    //if (!$candidate->send()) {
                        //$res['error'] = 'Помилка відправки документа';
                    //}
                    $res = array_merge($res, $tmplist);
                } else {
                    $res['notfound'] = '1';
                }
            }
        }

        return $res;
    }

    public function actionSendOne($id, $dsuc)
    {
        $res = [];
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        //if (Yii::$app->request->isAjax) {
        $candidate = DsUploadCandidates::findOne($dsuc);
        $one = Attachments::findOne($id);
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
        //die;

        if (isset($req->data) and isset($req->upload_url)) {
            $req = $this->dsapi->uploadDoc(
                $req->upload_url,
                $file
            );
            //echo '<pre>';
            //print_r($req);
            //echo '</pre>';
            //die;
            if (isset($req->data) and isset($req->data->url)) {
                $main = $candidate->main_class;
                if (strlen($candidate->main_proid) > 16) {
                    $main = $main::find()->where(['id' => $candidate->main_proid])->one();
                } else {
                    $main = $main::find()->where(['proid' => $candidate->main_proid])->one();
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
                        'index' => ($i == 'illustration') ? $one->index : 0,
                    ],
                ];

                if ($i != '' and $i != '_empty_') {
                    $data['data']['documentType'] = $i;
                }

                if ($one->description != '') {
                    $data['data']['description'] = $one->description;
                }

                if (!$main->pubDocs($one, $data)) {
                    //$this->delete();
                    throw new \yii\web\HttpException(501, 'Помилка публікації інформації про документ до DS');
                }
            } else {
                throw new \yii\web\HttpException(501, 'Помилка завантаження файлу до DS:'.json_encode($req));
            }
        } else {
            throw new \yii\web\HttpException(501, 'Помилка реестрації файлу в DS:'.json_encode($req));
        }

        //}

        return $res;
    }
}
