<?php
// includes/hot-offer-popup.php
$pdo_popup = getPDO();
$popupOffer = $pdo_popup->query("SELECT id, title, slug, featured_image, price_from_usd, duration_days FROM tours WHERE status='published' AND is_hot_offer = 1 ORDER BY RAND() LIMIT 1")->fetch();

if ($popupOffer):
  $popupImg = $popupOffer['featured_image'] ? (str_starts_with($popupOffer['featured_image'],'images/') ? '/filao/'.$popupOffer['featured_image'] : '/filao/uploads/'.$popupOffer['featured_image']) : '/filao/images/Filao/East Africa/pexels-balazsimon-15993990.jpg';
?>
<div id="hotOfferPopup" style="display:none; position:fixed; bottom:20px; left:20px; width:340px; background:#fff; border-radius:8px; box-shadow:0 15px 40px rgba(0,0,0,0.2); z-index:9998; overflow:hidden; transform:translateY(20px); opacity:0; transition:all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
  <button id="closeHotOffer" style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.5); border:none; color:#fff; width:28px; height:28px; border-radius:50%; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center; transition:background 0.3s;"><i class="fa fa-times"></i></button>
  <div style="height:160px; position:relative;">
    <img src="<?= htmlspecialchars($popupImg) ?>" style="width:100%; height:100%; object-fit:cover;" alt="Hot Offer">
    <div style="position:absolute; top:10px; left:10px; background:#E21B1B; color:#fff; font-size:10px; font-weight:700; text-transform:uppercase; padding:4px 8px; border-radius:4px; box-shadow:0 2px 4px rgba(0,0,0,0.3);"><i class="fa fa-fire mr-1"></i> Hot Deal</div>
  </div>
  <div style="padding:20px;">
    <h4 style="font-family:'Cormorant Garant',serif; font-size:22px; font-weight:600; color:#1C1712; margin-bottom:8px; line-height:1.2;"><?= htmlspecialchars($popupOffer['title']) ?></h4>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; font-family:'Inter',sans-serif;">
      <span style="font-size:13px; color:#6B6358;"><i class="fa fa-clock-o mr-1" style="color:#C49018;"></i> <?= htmlspecialchars($popupOffer['duration_days']) ?> Days</span>
      <?php if ($popupOffer['price_from_usd']): ?>
      <span style="font-size:16px; font-weight:700; color:#C49018;">$<?= number_format($popupOffer['price_from_usd']) ?></span>
      <?php endif; ?>
    </div>
    <a href="/filao/tours/<?= $popupOffer['slug'] ?>" style="display:block; text-align:center; background:#1C1712; color:#fff; padding:10px; border-radius:4px; font-family:'Inter',sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; text-decoration:none; transition:background 0.3s;">View Offer</a>
  </div>
</div>

<style>
  #closeHotOffer:hover { background:rgba(0,0,0,0.8) !important; }
  #hotOfferPopup a:hover { background:#C49018 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (sessionStorage.getItem('hotOfferShown')) return;

  const popup = document.getElementById('hotOfferPopup');
  const closeBtn = document.getElementById('closeHotOffer');
  let inactivityTimer;
  let isPopupVisible = false;

  // Show popup after 2 seconds
  setTimeout(() => {
    popup.style.display = 'block';
    // Trigger reflow
    void popup.offsetWidth;
    popup.style.opacity = '1';
    popup.style.transform = 'translateY(0)';
    sessionStorage.setItem('hotOfferShown', 'true');
    isPopupVisible = true;
    resetInactivityTimer();
  }, 2000);

  function hidePopup() {
    isPopupVisible = false;
    popup.style.opacity = '0';
    popup.style.transform = 'translateY(20px)';
    clearTimeout(inactivityTimer);
    setTimeout(() => { popup.style.display = 'none'; }, 500);
  }

  function resetInactivityTimer() {
    if (!isPopupVisible) return;
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(hidePopup, 8000);
  }

  closeBtn.addEventListener('click', hidePopup);

  // Reset timer on activity
  ['mousemove', 'scroll', 'keydown', 'click', 'touchstart'].forEach(evt => {
    document.addEventListener(evt, resetInactivityTimer);
  });
});
</script>
<?php endif; ?>
