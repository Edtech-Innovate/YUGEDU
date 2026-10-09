<?php
session_start();
if (isset($_SESSION["Password"]) || isset($_SESSION["Unique_ID"])) {
  header("Location: /dashboard");
}
include('includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
  <meta charset="utf-8" />
  <title>Login | <?= $app_title ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-touch-fullscreen" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta content="" name="description" />
  <meta content="" name="author" />
  <link href="assets/plugins/pace/pace-theme-flash.css" rel="stylesheet" type="text/css" />
  <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="assets/plugins/jquery-scrollbar/jquery.scrollbar.css" rel="stylesheet" type="text/css" media="screen" />
  <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen" />
  <link class="main-stylesheet" href="pages/css/pages.css" rel="stylesheet" type="text/css" />
  <link href="assets/css/toastr.min.css" rel="stylesheet" type="text/css" />
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
  <style>
    .has-error {
      border: none !important;
      border: solid 1px red !important;
      box-shadow: 0 1px 1px rgba(0, 0, 0, 0.16), 0 1px 1px rgba(0, 0, 0, 0.23);
    }

    .form-group-default.focused {
      border: solid 1px green !important;
      box-shadow: 0 1px 1px rgba(0, 0, 0, 0.16), 0 1px 1px rgba(0, 0, 0, 0.23);
    }

    .form-group-default {
      border-radius: 10px;
    }

    .form-group-default+.error:after {
      background-color: white !important;
      height: 0px !important;
    }

    .error {
      background: #ff00002e;
      width: max-content;
      margin-top: -3px;
      color: red;
      border-radius: 5px;
    }

    .login-wrapper {
      background-color: rgb(110 152 144 / 35%) !important;
    }

    .newlog_btn {
      background-color: #28704e !important;
      color: white !important;
      border-radius: 11px !important;
      width: 100% !important;
      padding: 0px !important;
      height: 40px !important;
      border: solid 1px #28704e !important;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .newlog_btn:hover {
      background-color: #1e5a3e !important;
      transform: translateY(-1px);
    }

    @media (min-width: 300px) and (max-width: 991px) {
      .form-group-default {
        padding-bottom: 5px !important;
        padding-left: 9px;
      }
    }

    .custom_login_form_card {
      height: auto !important;
      min-height: 550px;
      padding: 30px;
    }

    .login_cutsom_mb {
      margin-bottom: 30px !important;
      margin-top: 20px !important;
    }
    
    /* Modal Styles */
    .otp-modal .modal-content {
      border-radius: 15px;
      border: none;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .otp-modal .modal-header {
      background: linear-gradient(135deg, #28704e 0%, #1e5a3e 100%);
      color: white;
      border-radius: 15px 15px 0 0;
      padding: 20px;
      border: none;
    }
    
    .otp-modal .modal-title {
      font-weight: 600;
      font-size: 20px;
    }
    
    .otp-modal .modal-body {
      padding: 30px;
    }
    
    .otp-input {
      text-align: center;
      font-size: 24px !important;
      letter-spacing: 10px;
      font-weight: bold;
      padding: 12px !important;
    }
    
    .otp-timer {
      font-size: 13px;
      color: #666;
      text-align: center;
      margin-top: 15px;
    }
    
    .otp-timer span {
      color: #28704e;
      font-weight: bold;
    }
    
    .resend-link {
      text-align: center;
      margin-top: 15px;
    }
    
    .resend-link a {
      color: #28704e;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
    }
    
    .resend-link a:hover {
      text-decoration: underline;
    }
    
    .modal-footer {
      border-top: none;
      padding: 0 30px 30px 30px;
      justify-content: center;
    }
    
    .verify-otp-btn {
      background-color: #28704e !important;
      color: white !important;
      border-radius: 10px !important;
      padding: 10px 30px !important;
      font-weight: 600;
      width: 100%;
      transition: all 0.3s ease;
    }
    
    .verify-otp-btn:hover {
      background-color: #1e5a3e !important;
      transform: translateY(-1px);
    }
    
    .verify-otp-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }
    
    .close-btn {
      background: none;
      border: none;
      color: white;
      font-size: 28px;
      opacity: 0.8;
      cursor: pointer;
    }
    
    .close-btn:hover {
      opacity: 1;
    }

    /* Professional Loader Styles */
    .btn-loader {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-loader .loader-ring {
      width: 18px;
      height: 18px;
      border: 2px solid rgba(255,255,255,0.3);
      border-top: 2px solid #fff;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }

    .btn-loader .loader-dots {
      display: inline-flex;
      gap: 4px;
    }

    .btn-loader .loader-dots span {
      width: 6px;
      height: 6px;
      background: white;
      border-radius: 50%;
      animation: bounce 1.4s infinite ease-in-out both;
    }

    .btn-loader .loader-dots span:nth-child(1) { animation-delay: -0.32s; }
    .btn-loader .loader-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes bounce {
      0%, 80%, 100% { transform: scale(0); }
      40% { transform: scale(1); }
    }

    /* Full Page Loader */
    .fullpage-loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      z-index: 9999;
      display: none;
      align-items: center;
      justify-content: center;
    }

    .loader-card {
      background: white;
      border-radius: 20px;
      padding: 30px 40px;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      animation: fadeInUp 0.3s ease;
    }

    .loader-card .spinner {
      width: 50px;
      height: 50px;
      border: 4px solid #e0e0e0;
      border-top: 4px solid #28704e;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 15px;
    }

    .loader-card .loader-message {
      color: #28704e;
      font-size: 16px;
      font-weight: 500;
      margin-top: 10px;
    }

    .loader-card .loader-sub {
      color: #999;
      font-size: 13px;
      margin-top: 5px;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Button Loading State */
    .btn-loading {
      position: relative;
      pointer-events: none;
    }

    .btn-loading .btn-text {
      visibility: hidden;
      opacity: 0;
    }

    .btn-loading .btn-spinner {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-spinner {
      display: none;
    }

    .btn-loading .btn-spinner {
      display: flex;
    }

    .btn-spinner .ring {
      width: 18px;
      height: 18px;
      border: 2px solid rgba(255,255,255,0.3);
      border-top: 2px solid #fff;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
  </style>
  <script type="text/javascript">
    window.onload = function() {
      if (navigator.appVersion.indexOf("Windows NT 6.2") != -1)
        document.head.innerHTML += '<link rel="stylesheet" type="text/css" href="pages/css/windows.chrome.fix.css" />'
    }
  </script>
</head>

<body class="fixed-header ">
  <div class="login-wrapper row p-0 m-0">
    <div class="col-xl-6 col-lg-6 p-0 m-0 login_col1 d-flex justify-content-end">
      <div class="login-container h-100 d-flex align-items-center justify-content-center">
        <div class="p-l-50 p-r-50 p-t-50 m-t-30 sm-p-l-15 sm-p-r-15 sm-p-t-40">
          <div class="new_custom_h d-flex align-items-start">
            <div class="university_new_logo">
              <lottie-player src="assets/animation/Animation - 1748932884552.json" background="transparent" speed="0.5" loop autoplay style="width: 400px;height:600px"></lottie-player>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 p-0 m-0 login_col2 d-flex" style="height: 100vh;">
      <div class="bg-pic login_form_new d-flex h-100 justify-content-center align-items-center" style="background-image: url('<?= $login_cover ?>'); background-repeat: no-repeat; background-size: cover;">
        <div class="">
          <div class="card card-body custom_login_form_card shadow-lg m-0">
            <div class="d-flex justify-content-center align-items-center login_cutsom_mb">
              <div class="text-center mr-1">
                <?php if (!empty($dark_logo)) { ?>
                  <img src="<?= $dark_logo ?>" alt="logo" data-src="<?= $dark_logo ?>" data-src-retina="<?= $dark_logo ?>" style="width:50px; height:50px;">
                <?php } ?>
              </div>
              <div class="">
                <h2 class="mt-3 fw-bold title_text_login text-start mb-0">Welcome</h2>
                <p class="mb-4 title_text_login text-start">Sign in to your account</p>
              </div>
            </div>
            
            <!-- Password Login Form -->
            <form id="form-login" class="login-form" role="form" autocomplete="off" action="app/login/login-otp">
              <div class="form-group form-group-default mb-4">
                <label class="mb-2">User Name</label>
                <div class="controls">
                  <input type="text" name="username" style="text-transform: uppercase" placeholder="Username" class="form-control" required>
                </div>
              </div>
              <div class="form-group form-group-default mb-4">
                <label class="mb-2">Password</label>
                <div class="controls">
                  <input type="password" class="form-control" name="password" placeholder="Credentials" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 no-padding sm-p-l-10 d-flex justify-content-between">
                  <div class="form-check">
                    <input type="checkbox" checked value="1" id="checkbox1">
                    <label for="checkbox1">Remember me</label>
                  </div>
                  <a href="#" class="normal newlog_btn1">Lost your password?</a>
                </div>
                <div class="col-md-12 d-flex align-items-center justify-content-center mt-4">
                  <button aria-label="" class="btn newlog_btn btn-lg m-t-10 w-50 rounded-pill custom_login_btn" type="submit" id="signinBtn">Sign in</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- OTP Modal -->
  <div class="modal fade otp-modal" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between flex-row">
          <h5 class="modal-title" id="otpModalLabel">🔐 Verify OTP</h5>
          <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="text-center mb-3">Please enter the OTP sent to your email</p>
          <p class="text-center text-muted small" id="otpEmailDisplay"></p>
          <div class="form-group">
            <input type="text" class="form-control otp-input" id="otpCode" placeholder="------" maxlength="6" autocomplete="off" style="text-align: center; font-size: 24px; letter-spacing: 5px;">
          </div>
          <div class="otp-timer" id="otpTimer"></div>
          <div class="resend-link">
            <a id="resendOtpBtn">Didn't receive OTP? Resend</a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn verify-otp-btn" id="verifyOtpBtn">Verify OTP</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Full Page Loader -->
  <div id="fullpageLoader" class="fullpage-loader">
    <div class="loader-card">
      <div class="spinner"></div>
      <div class="loader-message" id="loaderMessage">Please wait...</div>
      <div class="loader-sub" id="loaderSubMessage"></div>
    </div>
  </div>
  
  <!-- BEGIN VENDOR JS -->
  <script src="assets/plugins/pace/pace.min.js" type="text/javascript"></script>
  <script src="assets/plugins/liga.js" type="text/javascript"></script>
  <script src="assets/plugins/jquery/jquery-3.2.1.min.js" type="text/javascript"></script>
  <script src="assets/plugins/modernizr.custom.js" type="text/javascript"></script>
  <script src="assets/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
  <script src="assets/plugins/popper/umd/popper.min.js" type="text/javascript"></script>
  <script src="assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
  <script src="assets/plugins/jquery/jquery-easy.js" type="text/javascript"></script>
  <script src="assets/plugins/jquery-unveil/jquery.unveil.min.js" type="text/javascript"></script>
  <script src="assets/plugins/jquery-ios-list/jquery.ioslist.min.js" type="text/javascript"></script>
  <script src="assets/plugins/jquery-actual/jquery.actual.min.js"></script>
  <script src="assets/plugins/jquery-scrollbar/jquery.scrollbar.min.js"></script>
  <script type="text/javascript" src="assets/plugins/select2/js/select2.full.min.js"></script>
  <script type="text/javascript" src="assets/plugins/classie/classie.js"></script>
  <script src="assets/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
  <!-- END VENDOR JS -->
  <script src="pages/js/pages.min.js"></script>
  <script src="assets/js/toastr.min.js"></script>

  <script>
    toastr.options = {
      "closeButton": false,
      "debug": false,
      "newestOnTop": false,
      "progressBar": true,
      "positionClass": "toast-top-right",
      "preventDuplicates": false,
      "onclick": null,
      "showDuration": "300",
      "hideDuration": "1000",
      "timeOut": "3000",
      "extendedTimeOut": "1000",
      "showEasing": "swing",
      "hideEasing": "linear",
      "showMethod": "fadeIn",
      "hideMethod": "fadeOut"
    }
    
    let otpTimerInterval;
    let otpExpiryTime = 0;
    let currentEmail = '';
    
    // Professional Loader Functions
    function showFullpageLoader(message, subMessage = '') {
      $('#loaderMessage').text(message);
      if (subMessage) {
        $('#loaderSubMessage').text(subMessage).show();
      } else {
        $('#loaderSubMessage').hide();
      }
      $('#fullpageLoader').fadeIn(200);
    }
    
    function hideFullpageLoader() {
      $('#fullpageLoader').fadeOut(200);
    }
    
    function setButtonLoading(btn, isLoading, loadingText = 'Processing...') {
      if (isLoading) {
        btn.data('original-text', btn.html());
        btn.addClass('btn-loading');
        btn.html('<span class="btn-text">' + loadingText + '</span><span class="btn-spinner"><span class="ring"></span> <span>' + loadingText + '</span></span>');
        btn.prop('disabled', true);
      } else {
        btn.removeClass('btn-loading');
        btn.html(btn.data('original-text'));
        btn.prop('disabled', false);
      }
    }
    
    // Password Login Validation
    $(function() {
      $('#form-login').validate();
      $("#form-login").on("submit", function(e) {
        e.preventDefault();
        if ($('#form-login').valid()) {
          var submitBtn = $('#signinBtn');
          setButtonLoading(submitBtn, true, 'Verifying...');
          
          var formData = new FormData(this);
          
          $.ajax({
            url: this.action,
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
              if (data.status == 201) {
                // OTP sent successfully, show modal
                setButtonLoading(submitBtn, false);
                toastr.success(data.message);
                currentEmail = data.email || '';
                $('#otpEmailDisplay').html('OTP sent to: <strong>' + (currentEmail || 'your email') + '</strong>');
                $('#otpModal').modal('show');
                $('#otpCode').val('');
                startOtpTimer(300);
              } else if (data.status == 200) {
                // Direct login
                setButtonLoading(submitBtn, true, 'Redirecting...');
                toastr.success(data.message);
                window.setTimeout(function() {
                  window.location.href = data.url;
                }, 1000);
              } else {
                setButtonLoading(submitBtn, false);
                toastr.error(data.message);
              }
            },
            error: function() {
              setButtonLoading(submitBtn, false);
              toastr.error('Network error. Please try again.');
            }
          });
        }
      });
      
      // OTP input handling - auto focus and auto submit
      $('#otpCode').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value);
        if (value.length === 6) {
          $('#verifyOtpBtn').click();
        }
      });
      
      // Verify OTP
    //   $('#verifyOtpBtn').click(function() {
    //     var otp = $('#otpCode').val().trim();
        
    //     if (!otp) {
    //       toastr.error('Please enter OTP');
    //       return;
    //     }
        
    //     if (otp.length != 6) {
    //       toastr.error('OTP must be 6 digits');
    //       return;
    //     }
        
    //     var btn = $(this);
    //     var originalText = btn.html();
    //     btn.prop('disabled', true);
    //     btn.html('<span class="btn-loader"><span class="loader-ring"></span> Verifying...</span>');
        
    //     $.ajax({
    //       url: 'app/login/verify-otp',
    //       type: 'post',
    //       data: { otp: otp },
    //       dataType: 'json',
    //       success: function(data) {
    //         if (data.status == 200) {
    //           showFullpageLoader('Login successful!', 'Redirecting to dashboard...');
    //           toastr.success(data.message);
    //           if (otpTimerInterval) clearInterval(otpTimerInterval);
    //           $('#otpModal').modal('hide');
    //           window.setTimeout(function() {
    //             window.location.href = data.url;
    //           }, 1000);
    //         } else {
    //           btn.prop('disabled', false);
    //           btn.html(originalText);
    //           toastr.error(data.message);
    //         }
    //       },
    //       error: function() {
    //         btn.prop('disabled', false);
    //         btn.html(originalText);
    //         toastr.error('Network error. Please try again.');
    //       }
    //     });
    //   });
      // Verify OTP
$('#verifyOtpBtn').click(function() {
  var otp = $('#otpCode').val().trim();
  
  if (!otp) {
    toastr.error('Please enter OTP');
    return;
  }
  
  if (otp.length != 6) {
    toastr.error('OTP must be 6 digits');
    return;
  }
  
  var btn = $(this);
  setButtonLoading(btn, true, 'Verifying OTP...');
  
  $.ajax({
    url: 'app/login/verify-otp',
    type: 'post',
    data: { otp: otp },
    dataType: 'json',
    success: function(data) {
      if (data.status == 200) {
        showFullpageLoader('Login successful!', 'Redirecting to dashboard...');
        toastr.success(data.message);
        if (otpTimerInterval) clearInterval(otpTimerInterval);
        $('#otpModal').modal('hide');
        window.setTimeout(function() {
          window.location.href = data.url;
        }, 1000);
      } else {
        setButtonLoading(btn, false);
        toastr.error(data.message);
      }
    },
    error: function() {
      setButtonLoading(btn, false);
      toastr.error('Network error. Please try again.');
    }
  });
});
      // Resend OTP
      $('#resendOtpBtn').click(function() {
        var resendLink = $(this);
        var originalText = resendLink.html();
        
        resendLink.html('<span class="btn-loader"><span class="loader-ring" style="width:12px; height:12px;"></span> Sending...</span>');
        resendLink.css('pointer-events', 'none');
        
        $.ajax({
          url: 'app/login/resend-otp',
          type: 'post',
          data: {},
          dataType: 'json',
          success: function(data) {
            if (data.status == 200) {
              toastr.success('New OTP sent to your email');
              startOtpTimer(300);
              $('#otpCode').val('').focus();
            } else {
              toastr.error(data.message);
            }
            resendLink.html(originalText);
            resendLink.css('pointer-events', 'auto');
          },
          error: function() {
            toastr.error('Network error. Please try again.');
            resendLink.html(originalText);
            resendLink.css('pointer-events', 'auto');
          }
        });
      });
      
      // OTP Timer Function
      function startOtpTimer(seconds) {
        if (otpTimerInterval) clearInterval(otpTimerInterval);
        otpExpiryTime = seconds;
        
        otpTimerInterval = setInterval(function() {
          if (otpExpiryTime <= 0) {
            clearInterval(otpTimerInterval);
            $('#otpTimer').html('<span style="color:red;">OTP expired! Please resend.</span>');
            $('#verifyOtpBtn').prop('disabled', true);
          } else {
            var minutes = Math.floor(otpExpiryTime / 60);
            var secs = otpExpiryTime % 60;
            $('#otpTimer').html('OTP valid for: <span>' + minutes + ':' + (secs < 10 ? '0' + secs : secs) + '</span>');
            $('#verifyOtpBtn').prop('disabled', false);
            otpExpiryTime--;
          }
        }, 1000);
      }
      
      // Reset timer when modal closes
      $('#otpModal').on('hidden.bs.modal', function() {
        if (otpTimerInterval) clearInterval(otpTimerInterval);
        $('#otpCode').val('');
        $('#verifyOtpBtn').prop('disabled', false);
        $('#verifyOtpBtn').html('Verify OTP');
      });
      
      // Enter key for OTP
      $('#otpCode').keypress(function(e) {
        if (e.which == 13) {
          $('#verifyOtpBtn').click();
        }
      });
    });
  </script>
</body>

</html>