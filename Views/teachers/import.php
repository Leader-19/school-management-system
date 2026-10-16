<?php
$pageTitle = 'Import Teachers';
$summary = $summary ?? null;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-0 text-gray-800">Import Teachers</h2>
        <p class="text-muted small mb-0 mt-1">Create multiple teacher accounts from an Excel or CSV file.</p>
    </div>
    <a href="<?= BASE_URL ?>/teacher" class="btn btn-sm btn-light border"><i class="fa-solid fa-arrow-left me-1"></i> Back to Teachers</a>
</div>

<?php if ($summary !== null): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold text-primary">Import results</h6></div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-md-4"><div class="border rounded p-3"><div class="h4 mb-0"><?= (int) $summary['total'] ?></div><small class="text-muted">Rows read</small></div></div>
            <div class="col-md-4"><div class="border rounded p-3"><div class="h4 mb-0 text-success"><?= (int) $summary['created'] ?></div><small class="text-muted">Created</small></div></div>
            <div class="col-md-4"><div class="border rounded p-3"><div class="h4 mb-0 <?= $summary['failed'] ? 'text-danger' : '' ?>"><?= (int) $summary['failed'] ?></div><small class="text-muted">Failed</small></div></div>
        </div>
    </div>
    <?php if (!empty($summary['errors'])): ?>
    <div class="card-body border-top">
        <h6 class="fw-bold">Rows that need attention</h6>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>File row</th><th>Username</th><th>Issue</th></tr></thead>
            <tbody><?php foreach ($summary['errors'] as $error): ?><tr>
                <td><?= (int) $error['row'] ?></td><td><?= htmlspecialchars($error['username']) ?></td><td><?= htmlspecialchars($error['message']) ?></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
        <?php if (!empty($summary['errors_omitted'])): ?><p class="small text-muted mt-2 mb-0"><?= (int) $summary['errors_omitted'] ?> additional errors were omitted.</p><?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5"><div class="card shadow-sm h-100">
        <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-file-arrow-up me-2"></i>Upload spreadsheet</h6></div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/teacher/import" method="POST" enctype="multipart/form-data">
                <?= ViewHelper::csrfField() ?>
                <label for="importFile" class="form-label fw-semibold">Choose a CSV or Excel file</label>
                <input class="form-control mb-2" type="file" id="importFile" name="import_file" accept=".csv,.xlsx" required>
                <div class="form-text mb-3">Up to 10 MB and 1,000 teacher rows per import. Each row is checked independently.</div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i> Import Teachers</button>
                <a href="<?= BASE_URL ?>/teacher/import-template" class="btn btn-outline-success"><i class="fa-solid fa-download me-1"></i> Download CSV template</a>
            </form>
        </div>
    </div></div>
    <div class="col-lg-7"><div class="card shadow-sm h-100">
        <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-list-check me-2"></i>Required columns</h6></div>
        <div class="card-body"><div class="table-responsive"><table class="table table-sm align-middle mb-3">
            <thead class="table-light"><tr><th>Column</th><th>Rule</th></tr></thead>
            <tbody>
                <tr><td><code>username</code></td><td>Required, at least 3 characters, unique.</td></tr>
                <tr><td><code>email</code></td><td>Required, valid and unique.</td></tr>
                <tr><td><code>password</code></td><td>Required, at least 6 characters.</td></tr>
                <tr><td><code>status</code></td><td>Optional: <code>active</code> or <code>inactive</code>. Defaults to active.</td></tr>
            </tbody>
        </table></div><p class="small text-muted mb-0">Account passwords are stored as hashes by the application. Share initial passwords with teachers securely.</p></div>
    </div></div>
</div>
