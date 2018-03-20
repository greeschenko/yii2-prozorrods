<?php

namespace greeschenko\prozorrods\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class DocsController extends Controller
{
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

    public function actionSend()
    {
        $res = [];
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->request->isPost and Yii::$app->request->isAjax) {
            $res = [1];
        }

        return $res;
    }
}
