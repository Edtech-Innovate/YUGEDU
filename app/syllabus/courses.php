<?php
if (isset($_GET['id'])) {
  require '../../includes/db-config.php';
  $id = intval($_GET['id']);
  ?>
  <option value="">Choose</option>
  <?php

  $courses = $conn->query("SELECT Sub_Courses.ID, CONCAT(Sub_Courses.Name,'(',Courses.Short_Name,')') as Name FROM Sub_Courses LEFT JOIN Courses on Course_ID = Courses.ID WHERE Sub_Courses.University_ID = $id AND Sub_Courses.Status =1 Order by Sub_Courses.Name ASC");
  // $courses = $conn->query("SELECT Sub_Courses.ID, CONCAT(Sub_Courses.Name, ' (', Courses.Short_Name, ')') as Name FROM Sub_Courses LEFT JOIN Courses ON Sub_Courses.Course_ID= Courses.ID LEFT JOIN Course_Types ON Courses.Course_Type_ID = Course_Types.ID WHERE Sub_Courses.University_ID = $id ORDER BY Sub_Courses.Name");
  while ($course = $courses->fetch_assoc()) {
    ?>
    <option value="<?php echo $course['ID'] ?>"><?php echo ucfirst(strtolower($course['Name'])) ?></option>
    <?php
  }
}
