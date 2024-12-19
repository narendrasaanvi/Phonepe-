<?php
require_once "utils/config.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $merchantId = filter_input(INPUT_POST, 'merchantId', FILTER_SANITIZE_STRING);
    $transactionId = filter_input(INPUT_POST, 'transactionId', FILTER_SANITIZE_STRING);

    // Check if all required session variables are set
    if (!isset($_SESSION['name'], $_SESSION['email'], $_SESSION['mobile'], $_SESSION['amount'])) {
        exit("Session variables not set.");
    }

    // Determine URL based on API status
    $url = (API_STATUS === "LIVE") ? LIVESTATUSCHECKURL . $merchantId . "/" . $transactionId : STATUSCHECKURL . $merchantId . "/" . $transactionId;
    $saltkey = (API_STATUS === "LIVE") ? SALTKEYLIVE : SALTKEYUAT;
    $saltindex = SALTINDEX;

    // Generate checksum for the request
    $checksum = hash("sha256", "/pg/v1/status/$merchantId/$transactionId$saltkey") . "###" . $saltindex;

    $headers = [
        "Content-Type: application/json",
        "Accept: application/json",
        "X-VERIFY: $checksum",
        "X-MERCHANT-ID: $merchantId"
    ];

    // Initialize cURL and set options
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    // Execute request and handle response
    $resp = curl_exec($curl);
    if ($resp === false || empty($resp)) {
        error_log("Curl error: " . curl_error($curl));
        exit("Payment status check failed. Please try again later.");
    }
    curl_close($curl);

    $responsePayment = json_decode($resp, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decoding error: " . json_last_error_msg());
        exit("Payment status check failed. Please try again later.");
    }

    header('Location: ' . BASE_URL . "success.php?tid=" . $responsePayment['data']['transactionId'] . "&amount=" . $responsePayment['data']['amount']);
    exit;
}
