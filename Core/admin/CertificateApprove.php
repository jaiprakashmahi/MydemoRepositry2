    <?php
    include 'conn.php';

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        $update = "UPDATE certificates SET approved = 1 WHERE id = $id";
        if ($conn->query($update)) {
            echo "<script>alert('Certificate approved successfully'); window.location.href='certificates.php';</script>";
        } else {
            echo "Error approving certificate: " . $conn->error;
        }
    } else {
        echo "No certificate ID provided.";
    }
    ?>
