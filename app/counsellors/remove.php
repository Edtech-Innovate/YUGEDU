<?php
require '../../includes/db-config.php';
session_start();

if (isset($_GET['uni']) && isset($_GET['user'])) {
    $uni = mysqli_real_escape_string($conn, $_GET['uni']);
    $user = mysqli_real_escape_string($conn, $_GET['user']);

    // Check if the user/counsellor is assigned as a reporting center for other users under this university,
    // or if the user themselves has a Reporting value pointing to a center.
    // Adjusting based on your University_User schema where `Reporting` references a Center's User_ID:
    $reporting_check = $conn->query("
        SELECT uu.Reporting, u.Name as Center_Name 
        FROM University_User uu 
        JOIN Users u ON uu.Reporting = u.ID 
        WHERE uu.User_ID = $user AND uu.University_ID = $uni AND uu.Reporting > 0
    ");

    if ($reporting_check && $reporting_check->num_rows > 0) {
        $center_names = [];
        while ($row = $reporting_check->fetch_assoc()) {
            $center_names[] = '<div class="mb-1"><i class="fa-solid fa-circle-dot text-warning me-2 small"></i>' . htmlspecialchars($row['Center_Name']) . '</div>';
        }
        $centers_list_html = implode('', $center_names);
        
        echo json_encode([
            'status' => 302, 
            'centers_list' => $centers_list_html
        ]);
        exit();
    }

    // If no active reporting dependency blocks the action, proceed to delete the association
    if ($_SESSION['Role'] == 'Administrator' || isset($_SESSION['university_id'])) {
        $delete = $conn->query("DELETE FROM University_User WHERE User_ID = $user AND University_ID = $uni");
        
        if ($delete) {
            echo json_encode(['status' => 200, 'message' => 'University association removed successfully!']);
        } else {
            echo json_encode(['status' => 302, 'message' => 'Something went wrong while removing the university!']);
        }
    } else {
        echo json_encode(['status' => 302, 'message' => 'Unauthorized action!']);
    }
} else {
    echo json_encode(['status' => 302, 'message' => 'Invalid parameters provided!']);
}