<?php
/**
 * Helper Functions with Proper ID Generation
 */

require_once 'database.php';
require_once 'config.php';

/**
 * Generate ID based on role and format
 */
function generateID($role, $startYear = null) {
    $db = getDB();
    $currentYear = date('Y');
    $shortYear = substr($currentYear, -2); // Last 2 digits for students
    
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateID($type, $year = null) {
    $year = $year ?? date('Y');
    $prefix = [
        'student' => 'STD',
        'Teacher' => 'TCH',
        'Principal' => 'PRC',
        'Vice Principal' => 'VCP',
        'Headmaster' => 'HDM',
        'Exam Officer' => 'EXO',
        'Bursar' => 'BUR',
        'Director' => 'DIR'
    ];
    
    $pref = $prefix[$type] ?? 'STF';
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return $pref . $year . $random;
}

function logActivity($action, $description) {
    // Implement logging to database or file
    $logFile = __DIR__ . '/../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$action}] {$description}" . PHP_EOL;
    
    // Ensure logs directory exists
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    error_log($logEntry, 3, $logFile);
}

function sendEmail($to, $subject, $body) {
    // Implement email sending (use PHPMailer or mail())
    // Placeholder for now
    return true;
}

    switch ($role) {
        case 'student':
            // Format: IFS/STD/26/0001
            $prefix = 'IFS/STD/' . $shortYear . '/';
            $sequence = getNextSequence('student', $currentYear);
            return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
        case 'teacher':
            // Format: IFS/TCH/2019/001
            $year = $startYear ?? $currentYear;
            $prefix = 'IFS/TCH/' . $year . '/';
            $sequence = getNextSequence('teacher', $year);
            return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
        case 'Director':
            // Format: IFS/DIR/2009/001
            $year = $startYear ?? $currentYear;
            $prefix = 'IFS/DIR/' . $year . '/';
            $sequence = getNextSequence('director', $year);
            return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
        case 'Principal':
            // Format: IFS/PRN/2015/001
            $year = $startYear ?? $currentYear;
            $prefix = 'IFS/PRN/' . $year . '/';
            $sequence = getNextSequence('principal', $year);
            return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
        case 'Vice_Principal':
            // Format: IFS/VP/2018/001
            $year = $startYear ?? $currentYear;
            $prefix = 'IFS/VP/' . $year . '/';
            $sequence = getNextSequence('vice_principal', $year);
            return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
        case 'Headmaster':
            // Format: IFS/HM/2012/001
            $year = $startYear ?? $currentYear;
            $prefix = 'IFS/HM/' . $year . '/';
            $sequence = getNextSequence('headmaster', $year);
            return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
        case 'Exam_Officer':
            // Format: IFS/EXO/2020/001
            $year = $startYear ?? $currentYear;
            $prefix = 'IFS/EXO/' . $year . '/';
            $sequence = getNextSequence('exam_officer', $year);
            return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
        case 'Bursar':
            // Format: IFS/BUR/2010/001
            $year = $startYear ?? $currentYear;
            $prefix = 'IFS/BUR/' . $year . '/';
            $sequence = getNextSequence('bursar', $year);
            return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
        default:
            throw new Exception("Unknown role: " . $role);
    }
}

/**
 * Get next sequence number for ID
 */
function getNextSequence($type, $year) {
    $db = getDB();
    
    // Create sequence tracking table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS id_sequences (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL,
            year TEXT NOT NULL,
            last_number INTEGER DEFAULT 0,
            UNIQUE(type, year)
        )
    ");
    
    // Get current sequence
    $stmt = $db->prepare("SELECT last_number FROM id_sequences WHERE type = ? AND year = ?");
    $stmt->execute([$type, $year]);
    $result = $stmt->fetch();
    
    if ($result) {
        $nextNumber = $result['last_number'] + 1;
        $stmt = $db->prepare("UPDATE id_sequences SET last_number = ? WHERE type = ? AND year = ?");
        $stmt->execute([$nextNumber, $type, $year]);
    } else {
        $nextNumber = 1;
        $stmt = $db->prepare("INSERT INTO id_sequences (type, year, last_number) VALUES (?, ?, ?)");
        $stmt->execute([$type, $year, $nextNumber]);
    }
    
    return $nextNumber;
}

/**
 * Parse ID to extract information
 */
function parseID($id) {
    $parts = explode('/', $id);
    if (count($parts) !== 4) return null;
    
    return [
        'school' => $parts[0],
        'type' => $parts[1],
        'year' => $parts[2],
        'sequence' => $parts[3]
    ];
}

/**
 * Get role from ID prefix
 */
function getRoleFromID($id) {
    $parsed = parseID($id);
    if (!$parsed) return null;
    
    $typeMap = [
        'STD' => 'Student',
        'TCH' => 'Teacher',
        'DIR' => 'Director',
        'PRN' => 'Principal',
        'VP' => 'Vice_Principal',
        'HM' => 'Headmaster',
        'EXO' => 'Exam_Officer',
        'BUR' => 'Bursar'
    ];
    
    return $typeMap[$parsed['type']] ?? null;
}

/**
 * Generate Receipt Number
 */
function generateReceiptNo() {
    $db = getDB();
    $date = date('Ymd');
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count FROM payments 
        WHERE date(payment_date) = date('now')
    ");
    $stmt->execute();
    $count = $stmt->fetch()['count'] + 1;
    
    return 'RCP/' . $date . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

/**
 * Calculate Grade
 */
function calculateGrade($total) {
    if ($total >= 70) return ['grade' => 'A', 'remark' => 'Excellent'];
    if ($total >= 60) return ['grade' => 'B', 'remark' => 'Very Good'];
    if ($total >= 50) return ['grade' => 'C', 'remark' => 'Good'];
    if ($total >= 45) return ['grade' => 'D', 'remark' => 'Pass'];
    if ($total >= 40) return ['grade' => 'E', 'remark' => 'Fair'];
    return ['grade' => 'F', 'remark' => 'Fail'];
}

/**
 * Get School Fee by Class
 */
function getSchoolFee($class) {
    if (strpos($class, 'Nursery') !== false) return NURSERY_SCHOOL_FEE;
    if (strpos($class, 'Primary') !== false) return PRIMARY_SCHOOL_FEE;
    if (strpos($class, 'JSS') !== false) return JSS_SCHOOL_FEE;
    if (strpos($class, 'SS') !== false) return SSS_SCHOOL_FEE;
    return 0;
}

/**
 * Format Currency
 */
function formatCurrency($amount) {
    return '₦' . number_format($amount, 2);
}

/**
 * Sanitize Input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Send JSON Response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Log Activity
 */
function logActivity($action, $details = '') {
    $db = getDB();
    $user = isset($_SESSION['user']) ? ($_SESSION['user']['reg_no'] ?? $_SESSION['user']['staff_id'] ?? 'system') : 'guest';
    
    $stmt = $db->prepare("
        CREATE TABLE IF NOT EXISTS activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user TEXT,
            user_role TEXT,
            action TEXT,
            details TEXT,
            ip_address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $stmt->execute();
    
    $role = isset($_SESSION['user']) ? ($_SESSION['user']['role'] ?? 'Student') : 'Guest';
    
    $stmt = $db->prepare("INSERT INTO activity_log (user, user_role, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user, $role, $action, $details, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
}

/**
 * Get Dashboard Stats
 */
function getDashboardStats($role = null) {
    $db = getDB();
    $stats = [];
    
    // Common stats
    $stmt = $db->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'");
    $stats['students'] = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM staff WHERE status = 'active'");
    $stats['staff'] = $stmt->fetch()['count'];
    
    // Role-specific stats
    if ($role === 'Bursar' || $role === 'Director' || $role === 'Principal') {
        $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'");
        $stats['revenue'] = $stmt->fetch()['total'];
        
        $stmt = $db->query("
            SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
            WHERE strftime('%Y-%m', expense_date) = strftime('%Y-%m', 'now')
        ");
        $stats['expenses'] = $stmt->fetch()['total'];
    }
    
    if ($role === 'Exam_Officer') {
        $stmt = $db->query("SELECT COUNT(*) as count FROM exams WHERE status = 'upcoming'");
        $stats['upcoming_exams'] = $stmt->fetch()['count'];
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM grades WHERE term = ? AND session = ?", [CURRENT_TERM, CURRENT_SESSION]);
        $stats['grades_entered'] = $stmt->fetch()['count'];
    }
    
    if ($role === 'Headmaster') {
        $today = date('Y-m-d');
        $stmt = $db->query("
            SELECT COUNT(DISTINCT reg_no) as count FROM attendance 
            WHERE attendance_date = ? AND status = 'present'
        ", [$today]);
        $stats['present_today'] = $stmt->fetch()['count'];
    }
    
    return $stats;
}

/**
 * Send Email (placeholder - integrate with your email service)
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    // Implement with PHPMailer or your preferred email service
    logActivity('email_sent', "To: $to, Subject: $subject");
    return true;
}

/**
 * Generate PDF (placeholder - integrate with TCPDF or similar)
 */
function generatePDF($content, $filename) {
    // Implement with TCPDF or Dompdf
    logActivity('pdf_generated', "Filename: $filename");
    return true;
}
?>