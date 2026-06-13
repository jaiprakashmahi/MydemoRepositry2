
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
	<meta name="viewport" content="width=device-width, initial-scale=1">

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
    <link href="css/admin-standard.css" rel="stylesheet">
	
</head>
<body>



    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
	
<?php include 'menu.php'; ?>
		
		<!--**********************************
            Content body start
        ***********************************-->
    	<div class="content-body">
		<div class="container-fluid">
    <form action="insert_center.php" method="POST" enctype="multipart/form-data">
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
                                <input type="text" class="form-control" name="name" placeholder="Your Name">
                            </div>

                            <div class="mb-3">
                                <label for="" class="form-label text-primary">Center Owner Name<span class="required">*</span></label>
                                <input type="text" class="form-control" name="owner_name" placeholder="Your Name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label text-primary">Email<span class="required">*</span></label>
                                <input type="email" class="form-control" name="email" placeholder="Enter Your Email">
                            </div>

                            <div class="mb-3">
                                <label for="0" class="form-label">Date of Create<span class="required">*</span></label>
                                <input class="form-control" type="date" name="date">
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image <span class="required">*</span></label>
                                <input type="file" class="form-control" name="photo">
                            </div>
                             <div class="mb-3">
                                <label for="reg_amount" class="form-label">Reg. Amount<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="reg_amount" value="3500.00" readonly>
                            </div>
                        </div>
                        <div class="col-xl-6 col-sm-6">

                            <div class="mb-3">
                                <label for="0" class="form-label">Phone No.<span class="required">*</span></label>
                                <input class="form-control" type="number" name="phone" placeholder="Enter Your Mobile No.">
                            </div>

                            <div class="mb-3">
                                <label for="0" class="form-label">State <span class="required">*</span></label>
                                    <select id="state-dropdown" name="state" class="form-control" required>
                                    <option value="">Select State</option>
                                    <?php
                                    include 'conn.php';
                                    $query = "SELECT id, name FROM states";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="0" class="form-label">District <span class="required">*</span></label>
                                <select id="district-dropdown" name="district" class="form-control" required>
                                    <option value="">Select District</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="block-dropdown" class="form-label">Block <span class="required">*</span></label>
                                <select id="block-dropdown" name="block" class="form-control" required disabled>
                                    <option value="">Select District First</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="pincode" class="form-label">Pincode<span class="required">*</span></label>
                                <input type="number" class="form-control" name="pincode" placeholder="Zip Code">
                            </div>

                            
                            <div class="mb-3">
                                <label for="id_proof" class="form-label">Id Proof<span class="required">*</span></label>
                                <input type="file" class="form-control" name="id_proof">
                            </div>
                           
                                
                            <div class="mb-3">
                                <label for="payment_mode" class="form-label">Payment Mode<span class="required">*</span></label>
                                        <select name="payment_mode" class="form-control" id="payment_mode" required>
                                            <option value="">Select Payment Mode</option>
                                            <option value="Online">Online</option>
                                            <option value="Offline">Offline</option>
                                        </select>
                            </div>
                                
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
                                <input type="text" class="form-control" name="username" placeholder="Username">
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
                        <button class="btn btn-primary" type="submit">Save</button>
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
    <script src="js/admin-standard.js"></script>
	<script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
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
        $('#district-dropdown').html('<option value="">Loading districts...</option>').prop('disabled', true);
        $('#block-dropdown').html('<option value="">Select District First</option>').prop('disabled', true);
        if (stateID) {
            $.ajax({
                type: 'POST',
                url: 'fetch_district.php',
                data: { state_id: stateID },
                success: function (html) {
                    $('#district-dropdown').html(html).prop('disabled', false);
                },
                error: function () {
                    Swal.fire('Error', 'Error loading districts', 'error');
                }
            });
        } else {
            $('#district-dropdown').html('<option value="">Select District</option>').prop('disabled', true);
        }
    });

    $('#district-dropdown').on('change', function () {
        let districtID = $(this).val();
        $('#block-dropdown').html('<option value="">Loading blocks...</option>').prop('disabled', true);
        if (districtID) {
            $.ajax({
                type: 'POST',
                url: 'fetch_block.php',
                data: { district_id: districtID },
                success: function (html) {
                    $('#block-dropdown').html(html).prop('disabled', false);
                },
                error: function () {
                    Swal.fire('Error', 'Error loading blocks', 'error');
                }
            });
        } else {
            $('#block-dropdown').html('<option value="">Select District First</option>').prop('disabled', true);
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
	

</body>

</html>