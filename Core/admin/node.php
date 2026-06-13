<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 900px;
            background: white;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #333;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .form-group label {
            font-weight: bold;
            width: 30%;
        }

        .form-group input, .form-group select {
            width: 65%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }

        .photo-box {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            width: 200px;
            height: 200px;
            position: absolute;
            right: 30px;
            top: 80px;
        }

        .photo-box label {
            font-size: 14px;
            cursor: pointer;
            display: block;
            margin-top: 10px;
        }

        .photo-box input {
            display: none;
        }

        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .footer div {
            width: 30%;
            text-align: center;
            font-weight: bold;
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Zonal Manager / District Coordinator / Block Coordinator <br> Application Form</h2>

    <!-- Personal Details -->
    <div class="form-group">
        <label>Applicant Name:</label>
        <input type="text" placeholder="Enter full name">
    </div>

    <div class="form-group">
        <label>Father’s Name:</label>
        <input type="text" placeholder="Enter father's name">
    </div>

    <div class="form-group">
        <label>Mother’s Name:</label>
        <input type="text" placeholder="Enter mother's name">
    </div>

    <div class="form-group">
        <label>Mobile Number:</label>
        <input type="tel" placeholder="Enter mobile number">
    </div>

    <div class="form-group">
        <label>Email ID:</label>
        <input type="email" placeholder="Enter email address">
    </div>

    <div class="form-group">
        <label>UID No:</label>
        <input type="text" placeholder="Enter UID number">
    </div>

    <div class="form-group">
        <label>PAN No:</label>
        <input type="text" placeholder="Enter PAN number">
    </div>

    <div class="form-group">
        <label>Date of Birth:</label>
        <input type="date">
    </div>

    <!-- Post Selection -->
    <label>Post:</label>
    <div class="radio-group">
        <label><input type="radio" name="post"> State Nodal</label>
        <label><input type="radio" name="post"> Zonal Manager</label>
        <label><input type="radio" name="post"> District Coordinator</label>
        <label><input type="radio" name="post"> Block Coordinator</label>
        <label><input type="radio" name="post"> Branch Manager</label>
        <label><input type="radio" name="post"> DEO-CUM-Instructor</label>
    </div>

    <!-- Address -->
    <h3>Full Address</h3>
    <div class="form-group">
        <label>Address:</label>
        <input type="text" placeholder="Enter full address">
    </div>

    <div class="form-group">
        <label>Block:</label>
        <input type="text" placeholder="Enter block">
    </div>

    <div class="form-group">
        <label>Police Station:</label>
        <input type="text" placeholder="Enter police station">
    </div>

    <div class="form-group">
        <label>District:</label>
        <input type="text" placeholder="Enter district">
    </div>

    <div class="form-group">
        <label>Pin Code:</label>
        <input type="text" placeholder="Enter pin code">
    </div>

    <div class="form-group">
        <label>Educational Qualification:</label>
        <input type="text" placeholder="Enter qualification">
    </div>

    <div class="form-group">
        <label>Extra Qualification:</label>
        <input type="text" placeholder="Enter additional qualification">
    </div>

    <div class="form-group">
        <label>Experience:</label>
        <input type="text" placeholder="Enter experience">
    </div>

    <!-- Bank Details -->
    <h3>Bank Details</h3>
    <div class="table-container">
        <table>
            <tr>
                <th>Bank Name</th>
                <th>Branch Name</th>
                <th>IFSC Code</th>
                <th>Account Number</th>
            </tr>
            <tr>
                <td><input type="text" placeholder="Enter bank name"></td>
                <td><input type="text" placeholder="Enter branch name"></td>
                <td><input type="text" placeholder="Enter IFSC code"></td>
                <td><input type="text" placeholder="Enter account number"></td>
            </tr>
        </table>
    </div>

    <!-- Photo Upload -->
    <div class="photo-box">
        <label for="photo">Upload Photo</label>
        <input type="file" id="photo">
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>
            <p>Place:</p>
        </div>
        <div>
            <p>Date:</p>
        </div>
        <div>
            <p>Signature</p>
        </div>
    </div>
</div>

</body>
</html>
