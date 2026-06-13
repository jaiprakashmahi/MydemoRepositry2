<?php
session_start(); // Required to use $_SESSION
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (isset($_POST['state_id'])) {
    $state_id = $_POST['state_id'];

    $stmt = $conn->prepare("SELECT id, name FROM districts WHERE state_id = ?");
    $stmt->bind_param("i", $state_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>Select District</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate Advertisement ID
    $year = date('Y');
    $prefix = "ADV" . $year;

    // Get last adv_id
    $query = "SELECT adv_id FROM vacancy WHERE adv_id LIKE '$prefix%' ORDER BY adv_id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $lastCode = $row['adv_id'];
        $lastNumber = (int)substr($lastCode, -4); // Get last 4 digits
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNumber = "0001";
    }

    $adv_id = $prefix . '-' . $newNumber;

    // 1. Get all form values safely
    $post        = $_POST['post'] ?? '';
    $state       = $_POST['state'] ?? '';
    $district    = $_POST['district'] ?? '';
    $eligibility = $_POST['city'] ?? ''; // You may want to rename 'city' to 'eligibility'
    $experience  = $_POST['expericnce'] ?? '';
    $no_of_post  = $_POST['no_of_post'] ?? '';
    $start_date  = $_POST['start_date'] ?? '';
    $end_date    = $_POST['end_date'] ?? '';

    // 2. Handle document upload
    $document = '';
    if (!empty($_FILES['document']['name'])) {
        $document = uniqid() . '_' . basename($_FILES['document']['name']);
        move_uploaded_file($_FILES['document']['tmp_name'], "uploads/" . $document);
    }

    // 3. Insert into the database
    $stmt = $conn->prepare("INSERT INTO vacancy 
        (adv_id, post, state_id, district_id, eligibility, experience, no_of_post, start_date, end_date, document) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssiissssss", 
        $adv_id,
        $post, 
        $state, 
        $district, 
        $eligibility, 
        $experience, 
        $no_of_post, 
        $start_date, 
        $end_date, 
        $document
    );

    if ($stmt->execute()) {
    $_SESSION['success_msg'] = "Post requirement saved successfully.";
} else {
    $_SESSION['error_msg'] = "Error saving data: " . $stmt->error;
}
header("Location: ".$_SERVER['PHP_SELF']); // Reload same page
exit(); // Safe to use now
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

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Page Title Here -->
	<title>Add Student</title>

<!-- FAVICONS ICON -->

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

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/png" href="images/favicon.png" >
	<link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
	<link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	
	<link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
	<!-- Style css -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>



	<style>
          body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
        }
        .container {
            width: 85%;
            margin: 20px auto;
            background: #e6ebee;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid black;
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
        <h2>Create New Vacency</h2>
            <form action="" method="post" enctype="multipart/form-data">
            
                <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>Post*</label>
                        <select name="post" required>
                            <option value="0">Select Post</option>
                            <option value="Teacher">Teacher</option>
                        </select>
                    </div>

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
                </div>
                 
                <div style="display: flex; width: 100%;">
                    <div class="form-group">
                        <label>Eligiblity*</label>
                        <select name="eligibility" class="form-control" required>
                                <option value="">-- Select Eligiblity --</option>
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
                    </div>
                    <div class="form-group">
                        <label>Experience*</label>
                        <input type="text" name="experience" placeholder="Expericnce in Year">
                    </div>
                    <div class="form-group">
                        <label>Total Post</label>
                        <input type="text" name="no_of_post" placeholder="Total Post">
                    </div>

                </div>
              <div style="display: flex; width: 100%;">
                <div class="form-group">
                    <label>Start Date*</label>
                   <input type="date" name="start_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>End Date*</label>
                    <input type="date" name="end_date"  class="form-control" >
                </div>
                <div class="form-group">
                    <label>Document*</label>
                    <input type="file" name="document" class="form-control">
                </div>
            </div>

            
        </div>

                
                
        <div class="submit-btn">
            <button type="submit">SUBMIT</button>
        </div>
    </form>

  
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
    


      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#state-dropdown').on('change', function () {
        let stateID = $(this).val();
        if (stateID) {
            $.ajax({
                type: 'POST',
                url: 'fetch_district.php',
                data: { state_id: stateID },
                success: function (html) {
                    $('#district-dropdown').html(html); // FIXED this line
                },
                error: function () {
                    alert('Error loading districts');
                }
            });
        } else {
            $('#district-dropdown').html('<option value="">Select District</option>'); // FIXED here too
        }
    });
</script>
	
	 <script>
		function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').css('background-image', 'url('+e.target.result +')');
            $('#imagePreview').hide();
            $('#imagePreview').fadeIn(650);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#imageUpload").change(function() {
    readURL(this);
});
	$('.remove-img').on('click', function() {
		var imageUrl = "images/no-img-avatar.png";
		$('.avatar-preview, #imagePreview').removeAttr('style');
		$('#imagePreview').css('background-image', 'url(' + imageUrl + ')');
	});



	</script>

<?php if (isset($_SESSION['success_msg'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '<?= addslashes($_SESSION["success_msg"]) ?>',
    confirmButtonText: 'OK'
});
</script>
<?php unset($_SESSION['success_msg']); endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?= addslashes($_SESSION["error_msg"]) ?>',
    confirmButtonText: 'Close'
});
</script>
<?php unset($_SESSION['error_msg']); endif; ?>

</body>
</html>