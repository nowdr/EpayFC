<nav class="nav bg-body shadow-sm border-top border-dark-subtle">
    <a href="items/display" class="nav-link d-flex flex-column align-items-center text-center link-light flex-fill">
        <i class="bi bi-search fs-5"></i>
        <small>Browse</small>
    </a>

    <?php if ($user): ?>
        <a href="items/my_items" class="nav-link d-flex flex-column align-items-center text-center link-light flex-fill">
            <i class="bi bi-house-door d-block fs-5"></i>
            <small>My Items</small>
        </a>
        <a href="items/add" class="nav-link d-flex flex-column align-items-center text-center link-light flex-fill">
            <i class="bi bi-plus-lg d-block fs-5"></i>
            <small>Add Offer</small>
        </a>

        <?php if ($user->is_admin()): ?>
            <a href="category/manage" class="nav-link d-flex flex-column align-items-center text-center link-light flex-fill">
                <i class="bi bi-feather"></i>
                <small>Categories</small>
            </a>
        <?php endif; ?>

        <a href="user/profile" class="nav-link d-flex flex-column align-items-center text-center link-light flex-fill">
            <i class="bi bi-gear d-block fs-5"></i>
            <small>Profile</small>
        </a>
    <?php else: ?>
        <a href="main/login" class="nav-link d-flex flex-column align-items-center text-center link-light flex-fill">
            <i class="bi bi-person-plus d-block fs-5"></i>
            <small>Join us</small>
        </a>
    <?php endif; ?>
</nav>