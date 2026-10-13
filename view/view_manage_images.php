<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Manage Images</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
        <?php $back_url = "items/open/" . $item->get_id() . "/" . $source . "/" . $user_research; $title = "Manage Images"; include "partials/top_navbar.php"; ?>
    </header>

    <main class="container py-4 flex-grow-1">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0">Add New Images</div>
            <div class="card-body bg-body-secondary">
                <form action="items/upload_image" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                    <input type="hidden" name="source" value="<?= $source ?>">
                    <input type="hidden" name="user_research" value="<?= $user_research ?>">
                    <input class="form-control mb-3" type="file" id="image_file" name="image_file" accept="image/jpeg, image/png, image/gif, image/webp" required>
                    <button type="submit" class="btn btn-primary">Upload Image</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0">Current Images</div>
            <div class="card-body bg-body-secondary">
                
                <div class="row g-2" id="sortable-images" data-item-id="<?= $item->get_id() ?>">
                    <?php $pictures = $item->get_pictures(); ?>
                    <?php if (count($pictures) === 1 && strpos($pictures[0]['picture_path'], 'default_item_picture.jpg') !== false): ?>
                        <small class="text-muted">No images uploaded yet.</small>
                    <?php else: ?>
                        <?php foreach ($pictures as $index => $pic): ?>
                            <?php 
                            if (strpos($pic['picture_path'], 'default_item_picture.jpg') !== false) {
                                continue; 
                            }
                            ?>
                            
                            <div class="col-4 col-md-2 sortable-item" data-picture-path="<?= $pic['picture_path'] ?>">
                                <div class="ratio ratio-16x9 d-block rounded overflow-hidden shadow-sm mb-2">
                                    <img src="<?= $pic["picture_path"] ?>"
                                        class="object-fit-contain bg-white"
                                        alt="Photo <?= $index + 1 ?>">
                                </div>

                                <div class="d-flex justify-content-center gap-2">
                                    <form action="items/move_image" method="post" class="js-move-form">
                                        <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                                        <input type="hidden" name="priority" value="<?= $pic['priority'] ?>">
                                        <input type="hidden" name="direction" value="left">
                                        <input type="hidden" name="source" value="<?= $source ?>">
                                        <button type="submit" class="btn btn-secondary js-btn-left" <?= ($index == 0) ? 'disabled' : '' ?>>
                                            <i class="bi bi-arrow-left"></i>
                                        </button>
                                    </form>

                                    <form action="items/move_image" method="post" class="js-move-form">
                                        <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                                        <input type="hidden" name="priority" value="<?= $pic['priority'] ?>">
                                        <input type="hidden" name="direction" value="right">
                                        <input type="hidden" name="source" value="<?= $source ?>">
                                        <button type="submit" class="btn btn-secondary js-btn-right" <?= ($index == count($pictures) - 1) ? 'disabled' : '' ?>>
                                            <i class="bi bi-arrow-right"></i>
                                        </button>
                                    </form>

                                    <form action="items/delete_image" method="post" class="js-delete-form">
                                        <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                                        <input type="hidden" name="priority" value="<?= $pic['priority'] ?>">
                                        <input type="hidden" name="source" value="<?= $source ?>">
                                        <input type="hidden" name="user_research" value="<?= $user_research ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="deleteImageModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-body text-center bg-body-secondary p-4 rounded">
            <p class="fs-3 fw-semibold text-danger">Are you sure ?</p>
            <hr>
            <div>
                <p>Do you really want to delete this image?</p>
                <p>This process cannot be undone.</p>
            </div>
            <div class="d-flex justify-content-center gap-3 mt-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="modalDeleteForm" action="items/delete_image/<?= $user_research ?>"  method="post">
                    <input type="hidden" name="item_id" value="<?= $item->get_id() ?>">
                    <input type="hidden" name="priority" id="modalDeletePriority" value="">
                    <input type="hidden" name="user_research" value="<?= $user_research ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="sticky-bottom">
        <?php include "partials/bot_navbar.php" ?>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="javascript/view_manage_images.js"></script>
</body>
</html>