<?php
include_once("../include/connect.php");
session_start();
$user = $_SESSION["user"];

echo '
<div class="container my-5">
    <h3 class="text-center mb-3">' . (($user["isSeller"]) ? "SELLER" : "CUSTOMER") . ' PROFILE</h3>
    <form action="../server/control/process_request.php" method="POST">
        <input type="hidden" value="' . $user["user_id"] . '" name="user_id"/>
        <div class="mb-1">
            <img id="profile_image_' . $user["image_id"] . '" src="../sources/images/person_icon.png" alt="Profile Picture" class="d-block mx-auto mb-1" style="width: 150px; height: 150px; border-radius: 50%; border: 2px solid gold;">
        </div>
        <div class="form-floating mb-3">
            <input id="profile-username" type="text" name="username" class="form-control" placeholder="Edit Username" value="' . $user["username"] . '" required>
            <label for="profile-username">Edit Username</label>
        </div>
        <div class="form-floating mb-3">
            <textarea id="profile-address" style="height: 100px; border: 2px solid gold;" name="address" class="form-control" placeholder="Edit Address">' . $user["address"] . '</textarea>
            <label for="profile-address"> Edit Address</label>
        </div>
        <div class="form-floating mb-3">
            <input id="profile-email" type="email" name="email" class="form-control" placeholder="Edit Email" value="' . $user["paypal_email"] . '">
            <label for="profile-email">Edit Email</label>
        </div>
        <div class="d-grid gap-2">
            <input type="submit" class="btn btn-dark btn-lg" value="EDIT" />
        </div>
    </form>
    <style>

    </style>
    
<script>
    loadProfileImage(' . $user["image_id"] . ');
    $(document).ready(()=>{
    $("h5#nav-profile-name").empty;
    $("h5#nav-profile-name").text("' . $user["username"] . '".toUpperCase());
    });
</script>
</div>
';
