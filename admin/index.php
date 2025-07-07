<?php
session_start();

if (!isset($_SESSION['admin_id'] ) || !isset($_SESSION['admin_username'])) {
    header("Location: login.php");
} else {
    header("Location: dashboard.php");
}

?>