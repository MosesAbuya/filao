-- ============================================================
-- Filao Adventures: Blog & Recommendations DB Migration
-- ============================================================

-- 1. Blogs table
CREATE TABLE IF NOT EXISTS `blogs` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `title`         VARCHAR(255)  NOT NULL,
  `slug`          VARCHAR(255)  NOT NULL UNIQUE,
  `excerpt`       TEXT,
  `body`          LONGTEXT,
  `featured_image`VARCHAR(500),
  `author`        VARCHAR(100)  DEFAULT 'Filao Adventures',
  `category`      VARCHAR(100),
  `tags`          VARCHAR(500),
  `status`        ENUM('published','draft') DEFAULT 'draft',
  `seo_title`     VARCHAR(255),
  `meta_description` TEXT,
  `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Recommended tours flag on tours table
ALTER TABLE `tours` ADD COLUMN IF NOT EXISTS `is_recommended` TINYINT(1) DEFAULT 0;
ALTER TABLE `tours` ADD COLUMN IF NOT EXISTS `recommended_activity` VARCHAR(150) DEFAULT NULL;
ALTER TABLE `tours` ADD COLUMN IF NOT EXISTS `country` VARCHAR(100) DEFAULT NULL;

-- 3. Seed some sample blog posts
INSERT IGNORE INTO `blogs` (`title`,`slug`,`excerpt`,`body`,`featured_image`,`author`,`category`,`tags`,`status`) VALUES
(
  'The Ultimate Guide to the Great Wildebeest Migration',
  'great-wildebeest-migration-guide',
  'Every year, nearly two million wildebeest, zebra and gazelle thunder across the Serengeti and Maasai Mara in the greatest wildlife spectacle on Earth. Here is everything you need to know.',
  '<p>The Great Wildebeest Migration is a continuous, year-round movement of enormous herds across the Serengeti ecosystem in Tanzania and Kenya''s Maasai Mara. Unlike popular belief, this is not a single seasonal event   it is a perpetual circular journey driven by rainfall and the relentless search for fresh grass.</p><h3>When Is the Best Time to See the River Crossings?</h3><p>The most dramatic phase of the migration   the Mara River crossings   typically occurs between <strong>July and October</strong>. This is when massive columns of wildebeest, desperate to reach the lush pastures of the northern Mara, must brave crocodile-infested waters in terrifying leaps of faith. Witnessing a crossing is an adrenaline-charged experience unlike any other in nature.</p><p>However, nature is unpredictable. The herds follow the rains, not the calendar, so exact crossing dates are never guaranteed. This is why booking with specialists like Filao Adventures, who monitor herd movements in real-time, gives you the best possible chance of witnessing this extraordinary event.</p><h3>The Annual Cycle</h3><ul><li><strong>January – March:</strong> Calving season in the Southern Serengeti. Up to 8,000 calves are born each day   a magnet for predators.</li><li><strong>April – May:</strong> The herds begin their long march northward through the Western Serengeti.</li><li><strong>June – July:</strong> The drama builds as herds congregate at the Grumeti River in Tanzania.</li><li><strong>July – October:</strong> Peak Mara River crossings in Kenya''s Maasai Mara   peak season.</li><li><strong>November – December:</strong> The herds return south to the Serengeti''s short-grass plains.</li></ul><h3>Choosing Your Camp</h3><p>Your choice of camp is crucial. We recommend mobile camps that reposition themselves to be as close as possible to the river crossing points. Fixed lodges can be miles from the action. Ask your Filao specialist about our exclusive private mobile camp packages, positioned right on the Mara River''s edge.</p>',
  'images/Filao/East Africa/Maasai Mara/free-photo-of-majestic-african-elephant-in-kenyan-savanna (6).jpeg',
  'Moses Kamau',
  'Wildlife',
  'migration,wildebeest,maasai mara,serengeti,kenya,tanzania',
  'published'
),
(
  'Top 10 Safari Packing Tips From Our Expert Guides',
  'safari-packing-tips',
  'Packing for a safari can be daunting. Too much and you''ll struggle with luggage restrictions on bush planes. Too little and you''ll be uncomfortable. Our guides reveal exactly what to bring.',
  '<p>After guiding hundreds of safaris, our experts have seen it all   from guests who arrive with five suitcases (a problem on six-seater bush planes with a 15kg limit) to those who forget the basics. Here is our definitive, time-tested guide to packing perfectly for an East African safari.</p><h3>Clothing: The Cardinal Rules</h3><p>Safari clothing requires a delicate balance of practicality and comfort. The most important rule: <strong>avoid bright colours and white.</strong> Blues can attract tsetse flies, and white clothes become safari-red within hours of a dusty game drive. Opt for neutrals   khaki, beige, olive green, and tan.</p><p>Layering is essential. The African bush transitions from cool pre-dawn starts (temperatures can drop to 10°C/50°F) to blazing midday heat within a few hours. Lightweight, breathable fabrics that can be layered are your best friend.</p><ul><li>3-4 pairs of lightweight safari trousers or convertible pants</li><li>4-5 breathable, long-sleeved shirts (UV protection is a bonus)</li><li>A warm fleece or lightweight down jacket for early morning drives</li><li>A wide-brimmed hat   non-negotiable for sun protection</li><li>Comfortable, closed-toe walking shoes for bush walks</li><li>Sandals for use around the camp</li></ul><h3>Gear and Gadgets</h3><p>Your camera setup will define your memories. Even if you''re not a professional photographer, we recommend bringing at minimum a camera with a decent optical zoom (200mm or more). Your smartphone simply will not capture that distant cheetah at the required level of detail.</p><ul><li>Camera + telephoto lens (300-500mm for serious wildlife photography)</li><li>Spare batteries and a universal charger</li><li>Binoculars   8x42 is the gold standard for safari</li><li>A headlamp for navigating camp after dark</li><li>High-SPF sunscreen and insect repellent (DEET-based)</li></ul><h3>Practical Must-Haves</h3><p>Pack any prescription medication in your hand luggage. Many remote camps have limited access to pharmacies. Also carry anti-malaria tablets (consult your doctor in advance), oral rehydration salts, and a basic first aid kit.</p>',
  'images/Filao/East Africa/pexels-kelly-17291020.jpg',
  'Sarah Mwangi',
  'Travel Tips',
  'packing,tips,safari,preparation,guides',
  'published'
),
(
  'Kenya vs Tanzania: Which Safari Destination is Right for You?',
  'kenya-vs-tanzania-safari',
  'Two extraordinary safari destinations separated by an invisible border. Both are legendary. But which is the right fit for your next adventure? Our specialists break it down.',
  '<p>Kenya and Tanzania share the same iconic ecosystems   the Serengeti and Maasai Mara are part of the same vast grassland, divided only by a national boundary. Yet these two safari destinations offer distinctly different experiences, and the best choice depends entirely on what you are looking for.</p><h3>The Case for Kenya</h3><p>Kenya is East Africa''s classic safari destination, offering an extraordinary concentration of wildlife in a relatively compact geographic area. Its biggest advantages:</p><ul><li><strong>Diversity:</strong> From the Maasai Mara''s famous big cats to Amboseli''s elephant herds against Kilimanjaro''s snow-capped peak, Samburu''s rare northern species, and Laikipia''s private conservancies   Kenya packs an enormous variety into one country.</li><li><strong>Infrastructure:</strong> Kenya has excellent road networks and a well-established tourism infrastructure. Getting between parks is relatively easy.</li><li><strong>Beach Proximity:</strong> The Kenyan coast   Diani Beach, Malindi, Watamu   is a short flight from Nairobi, making bush-and-beach combinations seamless.</li></ul><h3>The Case for Tanzania</h3><p>Tanzania boasts the largest concentration of wildlife in Africa and the continent''s most diverse range of habitats. Its flagship attractions:</p><ul><li><strong>Serengeti:</strong> Bigger, wilder, and less crowded than the Maasai Mara, the Serengeti''s vastness is awe-inspiring and the wildlife viewing is world-class year-round.</li><li><strong>Ngorongoro Crater:</strong> The world''s largest intact volcanic caldera is a natural fortress for wildlife, offering an almost guaranteed Big Five sighting.</li><li><strong>Zanzibar:</strong> The spice island offers picture-perfect white-sand beaches, historic Stone Town, and excellent snorkeling, making it the ideal safari extension.</li></ul><h3>Our Recommendation</h3><p>Do both. A classic combination is 5-6 nights on safari in Kenya (Maasai Mara + Amboseli), crossing into Tanzania for 3-4 nights in the Serengeti and Ngorongoro, then finishing with 3 nights on Zanzibar. Contact our specialists to craft this dream itinerary.</p>',
  'images/Filao/East Africa/pexels-droneafrica-13234382.jpg',
  'James Oduya',
  'Destinations',
  'kenya,tanzania,safari,comparison,serengeti,maasai mara',
  'published'
);
