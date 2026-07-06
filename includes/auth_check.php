<?php
/**
 * Authentication check helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkCustomerAuth() {
    if (!isset($_SESSION['customer_id'])) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header("Location: auth/login.php");
        exit;
    }
}

function checkAdminAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}
