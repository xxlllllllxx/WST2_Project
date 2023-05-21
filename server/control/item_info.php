<?php
include_once("../include/connect.php");

$query = "SELECT 
    p.product_id, 
    p.product_name, 
    p.product_icon_id, 
    p.product_cost, 
    p.product_price, 
    u.username,
    p.product_images,
    p.product_desc,
    u.user_id,
    s.seller_id
FROM tbl_product p 
JOIN tbl_shelf s ON p.product_id = s.product_id 
JOIN tbl_user u ON s.seller_id = u.user_id
WHERE p.product_id=?
GROUP BY p.product_id";
$stmt = $con->prepare($query);
$stmt->execute([$_POST["id"]]);
$result = $stmt->fetch();

$product_name = $result["product_name"];
$product_price = $result["product_price"];
$seller_name = $result["username"];
$product_id = $result["product_id"];
$seller_id = $result["seller_id"];
$product_desc = $result["product_desc"];

$product_images = [$result["product_icon_id"]] +  explode(", ", $result["product_images"]);
$src_array = [];

for ($i = 0; $i < count($product_images); $i++) {
    $query = "SELECT * FROM tbl_image WHERE image_id=?";
    $stmt = $con->prepare($query);
    $stmt->execute([$product_images[$i]]);
    $src_array[$i] = $stmt->fetch()['image_src'];
}

$carousel_indicators = '';
$carousel_items = '';

foreach ($product_images as $index => $product_image) {
    $active_class = ($index === 0) ? 'active' : '';
    $carousel_indicators .= '<button type="button" data-bs-target="#demo" data-bs-slide-to="' . $index . '" class="' . $active_class . '"></button>';

    $carousel_items .= '<div class="carousel-item ' . $active_class . '">';
    $image64 = base64_encode($src_array[$index]);
    $carousel_items .= '<img src="data:image/png;base64, ' . $image64 . '" alt="' . $product_name . '" class="d-block w-100">';
    $carousel_items .= '</div>';
}


$transactionlist = "";

session_start();
if (isset($_SESSION["user"])) {
    if ($_SESSION["user"]["isSeller"]) {
        $transactionlist = '<p class="text-light mt-5">TRANSACTION HISTORY</p>';
        $query = "SELECT 
            t.date,
            t.product_quantity,
            t.payment,
            u.username
        FROM tbl_transaction t
        JOIN tbl_user u ON u.user_id = t.buyer_id
        WHERE t.product_id=? AND t.paid = 1";
        $stmt = $con->prepare($query);
        $stmt->execute([$_POST["id"]]);
        $transactions = $stmt->fetchAll();
        $transactionlist .= '<div class="mb-5 table-responsive"><table class="table table-striped table-hover table-bordered bg-dark text-light" style="border-color: gold;">
        <thead>
            <tr>
                <th class="text-center">Buyer Name</th>
                <th class="text-center">Date</th>
                <th class="text-center">Quantity</th>
                <th class="text-center">Payment</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($transactions as $transaction) {
            $transactionlist .= '<tr class="text-light">
            <td>' . $transaction["username"] . '</td>
            <td>' . $transaction["date"] . '</td>
            <td class="text-center">' . $transaction["product_quantity"] . '</td>
            <td>P' . number_format($transaction["payment"], 2) . '</td>
        </tr>';
        }

        $transactionlist .= '</tbody></table></div>
        
        <form id="delete-form" class="mb-5" method="POST" action="../server/control/item_control.php">
            <input type="hidden" name="product_id_delete" value="' . $product_id . '"/>
            <input type="submit" class="btn btn-dark" value="REMOVE PRODUCT LISTING "/>
        </form>
        ';
    }
}


$product_full_details = '';
if (isset($_SESSION["user"]) && $_SESSION["user"]["isSeller"]) {
    $product_full_details = ' <form id="update-form" class="mb-5">
    <input type="hidden" name="product_id_update" value="' . $product_id . '"/>
    <div class="form-floating mb-3">
        <input type="text" name="product_name" id="product_name" value="' . $product_name . '" class="form-control" placeholder="Product Name">
        <label for="product_name">Product Name</label>
    </div>

    <div class="form-floating mb-3">
        <input type="text" name="product_price" id="product_price" value="' . $product_price . '" class="form-control" placeholder="Product Price">
        <label for="product_price">Product Price</label>
    </div>

    <div class="form-floating mb-3">
        <textarea style="height: 100px; border: 2px solid gold;" name="product_desc" id="description" class="form-control" placeholder="Product Description">' . $product_desc . '</textarea>
        <label for="description">Product Description</label>
    </div>
    <input type="submit" class="btn btn-dark" value="UPDATE PRODUCT DETAILS"/>
    
    </form> 
    <script>
    function showNotification(message, type) {
            var toastContainer = $("#toast-container");

            var toast = $("<div>").addClass("toast").attr("role", "alert").attr("aria-live", "assertive").attr("aria-atomic", "true").css("border", "2px solid white").css("border-radius", "5px");

            switch (type) {
                case "success":
                    toast.addClass("bg-success");
                    break;
                case "error":
                    toast.addClass("bg-danger");
                    break;
                case "warning":
                    toast.addClass("bg-warning");
                    break;
                default:
                    toast.addClass("bg-info");
            }

            var toastHeader = $("<div>").addClass("toast-header").css("background-color", "gold").text("Notification");
            var closeButton = $("<button>").addClass("btn-close  ms-auto me-1").attr("type", "button").attr("data-bs-dismiss", "toast").attr("aria-label", "Close");
            toastHeader.append(closeButton);
            var toastBody = $("<div>").addClass("toast-body").text(message).css("font-weight", "500").css("font-size", "14px");
            toast.append(toastHeader).append(toastBody);
            toastContainer.append(toast);
            toast.toast("show");
    }
     $(document).ready(()=>{
        $("form#update-form").submit(function(event) {
            event.preventDefault();
            
            const productId = $("input[name=product_id_update]").val();
            const productName = $("#product_name").val();
            const productPrice = $("#product_price").val();
            const productDesc = $("#description").val();
            $.ajax({
                type: "POST",
                url: "../server/control/item_control.php",
                data: {
                    "product_id_update": productId,
                    "product_name": productName,
                    "product_price": productPrice,
                    "product_desc": productDesc
                },
                datatype: "json",
                success: function(data) {
                    showNotification("Product Information Updated", "info");
                }
            });
        });
    });
    </script>
';
} else {
    $product_full_details = '<h1 class="text-light">' . strtoupper($product_name) . '</h1>
        <h3 class="text-light">' . ("P" . number_format($product_price, 2)) . '</h3>
        <h4 class="text-light">' . $seller_name . '</h4>
        <div class="form-floating mb-3">
            <textarea style="height: 100px; border: 2px solid gold;" name="description" class="form-control" placeholder="Product Desctiption" disabled>' . $product_desc . '</textarea>
            <label for="profile-address">Product Description</label>
        </div>';
}

echo '

<div class="m-2 bg-dark py-2">
    <div id="demo" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            ' . $carousel_indicators . '
        </div>
        <div class="carousel-inner">
            ' . $carousel_items . '
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    <div class="p-2 mt-2" style="border: 1px solid gold">
        ' . $product_full_details . '
        <form id="btn-product-info">
            <div class="form-group mb-3">
                <label for="quantity">' . ((!isset($_SESSION["user"])) ? "QUANTITY" : ((!$_SESSION["user"]["isSeller"]) ? "QUANTITY" : "RESTOCK PRODUCT")) . '</label>
                <input type="hidden" name="id" value="' . $product_id . '"/>
                <input type="hidden" name="seller_id" value="' . $seller_id . '"/>
                
                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity" required>
            </div>
            <input type="submit" class="btn btn-dark" value="' . ((!isset($_SESSION["user"])) ? "ADD TO CART" : ((!$_SESSION["user"]["isSeller"]) ? "ADD TO CART" : "RESTOCK")) . '"/>
        </form> 
        <script>
            $(document).ready(function() {
                $("form#btn-product-info").submit(function(event) {
                    event.preventDefault();
                    var formData = $(this).serialize();
                    $.ajax({
                        type: "POST",
                        url: "../server/control/item_control.php",
                        data: formData,
                        datatype: "json",
                        success: function(response) {
                            showNotification(response.message, response.status);
                            $("input#quantity").val("");

                        }
                    });
                });
            });
        </script>
        ' . $transactionlist . '
    </div>
    
</div>';
