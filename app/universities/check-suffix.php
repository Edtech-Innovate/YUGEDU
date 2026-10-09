<?php
require '../../includes/db-config.php';

if (isset($_POST['suffix']) && isset($_POST['id'])) {
    $suffix = trim($_POST['suffix']);
   // $university_id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT ID FROM Universities WHERE Center_Suffix = ?");
    $stmt->bind_param("s", $suffix);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    
    $stmt->close();
}
?>