<?php
/**
 * Landing Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';

// Load landing page configuration
$configFile = __DIR__ . '/config/landing.json';
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];

// Helper function to get config value with default
function getConfig($key, $default = null) {
    global $config;
    $keys = explode('.', $key);
    $value = $config;
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    return $value;
}

// Check if element is enabled
function isEnabled($section) {
    return getConfig($section . '.enabled', true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars(getConfig('hero.subheadline', 'Connect to help when you need it most')); ?>">
    <meta name="keywords" content="emergency help, food assistance, housing help, social services, crisis support">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo APP_NAME; ?> - Help When You Need It Most</title>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/opendyslexic" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/landing.css">
    
    <!-- Schema.org structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?php echo APP_NAME; ?>",
        "description": "<?php echo htmlspecialchars(getConfig('hero.subheadline', '')); ?>",
        "url": "<?php echo APP_URL; ?>",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "<?php echo htmlspecialchars(getConfig('footer.contactPhone', '')); ?>",
            "contactType": "customer service"
        }
    }
    </script>
</head>
<body class="landing-page">
    <!-- Quick Exit Button (Safety Feature) -->
    <?php if (getConfig('accessibility.quickExit', true)): ?>
    <button id="quick-exit" class="quick-exit-btn" title="Quick Exit - Opens safe website" aria-label="Quick exit to safety">
        <i class="fas fa-times"></i> Quick Exit
    </button>
    <?php endif; ?>

    <!-- Accessibility Toolbar -->
    <div class="accessibility-toolbar" id="a11y-toolbar">
        <button class="a11y-toggle" id="a11y-toggle" aria-label="Accessibility options">
            <i class="fas fa-universal-access"></i>
        </button>
        <div class="a11y-panel" id="a11y-panel">
            <h4>Accessibility</h4>
            <?php if (getConfig('accessibility.largeTextToggle', true)): ?>
            <label class="a11y-option">
                <input type="checkbox" id="toggle-large-text" data-toggle-accessibility="large-text">
                <span>Large Text</span>
            </label>
            <?php endif; ?>
            <?php if (getConfig('accessibility.highContrastToggle', true)): ?>
            <label class="a11y-option">
                <input type="checkbox" id="toggle-contrast" data-toggle-accessibility="high-contrast">
                <span>High Contrast</span>
            </label>
            <?php endif; ?>
            <?php if (getConfig('accessibility.simpleModeToggle', true)): ?>
            <label class="a11y-option">
                <input type="checkbox" id="toggle-simple" data-toggle-accessibility="simple-mode">
                <span>Simple Mode</span>
            </label>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation -->
    <?php if (isEnabled('navbar')): ?>
    <nav class="landing-navbar" id="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <div class="navbar-brand-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <span><?php echo htmlspecialchars(getConfig('navbar.logo', APP_NAME)); ?></span>
            </a>
            
            <div class="navbar-menu" id="navbar-menu">
                <?php 
                $navItems = getConfig('navbar.items', []);
                foreach ($navItems as $item): 
                    if (!empty($item['enabled'])):
                ?>
                <a href="<?php echo htmlspecialchars($item['href']); ?>" class="navbar-link">
                    <?php echo htmlspecialchars($item['text']); ?>
                </a>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
            
            <div class="navbar-actions">
                <a href="#get-help" class="btn btn-cta">Get Help</a>
                <?php if (getConfig('navbar.loginLink', true)): ?>
                <a href="login.php" class="btn btn-outline-light">Login</a>
                <?php endif; ?>
            </div>
            
            <button class="navbar-toggle" id="navbar-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Hero Section -->
    <?php if (isEnabled('hero')): ?>
    <section class="hero-section" id="hero">
        <div class="hero-background"></div>
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title" data-editable="hero.headline">
                    <?php echo htmlspecialchars(getConfig('hero.headline', 'Help When You Need It Most')); ?>
                </h1>
                <p class="hero-subtitle" data-editable="hero.subheadline">
                    <?php echo htmlspecialchars(getConfig('hero.subheadline', 'We connect people in need with real help and trusted helpers nearby. Confidential. Free. Fast.')); ?>
                </p>
                <div class="hero-ctas">
                    <a href="#get-help" class="btn btn-primary btn-lg hero-cta-primary">
                        <i class="fas fa-hand-holding-heart"></i>
                        <?php echo htmlspecialchars(getConfig('hero.primaryCta', 'Get Help Now')); ?>
                    </a>
                    <a href="#helpers" class="btn btn-outline-light btn-lg hero-cta-secondary">
                        <i class="fas fa-hands-helping"></i>
                        <?php echo htmlspecialchars(getConfig('hero.secondaryCta', 'I Want to Help')); ?>
                    </a>
                </div>
                <div class="hero-trust">
                    <span><i class="fas fa-globe"></i> Available in multiple languages</span>
                    <span><i class="fas fa-lock"></i> Confidential</span>
                    <span><i class="fas fa-shield-alt"></i> Safe & Secure</span>
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,64 C480,150 960,-20 1440,64 L1440,120 L0,120 Z" fill="#ffffff"/>
            </svg>
        </div>
    </section>
    <?php endif; ?>

    <!-- Quick Help Widget -->
    <?php if (isEnabled('quickHelp')): ?>
    <section class="quick-help-section" id="get-help">
        <div class="container">
            <div class="quick-help-card">
                <h2 class="section-title" data-editable="quickHelp.title">
                    <?php echo htmlspecialchars(getConfig('quickHelp.title', 'How Can We Help You Today?')); ?>
                </h2>
                <div class="quick-help-buttons">
                    <?php 
                    $buttons = getConfig('quickHelp.buttons', []);
                    foreach ($buttons as $button): 
                    ?>
                    <button class="quick-help-btn" data-action="<?php echo htmlspecialchars($button['action']); ?>">
                        <div class="quick-help-icon">
                            <i class="<?php echo htmlspecialchars($button['icon']); ?>"></i>
                        </div>
                        <span><?php echo htmlspecialchars($button['text']); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- How It Works -->
    <?php if (isEnabled('howItWorks')): ?>
    <section class="how-it-works-section" id="about">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-editable="howItWorks.title">
                    <?php echo htmlspecialchars(getConfig('howItWorks.title', 'How It Works')); ?>
                </h2>
                <p class="section-subtitle" data-editable="howItWorks.subtitle">
                    <?php echo htmlspecialchars(getConfig('howItWorks.subtitle', 'Getting help is simple')); ?>
                </p>
            </div>
            <div class="steps-container">
                <?php 
                $steps = getConfig('howItWorks.steps', []);
                foreach ($steps as $index => $step): 
                ?>
                <div class="step-card">
                    <div class="step-number"><?php echo $index + 1; ?></div>
                    <div class="step-icon">
                        <i class="<?php echo htmlspecialchars($step['icon']); ?>"></i>
                    </div>
                    <h3 class="step-title"><?php echo htmlspecialchars($step['title']); ?></h3>
                    <p class="step-description"><?php echo htmlspecialchars($step['description']); ?></p>
                </div>
                <?php if ($index < count($steps) - 1): ?>
                <div class="step-connector">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if ($avgTime = getConfig('howItWorks.avgResponseTime')): ?>
            <div class="response-time">
                <i class="fas fa-clock"></i>
                <span>Average response time: <strong><?php echo htmlspecialchars($avgTime); ?></strong></span>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Services Section -->
    <?php if (isEnabled('services')): ?>
    <section class="services-section" id="services">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-editable="services.title">
                    <?php echo htmlspecialchars(getConfig('services.title', 'Services & Resources')); ?>
                </h2>
                <p class="section-subtitle" data-editable="services.subtitle">
                    <?php echo htmlspecialchars(getConfig('services.subtitle', 'We provide assistance across multiple areas')); ?>
                </p>
            </div>
            <div class="services-grid">
                <?php 
                $services = getConfig('services.items', []);
                foreach ($services as $service): 
                    if (!empty($service['enabled'])):
                ?>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                    </div>
                    <h3 class="service-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                    <a href="#get-help" class="btn btn-outline btn-sm"><?php echo htmlspecialchars($service['cta']); ?></a>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <?php if (isEnabled('testimonials')): ?>
    <section class="testimonials-section" id="stories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-editable="testimonials.title">
                    <?php echo htmlspecialchars(getConfig('testimonials.title', 'Success Stories')); ?>
                </h2>
                <p class="section-subtitle" data-editable="testimonials.subtitle">
                    <?php echo htmlspecialchars(getConfig('testimonials.subtitle', 'Real stories from people we\'ve helped')); ?>
                </p>
            </div>
            <div class="testimonials-slider" id="testimonials-slider">
                <?php 
                $testimonials = getConfig('testimonials.items', []);
                foreach ($testimonials as $index => $testimonial): 
                ?>
                <div class="testimonial-card <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['quote']); ?></p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <?php if (!empty($testimonial['image'])): ?>
                            <img src="<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['author']); ?>">
                            <?php else: ?>
                            <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="author-info">
                            <strong><?php echo htmlspecialchars($testimonial['author']); ?></strong>
                            <span><?php echo htmlspecialchars($testimonial['role']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="testimonials-nav">
                <button class="testimonial-prev" aria-label="Previous testimonial"><i class="fas fa-chevron-left"></i></button>
                <div class="testimonials-dots">
                    <?php foreach ($testimonials as $index => $t): ?>
                    <button class="dot <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>" aria-label="Go to testimonial <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <button class="testimonial-next" aria-label="Next testimonial"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Helpers Section -->
    <?php if (isEnabled('helpers')): ?>
    <section class="helpers-section" id="helpers">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-editable="helpers.title">
                    <?php echo htmlspecialchars(getConfig('helpers.title', 'Make a Difference')); ?>
                </h2>
            </div>
            <div class="helpers-grid">
                <div class="helper-card volunteer-card">
                    <div class="helper-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3><?php echo htmlspecialchars(getConfig('helpers.volunteerCta.title', 'Become a Volunteer')); ?></h3>
                    <p><?php echo htmlspecialchars(getConfig('helpers.volunteerCta.description', 'Join our network of caring individuals making a real difference in people\'s lives.')); ?></p>
                    <a href="register.php?role=helper" class="btn btn-primary">
                        <?php echo htmlspecialchars(getConfig('helpers.volunteerCta.buttonText', 'Sign Up to Help')); ?>
                    </a>
                </div>
                <div class="helper-card partner-card">
                    <div class="helper-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3><?php echo htmlspecialchars(getConfig('helpers.partnerCta.title', 'Partner With Us')); ?></h3>
                    <p><?php echo htmlspecialchars(getConfig('helpers.partnerCta.description', 'Organizations, businesses, and government agencies—let\'s coordinate resources together.')); ?></p>
                    <a href="#contact" class="btn btn-outline">
                        <?php echo htmlspecialchars(getConfig('helpers.partnerCta.buttonText', 'Partner Inquiry')); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Impact Section -->
    <?php if (isEnabled('impact')): ?>
    <section class="impact-section" id="impact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-editable="impact.title">
                    <?php echo htmlspecialchars(getConfig('impact.title', 'Our Impact')); ?>
                </h2>
                <p class="section-subtitle" data-editable="impact.subtitle">
                    <?php echo htmlspecialchars(getConfig('impact.subtitle', 'Making a measurable difference in our community')); ?>
                </p>
            </div>
            <div class="impact-stats">
                <?php 
                $stats = getConfig('impact.stats', []);
                foreach ($stats as $stat): 
                ?>
                <div class="impact-stat">
                    <div class="stat-value"><?php echo htmlspecialchars($stat['value']); ?></div>
                    <div class="stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($link = getConfig('impact.transparencyLink')): ?>
            <div class="transparency-link">
                <a href="<?php echo htmlspecialchars($link); ?>" class="btn btn-link">
                    <i class="fas fa-chart-line"></i> View our transparency report
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Donation Section -->
    <?php if (isEnabled('donation')): ?>
    <section class="donation-section" id="donate">
        <div class="container">
            <div class="donation-card">
                <div class="donation-content">
                    <h2 class="section-title" data-editable="donation.title">
                        <?php echo htmlspecialchars(getConfig('donation.title', 'Support Our Mission')); ?>
                    </h2>
                    <p class="section-subtitle" data-editable="donation.subtitle">
                        <?php echo htmlspecialchars(getConfig('donation.subtitle', 'Your donation helps us reach more people in need')); ?>
                    </p>
                    <div class="donation-amounts">
                        <?php 
                        $amounts = getConfig('donation.amounts', [25, 50, 100, 250]);
                        foreach ($amounts as $amount): 
                        ?>
                        <button class="donation-amount" data-amount="<?php echo $amount; ?>">$<?php echo $amount; ?></button>
                        <?php endforeach; ?>
                        <?php if (getConfig('donation.customAmount', true)): ?>
                        <button class="donation-amount custom-amount">Other</button>
                        <?php endif; ?>
                    </div>
                    <?php if (getConfig('donation.recurringOption', true)): ?>
                    <label class="recurring-option">
                        <input type="checkbox" id="recurring-donation">
                        <span>Make this a monthly donation</span>
                    </label>
                    <?php endif; ?>
                    <button class="btn btn-primary btn-lg donate-btn">
                        <i class="fas fa-heart"></i> Donate Now
                    </button>
                    <div class="donation-trust">
                        <span><i class="fas fa-lock"></i> Secure payment</span>
                        <span><i class="fas fa-receipt"></i> Tax deductible</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Resources Section -->
    <?php if (isEnabled('resources')): ?>
    <section class="resources-section" id="resources">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" data-editable="resources.title">
                    <?php echo htmlspecialchars(getConfig('resources.title', 'Quick Resources')); ?>
                </h2>
                <p class="section-subtitle" data-editable="resources.subtitle">
                    <?php echo htmlspecialchars(getConfig('resources.subtitle', 'Immediate access to essential information')); ?>
                </p>
            </div>
            <div class="resources-grid">
                <?php 
                $resources = getConfig('resources.items', []);
                foreach ($resources as $resource): 
                ?>
                <div class="resource-item">
                    <div class="resource-icon">
                        <i class="<?php echo htmlspecialchars($resource['icon']); ?>"></i>
                    </div>
                    <div class="resource-info">
                        <h4><?php echo htmlspecialchars($resource['title']); ?></h4>
                        <p><?php echo htmlspecialchars($resource['value']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <?php if (isEnabled('footer')): ?>
    <footer class="landing-footer" id="contact">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-hands-helping"></i>
                        <span><?php echo APP_NAME; ?></span>
                    </div>
                    <p>Connecting people in need with real help and trusted helpers. Confidential. Free. Fast.</p>
                    <div class="social-links">
                        <?php 
                        $social = getConfig('footer.socialLinks', []);
                        foreach ($social as $platform => $url): 
                            if (!empty($url)):
                        ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" aria-label="<?php echo ucfirst($platform); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-<?php echo htmlspecialchars($platform); ?>"></i>
                        </a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#services">Our Services</a></li>
                        <li><a href="#get-help">Get Help</a></li>
                        <li><a href="#helpers">Volunteer</a></li>
                        <li><a href="#donate">Donate</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Accessibility</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars(getConfig('footer.contactPhone', '1-800-555-HELP')); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars(getConfig('footer.contactEmail', 'help@onesinc.org')); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(getConfig('footer.address', '')); ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?php echo htmlspecialchars(getConfig('footer.copyright', '© ' . date('Y') . ' OneSinc. All rights reserved.')); ?></p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Intake Modal -->
    <div class="modal-overlay" id="intake-modal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Request Help</h3>
                <button class="modal-close" aria-label="Close modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="intake-form" class="intake-form">
                    <div class="form-step active" data-step="1">
                        <h4>What type of help do you need?</h4>
                        <div class="help-type-grid">
                            <label class="help-type-option">
                                <input type="radio" name="help_type" value="food">
                                <div class="help-type-card">
                                    <i class="fas fa-utensils"></i>
                                    <span>Food</span>
                                </div>
                            </label>
                            <label class="help-type-option">
                                <input type="radio" name="help_type" value="housing">
                                <div class="help-type-card">
                                    <i class="fas fa-home"></i>
                                    <span>Housing</span>
                                </div>
                            </label>
                            <label class="help-type-option">
                                <input type="radio" name="help_type" value="mental_health">
                                <div class="help-type-card">
                                    <i class="fas fa-brain"></i>
                                    <span>Mental Health</span>
                                </div>
                            </label>
                            <label class="help-type-option">
                                <input type="radio" name="help_type" value="legal">
                                <div class="help-type-card">
                                    <i class="fas fa-gavel"></i>
                                    <span>Legal Aid</span>
                                </div>
                            </label>
                            <label class="help-type-option">
                                <input type="radio" name="help_type" value="employment">
                                <div class="help-type-card">
                                    <i class="fas fa-briefcase"></i>
                                    <span>Employment</span>
                                </div>
                            </label>
                            <label class="help-type-option">
                                <input type="radio" name="help_type" value="other">
                                <div class="help-type-card">
                                    <i class="fas fa-ellipsis-h"></i>
                                    <span>Other</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="form-step" data-step="2">
                        <h4>Contact Information</h4>
                        <p class="form-help">We'll only use this to connect you with help. Your information is confidential.</p>
                        <div class="form-group">
                            <label class="form-label">Your Name (optional)</label>
                            <input type="text" name="name" class="form-control" placeholder="First name is fine">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone or Email</label>
                            <input type="text" name="contact" class="form-control" placeholder="How can we reach you?" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tell us more (optional)</label>
                            <textarea name="details" class="form-control" rows="3" placeholder="Any details that would help us assist you"></textarea>
                        </div>
                    </div>
                    <div class="form-navigation">
                        <button type="button" class="btn btn-secondary prev-step" style="display:none;">Back</button>
                        <button type="button" class="btn btn-primary next-step">Continue</button>
                        <button type="submit" class="btn btn-primary submit-intake" style="display:none;">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </form>
                <div class="intake-success" style="display:none;">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Request Submitted!</h3>
                    <p>We'll reach out to you shortly. If this is urgent, please call:</p>
                    <p class="hotline"><strong><?php echo htmlspecialchars(getConfig('footer.contactPhone', '1-800-555-HELP')); ?></strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Volunteer Modal -->
    <div class="modal-overlay" id="volunteer-modal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Become a Volunteer</h3>
                <button class="modal-close" aria-label="Close modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p>Join our community of helpers making a difference every day.</p>
                <a href="register.php?role=helper" class="btn btn-primary w-100">
                    <i class="fas fa-user-plus"></i> Sign Up as Helper
                </a>
                <p class="mt-2 text-center">Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>

    <!-- Donate Modal -->
    <div class="modal-overlay" id="donate-modal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Make a Donation</h3>
                <button class="modal-close" aria-label="Close modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p>Thank you for your generosity! Your support helps us serve more people in need.</p>
                <form id="donate-form">
                    <div class="form-group">
                        <label class="form-label">Donation Amount</label>
                        <div class="donation-buttons">
                            <button type="button" class="donation-amount active" data-amount="25">$25</button>
                            <button type="button" class="donation-amount" data-amount="50">$50</button>
                            <button type="button" class="donation-amount" data-amount="100">$100</button>
                            <button type="button" class="donation-amount" data-amount="custom">Other</button>
                        </div>
                        <input type="number" name="custom_amount" class="form-control mt-1" placeholder="Enter amount" style="display:none;">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-heart"></i> Donate
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <script src="js/app.js"></script>
    <script src="js/landing.js"></script>
</body>
</html>
