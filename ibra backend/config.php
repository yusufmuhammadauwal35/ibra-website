<?php
/**
 * Ibra Foundation School Management System
 * Configuration File
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_PATH', __DIR__ . '/data/school.db');
define('DATA_DIR', __DIR__ . '/data');

// Create data directory if not exists
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// School configuration
define('SCHOOL_NAME', 'Ibra Foundation School Mirnga');
define('SCHOOL_SHORT_NAME', 'IFS');
define('SCHOOL_CODE', 'IFS');
define('CURRENT_SESSION', '2023/2024');
define('CURRENT_TERM', 'First Term');

// Paystack Configuration
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxxxxxx');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxxxxxx');
define('PAYSTACK_BASE_URL', 'https://api.paystack.co');
define('PAYSTACK_CALLBACK_URL', 'https://yoursite.com/payment/callback.php');
define('PAYMENT_CURRENCY', 'NGN');

// Session configuration
session_start();

// CORS headers
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');

// Role Definitions with Hierarchy
define('ROLES', [
    'Director' => [
        'level' => 1,
        'can_be_created_by' => [], // Only manual DB insert
        'permissions' => ['all']
    ],
    'Principal' => [
        'level' => 2,
        'can_be_created_by' => ['Director'],
        'permissions' => ['all_except_director']
    ],
    'Vice_Principal' => [
        'level' => 3,
        'can_be_created_by' => ['Director', 'Principal'],
        'permissions' => [
            'view_all_students', 'manage_students', 'view_all_staff',
            'manage_teachers', 'manage_classes', 'manage_subjects',
            'manage_exams', 'view_results', 'enter_grades',
            'view_attendance', 'mark_attendance', 'manage_timetable',
            'create_announcements', 'view_financial_reports',
            'approve_admissions', 'suspend_students'
        ]
    ],
    'Headmaster' => [
        'level' => 4,
        'can_be_created_by' => ['Director', 'Principal', 'Vice_Principal'],
        'permissions' => [
            'view_all_students', 'manage_students',
            'view_teachers', 'manage_classes', 'manage_subjects',
            'manage_exams', 'view_results', 'enter_grades',
            'view_attendance', 'mark_attendance', 'manage_timetable',
            'create_announcements', 'approve_admissions',
            'discipline_students', 'award_merits'
        ]
    ],
    'Exam_Officer' => [
        'level' => 5,
        'can_be_created_by' => ['Director', 'Principal', 'Vice_Principal', 'Headmaster'],
        'permissions' => [
            'view_all_students', 'manage_exams', 'enter_grades',
            'view_results', 'generate_reports', 'print_results',
            'create_result_templates', 'publish_results',
            'view_attendance_reports'
        ]
    ],
    'Bursar' => [
        'level' => 5,
        'can_be_created_by' => ['Director', 'Principal'], // Only top management
        'permissions' => [
            'view_all_students', 'process_payments', 'view_payments',
            'manage_fees', 'process_salaries', 'view_salaries',
            'manage_expenses', 'view_financial_reports',
            'generate_financial_statements', 'approve_refunds',
            'view_staff_list' // Only for salary purposes
        ]
    ],
    'Teacher' => [
        'level' => 6,
        'can_be_created_by' => ['Director', 'Principal', 'Vice_Principal', 'Headmaster'],
        'permissions' => [
            'view_assigned_students', 'view_assigned_classes',
            'enter_grades', 'view_own_results', 'mark_attendance',
            'view_own_salary', 'update_own_profile'
        ]
    ],
    'Student' => [
        'level' => 7,
        'can_be_created_by' => ['self'], // Self-registration with payment
        'permissions' => [
            'view_own_profile', 'view_own_results', 'view_own_payments',
            'make_payments', 'view_own_timetable'
        ]
    ]
]);

// ID Format Patterns
define('ID_FORMATS', [
    'student' => 'IFS/STD/{year}/{sequence}',      // IFS/STD/26/0001
    'teacher' => 'IFS/TCH/{start_year}/{sequence}', // IFS/TCH/2019/001
    'director' => 'IFS/DIR/{start_year}/{sequence}', // IFS/DIR/2009/001
    'principal' => 'IFS/PRN/{start_year}/{sequence}', // IFS/PRN/2015/001
    'vice_principal' => 'IFS/VP/{start_year}/{sequence}', // IFS/VP/2018/001
    'headmaster' => 'IFS/HM/{start_year}/{sequence}', // IFS/HM/2012/001
    'exam_officer' => 'IFS/EXO/{start_year}/{sequence}', // IFS/EXO/2020/001
    'bursar' => 'IFS/BUR/{start_year}/{sequence}' // IFS/BUR/2010/001
]);

// Fee amounts
define('REGISTRATION_FEE', 5500);
define('NURSERY_SCHOOL_FEE', 15000);
define('PRIMARY_SCHOOL_FEE', 20000);
define('JSS_SCHOOL_FEE', 25000);
define('SSS_SCHOOL_FEE', 30000);
?>