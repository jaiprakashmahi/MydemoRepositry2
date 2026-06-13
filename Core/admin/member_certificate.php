<?php
include 'conn.php';

if (!isset($_GET['id'])) {
    die("Certificate ID not provided.");
}

$id = intval($_GET['id']);

// Fetch member/certificate data
$query = "SELECT * FROM members WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    die("Certificate not found.");
}
$member = $result->fetch_assoc(); // ✅ Important fix

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Centered Certificate Content</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Playfair+Display&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Roboto', sans-serif;
      background: #f2f2f2;
    }

    .certificate-wrapper {
      width: 597px;
      height: 770px;
      margin: 40px auto;
      background-image: url('images/certificate_member.jpg'); /* Make sure this path is correct */
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      position: relative;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
      /* border: 10px solid #002147; */
    }

    .certificate-content {
      position: absolute;
      top: 60%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      width: 90%;
      color: #000;
    }

    .certificate-title {
      font-family: 'Playfair Display', serif;
      font-size: 46px;
      color: red;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .subtitle {
      font-size: 45px;
      font-weight: bold;
      margin-bottom: 0px;
      color: navy;
    }

    .recipient {
      font-size: 40px;
      color: darkred;
      margin-bottom: 10px;
      font-weight: bold;
    }
    .nametitle {
      font-size: 35px;
      color: #ed07079d;
      margin-bottom: 10px;
      font-weight: bold;
    }

    .description {
      font-size: 19px;
      margin-bottom: 30px;
      line-height: 1.6;
    }

    .footer-signature {
      font-size: 18px;
      font-weight: bold;
      color: red;
      text-align: right;
      margin-top: 50px;
      margin-right: 40px;
    }

    
    @media print {
      body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .certificate-wrapper {
        box-shadow: none;
        margin: 0;
      }

      button {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="certificate-wrapper">
    <div class="certificate-content">
      <!-- <div class="certificate-title">Certificate</div>
      <div class="subtitle">Authorization Certificate</div>
      <div class="recipient">Awarded to </div> -->
      <div class="nametitle"><?= htmlspecialchars($member['owner_name']) ?></div>
      <!-- <div style="font-size:30px;">Member Code:- <?= htmlspecialchars($member['unicode']) ?></div> -->
      <div class="description" >
         S/O & D/O <?= htmlspecialchars($member['father_name']) ?>, At-<?= htmlspecialchars($member['post_office']) ?>,<br>
        Dist.-<?= htmlspecialchars($member['district']) ?>, <?= htmlspecialchars($member['pincode']) ?> He is hereby nominated as a <span style="color:red;"><?= htmlspecialchars($member['member_type']) ?></span>
        for providing <span style="color:green;">Institute Related Work Only</span>.
      </div>
      <!-- <div class="footer-signature">Director</div> -->
    </div>
  </div>

</body>
    <div style="text-align:center; margin-bottom:20px;"><button onclick="window.print()">Print Certificate</button></div>

</html>
