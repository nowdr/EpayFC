<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Profile Picture</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = "user/profile"; $title ="Manage Profile Picture"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0">Current Profile Picture</div>

            <div class="card-body bg-body-secondary d-flex flex-column align-items-center text-center">
                <div class="mb-2" style="width: 100px;">
                    <div class="ratio ratio-1x1">
                        <img src="<?= $user->get_picture_path() ?>?v=<?= filemtime($user->get_picture_path()) ?>" 
                            class=" rounded-circle object-fit-cover shadow-sm" 
                            alt="Profile picture">
                    </div>
                </div>
                
                <div class="d-flex flex-column">
                    <span class="text-white"><?= $user->get_full_name() ?></span>
                    <small class="text-muted">@<?= $user->get_pseudo() ?></small>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0">Upload New Picture</div>
            <div class="card-body bg-body-secondary">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <div class="text-center mb-1">Please fix the following :</div>
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input class="form-control mb-3" type="file" id="profile_picture" name="profile_picture" accept="image/jpeg, image/png, image/gif, image/webp" required>
                    <button type="submit" name="action" value="upload" class="btn btn-primary">Upload Picture</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0">Remove Current Picture</div>

            <div class="card-body bg-body-secondary">
                <div class="alert alert-warning" role="alert">
                    This will permanently delete your current profile picture. You can upload a new one anytime.
                </div>

                <form method="post">
                    <button type="submit" name="action" value="delete" class="btn btn-danger" <?= $user->get_picture_path() === "ressources/default_profile_picture.jpg" ? "disabled" : "" ?>>Delete Picture</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="sticky-bottom">
        <?php include("partials/bot_navbar.php"); ?>
    </footer>
</body>
</html>