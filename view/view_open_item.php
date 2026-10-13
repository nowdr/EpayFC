<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Item Open</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="javascript/view_open_item.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    
    <header class="sticky-top">
        <?php $title = "Item open"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1" id="item-main-container" data-item-id="<?= $item->get_id() ?>" data-current-index="<?= $image_index ?>">
        <div class="row g-4">
            <div class="col-12 col-md-8">
                <div id="carousel-placeholder"></div>
                <div class="card border-0 shadow-sm mb-4 ">
                    <div class="ratio ratio-16x9" id="main-static-card">
                        <img src="<?= $main_image_path ?>"
                            class="card-img-top object-fit-contain bg-white" 
                            alt="<?= $item->get_title() ?>"> 
                    </div>

                    <div class="card-body bg-body-secondary d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                            <h1 class="card-title fs-3 fw-bold"><?=  $item->get_title() ?></h1>

                            <?php if ($item->get_is_auction()): ?>
                                <span class="badge rounded-pill bg-secondary p-2">Auction</span>
                            <?php endif; ?>
                        </div>
                        
                        <small class="text-muted mb-2"><?= $item->get_description() ?></small>

                        <small class="text-blod mb-2">Categories :</small>
                        
                        <?php foreach ($categories as $categorie): ?>
                            <small class="text-muted mb-2">&#x2022; <?= $categorie->get_name() ?></small>
                        <?php endforeach; ?>

                        <small class="text-muted">Start: <span class="fw-bold"><?= $item->get_created_at()->format("d/m/Y H:i:s") ?></span></small>

                        <small class="text-muted">End: <span class="fw-bold"><?= $item->get_end_at()->format("d/m/Y H:i:s") ?></span></small>
                    </div>
                </div>

                <?php if (count($item->get_pictures()) > 1): ?>
                    <div class="mb-4" id="php-gallery-backup">
                        <small class="text-muted d-block mb-2">Additional Images</small>
                        
                        <div class="row g-2">
                            <?php foreach ($item->get_pictures() as $index => $pic): ?>
                                <div class="col-4 col-md-2">
                                    <a href="items/open/<?= $item->get_id() . "/" . $source . (($index != 0) ? "/" . $index : "") ?>" 
                                        class="ratio ratio-16x9 d-block rounded overflow-hidden shadow-sm 
                                        <?= ($image_index === $index) ? "border border-secondary border-2" : "" ?>">
                                        <img src="<?= $pic["picture_path"] ?>"
                                            class="object-fit-contain bg-white"
                                            alt="Photo <?= $index + 1 ?>">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($bids)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            Bid History
                            <span class="badge rounded-pill bg-white text-dark shadow-sm p-2"><?= count($bids) . " " . (count($bids) > 1 ? "entries" : "entry") ?></span>
                        </div>

                        <div class="card-body bg-body-secondary p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach($bids as $bid): ?>
                                    <li class="list-group-item bg-transparent">
                                        <div class="d-flex justify-content-between">
                                            <div class="d-flex flex-column">
                                                <small class="fw-bold"><?= $bid->get_owner_pseudo() ?></small>
                                                <small class="text-muted"><?= $bid->get_created_at()->format('d/m/Y H:i:s') ?></small>
                                            </div>

                                            <span class="text-success">€ <?= number_format((float)$bid->get_amount(), 2, '.', '') ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-0">Pricing</div>

                    <div class="card-body bg-body-secondary">
                        <?php if ($item->get_is_auction()): ?>
                            <div class="d-flex justify-content-between">
                                <span><?= (empty($bids)) ? "Starting Bid" : "Current Bid" ?></span>

                                <span class="text-success fw-bold">€ <?= number_format((float)$current_price, 2, '.', '') ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item->get_buy_now_price()) && $item->get_buy_now_price() > 0): ?>
                            <div class="d-flex justify-content-between">
                                <span><?= $item->get_is_auction() ? "Buy Now" : "Price" ?></span>

                                <span class="fw-bold">€ <?= number_format((float)$item->get_buy_now_price(), 2, '.', '') ?></span>
                            </div> 
                        <?php endif; ?>

                        <hr>

                        <?php if ($is_finished): ?>
                            <div class="text-center">
                                <?php if (count($bids) > 0 || $item->get_buy_now_reached()): ?>
                                        <?php if ($is_highest_bidder): ?>
                                            <small class="text-muted">Congratulations! You purchased this item for € <?= number_format((float)$current_price, 2, '.', '') ?>.</small> 
                                        <?php else :?>
                                            <small class="text-muted"><?= $winner_pseudo ?> won this item for € <?= number_format((float)$current_price, 2, '.', '') ?>.</small>
                                        <?php endif; ?>
                                <?php else : ?>
                                    <small class="text-muted">This listing ended without a buyer.</small>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($is_owner): ?>
                            <div class="text-center">
                                <small class="text-muted">You cannot bid on / buy your own listing.</small>
                            </div>
                        <?php else: ?>
                            <fieldset <?= (!$user || $is_highest_bidder) ? "disabled" : "" ?>>
                                <?php if ($item->get_is_auction()) : ?>
                                    <form action="items/bid" method="post" class="mb-2">
                                        <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text">€</span>
                                                <input name="bid_amount" type="number" step="0.01" class="form-control" value="<?= (empty($bids)) ? $current_price : $current_price + 1 ?>">
                                            </div>
                                        <button type="submit" class="btn btn-success w-100 mb-2">Place Bid</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (!empty($item->get_buy_now_price()) && $item->get_buy_now_price() > 0): ?>
                                    <form action="items/buy" method="post">
                                        <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                                        <button type="submit" class="btn <?= $item->get_is_auction() ? "btn-outline-primary" : "btn-success" ?> w-100">
                                            <?= $item->get_is_auction() ? "Buy now at € " . number_format((float)$item->get_buy_now_price(), 2, '.', '') : '<i class="bi bi-cart-check"></i> Buy now'?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </fieldset>

                            <?php if (!$user): ?>
                                <div class="text-center">
                                    <small class="text-muted">Please Login to bid.</small>
                                </div>
                            <?php elseif ($is_highest_bidder): ?>
                                <div class="text-center">
                                    <small class="text-muted">You are the highest bidder !</small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-0">Seller Information</div>
                        
                    <div class="card-body bg-body-secondary d-flex align-items-center">
                        <div class="me-3" style="width: 50px;">
                            <div class="ratio ratio-1x1">
                                <img src="<?= $seller->get_picture_path() ?? 'ressources/default_profile_picture.jpg'?>" 
                                    class=" rounded-circle object-fit-cover shadow-sm" 
                                    alt="Seller profile picture">
                            </div>
                        </div>

                        <div>
                            <span class="fw-bold d-block"><?= $seller->get_pseudo() ?></span>
                            <small class="text-muted"><?= $seller->get_role() == 'admin' ?  'Admin' : 'Member' ?></small>
                        </div>
                    </div>
                </div>

                <?php if ($is_owner): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0">Manage Your Item</div>
                        <div class="card-body bg-body-secondary text-center">

                            <?php if (count($bids) === 0): ?>
                                <div class="d-grid gap-2">
                                    <a href="items/edit/<?= $item->get_id() . "/" . $source . "/" . $research?>" class="btn btn-outline-secondary text-start btn-sm">
                                        <i class="bi bi-pencil me-2"></i>Edit Item Details</a>
                                    <a href="items/manage_images/<?= $item->get_id() . "/" . $source . "/" . $research?>" class="btn btn-outline-primary text-start btn-sm">
                                        <i class="bi bi-images me-2"></i>Manage Images</a>
                                    <a href="items/delete_confirm/<?= $item->get_id() . "/" . $source . "/" . $research?>" class="btn btn-outline-danger text-start btn-sm">
                                        <i class="bi bi-trash me-2"></i>Delete Item</a>
                                </div>
                            <?php else : ?>
                                <small class="text-muted">This item can no longer be edited or deleted.</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="sticky-bottom">
        <?php include "partials/time_control.php"; include "partials/bot_navbar.php"; ?>
    </footer>
</body>
</html>