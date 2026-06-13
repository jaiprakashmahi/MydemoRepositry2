<?php
include '../conn.php';

$id = $_POST['id'] ?? 0;

$query = "SELECT mi.*, mwa.state_id, mwa.district_ids, mwa.block_ids
          FROM member_information mi
          LEFT JOIN member_working_area mwa ON mi.id = mwa.member_id
          WHERE mi.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if($member) {
    ?>
    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label required">Applicant Name</label>
            <input type="text" class="form-control" name="applicant_name" value="<?php echo $member['applicant_name']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label required">Father's Name</label>
            <input type="text" class="form-control" name="father_name" value="<?php echo $member['father_name']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label required">Mobile Number</label>
            <input type="tel" class="form-control" name="mobile_number" value="<?php echo $member['mobile_number']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label required">Email ID</label>
            <input type="email" class="form-control" name="email_id" value="<?php echo $member['email_id']; ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label required">Member Type</label>
            <select class="form-control" name="member_type_id" required>
                <option value="">Select Member Type</option>
                <?php
                $type_query = "SELECT * FROM member_types ORDER BY level";
                $type_result = mysqli_query($conn, $type_query);
                while($type = mysqli_fetch_assoc($type_result)) {
                    $selected = $type['id'] == $member['member_type_id'] ? 'selected' : '';
                    echo "<option value='{$type['id']}' {$selected}>{$type['type_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label required">Member Status</label>
            <select class="form-control" name="member_status" required>
                <option value="Active" <?php echo $member['member_status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $member['member_status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label required">Payment Status</label>
            <select class="form-control" name="payment_status" required>
                <option value="Paid" <?php echo $member['payment_status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="Pending" <?php echo $member['payment_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Partial" <?php echo $member['payment_status'] == 'Partial' ? 'selected' : ''; ?>>Partial</option>
            </select>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Current Photo</label>
            <?php if(!empty($member['passport_photo'])): ?>
                <div class="mb-2">
                    <img src="<?php echo $member['passport_photo']; ?>" class="img-thumbnail" style="max-height: 100px;">
                </div>
            <?php endif; ?>
            <label class="form-label">Update Photo (Optional)</label>
            <input type="file" class="form-control" name="passport_photo" accept="image/*">
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Change Password (Optional)</label>
            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
        </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">Member not found!</div>';
}
?>
