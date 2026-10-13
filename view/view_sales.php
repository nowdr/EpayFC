<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Sales</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = "user/profile"; $title = "Sales"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">Completed Sales</h1>
                <p class="text-muted">A snapshot of the deals you've wrapped up.</p>
            </div>
            
            <span class="badge rounded-pill bg-success p-2"><?= $items_count ?> sales</span>
        </div>
    
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <div class="card bg-body-secondary h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase text-muted fw-bold">Total Revenue</h6>
                        <div class="fs-1 d-block lh-1">€ <?= $total_revenue ?></div>
                        <small class="text-muted">Across <?= $items_count ?> sales</small>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card bg-body-secondary h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase text-muted fw-bold">Average Ticket</h6>
                        <div class="fs-1 d-block lh-1">€ <?= $average_ticket ?></div>
                        <small class="text-muted">Median buyer appetite indicator</small>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card bg-body-secondary h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase text-muted fw-bold">Loyal Bidder</h6>
                        <div class="fs-4 d-block lh-1"><?= $top_bidder ?></div>
                        <small class="text-muted">Most recurring winning bidder</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach($items as $index => $item): ?>
                <div class="col">
                    <div class="d-flex flex-column h-100">
                        <?php include('partials/item_card.php'); ?>

                        <div class="d-flex flex-column gap-1 text-muted ps-2 mt-2">
                            <small><i class="bi bi-currency-dollar me-1"></i>Final price € <?= $bids[$index]->get_amount() ?></small>

                            <small><i class="bi bi-trophy me-1"></i><?= $bids[$index]->get_owner_pseudo() ?></small>
                            
                            <small><i class="bi bi-clock me-1"></i>Closed on <?= $closing_dates[$item->get_id()] ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <footer class="sticky-bottom">
        <?php include "partials/time_control.php"; ?>
        <?php include("partials/bot_navbar.php"); ?>
    </footer>
</body>
</html>