<?php

define("BASE_URL", "https://sevafoundaton.in/seva-new/ppay/");
define("API_STATUS", "UAT"); //LIVE OR UAT
define("MERCHANTIDLIVE", "");
define("MERCHANTIDUAT", "PGTESTPAYUAT86");  //Sandbox testing
define("SALTKEYLIVE", " ");
define("SALTKEYUAT", "96434309-7796-489d-8924-ab56988a6076");
define("SALTINDEX", "1");
define("REDIRECTURL", "paymentstatus.php");
define("SUCCESSURL", "success.php");
define("FAILUREURL", "failure.php");
define("UATURLPAY", "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay");
define("LIVEURLPAY", "https://api.phonepe.com/apis/hermes/pg/v1/pay");
define("STATUSCHECKURL", "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/status/");
define("LIVESTATUSCHECKURL", "https://api.phonepe.com/apis/hermes/pg/v1/status/");
