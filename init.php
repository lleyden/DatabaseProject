<?php
session_start();
require_once "db.php";

function check_login() {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }
}

function check_role($role) {
    if ($_SESSION['role'] != $role) {
        die("Unauthorized access.");
    }
}
?>

