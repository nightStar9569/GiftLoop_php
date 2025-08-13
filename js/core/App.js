/**
 * Core Application Module
 * Handles common functionality across all pages
 */
class App {
    constructor() {
        this.currentPage = this.getCurrentPage();
        this.init();
    }

    init() {
        this.setupNavigation();
        this.setupCommonEventListeners();
        this.injectBackToTop();
        this.checkLoginStatus();
        this.setupAnimations();
    }

    getCurrentPage() {
        const path = window.location.pathname;
        const filename = path.split('/').pop().replace(/\.(html|php)$/,'');
        if (filename === '' || filename === 'index') return 'home';
        return filename;
    }

    setupNavigation() {
        // Set active navigation item
        const navLinks = document.querySelectorAll('[data-nav]');
        navLinks.forEach(link => {
            if (link.getAttribute('data-nav') === this.currentPage) {
                link.classList.add('active');
            }
        });

        // Mobile menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');
        
        if (hamburger && navMenu) {
            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
            });

            // Close mobile menu when clicking on a link
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                });
            });
        }

        // Navbar scroll effect
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            const onScroll = () => {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }
    }

    setupCommonEventListeners() {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Button ripple effects
        document.querySelectorAll('.btn-primary, .btn-secondary, .btn-pricing').forEach(button => {
            button.addEventListener('click', (e) => {
                this.createRippleEffect(e, button);
            });
        });
    }

    async checkLoginStatus() {
        // Try to sync with backend session first (for OAuth flows)
        try {
            const me = await (window.giftApi?.me?.());
            if (me && me.user) {
                localStorage.setItem('userData', JSON.stringify(me.user));
                localStorage.setItem('isLoggedIn', 'true');
            }
        } catch (_) {}
        const isLoggedIn = localStorage.getItem('isLoggedIn');
        const userMenu = document.querySelector('.user-menu');
        const loginBtn = document.getElementById('login-btn');
        
        if (isLoggedIn === 'true') {
            if (userMenu) userMenu.style.display = 'block';
            if (loginBtn) loginBtn.style.display = 'none';
            
            // Update user name
            const userData = JSON.parse(localStorage.getItem('userData') || '{}');
            const userName = document.querySelector('.user-name');
            if (userName) {
                userName.textContent = userData.firstName || 'ユーザー';
            }
            // Update avatar image
            const navAvatar = document.getElementById('navAvatar');
            const avatarUrl = userData.avatarUrl || userData.avatar_url;
            if (navAvatar && avatarUrl) {
                navAvatar.innerHTML = `<img src="${avatarUrl}" alt="avatar" style="width:100%;height:100%;object-fit:cover" />`;
            }
            // Show admin nav if admin
            const adminNav = document.querySelector('.admin-nav');
            if (adminNav && (userData.is_admin || userData.isAdmin)) {
                adminNav.style.display = 'block';
            }
            
            this.setupUserMenu();
        } else {
            if (userMenu) userMenu.style.display = 'none';
            if (loginBtn) loginBtn.style.display = 'block';
            const adminNav = document.querySelector('.admin-nav');
            if (adminNav) adminNav.style.display = 'none';
        }
    }

    setupUserMenu() {
        const userMenu = document.querySelector('.user-menu');
        const userDropdown = document.querySelector('.user-dropdown');
        const userProfile = document.querySelector('.user-profile');
        
        if (userMenu && userDropdown) {
            // Only prevent default behavior when clicking on the user profile button
            if (userProfile) {
                userProfile.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    userDropdown.classList.toggle('active');
                });
            }
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenu.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });

            // Logout functionality
            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.logout();
                });
            }
        }
    }

    async logout() {
        try {
            await (window.giftApi?.logout?.() || Promise.resolve());
        } catch (_) {
            // ignore
        }
        localStorage.removeItem('userData');
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('rememberMe');
        
        this.showNotification('ログアウトしました。', 'info');
        
        setTimeout(() => {
            window.location.reload();
        }, 800);
    }

    setupAnimations() {
        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.step, .feature-card, .pricing-card, .demo-container, .contact-content').forEach(el => {
            el.classList.add('loading');
            observer.observe(el);
        });
    }

    injectBackToTop() {
        // Avoid duplicate
        if (document.querySelector('.back-to-top')) return;
        const btn = document.createElement('button');
        btn.className = 'back-to-top';
        btn.setAttribute('aria-label', 'ページ上部へ戻る');
        btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        document.body.appendChild(btn);

        const onScroll = () => {
            const show = window.scrollY > 300;
            btn.classList.toggle('show', show);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    createRippleEffect(e, button) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        `;
        
        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            info: '#e91e63'
        };
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${colors[type]};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            font-weight: 600;
            max-width: 300px;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 4 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});

// Add CSS for ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Re-run setup after partials (header/footer) are injected
document.addEventListener('partials:loaded', () => {
    if (window.app) {
        window.app.setupNavigation();
        window.app.checkLoginStatus();
    }
}); 