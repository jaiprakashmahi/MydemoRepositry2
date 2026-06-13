<?php
include 'conn.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: center_list.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM center_details WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Center not found!";
    exit();
}

$row = $result->fetch_assoc();

// Password direct show
$actual_password = $row['password'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Center Details</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .details-box {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0,0,0,0.08);
        }

        .detail-row {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
        }

        .password-normal {
            background: #f8f9fa;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-weight: 600;
            color: #000;
            display: inline-block;
        }

        .center-image {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .btn-back {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="container mt-4">
    <div class="details-box">

        <h3>Center Full Details</h3>
        <hr>

        <?php if (!empty($row['photo'])): ?>
            <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" class="center-image">
        <?php endif; ?>

        <div class="detail-row">
            <span class="detail-label">Center Code:</span>
            <?= htmlspecialchars($row['center_code']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Center Name:</span>
            <?= htmlspecialchars($row['center_name']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Owner Name:</span>
            <?= htmlspecialchars($row['owner_name']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Username:</span>
            <?= htmlspecialchars($row['username']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Password:</span>
            <span class="password-normal">
                <?= htmlspecialchars($actual_password) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <?= htmlspecialchars($row['email']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Phone:</span>
            <?= htmlspecialchars($row['phone']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">State:</span>
            <?= htmlspecialchars($row['state']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">District:</span>
            <?= htmlspecialchars($row['district']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Block:</span>
            <?= htmlspecialchars($row['block']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Pincode:</span>
            <?= htmlspecialchars($row['pincode']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Registration Amount:</span>
            ₹<?= number_format($row['reg_amount'], 2) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Payment Mode:</span>
            <?= htmlspecialchars($row['payment_mode']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Payment Status:</span>
            <?= htmlspecialchars($row['payment_status']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Center Status:</span>
            <?= htmlspecialchars($row['center_status']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Created Date:</span>
            <?= date('d-m-Y', strtotime($row['date_of_create'])) ?>
        </div>

        <a href="CenterList.php" class="btn btn-primary btn-back">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>

    </div>
</div>

</body>
</html>
