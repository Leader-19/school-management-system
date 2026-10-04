<?php
/** @var string $pageTitle */
/** @var string $formAction */
/** @var string $submitLabel */
/** @var array $subjects */
/** @var array $classes */
/** @var array $data */
/** @var array|null $assignment */
$assignment = $assignment ?? [];
$data = $data ?: [];
$uploader = new Uploader();

$title       = (string) ($data['title'] ?? ($assignment['title'] ?? ''));
$description = (string) ($data['description'] ?? ($assignment['description'] ?? ''));
$subjectId   = (string) ($data['subject_id'] ?? ($assignment['subject_id'] ?? ''));
$classId     = (string) ($data['class_id'] ?? ($assignment['class_id'] ?? ''));
$dueDate     = (string) ($data['due_date'] ?? ($assignment['due_date'] ?? ''));

// datetime-local needs "Y-m-d\TH:i".
if ($dueDate !== '') {
    $timestamp = strtotime($dueDate);
    $dueDate = $timestamp === false ? '' : date('Y-m-d\TH:i', $timestamp);
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle ?? 'Create Assignment') ?></h2>
    <a href="<?= BASE_URL ?>/assignment" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-file-circle-plus me-2"></i>Assignment Details</h6>
    </div>
    <div class="card-body p-4">
        <!-- enctype is required for the document upload to arrive at the server -->
        <form action="<?= htmlspecialchars($formAction ?? (BASE_URL . '/assignment/create')) ?>"
              method="POST"
              enctype="multipart/form-data"
              class="needs-validation"
              novalidate>
            <?= ViewHelper::csrfField() ?>

            <div class="row mb-4">
                <div class="col-md-12 mb-3">
                    <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required
                           placeholder="E.g., Midterm Essay, Math Chapter 5 Homework"
                           value="<?= htmlspecialchars($title) ?>">
                    <div class="invalid-feedback">Please provide a title.</div>
                </div>

                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5"
                              placeholder="Provide detailed instructions for the assignment..."><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="subject_id" class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <select class="form-select" id="subject_id" name="subject_id" required>
                        <option value="">Select a Subject</option>
                        <?php foreach (($subjects ?? []) as $subject): ?>
                            <option value="<?= (int) $subject['id'] ?>" <?= ($subjectId === (string) $subject['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a subject.</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="class_id" class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
                    <select class="form-select" id="class_id" name="class_id" required>
                        <option value="">Select a Class</option>
                        <?php foreach (($classes ?? []) as $class): ?>
                            <option value="<?= (int) $class['id'] ?>" <?= ($classId === (string) $class['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a class.</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="due_date" class="form-label fw-semibold">Due Date &amp; Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" id="due_date" name="due_date" required
                           value="<?= htmlspecialchars($dueDate) ?>">
                    <div class="invalid-feedback">Please select a due date.</div>
                </div>
            </div>

            <!-- ===== Supporting document ===== -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="border rounded p-3 bg-light">
                        <label for="document" class="form-label fw-semibold">
                            <i class="fa-solid fa-paperclip me-1"></i> Supporting Document (optional)
                        </label>

                        <?php if (!empty($assignment['attachments']) && $uploader->exists($assignment['attachments'])): ?>
                            <div class="alert alert-success py-2 mb-3 d-flex align-items-center justify-content-between">
                                <span class="small">
                                    <i class="fa-solid fa-circle-check me-1"></i>
                                    Currently attached:
                                    <strong><?= htmlspecialchars($uploader->originalName($assignment['attachments'])) ?></strong>
                                </span>
                                <a href="<?= BASE_URL ?>/file/assignment/<?= (int) $assignment['id'] ?>"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fa-solid fa-download me-1"></i> Download
                                </a>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="remove_attachment" name="remove_attachment">
                                <label class="form-check-label small text-danger" for="remove_attachment">
                                    Remove the attached document
                                </label>
                            </div>
                        <?php endif; ?>

                        <input type="file" class="form-control" id="document" name="document"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.webp,.zip"
                               data-max-size="<?= Uploader::MAX_BYTES ?>">
                        <div class="form-text" id="documentHelp">
                            Maximum size <?= htmlspecialchars($uploader->formatSize(Uploader::MAX_BYTES)) ?>.
                            Allowed: <?= htmlspecialchars($uploader->allowedList()) ?>.
                            <?php if (!empty($assignment['attachments'])): ?>
                                <br>Uploading a new file replaces the current one.
                            <?php else: ?>
                                <br>Students will be able to download it from the assignment page.
                            <?php endif; ?>
                        </div>
                        <div class="invalid-feedback" id="documentFeedback"></div>
                    </div>
                </div>
            </div>

            <div class="text-end pt-3 mt-2 border-top">
                <a href="<?= BASE_URL ?>/assignment" class="btn btn-light me-2">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fa-solid fa-paper-plane me-1"></i>
                    <?= htmlspecialchars($submitLabel ?? 'Publish Assignment') ?>
                </button>
            </div>
        </form>
    </div>
</div>