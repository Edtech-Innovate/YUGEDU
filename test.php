<?php
require 'includes/db-config.php';



$mail = $conn->query("SELECT Subject as subject, Template as body, Attachments FROM Email_Templates WHERE University_ID = 21 AND Name = 'Welcome'");
if ($mail->num_rows > 0) {
  $mail = $mail->fetch_assoc();

  // Sender
  $university = $conn->query("SELECT Name as name, Email as email FROM University WHERE ID = $universityId");
  $sender = $university->fetch_assoc();

  $studentDetails = $conn->query("SELECT Lead_Status.Unique_ID AS`{{ student_id }}`,UPPER(Leads.`Name`)AS`{{ student_name }}`,Leads.Mobile as`{{ student_mobile }}`,Leads.Email as`{{ student_email }}`,UPPER(DATE_FORMAT(Students.DOB,'%d%b%Y'))as`{{ student_password }}`,Courses.`Name` as`{{ program }}`,Sub_Courses.`Name` as`{{ specialization }}`,Universities.`Name` as`{{ university_name }}`,Universities.Vertical as`{{ university_vertical }}` FROM Lead_Status LEFT JOIN Leads ON Lead_Status.Lead_ID=Leads.ID LEFT JOIN Universities ON Lead_Status.University_ID=Universities.ID LEFT JOIN Courses ON Lead_Status.Course_ID=Courses.ID LEFT JOIN Sub_Courses ON Lead_Status.Sub_Course_ID=Sub_Courses.ID LEFT JOIN Students ON Lead_Status.ID=Students.Lead_Status_ID WHERE Lead_Status.ID=192693");
  if ($studentDetails->num_rows > 0) {
    $studentDetails = $studentDetails->fetch_assoc();
    $receivers[] = array('email' => $studentDetails['{{ student_email }}'], 'name' => $studentDetails['{{ student_name }}']);
    foreach ($studentDetails as $key => $value) {
      $mail['body'] = str_replace($key, $value, $mail['body']);
    }
  }
}

echo sendMail($sender, $receivers, $mail);
