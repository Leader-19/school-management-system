<?php
$httpCode = $httpCode ?? 405;
$heading  = 'Method Not Allowed';
$message  = $message ?? 'This address does not accept that kind of request. Use the form buttons instead of typing the URL by hand.';
?>
<div class="d-flex align-items-center justify-content-center flex-grow-1" style="min-height: 60vh;">
    <div class="text-center">
        <div class="display-1 fw-bold text-warning mb-2"><?= (int) $httpCode ?></div>
        <h4 class="mb-3 text-gray-800"><?= htmlspecialchars($heading) ?></h4>
        <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>