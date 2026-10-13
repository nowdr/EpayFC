<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Purchases</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = "user/profile"; $title = "Purchases"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">My Purchases</h1>
                <p class="text-muted">Track the gear you've successfully secured.</p>
            </div>
            
            <span class="badge rounded-pill bg-primary p-2"><?= $items_count ?> purchases</span>
        </div>
        
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <div class="card bg-body-secondary h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase text-muted fw-bold">Total Spent</h6>
                        <span class="fs-1 d-block lh-1">€ <?= $total_spent ?></span>
                        <small class="text-muted">Across <?= $items_count ?> purchases</small>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card bg-body-secondary h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase text-muted fw-bold">Average Ticket</h6>
                        <span class="fs-1 d-block lh-1">€ <?= $average_ticket ?></span>
                        <small class="text-muted">Helps you plan future bids</small>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card bg-body-secondary h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title text-uppercase text-muted fw-bold">Top Seller</h6>
                        <span class="fs-4 d-block lh-1"><?= $top_seller ?></span>
                        <small class="text-muted">Who you've bought from the most</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach($items as $item): ?>
                <div class="col">
                    <div class="d-flex flex-column h-100">
                        <?php include('partials/item_card.php'); ?>
                        
                        <div class="d-flex flex-column gap-1 text-muted ps-2 mt-2">
                            <small><i class="bi bi-tag me-1"></i>Paid € <?= $item->get_max_bid() ?></small>

                            <small><i class="bi bi-clock me-1"></i>Closed on <?= $closing_dates[$item->get_id()] ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <footer class="sticky-bottom">
        <?php include "partials/time_control.php"; include "partials/bot_navbar.php"; ?>
    </footer>
</body>
</html>