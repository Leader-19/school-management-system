$pageTitle = 'Student Details';
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:3.25rem;height:3.25rem" aria-hidden="true"><i class="fa-solid fa-user-graduate fs-5"></i></span>
        <div>
            <h2 class="h3 mb-1 text-gray-800"><?= htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))) ?></h2>
            <div class="small text-muted">Student profile <span class="mx-1">&middot;</span> <?= htmlspecialchars((string) ($student['student_code'] ?? '')) ?></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/student/edit/<?= (int) $student['id'] ?>" class="btn btn-primary"><i class="fa-solid fa-pen-to-square me-1"></i> Edit student</a>
        <a href="<?= BASE_URL ?>/student" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Students</a>
    </div>
</div>

<?php
$studentStatus = $student['user_status'] ?? 'active';
$fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header py-3 px-4 bg-white">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-address-card me-2"></i>Personal information</h6>
            </div>
            <div class="card-body px-4">
                <div class="row py-3 border-bottom">
                    <div class="col-sm-4 small fw-semibold text-muted">Student code</div>
                    <div class="col-sm-8 mt-1 mt-sm-0"><span class="badge text-bg-primary-subtle text-primary px-3 py-2"><?= htmlspecialchars((string) $student['student_code']) ?></span></div>
                </div>
                <div class="row py-3 border-bottom">
                    <div class="col-sm-4 small fw-semibold text-muted">Full name</div>
                    <div class="col-sm-8 mt-1 mt-sm-0 fw-semibold"><?= htmlspecialchars($fullName) ?></div>
                </div>
                <div class="row py-3 border-bottom">
                    <div class="col-sm-4 small fw-semibold text-muted">Username</div>
                    <div class="col-sm-8 mt-1 mt-sm-0"><code><?= htmlspecialchars((string) ($student['username'] ?? 'N/A')) ?></code></div>
                </div>
                <div class="row py-3 border-bottom">
                    <div class="col-sm-4 small fw-semibold text-muted">Email</div>
                    <div class="col-sm-8 mt-1 mt-sm-0">
                        <?php if (!empty($student['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($student['email']) ?>"><?= htmlspecialchars($student['email']) ?></a>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row py-3 border-bottom">
                    <div class="col-sm-4 small fw-semibold text-muted">Date of birth</div>
                    <div class="col-sm-8 mt-1 mt-sm-0">
                        <?= !empty($student['date_of_birth']) ? htmlspecialchars(date('M d, Y', strtotime($student['date_of_birth']))) : 'N/A' ?>
                    </div>
                </div>
                <div class="row py-3 border-bottom">
                    <div class="col-sm-4 small fw-semibold text-muted">Gender</div>
                    <div class="col-sm-8 mt-1 mt-sm-0"><?= !empty($student['gender']) ? htmlspecialchars(ucfirst($student['gender'])) : '<span class="text-muted">Not provided</span>' ?></div>
                </div>
                <div class="row py-3 border-bottom">
                    <div class="col-sm-4 small fw-semibold text-muted">Phone</div>
                    <div class="col-sm-8 mt-1 mt-sm-0"><?= htmlspecialchars((string) (($student['phone'] ?? '') ?: 'Not provided')) ?></div>
                </div>
                <div class="row py-3">
                    <div class="col-sm-4 small fw-semibold text-muted">Address</div>
                    <div class="col-sm-8 mt-1 mt-sm-0 text-break"><?= htmlspecialchars((string) (($student['address'] ?? '') ?: 'Not provided')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-school me-2"></i>Academic information</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Class</div>
                    <div class="h5 mb-0 fw-bold"><?= htmlspecialchars((string) ($student['class_name'] ?? 'Unassigned')) ?></div>
                </div>
                <div class="mb-3">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Login Status</div>
                    <?php if ($studentStatus === 'active'): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger rounded-pill px-3 py-2">Inactive</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
