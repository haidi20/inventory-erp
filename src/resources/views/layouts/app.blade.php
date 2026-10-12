<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap"
        rel="stylesheet">

    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('klinik-theme');
                var systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

                if ((savedTheme || systemTheme) === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    @if (isset($page))
        @inertiaHead
    @endif

    <script>
        window.__csrf = '{{ csrf_token() }}';
    </script>

    @stack('styles')
</head>

<body class="antialiased {{ !auth()->check() ? 'guest-mode' : '' }}">

    @auth
        <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-chevron-left" id="toggleIcon"></i>
        </button>

        <!-- == SIDEBAR ============================================ -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-logo">I</div>
                <div>
                    <div class="sidebar-brand-text">Inventory</div>
                    @if(!empty($sidebarCompanyName))<div class="sidebar-brand-sub">{{ $sidebarCompanyName }}</div>@endif
                </div>
            </div>

            <nav id="sidebar-nav"></nav>

            <div style="height: 20px;"></div>
        </aside>

        <!-- == HEADER ============================================ -->
        <!-- == HEADER ============================================ -->
        <header class="header">
            <button class="header-btn mobile-menu-btn" id="mobileMenuBtn" title="Buka menu" aria-label="Buka menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="header-search">
                {{-- search --}}
            </div>
            <div class="header-right">
                <button class="header-btn" id="themeToggle" type="button" title="Mode Gelap/Terang" aria-label="Mode Gelap/Terang">
                    <i class="fa-solid fa-moon" id="themeIcon"></i>
                </button>
                @php $userRole = auth()->user()->roles->first(); @endphp
                <div class="user-dropdown" id="userDropdown">
                    <div class="header-user" id="userDropdownToggle">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <div>
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-role">{{ $userRole ? $userRole->label : 'User' }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down" id="userDropdownChevron" style="font-size:11px;color:var(--muted);margin-left:4px;transition:transform .2s;"></i>
                    </div>
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <button type="button" class="user-dropdown-item" id="changePasswordTrigger">
                            <i class="fa-solid fa-key"></i> Ganti Password
                        </button>
                        <div class="user-dropdown-divider"></div>
                        <form method="POST" action="/logout" style="margin:0;">
                            @csrf
                            <button type="submit" class="user-dropdown-item user-dropdown-danger">
                                <i class="fa-solid fa-right-from-bracket"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Modal Ganti Password (di luar header agar tidak kena stacking context) -->
                <div class="cp-modal-overlay" id="changePasswordModal" style="display:none;">
            <div class="cp-modal-box">
                <div class="cp-modal-header">
                    <span><i class="fa-solid fa-key" style="margin-right:8px;"></i>Ganti Password</span>
                    <button class="cp-modal-close" id="changePasswordClose">&times;</button>
                </div>
                <form method="POST" action="/change-password" id="changePasswordForm">
                    @csrf
                    <div class="cp-modal-body">
                        @if(session('cp_success'))
                            <div class="cp-alert-success">{{ session('cp_success') }}</div>
                        @endif
                        <div class="cp-form-group">
                            <label class="cp-label">Password Baru</label>
                            <input type="password" name="password" class="cp-input" placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="cp-form-group">
                            <label class="cp-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="cp-input" placeholder="Ulangi password baru" required autocomplete="new-password">
                        </div>
                        @error('password')
                            <div class="cp-alert-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="cp-modal-footer">
                        <button type="button" class="cp-btn-cancel" id="changePasswordCancel">Batal</button>
                        <button type="submit" class="cp-btn-save">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endauth

    <!-- == MAIN ============================================ -->
    @auth
        <main class="app-main">
            @if (isset($page))
                @inertia
            @endif
            @yield('content')
        </main>
    @else
        @if (isset($page))
            @inertia
        @endif
        @yield('content')
    @endauth

    <script>
        (function() {
            // == Sidebar collapse toggle ============================================
            var COLLAPSE_KEY = 'klinik_sidebar_collapsed';
            var toggleBtn = document.getElementById('sidebarToggle');
            var toggleIcon = document.getElementById('toggleIcon');
            var overlay = document.getElementById('sidebarOverlay');
            var mobileBtn = document.getElementById('mobileMenuBtn');
            var mobileMedia = window.matchMedia('(max-width: 768px)');

            function applyCollapse(collapsed) {
                if (collapsed) {
                    document.body.classList.add('sidebar-collapsed');
                    toggleIcon.className = 'fa-solid fa-chevron-right';
                } else {
                    document.body.classList.remove('sidebar-collapsed');
                    toggleIcon.className = 'fa-solid fa-chevron-left';
                }
            }

            function setMobileOpen(open) {
                document.body.classList.toggle('sidebar-mobile-open', open);
            }

            function syncSidebarMode() {
                if (mobileMedia.matches) {
                    document.body.classList.remove('sidebar-collapsed');
                    setMobileOpen(false);
                    toggleIcon.className = 'fa-solid fa-chevron-left';
                    return;
                }

                applyCollapse(savedCollapse);
            }

            var savedCollapse = false;
            try {
                savedCollapse = localStorage.getItem(COLLAPSE_KEY) === 'true';
            } catch (e) {}
            syncSidebarMode();

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (mobileMedia.matches) {
                        setMobileOpen(!document.body.classList.contains('sidebar-mobile-open'));
                        return;
                    }
                    savedCollapse = !document.body.classList.contains('sidebar-collapsed');
                    applyCollapse(savedCollapse);
                    try {
                        localStorage.setItem(COLLAPSE_KEY, String(savedCollapse));
                    } catch (e) {}
                });
            }

            if (mobileBtn) {
                mobileBtn.addEventListener('click', function() {
                    setMobileOpen(!document.body.classList.contains('sidebar-mobile-open'));
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    setMobileOpen(false);
                });
            }

            var STORE_KEY = 'klinik_sidebar_state';
            var details = document.querySelectorAll('details.nav-group[data-key]');
            var saved = {};

            try {
                saved = JSON.parse(localStorage.getItem(STORE_KEY) || '{}');
            } catch (e) {}

            details.forEach(function(el) {
                var key = el.getAttribute('data-key');
                // Only restore if there's a saved preference (not overriding open attr already set by blade)
                if (saved[key] === false) el.removeAttribute('open');
                else if (saved[key] === true) el.setAttribute('open', '');

                el.addEventListener('toggle', function() {
                    saved[key] = el.open;
                    try {
                        localStorage.setItem(STORE_KEY, JSON.stringify(saved));
                    } catch (e) {}
                });
            });

            document.querySelectorAll('.nav-item, details.nav-group > summary').forEach(function(item) {
                var label = item.textContent.replace(/\s+/g, ' ').trim();
                if (!label) return;
                item.setAttribute('data-label', label);
                if (!item.getAttribute('title')) item.setAttribute('title', label);
            });

            // Auto-mark active submenu links
            function syncSidebarActiveState() {
                var path = window.location.pathname;

                // Reset all active classes
                document.querySelectorAll('.nav-submenu a, .nav-item, details.nav-group').forEach(function(el) {
                    el.classList.remove('active', 'has-active');
                });

                document.querySelectorAll('.nav-submenu a').forEach(function(link) {
                    var href = link.getAttribute('href');
                    if (href === path || (href !== '/' && path.startsWith(href))) {
                        link.classList.add('active');
                        var detail = link.closest('details.nav-group');
                        if (detail) {
                            detail.setAttribute('open', '');
                            detail.classList.add('has-active');
                        }
                    }
                });

                document.querySelectorAll('.nav-item').forEach(function(link) {
                    var href = link.getAttribute('href');
                    if (href === path || (href !== '/' && path.startsWith(href))) {
                        link.classList.add('active');
                    }
                });
            }

            syncSidebarActiveState();

            // Listen for Inertia navigation finish to re-sync sidebar
            document.addEventListener('inertia:finish', function() {
                syncSidebarActiveState();
            });

            document.querySelectorAll('.nav-item, .nav-submenu a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (mobileMedia.matches) setMobileOpen(false);
                });
            });

            window.addEventListener('resize', syncSidebarMode);

            // -- Sidebar dari Inertia shared props --
            function buildSidebarNav(menus, currentPath) {
                var nav = document.getElementById('sidebar-nav');
                if (!nav) return;
                if (!Array.isArray(menus)) return;

                nav.innerHTML = '';

                menus.forEach(function(menu) {
                    if (menu.type === 'group') {
                        var isOpen = menu.is_open || false;
                        var details = document.createElement('details');
                        details.className = 'nav-group' + (menu.is_active ? ' has-active' : '');
                        details.setAttribute('data-key', menu.key);
                        if (isOpen) details.setAttribute('open', '');

                        var summary = document.createElement('summary');
                        if (menu.url) {
                            var groupLink = document.createElement('a');
                            groupLink.href = menu.url;
                            groupLink.className = 'nav-group-link';
                            groupLink.innerHTML = '<span class="nav-icon"><i class="fa-solid ' + menu.icon + '"></i></span>' + menu.label;
                            summary.appendChild(groupLink);
                        } else {
                            summary.innerHTML = '<span class="nav-icon"><i class="fa-solid ' + menu.icon + '"></i></span>' + menu.label;
                        }
                        summary.innerHTML += '<i class="fa-solid fa-chevron-right nav-arrow"></i>';
                        details.appendChild(summary);

                        var submenu = document.createElement('div');
                        submenu.className = 'nav-submenu';
                        (menu.items || []).forEach(function(item) {
                            var a = document.createElement('a');
                            a.href = item.url;
                            a.className = item.is_active ? 'active' : '';
                            a.innerHTML = '<span class="sub-dot"></span> ' + item.label;
                            submenu.appendChild(a);
                        });
                        details.appendChild(submenu);
                        nav.appendChild(details);
                    } else {
                        var a = document.createElement('a');
                        a.href = menu.url || '#';
                        a.className = 'nav-item' + (menu.is_active ? ' active' : '');
                        a.innerHTML = '<span class="nav-icon"><i class="fa-solid ' + menu.icon + '"></i></span>' + menu.label;
                        nav.appendChild(a);
                    }
                });

                // Restore saved sidebar state
                try {
                    var saved = JSON.parse(localStorage.getItem('klinik_sidebar_state') || '{}');
                    nav.querySelectorAll('details.nav-group[data-key]').forEach(function(el) {
                        var key = el.getAttribute('data-key');
                        if (saved[key] === false) el.removeAttribute('open');
                        else if (saved[key] === true) el.setAttribute('open', '');
                        el.addEventListener('toggle', function() {
                            saved[key] = el.open;
                            localStorage.setItem('klinik_sidebar_state', JSON.stringify(saved));
                        });
                    });
                } catch(e) {}

                // Add click to close mobile
                nav.querySelectorAll('.nav-item, .nav-submenu a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.matchMedia('(max-width: 768px)').matches) {
                            document.body.classList.remove('sidebar-mobile-open');
                        }
                    });
                });
            }

            function getInertiaProps() {
                try {
                    // Inertia v3: page data diletakkan di <script data-page="app" type="application/json"> terpisah
                    var scriptEl = document.querySelector('script[data-page="app"]');
                    if (scriptEl && scriptEl.textContent) {
                        var parsed = JSON.parse(scriptEl.textContent);
                        return (parsed && parsed.props) || {};
                    }
                    // Fallback Inertia v2: data-page menempel di <div id="app" data-page="...">
                    var el = document.getElementById('app');
                    if (el && el.dataset && el.dataset.page) {
                        return JSON.parse(el.dataset.page).props || {};
                    }
                } catch(e) {}
                return {};
            }

            function refreshSidebar() {
                var props = getInertiaProps();
                buildSidebarNav(props.sidebar_menus || [], window.location.pathname);
            }

            // Render awal
            refreshSidebar();

            // Re-render setiap Inertia navigasi selesai
            document.addEventListener('inertia:success', function() {
                refreshSidebar();
                syncSidebarActiveState();
            });

        // -- User Dropdown --
        function toggleUserDropdown() {
            var menu = document.getElementById('userDropdownMenu');
            var chevron = document.getElementById('userDropdownChevron');
            var open = menu.classList.toggle('open');
            chevron.style.transform = open ? 'rotate(180deg)' : '';
        }
        document.getElementById('userDropdownToggle').addEventListener('click', toggleUserDropdown);
        document.addEventListener('click', function(e) {
            var dd = document.getElementById('userDropdown');
            if (dd && !dd.contains(e.target)) {
                document.getElementById('userDropdownMenu').classList.remove('open');
                document.getElementById('userDropdownChevron').style.transform = '';
            }
        });

        // -- Modal Ganti Password --
        function openChangePassword() {
            document.getElementById('userDropdownMenu').classList.remove('open');
            document.getElementById('userDropdownChevron').style.transform = '';
            document.getElementById('changePasswordModal').style.display = 'flex';
        }
        function closeChangePassword() {
            document.getElementById('changePasswordModal').style.display = 'none';
            document.getElementById('changePasswordForm').reset();
        }
        function closeCPOutside(e) {
            if (e.target === document.getElementById('changePasswordModal')) closeChangePassword();
        }
        document.getElementById('changePasswordTrigger').addEventListener('click', openChangePassword);
        document.getElementById('changePasswordClose').addEventListener('click', closeChangePassword);
        document.getElementById('changePasswordCancel').addEventListener('click', closeChangePassword);
        document.getElementById('changePasswordModal').addEventListener('click', closeCPOutside);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeChangePassword();
        });


            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') setMobileOpen(false);
            });

            // Fullscreen toggle
            var fsBtn = document.querySelector('[title="Fullscreen"]');
            if (fsBtn) {
                fsBtn.addEventListener('click', function() {
                    if (!document.fullscreenElement) document.documentElement.requestFullscreen();
                    else document.exitFullscreen();
                });
            }

            var themeToggle = document.getElementById('themeToggle');
            var themeIcon = document.getElementById('themeIcon');
            if (themeToggle && themeIcon) {
                themeIcon.className = document.documentElement.classList.contains('dark')
                    ? 'fa-solid fa-sun'
                    : 'fa-solid fa-moon';

                themeToggle.addEventListener('click', function() {
                    var isDark = document.documentElement.classList.toggle('dark');

                    try {
                        localStorage.setItem('klinik-theme', isDark ? 'dark' : 'light');
                    } catch (error) {}

                    themeIcon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                });
            }
        })();
    </script>


    <!-- Page Loading Overlay -->
    <div id="page-loading-overlay" style="display:none;">
        <div class="page-loading-inner">
            <div class="page-loading-spinner"></div>
        </div>
    </div>

    <style>
        #page-loading-overlay {
            position: fixed;
            inset: 0;
            background: color-mix(in srgb, var(--lv-background) 82%, transparent);
            backdrop-filter: blur(2px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .page-loading-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        .page-loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--lv-border);
            border-top-color: var(--lv-primary);
            border-radius: 50%;
            animation: page-spin 0.7s linear infinite;
        }
        @keyframes page-spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        (function() {
            var overlay = document.getElementById('page-loading-overlay');
            var timer = null;

            document.addEventListener('inertia:start', function() {
                timer = setTimeout(function() {
                    if (overlay) overlay.style.display = 'flex';
                }, 150);
            });

            document.addEventListener('inertia:finish', function() {
                if (timer) clearTimeout(timer);
                if (overlay) overlay.style.display = 'none';
            });

            document.addEventListener('inertia:error', function() {
                if (timer) clearTimeout(timer);
                if (overlay) overlay.style.display = 'none';
            });
        })();
    </script>

    @stack('scripts')
</body>

</html>


