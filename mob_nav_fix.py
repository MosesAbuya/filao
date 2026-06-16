import re

with open('e:/xampp/htdocs/filao/includes/nav.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Destinations Panel (Level 1)
old_level1 = '''    <!-- Destinations Panel (Level 1) -->
    <div class="rmm-panel" id="rmm-panel-destinations">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>DESTINATIONS</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach($navDestinations as $navDestLoop): ?>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-dest-<?= $navDestLoop['id'] ?>"><?= strtoupper(htmlspecialchars($navDestLoop['name'])) ?> <i class="fa fa-angle-right"></i></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="destinations">VIEW ALL DESTINATIONS</a></li>
      </ul>
    </div>'''

new_level1 = '''    <!-- Destinations Panel (Level 1 - By Region) -->
    <div class="rmm-panel" id="rmm-panel-destinations">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-main"><i class="fa fa-angle-left"></i></button>
        <span>DESTINATIONS</span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php foreach($navRegions as $regionName => $countriesList): ?>
          <li><a href="#" class="rmm-trigger" data-target="rmm-panel-dest-region-<?= md5($regionName) ?>"><?= strtoupper(htmlspecialchars($regionName)) ?> <i class="fa fa-angle-right"></i></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="destinations">VIEW ALL REGIONS</a></li>
      </ul>
    </div>'''

if old_level1 in content:
    content = content.replace(old_level1, new_level1)
    print("Level 1 replaced OK")

# Replace Destinations Level 2
old_level2 = '''    <!-- Destinations Level 2 -->
    <?php foreach ($navDestinations as $navDestLoop): ?>
    <div class="rmm-panel" id="rmm-panel-dest-<?= $navDestLoop['id'] ?>">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-destinations"><i class="fa fa-angle-left"></i></button>
        <span><?= strtoupper(htmlspecialchars($navDestLoop['name'])) ?></span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php if(!empty($navDestLoop['tours'])): foreach ($navDestLoop['tours'] as $t): ?>
          <li><a href="tours/<?= $t['slug'] ?>"><?= strtoupper(htmlspecialchars($t['title'])) ?></a></li>
        <?php endforeach; else: ?>
          <li><a href="#">NO TOURS YET</a></li>
        <?php endif; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="destinations#<?= htmlspecialchars($navDestLoop['slug']) ?>">VIEW ALL IN <?= strtoupper(htmlspecialchars($navDestLoop['name'])) ?></a></li>
      </ul>
    </div>
    <?php endforeach; ?>'''

new_level2 = '''    <!-- Destinations Level 2 (Regions -> Countries) -->
    <?php foreach ($navRegions as $regionName => $countriesList): ?>
    <div class="rmm-panel" id="rmm-panel-dest-region-<?= md5($regionName) ?>">
      <div class="rmm-panel-header">
        <button class="rmm-back-btn" data-target="rmm-panel-destinations"><i class="fa fa-angle-left"></i></button>
        <span><?= strtoupper(htmlspecialchars($regionName)) ?></span>
      </div>
      <ul class="rmm-links rmm-bg-white">
        <?php 
        $uniqueCountries = [];
        foreach ($countriesList as $c) {
          if (!isset($uniqueCountries[$c['country']])) {
            $uniqueCountries[$c['country']] = $c['featured_image'];
          }
        }
        foreach ($uniqueCountries as $cName => $cImg): 
        ?>
          <li><a href="country?name=<?= urlencode($cName) ?>"><?= strtoupper(htmlspecialchars($cName)) ?></a></li>
        <?php endforeach; ?>
        <li class="rmm-view-all" style="margin-top:20px;"><a href="destinations">VIEW ALL IN <?= strtoupper(htmlspecialchars($regionName)) ?></a></li>
      </ul>
    </div>
    <?php endforeach; ?>'''

if old_level2 in content:
    content = content.replace(old_level2, new_level2)
    print("Level 2 replaced OK")

with open('e:/xampp/htdocs/filao/includes/nav.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Mobile nav updated.")
