<?php
$pageTitle = 'Edit Student';
$formAction = BASE_URL . '/student/edit/' . (int) ($student['id'] ?? 0);
$submitLabel = 'Update Student';
$showPasswordHint = true;
$showAccountStatus = true;
$validationErrors = [];

require __DIR__ . '/_form.php';