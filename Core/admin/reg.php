<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>


    <style>
          body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
        }
        .container {
            width: 85%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid red;
        }
        h2 {
            text-align: center;
        }
        .form-group {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            width: 50%;
            flex-direction: column;
        }
        .form-group label {
            width: 35%;
            font-weight: bold;
            padding: 5px;
        }
        .form-group input, .form-group select {
            width: 90%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group input[type="file"] {
            padding: 5px;
        }
        .submit-btn {
            text-align: center;
        }
        .submit-btn button {
            background: blue;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn button:hover {
            background: darkblue;
        }

        @media screen and (max-width: 768px) {
            .form-group {
                width: 100%;
            }
            .form-group label {
                width: 100%;
            }
            .form-group input, .form-group select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>STUDENT REGISTRATION</h2>
<form action="studentreg.php" method="post" enctype="multipart/form-data">
  <div style="display: flex; width: 100%;">
    <div class="form-group">
        <label>State*</label>
                <select id="state-dropdown" name="state" class="form-control" required>
                    <option value="">Select State</option>
                    <?php include 'conn.php';
                       $query = "SELECT id, name FROM states";
                        $result = mysqli_query($conn, $query);
                         while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                    ?>
                </select>
    </div>

    <div class="form-group">
        <label>District*</label>
            <select id="district-dropdown" name="district" class="form-control" required>
                <option value="">Select District</option>
            </select>
    </div>

          <div class="form-group">
                <label>Study Center*</label>
                <select name="study_center" required>
                    <option value="">Select Center</option>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?= htmlspecialchars($center['center_name']) ?>">
                            <?= htmlspecialchars($center['center_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
    
</div>


    
    <div style="display: flex; width: 100%;">

    <div class="form-group">
            <label>Course Name</label>
            <select name="course_name" onchange="fetchCourseDetails(this.value)" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['course_name']) ?>">
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Duration</label>
            <input type="text" id="duration" name="duration" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="text" id="price" name="price" class="form-control" readonly>
        </div>
    </div>

    

    <h3 style="color: red; text-align: center;">Student Details</h3>
    <div style="display: flex; width: 100%;">
        <div class="form-group">
            <label>Name*</label>
            <input type="text" name="name" placeholder="Enter Name">
        </div>
        <div class="form-group">
            <label>Father's Name*</label>
            <input type="text" name="father_name" placeholder="Enter Father's Name">
        </div>
         
        <div class="form-group">
            <label>Mother Name</label>
            <input type="text" name="mother_name" placeholder="Mother Name">
        </div>
    </div>
    <div style="display: flex; width: 100%;">
        <div class="form-group">
            <label>Email ID*</label>
            <input type="email" name="email" placeholder="Enter Email ID">
        </div>
        <div class="form-group">
            <label>Date of Birth*</label>
            <input type="date" name="dob" class="form-control" placeholder="Date of Birth" required>
        </div>

    </div>
    <div style="display: flex; width: 100%;">
        <div class="form-group">
            <label>Gender*</label>
            <select name="gender">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
 
        <div class="form-group">
            <label>Blood Group</label>
            <input type="text" name="blood_group" placeholder="Blood Group">
        </div>

        <div class="form-group">
            <label>Mobile Number*</label>
            <input type="text" name="mobile" placeholder="Enter Mobile Number">
        </div>
    </div>
    <div style="display: flex; width: 100%;">
        <div class="form-group">
            <label>Address*</label>
            <input type="text" name="address" placeholder="Enter Address">
        </div>
        <div class="form-group">
            <label>Pin Code*</label>
            <input type="text" name="pin_code" placeholder="Enter Pincode">
        </div>
         
    </div>
    <div style="display: flex; width: 100%;">
        <div class="form-group">
            <label>City*</label>
            <input type="text" name="city" placeholder="Enter City">
        </div>
         <div class="form-group">
            <label>Block*</label>
            <input type="text" name="block" placeholder="Enter Block">
        </div>
         <div class="form-group">
            <label>Post Office*</label>
            <input type="text" name="post_office" placeholder="Enter Post Office">
        </div>
        

    </div>
<div style="display: flex; width: 100%;">
    <div class="form-group">
        <label>Photo*</label>
        <input type="file" name="photo">
    </div>
    <div class="form-group">
        <label>ID Proof*</label>
        <input type="file" name="id_proof">
    </div>
</div>
<div style="display: flex; width: 100%;">
    <div class="form-group">
        <label>Reg. Amount</label>
        <input type="text" name="reg_amount" value="200.00" readonly>
    </div>
    
    <div class="form-group">
            <label>Payment Mode*</label>
            <select name="payment_mode" id="payment_mode" required>
                <option value="">Select Payment Mode</option>
                <option value="Online">Online</option>
                <option value="Offline">Offline</option>
            </select>
        </div>
    
</div>
    <h3 style="color: red; text-align: center;">Student Login Details</h3>

    <div style="display: flex; width: 100%;">
        <div class="form-group">
            <label>UserName*</label>
            <input type="text" name="username" placeholder="Username">
        </div>

        <div class="form-group">
            <label>Password*</label>
            <input type="password" name="password" placeholder="password">
        </div>
    </div>
 
    <div class="submit-btn">
        <button type="submit">SUBMIT</button>
    </div>

    
</form>



</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.querySelector('#payment_mode');
    if (select) {
        select.addEventListener('change', function () {
            handlePaymentMode(this.value);
        });
    }
});

function handlePaymentMode(mode) {
    if (mode === 'Online') {
        Swal.fire({
            title: 'Online Payment Selected',
            text: 'You will be redirected to make a ₹200 payment online after registration.',
            icon: 'info',
            confirmButtonText: 'Got it!'
        });
    } else if (mode === 'Offline') {
        Swal.fire({
            title: 'Offline Payment Selected',
            text: 'Please visit the center and pay ₹200 to complete registration.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    }
}
</script>


 
</body>
</html>
