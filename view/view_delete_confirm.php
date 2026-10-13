<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Delete Confirm</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <main class="card border-0 shadow-lg w-100" style="max-width: 400px;">
        <div class="card-body text-center bg-body-secondary">
            <p class="fs-3 fw-semibold text-danger">Are you sure ?</p>
            
            <hr>
            
            <div>
                <p>Do you really want to delete item <span class="fw-bold">"<?= $item->get_title() ?>"</span> and all of its dependencies ?</p>
                <p>This process cannot be undone.</p>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <a href="items/open/<?= $item->get_id() . "/" . $source ."/" . $user_research ?>" class="btn btn-secondary">Cancel</a>
                
                <form action="items/delete" method="post">
                    <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                    <input type="hidden" name="user_research" value="<?= $user_research ?>">
                    
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>