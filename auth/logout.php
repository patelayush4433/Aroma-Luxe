<?php
/**
 * Customer Logout Page
 */
require_once __DIR__ . '/../config/config.php';

unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

setFlashMessage("info", "You have logged out of your account.");
header("Location: ../index.php");
exit;
