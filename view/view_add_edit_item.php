<?php
    $back_url = $is_edit ? "items/open/" . $item->get_id() . "/" . $source . "/" . $user_research : "";
    $action = $is_edit ? "items/edit/" . $item->get_id(). "/" . $source . "/" . $user_research : "items/add" . "/" . $source . "/" . $user_research;
    $title = $is_edit ? "Edit item" : "Add item";
    $form_id = $is_edit ? "edit-form" : "add-form";
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="javascript/view_add_edit_item.js"></script>
    <script src="javascript/validate_item_form.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url; $title; $form_id; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <form id="<?=$form_id?>" data-item-id="<?= $item->get_id() ?? "" ?>" action="<?= $action ?>" method="post">
        
            <?php if (isset($errors["global"])): ?>
                <div class="alert alert-danger text-center mb-4" role="alert">
                    <?= $errors["global"] ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header border-0">Basic Information</div>
                
                <div class="card-body bg-body-secondary">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control <?= isset($errors["title"]) ? "is-invalid" : "" ?>" id="title" name="title" value="<?= $item->get_title() ?? "" ?>" placeholder="" required autocomplete="off">
                        <label for="title" class="text-muted">Item Title*</label>
                        <div class="invalid-feedback" id="title_error"><?= $errors["title"] ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control <?= isset($errors["description"]) ? "is-invalid" : '' ?>" id="description" name="description" placeholder=""><?= $item->get_description() ?? "" ?></textarea>
                        <label for="description" class="text-muted">Description</label>
                        <div class="invalid-feedback" id="description_error"><?= $errors["description"] ?></div>                            
                    </div>

                    <div class="form-floating">
                        <input type="number" class="form-control <?= isset($errors["duration_days"]) ? "is-invalid" : "" ?>" id="duration_days" name="duration_days" value="<?= ($item->get_duration_days() != 0) ? $item->get_duration_days() : ""?>" placeholder="" required>
                        <label for="duration_days" class="text-muted">Sale Duration (in Days)*</label>
                        <div class="invalid-feedback" id="duration_days_error"><?= $errors["duration_days"] ?></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0">Sale Type (in €)</div>

                <div class="card-body bg-body-secondary">
                    <div class="card border-primary overflow-hidden shadow-sm mb-4">
                        <div class="card-header border-primary text-primary bg-primary bg-opacity-10"><i class="bi bi-tag me-2"></i>Option 1 : Auction</div>
                        
                        <div class="card-body bg-body-secondary">
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control <?= isset($errors["starting_bid"]) ? "is-invalid" : "" ?>" step="0.01" id="starting_bid" name="starting_bid" value="<?= ($item->get_starting_bid() != 0) ? $item->get_starting_bid() : ""?>" placeholder="">
                                <label for="starting_bid" class="text-muted">Starting Bid</label>
                                <div class="invalid-feedback" id="starting_bid_error"> <?= $errors["starting_bid"] ?></div>
                            </div>

                            <div class="form-floating">
                                <input type="number" class="form-control <?= isset($errors["instant_purchase"]) ? "is-invalid" : "" ?>"step="0.01" id="instant_purchase" name="instant_purchase" value="<?= ($instant_purchase != 0) ? $instant_purchase : "" ?>" placeholder="">
                                <label for="instant_purchase" class="text-muted">Instant Purchase Price (optional)</label>
                                <div class="invalid-feedback" id="instant_purchase_error"><?= $errors["instant_purchase"] ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-success overflow-hidden shadow-sm">
                        <div class="card-header border-success text-success bg-success bg-opacity-10"><i class="bi bi-bag me-2"></i>Option 2 : Direct Sale</div>

                        <div class="card-body bg-body-secondary">
                            <div class="form-floating">
                                <input type="number" class="form-control <?= isset($errors["sale_price"]) ? "is-invalid" : "" ?>" step="0.01" id="sale_price" name="sale_price"  value="<?= ($sale_price != 0) ? $sale_price : "" ?>" placeholder="">
                                <label for="sale_price" class="text-muted">Sale Price</label>
                                <div class="invalid-feedback" id="sale_price_error"><?= $errors["sale_price"] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-0">Categories</div>

                <div class="card-body bg-body-secondary">
                    <small class="d-block mb-3">You can select up to 3 categories.</small>

                    <?php if (isset($errors["checkbox"])): ?>
                        <div class="text-danger small" id="checkbox_error">
                            <?= htmlspecialchars($errors["checkbox"]) ?>
                        </div>
                    <?php endif; ?>


                    <?php foreach ($categories as $categorie): ?>

                        <?php if(in_array($categorie->get_id(), $checked_categories)): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="<?= $categorie->get_id() ?>" id="<?= $categorie->get_name() ?>" checked>
                                <label class="form-check-label" for="<?= $categorie->get_name() ?>"><?= $categorie->get_name() ?></label>
                            </div>

                        <?php else: ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="<?= $categorie->get_id() ?>" id="<?= $categorie->get_name() ?>">
                                <label class="form-check-label" for="<?= $categorie->get_name() ?>"><?= $categorie->get_name() ?></label>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </main>

    <footer class="sticky-bottom">
        <?php include("partials/bot_navbar.php"); ?>
    </footer>
</body>
</html>