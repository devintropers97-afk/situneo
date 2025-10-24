<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Application Constants
 * ========================================
 */

// App Information
define('APP_NAME', 'SITUNEO DIGITAL');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://tester.situneo.my.id');

// Company Information
define('COMPANY_NAME', 'SITUNEO DIGITAL');
define('COMPANY_EMAIL', 'vins@situneo.my.id');
define('SUPPORT_EMAIL', 'support@situneo.my.id');
define('COMPANY_PHONE', '6283173868915');
define('COMPANY_WHATSAPP', '6283173868915');
define('COMPANY_NIB', '20250926145704515453');

// Email Configuration
define('FROM_EMAIL', 'noreply@tester.situneo.my.id');
define('FROM_NAME', 'SITUNEO DIGITAL');

// Session Configuration
define('SESSION_LIFETIME', 86400); // 24 hours
define('REMEMBER_LIFETIME', 2592000); // 30 days

// Password Configuration
define('MIN_PASSWORD_LENGTH', 8);

// User Roles
define('ROLE_USER', 1);
define('ROLE_ADMIN', 2);
define('ROLE_SUPER_ADMIN', 3);
define('ROLE_FREELANCER', 4);

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH_AVATARS', 'uploads/avatars/');
define('UPLOAD_PATH_PAYMENTS', 'uploads/payments/');
define('UPLOAD_PATH_PORTFOLIO', 'uploads/portfolio/');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Commission & Withdrawal
define('COMMISSION_RATE', 30); // 30%
define('MIN_WITHDRAWAL_AMOUNT', 50000); // Rp 50,000

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>
