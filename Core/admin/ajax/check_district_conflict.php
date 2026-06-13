<?php
include '../conn.php';

if (isset($_POST['district_ids']) && isset($_POST['member_type_id'])) {
    $district_ids = $_POST['district_ids'];
    $member_type_id = intval($_POST['member_type_id']);
    
    $conflicts = [];
    
    if (is_array($district_ids)) {
        foreach ($district_ids as $district_id) {
            $district_id = intval($district_id);
            
            // Check if district is already assigned to another member of same type
            $sql = "SELECT mi.applicant_name, mt.type_name 
                    FROM member_information mi
                    JOIN member_types mt ON mi.member_type_id = mt.id
                    JOIN member_working_area mwa ON mi.id = mwa.member_id
                    WHERE FIND_IN_SET(?, mwa.district_ids)
                    AND mi.member_type_id = ?
                    AND mi.member_status = 'Active'";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $district_id, $member_type_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $conflicts[] = "District ID {$district_id} is already assigned to {$row['applicant_name']} ({$row['type_name']})";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    if (count($conflicts) > 0) {
        echo json_encode([
            'has_conflict' => true,
            'message' => 'Warning: ' . implode('<br>', $conflicts)
        ]);
    } else {
        echo json_encode([
            'has_conflict' => false,
            'message' => 'No conflicts found'
        ]);
    }
}
?>