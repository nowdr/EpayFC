<div class="card h-100 text-white border-0 shadow-sm position-relative">
    <div class="ratio ratio-16x9">
        <?php $pictures = $item->get_pictures(); ?>
        <img src="<?= $pictures[0]['picture_path'] ?>"
            class="card-img-top object-fit-cover"
            alt="<?= $item->get_title() ?>">

        <div class="card-img-overlay">
            <div class="position-absolute top-0 start-0 d-flex p-2">
                <?php if ($item->get_isHighestBidder()): ?>
                    <span class="badge rounded-pill bg-success shadow-sm p-2">
                        <i class="bi bi-trophy me-1"></i>Highest Bidder
                    </span>
                <?php elseif ($item->get_hasBidByUser()): ?>
                    <span class="badge rounded-pill bg-secondary shadow-sm p-2">
                        <i class="bi bi-tag me-1"></i>Bidder</span>
                <?php endif; ?>
            </div>

            <div class="position-absolute top-0 end-0 d-flex p-2">
                <div class="d-grid gap-1">
                    <?php if ($item->get_is_auction()): ?>
                        <span class="badge rounded-pill bg-warning shadow-sm p-2">
                            <i class="bi bi-tag me-1"></i>Auction</span>
                    <?php endif; ?>
                    <?php if ($item->get_has_buy_now()): ?>
                        <span class="badge rounded-pill bg-primary shadow-sm p-2">
                            <i class="bi bi-bag me-1"></i>Buy Now
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="position-absolute bottom-0 end-0 d-flex p-2">
                <?php if (count($pictures) > 1): ?>
                    <span class="badge rounded-pill bg-dark bg-opacity-75 shadow-sm">
                        <i class="bi bi-images me-1"></i><?= count($pictures) ?> images
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body bg-body-secondary d-flex flex-column">
        <h5 class="card-title fw-bold text-truncate">
            <a href="<?= Configuration::get('web_root') ?>items/open/<?= $item->get_id() ?>/<?= $source ?>" class="text-decoration-none text-reset stretched-link">
                <?= $item->get_title() ?>
            </a>
        </h5>

        <small class="card-subtitle text-muted mb-2">
            by <?= $item->get_owner_pseudo() ?>
        </small>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <?php if($item->get_has_buy_now() == 0 && $item->get_is_auction() == 1): ?>
                <span class="fs-5 fw-bold text-warning">€ <?= $item->get_starting_bid() ?></span>
            <?php else: ?>
                <span class="fs-5 fw-bold text-primary">€ <?= $item->get_buy_now_price() ?></span>
            <?php endif; ?>
            
            <?php if($item->get_has_bids() == 1 && $item->get_max_bid() != $item->get_buy_now_price()): ?>
                <div class="text-end">
                    <small class="text-muted d-block">Current bid</small>
                    <span class="text-success">€ <?= $item->get_max_bid() ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-auto">
            <small class="<?= $item->get_remaining_time() == "Closed" ? "text-muted" : (str_contains($item->get_remaining_time(), "d") ? "text-success" : "text-warning") ?>"><i class="bi bi-clock me-1"></i><?= $item->get_remaining_time() ?></small>
        </div>
    </div>
</div>