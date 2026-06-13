<?php
// center_logout.php
session_start();
include '../conn.php';

session_unset();
session_destroy();

header('Location: member_login.php');
exit;
?>