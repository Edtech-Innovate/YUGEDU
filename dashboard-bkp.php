<?php include('includes/header-top.php'); ?>
<?php include('includes/header-bottom.php'); ?>
<?php include('includes/menu.php'); ?>

<style>
  .dash-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; margin-bottom: 30px; }
  .dash-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; background: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #eef2f5; box-shadow: 0 4px 20px rgba(0,0,0,0.02); margin-bottom: 25px; }
  .dash-welcome h2 { margin: 0 0 4px 0; font-size: 26px; font-weight: 700; color: #1e293b; }
  .dash-welcome p { margin: 0; color: #64748b; font-size: 14px; }
  
  .stat-banner-card { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 28px; border-radius: 10px; min-width: 260px; box-shadow: 0 10px 25px rgba(15,23,42,0.15); display: flex; flex-direction: column; justify-content: center; }
  .stat-banner-card .label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #080a0c; font-weight: 700; margin-bottom: 6px; }
  .stat-banner-card .value { font-size: 32px; font-weight: 800; line-height: 1; letter-spacing: -0.5px; }
  
  .grid-section-title { font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b; font-weight: 700; margin-bottom: 15px; padding-left: 5px; }
  
  /* Flexbox Responsive Grid */
  .dash-grid { display: flex; flex-wrap: wrap; margin: 0 -10px; }
  .dash-grid-col { flex: 0 0 100%; max-width: 100%; padding: 0 10px; margin-bottom: 20px; }
  
  @media (min-width: 576px) { .dash-grid-col { flex: 0 0 50%; max-width: 50%; } }
  @media (min-width: 992px) { .dash-grid-col { flex: 0 0 33.3333%; max-width: 33.3333%; } }
  @media (min-width: 1200px) { .dash-grid-col { flex: 0 0 25%; max-width: 25%; } }
  
  .session-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01); transition: all 0.2s ease-in-out; position: relative; overflow: hidden; }
  .session-card:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.05); border-color: #cbd5e1; }
  .session-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #10cfbd; }
  
  .card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
  .card-title { font-size: 16px; font-weight: 700; color: #334155; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .card-subtitle { font-size: 12px; color: #94a3b8; margin: 2px 0 0 0; }
  .card-badge { font-size: 11px; font-weight: 600; background: rgba(16, 207, 189, 0.12); color: #10cfbd; padding: 4px 10px; border-radius: 20px; }
  
  .card-divider { border-top: 1px dashed #e2e8f0; margin: 16px 0; }
  .card-counter { display: flex; align-items: baseline; }
  .card-num { font-size: 34px; font-weight: 800; color: #0f172a; line-height: 1; letter-spacing: -1px; }
  .card-unit { font-size: 13px; color: #64748b; margin-left: 8px; font-weight: 500; }

  /* Bottom Center Contact Info Styling */
  .dash-footer-contact { text-align: center; margin-top: 40px; margin-bottom: 15px; padding-top: 20px; border-top: 1px dashed #e2e8f0; font-size: 13px; color: #64748b; }
  .dash-footer-contact a { color: #10cfbd; font-weight: 600; text-decoration: none; }
  .dash-footer-contact a:hover { text-decoration: underline; }
</style>

<div class="page-container ">
  <?php include('includes/topbar.php'); ?>
  <div class="page-content-wrapper ">
    <div class="content ">
      <div class="jumbotron" data-pages="parallax">
        <div class="container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <ol class="breadcrumb">
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
      
      <div class="container-fluid">
        <?php
        if ($_SESSION['Role'] == 'Student') {
          include('dashboards/student.php');
        }
        //print_r($_SESSION);die;
        elseif($_SESSION['Role'] == 'Center' || $_SESSION['Role'] == 'Administrator' || $_SESSION['Role'] == 'University Head' || $_SESSION['Role'] == 'Counsellor' || $_SESSION['Role'] == 'Sub-Counsellor') {
            
            $university_id = intval($_SESSION['university_id']);
            
            // Query 1: Total overall student metrics
            $total_query = "SELECT COUNT(ID) as Grand_Total FROM Students WHERE University_ID = $university_id";
            $total_result = mysqli_query($conn, $total_query);
            $grand_total = ($total_result) ? mysqli_fetch_assoc($total_result)['Grand_Total'] : 0;

            // Query 2: Segmented session data matrix
            $query = "SELECT 
                        asess.ID as Session_ID,
                        asess.Name as Session_Name,
                        COUNT(s.ID) as Total_Students
                      FROM Admission_Sessions asess
                      LEFT JOIN Students s ON s.Admission_Session_ID = asess.ID AND s.University_ID = asess.University_ID
                      WHERE asess.University_ID = $university_id
                      GROUP BY asess.ID, asess.Name
                      ORDER BY asess.ID DESC";
            
            $result = mysqli_query($conn, $query);
            ?>

            <div class="dash-wrapper">
                <div class="dash-header">
                  <div class="dash-welcome">
                    <h2>Welcome back, <?php echo isset($_SESSION['Name']) ? htmlspecialchars($_SESSION['Name']) : 'User'; ?></h2>
                    <p>Overview of active admissions and panel configurations.</p>
                  </div>
                  
                  <div class="stat-banner-card">
                    <span class="label">Total Students Enrolled</span>
                    <span class="value"><?php echo number_format($grand_total); ?></span>
                  </div>
                </div>

                <div class="grid-section-title">Session Wise Students Enrolled</div>
                
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    echo '<div class="dash-grid">';
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $session_name = htmlspecialchars($row['Session_Name']);
                        $total_students = intval($row['Total_Students']);
                        ?>
                        <div class="dash-grid-col">
                          <div class="session-card">
                            <div class="card-top">
                              <div style="max-width: 75%;">
                                <h3 class="card-title" title="<?php echo $session_name; ?>">
                                  <?php echo $session_name; ?>
                                </h3>
                                <p class="card-subtitle">Admission Intake</p>
                              </div>
                              <span class="card-badge">Active</span>
                            </div>
                            
                            <div class="card-divider"></div>
                            
                            <div class="card-counter">
                              <span class="card-num"><?php echo number_format($total_students); ?></span>
                              <span class="card-unit">Students</span>
                            </div>
                          </div>
                        </div>
                        <?php
                    }
                    echo '</div>'; 
                } else {
                    echo '<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; color: #64748b; font-size:14px;">No active registration configurations found for this university profile.</div>';
                }
                ?>

                <div class="dash-footer-contact">
                  Contact Email: <a href="mailto:info@yugedu.in">info@yugedu.in</a>
                </div>

            </div> <?php } ?>
      </div> </div> </div> </div> <?php include('includes/footer-top.php'); ?>
<?php include('includes/footer-bottom.php'); ?>