<?php
include '../conn.php';

$id = $_POST['id'] ?? 0;

$query = "SELECT mi.*, mt.type_name, mwa.state_id, mwa.district_ids, mwa.block_ids
          FROM member_information mi
          LEFT JOIN member_types mt ON mi.member_type_id = mt.id
          LEFT JOIN member_working_area mwa ON mi.id = mwa.member_id
          WHERE mi.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if($member) {
    // Get working area details
    $working_area = '';
    if($member['state_id']) {
        $state_query = "SELECT name FROM states WHERE id = {$member['state_id']}";
        $state_result = mysqli_query($conn, $state_query);
        $state = mysqli_fetch_assoc($state_result);
        $working_area .= "<strong>State:</strong> " . $state['name'] . "<br>";
    }
    
    if($member['district_ids']) {
        $district_ids = explode(',', $member['district_ids']);
        $district_names = [];
        foreach($district_ids as $district_id) {
            $district_query = "SELECT name FROM districts WHERE id = $district_id";
            $district_result = mysqli_query($conn, $district_query);
            if($district = mysqli_fetch_assoc($district_result)) {
                $district_names[] = $district['name'];
            }
        }
        if(!empty($district_names)) {
            $working_area .= "<strong>Districts:</strong> " . implode(', ', $district_names) . "<br>";
        }
    }
    
    if($member['block_ids']) {
        $block_ids = explode(',', $member['block_ids']);
        $block_names = [];
        foreach($block_ids as $block_id) {
            $block_query = "SELECT name FROM blocks WHERE id = $block_id";
            $block_result = mysqli_query($conn, $block_query);
            if($block = mysqli_fetch_assoc($block_result)) {
                $block_names[] = $block['name'];
            }
        }
        if(!empty($block_names)) {
            $working_area .= "<strong>Blocks:</strong> " . implode(', ', $block_names) . "<br>";
        }
    }
    ?>
    <div class="row">
        <div class="col-md-4 text-center mb-3">
            <?php if(!empty($member['passport_photo'])): ?>
                <img src="<?php echo $member['passport_photo']; ?>" class="img-fluid rounded" style="max-height: 200px;">
            <?php else: ?>
                <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="fas fa-user fa-4x"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <h5 class="border-bottom pb-2">Personal Information</h5>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Name:</strong> <?php echo $member['applicant_name']; ?></div>
                <div class="col-md-6"><strong>Father:</strong> <?php echo $member['father_name']; ?></div>
                <div class="col-md-6"><strong>Mother:</strong> <?php echo $member['mother_name']; ?></div>
                <div class="col-md-6"><strong>Mobile:</strong> <?php echo $member['mobile_number']; ?></div>
                <div class="col-md-6"><strong>Email:</strong> <?php echo $member['email_id']; ?></div>
                <div class="col-md-6"><strong>DOB:</strong> <?php echo $member['date_of_birth']; ?></div>
            </div>
            
            <h5 class="border-bottom pb-2 mt-4">Member Details</h5>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Member Type:</strong> <?php echo $member['type_name']; ?></div>
                <div class="col-md-6"><strong>Post Applied:</strong> <?php echo $member['post_applied']; ?></div>
                <div class="col-md-6"><strong>Status:</strong> 
                    <span class="badge <?php echo $member['member_status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $member['member_status']; ?>
                    </span>
                </div>
                <div class="col-md-6"><strong>Payment Status:</strong> <?php echo $member['payment_status']; ?></div>
            </div>
            
            <h5 class="border-bottom pb-2 mt-4">Working Area</h5>
            <div class="mb-3"><?php echo $working_area ?: 'No working area assigned'; ?></div>
            
            <h5 class="border-bottom pb-2 mt-4">Bank Details</h5>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Bank:</strong> <?php echo $member['bank_name']; ?></div>
                <div class="col-md-6"><strong>Branch:</strong> <?php echo $member['branch_name']; ?></div>
                <div class="col-md-6"><strong>Account No:</strong> <?php echo $member['account_number']; ?></div>
                <div class="col-md-6"><strong>IFSC:</strong> <?php echo $member['ifsc_code']; ?></div>
            </div>
        </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">Member not found!</div>';
}
?>