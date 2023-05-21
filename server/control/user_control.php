<?php
include_once("../include/connect.php");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $hashed_password = md5($password);
    $query = "INSERT INTO tbl_user (username, password) VALUES (?, ?)";
    $stmt = $con->prepare($query);
    $stmt->execute([$username, $hashed_password]);

    $response = array("status" => "success", "message" => "User created successfully");
    echo json_encode($response);
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login-password"])) {
    $username = $_POST["login-username"];
    $password = $_POST["login-password"];

    $query = "SELECT password FROM tbl_user WHERE username=?";
    $stmt = $con->prepare($query);
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row["password"] === md5($password)) {
        $response = array("status" => "success", "message" => "Login successful", "username" => $username);

        $query = "SELECT user_id, username, image_id, paypal_email, isSeller, address FROM tbl_user WHERE username=?";
        $stmt = $con->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        include_once("../include/session.php");
        $_SESSION["user"] = $user;

        echo json_encode($response);
    } else {
        $response = array("status" => "error", "message" => "Invalid username or password");
        echo json_encode($response);
    }
} else if (isset($_POST["logout"])) {
    session_start();
    session_destroy();
    echo "success";
} else {
    $response = array("status" => "error", "message" => "Invalid request method");
    echo json_encode($response);
}
