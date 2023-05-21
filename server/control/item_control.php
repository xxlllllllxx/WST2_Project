<?php
include_once("../include/connect.php");
header('Content-Type: application/json');
if (isset($_POST["product_id_update"])) {
    $query = "UPDATE tbl_product SET product_name=?, product_price=?, product_desc=? WHERE product_id=?";
    $stmt = $con->prepare($query);
    $stmt->execute([$_POST["product_name"], $_POST["product_price"], $_POST["product_desc"], $_POST["product_id_update"]]);
    echo "success";
} else if (isset($_POST["product_id_delete"])) {
    $query = "DELETE FROM tbl_product WHERE product_id=?";
    $stmt = $con->prepare($query);
    $stmt->execute([$_POST["product_id_delete"]]);

    $query = "DELETE FROM tbl_shelf WHERE product_id=?";
    $stmt = $con->prepare($query);
    $stmt->execute([$_POST["product_id_delete"]]);
    header("Location: " . $site_url);
} else if (isset($_POST["id"])) {
    session_start();
    if (isset($_SESSION["user"])) {
        if ($_SESSION["user"]["isSeller"]) {
            $query = "INSERT INTO tbl_shelf(seller_id, product_id, stock_count) VALUES (?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->execute([$_SESSION["user"]["user_id"], $_POST["id"], $_POST["quantity"]]);
            $response = array("status" => "success", "message" => "Product Restocked!");
            echo json_encode($response);
        } else if (!$_SESSION["user"]["isSeller"]) {
            if (isset($_SESSION["shopping_cart"])) {
                $query = "INSERT INTO tbl_transaction(seller_id, product_id, buyer_id, product_quantity, shopping_cart_id) 
              VALUES (?, ?, ?, ?, ?)";
                $stmt = $con->prepare($query);
                $stmt->execute([$_POST["seller_id"], $_POST["id"], $_SESSION["user"]["user_id"], $_POST["quantity"], $_SESSION["shopping_cart"]]);
            } else {
                $query = "SELECT MAX(shopping_cart_id)+1 as next_id FROM tbl_transaction";
                $stmt = $con->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch();
                $next_id = $result["next_id"] ?? 1;

                $query = "INSERT INTO tbl_transaction(seller_id, product_id, buyer_id, product_quantity, shopping_cart_id) 
              VALUES (?, ?, ?, ?, ?)";
                $stmt = $con->prepare($query);
                $stmt->execute([$_POST["seller_id"], $_POST["id"], $_SESSION["user"]["user_id"], $_POST["quantity"], $next_id]);

                $_SESSION["shopping_cart"] = $next_id;
            }
            $response = array("status" => "success", "message" => "Added to Cart Successfully");
            echo json_encode($response);
        }
    } else {
        if (isset($_SESSION["shopping_cart"])) {
            $query = "INSERT INTO tbl_transaction(seller_id, product_id, buyer_id, product_quantity, shopping_cart_id) 
              VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->execute([$_POST["seller_id"], $_POST["id"], 0, $_POST["quantity"], $_SESSION["shopping_cart"]]);
        } else {
            $query = "SELECT MAX(shopping_cart_id)+1 as next_id FROM tbl_transaction";
            $stmt = $con->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $next_id = $result["next_id"] ?? 1;

            $query = "INSERT INTO tbl_transaction(seller_id, product_id, buyer_id, product_quantity, shopping_cart_id) 
              VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->execute([$_POST["seller_id"], $_POST["id"], 0, $_POST["quantity"], $next_id]);

            $_SESSION["shopping_cart"] = $next_id;
        }
        $response = array("status" => "success", "message" => "Added to Cart Successfully");
        echo json_encode($response);
    }
} else if (isset($_POST["seller_id"])) {
    $query = "SELECT 
            p.product_id, 
            p.product_name, 
            p.product_icon_id, 
            p.product_images, 
            p.product_cost, 
            p.product_price, 
            s.seller_id, 
            SUM(s.stock_count) - COALESCE((SELECT SUM(product_quantity) FROM tbl_transaction t WHERE t.product_id = p.product_id AND t.paid = 1), 0) AS total_stock 
        FROM tbl_product p
        JOIN tbl_shelf s ON p.product_id = s.product_id
        WHERE s.seller_id=?
        GROUP BY p.product_id, p.product_name";
    $stmt = $con->prepare($query);
    $stmt->execute([$_POST["seller_id"]]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} else {
    $query = "SELECT 
            p.product_id, 
            p.product_name, 
            p.product_icon_id, 
            p.product_images, 
            p.product_cost, 
            p.product_price, 
            u.username, 
            SUM(s.stock_count) - COALESCE((SELECT SUM(product_quantity) FROM tbl_transaction t WHERE t.product_id = p.product_id AND t.paid = 1), 0) AS total_stock  
        FROM tbl_product p 
        JOIN tbl_shelf s ON p.product_id = s.product_id 
        JOIN tbl_user u ON s.seller_id = u.user_id 
        GROUP BY p.product_id, p.product_name
        HAVING total_stock > 0";
    $stmt = $con->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
}
