<?php
include_once("../include/connect.php");
header('Content-Type: application/json');

$image_id = $_POST["image_id"];

$query = "SELECT image_src FROM tbl_image WHERE image_id=?";
$stmt = $con->prepare($query);
$stmt->execute([$image_id]);
$imageData = $stmt->fetchColumn();

$imageBase64 = base64_encode($imageData);

echo json_encode(array('id' => $image_id, 'imageBase64' => $imageBase64));
