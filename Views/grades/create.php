<?php
$pageTitle = 'Add Grade';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Add Manual Grade</h2>
    <a href="<?= BASE_URL ?>/grade" class="btn btn-sm btn-secondary shadow-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to Grades</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-award me-2"></i>Grade Details</h6>
    </div>
    <div class="card-body p-4">
        <form action="<?= BASE_URL ?>/grade/create" method="POST" class="needs-validation" novalidate>
            <?= ViewHelper::csrfField() ?>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="student_id" class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                    <select class="form-select" id="student_id" name="student_id" required>
                        <option value="">Select Student...</option>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['id']) ?>" <?= (isset($data['student_id']) && $data['student_id'] == $student['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">Please select a student.</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="subject_id" class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <select class="form-select" id="subject_id" name="subject_id" required>
                        <option value="">Select Subject...</option>
                        <?php if (!empty($subjects)): ?>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= htmlspecialchars($subject['id']) ?>" <?= (isset($data['subject_id']) && $data['subject_id'] == $subject['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subject['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">Please select a subject.</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="score" class="form-label fw-semibold">Score (0-100) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="score" name="score" min="0" max="100" value="<?= htmlspecialchars($data['score'] ?? '') ?>" required>
                    <div class="invalid-feedback">Please provide a score between 0 and 100.</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="semester" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                    <select class="form-select" id="semester" name="semester" required>
                        <option value="1" <?= (isset($data['semester']) && $data['semester'] == '1') ? 'selected' : '' ?>>Semester 1</option>
                        <option value="2" <?= (isset($data['semester']) && $data['semester'] == '2') ? 'selected' : '' ?>>Semester 2</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="academic_year" class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="academic_year" name="academic_year" value="<?= htmlspecialchars($data['academic_year'] ?? date('Y')) ?>" required>
                </div>
            </div>

            <div class="text-end pt-3 mt-2 border-top">
                <a href="<?= BASE_URL ?>/grade" class="btn btn-light me-2">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-save me-1"></i> Save Grade</button>
            </div>
        </form>
    </div>
</div>
