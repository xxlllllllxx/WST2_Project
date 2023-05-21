<?php
require_once("config.php");

$dsn = "mysql:host={$host};dbname={$db_name}";
try {
    $con = new PDO($dsn, $db_user, $db_pass);
    if ($con) {
        //echo "SUCCESSFULLY CONNECTED TO DATABASE";
    }
} catch (PDOException $e) {
    echo $e->getMessage();
}
