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
require_once __DIR__ . "/../controllers/HomeController.php";
require_once __DIR__ . "/../controllers/ContactController.php";
require_once __DIR__ . "/../controllers/PackageRequestController.php";
require_once __DIR__ . "/../controllers/AboutUsHomeController.php";



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
route("GET", "/setup/create-database", fn() => CreateAdminController::install());
route("GET", "/setup/create-admin", fn() => CreateAdminController::createAdmin());
route("GET", "/categories", fn() => CategoryController::listPublic());
route("GET", "/company", fn() => CompanyController::getPublic());
route("GET", "/theme", fn() => ThemeController::getPublic());
route("POST", "/contact", fn() => ContactController::create());
route("GET", "/home", fn()=>HomeController::get());

route("POST", "/package-request", fn()=>PackageRequestController::create());

route("GET", "/home/intro", fn() => HomeController::intro());



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

// Videos
route("GET",  "/admin/home/videos", fn()=>HomeController::listVideos());
route("POST", "/admin/home/videos/create", fn()=>HomeController::createVideo());
route("POST", "/admin/home/videos/update", fn()=>HomeController::updateVideo());
route("POST", "/admin/home/videos/delete", fn()=>HomeController::deleteVideo());

// FAQ
route("GET",  "/admin/home/faqs", fn()=>HomeController::listFaqs());
route("POST", "/admin/home/faqs/create", fn()=>HomeController::createFaq());
route("POST", "/admin/home/faqs/update", fn()=>HomeController::updateFaq());
route("POST", "/admin/home/faqs/delete", fn()=>HomeController::deleteFaq());



// Admin – Home content
route("GET",  "/admin/home/content", fn()=>HomeController::listContent());
route("POST", "/admin/home/content/update", fn()=>HomeController::updateContent());

// Admin – Why choose us
route("GET",  "/admin/home/why", fn()=>HomeController::listWhy());
route("POST", "/admin/home/why/create", fn()=>HomeController::createWhy());
route("POST", "/admin/home/why/update", fn()=>HomeController::updateWhy());
route("POST", "/admin/home/why/delete", fn()=>HomeController::deleteWhy());

// Admin – Testimonials
route("GET",  "/admin/home/testimonials", fn()=>HomeController::listTestimonials());
route("POST", "/admin/home/testimonials/create", fn()=>HomeController::createTestimonial());
route("POST", "/admin/home/testimonials/update", fn()=>HomeController::updateTestimonial());
route("POST", "/admin/home/testimonials/delete", fn()=>HomeController::deleteTestimonial());
route("GET",  "/admin/contact", fn() => ContactController::list());
route("POST", "/admin/contact/mark", fn() => ContactController::markContacted());
route("POST", "/admin/contact/delete", fn() => ContactController::delete());
route("POST", "/admin/why-subpoints/create", fn()=>HomeController::createWhySub());
route("POST", "/admin/why-subpoints/update", fn()=>HomeController::updateWhySub());
route("POST", "/admin/why-subpoints/delete", fn()=>HomeController::deleteWhySub());


route("GET",  "/admin/package-requests", fn()=>PackageRequestController::list());
route("POST", "/admin/package-requests/mark", fn()=>PackageRequestController::markContacted());
route("POST", "/admin/package-requests/delete", fn()=>PackageRequestController::delete());
route("POST", "/admin/home/intro/update", fn() => HomeController::updateIntro());


// Public
route("GET", "/home/sections", fn()=>AboutUshHomeController::listPublic());

// Admin
route("GET",  "/admin/home/sections", fn()=>AboutUshHomeController::listAdmin());
route("POST", "/admin/home/sections/create", fn()=>AboutUshHomeController::create());
route("POST", "/admin/home/sections/update", fn()=>AboutUshHomeController::update());
route("POST", "/admin/home/sections/delete", fn()=>AboutUshHomeController::delete());

// Images
route("POST", "/admin/home/sections/images/upload", fn()=>AboutUshHomeController::uploadImage());
route("POST", "/admin/home/sections/images/delete", fn()=>AboutUshHomeController::deleteImage());


route("POST", "/admin/home/intro/update", fn()=>HomeController::updateIntro());
route("POST", "/admin/home/intro/images/upload", fn()=>HomeController::uploadIntroImage());
route("POST", "/admin/home/intro/images/delete", fn()=>HomeController::deleteIntroImage());

not_found();
