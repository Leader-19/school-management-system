$pageTitle = 'Submit Assignment';
<?php $uploader = new Uploader(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Submit Assignment</h2>
    <a href="<?= BASE_URL ?>/assignment/show/<?= (int) ($assignment['id'] ?? 0) ?>" class="btn btn-sm btn-secondary shadow-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to Assignment</a>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <!-- Assignment Info Side Panel -->
        <div class="card shadow-sm border-0 h-100 bg-light">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-3"><?= htmlspecialchars($assignment['title'] ?? 'Untitled') ?></h5>

                <div class="mb-3 border-bottom pb-2">
                    <span class="text-muted small fw-bold d-block text-uppercase">Due Date</span>
                    <span class="fw-medium <?= strtotime($assignment['due_date']) < time() ? 'text-danger' : 'text-dark' ?>">
                        <i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars(date('M d, Y h:i A', strtotime($assignment['due_date'] ?? 'now'))) ?>
                    </span>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <span class="text-muted small fw-bold d-block text-uppercase">Subject</span>
                    <span class="badge bg-secondary"><?= htmlspecialchars($assignment['subject_name'] ?? 'N/A') ?></span>
                </div>

                <?php if (!empty($assignment['attachments'])): ?>
                    <div class="mb-3 border-bottom pb-2">
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1">Briefing Document</span>
                        <a href="<?= BASE_URL ?>/file/assignment/<?= (int) $assignment['id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fa-solid fa-download me-1"></i>
                            <?= htmlspecialchars($uploader->originalName($assignment['attachments'])) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <span class="text-muted small fw-bold d-block text-uppercase mb-1">Instructions</span>
                    <div class="small text-muted text-wrap" style="white-space: pre-line; max-height: 200px; overflow-y: auto;">
                        <?= htmlspecialchars($assignment['description'] ?? 'No instructions provided.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <!-- Submission Form -->
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="fa-solid fa-pen-nib me-2"></i>Your Work</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/assignment/submit/<?= (int) ($assignment['id'] ?? 0) ?>"
                      method="POST"
                      enctype="multipart/form-data"
                      class="needs-validation"
                      novalidate>
                    <?= ViewHelper::csrfField() ?>

                    <div class="alert alert-info py-2 small mb-4">
                        <i class="fa-solid fa-circle-info me-1"></i> Type your answer below, attach a file, or do both.
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label fw-semibold">Written Answer</label>
                        <textarea class="form-control" id="content" name="content" rows="12" placeholder="Type your answer here..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="file" class="form-label fw-semibold"><i class="fa-solid fa-paperclip me-1"></i> Attach a File</label>
                        <input type="file" class="form-control" id="file" name="file"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        <div class="form-text">
                            Maximum size <?= htmlspecialchars($uploader->formatSize(Uploader::MAX_BYTES)) ?>.
                            Allowed: <?= htmlspecialchars($uploader->allowedList()) ?>.
                        </div>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="submit" class="btn btn-success px-5 shadow-sm py-2 fw-bold text-uppercase" onclick="return confirm('Are you sure you want to submit this assignment? You cannot edit it after submission.')">
                            <i class="fa-solid fa-paper-plane me-2"></i> Submit Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
