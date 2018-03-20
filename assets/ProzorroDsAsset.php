<?php

namespace greeschenko\prozorrods\assets;

use yii\web\AssetBundle;

class ProzorroDsAsset extends AssetBundle
{
    public $sourcePath = '@greeschenko/prozorrods/web';
    public $css = [
        'css/prozorrods.min.css?v=0.0.0',
    ];
    public $js = [
        'js/prozorrods.min.js?v=0.0.0',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'greeschenko\prozorrods\assets\FontsAsset',
    ];
}
