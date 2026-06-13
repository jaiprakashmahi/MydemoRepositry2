 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../conn.php'; // Ensure this path is correct

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

// Now fetch courses separately
$query = "SELECT DISTINCT course_name, duration, price FROM courses";
$course_result = $conn->query($query);

$courses = [];
if ($course_result) {
    while ($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
} else {
    die("Course Query Error: " . $conn->error);
}

//State Fetching

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
	<link rel="shortcut icon" type="image/png" href="../images/favicon.png" >
	<link href="../vendor/wow-master/css/libs/animate.css" rel="stylesheet">
	<link href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../vendor/jquery-nice-select/css/nice-select.css">
	
	<link href="../vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
	
	
	 <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
	<!--swiper-slider-->
	
	<!-- Style css -->
    <link href="../https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">

	
</head>
<body>

  

    <div id="main-wrapper">
	
   <?php include 'center_menu.php';?>
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
				
							<?php include '../reg.php'; ?>
		
        <!--**********************************
            Content body end
        ***********************************-->
	<?php include '../footer.php';?>

      
        <!--**********************************
            Footer end
        ***********************************-->
	</div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="../vendor/global/global.min.js"></script>
	<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	  <script src="../vendor/moment/moment.min.js"></script>
	

	<script src="../vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
	<script src="../vendor/wow-master/dist/wow.min.js"></script>
	<script src="../js/custom.min.js"></script>
	<script src="../js/dlabnav-init.js"></script>
	<script src="../js/demo.js"></script>
	<script src="../js/styleSwitcher.js"></script>
	
	<script>
		$(function () {
			  $("#datepicker").datepicker({ 
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
<script>
function fetchCourseDetails(courseName) {
    if (courseName !== '') {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_course_details.php?course_name=' + encodeURIComponent(courseName), true);
        xhr.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                try {
                    const data = JSON.parse(this.responseText);
                    document.getElementById('duration').value = data.duration;
                    document.getElementById('price').value = data.price;
                } catch (e) {
                    console.error("JSON parse error:", this.responseText);
                }
            }
        };
        xhr.send();
    } else {
        document.getElementById('duration').value = '';
        document.getElementById('price').value = '';
    }
}
</script>

</body>
</html>