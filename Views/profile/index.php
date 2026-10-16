<?php
$pageTitle = 'My Profile';
$data = $data ?? [];
$profile = $profile ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h3 mb-1 text-gray-800">My profile</h2>
        <p class="text-muted small mb-0">Update your sign-in details and account password.</p>
    </div>
    <span class="badge rounded-pill bg-<?= htmlspecialchars(($profile['role_name'] ?? '') === 'admin' ? 'danger' : (($profile['role_name'] ?? '') === 'student' ? 'success' : 'primary')) ?> px-3 py-2">
        <?= htmlspecialchars(ucfirst((string) ($profile['role_name'] ?? 'User'))) ?> account
    </span>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-1"></i><?= htmlspecialchars($errors[0]) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 px-4">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-regular fa-id-card me-2"></i>Account details</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/profile" method="POST">
                    <?= ViewHelper::csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="profileUsername" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="profileUsername" name="username" value="<?= htmlspecialchars((string) ($data['username'] ?? '')) ?>" minlength="3" maxlength="50" autocomplete="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="profileEmail" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="profileEmail" name="email" value="<?= htmlspecialchars((string) ($data['email'] ?? '')) ?>" autocomplete="email" required>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="mb-3">
                        <h6 class="fw-bold mb-1">Change password</h6>
                        <p class="small text-muted mb-0">Leave these fields blank to keep your current password.</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="currentPassword" class="form-label fw-semibold">Current password</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password" autocomplete="current-password">
                        </div>
                        <div class="col-md-6">
                            <label for="newPassword" class="form-label fw-semibold">New password</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" minlength="6" autocomplete="new-password">
                            <div class="form-text">At least 6 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmPassword" class="form-label fw-semibold">Confirm new password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" minlength="6" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end border-top mt-4 pt-3">
                        <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 text-center">
                <span class="avatar avatar-tone-primary mx-auto mb-3" style="width:4rem;height:4rem;font-size:1.25rem" aria-hidden="true">
                    <?php
                    $nameParts = preg_split('/[\s._-]+/', trim((string) ($profile['username'] ?? 'User'))) ?: [];
                    $initials = count($nameParts) > 1 ? mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1) : mb_substr((string) ($nameParts[0] ?? 'U'), 0, 1);
                    echo htmlspecialchars(mb_strtoupper($initials));
                    ?>
                </span>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars((string) ($profile['username'] ?? 'User')) ?></h5>
                <div class="text-muted small mb-3"><?= htmlspecialchars((string) ($profile['email'] ?? '')) ?></div>
                <span class="badge bg-light text-dark border"><?= htmlspecialchars(ucfirst((string) ($profile['role_name'] ?? 'User'))) ?></span>
                <hr>
                <p class="small text-muted mb-0"><i class="fa-solid fa-shield-halved me-1 text-success"></i>Your account role and access permissions are managed by the school administrator.</p>
            </div>
        </div>
    </div>
</div>
