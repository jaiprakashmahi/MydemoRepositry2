<?php
include 'conn.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$query = "SELECT * FROM members WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Member not found!";
    exit;
}

$member = $result->fetch_assoc();
$selected_districts = explode(',', $member['district']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $center_name = $_POST['name'];
    $owner_name = $_POST['owner_name'];
    $email = $_POST['email'];
    $created_at = $_POST['date'];
    $phone = $_POST['phone'];
    $state = $_POST['state'];
    $districts = isset($_POST['district']) ? implode(',', $_POST['district']) : '';
    $post_office = $_POST['post_office'];
    $pincode = $_POST['pincode'];
    $member_type = $_POST['Node1'];

    $photo = $_FILES['photo']['name'] ?: $member['photo'];
    $id_card = $_FILES['id_card']['name'] ?: $member['id_card'];
    $username = $_POST['username'];
    $password = $_POST['password'];


    if ($_FILES['photo']['tmp_name']) {
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo);
    }
    if ($_FILES['id_card']['tmp_name']) {
        move_uploaded_file($_FILES['id_card']['tmp_name'], "uploads/" . $id_card);
    }

   if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $update_sql = "UPDATE members SET 
        member_type=?, center_name=?, owner_name=?, email=?, created_at=?, 
        photo=?, phone=?, state=?, district=?, post_office=?, pincode=?, id_card=?, username=?, password=? 
        WHERE id=?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssssssssssi", 
        $member_type, $center_name, $owner_name, $email, $created_at, 
        $photo, $phone, $state, $districts, $post_office, $pincode, $id_card, $username, $hashedPassword, $id
    );
} else {
    // If no password entered, do not update password
    $update_sql = "UPDATE members SET 
        member_type=?, center_name=?, owner_name=?, email=?, created_at=?, 
        photo=?, phone=?, state=?, district=?, post_office=?, pincode=?, id_card=?, username=? 
        WHERE id=?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssssssssssi", 
        $member_type, $center_name, $owner_name, $email, $created_at, 
        $photo, $phone, $state, $districts, $post_office, $pincode, $id_card, $username, $id
    );
}

if ($stmt->execute()) {
    header("Location: member.php");
    exit;
} else {
    echo "Update failed: " . $stmt->error;
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
                <div class="card">
                    <div class="card-header"><h5>Edit Member Details</h5></div>
                    <div class="card-body row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Member Type</label>
                                <select name="Node1" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="Node" <?= $member['member_type'] == 'Node' ? 'selected' : '' ?>>State Nodal</option>
                                    <option value="Zonal Manager" <?= $member['member_type'] == 'Zonal Manager' ? 'selected' : '' ?>>Zonal Manager</option>
                                    <option value="Dristic Co-Ordinator" <?= $member['member_type'] == 'Dristic Co-Ordinator' ? 'selected' : '' ?>>Dristic Co-Ordinator</option>
                                    <option value="Block Co-Ordinator" <?= $member['member_type'] == 'Block Co-Ordinator' ? 'selected' : '' ?>>Block Co-Ordinator</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Center Name</label>
                                <input type="text" class="form-control" name="name" value="<?= $member['center_name'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Center Owner Name</label>
                                <input type="text" class="form-control" name="owner_name" value="<?= $member['owner_name'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= $member['email'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date of Create</label>
                                <input type="date" class="form-control" name="date" value="<?= $member['created_at'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Photo</label>
                                <input type="file" class="form-control" name="photo">
                                <small>Current: <?= $member['photo'] ?></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone No.</label>
                                <input type="number" class="form-control" name="phone" value="<?= $member['phone'] ?>">
                            </div>
                            <!-- State Dropdown -->
                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <select id="state-dropdown" name="state" class="form-control" required>
                                    <option value="">Select State</option>
                                    <?php
                                    $stateQuery = "SELECT id, name FROM states";
                                    $stateResult = mysqli_query($conn, $stateQuery);
                                    while ($row = mysqli_fetch_assoc($stateResult)) {
                                        $selected = $row['id'] == $member['state'] ? 'selected' : '';
                                        echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- District Dropdown (Multi-select) -->
                            <div class="mb-3">
                                <label class="form-label">District</label>
                                <select id="district-dropdown" name="district[]" class="form-control" multiple required>
                                    <?php
                                    if (!empty($member['state'])) {
                                        $distQuery = "SELECT id, name FROM districts WHERE state_id = " . intval($member['state']);
                                        $distResult = mysqli_query($conn, $distQuery);
                                        while ($row = mysqli_fetch_assoc($distResult)) {
                                            $selected = in_array($row['id'], $selected_districts) ? 'selected' : '';
                                            echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Post Office</label>
                                <input type="text" class="form-control" name="post_office" value="<?= $member['post_office'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="number" class="form-control" name="pincode" value="<?= $member['pincode'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ID Proof</label>
                                <input type="file" class="form-control" name="id_card">
                                <small>Current: <?= $member['id_card'] ?></small>
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
                                <label for="username" class="form-label text-primary">Username</label>
                                <input type="text" class="form-control" name="username" value="<?= $member['username'] ?>">
                            </div>
                        </div>
                        <div class="col-xl-6 col-sm-6">
                            <div class="mb-3">
                                <label for="password" class="form-label text-primary">Update Password</label>
                                <input type="password" class="form-control" name="password" >
                            </div>
                        </div>
                    </div>
                  
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Update Member</button>
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