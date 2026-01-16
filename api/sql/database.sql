---

# 🗄️ `database.sql`

```sql
CREATE DATABASE IF NOT EXISTS simple_cms_api
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE simple_cms_api;

-- ========================
-- ADMINS
-- ========================
CREATE TABLE admins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================
-- ADMIN TOKENS
-- ========================
CREATE TABLE admin_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- ========================
-- COMPANY INFO
-- ========================
CREATE TABLE company_info (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_name VARCHAR(190) NOT NULL,
  logo_url VARCHAR(255),
  phone VARCHAR(50),
  email VARCHAR(190),
  address TEXT,
  website VARCHAR(190),
  map_url VARCHAR(255),
  whatsapp VARCHAR(50),
  facebook VARCHAR(190),
  instagram VARCHAR(190),
  youtube VARCHAR(190),
  linkedin VARCHAR(190),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO company_info (company_name) VALUES ('My Company');

-- ========================
-- WEBSITE THEME
-- ========================
CREATE TABLE website_theme (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  logo_url VARCHAR(255),
  favicon_url VARCHAR(255),
  primary_color VARCHAR(20),
  secondary_color VARCHAR(20),
  background_color VARCHAR(20),
  text_color VARCHAR(20),
  header_bg VARCHAR(20),
  footer_bg VARCHAR(20),
  font_family VARCHAR(120),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO website_theme (primary_color, background_color)
VALUES ('#0ea5e9', '#ffffff');

-- ========================
-- CATEGORIES
-- ========================
CREATE TABLE categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  image_url VARCHAR(255),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================
-- PRODUCTS
-- ========================
CREATE TABLE products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  image_url VARCHAR(255),
  description LONGTEXT,
  short_description TEXT,
  sku VARCHAR(80),
  price DECIMAL(12,2),
  mrp DECIMAL(12,2),
  offer_price DECIMAL(12,2),
  offer_start DATETIME,
  offer_end DATETIME,
  stock INT,
  unit VARCHAR(50),
  category_id BIGINT UNSIGNED,
  brand VARCHAR(120),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id)
    REFERENCES categories(id)
    ON DELETE SET NULL
);

-- ========================
-- PRODUCT IMAGES
-- ========================
CREATE TABLE product_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  is_primary TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ========================
-- BANNERS  
-- ========================
CREATE TABLE banners (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  banner_type ENUM('HOME_TOP','HOME_MIDDLE','SIDEBAR','CONTACT_US','ABOUT_US','NORMAL_IMAGE','HOME_BOTTOM_IMAGE') NOT NULL,
  title VARCHAR(190),
  subtitle VARCHAR(190),
  image_url VARCHAR(255) NOT NULL,
  link_url VARCHAR(255),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  start_date DATE,
  end_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================
-- CMS PAGES
-- ========================
CREATE TABLE pages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key VARCHAR(80) UNIQUE NOT NULL,
  title VARCHAR(190) NOT NULL,
  content LONGTEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO pages (page_key, title)
VALUES ('ABOUT_US','About Us'),
       ('TERMS','Terms & Conditions'),
       ('PRIVACY','Privacy Policy');

       CREATE TABLE home_content (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  about_title VARCHAR(190) DEFAULT NULL,
  about_text JSON DEFAULT NULL,
  why_title VARCHAR(190) DEFAULT NULL,
  map_embed_url TEXT DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO home_content (about_title, why_title)
VALUES ('About Us', 'Why Choose Us');


CREATE TABLE home_why_points (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(190) NOT NULL,
  description TEXT DEFAULT NULL,
  icon VARCHAR(120) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE home_why_subpoints (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  why_point_id BIGINT UNSIGNED NOT NULL,
  question VARCHAR(190) NOT NULL,
  answer TEXT DEFAULT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_why_subpoints_parent
    FOREIGN KEY (why_point_id)
    REFERENCES home_why_points(id)
    ON DELETE CASCADE
);


CREATE TABLE home_testimonials (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  role VARCHAR(120) DEFAULT NULL,
  message TEXT NOT NULL,
  image_url VARCHAR(255) DEFAULT NULL,
  rating TINYINT DEFAULT 5,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);


CREATE TABLE home_videos (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(190) DEFAULT NULL,
  video_url TEXT NOT NULL,
  thumbnail_url VARCHAR(255) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);


CREATE TABLE home_faqs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE contact_requests (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  subject VARCHAR(190) DEFAULT NULL,
  message TEXT NOT NULL,
  is_contacted TINYINT(1) DEFAULT 0,
  contacted_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE package_requests (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(120) NOT NULL,
  last_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  category VARCHAR(190) DEFAULT NULL,
  is_contacted TINYINT(1) DEFAULT 0,
  contacted_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE package_request_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  package_request_id BIGINT UNSIGNED NOT NULL,
  product_name VARCHAR(190) NOT NULL,
  quantity INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_package_request_items
    FOREIGN KEY (package_request_id)
    REFERENCES package_requests(id)
    ON DELETE CASCADE
);


ALTER TABLE package_requests
ADD request_type ENUM(
  'PRODUCT',
  'CATEGORY'
) NOT NULL DEFAULT 'PRODUCT' AFTER phone;

CREATE TABLE home_intro (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  heading VARCHAR(190) NOT NULL,
  sub_heading VARCHAR(255) DEFAULT NULL,
  description LONGTEXT DEFAULT NULL,
  points JSON DEFAULT NULL,
  closing_text LONGTEXT DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO home_intro (heading, sub_heading)
VALUES (
  'Welcome to Green Agile',
  'Your Trusted Partner for High-Quality Paper and Plastic Packaging Solutions'
);

CREATE TABLE home_sections (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  section_key VARCHAR(80) NOT NULL UNIQUE,
  heading VARCHAR(190) NOT NULL,
  sub_heading VARCHAR(255) DEFAULT NULL,
  content LONGTEXT DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE home_section_images (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  section_id BIGINT UNSIGNED NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_home_section_images
    FOREIGN KEY (section_id)
    REFERENCES home_sections(id)
    ON DELETE CASCADE
);


CREATE TABLE home_intro_images (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  image_url VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
