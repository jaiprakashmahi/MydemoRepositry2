<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ID Card</title>
  <style>
    body {
  font-family: Arial, sans-serif;
  background: #f0f0f0;
  display: flex;
  justify-content: center;
  margin-top: 40px;
}

.id-card {
  width: 300px;
  border: 2px solid #ccc;
  border-radius: 12px;
  background: white;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  overflow: hidden;
  text-align: center;
}

.header {
  background: #003366;
  color: white;
  padding: 10px 8px;
  font-size: 12px;
  position: relative;
}

.header h2 {
  margin: 5px 0;
  font-size: 20px;
  color: red;
}

.header h2 span {
  color: #00ccff;
}

.header .sub {
  font-weight: bold;
  margin: 5px 0;
  color: #fff;
}

.header .cin,
.header .contact,
.header .address {
  font-size: 10px;
  margin: 3px 0;
}

.middle {
  background: linear-gradient(#ffffff, #e6e6e6);
  padding: 10px 0;
  border-top: 5px solid orange;
  border-bottom: 5px solid green;
}

.emp-id {
  font-size: 12px;
  font-weight: bold;
  margin-bottom: 8px;
}

.photo img {
  width: 90px;
  height: 90px;
  border-radius: 5px;
  border: 2px solid #ccc;
}

.name {
  margin-top: 10px;
  font-size: 16px;
  font-weight: bold;
  color: #b30000;
}

.designation {
  font-size: 14px;
  color: #333;
}

.footer {
  padding: 8px;
}

.signatures {
  display: flex;
  justify-content: space-between;
  padding: 0 10px;
}

.sign-box {
  width: 45%;
}

.sign-box img {
  height: 30px;
}

.sign-box p {
  font-size: 10px;
  margin-top: 3px;
  border-top: 1px solid #333;
  padding-top: 2px;
}

  </style>
</head>
<body>
  <div class="id-card">
    <div class="header">
      <h2>SHARNAY <span>INSTITUTE</span></h2>
      <p class="sub">OF EDUCATION TECHNOLOGY PVT. LTD.</p>
      <p class="cin">CIN: U82990BR2024PTC068682</p>
      <p class="contact">
        Contact Us: <span>7808224312</span>, <span>7004021899</span><br>
        E-mail ID: sharnayinstitute@gmail.com
      </p>
      <p class="address">
        At+Po.-Satnapur, P.S.-Ujiarpur, Dist.-Samastipur (Bihar)-848132
      </p>
    </div>

    <div class="middle">
      <div class="emp-id">Employee ID - SICT0001/2024</div>
      <div class="photo">
        <img src="photo.jpg" alt="Employee Photo" />
      </div>
      <div class="name">Mukesh Kumar</div>
      <div class="designation">Zonal Manager</div>
    </div>

    <div class="footer">
      <div class="signatures">
        <div class="sign-box">
          <img src="employee-sign.png" alt="Employee Signature" />
          <p>Employee Signature</p>
        </div>
        <div class="sign-box">
          <img src="director-sign.png" alt="Director Signature" />
          <p>Director</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
