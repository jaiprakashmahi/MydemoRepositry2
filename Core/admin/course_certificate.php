<?php
include 'conn.php';

if (!isset($_GET['id'])) {
    die("Certificate ID not provided.");
}

$id = intval($_GET['id']);

// Fetch student/certificate data
$query = "SELECT * FROM certificates WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    die("Certificate not found.");
}

$student = $result->fetch_assoc();
$courseName = $student['course_name'];

// Fetch course details
$courseQuery = "SELECT * FROM courses WHERE course_name = ?";
$stmt = $conn->prepare($courseQuery);
$stmt->bind_param("s", $courseName);
$stmt->execute();
$courseResult = $stmt->get_result();

$course = $courseResult->fetch_assoc();  // ✅ Semicolon added
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Certificate of Course Completion</title>
  <link href="https://fonts.googleapis.com/css2?family=Georgia&family=Times+New+Roman&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Times New Roman', serif;
      margin: 0;
      padding: 0;
      background: #fff;
    }
    .certificate {
      width: 970px;
      height: 700px;
      padding: 40px 30px;
      margin: 20px auto;
      border: 10px solid #1a2b59;
      position: relative;
      box-sizing: border-box;
    }
    .certificate::before, .certificate::after {
      content: '';
      position: absolute;
      width: 0;
      height: 0;
      border-style: solid;
    }
    .certificate::before {
      top: 0;
      left: 0;
      border-width: 85px 225px 0 0;
      border-color: #035509ff transparent transparent transparent;
    }
    .certificate::after {
      bottom: 0;
      right: 0;
      border-width: 0 0 80px 240px;
      border-color: transparent transparent #035509ff transparent;
    }
    .header {
      text-align: center;
    }
    .header h1 {
      font-size: 34px;
      margin: 0;
      font-weight: bold;
    }
    .header p {
      margin: 5px 0;
      font-size: 18px;
    }
    .logo {
      text-align: left;
    }
    .logo img {
      width: 290px;
    }
    .title {
      text-align: center;
      color: #c62828;
      font-size: 26px;
      font-weight: bold;
      margin-top: 20px;
      text-transform: uppercase;
    }
    .content {
      margin: 30px 0;
      font-size: 18px;
      line-height: 2;
    }
    .content b {
      font-weight: bold;
    }
    .underline {
      border-bottom: 1px solid #000;
      display: inline-block;
      padding: 0 10px;
      min-width: 200px;
      text-align: center;
    }
    .footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 40px;
    }
    .footer .left,
    .footer .center,
    .footer .right {
      text-align: center;
      flex: 1;
    }
    .footer img {
      height: 60px;
    }
    .signature {
      margin-top: 20px;
      text-align: right;
    }
    .signature p {
      margin: 0;
    }
    .signature .name {
      font-weight: bold;
    }
    
  </style>
</head>
<body>
  <div class="certificate">
    <div class="header">
      <!-- <div class="logo">
        <img src="images/logo.png" alt="Logo">
      </div> -->
      <p style="text-align:right;">SL.No.: 10001</p>
      <h1 style="color:red; font-size:85px;">SHARNAY INSTITUTE</h1>
      <h3 style="color:#010195; font-size:30px; margin-top:0px; margin-bottom:12px;">Education & Technology Pvt. Ltd.  |  An ISO Certified 9001:2015</h3>
        <p>Registered Office : Nutan Market Sarairanjan Road, Satanpur, Samastipur Bihar-848132</p>
        <p>Email: sharnayinstitute@gmail.com, Web: sharnayinstitute.com</p>
    </div>
    <div class="title">Certificate of Course Completion</div>
    <div class="content">
      This is to certify that <span class="underline"><b><?= htmlspecialchars($student['name']) ?></b></span>
      Son/daughter/wife of <span class="underline"><b><?= htmlspecialchars($student['father_name']) ?></b></span> has completed
      the course <b><?= htmlspecialchars($student['course_name']) ?></b> 
      <small><?= htmlspecialchars($course['details']) ?></small>
      successfully on the Session from <span class="underline">January 2023</span> to <span class="underline">July 2023</span>
      and he/she was examined by the Examining Board and found proficient to receive this grade 
      <span class="underline"><b>A+</b></span>.
    </div>
    <div class="footer">
      <div class="left">
        <img src="images/msme.jpeg" alt="MSME Logo"><br>
        <small>Ministry of MSME, Govt. of India</small>
      </div>
      <div class="center">
        <img src="images/iso.png" alt="ISO Logo">
      </div>
      <div class="right">
        <img src="images/iaf.jpg" alt="IAF Logo">
      </div>
    
      <div class="signature">
      <p class="name">Sanjeet Kumar</p>
      <p>Director</p>
    </div>

    </div>

  </div>
  <div style="text-align:center; margin-bottom:20px;"><button onclick="window.print()">Print Certificate</button></div>
</body>
</html>
