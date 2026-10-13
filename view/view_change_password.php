<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = "user/profile"; $title = "Change Password"; $form_id = "password-form"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <form id="password-form" method="post">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0">Current Password</div>

                <div class="card-body bg-body-secondary">
                    <div class="form-floating">
                        <input type="password" class="form-control <?= isset($errors["current_password"]) ? "is-invalid" : "" ?>" id="current_password" name="current_password" placeholder="" required>
                        <label for="current_password" class="text-muted">Current password*</label>
                        <?php if (isset($errors["current_password"])): ?>
                            <div class="invalid-feedback">
                                <?= $errors["current_password"] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-0">New Password</div>
                
                <div class="card-body bg-body-secondary">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control <?= isset($errors["password"]) ? "is-invalid" : "" ?>" id="new_password" name="new_password" placeholder="" required>
                        <label for="password" class="text-muted">New password*</label>
                        <?php if (isset($errors["password"])): ?>
                            <div class="invalid-feedback">
                                <?= $errors["password"] ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control <?= isset($errors["password_confirm"]) ? "is-invalid" : "" ?>" id="new_password_confirm" name="new_password_confirm" placeholder="" required>
                        <label for="password_confirm" class="text-muted">Password confirmation*</label>
                        <?php if (isset($errors["password_confirm"])): ?>
                            <div class="invalid-feedback">
                                <?= $errors["password_confirm"] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>                
        </form>
    </main>

    <footer class="sticky-bottom">
        <?php include("partials/bot_navbar.php"); ?>
    </footer>
</body>
</html>