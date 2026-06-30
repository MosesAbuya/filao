<?php
require_once 'includes/db.php';
$pdo = getPDO();

// Category filter
$catFilter = trim($_GET['category'] ?? '');

// Pagination
$perPage = 9;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$whereClause = "WHERE b.status='published'" . ($catFilter ? " AND b.category=" . $pdo->quote($catFilter) : '');
$total = $pdo->query("SELECT COUNT(*) FROM blogs b $whereClause")->fetchColumn();
$totalPages = ceil($total / $perPage);

$blogs = $pdo->query("SELECT * FROM blogs b $whereClause ORDER BY b.created_at DESC LIMIT $perPage OFFSET $offset")->fetchAll();

// Categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM blogs WHERE status='published' AND category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Safari Travel Blog &mdash; Filao Adventures</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Expert safari insights, travel tips, wildlife guides and destination inspiration from Filao Adventures.">
  <link rel="icon" href="assets/favicon_io/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/filao-theme.css">
  <style>
    .blog-card { background:#fff; border-radius:6px; overflow:hidden; border:1px solid #E5DDD0; transition:transform 0.3s ease, box-shadow 0.3s ease; height:100%; display:flex; flex-direction:column; }
    .blog-card:hover { transform:translateY(-5px); box-shadow:0 16px 40px rgba(0,0,0,0.1); }
    .blog-card-img { height:220px; overflow:hidden; flex-shrink:0; }
    .blog-card-img img { width:100%; height:100%; object-fit:cover; transition:transform 0.5s ease; }
    .blog-card:hover .blog-card-img img { transform:scale(1.06); }
    .blog-card-body { padding:24px; flex:1; display:flex; flex-direction:column; }
    .blog-category-tag { font-size:10px; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:#C49018; margin-bottom:10px; display:block; font-family:'Inter',sans-serif; }
    .blog-card-title { font-family:'Cormorant Garant',serif; font-size:22px; font-weight:500; color:#1C1712; margin-bottom:12px; line-height:1.3; }
    .blog-card-title a { color:inherit; text-decoration:none; }
    .blog-card-title a:hover { color:#C49018; }
    .blog-excerpt { font-size:14.5px; color:#6B6358; line-height:1.7; flex:1; margin-bottom:16px; font-family:'Inter',sans-serif; }
    .blog-meta { font-size:11.5px; color:#9E9083; letter-spacing:0.05em; font-family:'Inter',sans-serif; display:flex; align-items:center; gap:12px; }
    .blog-read-more { margin-top:16px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#9E3A25; text-decoration:none; font-family:'Inter',sans-serif; transition:color 0.2s; }
    .blog-read-more:hover { color:#C49018; }
    .blog-filter-btn { font-size:11px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; padding:6px 16px; border-radius:30px; border:1px solid #E5DDD0; background:#fff; color:#4A4340; font-family:'Inter',sans-serif; transition:all 0.2s; text-decoration:none; }
    .blog-filter-btn:hover, .blog-filter-btn.active { background:#1C1712; color:#fff; border-color:#1C1712; }
  </style>
<?php @include_once __DIR__.'/includes/head_tags.php'; ?>
</head>
<body>
<?php require_once 'includes/nav.php'; ?>

<section class="fa-page-hero" style="background-image:url('images/Filao/East Africa/pexels-droneafrica-15373902.jpg'); height:450px; position:relative; background-size:cover; background-position:center;">
  <div class="overlay" style="position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.3));"></div>
  <div class="container fa-page-hero-content" style="max-width:1280px;text-align:center;position:relative;z-index:2;height:100%;display:flex;flex-direction:column;justify-content:center;padding-top:60px;">
    <span style="font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#C49018;font-family:'Inter',sans-serif;margin-bottom:12px;display:block;">Safari Stories</span>
    <h1 style="font-family:'Cormorant Garant',serif;font-size:clamp(40px,5vw,64px);color:#fff;margin-bottom:16px;">Insights From The Wild</h1>
    <p style="font-family:'Inter',sans-serif;font-size:18px;color:rgba(255,255,255,0.85);max-width:500px;margin:0 auto;">Expert guides, wildlife insights, travel tips, and destination stories from our safari specialists.</p>
    <div class="breadcrumb-fa justify-content-center mt-4" style="color:rgba(255,255,255,0.8);font-size:13px;">
      <a href="index" style="color:rgba(255,255,255,0.8);"><i class="fa fa-home"></i></a>
      <span class="bc-sep" style="margin:0 10px;">/</span>
      <span style="color:#fff;font-weight:600;">Travel Blog</span>
    </div>
  </div>
</section>

<!-- Filter Bar -->
<div id="blog-container">
  <div style="background:#FAF8F4; border-bottom:1px solid #E5DDD0; padding:20px 0;">
    <div class="container" style="max-width:1280px;">
      <div class="d-flex flex-wrap gap-2 align-items-center blog-filters">
        <a href="blog" class="blog-filter-btn <?= !$catFilter ? 'active' : '' ?>">All Posts</a>
        <?php foreach ($categories as $cat): ?>
        <a href="blog?category=<?= urlencode($cat) ?>" class="blog-filter-btn <?= $catFilter===$cat ? 'active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Blog Grid -->
  <section style="padding:80px 0; background:#F7F5F0; position:relative;">
    <div id="blog-loading" style="display:none; position:absolute; inset:0; background:rgba(247,245,240,0.7); z-index:10; align-items:center; justify-content:center;">
      <i class="fa fa-spinner fa-spin fa-3x" style="color:#C49018;"></i>
    </div>
    <div class="container" style="max-width:1280px;">
      <?php if (empty($blogs)): ?>
      <div class="text-center py-5">
        <h3 style="font-family:'Cormorant Garant',serif;color:#1C1712;">No posts found.</h3>
        <a href="blog" class="btn-filao-cta d-inline-block mt-3">View All Posts</a>
      </div>
      <?php else: ?>
      <div class="row g-4">
        <?php foreach ($blogs as $b): ?>
        <?php
          $imgSrc = $b['featured_image'] ? (str_starts_with($b['featured_image'],'images/') ? $b['featured_image'] : 'uploads/'.$b['featured_image']) : 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg';
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="blog-card">
            <div class="blog-card-img">
              <a href="blog/<?= urlencode($b['slug']) ?>">
                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($b['title']) ?>" loading="lazy">
              </a>
            </div>
            <div class="blog-card-body">
              <span class="blog-category-tag"><?= htmlspecialchars($b['category'] ?: 'Safari Stories') ?></span>
              <h2 class="blog-card-title">
                <a href="blog/<?= urlencode($b['slug']) ?>"><?= htmlspecialchars($b['title']) ?></a>
              </h2>
              <p class="blog-excerpt"><?= htmlspecialchars(mb_substr($b['excerpt'] ?: strip_tags($b['body']), 0, 140)) ?>...</p>
              <div class="blog-meta">
                <span><i class="fa fa-user-o mr-1"></i><?= htmlspecialchars($b['author']) ?></span>
                <span><i class="fa fa-calendar-o mr-1"></i><?= date('d M Y', strtotime($b['created_at'])) ?></span>
              </div>
              <a href="blog/<?= urlencode($b['slug']) ?>" class="blog-read-more">Read Article &rarr;</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="d-flex justify-content-center mt-5 gap-2 blog-pagination">
        <?php for ($p=1; $p<=$totalPages; $p++): ?>
        <a href="?page=<?= $p ?><?= $catFilter ? '&category='.urlencode($catFilter) : '' ?>"
           style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:14px;font-weight:700;font-family:'Inter',sans-serif;text-decoration:none;<?= $p===$page ? 'background:#1C1712;color:#fff;' : 'background:#fff;color:#4A4340;border:1px solid #E5DDD0;' ?>">
          <?= $p ?>
        </a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php require_once 'includes/footer.php'; ?>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="assets/js/filao-nav.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('blog-container');
    
    container.addEventListener('click', function(e) {
        const link = e.target.closest('.blog-filter-btn, .blog-pagination a');
        if (!link) return;
        
        e.preventDefault();
        const url = link.href;
        const loading = document.getElementById('blog-loading');
        if(loading) loading.style.display = 'flex';
        
        // Update URL
        window.history.pushState({path: url}, '', url);
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('blog-container').innerHTML;
                container.innerHTML = newContent;
            })
            .catch(err => {
                window.location.href = url; // fallback
            });
    });
    
    window.addEventListener('popstate', function() {
        window.location.reload();
    });
});
</script>
</body>
</html>
