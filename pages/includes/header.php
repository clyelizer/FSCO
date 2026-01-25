<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//force le navigateur a ne pas garder de cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache");

// Determine active page for styling
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="header">
    <div class="container header__inner">
        <a href="/index.php" class="logo">
            <i class="fas fa-brain logo-icon"></i>
            <span>FSCo</span>
        </a>
        <button class="header__toggle" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="nav">
            <a href="/index.php" class="nav__link <?= $current_page === 'index.php' ? 'active' : '' ?>">Accueil</a>
            <a href="/pages/Formations/formations.php"
                class="nav__link <?= $current_page === 'formations.php' ? 'active' : '' ?>">Formations</a>
            <a href="/pages/Ressources/ressources.php"
                class="nav__link <?= $current_page === 'ressources.php' ? 'active' : '' ?>">Ressources</a>
            <a href="/pages/Blogs/blogs.php"
                class="nav__link <?= $current_page === 'blogs.php' ? 'active' : '' ?>">Blogs</a>
            <a href="/pages/maBiblio/bibliotheque.php"
                class="nav__link <?= $current_page === 'bibliotheque.php' ? 'active' : '' ?>">Ma Bibliothèque</a>
            <a href="/pages/Suivi/suivi.php"
                class="nav__link <?= $current_page === 'suivi.php' ? 'active' : '' ?>">Suivi</a>
        </nav>
        <div class="actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu-container">
                    <button class="btn btn--outline user-menu-btn">
                        <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Mon Compte') ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <div class="user-dropdown-content">
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <a href="/pages/admin/index.php" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt"></i> Administration
                                </a>
                            <?php endif; ?>
                            <a href="/pages/auth/logout.php?redirect=login" class="dropdown-item">
                                <i class="fas fa-sync-alt"></i> Changer de compte
                            </a>
                            <!-- Logout with redirect to home -->
                            <a href="/pages/auth/logout.php" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
                <style>
                    .user-menu-container {
                        position: relative;
                        display: inline-block;
                    }

                    .user-menu-btn {
                        cursor: pointer;
                    }

                    .user-dropdown {
                        display: none;
                        position: absolute;
                        right: 0;
                        top: 100%;
                        background-color: transparent;
                        min-width: 220px;
                        z-index: 1000;
                        padding-top: 0.5rem;
                    }

                    .user-dropdown-content {
                        background-color: white;
                        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                        border-radius: 8px;
                        border: 1px solid #e5e7eb;
                        overflow: hidden;
                    }

                    .user-menu-container:hover .user-dropdown,
                    .user-menu-container.active .user-dropdown {
                        display: block;
                    }

                    .dropdown-item {
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                        padding: 0.75rem 1rem;
                        color: #374151;
                        text-decoration: none;
                        transition: background-color 0.2s;
                        font-size: 0.95rem;
                    }

                    .dropdown-item:hover {
                        background-color: #f3f4f6;
                    }

                    .dropdown-item i {
                        width: 20px;
                        text-align: center;
                    }

                    .logout-item {
                        color: #dc2626;
                        border-top: 1px solid #e5e7eb;
                    }

                    .logout-item:hover {
                        background-color: #fee2e2;
                    }

                    /* Mobile Responsive */
                    @media (max-width: 768px) {
                        .user-dropdown {
                            position: fixed;
                            right: 1rem;
                            left: auto;
                            top: 60px;
                            min-width: 200px;
                        }

                        .user-menu-container:hover .user-dropdown {
                            display: none;
                            /* Disable hover on mobile */
                        }

                        .user-menu-container.active .user-dropdown {
                            display: block;
                        }

                        .dropdown-item {
                            padding: 1rem;
                            font-size: 1rem;
                        }

                        .user-menu-btn {
                            padding: 0.5rem 0.75rem;
                            font-size: 0.9rem;
                        }
                    }

                    @media (max-width: 480px) {
                        .user-dropdown {
                            right: 0.5rem;
                            min-width: 180px;
                        }

                        .dropdown-item {
                            padding: 0.875rem 0.75rem;
                        }
                    }
                </style>
                <script>
                    // Mobile-friendly dropdown toggle
                    document.addEventListener('DOMContentLoaded', function () {
                        const userMenuBtn = document.querySelector('.user-menu-btn');
                        const userMenuContainer = document.querySelector('.user-menu-container');

                        if (userMenuBtn && userMenuContainer) {
                            userMenuBtn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                userMenuContainer.classList.toggle('active');
                            });

                            // Close dropdown when clicking outside
                            document.addEventListener('click', function (e) {
                                if (!userMenuContainer.contains(e.target)) {
                                    userMenuContainer.classList.remove('active');
                                }
                            });
                        }
                    });
                </script>
            <?php else: ?>
                <a href="/pages/auth/login.php" class="btn btn--outline">
                    <span>Se connecter</span>
                    <i class="fas fa-user mobile-hide-icon"></i>
                </a>
                <form class="start-form" id="headerNewsletterForm" style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="email" name="email" placeholder="saisir email pour les actualites" class="start-input"
                        required
                        style="padding: 0.5rem 1rem; border: 2px solid var(--border-color); border-radius: var(--radius-lg); font-size: 0.9rem; width: 180px;" />
                    <button type="submit" class="btn btn--primary"
                        style="padding: 0.5rem 1rem; font-size: 0.9rem;">Start</button>
                </form>
                <script>
                    document.getElementById('headerNewsletterForm').addEventListener('submit', function (e) {
                        e.preventDefault();
                        const btn = this.querySelector('button');
                        const originalText = btn.textContent;
                        btn.textContent = '...';
                        btn.disabled = true;

                        const formData = new FormData(this);

                        fetch('/pages/includes/form_mail_handler.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                if (data.status === 'success') this.reset();
                            })
                            .catch(err => {
                                console.error(err);
                                alert('Erreur de connexion');
                            })
                            .finally(() => {
                                btn.textContent = originalText;
                                btn.disabled = false;
                            });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('.header__toggle');
        const nav = document.querySelector('.nav');

        if (toggle && nav) {
            // Toggle menu on button click
            toggle.addEventListener('click', function (e) {
                e.stopPropagation(); // Prevent click from bubbling to document
                nav.classList.toggle('active');
                updateIcon();
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (e) {
                if (nav.classList.contains('active') && !nav.contains(e.target) && !toggle.contains(e.target)) {
                    nav.classList.remove('active');
                    updateIcon();
                }
            });

            // Close menu on scroll
            window.addEventListener('scroll', function () {
                if (nav.classList.contains('active')) {
                    nav.classList.remove('active');
                    updateIcon();
                }
            });

            // Helper to update icon
            function updateIcon() {
                const icon = toggle.querySelector('i');
                if (nav.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        }
    });
</script>