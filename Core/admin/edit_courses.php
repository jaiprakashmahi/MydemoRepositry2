<!DOCTYPE html>
<html lang="en">
<?php
include 'conn.php';

// Check if course_code is set
$course_code = isset($_GET['course_code']) ? $_GET['course_code'] : '';

if ($course_code) {
    // Fetch existing course details
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_code = ?");
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = $_POST["course_name"];
    $duration = $_POST["duration"];
    $price = $_POST["price"];
    $details = $_POST["details"];
    $old_image = $_POST["old_image"]; // Retrieve the old image filename

    $image = $old_image; // Default to old image
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        $image = time() . "_" . basename($_FILES["image"]["name"]); // Unique filename
        $target_file = $target_dir . $image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Delete old image if new image uploaded
                if (!empty($old_image) && file_exists("uploads/" . $old_image)) {
                    unlink("uploads/" . $old_image);
                }
            } else {
                echo "<script>alert('Image upload failed!');</script>";
                $image = $old_image; // Keep old image if upload fails
            }
        }
    }

    // Update query with image
    $updateSql = $conn->prepare("UPDATE courses SET course_name=?, duration=?, price=?, details=?, image=? WHERE course_code=?");
    $updateSql->bind_param("ssssss", $course_name, $duration, $price, $details, $image, $course_code);

    if ($updateSql->execute()) {
        echo "<script>alert('Course updated successfully!'); window.location.href='courses.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
    $updateSql->close();
}
?>

<head>
    	
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignLab" >
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
	    <div id="main-wrapper">
	
<?php include 'menu.php'; ?>
		
    <div class="content-body">
        <div class="container-fluid">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Edit Course</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-6 col-sm-6">
                                        <div class="mb-3">
                                            <label class="form-label text-primary">Course Name<span class="required">*</span></label>
                                            <input type="text" class="form-control" name="course_name" value="<?= htmlspecialchars($row['course_name'] ?? '') ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-primary">Duration<span class="required">*</span></label>
                                            <input type="text" class="form-control" name="duration" value="<?= htmlspecialchars($row['duration'] ?? '') ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-primary">Price<span class="required">*</span></label>
                                            <input type="number" class="form-control" name="price" value="<?= htmlspecialchars($row['price'] ?? '') ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-primary">Course Details<span class="required">*</span></label>
                                            <textarea class="form-control" name="details" required><?= htmlspecialchars($row['details'] ?? '') ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Upload New Image:<span class="required">*</span></label>
                                          	<input  class="form-control" type="file" name="image" id="imageUpload">
											<input type="hidden" name="old_image" value="<?= htmlspecialchars($row['image'] ?? '') ?>">
										</div>

                                        <div class="float-end">
                                            <button class="btn btn-primary" type="submit">Save</button>
                                        </div>
                                    </div>
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
    <!-- Required vendors -->


    <script>
        document.getElementById("imageUpload").addEventListener("change", function(event) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("imagePreview").src = e.target.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>
    <script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	
		
	<script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
	
	
	
    <script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
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
