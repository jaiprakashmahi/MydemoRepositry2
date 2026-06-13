<?php
session_start();
include 'conn.php';

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$action    = $_GET['action'] ?? '';
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

if ($action === 'delete' && $module_id > 0) {
    $stmt = $conn->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->bind_param("i", $module_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Module deleted successfully";
    header("Location: " . $_SERVER['PHP_SELF'] . "?course_id=" . $course_id);
    exit;
}

if (isset($_POST['add_modules'])) {
    $course_id = (int)$_POST['course_id'];

    if (!empty($_POST['module_name']) && is_array($_POST['module_name'])) {
        foreach ($_POST['module_name'] as $i => $name) {
            $module_name = trim($name);
            if ($module_name == '') continue;

            $module_code = trim($_POST['module_code'][$i] ?? '');
            $description = trim($_POST['description'][$i] ?? '');
            $written     = (int)($_POST['max_written_marks'][$i] ?? 0);
            $practical   = (int)($_POST['max_practical_marks'][$i] ?? 0);
            $hours       = (int)($_POST['duration_hours'][$i] ?? 0);
            $status      = $_POST['status'][$i] ?? 'active';

            $stmt = $conn->prepare("
                INSERT INTO modules
                (course_id, module_code, module_name, description, max_written_marks, max_practical_marks, duration_hours, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isssiiis",
                $course_id,
                $module_code,
                $module_name,
                $description,
                $written,
                $practical,
                $hours,
                $status
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    $_SESSION['success'] = "Modules added successfully";
    header("Location: " . $_SERVER['PHP_SELF'] . "?course_id=" . $course_id);
    exit;
}

if (isset($_POST['update_module'])) {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $module_id = (int)($_POST['module_id'] ?? 0);

    $module_code = trim($_POST['module_code'] ?? '');
    $module_name = trim($_POST['module_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $written     = (int)($_POST['max_written_marks'] ?? 0);
    $practical   = (int)($_POST['max_practical_marks'] ?? 0);
    $hours       = (int)($_POST['duration_hours'] ?? 0);
    $status      = $_POST['status'] ?? 'active';

    $stmt = $conn->prepare("
        UPDATE modules SET
        module_code = ?,
        module_name = ?,
        description = ?,
        max_written_marks = ?,
        max_practical_marks = ?,
        duration_hours = ?,
        status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sssiiisi",
        $module_code,
        $module_name,
        $description,
        $written,
        $practical,
        $hours,
        $status,
        $module_id
    );

    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Module updated successfully";
    header("Location: " . $_SERVER['PHP_SELF'] . "?course_id=" . $course_id);
    exit;
}

$courses = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");

$modules = [];
if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY order_number ASC, id DESC");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $modules[] = $row;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Module Management - Sharnay Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
        }

        .page-wrapper-custom {
            padding: 24px;
        }

        .page-heading-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 18px;
            padding: 22px 26px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.22);
        }

        .page-heading-card h4 {
            color: #fff;
            margin: 0;
            font-weight: 700;
        }

        .page-heading-card p {
            margin: 6px 0 0;
            opacity: .9;
        }

        .content-card {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 8px 25px rgba(16, 24, 40, 0.06);
            border: 1px solid #edf0f5;
            margin-bottom: 22px;
        }

        .course-select-box label {
            font-size: 14px;
            font-weight: 700;
            color: #344054;
            margin-bottom: 8px;
        }

        .course-select-box .form-select {
            height: 48px;
            border-radius: 12px;
            border: 1px solid #d0d5dd;
        }

        .table {
            vertical-align: middle;
        }

        .table thead th {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .3px;
            white-space: nowrap;
        }

        .table tbody td {
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .module-input-box {
            background: #f8f9fc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 18px;
        }

        .module-input-box-title {
            font-size: 15px;
            font-weight: 700;
            color: #344054;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .module-input-box .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #344054;
            margin-bottom: 7px;
        }

        .module-input-box .form-control,
        .module-input-box .form-select,
        .edit-form-box .form-control,
        .edit-form-box .form-select {
            height: 44px;
            border-radius: 10px;
            border: 1px solid #d0d5dd;
            box-shadow: none;
            font-size: 14px;
        }

        .module-input-box textarea.form-control,
        .edit-form-box textarea.form-control {
            height: 92px;
            resize: none;
        }

        .module-input-box .form-control:focus,
        .module-input-box .form-select:focus,
        .edit-form-box .form-control:focus,
        .edit-form-box .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, .10);
        }

        .input-gap {
            margin-bottom: 16px;
        }

        .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border: 0;
            padding: 18px 24px;
        }

        .modal-header h5 {
            color: #fff;
            margin: 0;
            font-weight: 700;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #eef2f7;
        }

        .btn-rounded {
            border-radius: 10px;
            padding: 9px 18px;
            font-weight: 600;
        }

        .btn-add-more {
            background: #eef2ff;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
        }

        .btn-add-more:hover {
            background: #e0e7ff;
            color: #3730a3;
        }

        .empty-state {
            text-align: center;
            padding: 36px 15px;
            color: #667085;
        }

        .empty-state h5 {
            color: #344054;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .page-wrapper-custom {
                padding: 14px;
            }

            .page-heading-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
<div id="main-wrapper">

    <?php include 'menu.php'; ?>

    <div class="content-body">
        <div class="container-fluid page-wrapper-custom">

            <div class="page-heading-card">
                <h4>📘 Module Management</h4>
                <p>Select a course and manage its modules, marks, duration, and status.</p>
            </div>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php } ?>

            <div class="content-card">
                <form method="get" class="course-select-box">
                    <label>Select Course</label>
                    <select name="course_id" class="form-select" onchange="this.form.submit()" required>
                        <option value="">-- Select Course --</option>
                        <?php while ($c = $courses->fetch_assoc()) { ?>
                            <option value="<?= $c['id'] ?>" <?= $course_id == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['course_name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
            </div>

            <?php if ($course_id > 0) { ?>

                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1">Module List</h5>
                            <small class="text-muted">All modules added under selected course.</small>
                        </div>

                        <button class="btn btn-success btn-rounded" data-bs-toggle="modal" data-bs-target="#addModal">
                            ➕ Add Modules
                        </button>
                    </div>

                    <?php if (count($modules) > 0) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped bg-white">
                                <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Written</th>
                                    <th>Practical</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                    <th width="150">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($modules as $i => $m) { ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($m['module_code']) ?></td>
                                        <td><?= htmlspecialchars($m['module_name']) ?></td>
                                        <td style="max-width:190px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?= htmlspecialchars($m['description']) ?>
                                        </td>
                                        <td><?= (int)$m['max_written_marks'] ?></td>
                                        <td><?= (int)$m['max_practical_marks'] ?></td>
                                        <td><?= (int)$m['duration_hours'] ?></td>
                                        <td>
                                            <span class="status-badge <?= $m['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                                <?= htmlspecialchars($m['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= $m['id'] ?>">
                                                Edit
                                            </button>

                                            <a href="<?= $_SERVER['PHP_SELF'] ?>?action=delete&module_id=<?= $m['id'] ?>&course_id=<?= $course_id ?>"
                                               onclick="return confirm('Delete module?')"
                                               class="btn btn-danger btn-sm">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="edit<?= $m['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form method="post">
                                                    <div class="modal-header">
                                                        <h5>Edit Module</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body edit-form-box">
                                                        <input type="hidden" name="update_module" value="1">
                                                        <input type="hidden" name="module_id" value="<?= $m['id'] ?>">
                                                        <input type="hidden" name="course_id" value="<?= $course_id ?>">

                                                        <div class="module-input-box">
                                                            <div class="row">
                                                                <div class="col-md-4 input-gap">
                                                                    <label class="form-label">Module Code</label>
                                                                    <input class="form-control" name="module_code" value="<?= htmlspecialchars($m['module_code']) ?>" placeholder="Enter module code">
                                                                </div>

                                                                <div class="col-md-8 input-gap">
                                                                    <label class="form-label">Module Name</label>
                                                                    <input class="form-control" name="module_name" value="<?= htmlspecialchars($m['module_name']) ?>" placeholder="Enter module name" required>
                                                                </div>

                                                                <div class="col-md-12 input-gap">
                                                                    <label class="form-label">Description</label>
                                                                    <textarea class="form-control" name="description" placeholder="Enter description"><?= htmlspecialchars($m['description']) ?></textarea>
                                                                </div>

                                                                <div class="col-md-4 input-gap">
                                                                    <label class="form-label">Written Marks</label>
                                                                    <input type="number" class="form-control" name="max_written_marks" value="<?= (int)$m['max_written_marks'] ?>">
                                                                </div>

                                                                <div class="col-md-4 input-gap">
                                                                    <label class="form-label">Practical Marks</label>
                                                                    <input type="number" class="form-control" name="max_practical_marks" value="<?= (int)$m['max_practical_marks'] ?>">
                                                                </div>

                                                                <div class="col-md-4 input-gap">
                                                                    <label class="form-label">Duration Hours</label>
                                                                    <input type="number" class="form-control" name="duration_hours" value="<?= (int)$m['duration_hours'] ?>" placeholder="Enter hours">
                                                                </div>

                                                                <div class="col-md-12 input-gap">
                                                                    <label class="form-label">Status</label>
                                                                    <select class="form-select" name="status">
                                                                        <option value="active" <?= $m['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                                                        <option value="inactive" <?= $m['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light btn-rounded" data-bs-dismiss="modal">Cancel</button>
                                                        <button class="btn btn-primary btn-rounded">Update Module</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="empty-state">
                            <h5>No Modules Found</h5>
                            <p class="mb-3">Click Add Modules to create module records for this course.</p>
                            <button class="btn btn-success btn-rounded" data-bs-toggle="modal" data-bs-target="#addModal">
                                ➕ Add Modules
                            </button>
                        </div>
                    <?php } ?>
                </div>

            <?php } ?>

        </div>
    </div>

    <?php include 'footer.php'; ?>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5>Add Modules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="add_modules" value="1">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">

                    <div id="rows">
                        <div class="module-input-box">
                            <div class="module-input-box-title">
                                <span>Module 1</span>
                            </div>

                            <div class="row">
                                <div class="col-md-4 input-gap">
                                    <label class="form-label">Module Code</label>
                                    <input name="module_code[]" class="form-control" placeholder="Enter module code">
                                </div>

                                <div class="col-md-8 input-gap">
                                    <label class="form-label">Module Name</label>
                                    <input name="module_name[]" class="form-control" placeholder="Enter module name" required>
                                </div>

                                <div class="col-md-12 input-gap">
                                    <label class="form-label">Description</label>
                                    <textarea name="description[]" class="form-control" placeholder="Enter description"></textarea>
                                </div>

                                <div class="col-md-4 input-gap">
                                    <label class="form-label">Written Marks</label>
                                    <input name="max_written_marks[]" type="number" class="form-control" value="100">
                                </div>

                                <div class="col-md-4 input-gap">
                                    <label class="form-label">Practical Marks</label>
                                    <input name="max_practical_marks[]" type="number" class="form-control" value="100">
                                </div>

                                <div class="col-md-4 input-gap">
                                    <label class="form-label">Duration Hours</label>
                                    <input name="duration_hours[]" type="number" class="form-control" placeholder="Enter hours">
                                </div>

                                <div class="col-md-12 input-gap">
                                    <label class="form-label">Status</label>
                                    <select name="status[]" class="form-select">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-add-more btn-rounded btn-sm" onclick="addRow()">
                        + Add More Module
                    </button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-rounded" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success btn-rounded">Save Modules</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>
<script src="js/admin-standard.js"></script>

<script>
let count = 1;

function addRow() {
    if (count >= 5) {
        alert("Maximum 5 modules allowed");
        return;
    }

    count++;

    document.getElementById('rows').insertAdjacentHTML('beforeend', `
        <div class="module-input-box">
            <div class="module-input-box-title">
                <span>Module ${count}</span>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeModuleBox(this)">Remove</button>
            </div>

            <div class="row">
                <div class="col-md-4 input-gap">
                    <label class="form-label">Module Code</label>
                    <input name="module_code[]" class="form-control" placeholder="Enter module code">
                </div>

                <div class="col-md-8 input-gap">
                    <label class="form-label">Module Name</label>
                    <input name="module_name[]" class="form-control" placeholder="Enter module name" required>
                </div>

                <div class="col-md-12 input-gap">
                    <label class="form-label">Description</label>
                    <textarea name="description[]" class="form-control" placeholder="Enter description"></textarea>
                </div>

                <div class="col-md-4 input-gap">
                    <label class="form-label">Written Marks</label>
                    <input name="max_written_marks[]" type="number" class="form-control" value="100">
                </div>

                <div class="col-md-4 input-gap">
                    <label class="form-label">Practical Marks</label>
                    <input name="max_practical_marks[]" type="number" class="form-control" value="100">
                </div>

                <div class="col-md-4 input-gap">
                    <label class="form-label">Duration Hours</label>
                    <input name="duration_hours[]" type="number" class="form-control" placeholder="Enter hours">
                </div>

                <div class="col-md-12 input-gap">
                    <label class="form-label">Status</label>
                    <select name="status[]" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    `);
}

function removeModuleBox(button) {
    button.closest('.module-input-box').remove();
    count--;
}
</script>

</body>
</html>