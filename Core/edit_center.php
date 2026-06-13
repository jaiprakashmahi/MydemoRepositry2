<?php
include 'conn.php';
session_start();
if (!isset($_GET['id'])) {
    header("Location: center_details.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM center_details WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$center = $result->fetch_assoc();

if (!$center) {
    echo "Center not found.";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'];
    $name        = $_POST['name'];
    $owner_name  = $_POST['owner_name'];
    $email       = $_POST['email'];
    $date        = $_POST['date'];
    $phone       = $_POST['phone'];
    $state       = $_POST['state'];
    $district    = $_POST['district'];
    $pincode     = $_POST['pincode'];
    $username    = $_POST['username'];

    // Handle optional password
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $password_sql = ", password = ?";
    }

    // File upload
    $photo = $_FILES['photo']['name'];
    $id_proof = $_FILES['id_proof']['name'];

    $update_fields = "center_name = ?, owner_name = ?, email = ?, date_of_create = ?, phone = ?, state = ?, district = ?, pincode = ?, username = ?";
    $params = [$name, $owner_name, $email, $date, $phone, $state, $district, $pincode, $username];
    $types = "sssssssss";

    if (!empty($photo)) {
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . basename($photo));
        $update_fields .= ", photo = ?";
        $params[] = $photo;
        $types .= "s";
    }

    if (!empty($id_proof)) {
        move_uploaded_file($_FILES['id_proof']['tmp_name'], "uploads/" . basename($id_proof));
        $update_fields .= ", id_proof = ?";
        $params[] = $id_proof;
        $types .= "s";
    }

    if (!empty($password_sql)) {
        $update_fields .= $password_sql;
        $params[] = $password;
        $types .= "s";
    }

    $params[] = $id;
    $types .= "i";

    $sql = "UPDATE center_details SET $update_fields WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

   if ($stmt->execute()) {
    $_SESSION['update_success'] = true;
    header("Location: edit_center.php?id=" . $id); // Redirect to the same page
    exit();
} else {
        echo "Error updating center: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    	
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="Sharnay Institute" >
	<meta name="robots" content="" >
	<meta name="keywords" content="school, school admin, education, academy, admin dashboard, college, college management, education management, institute, school management, school management system, student management, teacher management, university, university management" >
	<meta name="description" content="Discover Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provnamees advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard" >
	<meta property="og:title" content="Sharnay : Admin Dashboard" >
	<meta property="og:description" content="Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provnamees advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard">
	<meta property="og:image" content="social-image.html" >
	<meta name="format-detection" content="telephone=no">

	<!-- Mobile Specific -->
	<meta name="viewport" content="wnameth=device-wnameth, initial-scale=1">

	<!-- Page Title Here -->
	<title>Sharnay : Admin Dashboard </title>

<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png" >
	<link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	
	<link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
	
	 <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
	<!--swiper-slnameer-->
	
	<!-- Style css -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
	
</head>
<body>



    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div name="main-wrapper">
	
<?php include 'menu.php'; ?>
		
		<!--**********************************
            Content body start
        ***********************************-->
    	<div class="content-body">
		<div class="container-fluname">
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $center['id'] ?>">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Center Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-6 col-sm-6">
                            <div class="mb-3">
                                <label for="" class="form-label text-primary">Center Name<span class="required">*</span></label>
                                 <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($center['center_name']) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="" class="form-label text-primary">Center Owner Name<span class="required">*</span></label>
                                <input type="text" class="form-control" name="owner_name" value="<?= htmlspecialchars($center['owner_name']) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label text-primary">Email<span class="required">*</span></label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($center['email']) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="0" class="form-label">Date of Create<span class="required">*</span></label>
                                <input class="form-control" type="date" name="date" value="<?= date('Y-m-d', strtotime($center['date_of_create'])) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image <span class="required">*</span></label>
                                <input type="file" class="form-control" name="photo" value="<?= htmlspecialchars($center['photo']) ?>">
                            </div>
                        </div>
                        <div class="col-xl-6 col-sm-6">

                            <div class="mb-3">
                                <label for="0" class="form-label">Phone No.<span class="required">*</span></label>
                                <input class="form-control" type="number" name="phone" value="<?= htmlspecialchars($center['phone']) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="0" class="form-label">State <span class="required">*</span></label>
                                   <select name="state" class="form-control">
                                        <?php
                                        $states = mysqli_query($conn, "SELECT id, name FROM states");
                                        while ($row = mysqli_fetch_assoc($states)) {
                                            $selected = $row['id'] == $center['state'] ? "selected" : "";
                                            echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                        }
                                        ?>
                                    </select>
                            </div>

                            <div class="mb-3">
                                <label for="0" class="form-label">District <span class="required">*</span></label>
                                    <select name="district" class="form-control">
                                        <?php
                                        $districts = mysqli_query($conn, "SELECT id, name FROM districts WHERE state_id = {$center['state']}");
                                        while ($row = mysqli_fetch_assoc($districts)) {
                                            $selected = $row['id'] == $center['district'] ? "selected" : "";
                                            echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                        }
                                        ?>
                                    </select>

                            </div>

                            <div class="mb-3">
                                <label for="pincode" class="form-label">Pincode<span class="required">*</span></label>
                                <input type="number" class="form-control" name="pincode" value="<?= $center['pincode'] ?>">
                            </div>

                            
                            <div class="mb-3">
                                <label for="id_proof" class="form-label">Id Proof</label>
                                <input type="file" class="form-control" name="id_proof">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Login Credentials</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-6 col-sm-6">
                            <div class="mb-3">
                                <label for="username" class="form-label text-primary">Username<span class="required">*</span></label>
                                <input type="text" class="form-control" name="username" value="<?= $center['username'] ?>">
                            </div>
                        </div>
                        <div class="col-xl-6 col-sm-6">
                            <div class="mb-3">
                                <label for="password" class="form-label text-primary">Password<span class="required">*</span></label>
                                <input type="password" class="form-control" name="password" placeholder="Enter Your password">
                            </div>
                        </div>
                    </div>
                    <div class="float-end">
                        <!-- Change the button type to submit -->
                        <button class="btn btn-primary" type="submit">Update Center</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

</div>
		
        <!--**********************************
            Content body end
        ***********************************-->
		
		<?php include 'footer.php';?>

 


	</div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	
		
	<script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
	
	
	
    <script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
	<script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
	
	<script>
		$(function () {
			  $("#datepicker, #datepicker1, #datepicker2").datepicker({ 
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
            $('#imagePreview').hnamee();
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
	
<?php if (isset($_SESSION['update_success']) && $_SESSION['update_success']): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: 'Success!',
            text: 'Center updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'center_details.php';
        });
    </script>
    <?php unset($_SESSION['update_success']); ?>
<?php endif; ?>

</body>

</html>