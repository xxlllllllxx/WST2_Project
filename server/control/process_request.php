<?php
include_once("../include/connect.php");

if (isset($_POST["user_id"])) {
    $query = "UPDATE tbl_user SET username=?, address=?, paypal_email=? WHERE user_id=?";
    $stmt = $con->prepare($query);
    $stmt->execute([$_POST["username"], $_POST["address"], $_POST["email"], $_POST["user_id"]]);
}


$query = "SELECT user_id, username, image_id, paypal_email, isSeller, address FROM tbl_user WHERE user_id=?";
$stmt = $con->prepare($query);
$stmt->execute([$_POST["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include_once("../include/session.php");
$_SESSION["user"] = $user;

header("Location: " . $site_url);
