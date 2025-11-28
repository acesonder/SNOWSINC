# OneSinc - Social Services Platform

![OneSinc](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

**OneSinc** is a comprehensive web application designed to streamline social services delivery for vulnerable populations. Built with PHP, MySQL, HTML, CSS, JavaScript, and AJAX, it provides an intuitive platform for clients seeking assistance, helpers (social workers, volunteers), and administrators.

## 🚀 Features

### For Clients
- **Request Help** - Submit service requests for food, housing, legal aid, health services, and more
- **Track Status** - Monitor the progress of your assistance requests in real-time
- **Secure Messaging** - Communicate directly with your assigned helper
- **Resource Center** - Access categorized resources and helpful information
- **Task Management** - Keep track of appointments and to-dos
- **SOS Emergency Button** - Quick access to emergency services

### For Helpers (Social Workers/Volunteers)
- **Case Management** - View and manage assigned cases efficiently
- **Request Queue** - Review and accept pending assistance requests
- **Client Communication** - Secure messaging with clients
- **Progress Tracking** - Update request status and add notes
- **Resource Sharing** - Share helpful resources with clients

### For Administrators
- **User Management** - Manage all system users and roles
- **Reports & Analytics** - View system statistics and trends
- **System Settings** - Configure application settings
- **Activity Logs** - Monitor system activity

### Technical Features
- **Responsive Design** - Works seamlessly on desktop, tablet, and mobile
- **3D Interactive Navbar** - Modern, visually appealing interface
- **Accessibility Options** - High contrast, large text, dyslexia-friendly fonts
- **Multi-language Support** - Built-in language preference settings
- **AJAX-Powered** - Smooth, dynamic user experience
- **Secure Authentication** - Password hashing, CSRF protection, session management

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache/Nginx web server
- PDO PHP Extension
- mod_rewrite enabled (for Apache)

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/acesonder/SNOWSINC.git
cd SNOWSINC
```

### 2. Set Up the Database
```bash
# Log into MySQL
mysql -u root -p

# Create database and run schema
source database/schema.sql
```

### 3. Configure the Application

Create environment variables or update `config/database.php`:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'onesinc_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Set File Permissions
```bash
chmod 755 uploads/
chmod 644 config/*.php
```

### 5. Configure Web Server

**Apache (`.htaccess` included):**
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/SNOWSINC
    ServerName onesinc.local
    
    <Directory /var/www/html/SNOWSINC>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name onesinc.local;
    root /var/www/html/SNOWSINC;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. Access the Application

Open your browser and navigate to your configured domain or `http://localhost/SNOWSINC`

**Default Admin Credentials:**
- Email: `admin@onesinc.org`
- Password: `password`

⚠️ **Important:** Change the default password immediately after first login!

## 📁 Project Structure

```
SNOWSINC/
├── admin/                  # Admin panel pages
│   ├── users.php          # User management
│   ├── reports.php        # Reports & analytics
│   └── settings.php       # System settings
├── api/                    # AJAX API endpoints
│   ├── change-password.php
│   ├── notifications.php
│   └── tasks.php
├── config/                 # Configuration files
│   ├── app.php            # Application settings
│   └── database.php       # Database connection
├── css/                    # Stylesheets
│   └── style.css          # Main stylesheet
├── database/               # Database files
│   └── schema.sql         # Database schema
├── includes/               # PHP includes
│   ├── auth.php           # Authentication functions
│   ├── functions.php      # Helper functions
│   └── init.php           # Initialization
├── js/                     # JavaScript files
│   └── app.js             # Main JavaScript
├── templates/              # Page templates
│   ├── header.php         # Header template
│   └── footer.php         # Footer template
├── uploads/                # File uploads
├── dashboard.php           # Main dashboard
├── login.php              # Login page
├── register.php           # Registration page
├── requests.php           # Service requests
├── request-view.php       # Request details
├── messages.php           # Messaging
├── resources.php          # Resource center
├── resource-view.php      # Resource details
├── profile.php            # User profile
├── settings.php           # User settings
├── tasks.php              # Task management
├── notifications.php      # Notifications
├── help.php               # Help & support
├── feedback.php           # Feedback form
├── forgot-password.php    # Password reset
├── logout.php             # Logout handler
└── index.php              # Entry point
```

## 🔐 User Roles

| Role | Description | Permissions |
|------|-------------|-------------|
| **Client** | Individuals seeking assistance | Submit requests, view own data, message helpers |
| **Helper** | Social workers, volunteers | Manage cases, message clients, view resources |
| **Admin** | System administrators | Full access, user management, reports |

## 🎨 Customization

### Themes
Modify CSS variables in `css/style.css`:
```css
:root {
    --primary-color: #4f46e5;
    --primary-dark: #4338ca;
    --success-color: #22c55e;
    /* ... more variables */
}
```

### Service Categories
Update categories in `config/app.php`:
```php
define('SERVICE_CATEGORIES', [
    'food' => 'Food Assistance',
    'housing' => 'Housing & Shelter',
    // Add more categories
]);
```

## 🔒 Security Features

- **Password Hashing** - bcrypt with cost factor 10
- **CSRF Protection** - Token-based request validation
- **SQL Injection Prevention** - Prepared statements (PDO)
- **XSS Protection** - Output escaping with htmlspecialchars
- **Session Security** - Regeneration on login, secure cookies
- **Input Sanitization** - Server-side validation
- **Security Headers** - X-Content-Type-Options, X-Frame-Options, X-XSS-Protection

## 📱 Accessibility

OneSinc is designed with accessibility in mind:
- **High Contrast Mode** - For users with visual impairments
- **Large Text Mode** - Increased font sizes
- **Dyslexia-Friendly Font** - OpenDyslexic font option
- **Screen Reader Support** - ARIA labels and semantic HTML
- **Keyboard Navigation** - Full keyboard accessibility

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Font Awesome for icons
- Inter font family
- All contributors and supporters

---

**Built with ❤️ for social good**

---

## Original Summary:
The "onesinc" web app draws its industry value from a focus on streamlined, professional UI/UX, powerful deployment and admin tools, and an architecture designed for both usability and extensibility. This multifaceted approach is crucial for teams seeking efficiency, reliability, and a strong user experience in web-based deployment management. UI/UX Layout, Style, and Elements
Responsive & Professional Design: While specific UI files weren’t directly visible, the repository is mainly PHP-based with web features enabled (like GitHub Pages). Based on best practices for modern web apps and your requirements, a successful layout for "onesinc" should include a responsive framework that seamlessly scales across devices—from desktop dashboards to mobile modals.
3D and Modern Aesthetics: To compete in today's industry, a stylish navbar with 3D elements, animated transitions, and iconography would be part of the core design. Expect modals for workflows and a visually rich dashboard—with actionable widgets, graphical analytics, and dynamic loading states.
Intuitive Workflow: The user experience should center around effortless navigation: sidebars or tabbed views for multi-tool access, search functionality, clear CTAs, and context-aware notifications. Modals and overlays likely streamline rapid actions like deployment or settings changes.
Workflow Process
Streamlined Engagement: Users likely follow a prompt login (potentially root/blank-password for local dev), landing on a dashboard summarizing core metrics, actions, and deployment statuses. Modular workflows would enable initiating deployments, monitoring services, and customizing configurations in a few intuitive steps.
Guided Interactions: Modal confirmations, inline messaging, and a feedback-rich UI ensure users immediately understand the status and results of their actions—critical for success in high-stakes deployment environments.
Features, Tools, and Services
Comprehensive Deployment Management: As indicated by the emphasis on "deployments," core features probably include:
Visual deployment tracking (e.g., timeline or status cards)
Rollback, log viewing, and alert tools
Project/service management within a cohesive interface
Admin Tools & Integrations: The app likely leverages PHP backend services for management—potentially integrating SSH, database viewers (like phpMyAdmin), analytics widgets, and user/session management directly from the dashboard.
Customization & Extensibility: Being open-source and modular, custom themes, roles/permissions, and integration hooks can be added easily to match client or industry-specific needs.
Unique Customizations & Industry Success Factors
Tailored User Experience: The app likely supports customizable dashboards and navigation. For businesses, this flexibility means teams can tailor the UI to their unique deployment pipelines.
Developer-Friendly Architecture: By using PHP and standard web technologies, it remains highly accessible for teams familiar with LAMP stacks. The blank root-password login (for local dev) supports rapid prototyping and iteration.
Open Collaboration & Extensibility: With GitHub’s "pages," "projects," and "wiki" enabled, collaboration and documentation can integrate seamlessly with the platform’s interface, supporting robust onboarding and community-driven improvements.
How It Drives Industry Success
Efficiency & Clarity: By combining a visually engaging UI with a powerful, extensible backend, "onesinc" dramatically reduces the time it takes to manage deployments, troubleshoot issues, or onboard new team members.
Security & Control: Role-based access, modal security confirmations, and detailed analytics aid in compliance and prevent costly mistakes.
Customization for Differentiation: In a crowded SaaS/DevOps space, the ability to visually and functionally customize workflows—tailored down to the 3D navbars or deployment tools—gives organizations a competitive edge.

Features, and Elements
1. Login & Authentication
Simple, Inclusive Login: Minimalist but secure login; root/blank password in dev, strong auth in production. Emphasizes language accessibility, clarity (“Welcome back! Helping you help others.”), and error handling (password help links, passwordless email/SMS for vulnerable users).
Password Recovery: Immediate reset options via email/SMS/phone support, recognizing challenges faced by less tech-literate or memory-impaired users.
2. Registration & Onboarding
Role-Aware Signup: Guides new users—clients (vulnerable individuals), helpers (social workers, volunteers), and admins—through tailored onboarding, using tooltips, pictorials, and videos.
Identity & Needs Assessment: A second step lets clients indicate needs (food, housing, legal aid), disabilities, languages, and accessibility preferences (screen readers, large text, etc.).
3. Dashboard (Personalized Home)
3D Interactive Navbar: Visually striking, with large, touch-friendly icons for dashboard, resources, tasks, support, and reporting tools.
Quick Actions Cards: “Request help,” “Check status,” “Contact your advocate,” and “Important alerts”—all with visual cues (color coding, icons, gentle animations).
Personal Progress Stats: Accessible graphs and progress bars showing open requests, goals achieved, next steps, and current helpers assigned.
4. Service Requests & Support
New Request Wizard: Step-by-step modal for making a new request for aid (food, shelter, job search, mental health, etc.), with plain language tips and images.
Request Queue & Tracking: Table and card view of all requests, with sorting, status chips (“pending,” “in review,” “fulfilled”), filter by urgency, printable summaries for offline use.
Notifications: Real-time updates (toast popups, dashboard alerts) when status changes or new info/resources are available.
5. Resource Center
Categorized Help Library: Curated resources by housing, food, legal, health, employment. Quick visual filtering.
Resource Highlight: Featured programs and urgent services at the top; embedded social stories for encouragement.
Direct Links & Downloads: Print-friendly version, downloadable PDFs for offline clients, video explainers, and interactive guides.
6. Messenger & Collaboration
Secure Messaging: Private chat between clients, helpers, and support staff. Emoji/sticker sets for non-verbal communication.
Multilingual Support: Real-time translation for messages and documents; preset messages for commonly needed phrases.
Notifications & Read Receipts: Accessibility first—screen reader announcements, vibrational cues, and visual indicators.
7. Task & Progress Management
Task Lists: “My next steps” for clients (“Meet your counselor Tuesday, bring ID”), progress bars for goals, and reminders with snooze/dismiss options.
Helper Assignments: Easily switch or add helpers; see photos, roles, languages spoken, and contact options.
Calendar Sync: Link with Google/Outlook; auto-schedule follow-up appointments.
8. Profile & Personalization
Profile Control: Update contact preferences, visual accessibility settings (contrast/theme, text size), and select preferred communication methods (call/SMS/email/in-app).
Privacy & Consent: All settings explain, in plain language, what data is shared and with whom; instant opt-in/opt-out for notifications and data sharing.
Feedback Loops: Clients & helpers can rate experiences and provide suggestions directly via modal forms.
9. Reporting & Analytics (For Admins & Helpers)
Comprehensive Dashboards: Aggregated anonymous stats—number of open cases, resolved requests, response times, most requested services.
Heatmaps & Trends: Visual indicators for urgent community needs (e.g., spike in homelessness in a neighborhood).
Export Tools: CSV/PDF report generators for grants, board meetings, or compliance.
10. Settings & Security
Account Security: 2FA, password management, device control, recent activity logs.
Accessibility: Toggle for dyslexia font, narration, high-contrast, or simplified UI modes.
User Management: Admins assign roles, reset passwords, monitor system health in real time.
Future Features & Major Updates (Supercharged for Social Impact!)
A. AI-Powered Guidance & Personal Coaching
Predictive Aid Wizard: AI reviews needs and proactively suggests the next step (“Nearby food pantry has open slots this morning”) based on urgent trends and client history.
Virtual Coach Avatar: A friendly, customizable avatar guides users through difficult tasks in relatable, reassuring language, even narrating pages aloud or answering simple questions.
Emotional State Check-Ins: Gentle mood check modals at login, with escalation protocols for mental health intervention if concerning patterns detected.
B. Community Collaboration Spaces
Peer Support Forums: Safe, moderated spaces for sharing stories, seeking encouragement, and community-driven Q&A, with reputation badges for members who help most.
Group Scheduling Tools: Organize support groups, workshops, or events—auto-invitations, RSVP, and dynamic group chat channels.
C. Mobile Companion App
Unstable Internet Resilience: Core features (view needs, message helpers, access resources) cached offline and auto-synced when back online.
SOS & Emergency Modal: Immersive red-coded button for instant emergency contact (shelter, suicide hotline, legal crisis), with disguised options for users at risk (like domestic violence).
D. Multimodal Accessibility
Voice Command Capabilities: Full navigation and data entry by speech, with smart error correction and multiple voice profiles.
Screen Reader Perfection: Full ARIA compliance, keyboard shortcuts for every function, and real user testing with people who have disabilities.
Dyslexia and Visual Processing Support: Alternate content flows, text overlays, and explainer videos.
E. Enhanced Caseworker Toolkit
Bulk Actions & Macros: Batch-update case statuses, send group messages, or close resolved tickets in one click, saving hours every week.
Geospatial Outreach Toolkit: Map view of “clients in need,” overlay with public transport, shelters/food pantries, auto-route planning for outreach teams.
Mobile Intake Mode: Fill out all intake paperwork, snapshot documents, and capture signatures from the field—even without internet.
F. Real-Time Community Data Sharing
Anonymous Community Feed: Aggregate urgent needs (“5 requests for diapers nearby”) so local nonprofits, donors, or volunteers can respond directly.
Living Resource Map: Interactive map of live service status (capacity, hours, contacts) maintained by community partners and admins.
G. Personalized Advocacy & Empowerment
User-Led Roadmapping: Clients co-design their service plans, see progress on goals they set, and request new features—building trust and agency.
Success Stories Gallery: Dynamic, permission-based wall of past client successes, testimonials, and gratitude—motivating both users and the helpers who serve them.
H. Transparency and Trust
Shareable Personal Data Dashboard: Users see every access and use of their data, with instant revoke/grant options, and downloadable personal records for housing/job applications.
Radical Consent Engine: “Why do we need this information?” popups on every data entry, fostering radical transparency and control.
I. Cultural and Linguistic Inclusion
Multi-Regional Customization: Auto-translate not just content, but idioms, forms, and navigation for each community’s norms—plus an option for community leaders to help optimize translations and add local resources.
Inspirational Quotes & Art: Rotate thematically uplifting images and affirmations tailored to age, background, and expressed needs.
J. Continuous Improvement & Community-Driven Development
Direct Feature Voting: Users (clients and helpers) submit/endorse feature requests, vote and comment, influencing the update roadmap.
Open Data Portal: Share anonymized usage data, needs trends, and program effectiveness with the public to support research and funding.
Why This Matters for the Vulnerable and Their Helpers
For the Most Vulnerable:
Removes technical, language, and emotional barriers—provides rapid, dignified access to resources, human connection, urgent support, and hope.
For Those Who Help Them:
Automates grunt work, surfaces urgent cases, fosters new collaboration, and enables measurement and improvement—making service delivery less stressful and more impactful.
For the Whole Ecosystem:
By making every aspect transparent, customizable, and inclusive, “onesinc” isn’t just assisting—it’s empowering a new standard for dignity and care in social services tech.
