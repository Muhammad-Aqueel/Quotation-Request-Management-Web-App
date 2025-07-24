<?php
session_start();

if (!isset($_SESSION['user_id'] ) || !isset($_SESSION['user_username'])) {
    header("Location: login.php");
} else {
    header("Location: dashboard.php");
}

?>