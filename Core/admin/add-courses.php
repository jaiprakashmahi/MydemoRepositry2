<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignLab">
    <meta name="robots" content="">
    <meta name="keywords" content="school, school admin, education, academy, admin dashboard, college, college management, education management, institute, school management, school management system, student management, teacher management, university, university management">
    <meta name="description" content="Discover Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provides advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard">
    <meta property="og:title" content="Sharnay : Admin Dashboard">
    <meta property="og:description" content="Sharnay - the ultimate admin dashboard. Specially designed for professionals, and for business. Sharnay provides advanced features and an easy-to-use interface for creating a top-quality website with School and Education Dashboard">
    <meta property="og:image" content="social-image.html">
    <meta name="format-detection" content="telephone=no">

    <!-- Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Page Title Here -->
    <title>Sharnay : Admin Dashboard - Courses</title>

    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/wow-master/css/libs/animate.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/admin-standard.css" rel="stylesheet">
    
    <style>
        .btn-action {
            padding: 5px 10px;
            margin: 0 3px;
            font-size: 12px;
        }
        .btn-edit {
            background-color: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }
        .btn-edit:hover {
            background-color: #45a049;
            border-color: #45a049;
        }
        .btn-delete {
            background-color: #f44336;
            border-color: #f44336;
            color: white;
        }
        .btn-delete:hover {
            background-color: #da190b;
            border-color: #da190b;
        }
        .btn-add {
            background-color: #2196F3;
            border-color: #2196F3;
            color: white;
        }
        .btn-add:hover {
            background-color: #0b7dda;
            border-color: #0b7dda;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-footer {
            border-top: 1px solid #dee2e6;
        }
        .course-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .no-image {
            width: 80px;
            height: 60px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #999;
            font-size: 12px;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            display: none;
            margin-top: 10px;
        }
    </style>
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
                <!-- Add Course Button -->
                <div class="row mb-3">
                    <div class="col-xl-12">
                        <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">add</i>
                            Add Course
                        </button>
                    </div>
                </div>

                <!-- Courses Table -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Courses List</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="coursesTable" class="display table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Name</th>
                                                <th>Duration</th>
                                                <th>Price</th>
                                                <th>Image</th>
                                                <th>Created Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include 'conn.php';
                                            
                                            // Fetch courses from database
                                            $sql = "SELECT * FROM courses ORDER BY created_at DESC";
                                            $result = $conn->query($sql);
                                            
                                            if ($result->num_rows > 0) {
                                                while($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['course_code']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
                                                    echo "<td>$" . number_format($row['price'], 2) . "</td>";
                                                    echo "<td>";
                                                    if (!empty($row['image']) && file_exists($row['image'])) {
                                                        echo "<img src='" . htmlspecialchars($row['image']) . "' class='course-image' alt='Course Image'>";
                                                    } else {
                                                        echo "<div class='no-image'>No Image</div>";
                                                    }
                                                    echo "</td>";
                                                    echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                                                    echo "<td>";
                                                    echo "<button class='btn btn-action btn-edit' data-id='" . $row['id'] . "' data-code='" . htmlspecialchars($row['course_code']) . "' data-name='" . htmlspecialchars($row['course_name']) . "' data-duration='" . htmlspecialchars($row['duration']) . "' data-price='" . $row['price'] . "' data-details='" . htmlspecialchars($row['details']) . "' data-image='" . htmlspecialchars($row['image']) . "' data-bs-toggle='modal' data-bs-target='#editCourseModal'>";
                                                    echo "<i class='material-icons' style='font-size:14px;'>edit</i> Edit";
                                                    echo "</button>";
                                                    echo "<button class='btn btn-action btn-delete' data-id='" . $row['id'] . "' data-code='" . htmlspecialchars($row['course_code']) . "'>";
                                                    echo "<i class='material-icons' style='font-size:14px;'>delete</i> Delete";
                                                    echo "</button>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>No courses found</td></tr>";
                                            }
                                            
                                            $conn->close();
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!-- Add Course Modal -->
        <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="insert_course.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="mb-3">
                                        <label class="form-label text-primary">Course Name<span class="required">*</span></label>
                                        <input type="text" class="form-control" name="course_name" placeholder="Enter Course Name" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-primary">Duration<span class="required">*</span></label>
                                        <input type="text" class="form-control" name="duration" placeholder="Enter Duration (e.g., 3 months)" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-primary">Price<span class="required">*</span></label>
                                        <input type="number" class="form-control" name="price" placeholder="Enter Price" step="0.01" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-primary">Course Details<span class="required">*</span></label>
                                        <textarea class="form-control" name="details" placeholder="Enter Course Details" rows="4" required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Course Image</label>
                                        <input type="file" class="form-control" name="image" id="imageUpload" accept="image/*">
                                        <small class="text-muted">Optional: Upload course image (JPG, PNG, GIF)</small>
                                    </div>
                                    <img id="imagePreview" class="image-preview" src="" alt="Image Preview">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-add">Save Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Course Modal -->
        <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="update_course.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="edit_course_id" name="course_id">
                        <input type="hidden" id="edit_course_code" name="course_code">
                        <input type="hidden" id="current_image" name="current_image">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="mb-3">
                                        <label class="form-label text-primary">Course Code</label>
                                        <input type="text" class="form-control" id="display_course_code" disabled>
                                        <small class="text-muted">Course code cannot be changed</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-primary">Course Name<span class="required">*</span></label>
                                        <input type="text" class="form-control" id="edit_course_name" name="course_name" placeholder="Enter Course Name" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-primary">Duration<span class="required">*</span></label>
                                        <input type="text" class="form-control" id="edit_duration" name="duration" placeholder="Enter Duration" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-primary">Price<span class="required">*</span></label>
                                        <input type="number" class="form-control" id="edit_price" name="price" placeholder="Enter Price" step="0.01" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-primary">Course Details<span class="required">*</span></label>
                                        <textarea class="form-control" id="edit_details" name="details" placeholder="Enter Course Details" rows="4" required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Current Image</label>
                                        <div id="currentImagePreview" class="mb-2"></div>
                                        <label class="form-label">Update Image (Optional)</label>
                                        <input type="file" class="form-control" name="image" id="editImageUpload" accept="image/*">
                                        <small class="text-muted">Leave empty to keep current image</small>
                                    </div>
                                    <img id="editImagePreview" class="image-preview" src="" alt="Image Preview">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-edit">Update Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteCourseModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="delete_course.php" method="POST">
                        <input type="hidden" id="delete_course_id" name="course_id">
                        <div class="modal-body">
                            <p>Are you sure you want to delete the course: <strong id="delete_course_code"></strong>?</p>
                            <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-delete">Delete Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/admin-standard.js"></script>
    <script src="js/demo.js"></script>
    <script src="js/styleSwitcher.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#coursesTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'desc']]
            });

            // Image preview for add modal
            $('#imageUpload').change(function() {
                readURL(this, '#imagePreview');
            });

            // Image preview for edit modal
            $('#editImageUpload').change(function() {
                readURL(this, '#editImagePreview');
            });

            // Edit Course Modal Handler
            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                var code = $(this).data('code');
                var name = $(this).data('name');
                var duration = $(this).data('duration');
                var price = $(this).data('price');
                var details = $(this).data('details');
                var image = $(this).data('image');
                
                $('#edit_course_id').val(id);
                $('#edit_course_code').val(code);
                $('#display_course_code').val(code);
                $('#edit_course_name').val(name);
                $('#edit_duration').val(duration);
                $('#edit_price').val(price);
                $('#edit_details').val(details);
                $('#current_image').val(image);
                
                // Show current image
                if (image) {
                    $('#currentImagePreview').html('<img src="' + image + '" class="course-image" alt="Current Image">');
                } else {
                    $('#currentImagePreview').html('<div class="no-image">No Image</div>');
                }
                
                // Reset edit image preview
                $('#editImagePreview').hide();
                $('#editImageUpload').val('');
            });

            // Delete Course Modal Handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var code = $(this).data('code');
                
                $('#delete_course_id').val(id);
                $('#delete_course_code').text(code);
                $('#deleteCourseModal').modal('show');
            });
        });

        // Function to preview image
        function readURL(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $(previewId).attr('src', e.target.result);
                    $(previewId).show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>