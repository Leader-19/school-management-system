<?php
/**
 * Shared field renderer for the student create/edit forms.
 *
 * Expects: $data (array of current values), $classes (list of classes),
 *          $formAction (URL to POST to), $submitLabel, $showAccountStatus,
 *          $showPasswordHint.
 */
if (!function_exists('render_student_fields')) {
    function render_student_field($name, $label, $form, $type = 'text', $extra = '', $placeholder = '') {
        $id = 'field_' . $name;
        $value = htmlspecialchars((string) ($form[$name] ?? ''));
        $required = in_array($name, ['username', 'email', 'first_name', 'last_name', 'student_code'], true) ? ' required' : '';
        ?>
        <div class="col-md-4 mb-3">
            <label for="<?= $id ?>" class="form-label fw-semibold"><?= htmlspecialchars($label) ?><?= $required ?><span class="text-danger"> *</span></label>
            <input type="<?= htmlspecialchars($type) ?>"
                   class="form-control<?= $name === 'email' ? '' : '' ?>"
                   id="<?= $id ?>"
                   name="<?= htmlspecialchars($name) ?>"
                   value="<?= $value ?>"
                   placeholder="<?= htmlspecialchars($placeholder) ?>"
                   <?= $required ?>
                   <?= $extra ?>>
        </div>
        <?php
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800"><?= $pageTitle ?? 'Student' ?></h2>
    <a href="<?= BASE_URL ?>/student" class="btn btn-sm btn-secondary shadow-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to List</a>
</div>

<?php if (!empty($validationErrors)): ?>
    <div class="alert alert-danger shadow-sm">
        <i class="fa-solid fa-triangle-exclamation me-1"></i> Please fix the following:
        <ul class="mb-0 mt-2">
            <?php foreach ($validationErrors as $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= htmlspecialchars($formAction) ?>" method="POST" novalidate>
    <?= ViewHelper::csrfField() ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-user me-2"></i>Account Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <?php render_student_field('username', 'Username', $data, 'text', '', 'e.g. john.doe'); ?>
                <?php render_student_field('email', 'Email', $data, 'email', '', 'name@school.local'); ?>
                <?php
                $passwordExtra = ($showPasswordHint ?? false)
                    ? 'placeholder="Leave empty to keep current" autocomplete="new-password"'
                    : 'placeholder="Min. 6 characters" autocomplete="new-password"';
                render_student_field('password', 'Password', $data, 'password', $passwordExtra);
                ?>
            </div>

            <?php if ($showAccountStatus ?? false): ?>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="field_status" class="form-label fw-semibold">Account Status</label>
                        <select class="form-select" id="field_status" name="status">
                            <option value="active" <?= (($data['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (($data['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <div class="form-text">Inactive accounts cannot sign in.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-address-card me-2"></i>Personal Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <?php render_student_field('student_code', 'Student Code', $data, 'text', '', 'e.g. STU001'); ?>
                <?php render_student_field('first_name', 'First Name', $data); ?>
                <?php render_student_field('last_name', 'Last Name', $data); ?>
                <?php render_student_field('date_of_birth', 'Date of Birth', $data, 'date'); ?>

                <div class="col-md-4 mb-3">
                    <label for="field_gender" class="form-label fw-semibold">Gender</label>
                    <select class="form-select" id="field_gender" name="gender">
                        <?php
                        $currentGender = strtolower((string) ($data['gender'] ?? ''));
                        $genderOptions = ['' => 'Select Gender', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
                        foreach ($genderOptions as $value => $label):
                        ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= ($currentGender === $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php render_student_field('phone', 'Phone', $data, 'tel', '', 'Optional'); ?>

                <div class="col-md-12 mb-3">
                    <label for="field_address" class="form-label fw-semibold">Address</label>
                    <textarea class="form-control" id="field_address" name="address" rows="2"><?= htmlspecialchars((string) ($data['address'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-school me-2"></i>Academic Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="field_class_id" class="form-label fw-semibold">Assign to Class</label>
                    <select class="form-select" id="field_class_id" name="class_id">
                        <option value="">Select a Class (Optional)</option>
                        <?php foreach (($classes ?? []) as $class): ?>
                            <option value="<?= htmlspecialchars((string) $class['id']) ?>"
                                <?= ((string) ($data['class_id'] ?? '') === (string) $class['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end border-top pt-3">
        <a href="<?= BASE_URL ?>/student" class="btn btn-light me-2">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 shadow-sm">
            <i class="fa-solid fa-save me-1"></i> <?= htmlspecialchars($submitLabel ?? 'Save Student') ?>
        </button>
    </div>
</form>