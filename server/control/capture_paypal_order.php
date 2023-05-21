<?php
include_once("../include/PaypPal-PHP-SDK/autoload.php");

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'Aa4L0Ghm8p41A0JQ6rGTUTJxBzwkxCo4KMDqTRXX9sU3k3EWY0O5_N7_nJQzxA_MFCME7arOJI4-4-4V',
        'EPkaFbEp-lMhXBeSsm5E7O71F71DfwfULgtJ7FSYm2c4VN76bI6n-luw-wKYkCR8CfhAwdswBrPkoHvl'
    )
);

$orderID = $_POST['orderID'];

$order = \PayPal\Api\Order::get($orderID, $apiContext);

$purchaseUnit = $order->purchase_units[0];

$paymentCapture = new \PayPal\Api\PaymentCapture();
$paymentCapture->setAmount($purchaseUnit->amount);

try {
    $capture = $purchaseUnit->capture($paymentCapture, $apiContext);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'COMPLETED']);
} catch (\PayPal\Exception\PayPalException $ex) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERROR', 'message' => $ex->getMessage()]);
}
