<?php
include '../conn.php';

if (!isset($_GET['id'])) {
    die("Student ID not provided.");
}

$id = intval($_GET['id']);

// Fetch member/certificate data
$query = "SELECT * FROM onlinestudents WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    die("Student not found.");
}
$students = $result->fetch_assoc(); // ✅ Important fix

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Card</title>
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
            width: 350px;
            height: 220px;
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
            background-image: url('images/Certificate_idcard/Student_ID_final.jpg');
        }

        .id-card-back {
            background-image: url('images/student_idcard_back.jpg');
        }

        .info {
            position: absolute;
            top: 87px;
            left: 118px;
            font-size: 11px;
            line-height: 1.4;
        }

        .info p {
            margin: 2px 0;
        }

        .reg {
            position: absolute;
            top: 75px;
            left: 149px;
            font-size: 13px;
            font-weight: bold;
        }

        .photo {
            position: absolute;
            top: 95px;
            right: 11px;
            width: 61px;
            height: 75px;
            border: 2px solid #e66969ff;
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

        .address-title {
            font-weight: bold;
            background-color: #801336;
            color: white;
            padding: 2px 6px;
            display: inline-block;
            margin-bottom: 4px;
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
                background-image: url('images/Certificate_idcard/Student_ID_final.jpg') !important;
            }

            .id-card-back {
                background-image: url('images/student_idcard_back.jpg') !important;
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
        <div class="reg"><?= $students['student_code'] ?></div>
        <div class="info">
             <p><?= $students['name'] ?></p>
             <p><?= $students['dob'] ?></p>
             <p>ab+</p>
             <p><?= $students['course_name'] ?></p>
                <?= $students['mobile'] ?></p>
            <?= $students['session_start'] ?> to <?= $students['session_end'] ?></p>
        </div>
        <div class="photo">
            <img src="../uploads/students/photos/<?= $students['photo'] ?>" alt="Student Photo">
        </div>
    </div>
    
    <div class="id-card-back">
        <div class="info" style="margin-top: -59px; font-size: 13px;">
            <p><?= !empty($students['father_name']) ? htmlspecialchars($students['father_name']) : 'N/A' ?></p>
            <p><?= !empty($students['mother_name']) ? htmlspecialchars($students['mother_name']) :'N/A' ?></p>
            <p><?= !empty($students['address']) ? htmlspecialchars($students['address']) : 'N/A' ?> </p>
            <p><?= !empty($students['post_office']) ? htmlspecialchars($students['post_office']) : 'N/A' ?></p>
            <p><?= !empty($students['block']) ? htmlspecialchars($students['block']) : 'N/A' ?></p>
            <p><?= !empty($students['district']) ? htmlspecialchars($students['district']) : 'N/A' ?></p>
            <p><?= !empty($students['state']) ? htmlspecialchars($students['state']) : 'N/A' ?></p>
            <p><?= htmlspecialchars($students['pin_code']) ?></p>
        </div>
    </div>

    </div>
</body>

     <div class="print-btn">
        <button onclick="window.print()">Print ID Card</button>
    </div>
</html>

