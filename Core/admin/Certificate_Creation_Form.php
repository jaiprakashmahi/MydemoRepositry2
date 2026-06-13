<?php
  include 'conn.php';
  if (!isset($_GET['id'])) {
      die("No student ID provided.");
  }
  $student_id = $_GET['id'];
  $query = "SELECT * FROM students WHERE id = $student_id";
  $result = $conn->query($query);
  if ($result->num_rows == 0) {
      die("Student not found.");
  }
  $student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Create Certificate</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">
<div class="container bg-white shadow rounded p-5">
  <h2 class="mb-4">Certificate Creation Form</h2>

  <form action="save_certificate.php?id=<?= $student_id ?>" method="POST">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label>Student Code</label>
        <input type="text" name="student_code" class="form-control" required value="<?= htmlspecialchars($student['student_code']) ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Student Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($student['name']) ?>">
      </div>

      <div class="col-md-4 mb-3">
        <label>Date of Birth</label>
        <input type="date" name="dob" class="form-control" required value="<?= htmlspecialchars($student['dob']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label>Father's Name</label>
        <input type="text" name="father_name" class="form-control" required value="<?= htmlspecialchars($student['father_name']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label>Mother's Name</label>
        <input type="text" name="mother_name" class="form-control" required>
      </div>

      <div class="col-md-6 mb-3">
        <label>Course Name</label>
        <input type="text" name="course_name" class="form-control" required value="<?= htmlspecialchars($student['course_name']) ?>">
      </div>
      <div class="col-md-3 mb-3">
        <label>Duration</label>
        <input type="text" name="duration" class="form-control" required value="<?= htmlspecialchars($student['duration']) ?>">
      </div>
      <div class="col-md-3 mb-3">
        <label>Study Center</label>
        <input type="text" name="study_center" class="form-control" required value="<?= htmlspecialchars($student['study_center']) ?>">
      </div>

      <div class="col-md-3 mb-3">
        <label>Written Marks</label>
        <input type="number" name="written_marks" class="form-control marks" required>
      </div>
      <div class="col-md-3 mb-3">
        <label>Practical Marks</label>
        <input type="number" name="practical_marks" class="form-control marks" required>
      </div>
      <div class="col-md-3 mb-3">
        <label>Project Marks</label>
        <input type="number" name="project_marks" class="form-control marks" required>
      </div>
      <div class="col-md-3 mb-3">
        <label>Viva Marks</label>
        <input type="number" name="viva_marks" class="form-control marks" required>
      </div>

      <div class="col-md-4 mb-3">
        <label>Percentage</label>
        <input type="number" step="0.01" name="percentage" id="percentage" class="form-control" readonly>
      </div>
      <div class="col-md-4 mb-3">
        <label>Grade</label>
        <input type="text" name="grade" id="grade" class="form-control" readonly>
      </div>
      <div class="col-md-4 mb-3">
        <label>Issue Date</label>
        <input type="date" name="issue_date" class="form-control" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Certificate</button>
  </form>
</div>

<script>
  const markFields = document.querySelectorAll('.marks');
  const percentageInput = document.getElementById('percentage');
  const gradeInput = document.getElementById('grade');

  markFields.forEach(input => {
    input.addEventListener('input', calculatePercentageAndGrade);
  });

  function calculatePercentageAndGrade() {
    let total = 0;
    let valid = true;

    markFields.forEach(input => {
      const val = parseFloat(input.value);
      if (!isNaN(val)) {
        total += val;
      } else {
        valid = false;
      }
    });

    if (valid && total > 0) {
      const percentage = (total / 400) * 100;
      percentageInput.value = percentage.toFixed(2);

      let grade = '';
      if (percentage >= 90) grade = 'A+';
      else if (percentage >= 80) grade = 'A';
      else if (percentage >= 70) grade = 'B+';
      else if (percentage >= 60) grade = 'B';
      else if (percentage >= 50) grade = 'C';
      else grade = 'F';

      gradeInput.value = grade;
    } else {
      percentageInput.value = '';
      gradeInput.value = '';
    }
  }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $('form').on('submit', function(e) {
    e.preventDefault(); // Prevent default form submit
    const form = $(this);

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      success: function(response) {
        alert("Certificate saved successfully!");
        window.location.href = "certificates.php";
      },
      error: function() {
        alert("There was an error saving the certificate.");
      }
    });
  });
</script>
</body>
</html>
