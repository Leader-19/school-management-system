<?php
/** @var string $pageTitle */
/** @var string $formAction */
/** @var string $submitLabel */
/** @var array $teachers */
/** @var array $data */
$selectedTeacher = (string) ($data['teacher_id'] ?? '');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle ?? 'Class') ?></h2>
    <a href="<?= BASE_URL ?>/class" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-door-open me-2"></i>Class Details</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= htmlspecialchars($formAction) ?>" method="POST" novalidate>
                    <?= ViewHelper::csrfField() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required maxlength="100"
                                   placeholder="e.g. Class 10A"
                                   value="<?= htmlspecialchars((string) ($data['name'] ?? '')) ?>">
                            <div class="invalid-feedback">Please provide a class name.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="teacher_id" class="form-label fw-semibold">Homeroom Teacher</label>
                            <select class="form-select" id="teacher_id" name="teacher_id">
                                <option value="">Unassigned</option>
                                <?php foreach (($teachers ?? []) as $teacher): ?>
                                    <option value="<?= (int) $teacher['id'] ?>" <?= ($selectedTeacher === (string) $teacher['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($teacher['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Only accounts with the teacher role are listed.</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      maxlength="500"
                                      placeholder="e.g. 10th Grade Section A"><?= htmlspecialchars((string) ($data['description'] ?? '')) ?></textarea>
                        </div>
                    </div>

                    <div class="text-end border-top pt-3 mt-2">
                        <a href="<?= BASE_URL ?>/class" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa-solid fa-save me-1"></i> <?= htmlspecialchars($submitLabel ?? 'Save Class') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 bg-light h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-circle-info me-2"></i>Notes</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li class="mb-2">Students are assigned to a class after the class exists.</li>
                    <li class="mb-2">Class names must be unique.</li>
                    <li class="mb-2">Removing a class only unassigns its students; it does not delete them.</li>
                    <li>A class that still has assignments cannot be deleted.</li>
                </ul>
            </div>
        </div>
    </div>
</div>