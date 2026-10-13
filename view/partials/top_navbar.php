<nav class="container-fluid bg-body shadow-sm border-bottom border-dark-subtle">
    <div class="row align-items-center p-2">
        <div class="col-3 d-flex justify-content-start">
            <?php if ($back_url): ?>
                <a href="<?= $back_url ?>" class="nav-link link-light">
                    <i class="bi bi-chevron-left fs-4"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="col-6 text-center">
            <h2 class="mb-0"><?= $title ?></h2>
        </div>

        <div class="col-3 d-flex justify-content-end">
            <?php if (isset($form_id)): ?>
                <button type="submit" form="<?= $form_id ?>" class="btn nav-link link-light">
                    <i class="bi bi-save fs-4"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
</nav>