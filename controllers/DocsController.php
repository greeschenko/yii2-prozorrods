<?php

namespace greeschenko\prozorrods\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use greeschenko\prozorrods\helpers\DSDriver;
use greeschenko\prozorrods\models\DsUploadCandidates;

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
                        'actions' => ['send'],
                        'roles' => ['@'],
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
                    if (!$candidate->send()) {
                        $res['error'] = 'Помилка відправки документа';
                    }
                } else {
                    $res['notfound'] = '1';
                }
            }
        }

        return $res;
    }
}
