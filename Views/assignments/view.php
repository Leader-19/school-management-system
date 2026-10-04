$pageTitle = 'Assignment Details';
<?php
$uploader = new Uploader();
$currentUser = Auth::user();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Assignment Details</h2>
    <div>
        <?php if (Auth::hasRole('student') && empty($submission)): ?>
            <a href="<?= BASE_URL ?>/assignment/submit/<?= $assignment['id'] ?? 0 ?>" class="btn btn-sm btn-success shadow-sm me-2"><i class="fa-solid fa-upload me-1"></i> Submit Work</a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('create_assignments')): ?>
            <a href="<?= BASE_URL ?>/assignment/edit/<?= (int) ($assignment['id'] ?? 0) ?>" class="btn btn-sm btn-primary shadow-sm me-2"><i class="fa-solid fa-edit me-1"></i> Edit</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/assignment" class="btn btn-sm btn-secondary shadow-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($assignment['title'] ?? 'Untitled') ?></h5>
                <?php 
                    $dueDate = strtotime($assignment['due_date']);
                    $isOverdue = $dueDate < time();
                ?>
                <span class="badge <?= $isOverdue ? 'bg-danger' : 'bg-success' ?> fs-6">
                    <?= $isOverdue ? 'Overdue' : 'Active' ?>
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4 border-bottom pb-4">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Subject</div>
                        <div class="fs-6"><span class="badge bg-secondary"><?= htmlspecialchars($assignment['subject_name'] ?? 'N/A') ?></span></div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Class</div>
                        <div class="fs-6"><?= htmlspecialchars($assignment['class_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Teacher</div>
                        <div class="fs-6"><i class="fa-solid fa-chalkboard-user me-1 text-primary"></i> <?= htmlspecialchars($assignment['teacher_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Due Date</div>
                        <div class="fs-6 <?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                            <i class="fa-regular fa-calendar-xmark me-1"></i> <?= htmlspecialchars(date('M d, Y h:i A', $dueDate)) ?>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <h6 class="fw-bold mb-3">Instructions:</h6>
                    <div class="p-3 bg-light rounded border text-wrap" style="white-space: pre-line;">
                        <?= htmlspecialchars($assignment['description'] ?? 'No description provided.') ?>
                    </div>
                </div>

                <?php if (!empty($assignment['attachments'])): ?>
                    <div class="mb-2">
                        <h6 class="fw-bold mb-3">Supporting Document:</h6>
                        <?php if ($uploader->exists($assignment['attachments'])): ?>
                            <a href="<?= BASE_URL ?>/file/assignment/<?= (int) $assignment['id'] ?>" class="btn btn-outline-primary">
                                <i class="fa-solid fa-file-arrow-down me-2"></i>
                                <?= htmlspecialchars($uploader->originalName($assignment['attachments'])) ?>
                            </a>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0 py-2 small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                The attached file is missing from the server.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-muted small py-3">
                Created on <?= htmlspecialchars(date('M d, Y', strtotime($assignment['created_at'] ?? 'now'))) ?>
            </div>
        </div>
    </div>
</div>

<?php if (Auth::hasRole('student')): ?>
    <!-- Student Submission Status Area -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-info"><i class="fa-solid fa-file-export me-2"></i>My Submission</h6>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($submission)): ?>
                <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                    <i class="fa-solid fa-circle-check fa-2x me-3"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Successfully Submitted!</h6>
                        <p class="mb-0 small">You submitted your work on <?= htmlspecialchars(date('M d, Y h:i A', strtotime($submission['submitted_at']))) ?>.</p>
                    </div>
                </div>

                <h6 class="fw-bold mb-2">Your Answer:</h6>
                <div class="p-3 bg-light rounded border mb-4 text-wrap" style="white-space: pre-line;">
                    <?= htmlspecialchars($submission['content'] ?? '') ?>
                </div>

                <?php if (!empty($submission['file_path'])): ?>
                    <h6 class="fw-bold mb-2">Attached File:</h6>
                    <a href="<?= BASE_URL ?>/file/submission/<?= (int) $submission['id'] ?>" class="btn btn-outline-primary mb-4">
                        <i class="fa-solid fa-paperclip me-2"></i>
                        <?= htmlspecialchars($submission['file_name'] ?: 'Download attachment') ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($submission['feedback'])): ?>
                    <h6 class="fw-bold mb-2">Teacher Feedback:</h6>
                    <div class="p-3 bg-info bg-opacity-10 rounded border mb-4 text-wrap" style="white-space: pre-line;">
                        <?= htmlspecialchars($submission['feedback']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($submission['grade']) && $submission['grade'] !== null): ?>
                    <div class="card bg-success text-white border-0">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <h5 class="mb-0"><i class="fa-solid fa-award me-2"></i> Grade Received</h5>
                            <h3 class="mb-0 fw-bold"><?= htmlspecialchars($submission['grade']) ?><small class="fs-6 fw-normal">/100</small></h3>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0 border-0">
                        <i class="fa-solid fa-hourglass-half me-2"></i> Your submission is pending review and has not been graded yet.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fa-solid fa-file-circle-xmark fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Not Submitted Yet</h5>
                    <p class="text-muted mb-4">You have not submitted any work for this assignment.</p>
                    <a href="<?= BASE_URL ?>/assignment/submit/<?= $assignment['id'] ?? 0 ?>" class="btn btn-success px-4"><i class="fa-solid fa-upload me-2"></i> Submit Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (Auth::hasRole('teacher') || Auth::hasRole('admin')): ?>
    <!-- Teacher/Admin Submissions List Area -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-inbox me-2"></i>Student Submissions</h6>
            <span class="badge bg-primary rounded-pill"><?= count($submissions ?? []) ?> Total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Student Name</th>
                            <th>Submitted At</th>
                            <th>Attachment</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th class="pe-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($submissions)): ?>
                            <?php foreach($submissions as $sub): ?>
                                <tr>
                                    <td class="ps-4 fw-medium"><?= htmlspecialchars($sub['student_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($sub['submitted_at']))) ?></td>
                                    <td>
                                        <?php if (!empty($sub['file_path'])): ?>
                                            <a href="<?= BASE_URL ?>/file/submission/<?= (int) $sub['id'] ?>" class="badge bg-info text-decoration-none" title="Download attachment">
                                                <i class="fa-solid fa-paperclip me-1"></i>File
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Text only</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(isset($sub['grade']) && $sub['grade'] !== null): ?>
                                            <span class="badge bg-success">Graded</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold">
                                        <?php if(isset($sub['grade']) && $sub['grade'] !== null): ?>
                                            <?= htmlspecialchars($sub['grade']) ?>/100
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#gradeModal<?= (int) $sub['id'] ?>">
                                            <i class="fa-solid fa-check-to-slot me-1"></i> Review &amp; Grade
                                        </button>

                                        <!-- Grading Modal for this submission -->
                                        <div class="modal fade text-start" id="gradeModal<?= (int) $sub['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-light">
                                                        <h5 class="modal-title fw-bold">Review Submission: <?= htmlspecialchars($sub['student_name'] ?? '') ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="<?= BASE_URL ?>/assignment/grade/<?= (int) $sub['id'] ?>" method="POST">
                                                        <?= ViewHelper::csrfField() ?>
                                                        <div class="modal-body p-4">
                                                            <h6 class="fw-bold mb-2">Student's Answer:</h6>
                                                            <?php if (trim((string) ($sub['content'] ?? '')) !== ''): ?>
                                                                <div class="p-3 bg-light rounded border mb-4 text-wrap" style="white-space: pre-line; max-height: 300px; overflow-y: auto;">
                                                                    <?= htmlspecialchars($sub['content']) ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <p class="text-muted small mb-4">No written answer - only an attached file.</p>
                                                            <?php endif; ?>

                                                            <?php if (!empty($sub['file_path'])): ?>
                                                                <h6 class="fw-bold mb-2">Attachment:</h6>
                                                                <a href="<?= BASE_URL ?>/file/submission/<?= (int) $sub['id'] ?>" class="btn btn-outline-primary mb-4">
                                                                    <i class="fa-solid fa-download me-2"></i>
                                                                    <?= htmlspecialchars($sub['file_name'] ?: 'Download attachment') ?>
                                                                </a>
                                                            <?php endif; ?>

                                                            <div class="row align-items-center bg-light p-3 rounded border">
                                                                <div class="col-md-6">
                                                                    <label for="grade<?= (int) $sub['id'] ?>" class="form-label fw-bold mb-0">Assign Grade (0-100):</label>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="number" class="form-control form-control-lg text-center" id="grade<?= (int) $sub['id'] ?>" name="score" min="0" max="100" required value="<?= htmlspecialchars((string) ($sub['grade'] ?? '')) ?>">
                                                                </div>
                                                            </div>

                                                            <div class="mt-3">
                                                                <label for="feedback<?= (int) $sub['id'] ?>" class="form-label fw-bold">Feedback</label>
                                                                <textarea class="form-control" id="feedback<?= (int) $sub['id'] ?>" name="feedback" rows="3" placeholder="Optional comment for the student..."><?= htmlspecialchars((string) ($sub['feedback'] ?? '')) ?></textarea>
                                                            </div>

                                                            <input type="hidden" name="assignment_id" value="<?= (int) $assignment['id'] ?>">
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-save me-1"></i> Save Grade</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No submissions yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
