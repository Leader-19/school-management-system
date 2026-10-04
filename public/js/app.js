/**
 * School Management System - front end helpers.
 *
 * No build step and no framework: everything here is progressive
 * enhancement, so every page still works with JavaScript disabled.
 */
document.addEventListener("DOMContentLoaded", function () {

    var isDesktop = function () {
        return window.matchMedia('(min-width: 992px)').matches;
    };

    // -----------------------------------------------------------------
    // 1. Sidebar
    //    Desktop: collapsing the sidebar is a stored preference.
    //    Mobile: the sidebar is an off-canvas drawer with a backdrop.
    // -----------------------------------------------------------------
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const toggle = document.getElementById('sidebarCollapse');

    function setMobileOpen(open) {
        document.body.classList.toggle('sidebar-mobile-open', open);
        if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setDesktopCollapsed(collapsed) {
        if (sidebar) sidebar.classList.toggle('collapsed', collapsed);
        if (content) content.classList.toggle('collapsed', collapsed);
        if (toggle) toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

        try {
            window.localStorage.setItem('sms.sidebar', collapsed ? 'closed' : 'open');
        } catch (e) {
            /* private mode - the choice just will not persist */
        }
    }

    if (toggle && sidebar) {
        // Restore the previous collapse choice on desktop.
        let stored = null;
        try {
            stored = window.localStorage.getItem('sms.sidebar');
        } catch (e) {
            stored = null;
        }

        if (isDesktop() && stored === 'closed') {
            setDesktopCollapsed(true);
        }

        toggle.addEventListener('click', function () {
            if (isDesktop()) {
                setDesktopCollapsed(!sidebar.classList.contains('collapsed'));
            } else {
                setMobileOpen(!document.body.classList.contains('sidebar-mobile-open'));
            }
        });
    }

    // Clicking the backdrop or a link closes the mobile drawer.
    document.querySelectorAll('[data-sidebar-close]').forEach(function (el) {
        el.addEventListener('click', function () {
            setMobileOpen(false);
        });
    });

    if (sidebar) {
        sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (!isDesktop()) {
                    setMobileOpen(false);
                }
            });
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            setMobileOpen(false);
        }
    });

    // If the viewport crosses the breakpoint, reset the drawer state.
    window.matchMedia('(min-width: 992px)').addEventListener('change', function () {
        setMobileOpen(false);
    });

    // -----------------------------------------------------------------
    // 2. Confirm destructive actions
    // -----------------------------------------------------------------
    document.querySelectorAll('.confirm-delete').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const message = form.dataset.confirm
                || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!window.confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // -----------------------------------------------------------------
    // 3. Auto-dismiss flash messages
    // -----------------------------------------------------------------
    document.querySelectorAll('.flash-message').forEach(function (msg) {
        if (!window.bootstrap || !bootstrap.Alert) return;

        setTimeout(function () {
            try {
                bootstrap.Alert.getOrCreateInstance(msg).close();
            } catch (e) {
                msg.remove();
            }
        }, 6000);
    });

    // -----------------------------------------------------------------
    // 4. Bootstrap client-side validation
    // -----------------------------------------------------------------
    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // -----------------------------------------------------------------
    // 5. Client-side table filter (debounced) + "/" keyboard shortcut
    // -----------------------------------------------------------------
    const searchInput = document.getElementById('tableSearch');

    if (searchInput) {
        const target = searchInput.dataset.target || '#dataTable';
        const rows = () => document.querySelectorAll(target + ' tbody .searchable-row');
        let debounceTimer = null;

        function applyFilter() {
            const filter = searchInput.value.trim().toLowerCase();

            rows().forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });

            const noneRow = document.querySelector(target + ' .no-match');
            if (noneRow) {
                const visible = Array.from(rows()).filter(function (r) {
                    return r.style.display !== 'none';
                });
                noneRow.style.display = visible.length ? 'none' : '';
            }
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(applyFilter, 200);
        });

        // Press "/" anywhere (outside a field) to jump to search.
        document.addEventListener('keydown', function (e) {
            if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;

            const tag = (document.activeElement && document.activeElement.tagName) || '';
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;

            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        });
    }

    // -----------------------------------------------------------------
    // 6. Password visibility toggles ([data-password-toggle] buttons)
    // -----------------------------------------------------------------
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        button.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            }
        });
    });

    // -----------------------------------------------------------------
    // 7. File inputs: show the chosen file, enforce the size limit
    //    before the upload starts. The server re-validates regardless.
    // -----------------------------------------------------------------
    document.querySelectorAll('input[type="file"][data-max-size]').forEach(function (input) {
        const maxBytes = parseInt(input.dataset.maxSize, 10) || 0;
        const wrapper = input.closest('.border, .mb-4, div') || input.parentElement;
        const feedback = wrapper.querySelector('.invalid-feedback');

        const label = document.createElement('div');
        label.className = 'form-text text-success fw-semibold mt-1 d-none';

        const help = wrapper.querySelector('.form-text');
        wrapper.insertBefore(label, help ? help.nextSibling : input.nextSibling);

        function reset() {
            input.classList.remove('is-invalid');
            label.classList.add('d-none');
            if (feedback) feedback.textContent = '';
        }

        input.addEventListener('change', function () {
            reset();

            const file = this.files && this.files[0];
            if (!file) return;

            if (maxBytes && file.size > maxBytes) {
                this.classList.add('is-invalid');
                if (feedback) {
                    feedback.textContent = 'This file is ' + formatBytes(file.size) +
                        ', which exceeds the ' + formatBytes(maxBytes) + ' limit.';
                }
                return;
            }

            label.textContent = 'Selected: ' + file.name + ' (' + formatBytes(file.size) + ')';
            label.classList.remove('d-none');
        });

        if (input.form) {
            input.form.addEventListener('submit', function (event) {
                if (input.classList.contains('is-invalid')) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);
        }
    });

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        const units = ['KB', 'MB', 'GB'];
        let value = bytes / 1024;
        let unit = 0;
        while (value >= 1024 && unit < units.length - 1) {
            value /= 1024;
            unit++;
        }
        return (Math.round(value * 10) / 10) + ' ' + units[unit];
    }

    // -----------------------------------------------------------------
    // 8. Submit-once, so a double click cannot create two records
    // -----------------------------------------------------------------
    document.querySelectorAll('form:not([data-no-lock])').forEach(function (form) {
        form.addEventListener('submit', function () {
            const button = form.querySelector('button[type="submit"]:not([data-no-lock])');
            if (!button || button.dataset.busy === '1') return;

            button.dataset.busy = '1';
            button.disabled = true;

            // Re-enable if the browser restores the page from bfcache.
            setTimeout(function () {
                button.disabled = false;
                button.dataset.busy = '';
            }, 8000);
        });
    });

    // -----------------------------------------------------------------
    // 9. Accessibility touch-ups
    // -----------------------------------------------------------------
    document.querySelectorAll('.breadcrumb').forEach(function (crumb) {
        crumb.setAttribute('aria-label', 'Breadcrumb');
    });
});
