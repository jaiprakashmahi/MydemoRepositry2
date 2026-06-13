<?php
session_start();
include '../conn.php';

// Check if center is logged in
if (!isset($_SESSION['center_logged_in']) || $_SESSION['center_logged_in'] !== true) {
    header("Location: member_login.php");
    exit();
}

$center_id = $_SESSION['center_id'];
$center_name = $_SESSION['center_name'];
$phone = $_SESSION['phone'];

// Initialize variables
$error = '';
$success = '';
$balance = 0;
$transactions = [];
$payment_methods = [];

// Get wallet balance
$stmt = $conn->prepare("SELECT balance FROM center_wallet WHERE center_id = ?");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $balance = $row['balance'];
}
$stmt->close();

// Get payment methods
$stmt = $conn->prepare("SELECT * FROM center_payment_methods WHERE center_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$payment_methods_result = $stmt->get_result();
while ($row = $payment_methods_result->fetch_assoc()) {
    $payment_methods[] = $row;
}
$stmt->close();

// Get transactions
$stmt = $conn->prepare("SELECT * FROM wallet_transactions WHERE center_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$transactions_result = $stmt->get_result();
while ($row = $transactions_result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Handle add money request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_money'])) {
    $amount = floatval($_POST['amount']);
    $payment_method_id = intval($_POST['payment_method']);
    
    if ($amount <= 0) {
        $error = "Please enter a valid amount";
    } elseif ($amount > 100000) {
        $error = "Maximum amount per transaction is ₹1,00,000";
    } elseif (empty($payment_method_id)) {
        $error = "Please select a payment method";
    } else {
        // Get payment method details
        $stmt = $conn->prepare("SELECT * FROM center_payment_methods WHERE id = ? AND center_id = ?");
        $stmt->bind_param("ii", $payment_method_id, $center_id);
        $stmt->execute();
        $pm_result = $stmt->get_result();
        
        if ($pm_result->num_rows > 0) {
            $payment_method = $pm_result->fetch_assoc();
            $transaction_id = 'TXN' . time() . rand(1000, 9999);
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert transaction record
                $stmt = $conn->prepare("INSERT INTO wallet_transactions (center_id, transaction_type, amount, payment_method, transaction_id, status, description) VALUES (?, 'credit', ?, ?, ?, 'pending', ?)");
                $method_type = $payment_method['method_type'];
                $description = "Wallet recharge via " . ucfirst(str_replace('_', ' ', $method_type));
                $stmt->bind_param("idsss", $center_id, $amount, $method_type, $transaction_id, $description);
                $stmt->execute();
                $transaction_insert_id = $conn->insert_id;
                
                // Simulate payment processing (in real application, integrate with payment gateway)
                // For demo, we'll mark as successful immediately
                sleep(1); // Simulate processing delay
                
                // Update transaction as successful
                $stmt = $conn->prepare("UPDATE wallet_transactions SET status = 'success' WHERE id = ?");
                $stmt->bind_param("i", $transaction_insert_id);
                $stmt->execute();
                
                // Update wallet balance
                $stmt = $conn->prepare("INSERT INTO center_wallet (center_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + ?");
                $stmt->bind_param("idd", $center_id, $amount, $amount);
                $stmt->execute();
                
                $conn->commit();
                $success = "₹" . number_format($amount, 2) . " added to your wallet successfully!";
                header("Location: wallet.php?success=" . urlencode($success));
                exit();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Transaction failed: " . $e->getMessage();
            }
        } else {
            $error = "Invalid payment method selected";
        }
    }
}

// Handle add payment method request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_payment_method'])) {
    $method_type = $_POST['method_type'];
    
    // Basic validation
    if ($method_type == 'upi') {
        $upi_id = trim($_POST['upi_id']);
        if (empty($upi_id)) {
            $error = "Please enter a UPI ID";
        } elseif (!preg_match('/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/', $upi_id)) {
            $error = "Please enter a valid UPI ID (e.g., yourname@okicici)";
        } else {
            // Check if UPI ID already exists
            $check_stmt = $conn->prepare("SELECT id FROM center_payment_methods WHERE upi_id = ? AND center_id = ?");
            $check_stmt->bind_param("si", $upi_id, $center_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = "This UPI ID is already registered";
            } else {
                $stmt = $conn->prepare("INSERT INTO center_payment_methods (center_id, method_type, upi_id, is_default) VALUES (?, 'upi', ?, 1)");
                $stmt->bind_param("is", $center_id, $upi_id);
            }
            $check_stmt->close();
        }
    } elseif ($method_type == 'debit_card' || $method_type == 'credit_card') {
        $card_number = str_replace(' ', '', $_POST['card_number']);
        $card_holder_name = trim($_POST['card_holder_name']);
        $expiry_date = $_POST['expiry_date'];
        $cvv = $_POST['cvv'];
        
        // Validate card number length (13-19 digits for most cards)
        if (empty($card_number) || strlen($card_number) < 13 || strlen($card_number) > 19) {
            $error = "Please enter a valid card number (13-19 digits)";
        } elseif (!preg_match('/^[0-9]{13,19}$/', $card_number)) {
            $error = "Card number should contain only digits";
        } elseif (empty($card_holder_name)) {
            $error = "Please enter card holder name";
        } elseif (empty($expiry_date)) {
            $error = "Please enter expiry date";
        } elseif (empty($cvv) || strlen($cvv) < 3 || strlen($cvv) > 4) {
            $error = "Please enter valid CVV (3-4 digits)";
        } else {
            // Check if card is not expired
            $expiry = explode('-', $expiry_date);
            $expiry_year = intval($expiry[0]);
            $expiry_month = intval($expiry[1]);
            $current_year = date('Y');
            $current_month = date('m');
            
            if ($expiry_year < $current_year || ($expiry_year == $current_year && $expiry_month < $current_month)) {
                $error = "Card has expired";
            } else {
                // Mask card number for storage
                $last_four = substr($card_number, -4);
                $first_six = substr($card_number, 0, 6);
                $masked_card = $first_six . str_repeat('*', strlen($card_number) - 10) . $last_four;
                
                // Check if card already exists
                $check_stmt = $conn->prepare("SELECT id FROM center_payment_methods WHERE card_number LIKE ? AND center_id = ?");
                $check_pattern = "%" . $last_four;
                $check_stmt->bind_param("si", $check_pattern, $center_id);
                $check_stmt->execute();
                if ($check_stmt->get_result()->num_rows > 0) {
                    $error = "This card is already registered";
                } else {
                    $stmt = $conn->prepare("INSERT INTO center_payment_methods (center_id, method_type, card_number, card_holder_name, expiry_date, cvv, is_default) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    $stmt->bind_param("isssss", $center_id, $method_type, $masked_card, $card_holder_name, $expiry_date, $cvv);
                }
                $check_stmt->close();
            }
        }
    } else {
        $error = "Invalid payment method type";
    }
    
    if (empty($error) && isset($stmt)) {
        if ($stmt->execute()) {
            // Remove default flag from other payment methods
            $update_stmt = $conn->prepare("UPDATE center_payment_methods SET is_default = 0 WHERE center_id = ? AND id != ?");
            $update_stmt->bind_param("ii", $center_id, $conn->insert_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            $success = "Payment method added successfully!";
            header("Location: wallet.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Failed to add payment method: " . $conn->error;
        }
    }
}

// Handle delete payment method
if (isset($_GET['delete_payment'])) {
    $payment_id = intval($_GET['delete_payment']);
    $stmt = $conn->prepare("DELETE FROM center_payment_methods WHERE id = ? AND center_id = ?");
    $stmt->bind_param("ii", $payment_id, $center_id);
    if ($stmt->execute()) {
        $success = "Payment method deleted successfully!";
        header("Location: wallet.php?success=" . urlencode($success));
        exit();
    } else {
        $error = "Failed to delete payment method";
    }
}

// Handle set default payment method
if (isset($_GET['set_default'])) {
    $payment_id = intval($_GET['set_default']);
    
    // Start transaction
    $conn->begin_transaction();
    try {
        // Remove default from all
        $stmt = $conn->prepare("UPDATE center_payment_methods SET is_default = 0 WHERE center_id = ?");
        $stmt->bind_param("i", $center_id);
        $stmt->execute();
        
        // Set new default
        $stmt = $conn->prepare("UPDATE center_payment_methods SET is_default = 1 WHERE id = ? AND center_id = ?");
        $stmt->bind_param("ii", $payment_id, $center_id);
        $stmt->execute();
        
        $conn->commit();
        $success = "Default payment method updated successfully!";
        header("Location: wallet.php?success=" . urlencode($success));
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to update default payment method";
    }
}

// Get success/error messages from URL
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

include 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mt-4 mb-4">Wallet</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Wallet Balance Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-wallet"></i> Wallet Balance</h5>
                    <span class="badge bg-light text-primary">Center: <?php echo htmlspecialchars($center_name); ?></span>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-4 text-success">₹<?php echo number_format($balance, 2); ?></h1>
                    <p class="text-muted">Available Balance</p>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addMoneyModal">
                        <i class="fas fa-plus-circle"></i> Add Money
                    </button>
                </div>
            </div>
            
            <!-- Payment Methods Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-credit-card"></i> Payment Methods</h5>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                        <i class="fas fa-plus"></i> Add New
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (empty($payment_methods)): ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No payment methods added yet. Add a payment method to recharge your wallet.
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($payment_methods as $method): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border <?php echo $method['is_default'] ? 'border-primary' : ''; ?>">
                                        <div class="card-body">
                                            <?php if ($method['method_type'] == 'upi'): ?>
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-primary text-white rounded-circle p-2 me-2">
                                                        <i class="fas fa-mobile-alt"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">UPI</h6>
                                                        <small class="text-muted">Instant Payment</small>
                                                    </div>
                                                </div>
                                                <p class="mb-2"><strong>ID:</strong> <?php echo htmlspecialchars($method['upi_id']); ?></p>
                                            <?php elseif ($method['method_type'] == 'debit_card'): ?>
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-success text-white rounded-circle p-2 me-2">
                                                        <i class="fas fa-credit-card"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">Debit Card</h6>
                                                        <small class="text-muted">Bank Card</small>
                                                    </div>
                                                </div>
                                                <p class="mb-2"><strong>Card:</strong> <?php echo htmlspecialchars($method['card_number']); ?></p>
                                                <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($method['card_holder_name']); ?></p>
                                                <p class="mb-2"><strong>Expiry:</strong> <?php echo htmlspecialchars($method['expiry_date']); ?></p>
                                            <?php elseif ($method['method_type'] == 'credit_card'): ?>
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-warning text-white rounded-circle p-2 me-2">
                                                        <i class="fas fa-credit-card"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">Credit Card</h6>
                                                        <small class="text-muted">Credit Card</small>
                                                    </div>
                                                </div>
                                                <p class="mb-2"><strong>Card:</strong> <?php echo htmlspecialchars($method['card_number']); ?></p>
                                                <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($method['card_holder_name']); ?></p>
                                                <p class="mb-2"><strong>Expiry:</strong> <?php echo htmlspecialchars($method['expiry_date']); ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="mt-3 d-flex justify-content-between">
                                                <?php if (!$method['is_default']): ?>
                                                    <a href="wallet.php?set_default=<?php echo $method['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-check-circle"></i> Set Default
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Default</span>
                                                <?php endif; ?>
                                                
                                                <a href="wallet.php?delete_payment=<?php echo $method['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this payment method?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Transaction History -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Transaction History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No transactions found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Transaction ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr class="transaction-row <?php echo $transaction['transaction_type'] == 'credit' ? 'transaction-credit' : 'transaction-debit'; ?>">
                                            <td><?php echo date('d-m-Y H:i:s', strtotime($transaction['created_at'])); ?></td>
                                            <td><code><?php echo htmlspecialchars($transaction['transaction_id']); ?></code></td>
                                            <td>
                                                <?php if ($transaction['transaction_type'] == 'credit'): ?>
                                                    <span class="badge bg-success">Credit</span>
                                                <?php elseif ($transaction['transaction_type'] == 'debit'): ?>
                                                    <span class="badge bg-danger">Debit</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Registration</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold <?php echo $transaction['transaction_type'] == 'credit' ? 'text-success' : 'text-danger'; ?>">
                                                ₹<?php echo number_format($transaction['amount'], 2); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $method = $transaction['payment_method'] ?? 'N/A';
                                                echo ucfirst(str_replace('_', ' ', $method));
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($transaction['status'] == 'success'): ?>
                                                    <span class="badge bg-success">Success</span>
                                                <?php elseif ($transaction['status'] == 'failed'): ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Money Modal -->
<div class="modal fade" id="addMoneyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="wallet.php" id="addMoneyForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Money to Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   min="100" max="100000" step="100" required
                                   placeholder="Enter amount">
                        </div>
                        <div class="form-text">Minimum ₹100, Maximum ₹1,00,000</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <?php foreach ($payment_methods as $method): ?>
                                <option value="<?php echo $method['id']; ?>" <?php echo $method['is_default'] ? 'selected' : ''; ?>>
                                    <?php 
                                    $label = ucfirst(str_replace('_', ' ', $method['method_type']));
                                    if ($method['method_type'] == 'upi') {
                                        $label .= " - " . $method['upi_id'];
                                    } elseif ($method['method_type'] == 'debit_card' || $method['method_type'] == 'credit_card') {
                                        $label .= " (" . $method['card_number'] . ")";
                                    }
                                    echo htmlspecialchars($label);
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($payment_methods)): ?>
                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle"></i> No payment methods added. Please add a payment method first.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> For demo purposes only. In production, integrate with a payment gateway.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_money" class="btn btn-primary" 
                            id="addMoneyBtn" <?php echo empty($payment_methods) ? 'disabled' : ''; ?>>
                        <i class="fas fa-lock"></i> Proceed to Pay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="wallet.php" id="paymentMethodForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="method_type" class="form-label">Payment Method Type</label>
                        <select class="form-control" id="method_type" name="method_type" required onchange="showPaymentFields()">
                            <option value="">Select Type</option>
                            <option value="upi">UPI</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>
                    
                    <!-- UPI Fields -->
                    <div id="upi_fields" style="display: none;">
                        <div class="mb-3">
                            <label for="upi_id" class="form-label">UPI ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                <input type="text" class="form-control" id="upi_id" name="upi_id" 
                                       placeholder="yourname@okicici">
                            </div>
                            <div class="form-text">Example: yourname@okhdfcbank, yourname@okicici, yourname@oksbi</div>
                        </div>
                    </div>
                    
                    <!-- Card Fields -->
                    <div id="card_fields" style="display: none;">
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                <input type="text" class="form-control" id="card_number" name="card_number" 
                                       maxlength="19" placeholder="1234 5678 9012 3456"
                                       oninput="formatCardNumber(this)">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="card_holder_name" class="form-label">Card Holder Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="card_holder_name" name="card_holder_name"
                                       placeholder="John Doe">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="month" class="form-control" id="expiry_date" name="expiry_date">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="cvv" name="cvv" 
                                           maxlength="4" placeholder="123" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-shield-alt"></i> Your payment details are securely stored. Never share your CVV with anyone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_payment_method" class="btn btn-primary" id="savePaymentBtn" disabled>
                        <i class="fas fa-save"></i> Save Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showPaymentFields() {
    var methodType = document.getElementById('method_type').value;
    var saveBtn = document.getElementById('savePaymentBtn');
    
    // Hide all fields first
    document.getElementById('upi_fields').style.display = 'none';
    document.getElementById('card_fields').style.display = 'none';
    
    // Clear all inputs
    document.getElementById('upi_id').value = '';
    document.getElementById('card_number').value = '';
    document.getElementById('card_holder_name').value = '';
    document.getElementById('expiry_date').value = '';
    document.getElementById('cvv').value = '';
    
    // Show relevant fields
    if (methodType === 'upi') {
        document.getElementById('upi_fields').style.display = 'block';
        saveBtn.disabled = false;
    } else if (methodType === 'debit_card' || methodType === 'credit_card') {
        document.getElementById('card_fields').style.display = 'block';
        saveBtn.disabled = false;
    } else {
        saveBtn.disabled = true;
    }
}

function formatCardNumber(input) {
    // Remove all non-digits
    var value = input.value.replace(/\D/g, '');
    
    // Add space after every 4 digits
    var formatted = '';
    for (var i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    
    // Update input value
    input.value = formatted;
}

// Validate form before submission
document.getElementById('addMoneyForm')?.addEventListener('submit', function(e) {
    var amount = document.getElementById('amount').value;
    var paymentMethod = document.getElementById('payment_method').value;
    
    if (!amount || amount < 100 || amount > 100000) {
        e.preventDefault();
        alert('Please enter a valid amount between ₹100 and ₹1,00,000');
        return false;
    }
    
    if (!paymentMethod) {
        e.preventDefault();
        alert('Please select a payment method');
        return false;
    }
    
    return true;
});

document.getElementById('paymentMethodForm')?.addEventListener('submit', function(e) {
    var methodType = document.getElementById('method_type').value;
    
    if (methodType === 'upi') {
        var upiId = document.getElementById('upi_id').value;
        var upiRegex = /^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/;
        if (!upiRegex.test(upiId)) {
            e.preventDefault();
            alert('Please enter a valid UPI ID (e.g., yourname@okicici)');
            return false;
        }
    } else if (methodType === 'debit_card' || methodType === 'credit_card') {
        var cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        var cardHolder = document.getElementById('card_holder_name').value;
        var expiryDate = document.getElementById('expiry_date').value;
        var cvv = document.getElementById('cvv').value;
        
        if (cardNumber.length < 13 || cardNumber.length > 19) {
            e.preventDefault();
            alert('Please enter a valid card number (13-19 digits)');
            return false;
        }
        
        if (!cardHolder.trim()) {
            e.preventDefault();
            alert('Please enter card holder name');
            return false;
        }
        
        if (!expiryDate) {
            e.preventDefault();
            alert('Please select expiry date');
            return false;
        }
        
        if (cvv.length < 3 || cvv.length > 4) {
            e.preventDefault();
            alert('Please enter valid CVV (3-4 digits)');
            return false;
        }
    }
    
    return true;
});

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Show success message from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const successMsg = urlParams.get('success');
        // Message is already shown via PHP
    }
});
</script>

<style>
.card {
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
    border: none;
}
.card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}
.display-4 {
    font-weight: 300;
    letter-spacing: -1px;
}
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.02);
}
.payment-method-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.payment-method-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.transaction-row {
    border-left: 4px solid transparent;
}
.transaction-credit {
    border-left-color: #28a745;
}
.transaction-debit {
    border-left-color: #dc3545;
}
.modal-content {
    border-radius: 10px;
}
.input-group-text {
    background-color: #f8f9fa;
}
</style>

<?php include 'footer.php'; ?>