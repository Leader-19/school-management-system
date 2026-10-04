<div class="login-page">
    <div class="login-shell">
        <!-- Brand panel -->
        <aside class="login-aside">
            <div>
                <span class="brand-tile mb-4"><i class="fa-solid fa-graduation-cap"></i></span>
                <h1 class="login-aside-title mb-3">School Management System</h1>
                <p class="login-aside-text mb-4">
                    One place for students, classes, assignments and grades.
                </p>

                <div class="d-flex flex-column gap-3">
                    <div class="login-feature">
                        <i class="fa-solid fa-users"></i>
                        <span>Manage students, classes and enrolments in seconds.</span>
                    </div>
                    <div class="login-feature">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Post assignments and track every submission.</span>
                    </div>
                    <div class="login-feature">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Record grades and follow performance over time.</span>
                    </div>
                </div>
            </div>

            <small class="text-white-50">&copy; <?= date('Y') ?> School MS</small>
        </aside>

        <!-- Form panel -->
        <section class="login-main">
            <div class="mb-4">
                <h2 class="login-title mb-1">Welcome back</h2>
                <p class="text-muted mb-0">Sign in to your account to continue.</p>
            </div>

            <form action="<?= BASE_URL ?>/login" method="POST">
                <?= ViewHelper::csrfField() ?>
                <div class="mb-3">
                    <label for="login" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" class="form-control" id="login" name="login" required autofocus
                               placeholder="Enter your username or email" autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required
                               placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" class="btn btn-light border" data-password-toggle="password"
                                aria-label="Show password" tabindex="-1">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
                </button>
            </form>
        </section>
    </div>
</div>
