<?php
require_once __DIR__ . '/auth_guard.php';

$pdo = getPDO();

// Get dependencies
$categories = $pdo->query("SELECT id, name FROM tour_categories ORDER BY display_order")->fetchAll();
$activities = $pdo->query("SELECT id, name FROM taxonomies WHERE type = 'activity' ORDER BY name")->fetchAll();
$features = $pdo->query("SELECT id, name FROM taxonomies WHERE type = 'feature' ORDER BY name")->fetchAll();
$destinations = $pdo->query("SELECT id, name FROM destinations ORDER BY name")->fetchAll();
$accommodations = $pdo->query("SELECT id, name FROM accommodations ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect('create-tour.php');
    }

    try {
        $pdo->beginTransaction();

        $title = trim($_POST['title']);
        $slug = !empty($_POST['slug']) ? generateSlug($_POST['slug']) : generateSlug($title);
        $slug = uniqueSlug('tours', $slug);

        $status = $_POST['status'] ?? 'draft';
        // Editors are allowed to publish as per user request

        // Handle image
        $featuredImage = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $uploaded = handleImageUpload('featured_image', 'tours');
            if ($uploaded) {
                $featuredImage = $uploaded;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO tours 
            (title, slug, description, excerpt, duration_days, price_from_usd, status, start_destination_id, end_destination_id, featured_image, 
            focus_keyphrase, seo_title, meta_description, highlights, inclusions, exclusions,
            is_hot_offer, is_active_ad, is_joining_tour, price_1_pax, price_2_pax, price_3_pax, price_4_pax, price_5_pax, price_6_pax,
            price_child_1_pax, price_child_2_pax, price_child_3_pax, price_child_4_pax, price_child_5_pax, price_child_6_pax) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->execute([
            $title, $slug, $_POST['description'] ?? '', $_POST['excerpt'] ?? '', 
            (int)$_POST['duration_days'], (float)$_POST['price_from_usd'], $status, 
            !empty($_POST['start_destination_id']) ? (int)$_POST['start_destination_id'] : null,
            !empty($_POST['end_destination_id']) ? (int)$_POST['end_destination_id'] : null,
            $featuredImage,
            $_POST['focus_keyphrase'] ?? '', $_POST['seo_title'] ?? '', $_POST['meta_description'] ?? '',
            $_POST['highlights'] ?? '', $_POST['inclusions'] ?? '', $_POST['exclusions'] ?? '',
            isset($_POST['is_hot_offer']) ? 1 : 0,
            isset($_POST['is_active_ad']) ? 1 : 0,
            isset($_POST['is_joining_tour']) ? 1 : 0,
            !empty($_POST['price_1_pax']) ? (float)$_POST['price_1_pax'] : null,
            !empty($_POST['price_2_pax']) ? (float)$_POST['price_2_pax'] : null,
            !empty($_POST['price_3_pax']) ? (float)$_POST['price_3_pax'] : null,
            !empty($_POST['price_4_pax']) ? (float)$_POST['price_4_pax'] : null,
            !empty($_POST['price_5_pax']) ? (float)$_POST['price_5_pax'] : null,
            !empty($_POST['price_6_pax']) ? (float)$_POST['price_6_pax'] : null,
            !empty($_POST['price_child_1_pax']) ? (float)$_POST['price_child_1_pax'] : null,
            !empty($_POST['price_child_2_pax']) ? (float)$_POST['price_child_2_pax'] : null,
            !empty($_POST['price_child_3_pax']) ? (float)$_POST['price_child_3_pax'] : null,
            !empty($_POST['price_child_4_pax']) ? (float)$_POST['price_child_4_pax'] : null,
            !empty($_POST['price_child_5_pax']) ? (float)$_POST['price_child_5_pax'] : null,
            !empty($_POST['price_child_6_pax']) ? (float)$_POST['price_child_6_pax'] : null
        ]);
        
        $tourId = $pdo->lastInsertId();

        // Handle gallery images
        $galleryPaths = handleMultipleImageUpload('gallery_images', 'tours');
        if (!empty($galleryPaths)) {
            $galleryStmt = $pdo->prepare("INSERT INTO tour_images (tour_id, image_path, sort_order) VALUES (?, ?, ?)");
            foreach ($galleryPaths as $idx => $path) {
                $galleryStmt->execute([$tourId, $path, $idx]);
            }
        }

        // Categories pivot
        if (!empty($_POST['categories']) && is_array($_POST['categories'])) {
            $catStmt = $pdo->prepare("INSERT INTO tour_category_pivot (tour_id, category_id) VALUES (?, ?)");
            foreach ($_POST['categories'] as $catId) {
                $catStmt->execute([$tourId, (int)$catId]);
            }
        }

        // Taxonomies pivot
        if (!empty($_POST['taxonomies']) && is_array($_POST['taxonomies'])) {
            $taxStmt = $pdo->prepare("INSERT INTO tour_taxonomy_pivot (tour_id, taxonomy_id) VALUES (?, ?)");
            foreach ($_POST['taxonomies'] as $taxId) {
                $taxStmt->execute([$tourId, (int)$taxId]);
            }
        }

        // Seasonality
        if (!empty($_POST['season']) && is_array($_POST['season'])) {
            $seasonStmt = $pdo->prepare("INSERT INTO tour_seasonality (tour_id, month_number, rating) VALUES (?, ?, ?)");
            foreach ($_POST['season'] as $month => $rating) {
                $seasonStmt->execute([$tourId, (int)$month, $rating]);
            }
        }

        // Itinerary Steps
        if (!empty($_POST['steps']) && is_array($_POST['steps'])) {
            $stepStmt = $pdo->prepare("INSERT INTO itinerary_steps 
                (tour_id, step_number, destination_id, nights_count, step_title, step_description, accommodation_id, transit_mode, transit_duration, step_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            foreach ($_POST['steps'] as $index => $step) {
                if (empty($step['destination_id'])) {
                    throw new Exception("Please select a destination for all itinerary steps.");
                }
                $stepImage = null;
                if (!empty($_FILES['steps']['name'][$index]['image'])) {
                    // Manual file upload logic for steps array
                    $fileArray = [
                        'name' => $_FILES['steps']['name'][$index]['image'],
                        'type' => $_FILES['steps']['type'][$index]['image'],
                        'tmp_name' => $_FILES['steps']['tmp_name'][$index]['image'],
                        'error' => $_FILES['steps']['error'][$index]['image'],
                        'size' => $_FILES['steps']['size'][$index]['image'],
                    ];
                    $_FILES['temp_step_img'] = $fileArray;
                    $stepImgUploaded = handleImageUpload('temp_step_img', 'steps');
                    if ($stepImgUploaded) $stepImage = $stepImgUploaded;
                }

                $stepStmt->execute([
                    $tourId, 
                    (int)($index + 1), // order
                    (int)$step['destination_id'],
                    (int)$step['nights'],
                    $step['title'] ?? '',
                    $step['description'] ?? '',
                    !empty($step['accommodation_id']) ? (int)$step['accommodation_id'] : null,
                    $step['transit_mode'] ?? '',
                    $step['transit_duration'] ?? '',
                    $stepImage
                ]);
            }
        }

        $pdo->commit();
        setFlash('success', 'Tour created successfully.');
        redirect('tours.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Error creating tour: ' . $e->getMessage());
    }
}

$pageTitle = 'Create New Tour';
include 'partials/head.php';
include 'partials/sidebar.php';
?>

<div class="admin-main">
    <?php include 'partials/navbar.php'; ?>
    
    <div class="dashboard-content">
        <form action="create-tour.php" method="POST" enctype="multipart/form-data" id="tourForm">
            <?= csrfField() ?>
            <div class="container-fluid px-3 px-lg-4">
                
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <div class="page-icon"><i class="bi bi-map"></i></div>
                        <div>
                            <span class="eyebrow">Tours & Safaris</span>
                            <h1>Create New Tour</h1>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a href="tours.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save Tour</button>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-pills tour-nav-tabs" id="tourTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button"><i class="bi bi-info-circle"></i> General</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-logistics" type="button"><i class="bi bi-journal-text"></i> Details & Pricing</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-itinerary" type="button"><i class="bi bi-list-ol"></i> Itinerary Builder</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-media" type="button"><i class="bi bi-images"></i> Media</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button"><i class="bi bi-search"></i> SEO</button></li>
                </ul>

                <div class="tab-content" id="tourTabsContent">
                    
                    <!-- General Tab -->
                    <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="panel mb-4">
                                    <div class="mb-4">
                                        <label for="title" class="form-label">Tour Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg font-serif" id="title" name="title" placeholder="e.g. 7-Day Great Migration Safari">
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Permanent Link (Slug)</label>
                                        <div class="input-group slug-group">
                                            <span class="slug-prefix">filaoadventures.co.ke/tour/</span>
                                            <input type="text" class="form-control slug-input" id="slug" name="slug" readonly>
                                            <button type="button" class="slug-lock-btn" id="toggleSlug" title="Edit slug"><i class="bi bi-lock-fill"></i></button>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="editor-label">Full Description <span class="text-danger">*</span></label>
                                        <div class="editor-wrapper">
                                            <textarea id="description" name="description" class="editor-full"></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label for="excerpt" class="form-label">Short Excerpt (Cards & Listings)</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Brief summary of the tour..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="panel mb-4">
                                    <div class="panel-header mb-3"><h2>Publishing</h2></div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select mb-3">
                                            <option value="draft">Draft</option>
                                            <option value="published">Published</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_hot_offer" name="is_hot_offer" value="1">
                                            <label class="form-check-label" for="is_hot_offer">Mark as Hot Offer</label>
                                        </div>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_active_ad" name="is_active_ad" value="1">
                                            <label class="form-check-label" for="is_active_ad">Active Ad</label>
                                        </div>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_joining_tour" name="is_joining_tour" value="1">
                                            <label class="form-check-label" for="is_joining_tour">Joining Tour</label>
                                        </div>
                                        <hr>
                                        <label class="form-label mt-2">Map Starting Point</label>
                                        <select name="start_destination_id" id="start_destination_id" class="form-select">
                                            <option value="">Default (Nairobi)</option>
                                            <?php foreach ($destinations as $d): ?>
                                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <label class="form-label mt-3">Map Ending Point</label>
                                        <select name="end_destination_id" id="end_destination_id" class="form-select">
                                            <option value="">Default (Same as Start)</option>
                                            <?php foreach ($destinations as $d): ?>
                                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted d-block mt-1">If blank, the Starting Point is used as the end.</small>
                                    </div>
<script>
document.getElementById('start_destination_id').addEventListener('change', function() {
    var endSelect = document.getElementById('end_destination_id');
    if (!endSelect.value || endSelect.dataset.autoSynced === 'true') {
        endSelect.value = this.value;
        endSelect.dataset.autoSynced = 'true';
    }
});
document.getElementById('end_destination_id').addEventListener('change', function() {
    this.dataset.autoSynced = 'false';
});
</script>
                                </div>
                                <div class="panel mb-4">
                                    <div class="panel-header mb-3"><h2>Categories</h2></div>
                                    <div class="category-chip-grid">
                                        <?php foreach($categories as $cat): ?>
                                            <div class="category-chip">
                                                <input type="checkbox" name="categories[]" id="cat_<?= $cat['id'] ?>" value="<?= $cat['id'] ?>">
                                                <label for="cat_<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="panel">
                                    <div class="panel-header mb-3"><h2>Taxonomies</h2></div>
                                    
                                    <label class="form-label text-muted small text-uppercase">Activities</label>
                                    <div class="category-chip-grid mb-3">
                                        <?php foreach($activities as $tax): ?>
                                            <div class="category-chip">
                                                <input type="checkbox" name="taxonomies[]" id="tax_<?= $tax['id'] ?>" value="<?= $tax['id'] ?>">
                                                <label for="tax_<?= $tax['id'] ?>"><?= sanitize($tax['name']) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <label class="form-label text-muted small text-uppercase">Features</label>
                                    <div class="category-chip-grid">
                                        <?php foreach($features as $tax): ?>
                                            <div class="category-chip">
                                                <input type="checkbox" name="taxonomies[]" id="tax_<?= $tax['id'] ?>" value="<?= $tax['id'] ?>">
                                                <label for="tax_<?= $tax['id'] ?>"><?= sanitize($tax['name']) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details & Pricing -->
                    <div class="tab-pane fade" id="tab-logistics" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="panel mb-4">
                                    <div class="panel-header"><h2>Highlights & Features</h2></div>
                                    <div class="mb-4">
                                        <label class="editor-label">Tour Highlights (Bullet Points)</label>
                                        <div class="editor-wrapper"><textarea name="highlights" class="editor-simple"></textarea></div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="editor-label">Inclusions</label>
                                        <div class="editor-wrapper"><textarea name="inclusions" class="editor-simple"></textarea></div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="editor-label">Exclusions</label>
                                        <div class="editor-wrapper"><textarea name="exclusions" class="editor-simple"></textarea></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="panel mb-4">
                                    <div class="panel-header"><h2>Pricing & Logistics</h2></div>
                                    <div class="mb-3">
                                        <label class="form-label">Duration (Days)</label>
                                        <input type="number" class="form-control" name="duration_days" min="1" value="1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Starting Price (USD)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">$</span>
                                            <input type="number" class="form-control" name="price_from_usd" step="0.01" min="0" value="0.00">
                                        </div>
                                        <small class="text-muted d-block mt-1">This is the 'From' price shown on cards.</small>
                                    </div>
                                    <hr>
                                    <h6 class="mb-3">Detailed Pricing (Adults)</h6>
                                    <div class="row g-2 mb-3">
                                        <?php for($i=1; $i<=6; $i++): ?>
                                        <div class="col-6 mb-2">
                                            <label class="form-label small"><?= $i ?> Adult<?= $i>1?'s':'' ?> (USD)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" class="form-control" name="price_<?= $i ?>_pax" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                    <h6 class="mb-3">Detailed Pricing (Children < 12 yrs)</h6>
                                    <div class="row g-2">
                                        <?php for($i=1; $i<=6; $i++): ?>
                                        <div class="col-6 mb-2">
                                            <label class="form-label small"><?= $i ?> Child<?= $i>1?'ren':'' ?> (USD)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light">$</span>
                                                <input type="number" class="form-control" name="price_child_<?= $i ?>_pax" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="panel">
                                    <div class="panel-header"><h2>Seasonality</h2></div>
                                    <div class="seasonality-grid">
                                        <?php for($m=1; $m<=12; $m++): ?>
                                            <div class="season-cell">
                                                <span class="month-label"><?= substr(monthName($m), 0, 3) ?></span>
                                                <select name="season[<?= $m ?>]" class="season-select" onchange="this.className='season-select season-select-'+this.value">
                                                    <option value="Best">Best</option>
                                                    <option value="Good" selected>Good</option>
                                                    <option value="Mixed">Mixed</option>
                                                    <option value="Low">Low</option>
                                                </select>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Itinerary Builder Tab -->
                    <div class="tab-pane fade" id="tab-itinerary" role="tabpanel">
                        <div class="panel mb-4">
                            <div class="panel-header">
                                <div>
                                    <h2>Itinerary Steps</h2>
                                    <p>Drag and drop to reorder. Images are optional per step.</p>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addStepBtn">
                                    <i class="bi bi-plus-lg"></i> Add Day
                                </button>
                            </div>

                            <div class="itinerary-builder" id="itineraryContainer">
                                <div class="step-connector d-none d-md-block"></div>
                                <!-- Steps will be appended here via JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Media Tab -->
                    <div class="tab-pane fade" id="tab-media" role="tabpanel">
                        <div class="panel">
                            <div class="panel-header"><h2>Featured Image</h2></div>
                            <div class="img-upload-zone" id="featuredImgZone">
                                <i class="bi bi-cloud-arrow-up img-upload-icon"></i>
                                <p>Drag and drop a high-res image, or click to browse</p>
                                <p class="small text-muted mt-1 mb-0">Recommended: 1920x1080px (JPG/WEBP)</p>
                                <input type="file" id="featured_image" name="featured_image" accept="image/jpeg, image/png, image/webp">
                            </div>
                            <div id="featuredPreviewContainer" class="d-none mt-3">
                                <div class="img-preview-wrap">
                                    <img src="" alt="Preview" class="img-preview" id="featuredPreview">
                                    <button type="button" class="img-remove-btn" id="removeFeaturedImg"><i class="bi bi-x"></i></button>
                                </div>
                            </div>
                            
                            <div class="panel-header mt-5"><h2>Tour Gallery</h2></div>
                            <div class="mb-3">
                                <label class="form-label">Upload Additional Images</label>
                                <input type="file" class="form-control" name="gallery_images[]" multiple accept="image/jpeg, image/png, image/webp">
                                <p class="text-muted small mt-1">Select multiple images to create a stunning gallery for this tour.</p>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Tab -->
                    <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                        <div class="panel">
                            <div class="panel-header"><h2>Search Engine Optimization</h2></div>
                            <div class="mb-4">
                                <label class="form-label">Focus Keyphrase</label>
                                <input type="text" class="form-control" name="focus_keyphrase" placeholder="e.g. Kenya Migration Safari">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">SEO Title</label>
                                <input type="text" class="form-control" name="seo_title" id="seo_title" placeholder="Leave blank to use tour title">
                                <div class="char-counter" id="seo_title_counter">0 / 60 chars</div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Meta Description</label>
                                <textarea class="form-control" name="meta_description" id="meta_description" rows="3" placeholder="Compelling description for search results..."></textarea>
                                <div class="char-counter" id="meta_description_counter">0 / 160 chars</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
    
    <?php include 'partials/footer.php'; ?>
</div>

<!-- Raw data for JS Builder -->
<script>
    const destinations = <?= json_encode($destinations) ?>;
    const accommodations = <?= json_encode($accommodations) ?>;
</script>
<?php include 'partials/scripts.php'; ?>
<script src="assets/js/tour-builder.js"></script>
</body>
</html>
