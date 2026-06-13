<?php
// Database connection
include 'conn.php';

// Fetch all centers
$sql = "SELECT * FROM center_details";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>	
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="" >
	<meta name="robots" content="" >

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Page Title Here -->
	<title>Center Details : </title>

<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png" >
	<link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link rel="stylesheet" href="vendor/jquery-nice-select/css/nice-select.css">
	<!--swiper-slider-->
	
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
				<!-- Row -->
				<div class="row">
					<div class="col-xl-12">
                        <!-- Row -->
						<div class="row">
                            <!--column-->
							<div class="col-xl-12">
								<div class="page-title flex-wrap">
									<div class="input-group search-area mb-md-0 mb-3">
										<input type="text" class="form-control" placeholder="Search here...">
										<span class="input-group-text"><a href="javascript:void(0)">
											<svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M17.5605 15.4395L13.7527 11.6317C14.5395 10.446 15 9.02625 15 7.5C15 3.3645 11.6355 0 7.5 0C3.3645 0 0 3.3645 0 7.5C0 11.6355 3.3645 15 7.5 15C9.02625 15 10.446 14.5395 11.6317 13.7527L15.4395 17.5605C16.0245 18.1462 16.9755 18.1462 17.5605 17.5605C18.1462 16.9747 18.1462 16.0252 17.5605 15.4395V15.4395ZM2.25 7.5C2.25 4.605 4.605 2.25 7.5 2.25C10.395 2.25 12.75 4.605 12.75 7.5C12.75 10.395 10.395 12.75 7.5 12.75C4.605 12.75 2.25 10.395 2.25 7.5V7.5Z" fill="#01A3FF"/>
											</svg>
										</a>
										</span>
									</div>
									<div>
										<!-- <select class="default-select me-3" aria-label="Default">
											<option selected>Newest</option>
											<option value="1">Oldest</option>
											<option value="2">Recent</option>
										</select> -->
										<button type="button" class="btn btn-primary">
										 <a href="create-center.php"> + Create Center</a>
										</button>
									</div>
								</div>
							</div>
                            <!--/column-->
                            <!--column-->
                            <div class="col-xl-12">
                                 <!-- Row -->
                                <div class="row">
                                    <!--column-->
                                     <?php
									    // Check if any records exist
									      	$sql = "SELECT * FROM center_details";
											$result = $conn->query($sql);

											if ($result->num_rows > 0) {
												while ($row = $result->fetch_assoc()) {
											?>
									        <div class="col-xl-3 col-lg-4 col-sm-6"> <!-- Single card column -->
									            <div class="card contact_list text-center"> <!-- Single card -->
									                <div class="card-body">
									                    <div class="user-content">
									                        <div class="user-info">
									                            <div class="user-img">
 																	<img src="uploads/<?php echo $row['photo']; ?>" alt="" class="avatar avatar-xl"><!-- Placeholder image -->
									                            </div>
									                            <div class="user-details">
									                                <h4 class="user-name mb-0"><?php echo htmlspecialchars($row['center_name']); ?></h4> <!-- Center Name -->
									                                <p><?php echo htmlspecialchars($row['email']); ?></p> <!-- Email -->
									                            </div>
									                        </div>
									                        <div class="dropdown">
									                            <a href="javascript:void(0);" class="btn sharp btn-light" data-bs-toggle="dropdown" aria-expanded="false">
									                                <svg width="24" height="6" viewBox="0 0 24 6" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M12.0012 0.359985C11.6543 0.359985 11.3109 0.428302 10.9904 0.561035C10.67 0.693767 10.3788 0.888317 10.1335 1.13358C9.88829 1.37883 9.69374 1.67 9.56101 1.99044C9.42828 2.31089 9.35996 2.65434 9.35996 3.00119C9.35996 3.34803 9.42828 3.69148 9.56101 4.01193C9.69374 4.33237 9.88829 4.62354 10.1335 4.8688C10.3788 5.11405 10.67 5.3086 10.9904 5.44134C11.3109 5.57407 11.6543 5.64239 12.0012 5.64239C12.7017 5.64223 13.3734 5.36381 13.8686 4.86837C14.3638 4.37294 14.6419 3.70108 14.6418 3.00059C14.6416 2.3001 14.3632 1.62836 13.8677 1.13315C13.3723 0.637942 12.7004 0.359826 12 0.359985H12.0012ZM3.60116 0.359985C3.25431 0.359985 2.91086 0.428302 2.59042 0.561035C2.26997 0.693767 1.97881 0.888317 1.73355 1.13358C1.48829 1.37883 1.29374 1.67 1.16101 1.99044C1.02828 2.31089 0.959961 2.65434 0.959961 3.00119C0.959961 3.34803 1.02828 3.69148 1.16101 4.01193C1.29374 4.33237 1.48829 4.62354 1.73355 4.8688C1.97881 5.11405 2.26997 5.3086 2.59042 5.44134C2.91086 5.57407 3.25431 5.64239 3.60116 5.64239C4.30165 5.64223 4.97339 5.36381 5.4686 4.86837C5.9638 4.37294 6.24192 3.70108 6.24176 3.00059C6.2416 2.3001 5.96318 1.62836 5.46775 1.13315C4.97231 0.637942 4.30045 0.359826 3.59996 0.359985H3.60116ZM20.4012 0.359985C20.0543 0.359985 19.7109 0.428302 19.3904 0.561035C19.07 0.693767 18.7788 0.888317 18.5336 1.13358C18.2883 1.37883 18.0937 1.67 17.961 1.99044C17.8283 2.31089 17.76 2.65434 17.76 3.00119C17.76 3.34803 17.8283 3.69148 17.961 4.01193C18.0937 4.33237 18.2883 4.62354 18.5336 4.8688C18.7788 5.11405 19.07 5.3086 19.3904 5.44134C19.7109 5.57407 20.0543 5.64239 20.4012 5.64239C21.1017 5.64223 21.7734 5.36381 22.2686 4.86837C22.7638 4.37294 23.0419 3.70108 23.0418 3.00059C23.0416 2.3001 22.7632 1.62836 22.2677 1.13315C21.7723 0.637942 21.1005 0.359826 20.4 0.359985H20.4012Z" fill="#A098AE"/>
															</svg>
                           	 </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="javascript:void(0);" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-danger btn-sm">Delete</a>
                                <a class="dropdown-item" href="edit_center.php?id=<?php echo $row['id']; ?>">Edit</a> 
                                <a class="dropdown-item" href="center/index.php?id=<?php echo $row['id']; ?>">View</a> <!-- Edit button -->
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-3">
                            <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" class="btn btn-primary btn-sm w-50 me-2">
                                <?php echo htmlspecialchars($row['phone']); ?> <!-- Phone -->
                            </a>
                            <a class="btn btn-light btn-sm w-50">
                                <i class="fa-sharp fa-envelope me-2"></i><?php echo htmlspecialchars($row['center_code']); ?> <!-- District -->
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
        }
    } else {
        echo "<p class='text-center'>No centers found.</p>";
    }
    ?>
</div>
</div>
                                  
							</div>
                           
                        	<!--/Row -->
						</div>
					
						<!-- <div class="table-pagenation teach">
							<small>Showing <span>1-5</span>from <span>100</span>data</small>
							<nav>
								<ul class="pagination pagination-gutter pagination-primary no-bg">
									<li class="page-item page-indicator">
										<a class="page-link" href="javascript:void(0)">
										<i class="fa-solid fa-chevron-left"></i></a>
									</li>
									<li class="page-item "><a class="page-link" href="javascript:void(0)">1</a>
									</li>
									<li class="page-item active"><a class="page-link" href="javascript:void(0)">2</a></li>
									<li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
									<li class="page-item page-indicator">
										<a class="page-link" href="javascript:void(0)">
										<i class="fa-solid fa-chevron-right"></i></a>
									</li>
								</ul>
							</nav>
						</div> -->
					</div>
				</div>
			</div>
		</div>
		
        <!--**********************************
            Content body end
        ***********************************-->

		<?php include 'footer.php'; ?>
        
	</div>
    

		
	
    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	
	
	<!-- Chart piety plugin files -->
	<script src="vendor/jquery-nice-select/js/jquery.nice-select.min.js"></script>
	
	<!--swiper-slider-->
	<script src="vendor/swiper/js/swiper-bundle.min.js"></script>

	<script src="vendor/wow-master/dist/wow.min.js"></script>
	
    <script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
	<script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>
	<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to PHP delete script
            window.location.href = 'delete_center.php?id=' + id;
        }
    });
}
</script>


</body>

</html>