/**
 * OneSinc - Social Services Platform
 * Main JavaScript File
 */

// Global namespace
const OneSinc = {
    config: {
        apiUrl: '/api',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content
    },

    // Initialize application
    init() {
        this.initDropdowns();
        this.initModals();
        this.initToasts();
        this.initForms();
        this.initSidebar();
        this.initAccessibility();
        console.log('OneSinc initialized');
    },

    // Dropdown menus
    initDropdowns() {
        document.querySelectorAll('[data-dropdown]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const target = document.getElementById(trigger.dataset.dropdown);
                if (target) {
                    target.classList.toggle('active');
                }
            });
        });

        // Close dropdowns on outside click
        document.addEventListener('click', () => {
            document.querySelectorAll('.user-dropdown.active').forEach(el => {
                el.classList.remove('active');
            });
        });
    },

    // Modal handling
    initModals() {
        // Open modal
        document.querySelectorAll('[data-modal]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.dataset.modal;
                this.openModal(modalId);
            });
        });

        // Close modal
        document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target === el) {
                    this.closeModal(el.closest('.modal-overlay'));
                }
            });
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal-overlay.active');
                if (activeModal) {
                    this.closeModal(activeModal);
                }
            }
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

    // Toast notifications
    initToasts() {
        // Create toast container if not exists
        if (!document.querySelector('.toast-container')) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
    },

    showToast(type, title, message, duration = 5000) {
        const container = document.querySelector('.toast-container');
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
                <div class="toast-title">${title}</div>
                ${message ? `<div class="toast-message">${message}</div>` : ''}
            </div>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;

        container.appendChild(toast);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.removeToast(toast);
        });

        // Auto remove
        if (duration > 0) {
            setTimeout(() => this.removeToast(toast), duration);
        }

        return toast;
    },

    removeToast(toast) {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    },

    // Form handling
    initForms() {
        // AJAX form submission
        document.querySelectorAll('form[data-ajax]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm(form);
            });
        });

        // Form validation
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });

        // Real-time validation
        document.querySelectorAll('.form-control[required]').forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
        });
    },

    async submitForm(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        
        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:2px;"></span> Processing...';
            }

            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('success', 'Success', data.message);
                if (data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 1000);
                }
                if (form.dataset.reset !== 'false') {
                    form.reset();
                }
            } else {
                this.showToast('danger', 'Error', data.message || 'An error occurred');
                if (data.errors) {
                    this.showFormErrors(form, data.errors);
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showToast('danger', 'Error', 'Network error. Please try again.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    },

    validateForm(form) {
        let isValid = true;
        form.querySelectorAll('[required]').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    },

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMsg = '';

        // Required validation
        if (field.required && !value) {
            isValid = false;
            errorMsg = 'This field is required';
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMsg = 'Please enter a valid email address';
            }
        }

        // Password validation
        if (field.type === 'password' && value && field.minLength) {
            if (value.length < field.minLength) {
                isValid = false;
                errorMsg = `Password must be at least ${field.minLength} characters`;
            }
        }

        // Update UI
        const errorEl = field.parentElement.querySelector('.form-error');
        if (isValid) {
            field.classList.remove('error');
            if (errorEl) errorEl.remove();
        } else {
            field.classList.add('error');
            if (!errorEl) {
                const error = document.createElement('div');
                error.className = 'form-error';
                error.textContent = errorMsg;
                field.parentElement.appendChild(error);
            } else {
                errorEl.textContent = errorMsg;
            }
        }

        return isValid;
    },

    showFormErrors(form, errors) {
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                const errorEl = document.createElement('div');
                errorEl.className = 'form-error';
                errorEl.textContent = errors[field];
                input.parentElement.appendChild(errorEl);
            }
        });
    },

    // Sidebar toggle for mobile
    initSidebar() {
        const toggle = document.querySelector('[data-sidebar-toggle]');
        const sidebar = document.querySelector('.sidebar');
        
        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('active') && 
                    !sidebar.contains(e.target) && 
                    !toggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });
        }
    },

    // Accessibility features
    initAccessibility() {
        // Load saved preferences
        const prefs = this.loadAccessibilityPrefs();
        if (prefs.highContrast) document.body.classList.add('high-contrast');
        if (prefs.largeText) document.body.classList.add('large-text');
        if (prefs.dyslexiaFont) document.body.classList.add('dyslexia-font');

        // Toggle handlers
        document.querySelectorAll('[data-toggle-accessibility]').forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                const setting = e.target.dataset.toggleAccessibility;
                this.toggleAccessibility(setting, e.target.checked);
            });
        });
    },

    toggleAccessibility(setting, enabled) {
        const classMap = {
            'high-contrast': 'high-contrast',
            'large-text': 'large-text',
            'dyslexia-font': 'dyslexia-font'
        };

        if (classMap[setting]) {
            document.body.classList.toggle(classMap[setting], enabled);
            this.saveAccessibilityPrefs({ [setting]: enabled });
        }
    },

    loadAccessibilityPrefs() {
        try {
            return JSON.parse(localStorage.getItem('accessibility') || '{}');
        } catch {
            return {};
        }
    },

    saveAccessibilityPrefs(newPrefs) {
        const prefs = { ...this.loadAccessibilityPrefs(), ...newPrefs };
        localStorage.setItem('accessibility', JSON.stringify(prefs));
    },

    // AJAX helper
    async ajax(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (this.config.csrfToken) {
            defaults.headers['X-CSRF-Token'] = this.config.csrfToken;
        }

        const config = { ...defaults, ...options };
        
        try {
            const response = await fetch(url, config);
            return await response.json();
        } catch (error) {
            console.error('AJAX error:', error);
            throw error;
        }
    },

    // Confirm dialog
    async confirm(message, title = 'Confirm') {
        return new Promise(resolve => {
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay active';
            overlay.innerHTML = `
                <div class="modal">
                    <div class="modal-header">
                        <h3 class="modal-title">${title}</h3>
                        <button class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-action="cancel">Cancel</button>
                        <button class="btn btn-primary" data-action="confirm">Confirm</button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';

            const handleAction = (confirmed) => {
                overlay.remove();
                document.body.style.overflow = '';
                resolve(confirmed);
            };

            overlay.querySelector('[data-action="confirm"]').onclick = () => handleAction(true);
            overlay.querySelector('[data-action="cancel"]').onclick = () => handleAction(false);
            overlay.querySelector('.modal-close').onclick = () => handleAction(false);
        });
    },

    // Loading overlay
    showLoading() {
        let overlay = document.querySelector('.loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(overlay);
        }
    },

    hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) overlay.remove();
    },

    // Format date
    formatDate(dateString, format = 'short') {
        const date = new Date(dateString);
        const options = format === 'short' 
            ? { month: 'short', day: 'numeric', year: 'numeric' }
            : { month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    },

    // Time ago
    timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        const intervals = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60
        };

        for (const [unit, value] of Object.entries(intervals)) {
            const count = Math.floor(seconds / value);
            if (count >= 1) {
                return `${count} ${unit}${count > 1 ? 's' : ''} ago`;
            }
        }
        return 'Just now';
    },

    // Debounce
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => OneSinc.init());

// Make globally available
window.OneSinc = OneSinc;
