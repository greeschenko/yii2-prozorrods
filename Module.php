<?php

namespace greeschenko\prozorrods;

use greeschenko\prozorrods\models\DsUploadCandidates;

class Module extends \yii\base\Module
{
    const VER = '0.1-dev';

    public $istest = false;
    public $proddomen = [];

    public $dsurl;
    public $dsname;
    public $dskey;

    public $test_dsurl;
    public $test_dsname;
    public $test_dskey;

    public function init()
    {
        parent::init();

        if (!in_array($_SERVER['SERVER_NAME'], $this->proddomen)) {
            $this->dsurl = $this->test_dsurl;
            $this->dsname = $this->test_dsname;
            $this->dskey = $this->test_dskey;

            $this->istest = true;
        }

        $this->components = [
        ];
    }

    /**
     * synchronous sending candidate.
     */
    public function sendCandidate($id)
    {
        $candidate = DsUploadCandidates::findOne($id);
        if ($candidate != null) {
            if ($candidate->send()) {
                return true;
            } else {
                throw new \yii\web\HttpException(501, 'Помилка статичної відправки документу ID = '.$id);
            }
        }

        return false;
    }
}
