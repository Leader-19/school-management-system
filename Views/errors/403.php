<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-danger mb-0">403</h1>
        <h4 class="text-muted mb-3"><i class="fa-solid fa-lock me-2"></i>Access Restricted</h4>
        <p class="text-muted mb-4">
            <?= htmlspecialchars($message ?? 'Your account does not have permission to view this page.') ?>
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
            <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
        </div>
    </div>
</div>
