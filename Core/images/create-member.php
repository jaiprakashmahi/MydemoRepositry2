<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
      
    <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="author" content="DexignLab" >
  <meta name="robots" content="" >
 

  <!-- Mobile Specific -->
  <meta name="viewport" content="wnameth=device-wnameth, initial-scale=1">

  <!-- Page Title Here -->
  <title>Create Member</title>

<!-- FAVICONS ICON -->
  <link rel="shortcut icon" type="image/png" href="images/favicon.png" >
  <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
  <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
  
  <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
  
   <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
<form action="insert_member.php" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Member Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-6 col-sm-6">
                          <div class="mb-3">
                                <label for="member-type" class="form-label text-primary">Member Type<span class="required">*</span></label>
                                <!-- First Dropdown -->
                                <select name="Node1" id="node1" class="form-control">
                                    <option value="">Member Type</option>
                                    <option value="State Node">State Nodal </option>
                                    <option value="Zonal Manager">Zonal Manager</option>
                                    <option value="Dristic Co-Ordinator">Dristic Co-Ordinator</option>
                                    <option value="Block Co-Ordinator">Block Co-Ordinator</option>
                                </select>
                            </div>
                            <!-- <div class="mb-3">
                                <label for="" class="form-label text-primary">Center Name<span class="required">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Your Name">
                            </div> -->

                            <div class="mb-3">
                                <label for="" class="form-label text-primary">Member Name<span class="required">*</span></label>
                                <input type="text" class="form-control" name="owner_name" placeholder="Your Name">
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label text-primary">Father Name<span class="required">*</span></label>
                                <input type="text" class="form-control" name="father_name" placeholder="Your Father Name">
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
                                <label for="id_card" class="form-label">Photo<span class="required">*</span></label>
                                <input type="file" class="form-control" name="photo" placeholder="Your Photo">
                            </div>
                        </div>
                        <div class="col-xl-6 col-sm-6">

                            <div class="mb-3">
                                <label for="0" class="form-label">Phone No.<span class="required">*</span></label>
                                <input class="form-control" type="number" name="phone" placeholder="Enter Your Mobile No.">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">State</label>
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
                                <label class="form-label">District</label>
                                <select id="district-dropdown" name="district[]" class="form-control" multiple required>
                                    <option value="">Select District</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="Post_Office" class="form-label">Post Office<span class="required">*</span></label>
                                <input type="text" class="form-control" name="post_office" placeholder="Post Office">
                            </div>

                            <div class="mb-3">
                                <label for="pincode" class="form-label">Pin Code<span class="required">*</span></label>
                                <input type="number" class="form-control" name="pincode" placeholder="Pin Code">
                            </div>
                            <div class="mb-3">
                                <label for="id_card" class="form-label">ID Prof.<span class="required">*</span></label>
                                <input type="file" class="form-control" name="id_card" placeholder="Id Card">
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
  <script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script><!-- Load jQuery (ensure only one version is included) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Bootstrap Datepicker -->
<script>
    $(function () {
        $("#datepicker, #datepicker1, #datepicker2").datepicker({
            autoclose: true,
            todayHighlight: true
        }).datepicker('update', new Date());
    });
</script>

<!-- Image preview (optional) -->
<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                $('#imagePreview').fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function () {
        $("#imageUpload").change(function () {
            readURL(this);
        });

        $('.remove-img').on('click', function () {
            let imageUrl = "images/no-img-avatar.png";
            $('#imagePreview').removeAttr('style').css('background-image', 'url(' + imageUrl + ')');
        });
    });
</script>

<script>
$(document).ready(function () {
    const $district = $('#district-dropdown');

    // Function to re-initialize the district dropdown based on member type
    function updateDistrictBehavior() {
        const type = $('#node1').val();

        // Destroy any existing Select2 instance
        $district.select2('destroy');

        if (type === 'State Node' || type === 'Zonal Manager') {
            $district.prop('multiple', true);
            $district.select2({
                placeholder: 'Select District',
                maximumSelectionLength: 5,
                width: '100%'
            });
        } else {
            $district.prop('multiple', false);

            // Retain only the first selected value, if any
            let firstSelected = $district.find('option:selected').first().val();
            $district.val(firstSelected ? [firstSelected] : []);

            // Re-initialize as single-select
            $district.select2({
                placeholder: 'Select District',
                width: '100%'
            });
        }
    }

    // Trigger when member type changes
    $('#node1').on('change', function () {
        updateDistrictBehavior();
    });

    // Load districts dynamically on state change
    $('#state-dropdown').on('change', function () {
        let stateID = $(this).val();
        if (stateID) {
            $.ajax({
                type: 'POST',
                url: 'fetch_district.php',
                data: { state_id: stateID },
                success: function (html) {
                    $district.html(html);
                    updateDistrictBehavior(); // reinitialize Select2 with correct settings
                },
                error: function () {
                    alert('Error loading districts');
                }
            });
        } else {
            $district.html('<option value="">Select District</option>');
            updateDistrictBehavior(); // reset behavior if state is cleared
        }
    });

    // Initialize Select2 on page load
    $district.select2({
        placeholder: 'Select District',
        width: '100%'
    });

    // Also initialize the correct behavior based on any pre-selected member type
    updateDistrictBehavior();
});
</script>

</body>

</html>