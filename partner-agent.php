<?php
require_once 'includes/db.php';
$pdo = getPDO();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Apply To Be An Agent | Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Apply to become an agent with Filao Adventures and offer your clients the best luxury safaris in Africa.">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    .contact-form-box { background:#fff;padding:40px;border-radius:4px;box-shadow:0 8px 32px rgba(0,0,0,0.05);border-top:3px solid #C49018; margin-top: 40px; }
    .contact-form-box .form-control, .contact-form-box .form-select { border:1px solid #E5DDD0;border-radius:3px;font-family:'Inter',sans-serif;font-size:14px;padding:12px 16px;color:#1C1712;background:#FAF8F4;margin-bottom:20px; width:100%; }
    .contact-form-box .form-control:focus, .contact-form-box .form-select:focus { border-color:#C49018;box-shadow:0 0 0 3px rgba(196,144,24,.14);outline:none;background:#fff; }
    .contact-form-box label { font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#6B6358;font-family:'Inter',sans-serif;margin-bottom:6px; display:block; }
    .contact-form-box h4 { font-family:'Cormorant Garant',serif;font-size:24px;color:#1C1712;margin-bottom:20px; border-bottom:1px solid #E5DDD0; padding-bottom:10px; margin-top:30px; }
    .contact-form-box h4:first-of-type { margin-top: 0; }
    .contact-form-box .form-check { margin-bottom: 20px; }
    .contact-form-box .form-check-label { font-size: 14px; font-weight: 500; letter-spacing: normal; text-transform: none; color: #1C1712; display: inline-block; margin-bottom: 0; }
    .contact-form-box .form-check-input { margin-top: 0.3rem; margin-right: 10px; }
    .contact-form-box .radio-group { margin-bottom: 20px; }
    .contact-form-box .radio-group label { display: inline-block; margin-right: 20px; font-size: 14px; font-weight: 500; text-transform: none; letter-spacing: normal; }
    .contact-form-box .radio-group .radio-title { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #6B6358; margin-bottom: 6px; display: block; }
    .contact-form-box .btn-submit { background:#C49018; color:#fff; border:none; padding:15px 40px; font-family:'Inter',sans-serif; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; border-radius:30px; cursor:pointer; transition:background 0.3s; margin-top: 20px; }
    .contact-form-box .btn-submit:hover { background:#A87B14; }
    
    .rich-content h3 { font-family: 'Cormorant Garant', serif; font-size: 32px; color: #1C1712; margin-top: 40px; margin-bottom: 20px; font-weight: 500; }
    .rich-content p { font-size: 16.5px; color: #4A4340; line-height: 1.85; margin-bottom: 20px; font-family: 'Inter', sans-serif; }
    
    .notice-text { font-size: 13px; color: #6B6358; font-style: italic; margin-bottom: 30px; padding: 15px; background: #FAF8F4; border-left: 3px solid #C49018; }
  </style>
<?php @include_once __DIR__.'/includes/head_tags.php'; ?>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<section class="fa-page-hero" style="background-image:url('images/Filao/Company/tourist behind tour vehicle.jpeg'); height: 400px; position:relative; background-size:cover; background-position:center;">
  <div class="overlay" style="position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.3));"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;text-align:center;position:relative;z-index:2; height:100%; display:flex; flex-direction:column; justify-content:center; padding-top:80px;">
    <h1 style="font-family:'Cormorant Garant',serif;font-size:clamp(40px,5vw,60px);color:#fff;margin-bottom:15px;">Apply To Be An Agent</h1>
    <div class="breadcrumb-fa justify-content-center" style="font-family:'Inter',sans-serif;font-size:13px;color:rgba(255,255,255,0.8);">
      <a href="index" style="color:rgba(255,255,255,0.8);"><i class="fa fa-home"></i></a>
      <span class="bc-sep" style="margin:0 10px;">/</span>
      <span class="bc-current" style="color:#fff;font-weight:600;">Apply To Be An Agent</span>
    </div>
  </div>
</section>

<section class="section-pad bg-cream" style="padding: 60px 0;">
  <div class="container" style="max-width:900px; margin: 0 auto;">
    
    <div class="rich-content" style="text-align: center; margin-bottom: 40px;">
        <h3>Why Become an Agent with Filao Adventures?</h3>
        <p>Working with Filao Adventures allows you to offer your clients unparalleled access to the finest luxury safaris in East Africa. As an agent, you will gain access to our exclusive B2B rates, receive dedicated support from our Nairobi headquarters, and get regular updates on new itineraries, immersive experiences, and elite properties.</p>
        <p>Submit the form below to begin the onboarding process. Upon review, our specialist team will contact you to finalize the partnership and grant you access to our agent portal.</p>
    </div>

    <div class="contact-form-box">
        <p class="notice-text">Note: When you submit this form, it will not automatically collect your details like name and email address unless you provide it yourself in the fields below.</p>
        
        <form action="#" method="POST" id="agentOnboardingForm">
            <!-- Section 1 -->
            <h4>Step 1: Company & Contact Information</h4>
            <div class="row">
                <div class="col-md-12">
                    <label>Company Name *</label>
                    <input type="text" name="company_name" class="form-control" placeholder="Enter your answer" required>
                </div>
                <div class="col-md-12">
                    <label>Street Address *</label>
                    <input type="text" name="street_address" class="form-control" placeholder="Enter your answer" required>
                </div>
                <div class="col-md-6">
                    <label>City/Town *</label>
                    <input type="text" name="city" class="form-control" placeholder="Enter your answer" required>
                </div>
                <div class="col-md-6">
                    <label>State/Province</label>
                    <input type="text" name="state" class="form-control" placeholder="Enter your answer">
                </div>
                <div class="col-md-6">
                    <label>Country *</label>
                    <input type="text" name="country" class="form-control" placeholder="Enter your answer" required>
                </div>
                <div class="col-md-6">
                    <label>Company Registration Number</label>
                    <input type="text" name="company_reg_number" class="form-control" placeholder="Enter your answer">
                </div>
                <div class="col-md-12">
                    <label>Company Web Address</label>
                    <input type="url" name="website" class="form-control" placeholder="https://www.example.com">
                </div>
            </div>

            <!-- Section 2 -->
            <h4>Step 2: Booking Agent Details</h4>
            <div class="row">
                <div class="col-md-6">
                    <label>Contact Name (Booking Agent) *</label>
                    <input type="text" name="agent_name" class="form-control" placeholder="Enter your answer" required>
                </div>
                <div class="col-md-6">
                    <label>Phone Number (Booking Agent) *</label>
                    <input type="number" name="agent_phone" class="form-control" placeholder="The value must be a number" required>
                </div>
                <div class="col-md-6">
                    <label>Email Address (Booking Agent) *</label>
                    <input type="email" name="agent_email" class="form-control" placeholder="Please enter an email" required>
                </div>
                <div class="col-md-6">
                    <label>Mobile Number (Booking Agent)</label>
                    <input type="number" name="agent_mobile" class="form-control" placeholder="The value must be a number">
                </div>
                
                <div class="col-md-12 radio-group mt-3">
                    <span class="radio-title">Are you a Wholesale or Retail Agent? *</span>
                    <label><input type="radio" name="agent_type" value="RETAIL" required> RETAIL</label>
                    <label><input type="radio" name="agent_type" value="WHOLESALE" required> WHOLESALE</label>
                </div>
                <div class="col-md-12 radio-group">
                    <span class="radio-title">Would you like to receive product updates? *</span>
                    <label><input type="radio" name="product_updates" value="YES" required> YES</label>
                    <label><input type="radio" name="product_updates" value="NO" required> NO</label>
                </div>
                <div class="col-md-12" id="updatesEmailWrapper" style="display:none;">
                    <label>Email Address for Product Updates</label>
                    <input type="email" name="updates_email" class="form-control" placeholder="Please enter an email">
                </div>
            </div>

            <!-- Section 3 -->
            <h4>Step 3: 24hrs Emergency Contact</h4>
            <div class="row">
                <div class="col-md-12">
                    <label>Emergency Contact Name *</label>
                    <input type="text" name="emergency_name" class="form-control" placeholder="Enter your answer" required>
                </div>
                <div class="col-md-6">
                    <label>Emergency Contact Number *</label>
                    <input type="number" name="emergency_phone" class="form-control" placeholder="The value must be a number" required>
                </div>
                <div class="col-md-6">
                    <label>Emergency Contact Email Address *</label>
                    <input type="email" name="emergency_email" class="form-control" placeholder="Please enter an email" required>
                </div>
            </div>

            <!-- Section 4 -->
            <h4>Step 4: Required Agreement</h4>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="terms_agreement" value="YES" id="termsAgreement" required>
                <label class="form-check-label" for="termsAgreement">
                    I agree to the Terms and Conditions and <a href="https://www.hemingways-expeditions.com/privacy-policy" target="_blank" style="color:#C49018;text-decoration:underline;">Privacy Policy</a> *
                </label>
            </div>

            <div style="text-align: right; margin-top: 30px;">
                <button type="submit" class="btn-submit">Submit Application</button>
            </div>
        </form>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script>
$(document).ready(function() {
    // Optional: Hide/show updates email based on radio choice
    $('input[name="product_updates"]').change(function() {
        if($(this).val() === 'YES') {
            $('#updatesEmailWrapper').slideDown();
        } else {
            $('#updatesEmailWrapper').slideUp();
            $('input[name="updates_email"]').val('');
        }
    });

    $('#agentOnboardingForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var btn = $(form).find('.btn-submit');
        var originalBtnText = btn.text();
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Submitting...');
        
        var data = new FormData(form);
        var basePath = window.location.hostname === 'localhost' ? '/filao' : '';
        
        fetch(basePath + '/handlers/agent_application.php', { method: 'POST', body: data })
          .then(r => {
            if (!r.ok) {
              return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 300)); });
            }
            return r.text();
          })
          .then(txt => {
            try { var res = JSON.parse(txt); } catch(e) { throw new Error('Invalid JSON: ' + txt.substring(0, 300)); }
            if(res.success) {
              form.reset();
              btn.html(originalBtnText).prop('disabled', false);
              
              var toast = $('<div class="fa-toast-success"><i class="fa fa-check-circle"></i> ' + res.message + '</div>');
              toast.css({
                position:'fixed', top:'30px', right:'30px', zIndex:99999,
                background:'linear-gradient(135deg,#628C52,#4e7040)', color:'#fff',
                padding:'18px 28px', borderRadius:'12px', fontFamily:"'Inter',sans-serif",
                fontSize:'14px', boxShadow:'0 8px 32px rgba(98,140,82,0.35)',
                display:'flex', alignItems:'center', gap:'10px', maxWidth:'420px',
                animation:'slideInRight 0.4s ease'
              });
              $('body').append(toast);
              setTimeout(function(){ toast.fadeOut(400, function(){ toast.remove(); }); }, 5000);
            } else {
              alert('Error: ' + res.message);
              btn.html(originalBtnText).prop('disabled', false);
            }
          })
          .catch(err => {
            console.error(err);
            alert('An error occurred while submitting your application. Please try again.');
            btn.html(originalBtnText).prop('disabled', false);
          });
    });
});
</script>
</body>
</html>
