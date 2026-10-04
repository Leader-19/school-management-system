<?php
/**
 * Bulk import students from an Excel/CSV spreadsheet.
 *
 * @var array|null $classes  All classes, for the class-name guide.
 * @var array|null $summary  Import report: total, created, failed,
 *                           created_names, errors, errors_omitted.
 */
$pageTitle = 'Import Students';
$summary   = $summary ?? null;
$classes   = $classes ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Import Students</h2>
        <p class="text-muted small mb-0 mt-1">Create many students at once from an Excel (.xlsx) or CSV file.</p>
    </div>
    <a href="<?= BASE_URL ?>/student" class="btn btn-sm btn-light border">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Students
    </a>
</div>

<?php if ($summary !== null): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-clipboard-check me-2"></i>Import Result
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-1">
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <div class="h4 mb-0"><?= (int) $summary['total'] ?></div>
                    <div class="text-muted small">Rows read</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <div class="h4 mb-0 text-success"><?= (int) $summary['created'] ?></div>
                    <div class="text-muted small">Created</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <div class="h4 mb-0 <?= (int) $summary['failed'] > 0 ? 'text-danger' : '' ?>">
                        <?= (int) $summary['failed'] ?>
                    </div>
                    <div class="text-muted small">Failed</div>
                </div>
            </div>
        </div>

        <?php if ((int) $summary['created'] > 0): ?>
            <div class="alert alert-success mb-3 mt-3">
                <i class="fa-solid fa-circle-check me-1"></i>
                <strong><?= (int) $summary['created'] ?></strong> student<?= (int) $summary['created'] === 1 ? '' : 's' ?> created.
                They can sign in with the username and password from the file.
                <?php if (!empty($summary['created_names'])): ?>
                    <div class="small mt-1 text-body-secondary">
                        <?= htmlspecialchars(implode(' &middot; ', array_slice($summary['created_names'], 0, 10))) ?>
                        <?= count($summary['created_names']) > 10 ? ' &hellip;' : '' ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($summary['errors'])): ?>
    <div class="card-body border-top bg-white">
        <h6 class="fw-bold mb-2">
            <i class="fa-solid fa-triangle-exclamation text-danger me-1"></i>
            Rows that were not imported
        </h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>File row</th>
                        <th>Student</th>
                        <th>Problem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary['errors'] as $error): ?>
                        <tr>
                            <td class="text-muted"><?= (int) $error['row'] ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($error['student']) ?></td>
                            <td><?= htmlspecialchars($error['message']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ((int) $summary['errors_omitted'] > 0): ?>
            <p class="text-muted small mt-2 mb-0">
                &hellip; and <?= (int) $summary['errors_omitted'] ?> more failed rows not shown. Fix the issues above, then upload a new file with only the remaining students.
            </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fa-solid fa-file-excel me-2"></i>Upload Spreadsheet
                </h6>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/student/import" method="POST"
                      enctype="multipart/form-data" class="needs-validation">
                    <?= ViewHelper::csrfField() ?>

                    <div class="mb-3">
                        <label for="importFile" class="form-label fw-semibold">Choose a file</label>
                        <input type="file" class="form-control" id="importFile" name="import_file"
                               accept=".xlsx,.csv" data-max-size="<?= Uploader::MAX_BYTES ?>" required>
                        <div class="invalid-feedback"></div>
                        <div class="form-text">Accepted formats: .xlsx or .csv, up to 10&nbsp;MB, at most 1000 students per file.</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-upload me-1"></i> Import Students
                        </button>
                        <a href="<?= BASE_URL ?>/student/import-template" class="btn btn-outline-success">
                            <i class="fa-solid fa-download me-1"></i> Download template (CSV)
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fa-solid fa-table-list me-2"></i>Required Columns (row 1 of the file)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-3">
                        <thead class="table-light">
                            <tr><th>Column</th><th>Rules</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>student_code</code></td><td>Required. Unique, e.g. STU001.</td></tr>
                            <tr><td><code>first_name</code></td><td>Required.</td></tr>
                            <tr><td><code>last_name</code></td><td>Required.</td></tr>
                            <tr><td><code>username</code></td><td>Required. Unique login name.</td></tr>
                            <tr><td><code>email</code></td><td>Required. Valid and unique.</td></tr>
                            <tr><td><code>password</code></td><td>Required. At least 6 characters.</td></tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mb-2">Optional columns</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-3">
                        <thead class="table-light">
                            <tr><th>Column</th><th>Rules</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>date_of_birth</code></td><td>Prefer YYYY-MM-DD; real Excel date cells also work.</td></tr>
                            <tr><td><code>gender</code></td><td>male / female / other.</td></tr>
                            <tr><td><code>class</code></td><td>Class name or class id. Leave empty for unassigned.</td></tr>
                            <tr><td><code>phone</code></td><td>Any text.</td></tr>
                            <tr><td><code>address</code></td><td>Any text.</td></tr>
                            <tr><td><code>status</code></td><td>active (default) or inactive.</td></tr>
                        </tbody>
                    </table>
                </div>

                <p class="small text-muted mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Column order does not matter and common alternative names (e.g. <em>DOB</em>,
                    <em>Name</em> headings such as <em>First Name</em>) are recognised. Students whose
                    username, email or student code already exist are skipped and reported.
                </p>
            </div>
        </div>

        <?php if (!empty($classes)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fa-solid fa-door-open me-2"></i>Class Names You Can Use
                </h6>
            </div>
            <div class="card-body">
                <?php foreach ($classes as $class): ?>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis border me-1 mb-1">
                        <?= htmlspecialchars((string) $class['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
