<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Browse Items</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="javascript/research.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = false; $title = "Browse"; include("partials/top_navbar.php"); ?>
    </header>

    <main class="container py-4 flex-grow-1" id="top-main"  data-search="<?= $user_research ?>">
        <?php if(!empty($itemsParticipated)): ?>
            <div class="mb-4">
                <h3 id="title-participating" class="mb-3">Items I'm Participating In</h3>

                <div id="participated-list" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($itemsParticipated as $item): ?>
                        <?php if($item->get_buy_now_reached() != '1' && $item->get_remaining_time() != 'Expired'): ?>
                            <div class="col">
                                <?php include 'partials/item_card.php'; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($otherItems)): ?>
            <div>
                <h3 id="title-others" class="mb-3">Other Available Items</h3>

                <div id="others-list" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($otherItems as $item): ?>
                        <?php if($item->get_buy_now_reached() != '1' && $item->get_remaining_time() != 'Expired'): ?>
                            <div class="col">
                                <?php include 'partials/item_card.php'; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        </main>

    <footer class="sticky-bottom">
        <?php include "partials/time_control.php"; include "partials/bot_navbar.php"; ?>
    </footer>
</body>
</html>