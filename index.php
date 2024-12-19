<?php
require_once "utils/config.php";

function getTransactionID()
{

    return rand(1111111111, 99999999999);
}


session_start();
session_regenerate_id(true); // Prevent session fixation attacks

// Define user input values
$name = "John Doe";
$email = "narendrask786@gmail.com";
$mobile = "1234567890";
$amount = 1000;

// Validate inputs
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Invalid email address.");
}

if (!is_numeric($mobile) || strlen($mobile) < 10) {
    exit("Invalid phone number.");
}

if ($amount <= 0) {
    exit("Invalid amount.");
}

// Store variables in session
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['mobile'] = $mobile;
$_SESSION['amount'] = $amount;

// Define Merchant details
$merchantId = MERCHANTIDUAT;
$saltKey = SALTKEYUAT;
$saltIndex = SALTINDEX;

// Prepare payload for payment request
$payLoad = [
    'merchantId' => $merchantId,
    'merchantTransactionId' => "MT-" . getTransactionID(),
    'merchantUserId' => "M-" . uniqid(),
    'amount' => $amount * 100, // Amount in paise or cents
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
    $error_msg = curl_error($curl);
    curl_close($curl);
    error_log("cURL Error: " . $error_msg); // Log error
    exit("Payment initiation failed. Please try again later.");
}

curl_close($curl);
$res = json_decode($response, true);

if ($res === null) {
    exit("Error decoding response. Please try again later.");
}

if (isset($res['success'], $res['data']['instrumentResponse']['redirectInfo']['url']) && $res['success'] === true) {
    $payUrl = $res['data']['instrumentResponse']['redirectInfo']['url'];

    // Ensure the URL is valid before redirecting
    if (filter_var($payUrl, FILTER_VALIDATE_URL)) {
        header('Location: ' . $payUrl);
        exit;
    } else {
        exit("Invalid payment URL received.");
    }
} else {
    echo "Payment initiation failed. Please try again.";
    if (isset($res['message'])) {
        error_log("Payment error message: " . $res['message']);
        echo "<br>Error Message: " . htmlspecialchars($res['message']);
    }
}
