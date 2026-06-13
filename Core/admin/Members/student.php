<?php
include '../conn.php';
session_start();

// Optional: Filter by logged-in member's district/state
$state = $_SESSION['state'] ?? '';
$district = $_SESSION['district'] ?? '';

// Step 1: Fetch center names (assuming members are only allowed to view their own centers)
$center_names = [];
$stmt = $conn->prepare("SELECT center_name FROM center_details WHERE state = ? AND district = ?");
$stmt->bind_param("ss", $state, $district);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $center_names[] = $row['center_name'];
}
$stmt->close();

// Step 2: Fetch registered students for those centers
$students = [];

if (!empty($center_names)) {
    $placeholders = implode(',', array_fill(0, count($center_names), '?'));
    $types = str_repeat('s', count($center_names));

    $query = "SELECT * FROM students WHERE study_center IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$center_names);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    	<title>All Students - Welcome To Sharnay Institute Dashboard</title>

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
	
<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png" >
	<link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
	<link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	
	<link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
	
	
	<!-- Style css -->
	<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
	<!-- Style css -->
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

	
</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
	<div id="preloader">
		<div class="loader"></div>
	  </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
	
       <?php include 'menu.php'; ?>
        <!--**********************************
            Sidebar end
        ***********************************-->
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
				<!-- Row -->
				<div class="row">
					<div class="col-xl-12">
						<div class="row">
							<div class="col-xl-12">
								<div class="page-title flex-wrap">
									<div class="input-group search-area mb-md-0 mb-3">
										<input type="text" class="form-control" placeholder="Search here...">
										<span class="input-group-text"><a href="javascript:void(0)">
											<svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M17.5605 15.4395L13.7527 11.6317C14.5395 10.446 15 9.02625 15 7.5C15 3.3645 11.6355 0 7.5 0C3.3645 0 0 3.3645 0 7.5C0 11.6355 3.3645 15 7.5 15C9.02625 15 10.446 14.5395 11.6317 13.7527L15.4395 17.5605C16.0245 18.1462 16.9755 18.1462 17.5605 17.5605C18.1462 16.9747 18.1462 16.0252 17.5605 15.4395V15.4395ZM2.25 7.5C2.25 4.605 4.605 2.25 7.5 2.25C10.395 2.25 12.75 4.605 12.75 7.5C12.75 10.395 10.395 12.75 7.5 12.75C4.605 12.75 2.25 10.395 2.25 7.5V7.5Z" fill="#01A3FF"/>
											</svg>
										</a></span>
									</div>
									<div>
										<!-- <select class="image-select bs-select dashboard-select me-3" aria-label="Default">
											<option selected>Newest</option>
											<option value="1">Oldest</option>
											<option value="2">Recent</option>
										</select> -->
										<!-- Button trigger modal -->
										<button type="button" class="btn btn-primary">
										<a href="add-student.php"> + New Student </a>
										</button>
									</div>
								</div>
							</div>
							<!--column-->
							<div class="col-xl-12 wow fadeInUp" data-wow-delay="1.5s">
								<div class="table-responsive full-data">
									<table class="table-responsive-lg table display dataTablesCard student-tab dataTable no-footer" id="example-student">
										<thead>
											<tr>
												<th>
													<input type="checkbox" class="form-check-input" id="checkAll" required="">
												</th>
												
												<th>Student Code</th>
												<th>Name</th>
												<th>Course</th>
												<th>Duration</th>
												<th>Price</th>
												<th>Contact</th>
												<th>Study Center</th>
												<th>Session</th>
												<th>Grade</th>
												<th class="text-end">Create Certifacate</th>
												<th>View Marksheet</th>
												<th>View Certifacate</th>
												<th class="text-end">Action</th>
											</tr>
										</thead>
										<tbody>
										<?php foreach ($students as $index => $student): ?>
											<tr>
												<td>
													<div class="checkbox me-0 align-self-center">
														<div class="custom-control custom-checkbox ">
															<input type="checkbox" class="form-check-input" name="selected[]" value="<?= $student['id']; ?>">
															<label class="custom-control-label" for="check10"></label>
														</div>
													</div>
												</td>
												<td><?= htmlspecialchars($student['student_code']) ?></td>
												<td><?= htmlspecialchars($student['name']) ?></td>
												<td><?= htmlspecialchars($student['course_name']) ?></td>
												<td><?= htmlspecialchars($student['duration']) ?></td>
												<td><?= htmlspecialchars($student['price']) ?></td>
												<td><?= htmlspecialchars($student['mobile']) ?></td>
												<td><?= htmlspecialchars($student['study_center']) ?></td>
												<td>June 2024 To Dec 2024</td>
												<td>
													<div class="badge bg-secondary">
														<?= htmlspecialchars($student['grade'] ?? 'N/A') ?>
													</div>
												</td>
												<td class="text-end">
													<a href="Certificate_Creation_Form.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-primary">Generate</a>
												</td>
												<td>
													<a href="certificate.php?id=<?= $student['id'] ?>" class="btn btn-success btn-sm">View Marksheet</a>
												</td>
												<td>
													<a href="course_certificate.php?id=<?= $student['id'] ?>" class="btn btn-success btn-sm">View Certificate</a>
												</td>
												<td class="text-end">
													<a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-warning me-2" title="Edit">
														<i class="fas fa-edit"></i>
													</a>
													<a href="delete_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-danger" 
													title="Delete" onclick="return confirm('Are you sure to delete this student?');">
														<i class="fas fa-trash-alt"></i>
													</a>
												</td>
											</tr>
											<?php endforeach; ?>

											<?php if (empty($students)): ?>
												<tr><td colspan="14" style="text-align: center;">No students found.</td></tr>
											<?php endif; ?>
												</tbody>
											</table> 
								</div>
							</div>
							<!--/column-->
						</div>
					</div>
				</div>
				<!--**********************************
					Footer start
				***********************************-->
			</div>
		</div>
		
        <!--**********************************
            Content body end
        ***********************************-->
			<!-- footer-start -->
			
				<?php include 'footer.php';?>
	</div>

    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	
	<!--datatables-->
	<script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
	<script src="js/plugins-init/datatables.init.js"></script>
	
	<!-- Dashboard 1 -->
	<script src="vendor/wow-master/dist/wow.min.js"></script>
	
	<script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
	<script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
	
	
</body>

</html>