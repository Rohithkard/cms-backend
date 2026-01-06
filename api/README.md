# Simple PHP CMS + Ecommerce API

A plain PHP (no Laravel, no CodeIgniter) CMS & Ecommerce backend with:
- Admin authentication
- Company info
- Website theme (colors, logo, fonts)
- Categories with images
- Products with offers & multiple images
- Banners
- CMS pages (About, Terms, Privacy)
- REST APIs (JSON)
- NGINX + PHP-FPM compatible

---

## 🧰 TECH STACK

- PHP 8.1+ (tested on PHP 8.3)
- MySQL / MariaDB
- NGINX
- No framework (plain PHP)
- PDO
- JWT-like token auth (DB tokens)

---

## 📁 PROJECT STRUCTURE



api/
├── config/
│ ├── config.php
│ └── db.php
├── core/
│ ├── auth.php
│ ├── response.php
│ ├── upload.php
│ ├── url.php
│ └── utils.php
├── controllers/
│ ├── AuthController.php
│ ├── CompanyController.php
│ ├── ThemeController.php
│ ├── CategoryController.php
│ ├── ProductController.php
│ ├── BannerController.php
│ └── PageController.php
├── routes/
│ └── router.php
├── public/
│ └── index.php
├── uploads/ ← must be writable
└── README.md


---

## 🚀 DEPLOYMENT STEPS (NEW SERVER)

### 1️⃣ Copy Project Files

Upload the entire `api/` folder to:



/www/wwwroot/yourdomain.com/api


---

### 2️⃣ Create Database

```bash
mysql -u root -p

CREATE DATABASE simple_cms_api
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
EXIT;

3️⃣ Import Database Schema
mysql -u root -p simple_cms_api < database.sql

4️⃣ Configure Database

Edit:

api/config/config.php

<?php
return [
  "app" => [
    "base_url" => "https://yourdomain.com"
  ],
  "db" => [
    "host" => "localhost",
    "name" => "simple_cms_api",
    "user" => "DB_USER",
    "pass" => "DB_PASSWORD",
    "charset" => "utf8mb4"
  ]
];

5️⃣ Fix Upload Permissions
cd /www/wwwroot/yourdomain.com/api
chown -R www:www uploads
chmod -R 775 uploads

6️⃣ NGINX CONFIG (IMPORTANT)

Set root to API public folder and expose uploads:

root /www/wwwroot/yourdomain.com/api/public;

location ^~ /uploads/ {
    alias /www/wwwroot/yourdomain.com/api/uploads/;
    expires 30d;
}


Reload NGINX:

nginx -t && nginx -s reload

7️⃣ Create Admin User (ONE TIME)

Call this endpoint in browser or Postman:

GET /setup/create-admin?email=admin@example.com&pass=Admin@123&name=Admin


⚠️ Delete or disable this route after setup.

🔐 AUTH FLOW

Login → /auth/login

Receive token

Use header:

Authorization: Bearer {token}

📦 IMAGE UPLOAD RULES

Images stored as relative paths:

/uploads/filename.png


API converts them to full URLs automatically:

https://yourdomain.com/uploads/filename.png

✅ READY ENDPOINTS

/company

/theme

/categories

/products

/products/view

/banners

/pages/{key}

Admin:

/admin/*

🛡️ SECURITY NOTES

Disable setup routes after deployment

Do not expose /api directory listing

Keep uploads/ writable only

📞 SUPPORT

This backend is framework-free and portable.
You can deploy it on:

aaPanel

cPanel

VPS

Docker (optional)

Happy deploying 🚀