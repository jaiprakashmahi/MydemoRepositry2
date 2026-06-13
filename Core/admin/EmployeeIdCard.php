<?php
include 'conn.php';

if (!isset($_GET['id'])) {
    die("Member ID not provided.");
}

$id = intval($_GET['id']);

// Join states and districts for both current and permanent address
$query = "SELECT m.*, 
                 s.name AS state_name, 
                 GROUP_CONCAT(DISTINCT d.name SEPARATOR ', ') AS district_name,
                 ps.name AS p_state_name,
                 pd.name AS p_district_name
          FROM members m
          LEFT JOIN states s ON m.state = s.id
          LEFT JOIN districts d ON FIND_IN_SET(d.id, m.district)
          LEFT JOIN states ps ON m.p_state = ps.id
          LEFT JOIN districts pd ON m.p_district = pd.id
          WHERE m.id = $id
          GROUP BY m.id";




$result = $conn->query($query);

if ($result->num_rows == 0) {
    die("Member not found.");
}

$member = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee ID Card</title>
        <style>
        /* body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            margin: 50px;
            flex-wrap: wrap;
        } */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 30px;
        }
        .card-wrapper {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .id-card, .id-card-back {
            width: 270px;
            height: 415px;
            border: 2px solid #333;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            padding: 20px;
            position: relative;
            box-sizing: border-box;
            color: #000;
        }

        .id-card {
            background-image: url('images/emp_idfront.jpg');
        }

        .id-card-back {
            background-image: url('images/emp_idback.jpg');
        }

        .info {
            position: absolute;
            top: 218px;
            left: 105px;
            font-size: 15px;
            line-height: 1.4;
        }

        .info p {
            margin: 2px 0;
        }

        .reg {
            position: absolute;
            top: 95px;
            left: 175px;
            font-size: 12px;
            font-weight: bold;
        }

        .photo {
            position: absolute;
            top: 115px;
            right: 75px;
            width: 85px;
            height: 102px;
            /* border: 2px solid #000; */
            border-radius: 5px;
            overflow: hidden;
            background: #ddd;
        }

        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .address-section {
            position: absolute;
            top: 60px;
            left: 20px;
            font-size: 14px;
            line-height: 1.4;
            width: 90%;
        }

        .address-section p {
            margin: 2px 0;
        }

       
        .print-btn {
            margin-top: 20px;
        }

        .print-btn button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .id-card {
                background-image: url('images/emp_idfront.jpg') !important;
            }

            .id-card-back {
                background-image: url('images/emp_idback.jpg') !important;
            }

            button {
                display: none;
            }
            .print-btn {
                display: none;
            }
        }

       
    </style>
</head>
<body>
    <div class="card-wrapper">
    <div class="id-card">
        <div class="reg"><?= $member['unicode'] ?></div>
        <div class="info">
             <p><?= $member['owner_name'] ?></p>
             <p><?= $member['member_type'] ?></p>
             <p><?= !empty($member['blood_group']) ? htmlspecialchars($member['blood_group']) : 'N/A' ?></p>
             <p><?= $member['phone'] ?></p>
            <p style="font-size:10px;"><?= htmlspecialchars($member['state_name']) ?>:- <?= htmlspecialchars($member['district_name']) ?></p>
        </div>
        <div class="photo">
            <img src="uploads/<?= $member['photo'] ?>" alt="Student Photo">
        </div>
    </div>
    
    <div class="id-card-back">
        <div class="info" style="margin-top: -139px; font-size: 15px; line-height: 2.2;">
            <p><?= !empty($member['p_address']) ? htmlspecialchars($member['p_address']) : 'N/A' ?></p>
            <p><?= !empty($member['post_office']) ? htmlspecialchars($member['post_office']) : 'N/A' ?></p>
            <p><?= !empty($member['block']) ? htmlspecialchars($member['block']) : 'N/A' ?></p>
            <p><?= !empty($member['p_district_name']) ? htmlspecialchars($member['p_district_name']) : 'N/A' ?></p>
            <p><?= !empty($member['p_state_name']) ? htmlspecialchars($member['p_state_name']) : 'N/A' ?></p>
            <p><?= !empty($member['pincode']) ? htmlspecialchars($member['pincode']) : 'N/A' ?></p>
        </div>
    </div>

    </div>
</body>

     <div class="print-btn">
        <button onclick="window.print()">Print ID Card</button>
    </div>
</html>

