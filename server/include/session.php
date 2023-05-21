<?php

if (isset($_POST["checksession"])) {
    session_start();
    echo json_encode($_SESSION);
} else {
    $expire_time = 1;
    session_set_cookie_params($expire_time);
    session_start();
}
