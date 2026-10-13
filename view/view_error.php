<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
    
<body>
    <div class="d-flex flex-column justify-content-center align-items-center min-vh-100">
        <h1>Oops!</h1>
        <p><?= $error ?></p>
        <a href="items/display" class="btn btn-primary">Go To Homepage</a>
    </div>
</body>
</html>