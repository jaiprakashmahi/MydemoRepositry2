<?php
session_start(); // Required to use $_SESSION
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    die("Teacher ID not provided.");
}

$id = intval($_GET['id']);
$query = "SELECT * FROM teachers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Teacher not found.");
}

$teacher = $result->fetch_assoc();

// Fetch Education
$edu_query = $conn->prepare("SELECT * FROM teacher_education WHERE teacher_id = ?");
$edu_query->bind_param("i", $id);
$edu_query->execute();
$education_result = $edu_query->get_result();

// Fetch Experience
$exp_query = $conn->prepare("SELECT * FROM teacher_experience WHERE teacher_id = ?");
$exp_query->bind_param("i", $id);
$exp_query->execute();
$experience_result = $exp_query->get_result();
// First fetch center data
$sql = "SELECT id, center_name FROM center_details";
$result = $conn->query($sql);

// Store center data in array
    $centers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $centers[] = $row;
        }
    } else {
        die("Center Query Error: " . $conn->error);
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    	
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignLab" >
	<meta name="robots" content="" >
	<meta name="keywords" content="" >
	<meta name="description" content="" >
	<meta property="og:title" content="" >
	<meta property="og:description" content="">
	<meta property="og:image" content="social-image.html" >
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Edit Teacher Registration Form</title>

	<link rel="shortcut icon" type="image/png" href="images/favicon.png" >
	<link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
	<link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">	
	<link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="css/style.css" rel="stylesheet">

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

  

    <div id="main-wrapper">
	
   <?php include 'menu.php';?>
		<!--**********************************
            Content body start
        ***********************************-->
    <div class="content-body">
	
    <div class="container">
        <h2>Edit TEACHER </h2>
        <form action="UpdateTeacher.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($teacher['id']) ?>">

                <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>Adv. Id*</label>
                        <input type="text" name="adv_id" value="<?= htmlspecialchars($teacher['adv_id'] ?? '') ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Name*</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($teacher['name']) ?>" placeholder="Enter Name">
                    </div>
                </div>
                    <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>Father's Name*</label>
                        <input type="text" name="father_name" value="<?= htmlspecialchars($teacher['father_name']) ?>" placeholder="Enter Father's Name">
                    </div>
                    
                    <div class="form-group">
                        <label>Mother Name</label>
                        <input type="text" name="mother_name" value="<?= htmlspecialchars($teacher['mother_name']) ?>" placeholder="Mother Name">
                    </div>
                </div>
                <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>Email ID*</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" placeholder="Enter Email ID">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth*</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($teacher['dob']) ?>" class="form-control" placeholder="Date of Birth" required>
                    </div>

                </div>
                <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>Gender*</label>
                        <select name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($teacher['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($teacher['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
            
                    <div class="form-group">
                        <label>Blood Group</label>
                        <input type="text" name="blood_group" value="<?= htmlspecialchars($teacher['blood_group']) ?>" placeholder="Blood Group">
                    </div>

                    <div class="form-group">
                        <label>Mobile Number*</label>
                        <input type="text" name="mobile" value="<?= htmlspecialchars($teacher['mobile']) ?>" placeholder="Enter Mobile Number">
                    </div>
                </div>
                <div style="display: flex; width: 100%;">
                <div class="form-group">
                    <label>State*</label>
                           <select id="state-dropdown" name="state_id" class="form-control" required>
                                <option value="">Select State</option>
                                <?php
                                $query = "SELECT id, name FROM states";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $selected = ($teacher['state_id'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                </div>

                <div class="form-group">
                    <label>District*</label>
                        <select id="district-dropdown" name="district_id" class="form-control" required>
                            <option value="">Select District</option>
                        </select>
                </div>                
            </div>


                <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>Address*</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($teacher['address']) ?>" placeholder="Enter Address">
                    </div>
                    <div class="form-group">
                        <label>Pin Code*</label>
                        <input type="text" name="pin_code" value="<?= htmlspecialchars($teacher['pin_code']) ?>" placeholder="Enter Pincode">
                    </div>
                    
                </div>
                <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>City*</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($teacher['city']) ?>" placeholder="Enter City">
                    </div>
                    <div class="form-group">
                        <label>Block*</label>
                        <input type="text" name="block" value="<?= htmlspecialchars($teacher['block']) ?>" placeholder="Enter Block">
                    </div>
                    <div class="form-group">
                        <label>Post Office*</label>
                        <input type="text" name="post_office" value="<?= htmlspecialchars($teacher['post_office']) ?>" placeholder="Enter Post Office">
                    </div>
                    

                </div>
            <div style="display: flex; width: 100%;">
                <div class="form-group">
                    <?php if ($teacher['photo']): ?>
                        <img src="uploads/<?= $teacher['photo'] ?>" width="60">
                    <?php endif; ?>
                    <input type="file" name="photo">
                </div>
                <div class="form-group">
                    <?php if ($teacher['id_proof']): ?>
                    <img src="uploads/<?= $teacher['id_proof'] ?>" width="60">
                <?php endif; ?>
                <input type="file" name="id_proof">
                </div>
            </div>
            
            <hr style="height:2px; border-width:100%; color:red; background-color:gray">
            
                <h3 style="color: red; text-align: left;"><ul>Education Summery<ul></h3>

               <div style="display: flex; width: 100%;">
            <table class="table table-bordered" id="education-table">
                <!-- <table class="table table-bordered" id="education-table"> -->
                <thead>
                    <tr>
                        <th>Education</th>
                        <th>Session From</th>
                        <th>Session To</th>
                        <th>Total Marks</th>
                        <th>Obt. Marks</th>
                        <th>% / CGPA</th>
                        <th>Grade</th>
                        <th>Upload Marksheet</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="education-body">
<?php if ($education_result->num_rows > 0): ?>
    <?php while ($edu = $education_result->fetch_assoc()): ?>
        <tr>
            <td>
                <select name="education[]" class="form-control" required>
                    <option value="">-- Select Education --</option>
                    <?php
                    $eduOptions = [
                        "PhD", "M.Sc-IT", "M.Tech", "M.Sc.", "MCA", "MBA", "MA", "B.Tech", "B.Sc-IT",
                        "B.Sc", "BCA", "B.Com", "BBA", "B.ED", "BA", "Graduation", "12th", "10th"
                    ];
                    foreach ($eduOptions as $option) {
                        $selected = ($edu['education'] == $option) ? 'selected' : '';
                        echo "<option value='$option' $selected>$option</option>";
                    }
                    ?>
                </select>
            </td>
            <td><input type="text" name="session_from[]" class="form-control" value="<?= htmlspecialchars($edu['session_from']) ?>"></td>
            <td><input type="text" name="session_to[]" class="form-control" value="<?= htmlspecialchars($edu['session_to']) ?>"></td>
            <td><input type="number" name="total_marks[]" class="form-control" value="<?= htmlspecialchars($edu['total_marks']) ?>"></td>
            <td><input type="number" name="obt_marks[]" class="form-control" value="<?= htmlspecialchars($edu['obt_marks']) ?>"></td>
            <td><input type="text" name="percentage[]" class="form-control" value="<?= htmlspecialchars($edu['percentage']) ?>"></td>
            <td><input type="text" name="grade[]" class="form-control" value="<?= htmlspecialchars($edu['grade']) ?>"></td>
            <td>
                <input type="file" name="marksheet[]" class="form-control">
                <?php if (!empty($edu['marksheet'])): ?>
                    <small><a href="uploads/education_docs/<?= htmlspecialchars($edu['marksheet']) ?>" target="_blank">View File</a></small>
                <?php endif; ?>
            </td>
            <td><button type="button" class="btn btn-danger remove-row">X</button></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <!-- If no education exists, show one empty row -->
    <tr>
        <td>
            <select name="education[]" class="form-control" required>
                <option value="">-- Select Education --</option>
                <?php foreach ($eduOptions as $option) echo "<option value='$option'>$option</option>"; ?>
            </select>
        </td>
        <td><input type="text" name="session_from[]" class="form-control" placeholder="YYYY"></td>
        <td><input type="text" name="session_to[]" class="form-control" placeholder="YYYY"></td>
        <td><input type="number" name="total_marks[]" class="form-control"></td>
        <td><input type="number" name="obt_marks[]" class="form-control"></td>
        <td><input type="text" name="percentage[]" class="form-control"></td>
        <td><input type="text" name="grade[]" class="form-control"></td>
        <td><input type="file" name="marksheet[]" class="form-control"></td>                    
        <td><button type="button" class="btn btn-danger remove-row">X</button></td>
    </tr>
<?php endif; ?>
</tbody>

                <tfoot>
                    <tr>
                        <td colspan="9" class="text-left">
                            <button type="button" class="btn btn-primary" id="addRow">+ Add More</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
            
        </div>

                    
                        <hr style="height:2px; border-width:100%; color:red; background-color:gray">
                    
                    <h3 style="color: red; text-align: left;">Experience</h3>

    <div id="experience-container">
    <?php if ($experience_result->num_rows > 0): ?>
        <?php while ($exp = $experience_result->fetch_assoc()): ?>
            <div class="experience-row" style="display: flex; width: 100%; gap: 10px; margin-bottom: 10px;">
                <div class="form-group">
                    <label>Org.*</label>
                    <input type="text" name="experience_org[]" class="form-control" value="<?= htmlspecialchars($exp['org']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input type="text" name="experience_role[]" class="form-control" value="<?= htmlspecialchars($exp['role']) ?>">
                </div>

                <div class="form-group">
                    <label>From</label>
                    <input type="text" name="experience_from[]" class="form-control" value="<?= htmlspecialchars($exp['experience_from']) ?>">
                </div>

                <div class="form-group">
                    <label>To</label>
                    <input type="text" name="experience_to[]" class="form-control" value="<?= htmlspecialchars($exp['experience_to']) ?>">
                </div>

                <div class="form-group">
                    <label>Document</label>
                    <input type="file" name="experience_doc[]" class="form-control">
                    <?php if (!empty($exp['document'])): ?>
                        <small><a href="uploads/experience_docs/<?= htmlspecialchars($exp['experience_doc']) ?>" target="_blank">View File</a></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="button" class="btn btn-danger remove-experience">X</button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <!-- Default empty row if no experience found -->
        <div class="experience-row" style="display: flex; width: 100%; gap: 10px; margin-bottom: 10px;">
            <div class="form-group">
                <label>Org.*</label>
                <input type="text" name="experience_org[]" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <input type="text" name="experience_role[]" class="form-control">
            </div>

            <div class="form-group">
                <label>From</label>
                <input type="text" name="experience_from[]" class="form-control">
            </div>

            <div class="form-group">
                <label>To</label>
                <input type="text" name="experience_to[]" class="form-control">
            </div>

            <div class="form-group">
                <label>Document</label>
                <input type="file" name="experience_doc[]" class="form-control">
            </div>

            <div class="form-group">
                <label>&nbsp;</label><br>
                <button type="button" class="btn btn-danger remove-experience">X</button>
            </div>
        </div>
    <?php endif; ?>


        

        <!-- Add More Button -->
            <button type="button" class="btn btn-primary" id="add-experience">+ Add More</button>
        </div>
        
        <div style="text-align: center;">
            <div class="form-group" style="display: inline-block;">
            <label>Allot Center*</label>
            <select name="allot_center" required>
                <option value="">Select Center</option>
                <?php foreach ($centers as $center): ?>
                    <option value="<?= htmlspecialchars($center['center_name']) ?>"
                        <?= ($teacher['allot_center'] == $center['center_name']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($center['center_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        </div>
                
        <div class="submit-btn">
            <button type="submit">Update Now</button> &nbsp; &nbsp; <button><a href="Teachers.php">Back to Teachers List</a></button>
        </div>
        
    </form>



    </div>    
	</div>
    
            <?php include 'footer.php';?>
    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
	<script src="js/plugins-init/datatables.init.js"></script>
	<script src="vendor/wow-master/dist/wow.min.js"></script>	
	<script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
	<script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
	
	<script>
		$(function () {
			  $("#datepicker").datepicker({ 
					autoclose: true, 
					todayHighlight: true
			  }).datepicker('update', new Date());
		
		});

	</script>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $('#state-dropdown').on('change', function () {
        let stateID = $(this).val();
        if (stateID) {
            $.ajax({
                type: 'POST',
                url: 'fetch_district.php', // ✅ should point to separate file
                data: { state_id: stateID },
                success: function (html) {
                    $('#district-dropdown').html(html);
                },
                error: function () {
                    alert('Error loading districts');
                }
            });
        } else {
            $('#district-dropdown').html('<option value="">Select District</option>');
        }
    });
</script>
<script>
    $(document).ready(function () {
    const selectedState = $('#state-dropdown').val();
    const selectedDistrict = "<?= $teacher['district_id'] ?>";

    if (selectedState) {
        $.ajax({
            type: 'POST',
            url: 'fetch_district.php',
            data: { state_id: selectedState },
            success: function (html) {
                $('#district-dropdown').html(html);
                $('#district-dropdown').val(selectedDistrict); // set selected district
            },
            error: function () {
                alert('Error loading districts');
            }
        });
    }
});

</script>

<script>
$(document).ready(function () {
    $('#addRow').click(function () {
        let newRow = `
            <tr>
                        <td>
                            <select name="education[]" class="form-control" required>
                                <option value="">-- Select Education --</option>
                                <option value="PhD">PhD</option>
                                <option value="M.Sc-IT">M.Sc-IT</option>
                                <option value="M.Tech">M.Tech</option>
                                <option value="M.Sc.">M.Sc.</option>
                                <option value="MCA">MCA</option>
                                <option value="MBA">MBA</option>
                                <option value="MA">MA</option>
                                <option value="B.Tech">B.Tech</option>
                                <option value="B.Sc-IT">B.Sc-IT</option>
                                <option value="B.Sc">B.Sc</option>
                                <option value="BCA">BCA</option>
                                <option value="B.Com">B.Com</option>
                                <option value="BBA">BBA</option>
                                <option value="B.ED">B.ED</option>
                                <option value="BA">BA</option>
                                <option value="Graduation">Graduation</option>
                                <option value="12th">12th</option>
                                <option value="10th">10th</option>
                            </select>
                        </td>
                <td><input type="text" name="session_from[]" class="form-control" placeholder="YYYY"></td>
                <td><input type="text" name="session_to[]" class="form-control" placeholder="YYYY"></td>
                <td><input type="number" name="total_marks[]" class="form-control"></td>
                <td><input type="number" name="obt_marks[]" class="form-control"></td>
                <td><input type="text" name="percentage[]" class="form-control"></td>
                <td><input type="text" name="grade[]" class="form-control"></td>
                <td><input type="file" name="marksheet[]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger remove-row">X</button></td>
            </tr>`;
        $('#education-body').append(newRow);
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });
});

</script>
<script>
    $(document).ready(function () {
        $('#add-experience').click(function () {
            const newRow = `
                <div class="experience-row" style="display: flex; width: 100%; gap: 10px; margin-bottom: 10px;">
                    <div class="form-group">
                        <input type="text" name="experience_org[]" class="form-control" placeholder="Organization" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="experience_role[]" class="form-control" placeholder="Role">
                    </div>
                    <div class="form-group">
                        <input type="text" name="experience_from[]" class="form-control" placeholder="From (YYYY-MM)">
                    </div>
                    <div class="form-group">
                        <input type="text" name="experience_to[]" class="form-control" placeholder="To (YYYY-MM)">
                    </div>
                    <div class="form-group">
                        <input type="file" name="experience_doc[]" class="form-control">
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-danger remove-experience">X</button>
                    </div>
                </div>`;
            $('#experience-container').append(newRow);
        });

        // Remove experience row
        $(document).on('click', '.remove-experience', function () {
            $(this).closest('.experience-row').remove();
        });
    });
</script>
<!-- SweetAlert Success Message -->
<?php if (isset($_SESSION['success_msg'])): ?>
<script>
    Swal.fire({
        title: 'Success',
        text: '<?= $_SESSION['success_msg'] ?>',
        icon: 'success',
        confirmButtonText: 'OK'
    });
</script>
<?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

</body>
</html>