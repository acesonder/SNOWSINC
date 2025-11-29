/**
 * Landing Page JavaScript
 * OneSinc - Social Services Platform
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all landing page features
    LandingPage.init();
});

const LandingPage = {
    init() {
        this.initNavbar();
        this.initQuickExit();
        this.initAccessibility();
        this.initQuickHelp();
        this.initTestimonials();
        this.initDonation();
        this.initModals();
        this.initIntakeForm();
        this.initSmoothScroll();
        console.log('Landing page initialized');
    },

    // Navbar scroll effect
    initNavbar() {
        const navbar = document.getElementById('navbar');
        const toggle = document.getElementById('navbar-toggle');
        const menu = document.getElementById('navbar-menu');

        if (navbar) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        }

        if (toggle && menu) {
            toggle.addEventListener('click', () => {
                menu.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.remove('active');
                }
            });
        }
    },

    // Quick Exit functionality
    initQuickExit() {
        const exitBtn = document.getElementById('quick-exit');
        if (exitBtn) {
            exitBtn.addEventListener('click', () => {
                // Redirect to safe site immediately
                window.location.replace('https://weather.com');
            });

            // Also trigger on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    window.location.replace('https://weather.com');
                }
            });
        }
    },

    // Accessibility panel
    initAccessibility() {
        const toggle = document.getElementById('a11y-toggle');
        const panel = document.getElementById('a11y-panel');

        if (toggle && panel) {
            toggle.addEventListener('click', () => {
                panel.classList.toggle('active');
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!toggle.contains(e.target) && !panel.contains(e.target)) {
                    panel.classList.remove('active');
                }
            });
        }

        // Load saved preferences
        const prefs = this.loadAccessibilityPrefs();
        
        // Large text
        const largeTextToggle = document.getElementById('toggle-large-text');
        if (largeTextToggle) {
            largeTextToggle.checked = prefs.largeText || false;
            if (prefs.largeText) document.body.classList.add('large-text');
            largeTextToggle.addEventListener('change', (e) => {
                document.body.classList.toggle('large-text', e.target.checked);
                this.saveAccessibilityPref('largeText', e.target.checked);
            });
        }

        // High contrast
        const contrastToggle = document.getElementById('toggle-contrast');
        if (contrastToggle) {
            contrastToggle.checked = prefs.highContrast || false;
            if (prefs.highContrast) document.body.classList.add('high-contrast');
            contrastToggle.addEventListener('change', (e) => {
                document.body.classList.toggle('high-contrast', e.target.checked);
                this.saveAccessibilityPref('highContrast', e.target.checked);
            });
        }

        // Simple mode
        const simpleToggle = document.getElementById('toggle-simple');
        if (simpleToggle) {
            simpleToggle.checked = prefs.simpleMode || false;
            if (prefs.simpleMode) document.body.classList.add('simple-mode');
            simpleToggle.addEventListener('change', (e) => {
                document.body.classList.toggle('simple-mode', e.target.checked);
                this.saveAccessibilityPref('simpleMode', e.target.checked);
            });
        }
    },

    loadAccessibilityPrefs() {
        try {
            return JSON.parse(localStorage.getItem('landingA11y') || '{}');
        } catch {
            return {};
        }
    },

    saveAccessibilityPref(key, value) {
        const prefs = this.loadAccessibilityPrefs();
        prefs[key] = value;
        localStorage.setItem('landingA11y', JSON.stringify(prefs));
    },

    // Quick Help buttons
    initQuickHelp() {
        const buttons = document.querySelectorAll('.quick-help-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.action;
                this.openModal(action + '-modal');
            });
        });
    },

    // Testimonials slider
    initTestimonials() {
        const slider = document.getElementById('testimonials-slider');
        if (!slider) return;

        const cards = slider.querySelectorAll('.testimonial-card');
        const dots = document.querySelectorAll('.testimonials-dots .dot');
        const prevBtn = document.querySelector('.testimonial-prev');
        const nextBtn = document.querySelector('.testimonial-next');
        
        let currentIndex = 0;
        const total = cards.length;

        const showSlide = (index) => {
            cards.forEach((card, i) => {
                card.classList.toggle('active', i === index);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
            currentIndex = index;
        };

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                showSlide((currentIndex - 1 + total) % total);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                showSlide((currentIndex + 1) % total);
            });
        }

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => showSlide(i));
        });

        // Auto-advance every 5 seconds
        setInterval(() => {
            showSlide((currentIndex + 1) % total);
        }, 5000);
    },

    // Donation amount selection
    initDonation() {
        const amountBtns = document.querySelectorAll('.donation-amount');
        const customInput = document.querySelector('input[name="custom_amount"]');
        
        amountBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                amountBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                if (customInput) {
                    if (btn.classList.contains('custom-amount') || btn.dataset.amount === 'custom') {
                        customInput.style.display = 'block';
                        customInput.focus();
                    } else {
                        customInput.style.display = 'none';
                    }
                }
            });
        });

        // Donate button
        const donateBtn = document.querySelector('.donate-btn');
        if (donateBtn) {
            donateBtn.addEventListener('click', () => {
                this.openModal('donate-modal');
            });
        }
    },

    // Modal handling
    initModals() {
        // Close buttons
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                this.closeModal(btn.closest('.modal-overlay'));
            });
        });

        // Click outside to close
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this.closeModal(overlay);
                }
            });
        });

        // Open modal links
        document.querySelectorAll('[data-modal]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.openModal(link.dataset.modal);
            });
        });
    },

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },

    closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    },

    // Intake form multi-step
    initIntakeForm() {
        const form = document.getElementById('intake-form');
        if (!form) return;

        const steps = form.querySelectorAll('.form-step');
        const prevBtn = form.querySelector('.prev-step');
        const nextBtn = form.querySelector('.next-step');
        const submitBtn = form.querySelector('.submit-intake');
        let currentStep = 0;

        const showStep = (step) => {
            steps.forEach((s, i) => {
                s.classList.toggle('active', i === step);
            });
            
            if (prevBtn) prevBtn.style.display = step === 0 ? 'none' : 'inline-flex';
            if (nextBtn) nextBtn.style.display = step === steps.length - 1 ? 'none' : 'inline-flex';
            if (submitBtn) submitBtn.style.display = step === steps.length - 1 ? 'inline-flex' : 'none';
            
            currentStep = step;
        };

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentStep > 0) showStep(currentStep - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                // Validate current step
                const currentStepEl = steps[currentStep];
                const helpType = currentStepEl.querySelector('input[name="help_type"]:checked');
                
                if (currentStep === 0 && !helpType) {
                    this.showToast('warning', 'Please select a type of help');
                    return;
                }
                
                if (currentStep < steps.length - 1) {
                    showStep(currentStep + 1);
                }
            });
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Simple validation
            const contact = form.querySelector('input[name="contact"]');
            if (!contact.value.trim()) {
                this.showToast('warning', 'Please provide contact information');
                return;
            }

            // Show success
            form.style.display = 'none';
            const success = document.querySelector('.intake-success');
            if (success) {
                success.style.display = 'block';
            }

            this.showToast('success', 'Request submitted successfully!');

            // In a real app, you would send this data to the server
            const formData = new FormData(form);
            console.log('Form data:', Object.fromEntries(formData));
        });
    },

    // Smooth scrolling for anchor links
    initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                const href = anchor.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const navHeight = document.querySelector('.landing-navbar')?.offsetHeight || 0;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                    // Close mobile menu if open
                    const menu = document.getElementById('navbar-menu');
                    if (menu) menu.classList.remove('active');
                }
            });
        });
    },

    // Toast notification
    showToast(type, message) {
        const container = document.querySelector('.toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: 'fas fa-check-circle',
            warning: 'fas fa-exclamation-triangle',
            danger: 'fas fa-times-circle',
            info: 'fas fa-info-circle'
        };

        toast.innerHTML = `
            <i class="toast-icon ${icons[type] || icons.info}"></i>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;

        container.appendChild(toast);

        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });

        setTimeout(() => toast.remove(), 5000);
    }
};

// Make globally available
window.LandingPage = LandingPage;
