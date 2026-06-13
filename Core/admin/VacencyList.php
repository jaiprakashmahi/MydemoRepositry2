<?php
include 'conn.php';
$query = "
SELECT v.*, s.name AS state_name, d.name AS district_name
FROM vacancy v
LEFT JOIN states s ON v.state_id = s.id
LEFT JOIN districts d ON v.district_id = d.id
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    	<title>All Vacency - Welcome To Sharnay Institute Dashboard</title>

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
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
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
										<a href="CreateVacency.php"> + Create New Vacency </a>
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
												
												<th>Adv.Id</th>
												<th>Post Name</th>
												<th>State</th>
												<th>District</th>
												<th>Eligiblity</th>
												<th>Experience</th>
												<th>Total Post</th>
												<th>Statr Date</th>
												<th>End date</th>
												<th>Document</th>
												<th class="text-end">Action</th>
											</tr>
										</thead>
										<tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input" name="selected[]" value="<?= $row['id']; ?>">
                                                    </td>
                                                    <td><?= htmlspecialchars($row['adv_id']) ?></td>
                                                    <td><?= htmlspecialchars($row['post']) ?></td>
                                                    <td><?= htmlspecialchars($row['state_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['district_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['eligibility']) ?></td>
                                                    <td><?= htmlspecialchars($row['experience']) ?></td>
                                                    <td><?= htmlspecialchars($row['no_of_post']) ?></td>
                                                    <td><?= htmlspecialchars($row['start_date']) ?></td>
                                                    <td><?= htmlspecialchars($row['end_date']) ?></td>
                                                    <td>
                                                        <?php if (!empty($row['document'])): ?>
                                                            <a href="uploads/<?= htmlspecialchars($row['document']) ?>" target="_blank" style="color:#044604; text-decoration:underline;">View</a>
                                                        <?php else: ?>
                                                            No Document
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="EditVacancy.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="DeleteVacancy.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure to delete this vacancy?');">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="12" style="text-align: center;">No Vacancies found.</td></tr>
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

	
</body>
</html>