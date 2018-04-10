<?php

namespace greeschenko\prozorrods\helpers;

use greeschenko\prozorrosale\proapi\Handler;

class DSDriver extends Handler
{
    /**
     * @param mixed $apiurl
     * @param mixed $key
     */
    public function __construct($apiurl, $name, $key)
    {
        $this->apiurl = $apiurl;
        $this->name = $name;
        $this->key = $key;
    }

    public function registerDoc($data, $debag = false)
    {
        $this->getCookie();

        return $this->sendPOST($this->apiurl.'/register', $data, $debag);
    }

    public function uploadDoc($upload_url, $file)
    {
        $this->getCookie();

        return $this->sendFILE($upload_url, $file);
    }
}
