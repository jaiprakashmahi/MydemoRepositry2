<?php
include 'conn.php';
$query = "SELECT * FROM onlinestudents WHERE approved = 1";
$result = $conn->query($query);
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
    <link href="css/admin-standard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
@media print {
  /* Expand table contents during print */
  .table-responsive {
    overflow: visible !important;
    height: auto !important;
  }

  .dataTables_wrapper {
    overflow: visible !important;
  }

  /* Hide other page elements */
  body * {
    visibility: hidden;
  }

  #print-section, #print-section * {
    visibility: visible;
  }

  #print-section {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }

  /* Optional: Hide buttons & checkboxes */
  .btn,
  .form-check-input,
  .custom-checkbox,
  .text-end a {
    display: none !important;
  }
}
</style>
	
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
							<div class="col-xl-12 wow fadeInUp" data-wow-delay="1.5s" id="print-section">
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
												<th>Photo</th>
												<th>Id Proof</th>
												<th>Id Card Download</th>
												<th>Create Certifacate</th>
												<th>Add Marks</th>
												<th>View Marksheet</th>
												<th>View Certifacate</th>
												<th class="text-end">Action</th>
											</tr>
										</thead>
										<tbody>
											<?php
												if ($result->num_rows > 0):
												while ($row = $result->fetch_assoc()):
											?>
											
											<tr>
												<td>
													<div class="checkbox me-0 align-self-center">
														<div class="custom-control custom-checkbox ">
															 <input type="checkbox" class="form-check-input" name="selected[]" value="<?= $row['id']; ?>">
															<label class="custom-control-label" for="check10"></label>
														</div>
													</div>
												</td>
												  <td><?= htmlspecialchars($row['student_code']) ?></td>
												    <td><?= htmlspecialchars($row['name']) ?></td>
												    <td><?= htmlspecialchars($row['course_name']) ?></td>
												    <td><?= htmlspecialchars($row['duration']) ?></td>
												    <td><?= htmlspecialchars($row['price']) ?></td>
												    <td><?= htmlspecialchars($row['mobile']) ?></td>
												    <td><?= htmlspecialchars($row['study_center']) ?></td>
													<td>
														<?php 
															if (!empty($row['session_start']) && !empty($row['session_end'])) {
																echo "From: " . htmlspecialchars($row['session_start']) . "<br>To: " . htmlspecialchars($row['session_end']);
															} else {
																echo "Pending";
															}
														?>
													</td>
												    <td>
												        <div class="badge bg-secondary">
												            <?= htmlspecialchars($row['grade'] ?? 'N/A') ?>
												        </div>
												    </td>
													<td>
                                                        <?php if (!empty($row['photo'])): ?>
                                                            <img src="../uploads/students/photos/<?= htmlspecialchars($row['photo']) ?>" alt="Photo" width="80" height="80">
                                                        <?php else: ?>
                                                            No Photo
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($row['id_proof'])): ?>
                                                            <a href="../uploads/students/id_proofs/<?= htmlspecialchars($row['id_proof']) ?>" target="_blank" style="color:black;">View</a>
                                                        <?php else: ?>
                                                            No ID
                                                        <?php endif; ?>
                                                    </td>   
													<td><a href="StudentIdCard.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Download ID Card</a></td>
												   <td class="text-end">
														<?php
														if (!empty($row['session_end']) && date('Y-m-d') >= $row['session_end']) {
															// Course complete - allow generation
															echo '<a href="Marksheet_Creation_Form.php?id=' . $row['id'] . '" class="btn btn-sm btn-primary">Generate</a>';
														} else {
															// Course not completed yet
															echo '<span class="btn btn-sm btn-secondary disabled">Pending</span>';
														}
														?>
													</td>
<td>
    <a href="add_marks.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">
        <i class="fas fa-edit"></i> Add Marks
    </a>
</td>
													<td><a href="certificate.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">View Marksheet</a></td>
													<td><a href="org_certificate.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">View Certifacate</a></td>

												    <td class="text-end">
													    <!-- Edit -->
													    <a href="edit_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-2" title="Edit">
													        <i class="fas fa-edit"></i>
													    </a>

													    <!-- Delete -->
													    <a href="delete_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" 
													       title="Delete" onclick="return confirm('Are you sure to delete this student?');">
													        <i class="fas fa-trash-alt"></i>
													    </a>
													</td>
												</tr>
												<?php
												    endwhile;
												else:
												?>
												<tr><td colspan="10" style="text-align: center;">No students found.</td></tr>
												<?php endif; ?>
												</tbody>

									</table> 
								</div>
							</div>
							<!-- <div style="margin: 20px 0;">
								<button onclick="printAllData()">🖨️ Print Student List</button>
							</div> -->


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
    <script src="js/admin-standard.js"></script>
	<script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
	<!-- <script>
		function printAllData() {
			const original = document.body.innerHTML;
			const printArea = document.getElementById("print-section").innerHTML;

			document.body.innerHTML = printArea;
			window.print();
			document.body.innerHTML = original;

			// Reload for full JS functionality
			location.reload();
		}
	</script> -->
</body>
</html>