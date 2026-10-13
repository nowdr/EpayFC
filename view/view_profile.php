<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = false; $title = "Profile"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body bg-body-secondary d-flex flex-column align-items-center text-center">
                <div class="mb-2" style="width: 100px;">
                    <div class="ratio ratio-1x1">
                        <img src="<?= $user->get_picture_path() ?>?v=<?= filemtime($user->get_picture_path()) ?>" 
                            class="rounded-circle object-fit-cover shadow-sm" 
                            alt="Profile picture">
                    </div>
                </div>
                
                <div class="d-flex flex-column">
                    <span class="text-white"><?= $user->get_full_name() ?></span>
                    <small class="text-muted">@<?= $user->get_pseudo() ?></small>
                    <small class="text-muted"><?= $user->get_email() ?></small>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0">My Activities</div>
            
            <div class="card-body bg-body-secondary d-flex flex-column gap-2">
                <a href="user/sales" class="btn btn-outline-primary d-flex align-items-center p-2">
                    <i class="bi bi-card-list fs-4 me-3"></i>
                    
                    <div class="d-flex flex-column text-start">
                        <span>Sales</span>
                        <small class="opacity-75">Items you have sold</small>
                    </div>

                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="user/purchases" class="btn btn-outline-primary d-flex align-items-center p-2">
                    <i class="bi bi-cart fs-4 me-3"></i>
                    
                    <div class="d-flex flex-column text-start">
                        <span>Purchases</span>
                        <small class="opacity-75">Items you have purchased</small>
                    </div>

                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0">Account Settings</div>

            <div class="card-body bg-body-secondary d-flex flex-column gap-2">
                <a href="user/edit_profile" class="btn btn-outline-primary d-flex align-items-center p-2">
                    <i class="bi bi-gear fs-4 me-3"></i>
                    
                    <div class="d-flex flex-column text-start">
                        <span>Edit Profile</span>
                        <small class="opacity-75">Update your personal information</small>
                    </div>

                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="user/change_password" class="btn btn-outline-primary d-flex align-items-center p-2">
                    <i class="bi bi-key fs-4 me-3"></i>

                    <div class="d-flex flex-column text-start">
                        <span>Change Password</span>
                        <small class="opacity-75">Update your account security</small>
                    </div>

                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="user/profile_picture" class="btn btn-outline-primary d-flex align-items-center p-2">
                    <i class="bi bi-person-circle fs-4 me-3"></i>
                    
                    <div class="d-flex flex-column text-start">
                        <span>Profile Picture</span>
                        <small class="opacity-75">Upload or change your profile picture</small>
                    </div>

                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="user/logout" class="btn btn-outline-danger d-flex align-items-center p-2">
                    <i class="bi bi-box-arrow-right fs-4 me-3"></i>
                    
                    <div class="d-flex flex-column text-start">
                        <span>Logout</span>
                        <small class="opacity-75">Sign out of your account</small>
                    </div>

                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>
            </div>
        </div>
    </main>

    <footer class="sticky-bottom">
        <?php include("partials/bot_navbar.php"); ?>
    </footer>

</body>
</html>