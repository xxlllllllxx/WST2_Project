<?php
include_once("../include/connect.php");

if (isset($_POST["product_name"]) && isset($_POST["product_cost"])) {
    $image_ids = array();
    foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
        $image_data = file_get_contents($tmp_name);
        $query = "INSERT INTO tbl_image (image_src, type) VALUES (?, ?)";
        $stmt = $con->prepare($query);
        $stmt->execute([$image_data, "2"]);
        $image_ids[] = $con->lastInsertId();
    }

    $product_images = implode(", ", $image_ids);

    $query = "INSERT INTO tbl_product (product_name, product_cost, product_icon_id,  product_price, product_desc, product_images) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->execute([$_POST["product_name"], $_POST["product_cost"], $image_ids[0], $_POST["product_price"], $_POST["product_desc"], $product_images]);

    $query = "INSERT INTO tbl_shelf(seller_id, product_id) VALUES (?, ?)";
    $stmt = $con->prepare($query);
    session_start();
    $stmt->execute([$_SESSION["user"]["user_id"], $con->lastInsertId()]);

    header("Location: " . $site_url);
} else {
    echo '<div class="container text-light w-auto mx-3 py-3 px-2" style="border: 2px solid gold;">
    <h4>ADD NEW PRODUCT</h4>
    <form id="product-form" method="post" enctype="multipart/form-data" action="../server/control/seller_control.php" class="p-3">
    <h3 class="text-center mb-3">ADD NEW PRODUCT</h3>
    <div class="form-floating mb-3">
        <input type="text" name="product_name" class="form-control" id="floatingProductName" placeholder="Enter Product Name" maxlength="20" required>
        <label for="floatingProductName">Product Name</label>
    </div>
    <div class="form-floating mb-3">
        <input type="number" name="product_cost" class="form-control" id="floatingProductCost" placeholder="Enter Product Cost" required>
        <label for="floatingProductCost">Product Cost</label>
    </div>
    <div class="form-floating mb-3">
        <input type="number" name="product_price" class="form-control" id="floatingProductPrice" placeholder="Enter Product Price" required>
        <label for="floatingProductPrice">Product Price</label>
    </div>
    <div class="form-floating mb-3">
        <textarea name="product_desc" class="form-control" id="floatingProductDesc" placeholder="Enter Product Description" required></textarea>
        <label for="floatingProductDesc">Product Description</label>
    </div>
    <div class="form-group mb-3">
        <label for="product_images" class="form-label">Product Images</label>
        <input type="file" class="form-control" id="product_images" name="images[]" multiple required>
    </div>
    <div class="d-grid gap-2">
        <input id="" type="submit" value="SUBMIT" class="btn btn-dark btn-lg">
    </div>
</form>

</div>';
}
