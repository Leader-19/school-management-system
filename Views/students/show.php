$pageTitle = 'Student Details';
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Student Details</h2>
    <div>
        <a href="<?= BASE_URL ?>/student/edit/<?= (int) $student['id'] ?>" class="btn btn-sm btn-primary shadow-sm"><i class="fa-solid fa-edit me-1"></i> Edit</a>
        <a href="<?= BASE_URL ?>/student" class="btn btn-sm btn-secondary shadow-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to List</a>
    </div>
</div>

<?php
$studentStatus = $student['user_status'] ?? 'active';
$fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-user me-2"></i>Personal Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Student Code</div>
                    <div class="col-md-8"><span class="badge bg-dark px-3 py-2"><?= htmlspecialchars((string) $student['student_code']) ?></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Full Name</div>
                    <div class="col-md-8"><?= htmlspecialchars($fullName) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Username</div>
                    <div class="col-md-8"><code><?= htmlspecialchars((string) ($student['username'] ?? 'N/A')) ?></code></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Email</div>
                    <div class="col-md-8">
                        <?php if (!empty($student['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($student['email']) ?>"><?= htmlspecialchars($student['email']) ?></a>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Date of Birth</div>
                    <div class="col-md-8">
                        <?= !empty($student['date_of_birth']) ? htmlspecialchars(date('M d, Y', strtotime($student['date_of_birth']))) : 'N/A' ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Gender</div>
                    <div class="col-md-8"><?= !empty($student['gender']) ? htmlspecialchars(ucfirst($student['gender'])) : 'N/A' ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Phone</div>
                    <div class="col-md-8"><?= htmlspecialchars((string) ($student['phone'] ?: 'N/A')) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold text-muted">Address</div>
                    <div class="col-md-8"><?= htmlspecialchars((string) ($student['address'] ?: 'N/A')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-school me-2"></i>Academic Info</h6>
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