<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Header & Quick Actions -->
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="page-title fw-bold text-dark mb-1">Welcome back, <?= htmlspecialchars($adminName ?? 'Administrator') ?> 👋</h2>
        <p class="page-subtitle text-muted mb-0">Here's an overview of your school today, <?= date('l, M j, Y') ?>.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/student/create" class="btn btn-sm btn-primary shadow-sm px-3 py-2 rounded-pill"><i class="fa-solid fa-user-plus me-1"></i> Add Student</a>
        <a href="<?= BASE_URL ?>/class/create" class="btn btn-sm btn-info text-white shadow-sm px-3 py-2 rounded-pill"><i class="fa-solid fa-door-open me-1"></i> New Class</a>
        <a href="<?= BASE_URL ?>/assignment/create" class="btn btn-sm btn-success shadow-sm px-3 py-2 rounded-pill"><i class="fa-solid fa-plus me-1"></i> Create Assignment</a>
    </div>
</div>

<!-- Stat Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <span class="stat-icon shadow-sm"><i class="fa-solid fa-users"></i></span>
                <div class="min-width-0">
                    <div class="stat-label text-muted text-uppercase fs-7 fw-semibold">Total Students</div>
                    <div class="stat-value fs-4 fw-bold text-dark"><?= $totalStudents ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <span class="stat-icon tone-success shadow-sm"><i class="fa-solid fa-chalkboard-user"></i></span>
                <div class="min-width-0">
                    <div class="stat-label text-muted text-uppercase fs-7 fw-semibold">Total Teachers</div>
                    <div class="stat-value fs-4 fw-bold text-dark"><?= $totalTeachers ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <span class="stat-icon tone-info shadow-sm"><i class="fa-solid fa-door-open"></i></span>
                <div class="min-width-0">
                    <div class="stat-label text-muted text-uppercase fs-7 fw-semibold">Total Classes</div>
                    <div class="stat-value fs-4 fw-bold text-dark"><?= $totalClasses ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <span class="stat-icon tone-warning shadow-sm"><i class="fa-solid fa-clipboard-list"></i></span>
                <div class="min-width-0">
                    <div class="stat-label text-muted text-uppercase fs-7 fw-semibold">Assignments</div>
                    <div class="stat-value fs-4 fw-bold text-dark"><?= $totalAssignments ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphs & Visual Overview Row -->
<div class="row g-4 mb-4">
    <!-- School Overview Chart with Attendance Line -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-chart-column me-2"></i>School & Attendance Overview (ស្ថិតិសិស្ស និងវត្តមាន)</h6>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 small">Monthly</span>
            </div>
            <div class="card-body px-4 pb-4">
                <div style="height: 290px; position: relative;">
                    <canvas id="schoolOverviewChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Male vs Female Students Chart with Center Icon & Total Count Only -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <span class="me-2">
                        <i class="fa-solid fa-person" style="color: #38bdf8;" title="Boy"></i>
                        <i class="fa-solid fa-person-dress ms-1" style="color: #fbbf24;" title="Girl"></i>
                    </span>
                    Student Gender (សិស្សប្រុស-ស្រី)
                </h6>
                <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1 small">Ratio</span>
            </div>
            <div class="card-body px-4 pb-4 d-flex flex-column align-items-center justify-content-center">

                <div style="height: 220px; width: 100%; position: relative;" class="d-flex align-items-center justify-content-center">
                    <canvas id="genderPieChart"></canvas>

                    <!-- Center Person & Dress Silhouettes Overlay -->
                    <div class="position-absolute text-center d-flex flex-column align-items-center justify-content-center pe-none" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-person fs-4" style="color: #38bdf8;" title="Male"></i>
                            <i class="fa-solid fa-person-dress fs-4" style="color: #fbbf24;" title="Female"></i>
                        </div>
                        <div class="fw-extrabold fs-2 text-dark lh-1" style="font-weight: 800; letter-spacing: -0.5px;"><?= (int)($totalStudents ?? 0) ?></div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-4 mt-3 w-100">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle shadow-sm" style="width: 12px; height: 12px; background-color: #38bdf8; display: inline-block;"></span>
                        <small class="text-muted fw-semibold">Male (ប្រុស):</small>
                        <span class="fw-bold text-dark"><?= (int)($totalMaleStudents ?? 0) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle shadow-sm" style="width: 12px; height: 12px; background-color: #fbbf24; display: inline-block;"></span>
                        <small class="text-muted fw-semibold">Female (ស្រី):</small>
                        <span class="fw-bold text-dark"><?= (int)($totalFemaleStudents ?? 0) ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Main Activity & Getting Started Row -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-bell me-2"></i>Recent Activity</h6>
                <a href="<?= BASE_URL ?>/class" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Manage Classes</a>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4 bg-light border border-light-subtle">
                    <span class="stat-icon tone-success flex-shrink-0 shadow-sm" style="width: 2.6rem; height: 2.6rem; font-size: 1rem;">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </span>
                    <div>
                        <div class="fw-bold text-dark">System is running smoothly</div>
                        <div class="text-muted small">All services operational. Welcome back to the admin portal.</div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2 ms-auto">Live</span>
                </div>

                <div class="row g-3">
                    <div class="col-sm-4">
                        <a href="<?= BASE_URL ?>/student" class="text-decoration-none">
                            <div class="d-flex align-items-center gap-2 p-3 rounded-4 border bg-white h-100 quick-link shadow-sm">
                                <i class="fa-solid fa-users text-primary fs-5"></i>
                                <span class="fw-semibold text-body">View Students</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-4">
                        <a href="<?= BASE_URL ?>/assignment" class="text-decoration-none">
                            <div class="d-flex align-items-center gap-2 p-3 rounded-4 border bg-white h-100 quick-link shadow-sm">
                                <i class="fa-solid fa-book text-info fs-5"></i>
                                <span class="fw-semibold text-body">View Assignments</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-4">
                        <a href="<?= BASE_URL ?>/grade" class="text-decoration-none">
                            <div class="d-flex align-items-center gap-2 p-3 rounded-4 border bg-white h-100 quick-link shadow-sm">
                                <i class="fa-solid fa-graduation-cap text-success fs-5"></i>
                                <span class="fw-semibold text-body">View Grades</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-rocket me-2"></i>Getting Started</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex flex-column gap-3">
                    <a href="<?= BASE_URL ?>/student/create" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item p-2 rounded-3">
                        <span class="stat-icon tone-info shadow-sm flex-shrink-0" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-user-plus"></i></span>
                        <span>
                            <span class="fw-semibold text-dark d-block">Add students</span>
                            <span class="text-muted small">Create student accounts one by one.</span>
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/class/create" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item p-2 rounded-3">
                        <span class="stat-icon tone-warning shadow-sm flex-shrink-0" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-door-open"></i></span>
                        <span>
                            <span class="fw-semibold text-dark d-block">Set up classes</span>
                            <span class="text-muted small">Group students and assign homeroom teachers.</span>
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/assignment/create" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item p-2 rounded-3">
                        <span class="stat-icon tone-success shadow-sm flex-shrink-0" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span>
                            <span class="fw-semibold text-dark d-block">Post an assignment</span>
                            <span class="text-muted small">Give work to a class with a due date.</span>
                        </span>
                    </a>
                    <a href="<?= BASE_URL ?>/grade" class="d-flex align-items-center gap-3 text-decoration-none getting-started-item p-2 rounded-3">
                        <span class="stat-icon shadow-sm flex-shrink-0" style="width: 2.4rem; height: 2.4rem; font-size: 0.9rem;"><i class="fa-solid fa-award"></i></span>
                        <span>
                            <span class="fw-semibold text-dark d-block">Record grades</span>
                            <span class="text-muted small">Enter results so students can track progress.</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // ១. ទិន្នន័យសិស្សសរុប និងអត្រាវត្តមានប្រចាំខែពី PHP
    const monthlyData = <?= json_encode($monthlyEnrollments ?? [0,0,0,0,0,0,0,0,0,0,0,0]) ?>;
    const attendanceData = <?= json_encode($monthlyAttendance ?? [0,0,0,0,0,0,0,0,0,0,0,0]) ?>;

    const ctxOverview = document.getElementById('schoolOverviewChart').getContext('2d');
    new Chart(ctxOverview, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'សិស្សសរុប (Total Students)',
                    data: monthlyData,
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'អត្រាវត្តមាន (%) (Attendance Rate)',
                    data: attendanceData,
                    type: 'line',
                    borderColor: '#198754',
                    backgroundColor: '#198754',
                    borderWidth: 3,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    ticks: { precision: 0 }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    min: 0,
                    max: 100,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: function(value) { return value + '%'; }
                    }
                }
            }
        }
    });

    // ២. ទិន្នន័យយេនឌ័រប្រុស-ស្រី
    const maleStudents = <?= (int)($totalMaleStudents ?? 0) ?>;
    const femaleStudents = <?= (int)($totalFemaleStudents ?? 0) ?>;

    const ctxGender = document.getElementById('genderPieChart').getContext('2d');
    const hasData = maleStudents > 0 || femaleStudents > 0;
    const chartData = hasData ? [maleStudents, femaleStudents] : [1, 1];
    const chartColors = hasData ? ['#38bdf8', '#fbbf24'] : ['#e2e8f0', '#cbd5e1'];

    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: ['Male (សិស្សប្រុស)', 'Female (សិស្សស្រី)'],
            datasets: [{
                data: chartData,
                backgroundColor: chartColors,
                borderWidth: 0,
                borderRadius: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (!hasData) return ' គ្មានទិន្នន័យ';
                            return ' ' + context.label + ': ' + context.raw + ' នាក់';
                        }
                    }
                }
            },
            cutout: '76%'
        }
    });
});
</script>