<?php


$secret_key = "6LceVuslAAAAAJYbQKCtNH2bre0CULhUCWb70oP7";
$response_token = $_POST["g-recaptcha-response"];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    "secret" => $secret_key,
    "response" => $response_token
)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close($ch);

$response = json_decode($server_output);
if ($response->success) {
    $query = "SELECT COUNT(*) as count FROM tbl_user WHERE username = ?";
    $stmt = $con->prepare($query);
    $stmt->execute([$_POST["username"]]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['count'] > 0) {
        $message = array("message" => "Choose another Username", "type" => "info");
        echo json_encode($message);
    } else {
        if (isset($_POST["address"]) && isset($_POST["email"])) {
            $query = "INSERT INTO tbl_user(username, password, paypal_email, isSeller, address) VALUES(?, ?, ?, 1, ?)";
            $stmt = $con->prepare($query);
            $stmt->execute([$_POST["username"], md5($_POST["password"]), $_POST["email"], $_POST["address"]]);
        } else {
            $query = "INSERT INTO tbl_user(username, password) VALUES(?, ?)";
            $stmt = $con->prepare($query);
            $stmt->execute([$_POST["username"], md5($_POST["password"])]);
        }
        $message = array("message" => "Signup Success", "type" => "success");
        echo json_encode($message);
    }
} else {
    $message = array("message" => "Signup Failed", "type" => "error");
    echo json_encode($message);
}
