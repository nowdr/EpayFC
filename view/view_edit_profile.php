<!DOCTYPE html>
<html lang="en" data-bs-theme="dark"> 
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="javascript/validate_user_form.js"></script>
</head>

<body class="d-flex flex-column min-vh-100"> 
    <header class="sticky-top">
        <?php $back_url = "user/profile"; $title = "Edit Profile"; $form_id = "profile-form"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <form id="profile-form" method="post">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0">Personal Information</div>
                
                <div class="card-body bg-body-secondary">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control <?= isset($errors["full_name"]) ? "is-invalid" : "" ?>" id="full_name" name="full_name" value="<?= $temp_data["full_name"] ?>" placeholder="" required>
                        <label for="full_name" class="text-muted">Full name*</label>
                            <div class="invalid-feedback" id="full_name_error"><?= $errors["full_name"] ?></div>     
                            
                    </div>

                    <div class="form-floating">
                        <input type="text" class="form-control <?= isset($errors["pseudo"]) ? "is-invalid" : "" ?>" id="pseudo" name="pseudo" value="<?= $temp_data["pseudo"] ?>" placeholder="" required>
                        <label for="pseudo" class="text-muted">Username*</label>
                        <div class="invalid-feedback" id="pseudo_error"><?= $errors["pseudo"] ?></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-0">Contact & Payment</div>

                <div class="card-body bg-body-secondary">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control <?= isset($errors["email"]) ? "is-invalid" : "" ?>" id="email" name="email" value="<?= $temp_data["email"] ?>" placeholder="" required>
                        <label for="email" class="text-muted">Email address*</label>
                            <div class="invalid-feedback" id="email_error"><?= $errors["email"] ?></div>
                    </div>

                    <div class="form-floating">
                        <input type="text" class="form-control <?= isset($errors["iban"]) ? "is-invalid" : "" ?>" id="iban" name="iban" value="<?= $temp_data["iban"] ?>" placeholder="">
                        <label for="iban" class="text-muted">IBAN (optional)</label>
                        <div class="invalid-feedback" id="iban_error"><?= $errors["iban"] ?></div>
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