<?php
session_start();
include '../../conn.php';

if (!isset($_SESSION['member_logged_in'])) {
    header('Location: ../member_login.php');
    exit();
}

// Redirect to actual dashboard
header('Location: ../Members/index.php');
exit();
?>