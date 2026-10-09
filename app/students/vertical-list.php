<?php
if (isset($_GET['id']) && !empty($_GET['id'])) {
    require '../../includes/db-config.php';
    session_start();

    $role_query = str_replace("{{ table }}", "Users", $_SESSION['RoleQuery']);
    $role_query = str_replace("{{ column }}", "ID", $role_query);

    echo '<option value="">Choose</option>';

    $id = (int) $_GET['id']; // vertical id

    $Usersquery = '';
    $userArr = array(
        "Academic Head", "Accountant", "Administrator",
        "Counsellor", "Operations", "Sub-Counsellor", "University Head"
    );

    if ($_SESSION['Role'] == "Center") {
        $Usersquery = " AND (Users.Role ='Center' OR Users.Role ='Sub-Center')";
    } else if (in_array($_SESSION['Role'], $userArr)) {
        $Usersquery = " AND Users.Role ='Center'";
    }

    // 🔹 Always filter by Vertical only
    $sql = "
        SELECT Users.ID, CONCAT(Users.Name, ' (', Users.Code, ')') as Name
        FROM Users
        WHERE Users.Vertical = $id 
          AND Users.Status = 1
          $Usersquery
          $role_query
        GROUP BY Users.ID
        ORDER BY Users.Code ASC
    ";

    // Debug query (remove in production)
    // echo $sql; die;

    $centers = $conn->query($sql);

    if ($centers && $centers->num_rows > 0) {
        while ($center = $centers->fetch_assoc()) { ?>
            <option value="<?php echo $center['ID'] ?>">
                <?php echo $center['Name'] ?>
            </option>
        <?php }
    }
}
