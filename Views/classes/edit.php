<?php
/** @var array $class */
/** @var array $teachers */
/** @var array $data */
$pageTitle = 'Edit Class';
$formAction = BASE_URL . '/class/edit/' . (int) ($class['id'] ?? 0);
$submitLabel = 'Update Class';

require __DIR__ . '/_form.php';