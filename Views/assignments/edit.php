<?php
$pageTitle = 'Edit Assignment';
$formAction = BASE_URL . '/assignment/edit/' . (int) ($assignment['id'] ?? 0);
$submitLabel = 'Save Changes';

require __DIR__ . '/_form.php';