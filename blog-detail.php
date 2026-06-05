<?php
require_once 'includes/db.php';
$pdo = getPDO();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: /filao/blog'); exit; }

$blog = $pdo->prepare("SELECT * FROM blogs WHERE slug=? AND status='published'");
$blog->execute([$slug]);
$blog = $blog->fetch();
if (!$blog) { header('Location: /filao/blog'); exit; }

// Related posts (same category, exclude current)
$related = $pdo->prepare("SELECT id, title, slug, excerpt, featured_image, author, created_at, category FROM blogs WHERE status='published' AND id != ? ORDER BY (category=?) DESC, created_at DESC LIMIT 3");
$related->execute([$blog['id'], $blog['category']]);
$related = $related->fetchAll();

$imgSrc = $blog['featured_image'] ? (str_starts_with($blog['featured_image'],'images/') ? $blog['featured_image'] : 'uploads/'.$blog['featured_image']) : 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg';
$readTime = max(1, round(str_word_count(strip_tags($blog['body'])) / 200));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $base_href = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') ? '/filao/' : '/'; ?>
  <base href="<?= $base_href ?>">
  <title><?= htmlspecialchars($blog['seo_title'] ?: $blog['title']) ?> &mdash; Filao Adventures Blog</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="<?= htmlspecialchars($blog['meta_description'] ?: mb_substr(strip_tags($blog['excerpt']),0,160)) ?>">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    /* Hero */
    .bd-hero { height:560px; background-size:cover; background-position:center; position:relative; display:flex; flex-direction:column; justify-content:flex-end; }
    .bd-hero .overlay { position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 60%); }
    .bd-hero-content { position:relative; z-index:2; padding:0 0 40px; max-width:900px; }
    .bd-category { font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;font-family:'Inter',sans-serif;margin-bottom:12px;display:block; }
    .bd-title { font-family:'Cormorant Garant',serif;font-size:clamp(32px,4.5vw,54px);color:#fff;margin-bottom:16px;line-height:1.15;font-weight:400; }
    .bd-meta { font-family:'Inter',sans-serif;font-size:13px;color:rgba(255,255,255,0.75);display:flex;gap:20px;align-items:center;flex-wrap:wrap; }
    .bd-meta i { color:#C49018;margin-right:5px; }
    .hero-breadcrumb { position:absolute;bottom:20px;right:30px;z-index:2;font-family:'Inter',sans-serif;font-size:13px;color:rgba(255,255,255,0.8); }
    .hero-breadcrumb a { color:rgba(255,255,255,0.8);text-decoration:none; }
    .hero-breadcrumb a:hover { color:#C49018; }

    /* Article body */
    .blog-body { font-family:'Inter',sans-serif;font-size:17px;line-height:1.9;color:#2C2420; }
    .blog-body h2 { font-family:'Cormorant Garant',serif;font-size:30px;color:#1C1712;margin-top:44px;margin-bottom:18px;font-weight:500; }
    .blog-body h3 { font-family:'Cormorant Garant',serif;font-size:24px;color:#1C1712;margin-top:36px;margin-bottom:14px;font-weight:500; }
    .blog-body p { margin-bottom:22px; }
    .blog-body ul, .blog-body ol { margin-bottom:22px; padding-left:24px; }
    .blog-body ul li, .blog-body ol li { margin-bottom:10px;line-height:1.7; }
    .blog-body blockquote { font-family:'Cormorant Garant',serif;font-size:24px;font-style:italic;color:#C49018;border-left:4px solid #C49018;padding:24px 32px;background:#FAF8F4;margin:40px 0;line-height:1.6; }
    .blog-body strong { color:#1C1712;font-weight:600; }
    .blog-body img { width:100%;border-radius:6px;margin:30px 0; }

    /* Sidebar */
    .blog-sidebar-box { background:#FAF8F4;border:1px solid #E5DDD0;border-radius:6px;padding:28px;margin-bottom:28px; }
    .blog-sidebar-box h4 { font-family:'Cormorant Garant',serif;font-size:22px;color:#1C1712;margin-bottom:18px;border-bottom:1px solid #E5DDD0;padding-bottom:12px; }

    /* Related card */
    .rel-card { background:#fff;border:1px solid #E5DDD0;border-radius:6px;overflow:hidden;transition:box-shadow 0.3s; }
    .rel-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.08); }
    .rel-card img { width:100%;height:160px;object-fit:cover; }
    .rel-card-body { padding:18px; }
    .rel-card-title { font-family:'Cormorant Garant',serif;font-size:18px;color:#1C1712;font-weight:500;margin-bottom:8px;line-height:1.3; }
    .rel-card-title a { color:inherit;text-decoration:none; }
    .rel-card-title a:hover { color:#C49018; }
  </style>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<!-- HERO -->
<section class="bd-hero" style="background-image:url('<?= htmlspecialchars($imgSrc) ?>');">
  <div class="overlay"></div>
  <div class="container bd-hero-content" style="max-width:1280px;padding-left:24px;padding-right:24px;">
    <span class="bd-category"><?= htmlspecialchars($blog['category'] ?: 'Safari Stories') ?></span>
    <h1 class="bd-title"><?= htmlspecialchars($blog['title']) ?></h1>
    <div class="bd-meta">
      <span><i class="fa fa-user-o"></i><?= htmlspecialchars($blog['author']) ?></span>
      <span><i class="fa fa-calendar-o"></i><?= date('d F Y', strtotime($blog['created_at'])) ?></span>
      <span><i class="fa fa-clock-o"></i><?= $readTime ?> min read</span>
    </div>
  </div>
  <div class="hero-breadcrumb">
    <a href="index"><i class="fa fa-home"></i></a>
    <span style="margin:0 8px;opacity:0.5;">/</span>
    <a href="blog">Blog</a>
    <span style="margin:0 8px;opacity:0.5;">/</span>
    <span style="color:#fff;"><?= htmlspecialchars(mb_substr($blog['title'],0,35)) ?>...</span>
  </div>
</section>

<!-- CONTENT -->
<section style="padding:80px 0;background:#F7F5F0;">
  <div class="container" style="max-width:1280px;">
    <div class="row g-5">

      <!-- Article -->
      <div class="col-lg-9">
        <div style="background:#fff;padding:32px;border-radius:6px;box-shadow:0 4px 20px rgba(0,0,0,0.04);">
          <?php if ($blog['excerpt']): ?>
          <p style="font-size:18px;color:#4A4340;line-height:1.8;font-family:'Cormorant Garant',serif;font-style:italic;border-left:4px solid #C49018;padding-left:20px;margin-bottom:36px;">
            <?= htmlspecialchars($blog['excerpt']) ?>
          </p>
          <?php endif; ?>
          <div class="blog-body">
            <?= $blog['body'] ?>
          </div>

          <!-- Tags -->
          <?php if ($blog['tags']): ?>
          <div style="margin-top:40px;padding-top:24px;border-top:1px solid #E5DDD0;">
            <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#6B6358;font-family:'Inter',sans-serif;margin-right:10px;">Tags:</span>
            <?php foreach (explode(',', $blog['tags']) as $tag): ?>
            <a href="blog?tag=<?= urlencode(trim($tag)) ?>"
               style="display:inline-block;margin:3px;padding:4px 12px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;background:#FAF8F4;border:1px solid #E5DDD0;border-radius:30px;color:#4A4340;text-decoration:none;font-family:'Inter',sans-serif;">
              <?= htmlspecialchars(trim($tag)) ?>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Share -->
          <div style="margin-top:32px;padding-top:24px;border-top:1px solid #E5DDD0;display:flex;align-items:center;gap:12px;">
            <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#6B6358;font-family:'Inter',sans-serif;">Share:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" style="width:36px;height:36px;border-radius:50%;background:#1877f2;color:#fff;display:flex;align-items:center;justify-content:center;"><i class="fa fa-facebook"></i></a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($blog['title']) ?>" target="_blank" style="width:36px;height:36px;border-radius:50%;background:#1da1f2;color:#fff;display:flex;align-items:center;justify-content:center;"><i class="fa fa-twitter"></i></a>
            <a href="https://wa.me/?text=<?= urlencode($blog['title'].' - http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank" style="width:36px;height:36px;border-radius:50%;background:#25d366;color:#fff;display:flex;align-items:center;justify-content:center;"><i class="fa fa-whatsapp"></i></a>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-3">
        <!-- CTA Box -->
        <div style="background:#1C1712;border-radius:6px;padding:32px;margin-bottom:28px;text-align:center;">
          <span style="font-size:11px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#C49018;font-family:'Inter',sans-serif;">Ready to Go?</span>
          <h3 style="font-family:'Cormorant Garant',serif;font-size:26px;color:#fff;margin:12px 0 16px;">Plan Your Safari Today</h3>
          <p style="font-size:14px;color:rgba(255,255,255,0.7);font-family:'Inter',sans-serif;margin-bottom:24px;">Let our specialists craft the perfect itinerary for your African adventure.</p>
          <a href="contact" class="btn-filao-cta" style="display:block;padding:12px;text-align:center;background:#C49018;color:#fff;border-radius:4px;text-decoration:none;font-weight:700;font-family:'Inter',sans-serif;font-size:12px;text-transform:uppercase;letter-spacing:0.1em;">Enquire Now</a>
        </div>

        <!-- Related Posts -->
        <?php if (!empty($related)): ?>
        <div class="blog-sidebar-box">
          <h4>More Stories</h4>
          <?php foreach ($related as $rel): ?>
          <?php $relImg = $rel['featured_image'] ? (str_starts_with($rel['featured_image'],'images/') ? $rel['featured_image'] : 'uploads/'.$rel['featured_image']) : 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg'; ?>
          <div class="rel-card mb-3">
            <img src="<?= htmlspecialchars($relImg) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy">
            <div class="rel-card-body">
              <span style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#C49018;font-family:'Inter',sans-serif;"><?= htmlspecialchars($rel['category'] ?: 'Safari Stories') ?></span>
              <div class="rel-card-title mt-1">
                <a href="blog/<?= urlencode($rel['slug']) ?>"><?= htmlspecialchars($rel['title']) ?></a>
              </div>
              <small style="font-size:11px;color:#9E9083;font-family:'Inter',sans-serif;"><?= date('d M Y', strtotime($rel['created_at'])) ?></small>
            </div>
          </div>
          <?php endforeach; ?>
          <a href="blog" style="display:block;text-align:center;margin-top:8px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#9E3A25;text-decoration:none;font-family:'Inter',sans-serif;">View All Posts &rarr;</a>
        </div>
        <?php endif; ?>

        <!-- Categories -->
        <div class="blog-sidebar-box">
          <h4>Categories</h4>
          <?php
          $cats = $pdo->query("SELECT category, COUNT(*) as cnt FROM blogs WHERE status='published' GROUP BY category ORDER BY cnt DESC")->fetchAll();
          foreach ($cats as $c): ?>
          <a href="blog?category=<?= urlencode($c['category']) ?>"
             style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #E5DDD0;text-decoration:none;font-family:'Inter',sans-serif;font-size:14px;color:#4A4340;">
            <?= htmlspecialchars($c['category']) ?>
            <span style="font-size:11px;font-weight:700;background:#FAF8F4;padding:2px 8px;border-radius:20px;color:#6B6358;"><?= $c['cnt'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
</body>
</html>
