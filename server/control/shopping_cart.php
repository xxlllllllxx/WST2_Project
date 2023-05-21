<?php
include_once("../include/connect.php");

if (isset($_POST["data"])) {
    $data = $_POST["data"];
    echo $data;
}

session_start();
if (isset($_POST["transaction_ids"]) && isset($_SESSION["shopping_cart"])) {
    $ids = $_POST["transaction_ids"];
    $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
    $query = "UPDATE tbl_transaction SET paid = '1' WHERE transaction_id IN ($placeholders)";
    $stmt = $con->prepare($query);
    $stmt->execute($ids);
    $response = array("status" => "success", "message" => "Item Bought Successfully");
    echo json_encode($response);
} else {

    if (isset($_SESSION["shopping_cart"]) && !isset($_POST["data"])) {
        $query = 'SELECT 
        t.transaction_id,
        t.date,
        s.username,
        p.product_name, 
        p.product_price,
        t.product_quantity, 
        t.shopping_cart_id
    FROM tbl_transaction t
    JOIN tbl_user s ON t.seller_id = s.user_id
    JOIN tbl_product p ON t.product_id = p.product_id
    WHERE t.shopping_cart_id=? AND t.paid = 0';

        $stmt = $con->prepare($query);
        $stmt->execute([$_SESSION["shopping_cart"]]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($result);
    }
}
