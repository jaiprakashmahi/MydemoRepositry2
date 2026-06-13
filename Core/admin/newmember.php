<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Application Form</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f8f8f8;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      background-color: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      width: 700px;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
    }

    a {
      color: #007bff;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      font-weight: bold;
    }

    input, select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .radio-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 20px;
    }

    .radio-group label {
      margin-bottom: 10px;
    }

    .photo-section {
      border: 1px solid #ccc;
      padding: 40px;
      text-align: center;
    }

    .select-group {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }

    .select-group div {
      flex: 1;
      margin-right: 20px;
    }

    .select-group div:last-child {
      margin-right: 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Application Form</h1>
    <a href="#">Apply post</a>

    <div class="radio-group">
      <label><input type="radio" name="post" checked /> State Nodal</label>
      <label><input type="radio" name="post" /> Zonal Manager</label>
      <label><input type="radio" name="post" /> District Coordinator</label>
      <label><input type="radio" name="post" /> Block Coordinator</label>
      <label><input type="radio" name="post" /> Branch Manager</label>
      <label><input type="radio" name="post" /> DEO-CUM-Instructor</label>
    </div>

    <div class="form-group">
      <label>Applicant Name:</label>
      <input type="text" />
    </div>
    <div class="form-group">
      <label>Father's Name:</label>
      <input type="text" />
    </div>
    <div class="form-group">
      <label>Mother's Name:</label>
      <input type="text" />
    </div>
    <div class="form-group">
      <label>Mobile Number:</label>
      <input type="text" />
    </div>
    <div class="form-group">
      <label>Email ID:</label>
      <input type="email" />
    </div>
    <div class="form-group">
      <label>UID No:</label>
      <input type="text" />
    </div>
    <div class="form-group">
      <label>PAN No:</label>
      <input type="text" />
    </div>
    <div class="form-group">
      <label>Date of Birth:</label>
      <input type="date" />
    </div>

    <h3>Working Area</h3>
    <div class="select-group">
      <div>
        <label>State Nodal</label>
        <select>
          <option>State List</option>
        </select>
      </div>
      <div>
        <label>Zonal Manager</label>
        <select>
          <option>District List</option>
        </select>
      </div>
    </div>

    <div class="photo-section">
      <p>Current Passport size photo</p>
    </div>
  </div>
</body>
</html>
