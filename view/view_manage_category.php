<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Manage categories</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="javascript/manage_categories.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = false; $title = "Categories"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body bg-body-secondary" id="categories-list">
                <?php foreach ($categories as $category): 
                    $display_name = $submitted_names[$category->get_id()] ?? $category->get_name();
                    ?>
                    <form action="category/manage_category" method="post">
                        <input type="hidden" name="category_id" value="<?= $category->get_id() ?>">
                        <div class="form-floating mb-3">
                            <div class="d-grid d-md-flex gap-1">
                                <input type="text" class="form-control py-1 <?= isset($errors[$category->get_id()]) ? "is-invalid" : "" ?>" name="name" value="<?= htmlspecialchars($display_name) ?>" required autocomplete="off">
                                <button type="submit" name="action" value="moveUp" class="btn btn-sm btn-outline-secondary" <?= $category->get_priority() == 1 ? 'disabled' : '' ?>>Up</button>
                                <button type="submit" name="action" value="moveDown" class="btn btn-sm btn-outline-secondary" <?= $category->get_priority() == count($categories) ? 'disabled' : '' ?>>Down</button>
                                <button type="submit" name="action" value="save" class="btn btn-outline-success">Save</button>
                                <button type="submit" name="action" value="delete" class="btn btn-outline-danger" <?= $category->is_empty() ? "" : "disabled" ?>>Delete</button>
                            </div>
                            <?php if (isset($errors[$category->get_id()])): ?>
                                <div class="text-danger small mt-2">
                                    <?php
                                        $msg = (string)$errors[$category->get_id()];
                                        echo htmlspecialchars($msg);
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endforeach; ?>
                <hr>
                <form action="category/manage_category" method="post">
                    <input type="hidden" name="category_id" value="0">
                    <div class="d-grid d-md-flex gap-1">
                        <input type="text" class="form-control py-1 <?= isset($errors[0]) ? "is-invalid" : "" ?>?>" name="name" value="<?= $add_name ?? "" ?>" placeholder="Category name" required autocomplete="off">
                        <button type="submit" name="action" value="add" class="btn btn-outline-primary"> + </button>
                    </div>
                    <?php if (isset($errors[0])): ?>
                        <div class="text-danger small mt-2">
                            <?php
                                $msg_add = (string)$errors[0];
                                echo htmlspecialchars($msg_add);
                            ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>


    </main>

    <footer class="sticky-bottom">
        <?php include("partials/bot_navbar.php"); ?>
    </footer>
</body>
</html>