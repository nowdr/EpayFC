<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Log In</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <main class="card border-0 shadow-lg w-100" style="max-width: 400px;">
        <div class="card-header">
            <h2 class="text-center">Log In</h2>
        </div>

        <div class="card-body bg-body-secondary">
            <?php if (isset($errors["login"])): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?= $errors["login"] ?>
                </div>
            <?php endif; ?>
            
            <form action="main/login" method="post">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="" required>
                    <label for="email" class="text-muted">Email address</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="" required>
                    <label for="password" class="text-muted">Password</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-2">Log in</button>
            </form>

            <a href="items/index" class="btn btn-secondary w-100 mb-3" role="button">Continue as guest</a>

            <a href="main/signup" class="d-block text-center text-decoration-none mb-4">New here ? Create an account !</a>

            <?php if (configuration::is_dev()): ?>
                <div class="text-center border-top p-2">
                    <p class="text-warning fw-bold mb-1">For debug purpose</p>
                    
                    <form action="main/login" method="post">
                        <input type="hidden" name="email" value="boverhaegen@epfc.eu">
                        <input type="hidden" name="password" value="Password1,">
                        <button type="submit" class="btn btn-link text-decoration-none text-white p-0 small">Login as <b>boverhaegen@epfc.eu</b></button>
                    </form>
                    
                    <form action="main/login" method="post">
                        <input type="hidden" name="email" value="mamichel@epfc.eu">
                        <input type="hidden" name="password" value="Password1,">
                        <button type="submit" class="btn btn-link text-decoration-none text-white p-0 small">Login as <b>mamichel@epfc.eu</b></button>
                    </form>
                    
                    <form action="main/login" method="post">
                        <input type="hidden" name="email" value="quhouben@epfc.eu">
                        <input type="hidden" name="password" value="Password1,">
                        <button type="submit" class="btn btn-link text-decoration-none text-white p-0 small">Login as <b>quhouben@epfc.eu</b></button>
                    </form>
                    
                    <form action="main/login" method="post">
                        <input type="hidden" name="email" value="xapigeolet@epfc.eu">
                        <input type="hidden" name="password" value="Password1,">
                        <button type="submit" class="btn btn-link text-decoration-none text-white p-0 small">Login as <b>xapigeolet@epfc.eu</b></button>
                    </form>

                    <form action="main/login" method="post" class="mb-1">
                        <input type="hidden" name="email" value="admin@epfc.eu">
                        <input type="hidden" name="password" value="Password1,">
                        <button type="submit" class="btn btn-link text-decoration-none text-white p-0 small">Login as <b>admin@epfc.eu</b></button>
                    </form>
                    
                    <div class="d-flex flex-column">
                        <a href="setup/install" class="text-decoration-none fw-bold text-success">Restore original data</a>
                        <a href="setup/export" class="text-decoration-none fw-bold text-warning">Backup personal data</a>
                        <a href="setup/restore" class="text-decoration-none fw-bold text-warning">Restore personal data</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>