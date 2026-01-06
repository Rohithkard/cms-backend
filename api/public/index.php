<?php
require_once __DIR__ . "/../routes/router.php";

require_once __DIR__ . "/../controllers/AuthController.php";
require_once __DIR__ . "/../controllers/CompanyController.php";
require_once __DIR__ . "/../controllers/BannerController.php";
require_once __DIR__ . "/../controllers/ProductController.php";
require_once __DIR__ . "/../controllers/PageController.php";
require_once __DIR__ . "/../controllers/CreateAdminController.php";
require_once __DIR__ . "/../controllers/CategoryController.php";
require_once __DIR__ . "/../controllers/CompanyController.php";
require_once __DIR__ . "/../controllers/ThemeController.php";



/**
 * Public APIs
 */
route("POST", "/auth/login", fn() => AuthController::login());

route("GET", "/pages", fn() => PageController::listPublic()); // list all pages
route("GET", "/pages/about-us", fn() => PageController::getByKey("ABOUT_US"));
route("GET", "/pages/more-info", fn() => PageController::getByKey("MORE_INFO"));

route("GET", "/banners", fn() => BannerController::listPublic());
route("GET", "/products", fn() => ProductController::listPublic());
route("GET", "/products/view", fn() => ProductController::viewPublic()); // ?id= OR ?slug=
route("GET", "/setup/create-admin", fn() => CreateAdminController::createAdmin());
route("GET", "/categories", fn() => CategoryController::listPublic());
route("GET", "/company", fn() => CompanyController::getPublic());
route("GET", "/theme", fn() => ThemeController::getPublic());



/**
 * Admin APIs (require Bearer token)
 */
route("POST", "/admin/company/update", fn() => CompanyController::update());
route("POST", "/admin/theme/update", fn() => ThemeController::update());

route("POST", "/admin/upload/image", fn() => CompanyController::uploadImage()); // generic uploader

route("POST", "/admin/banners/create", fn() => BannerController::create());
route("POST", "/admin/banners/update", fn() => BannerController::update());
route("POST", "/admin/banners/delete", fn() => BannerController::delete());
route("GET",  "/admin/banners", fn() => BannerController::listAdmin());

route("POST", "/admin/products/create", fn() => ProductController::create());
route("POST", "/admin/products/update", fn() => ProductController::update());
route("POST", "/admin/products/delete", fn() => ProductController::delete());
route("GET",  "/admin/products", fn() => ProductController::listAdmin());

route("POST", "/admin/pages/update", fn() => PageController::update());

route("POST", "/admin/categories/create", fn() => CategoryController::create());
route("POST", "/admin/categories/update", fn() => CategoryController::update());
route("POST", "/admin/categories/delete", fn() => CategoryController::delete());
route("POST", "/admin/company/update", fn() => CompanyController::update());

route("POST", "/admin/products/upload-image", fn() => ProductController::uploadImage());
route("POST", "/admin/products/delete-image", fn() => ProductController::deleteImage());



not_found();
