<?php

include '../../Controllers/CategoryController.php';
require_once __DIR__ . '/../../Models/Category.php';
$error = "";
$categoryController = new CategoryController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["title"]) && isset($_POST["description"])) {
        if (!empty($_POST["title"])) {
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';

            $category = new Category($title, $description);
            $categoryController->addCategory($category);

            // Redirection vers la liste des catégories (à créer)
            header('Location: categoryList.php');
            exit;
        } else {
            $error = "Le titre est obligatoire.";
        }
    } else {
        $error = "Informations manquantes.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from booking.webestica.com/agent-dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 20 Feb 2024 15:41:29 GMT -->

<head>
    <title>Booking - Multipurpose Online Booking Theme</title>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Webestica.com">
    <meta name="description" content="Booking - Multipurpose Online Booking Theme">

    <!-- Dark mode -->
    <script>
        const storedTheme = localStorage.getItem('theme')

        const getPreferredTheme = () => {
            if (storedTheme) {
                return storedTheme
            }
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
        }

        const setTheme = function(theme) {
            if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark')
            } else {
                document.documentElement.setAttribute('data-bs-theme', theme)
            }
        }

        setTheme(getPreferredTheme())

        window.addEventListener('DOMContentLoaded', () => {
            var el = document.querySelector('.theme-icon-active');
            if (el != 'undefined' && el != null) {
                const showActiveTheme = theme => {
                    const activeThemeIcon = document.querySelector('.theme-icon-active use')
                    const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
                    const svgOfActiveBtn = btnToActive.querySelector('.mode-switch use').getAttribute('href')

                    document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
                        element.classList.remove('active')
                    })

                    btnToActive.classList.add('active')
                    activeThemeIcon.setAttribute('href', svgOfActiveBtn)
                }

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                    if (storedTheme !== 'light' || storedTheme !== 'dark') {
                        setTheme(getPreferredTheme())
                    }
                })

                showActiveTheme(getPreferredTheme())

                document.querySelectorAll('[data-bs-theme-value]')
                    .forEach(toggle => {
                        toggle.addEventListener('click', () => {
                            const theme = toggle.getAttribute('data-bs-theme-value')
                            localStorage.setItem('theme', theme)
                            setTheme(theme)
                            showActiveTheme(theme)
                        })
                    })

            }
        })
    </script>

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&amp;family=Poppins:wght@400;500;700&amp;display=swap">

    <!-- Plugins CSS -->
    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/apexcharts/css/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/choices/css/choices.min.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">

</head>

<body>


    <!-- Header END -->

    <!-- **************** MAIN CONTENT START **************** -->
    <main>

        <!-- =======================
Menu item START -->
        <section class="pt-4">
            <div class="container">
                <div class="card rounded-3 border p-3 pb-2">
                    <!-- Avatar and info START -->
                    <div class="d-sm-flex align-items-center">
                        <div class="avatar avatar-xl mb-2 mb-sm-0">
                            <img class="avatar-img rounded-circle" src="/Views/assets/images/avatar/01.jpg" alt="">
                        </div>
                        <h4 class="mb-2 mb-sm-0 ms-sm-3"><span class="fw-light">Hi</span> Jacqueline Miller</h4>
                        <a href="add-listing.html" class="btn btn-sm btn-primary-soft mb-0 ms-auto flex-shrink-0"><i class="bi bi-plus-lg fa-fw me-2"></i>Add New Listing</a>
                    </div>
                    <!-- Avatar and info START -->

                    <!-- Responsive navbar toggler -->
                    <button class="btn btn-primary w-100 d-block d-xl-none mt-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashboardMenu" aria-controls="dashboardMenu">
                        <i class="bi bi-list"></i> Dashboard Menu
                    </button>

                    <!-- Nav links START -->
                    <div class="offcanvas-xl offcanvas-end mt-xl-3" tabindex="-1" id="dashboardMenu">
                        <div class="offcanvas-header border-bottom p-3">
                            <h5 class="offcanvas-title">Menu</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#dashboardMenu" aria-label="Close"></button>
                        </div>
                        <!-- Offcanvas body -->
                        <div class="offcanvas-body p-3 p-xl-0">
                            <!-- Nav item -->
                            <div class="navbar navbar-expand-xl">
                                <ul class="navbar-nav navbar-offcanvas-menu">

                                    <li class="nav-item"> <a class="nav-link active" href="agent-dashboard.html"><i class="bi bi-house-door fa-fw me-1"></i>Dashboard</a> </li>

                                    <li class="nav-item"> <a class="nav-link" href="agent-listings.html"><i class="bi bi-journals fa-fw me-1"></i>Listings</a> </li>

                                    <li class="nav-item"> <a class="nav-link" href="agent-bookings.html"><i class="bi bi-bookmark-heart fa-fw me-1"></i>Bookings</a> </li>

                                    <li class="nav-item"> <a class="nav-link" href="agent-activities.html"><i class="bi bi-bell fa-fw me-1"></i>Activities</a> </li>

                                    <li class="nav-item"> <a class="nav-link" href="agent-earnings.html"><i class="bi bi-graph-up-arrow fa-fw me-1"></i>Earnings</a> </li>

                                    <li class="nav-item"> <a class="nav-link" href="agent-reviews.html"><i class="bi bi-star fa-fw me-1"></i>Reviews</a></li>

                                    <li> <a class="nav-link" href="agent-settings.html"><i class="bi bi-gear fa-fw me-1"></i>Settings</a></li>

                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="dropdoanMenu" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="bi bi-list-ul fa-fw me-1"></i>Dropdown
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="dropdoanMenu">
                                            <!-- Dropdown menu -->
                                            <li> <a class="dropdown-item" href="#">Item 1</a></li>
                                            <li> <a class="dropdown-item" href="#">Item 2</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Nav links END -->
                </div>
            </div>
        </section>
        <!-- =======================
Menu item END -->

        <!-- =======================
Content START -->
        <section class="pt-0">
            <div class="container vstack gap-4">
                <!-- Title START -->
                <div class="row">
                    <div class="col-12">
                        <h1 class="fs-4 mb-0"><i class="bi bi-house-door fa-fw me-1"></i>Ajouter categories</h1>
                    </div>
                </div>
                <div class="content mt-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Titre de la catégorie">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Description optionnelle"></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <!-- =======================
Content END -->

    </main>

    <div class="back-top"></div>

    <!-- Bootstrap JS -->
    <script src="/Views/assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Vendors -->
    <script src="/Views/assets/vendor/apexcharts/js/apexcharts.min.js"></script>
    <script src="/Views/assets/vendor/choices/js/choices.min.js"></script>

    <!-- ThemeFunctions -->
    <script src="/Views/assets/js/functions.js"></script>
    <script src="categorie.js"></script>
    <script src="addCategory.js"></script>
</body>

<!-- Mirrored from booking.webestica.com/agent-dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 20 Feb 2024 15:41:29 GMT -->

</html>