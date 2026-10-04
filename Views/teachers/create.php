<?php
/** @var array $data */
$pageTitle = 'Add Teacher';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Add Teacher</h2>
    <a href="<?= BASE_URL ?>/teacher" class="btn btn-sm btn-light border">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Teachers
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-chalkboard-user me-2"></i>Teacher Account</h6>
    </div>
    <div class="card-body p-4">
        <form action="<?= BASE_URL ?>/teacher/create" method="POST" class="needs-validation" novalidate>
            <?= ViewHelper::csrfField() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?= htmlspecialchars($data['username'] ?? '') ?>"
                           minlength="3" maxlength="50" required>
                    <div class="invalid-feedback">Please provide a username (at least 3 characters).</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                    <div class="invalid-feedback">Please provide a valid email address.</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="6" required data-password-toggle-target>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                                data-password-toggle="password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= ($data['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="text-end pt-3 mt-2 border-top">
                <a href="<?= BASE_URL ?>/teacher" class="btn btn-light me-2">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fa-solid fa-save me-1"></i> Save Teacher
                </button>
            </div>
        </form>
    </div>
</div>
