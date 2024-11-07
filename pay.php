<?php
require_once "utils/config.php";
require_once "utils/common.php";

session_start();
session_regenerate_id(true); // Prevent session fixation attacks

if (isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['amount'])) {
    // Sanitize inputs
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $mobile = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
    $medicines_amount = filter_var($_POST['medicines_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $shelter_amount = filter_var($_POST['shelter_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $doctor_amount = filter_var($_POST['doctor_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $total_amount = filter_var($_POST['total_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);



    // Store variables in session
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['mobile'] = $mobile;
    $_SESSION['amount'] = $amount;
    $_SESSION['city'] = $city;
    $_SESSION['medicines_amount'] = $medicines_amount;
    $_SESSION['shelter_amount'] = $shelter_amount;
    $_SESSION['doctor_amount'] = $doctor_amount;
    $_SESSION['total_amount'] = $total_amount;




    // Define Merchant details
    $merchantId = MERCHANTIDUAT;
    $saltKey = SALTKEYUAT;
    $saltIndex = SALTINDEX;

    // Prepare payload for payment request
    $payLoad = [
        'merchantId' => $merchantId,
        'merchantTransactionId' => "MT-" . getTransactionID(),
        'merchantUserId' => "M-" . uniqid(),
        'amount' => $amount * 100,
        'redirectUrl' => BASE_URL . REDIRECTURL,
        'redirectMode' => "POST",
        'callbackUrl' => BASE_URL . REDIRECTURL,
        'mobileNumber' => $mobile,
        'paymentInstrument' => [
            'type' => "PAY_PAGE",
        ]
    ];

    // Encode payload in Base64 and prepare checksum
    $payloadBase64 = base64_encode(json_encode($payLoad));
    $payLoadData = $payloadBase64 . "/pg/v1/pay" . $saltKey;
    $checksum = hash("sha256", $payLoadData) . '###' . $saltIndex;

    $url = (API_STATUS === "LIVE") ? LIVEURLPAY : UATURLPAY;

    // Set up cURL and execute
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode(['request' => $payloadBase64]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "X-VERIFY: $checksum",
            "Accept: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
        error_log("cURL Error: " . curl_error($curl)); // Log error
        curl_close($curl);
        exit("Payment initiation failed. Please try again later.");
    }

    curl_close($curl);
    $res = json_decode($response, true);

    if (isset($res['success'], $res['data']['instrumentResponse']['redirectInfo']['url']) && $res['success'] === true) {
        $payUrl = $res['data']['instrumentResponse']['redirectInfo']['url'];
        header('Location: ' . filter_var($payUrl, FILTER_VALIDATE_URL));
        exit;
    } else {
        echo "Payment initiation failed. Please try again.";
        if (isset($res['message'])) {
            error_log("Payment error message: " . $res['message']);
            echo "<br>Error Message: " . htmlspecialchars($res['message']);
        }
    }
} else {
    exit("Invalid input data.");
}
