ALTER TABLE filao_adventures.taxonomies ADD COLUMN image VARCHAR(500) NULL AFTER slug;
ALTER TABLE filao_adventures.destinations ADD COLUMN region VARCHAR(100) NULL AFTER country;
CREATE TABLE IF NOT EXISTS filao_adventures.tour_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES filao_adventures.tours(id) ON DELETE CASCADE
);
