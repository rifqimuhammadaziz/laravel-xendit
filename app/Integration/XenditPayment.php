<?php

namespace App\Integration;

use Carbon\Carbon;
use Xendit\Configuration;

Class XenditPayment {
    protected $secretAPIKey;
    protected $eWallets;

    public function __construct()
    {
        $this->secretAPIKey = env("XENDIT_DEV_APIKEY");
        $this->eWallets = array('GOPAY', 'OVO', 'DANA', 'Link-Aja');
        Configuration::setXenditKey($this->secretAPIKey);
    }

}