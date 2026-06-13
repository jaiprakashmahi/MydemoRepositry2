<?php

include 'conn.php';


$center_id = intval($_GET['center_id']);

// Get center details
$query = "SELECT cd.*, COALESCE(cw.balance, 0) as wallet_balance 
          FROM center_details cd 
          LEFT JOIN center_wallet cw ON cd.id = cw.center_id 
          WHERE cd.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();
$center = $result->fetch_assoc();

// Get wallet transactions
$trans_query = "SELECT * FROM wallet_transactions 
                WHERE center_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20";
$trans_stmt = $conn->prepare($trans_query);
$trans_stmt->bind_param("i", $center_id);
$trans_stmt->execute();
$transactions = $trans_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Center Wallet Details - <?php echo htmlspecialchars($center['center_name']); ?></title>
    <!-- Include same headers as admin_wallet.php -->
</head>
<body>
    <!-- Similar structure to show detailed wallet info for specific center -->
</body>
</html>