<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="javascript/validate_user_form.js"></script>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <main class="card border-0 shadow-lg w-100" style="max-width: 400px;">
        <div class="card-header">
            <h2 class="text-center">Sign Up</h2>
        </div>

        <div class="card-body bg-body-secondary">
            <form action="main/signup" method="post">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control <?= isset($errors["email"]) ? "is-invalid" : "" ?>" id="email" name="email" value="<?= $email ?>" placeholder="" required>
                    <label for="email" class="text-muted">Email address</label>
                    <div class="invalid-feedback" id="email_error"><?= $errors["email"] ?></div>
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control <?= isset($errors["full_name"]) ? "is-invalid" : "" ?>" id="full_name" name="full_name" value="<?= $full_name ?>" placeholder="" required>
                    <label for="full_name" class="text-muted">Full name</label>
                    <div class="invalid-feedback" id="full_name_error"><?= $errors["full_name"] ?></div>
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control <?= isset($errors["pseudo"]) ? "is-invalid" : "" ?>" id="pseudo" name="pseudo" value="<?= $pseudo ?>" placeholder="" required>
                    <label for="pseudo" class="text-muted">Username</label>
                        <div class="invalid-feedback" id="pseudo_error"><?= $errors["pseudo"] ?></div>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control <?= isset($errors["password"]) ? "is-invalid" : "" ?>" id="password" name="password" placeholder="" required>
                    <label for="password" class="text-muted">Password</label>
                    <div class="invalid-feedback" id="password_error"><?= $errors["password"] ?></div>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control <?= isset($errors["password_confirm"]) ? "is-invalid" : "" ?>" id="password_confirm" name="password_confirm" placeholder="" required>
                    <label for="password_confirm" class="text-muted">Password confirmation</label>
                        <div class="invalid-feedback" id="password_confirm_error"><?= $errors["password_confirm"] ?></div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-2">Sign Up</button>
            </form>

            <a href="main/login" class="btn btn-outline-danger w-100" role="button">Cancel</a>
        </div>
    </main>
</body>
</html>