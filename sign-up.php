<?php 
  include ($_SERVER['DOCUMENT_ROOT'].'/includes/db-config.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title> <?=$organization_name?> </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
    <link rel="apple-touch-icon" href="pages/ico/60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="pages/ico/76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="pages/ico/120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="pages/ico/152.png">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
    window.onload = function()
    {
      // fix for windows 8
      if (navigator.appVersion.indexOf("Windows NT 6.2") != -1)
        document.head.innerHTML += '<link rel="stylesheet" type="text/css" href="pages/css/windows.chrome.fix.css" />'
    }
    </script>

    <script>
      function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
          return false;
        return true;
      }
    </script>

    <style>
      .verification-code--inputs input[type=text] {
        border: 2px solid #e1e1e1;
        border-radius: 5px;
        width: 46px;
        height: 46px;
        padding: 10px;
        text-align: center;
        display: inline-block;
        box-sizing:border-box;
      }
    </style>
  </head>
  <body class="fixed-header ">
    <div class="register-container full-height sm-p-t-30">
      <div class="d-flex justify-content-center flex-column full-height ">
        <img src="<?=$dark_logo?>" alt="logo" data-src="<?=$dark_logo?>" data-src-retina="<?=$dark_logo?>" width="100">
        <h3>Become a Partner</h3>
        <form id="form-register" class="p-t-15" role="form" action="/app/partners/store">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group form-group-default">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="John Smith" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group form-group-default">
                <label>Mobile</label>
                <input type="text" onkeypress="return isNumberKey(event)" name="mobile" id="mobile" placeholder="ex: 98637XXXXX" maxlength="10" minlength="10" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group form-group-default">
                <label>Email</label>
                <input type="email" name="email" placeholder="We will send login details" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row m-t-10">
            <div id="recaptcha-container"></div>
          </div>
          <div class="row m-t-10">
            <div class="col-lg-6">
              <p><small>I agree the <a href="#" class="text-info">Terms</a> and <a href="#" class="text-info">Conditions</a>.</small></p>
            </div>
            <div class="col-lg-6 text-right">
              <a href="mailto::partner@vidyaplanet.in" class="text-info small">Help? Contact Support</a>
            </div>
          </div>
          <button aria-label="" class="btn btn-primary btn-cons m-t-10" type="submit">Sign-Up</button>
        </form>
      </div>
    </div>

    <div class="modal fade slide-up" id="mdmodal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="false">
      <div class="modal-dialog modal-md">
        <div class="modal-content-wrapper">
          <div class="modal-content" id="md-modal-content">
            <div class="modal-header clearfix text-left">
              <h5>Verify <span class="semi-bold"></span>OTP</h5>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 d-flex justify-content-center">
                  <div class="verification-code--inputs">
                    <input type="text" onkeypress="return isNumberKey(event)" maxlength="1" />
                    <input type="text" onkeypress="return isNumberKey(event)" maxlength="1" />
                    <input type="text" onkeypress="return isNumberKey(event)" maxlength="1" />
                    <input type="text" onkeypress="return isNumberKey(event)" maxlength="1" />
                    <input type="text" onkeypress="return isNumberKey(event)" maxlength="1" />
                    <input type="text" onkeypress="return isNumberKey(event)" maxlength="1" />
                  </div>
                  <input type="hidden" id="verificationCode" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- BEGIN VENDOR JS -->
    <script src="assets/plugins/pace/pace.min.js" type="text/javascript"></script>
    <!--  A polyfill for browsers that don't support ligatures: remove liga.js if not needed-->
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
    <script>
      $(function(){
        $('#form-register').validate()
      })
    </script>

    <script src="https://www.gstatic.com/firebasejs/8.6.3/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/4.8.1/firebase.js"></script>

    <script>
      const firebaseConfig = {
        apiKey: "AIzaSyAUlRR-GARimphV5oDVn6qSZSC7yytJyvA",
        authDomain: "vidya-planet.firebaseapp.com",
        projectId: "vidya-planet",
        storageBucket: "vidya-planet.appspot.com",
        messagingSenderId: "851137780557",
        appId: "1:851137780557:web:bd3d03c08edacaf286b504",
        measurementId: "G-7BQ1TE7V87"
      };

      // Initialize Firebase
      firebase.initializeApp(firebaseConfig);
    </script>

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
    </script>
    
    <script>
      $(function(){
        $("#form-register").on("submit", function(e){
          if($('#form-register').valid()){
            $(':input[type="submit"]').prop('disabled', true);
            var formData = new FormData(this);
            $.ajax({
              url: this.action,
              type: 'post',
              data: formData,
              cache:false,
              contentType: false,
              processData: false,
              dataType: "json",
              success: function(data) {
                if(data.status==200){
                  sendOTP($('#mobile').val());
                }else{
                  $(':input[type="submit"]').prop('disabled', false);
                  toastr.error(data.message);
                }
              }
            });
            e.preventDefault();
          }
        });

        function sendOTP(mobile){
          $.ajax({
            url:"https://ipapi.co/country_calling_code/",
            success: function(response){
                window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container');
                firebase.auth().signInWithPhoneNumber(response+mobile, window.recaptchaVerifier)
                .then(function(confirmationResult) {
                  if(window.confirmationResult = confirmationResult){
                    $('#mdmodal').modal('show');
                  }
                })
            }
          })
        }
      })

      function verifyOTP(value){
        if(value.length>0){
          var otp = $('#verificationCode').val();
          window.confirmationResult.confirm(otp).then(function(result) {
            $('.modal').modal('hide');
            toastr.success('Mobile Number verified successfully!');
            $('#recaptcha-container').html('');
            $('#form-register')[0].reset();
            Swal.fire(
              'Thank You!',
              'Your request submitted successfully! We will contact you soon.',
              'success'
            )
          },function(error) {
            // alert(error);
            toastr.warning('Invalid OTP!');
            $('#verify_otp_button').prop('disabled', false);
          });
        }
      }
    </script>

    <script>
      var verificationCode = [];
      $(".verification-code--inputs input[type=text]").keyup(function (e) {
        // Get Input for Hidden Field
        $(".verification-code--inputs input[type=text]").each(function (i) {
          verificationCode[i] = $(".verification-code--inputs input[type=text]")[i].value; 
          $('#verificationCode').val(Number(verificationCode.join('')));
          if(i==5){
            verifyOTP(verificationCode[i]);
          }
          // console.log( $('#verificationCode').val() );
        });

        //console.log(event.key, event.which);
        if ($(this).val() > 0) {
          if (event.key == 1 || event.key == 2 || event.key == 3 || event.key == 4 || event.key == 5 || event.key == 6 || event.key == 7 || event.key == 8 || event.key == 9 || event.key == 0) {
            $(this).next().focus();
          }
        }else {
          if(event.key == 'Backspace'){
            $(this).prev().focus();
          }
        }
      });
    </script>
  </body>
</html>