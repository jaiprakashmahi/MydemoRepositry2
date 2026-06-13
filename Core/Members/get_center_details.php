<?php
include '../conn.php';

if (isset($_POST['center_id'])) {
    $center_id = intval($_POST['center_id']);
    
    $query = "SELECT * FROM center_details WHERE id = $center_id";
    $result = mysqli_query($conn, $query);
    $center = mysqli_fetch_assoc($result);
    
    if ($center) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Center Code</th>
                        <td><?php echo $center['center_code']; ?></td>
                    </tr>
                    <tr>
                        <th>Center Name</th>
                        <td><?php echo htmlspecialchars($center['center_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Owner Name</th>
                        <td><?php echo htmlspecialchars($center['owner_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo $center['email']; ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo $center['phone']; ?></td>
                    </tr>
                    <tr>
                        <th>State</th>
                        <td><?php echo htmlspecialchars($center['state']); ?></td>
                    </tr>
                    <tr>
                        <th>District</th>
                        <td><?php echo htmlspecialchars($center['district']); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Block</th>
                        <td><?php echo htmlspecialchars($center['block'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Pincode</th>
                        <td><?php echo $center['pincode']; ?></td>
                    </tr>
                    <tr>
                        <th>Registration Amount</th>
                        <td>₹<?php echo $center['reg_amount']; ?></td>
                    </tr>
                    <tr>
                        <th>Payment Mode</th>
                        <td><?php echo $center['payment_mode']; ?></td>
                    </tr>
                    <tr>
                        <th>Payment Status</th>
                        <td><?php echo $center['payment_status']; ?></td>
                    </tr>
                    <tr>
                        <th>Center Status</th>
                        <td><?php echo $center['center_status']; ?></td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td><?php echo $center['username']; ?></td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <td><?php echo $center['date_of_create']; ?></td>
                    </tr>
                </table>
            </div>
            <?php if ($center['photo']): ?>
            <div class="col-12">
                <hr>
                <h6>Center Image:</h6>
                <img src="../<?php echo $center['photo']; ?>" alt="Center Image" style="max-width: 200px; border-radius: 10px;">
            </div>
            <?php endif; ?>
            <?php if ($center['id_proof']): ?>
            <div class="col-12 mt-3">
                <h6>ID Proof:</h6>
                <a href="../<?php echo $center['id_proof']; ?>" target="_blank" class="btn btn-sm btn-primary">View ID Proof</a>
            </div>
            <?php endif; ?>
        </div>
        <?php
    } else {
        echo '<p class="text-danger">Center not found</p>';
    }
}
?>