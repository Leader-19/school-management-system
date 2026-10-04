<?php
/**
 * Application Route Table
 * -----------------------
 * All application routes are defined here, separate from the routing
 * engine (Core/Router.php) and the controllers (Controllers/).
 *
 * Format:
 *     ['HTTP_METHOD', '/url/path', 'ControllerName', 'actionMethod']
 *     ['HTTP_METHOD', '/url/path', 'ControllerName', 'actionMethod', [middleware]]
 *
 * Path parameters use {placeholder} syntax and are passed to the
 * controller action in order, e.g.:
 *     ['GET', '/student/edit/{id}', 'StudentController', 'edit']
 *
 * Middleware (optional 5th element) short-circuits the request before the
 * controller runs:
 *     AuthMiddleware::class                          - must be signed in
 *     [PermissionMiddleware::class, ['manage_x']]    - must hold the permission
 *     CsrfMiddleware::class                          - token check on POST
 */

// return router end point

return [

    // ------------------------------------------------------------------
    // Home / Dashboard
    // ------------------------------------------------------------------
    ['GET', '/', 'DashboardController', 'index', [AuthMiddleware::class]],
    ['GET', '/dashboard', 'DashboardController', 'index', [AuthMiddleware::class]],

    // ------------------------------------------------------------------
    // Authentication
    // ------------------------------------------------------------------
    ['GET', '/login', 'AuthController', 'index'],
    ['POST', '/login', 'AuthController', 'login'],
    ['GET', '/logout', 'AuthController', 'logout'],

    // Aliases for the /auth URLs (kept so older links keep working)
    ['GET', '/auth', 'AuthController', 'index'],
    ['GET', '/auth/login', 'AuthController', 'index'],
    ['POST', '/auth/login', 'AuthController', 'login'],
    ['GET', '/auth/logout', 'AuthController', 'logout'],

    // ------------------------------------------------------------------
    // Students
    // ------------------------------------------------------------------
    ['GET', '/student', 'StudentController', 'index', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],
    ['GET', '/student/create', 'StudentController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],
    ['POST', '/student/create', 'StudentController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']], CsrfMiddleware::class]],
    ['GET', '/student/edit/{id}', 'StudentController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],
    ['POST', '/student/edit/{id}', 'StudentController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']], CsrfMiddleware::class]],
    ['POST', '/student/delete/{id}', 'StudentController', 'delete', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']], CsrfMiddleware::class]],
    ['GET', '/student/show/{id}', 'StudentController', 'show', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],

    // Bulk import from an Excel/CSV spreadsheet
    ['GET', '/student/import', 'StudentController', 'import', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],
    ['POST', '/student/import', 'StudentController', 'import', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']], CsrfMiddleware::class]],
    ['GET', '/student/import-template', 'StudentController', 'template', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],

    // ------------------------------------------------------------------
    // Classes
    // ------------------------------------------------------------------
    ['GET', '/class', 'ClassController', 'index', [AuthMiddleware::class]],

    // ------------------------------------------------------------------
    // Teachers (admin)
    // ------------------------------------------------------------------
    ['GET', '/teacher', 'TeacherController', 'index', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_users']]]],
    ['GET', '/teacher/create', 'TeacherController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_users']]]],
    ['POST', '/teacher/create', 'TeacherController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_users']], CsrfMiddleware::class]],
    ['GET', '/teacher/edit/{id}', 'TeacherController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_users']]]],
    ['POST', '/teacher/edit/{id}', 'TeacherController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_users']], CsrfMiddleware::class]],
    ['POST', '/teacher/delete/{id}', 'TeacherController', 'delete', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_users']], CsrfMiddleware::class]],

    // ------------------------------------------------------------------
    // Attendance (staff with manage_students)
    // ------------------------------------------------------------------
    ['GET', '/attendance', 'AttendanceController', 'index', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],
    ['GET', '/attendance/take/{id}', 'AttendanceController', 'take', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],
    ['POST', '/attendance/take/{id}', 'AttendanceController', 'take', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']], CsrfMiddleware::class]],
    ['GET', '/attendance/student/{id}', 'AttendanceController', 'student', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_students']]]],
    ['GET', '/class/create', 'ClassController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_classes']]]],
    ['POST', '/class/create', 'ClassController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_classes']], CsrfMiddleware::class]],
    ['GET', '/class/show/{id}', 'ClassController', 'show', [AuthMiddleware::class]],
    ['GET', '/class/edit/{id}', 'ClassController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_classes']]]],
    ['POST', '/class/edit/{id}', 'ClassController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_classes']], CsrfMiddleware::class]],
    ['POST', '/class/delete/{id}', 'ClassController', 'delete', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_classes']], CsrfMiddleware::class]],

    // ------------------------------------------------------------------
    // Assignments
    // ------------------------------------------------------------------
    ['GET', '/assignment', 'AssignmentController', 'index', [AuthMiddleware::class, [PermissionMiddleware::class, ['view_assignments']]]],
    ['GET', '/assignment/create', 'AssignmentController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['create_assignments']]]],
    ['POST', '/assignment/create', 'AssignmentController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['create_assignments']], CsrfMiddleware::class]],
    ['GET', '/assignment/show/{id}', 'AssignmentController', 'show', [AuthMiddleware::class, [PermissionMiddleware::class, ['view_assignments']]]],
    ['GET', '/assignment/edit/{id}', 'AssignmentController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['create_assignments']]]],
    ['POST', '/assignment/edit/{id}', 'AssignmentController', 'edit', [AuthMiddleware::class, [PermissionMiddleware::class, ['create_assignments']], CsrfMiddleware::class]],
    ['GET', '/assignment/submit/{id}', 'AssignmentController', 'submit', [AuthMiddleware::class, [PermissionMiddleware::class, ['submit_assignments']]]],
    ['POST', '/assignment/submit/{id}', 'AssignmentController', 'submit', [AuthMiddleware::class, [PermissionMiddleware::class, ['submit_assignments']], CsrfMiddleware::class]],
    ['POST', '/assignment/grade/{id}', 'AssignmentController', 'grade', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_grades']], CsrfMiddleware::class]],
    ['POST', '/assignment/delete/{id}', 'AssignmentController', 'delete', [AuthMiddleware::class, [PermissionMiddleware::class, ['create_assignments']], CsrfMiddleware::class]],

    // ------------------------------------------------------------------
    // Grades
    // ------------------------------------------------------------------
    ['GET', '/grade', 'GradeController', 'index', [AuthMiddleware::class]],
    ['GET', '/grade/create', 'GradeController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_grades']]]],
    ['POST', '/grade/create', 'GradeController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_grades']], CsrfMiddleware::class]],
    ['POST', '/grade/delete/{id}', 'GradeController', 'delete', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_grades']], CsrfMiddleware::class]],

    // ------------------------------------------------------------------
    // Uploaded files (permission checked, never served directly)
    // ------------------------------------------------------------------
    ['GET', '/file/assignment/{id}', 'FileController', 'assignmentDocument', [AuthMiddleware::class]],
    ['GET', '/file/submission/{id}', 'FileController', 'submissionFile', [AuthMiddleware::class]],
];
