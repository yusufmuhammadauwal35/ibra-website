<?php
/**
 * API Endpoint for Ibra Foundation School Management System
 * All backend operations go through this file
 */

require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // Authentication
        case 'login':
            handleLogin();
            break;
            
        case 'logout':
            $result = $auth->logout();
            jsonResponse($result);
            break;
            
        case 'check_session':
            checkSession();
            break;
            
        // Students - REMOVED DUPLICATE
        case 'register_student':
            registerStudent();
            break;
            
        case 'get_students':
            getStudents();
            break;
            
        case 'get_student':
            getStudent();
            break;
            
        case 'update_student':
            updateStudent();
            break;
            
        case 'delete_student':
            deleteStudent();
            break;
            
        // Staff
        case 'get_staff':
            getStaff();
            break;
            
        case 'add_staff':
            addStaff();
            break;
            
        case 'update_staff':
            updateStaff();
            break;
            
        case 'delete_staff':
            deleteStaff();
            break;
            
        // Payments
        case 'make_payment':
            makePayment();
            break;
            
        case 'get_payments':
            getPayments();
            break;
            
        case 'get_student_payments':
            getStudentPayments();
            break;
            
        // Paystack Payment Endpoints
        case 'initialize_payment':
            initializePayment();
            break;

        case 'verify_payment':
            verifyPayment();
            break;

        case 'get_paystack_transactions':
            getPaystackTransactions();
            break;

        case 'get_banks':
            getBanks();
            break;

        case 'resolve_account':
            resolveAccount();
            break;

        case 'initialize_salary_transfer':
            initializeSalaryTransfer();
            break;

        case 'finalize_transfer':
            finalizeTransfer();
            break;

        case 'get_payment_history':
            getPaymentHistory();
            break;

        case 'get_paystack_key':
            getPaystackKey();
            break;
            
        // Salaries
        case 'process_salary':
            processSalary();
            break;
            
        case 'process_all_salaries':
            processAllSalaries();
            break;
            
        case 'get_salaries':
            getSalaries();
            break;
            
        case 'get_staff_salary':
            getStaffSalary();
            break;
            
        // Expenses
        case 'add_expense':
            addExpense();
            break;
            
        case 'get_expenses':
            getExpenses();
            break;
            
        // Attendance
        case 'mark_attendance':
            markAttendance();
            break;
            
        case 'get_attendance':
            getAttendance();
            break;
            
        case 'save_attendance':
            saveAttendance();
            break;
            
        // Grades
        case 'enter_grades':
            enterGrades();
            break;
            
        case 'get_grades':
            getGrades();
            break;
            
        case 'save_grades':
            saveGrades();
            break;
            
        // Classes
        case 'get_classes':
            getClasses();
            break;
            
        case 'add_class':
            addClass();
            break;
            
        // Subjects
        case 'get_subjects':
            getSubjects();
            break;
            
        case 'add_subject':
            addSubject();
            break;
            
        // Timetable
        case 'get_timetable':
            getTimetable();
            break;
            
        case 'update_timetable':
            updateTimetable();
            break;
            
        // Announcements
        case 'create_announcement':
            createAnnouncement();
            break;
            
        case 'get_announcements':
            getAnnouncements();
            break;
            
        // Exams
        case 'create_exam':
            createExam();
            break;
            
        case 'get_exams':
            getExams();
            break;
            
        // Dashboard
        case 'get_dashboard_stats':
            getDashboardStats();
            break;
            
        case 'get_teacher_dashboard':
            getTeacherDashboard();
            break;
            
        case 'get_bursar_dashboard':
            getBursarDashboard();
            break;
            
        // Reports
        case 'generate_report':
            generateReport();
            break;
            
        case 'get_monthly_summary':
            getMonthlySummary();
            break;

        // Staff management (by authorized users) - MOVED FROM BOTTOM
        case 'create_staff':
            createStaff();
            break;

        case 'approve_staff_application':
            approveStaffApplication();
            break;

        case 'get_pending_applications':
            getPendingApplications();
            break;

        case 'reject_staff_application':
            rejectStaffApplication();
            break;

        // Role-specific data
        case 'get_role_dashboard':
            getRoleDashboard();
            break;

        case 'get_my_permissions':
            getMyPermissions();
            break;

        // Director-specific
        case 'get_management_team':
            getManagementTeam();
            break;

        case 'get_audit_logs':
            getAuditLogs();
            break;

        // Principal-specific
        case 'approve_admission':
            approveAdmission();
            break;

        case 'get_pending_admissions':
            getPendingAdmissions();
            break;

        // Vice Principal-specific
        case 'get_discipline_cases':
            getDisciplineCases();
            break;

        case 'create_discipline_case':
            createDisciplineCase();
            break;

        // Headmaster-specific
        case 'get_daily_attendance':
            getDailyAttendance();
            break;

        case 'award_merit':
            awardMerit();
            break;

        // Exam Officer-specific
        case 'publish_results':
            publishResults();
            break;

        case 'get_result_statistics':
            getResultStatistics();
            break;
            
        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    logActivity('error', $e->getMessage());
    jsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
}

// ==================== HANDLER FUNCTIONS ====================

function handleLogin() {
    global $auth;
    $data = json_decode(file_get_contents('php://input'), true);
    
    $regNo = sanitize($data['reg_no'] ?? '');
    $password = $data['password'] ?? '';
    $type = $data['type'] ?? 'student';
    
    if (empty($regNo) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Registration number and password required'], 400);
    }
    
    $result = $auth->login($regNo, $password, $type);
    
    if ($result['success']) {
        logActivity('login', "User {$regNo} logged in");
    }
    
    jsonResponse($result);
}

function checkSession() {
    global $auth;
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        $type = $auth->getUserType();
        jsonResponse([
            'logged_in' => true,
            'user' => $user,
            'type' => $type,
            'role' => $type === 'staff' ? $user['role'] : null
        ]);
    } else {
        jsonResponse(['logged_in' => false]);
    }
}

// FIXED: Moved Paystack functions outside of registerStudent
function initializePayment() {
    global $auth, $paymentProcessor;
    $auth->requireAuth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $result = $paymentProcessor->initializeFeePayment(
        $data['reg_no'],
        $data['payment_type'],
        $data['amount'],
        $data['email'],
        $data['callback_url'] ?? null
    );
    
    jsonResponse($result);
}

function verifyPayment() {
    global $auth, $paymentProcessor;
    $auth->requireAuth();
    
    $reference = $_GET['reference'] ?? '';
    
    if (empty($reference)) {
        jsonResponse(['success' => false, 'message' => 'Reference required'], 400);
    }
    
    $result = $paymentProcessor->verifyPayment($reference);
    jsonResponse($result);
}
function getPaystackTransactions() {
    global $auth, $paymentProcessor;

    $auth->requireRole(['Bursar', 'Director']);

    $page = $_GET['page'] ?? 1;
    $perPage = $_GET['per_page'] ?? 50;

    $result = $paymentProcessor->getAllTransactions($page, $perPage);
    jsonResponse($result);
}


function getBanks() {
    global $paymentProcessor;
    $result = $paymentProcessor->listBanks();
    jsonResponse($result);
}


function resolveAccount() {
    global $paymentProcessor;
    
    $accountNumber = $_GET['account_number'] ?? '';
    $bankCode = $_GET['bank_code'] ?? '';
    
    if (empty($accountNumber) || empty($bankCode)) {
        jsonResponse(['success' => false, 'message' => 'Account number and bank code required'], 400);
    }
    
    $result = $paymentProcessor->paystack->resolveAccount($accountNumber, $bankCode);
    jsonResponse($result);
}

function initializeSalaryTransfer() {
    global $auth, $paymentProcessor;
    $auth->requireRole(['Bursar', 'Director']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $result = $paymentProcessor->initializeSalaryTransfer(
        $data['staff_id'],
        $data['amount'],
        $data['bank_code'],
        $data['account_number'],
        $data['narration'] ?? 'Salary Payment'
    );
    
    jsonResponse($result);
}

function finalizeTransfer() {
    global $auth, $paymentProcessor;
    $auth->requireRole(['Bursar', 'Director']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $result = $paymentProcessor->paystack->finalizeTransfer(
        $data['transfer_code'],
        $data['otp']
    );
    
    jsonResponse($result);
}

function getPaymentHistory() {
    global $auth, $paymentProcessor;
    $auth->requireAuth();
    
    $regNo = $_GET['reg_no'] ?? null;
    
    if (!$regNo && $auth->getUserType() === 'student') {
        $user = $auth->getCurrentUser();
        $regNo = $user['reg_no'];
    }
    
    if (!$regNo) {
        jsonResponse(['success' => false, 'message' => 'Registration number required'], 400);
    }
    
    $history = $paymentProcessor->getPaymentHistory($regNo);
    jsonResponse(['success' => true, 'history' => $history]);
}

function getPaystackKey() {
    global $paymentProcessor;
    // Only return public key - safe for frontend
    jsonResponse([
        'success' => true,
        'public_key' => $paymentProcessor->getPublicKey()
    ]);
}

// FIXED: Cleaned up registerStudent function
function registerStudent() {
    global $auth;
    $auth->requireRole(['Director', 'Principal', 'Headmaster']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $regNo = generateRegNo('student');
    $password = password_hash($regNo, PASSWORD_DEFAULT); // Default password is reg_no
    
    try {
        $stmt = $db->prepare("
            INSERT INTO students (reg_no, surname, first_name, other_names, date_of_birth, gender, class, 
                                parent_name, parent_phone, parent_email, address, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $regNo,
            sanitize($data['surname']),
            sanitize($data['first_name']),
            sanitize($data['other_names'] ?? ''),
            $data['date_of_birth'],
            sanitize($data['gender']),
            sanitize($data['class']),
            sanitize($data['parent_name']),
            sanitize($data['parent_phone']),
            sanitize($data['parent_email'] ?? ''),
            sanitize($data['address']),
            $password
        ]);
        
        // Create initial payment record for registration fee
        $receiptNo = generateReceiptNo();
        $stmt = $db->prepare("
            INSERT INTO payments (payment_type, reg_no, amount, description, payment_method, receipt_no, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Registration Fee',
            $regNo,
            REGISTRATION_FEE,
            'Initial registration fee payment',
            'Bank Transfer',
            $receiptNo,
            'completed'
        ]);
        
        logActivity('student_registered', "Student {$regNo} registered");
        
        jsonResponse([
            'success' => true,
            'message' => 'Student registered successfully',
            'reg_no' => $regNo,
            'receipt_no' => $receiptNo
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
    }
}

function getStudents() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $class = $_GET['class'] ?? null;
    $search = $_GET['search'] ?? null;
    
    $sql = "SELECT * FROM students WHERE 1=1";
    $params = [];
    
    if ($class) {
        $sql .= " AND class = ?";
        $params[] = $class;
    }
    
    if ($search) {
        $sql .= " AND (reg_no LIKE ? OR surname LIKE ? OR first_name LIKE ?)";
        $searchTerm = "%{$search}%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    $sql .= " ORDER BY surname, first_name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
    // Get payment status for each student
    foreach ($students as &$student) {
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0) as paid 
            FROM payments 
            WHERE reg_no = ? AND payment_type = 'School Fees' AND status = 'completed'
        ");
        $stmt->execute([$student['reg_no']]);
        $paid = $stmt->fetch()['paid'];
        
        $fee = getSchoolFee($student['class']);
        $student['fee_due'] = $fee;
        $student['fee_paid'] = $paid;
        $student['fee_balance'] = $fee - $paid;
        $student['payment_status'] = $paid >= $fee ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
    }
    
    jsonResponse(['success' => true, 'students' => $students]);
}

function getStudent() {
    global $auth;
    $auth->requireAuth();
    
    $regNo = $_GET['reg_no'] ?? null;
    if (!$regNo) {
        jsonResponse(['error' => 'Registration number required'], 400);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM students WHERE reg_no = ?");
    $stmt->execute([$regNo]);
    $student = $stmt->fetch();
    
    if (!$student) {
        jsonResponse(['error' => 'Student not found'], 404);
    }
    
    // Get payments
    $payments = getStudentPaymentsData($regNo); // Renamed to avoid conflict
    $student['payments'] = $payments;
    
    // Get results
    $results = getStudentResults($regNo);
    $student['results'] = $results;
    
    jsonResponse(['success' => true, 'student' => $student]);
}

function updateStudent() {
    global $auth;
    $auth->requireRole(['Director', 'Principal', 'Headmaster']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $stmt = $db->prepare("
        UPDATE students SET
            surname = ?,
            first_name = ?,
            other_names = ?,
            class = ?,
            parent_name = ?,
            parent_phone = ?,
            parent_email = ?,
            address = ?,
            status = ?
        WHERE reg_no = ?
    ");
    
    $stmt->execute([
        sanitize($data['surname']),
        sanitize($data['first_name']),
        sanitize($data['other_names']),
        sanitize($data['class']),
        sanitize($data['parent_name']),
        sanitize($data['parent_phone']),
        sanitize($data['parent_email']),
        sanitize($data['address']),
        sanitize($data['status']),
        sanitize($data['reg_no'])
    ]);
    
    logActivity('student_updated', "Student {$data['reg_no']} updated");
    jsonResponse(['success' => true, 'message' => 'Student updated successfully']);
}

function deleteStudent() {
    global $auth;
    $auth->requireRole(['Director', 'Principal']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $stmt = $db->prepare("UPDATE students SET status = 'inactive' WHERE reg_no = ?");
    $stmt->execute([sanitize($data['reg_no'])]);
    
    logActivity('student_deleted', "Student {$data['reg_no']} marked as inactive");
    jsonResponse(['success' => true, 'message' => 'Student removed successfully']);
}

function getStaff() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $role = $_GET['role'] ?? null;
    
    $sql = "SELECT * FROM staff WHERE 1=1";
    $params = [];
    
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    
    $sql .= " ORDER BY name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $staff = $stmt->fetchAll();
    
    // Remove password from response
    foreach ($staff as &$member) {
        unset($member['password']);
    }
    
    jsonResponse(['success' => true, 'staff' => $staff]);
}

function addStaff() {
    global $auth;
    $auth->requireRole(['Director', 'Principal']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $staffId = generateRegNo('staff');
    $password = password_hash($data['password'] ?? 'password123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO staff (staff_id, name, email, phone, role, department, basic_salary, allowances, deductions, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $staffId,
        sanitize($data['name']),
        sanitize($data['email']),
        sanitize($data['phone']),
        sanitize($data['role']),
        sanitize($data['department']),
        $data['basic_salary'] ?? 0,
        $data['allowances'] ?? 0,
        $data['deductions'] ?? 0,
        $password
    ]);
    
    logActivity('staff_added', "Staff {$staffId} added");
    jsonResponse(['success' => true, 'message' => 'Staff added successfully', 'staff_id' => $staffId]);
}

function updateStaff() {
    global $auth;
    $auth->requireRole(['Director', 'Principal']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $stmt = $db->prepare("
        UPDATE staff SET
            name = ?,
            email = ?,
            phone = ?,
            role = ?,
            department = ?,
            basic_salary = ?,
            allowances = ?,
            deductions = ?,
            status = ?
        WHERE staff_id = ?
    ");
    
    $stmt->execute([
        sanitize($data['name']),
        sanitize($data['email']),
        sanitize($data['phone']),
        sanitize($data['role']),
        sanitize($data['department']),
        $data['basic_salary'],
        $data['allowances'],
        $data['deductions'],
        sanitize($data['status']),
        sanitize($data['staff_id'])
    ]);
    
    logActivity('staff_updated', "Staff {$data['staff_id']} updated");
    jsonResponse(['success' => true, 'message' => 'Staff updated successfully']);
}

function deleteStaff() {
    global $auth;
    $auth->requireRole(['Director']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $stmt = $db->prepare("UPDATE staff SET status = 'inactive' WHERE staff_id = ?");
    $stmt->execute([sanitize($data['staff_id'])]);
    
    logActivity('staff_deleted', "Staff {$data['staff_id']} marked as inactive");
    jsonResponse(['success' => true, 'message' => 'Staff removed successfully']);
}

function makePayment() {
    global $auth;
    $auth->requireAuth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $receiptNo = generateReceiptNo();
    $user = $auth->getCurrentUser();
    
    $stmt = $db->prepare("
        INSERT INTO payments (payment_type, reg_no, amount, description, payment_method, receipt_no, recorded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        sanitize($data['payment_type']),
        sanitize($data['reg_no']),
        $data['amount'],
        sanitize($data['description'] ?? ''),
        sanitize($data['payment_method']),
        $receiptNo,
        $user['id'] ?? null
    ]);
    
    logActivity('payment_made', "Payment of {$data['amount']} for {$data['reg_no']}");
    jsonResponse(['success' => true, 'message' => 'Payment recorded successfully', 'receipt_no' => $receiptNo]);
}

function getPayments() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $type = $_GET['type'] ?? null;
    $from = $_GET['from'] ?? null;
    $to = $_GET['to'] ?? null;
    
    $sql = "SELECT p.*, s.surname, s.first_name FROM payments p LEFT JOIN students s ON p.reg_no = s.reg_no WHERE 1=1";
    $params = [];
    
    if ($type) {
        $sql .= " AND p.payment_type = ?";
        $params[] = $type;
    }
    
    if ($from) {
        $sql .= " AND date(p.payment_date) >= ?";
        $params[] = $from;
    }
    
    if ($to) {
        $sql .= " AND date(p.payment_date) <= ?";
        $params[] = $to;
    }
    
    $sql .= " ORDER BY p.payment_date DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'payments' => $payments]);
}

// FIXED: Renamed to avoid conflict with case statement
function getStudentPaymentsData($regNo = null) {
    global $auth;
    $auth->requireAuth();
    
    if (!$regNo) {
        $regNo = $_GET['reg_no'] ?? null;
    }
    
    if (!$regNo) {
        jsonResponse(['error' => 'Registration number required'], 400);
        return [];
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM payments WHERE reg_no = ? ORDER BY payment_date DESC");
    $stmt->execute([$regNo]);
    return $stmt->fetchAll();
}

// Case handler for get_student_payments
function getStudentPayments() {
    $payments = getStudentPaymentsData();
    jsonResponse(['success' => true, 'payments' => $payments]);
}

function processSalary() {
    global $auth;
    $auth->requireRole(['Bursar', 'Director']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $user = $auth->getCurrentUser();
    $monthYear = date('Y-m');
    
    // Check if already paid
    $stmt = $db->prepare("SELECT id FROM salaries WHERE staff_id = ? AND month_year = ?");
    $stmt->execute([$data['staff_id'], $monthYear]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Salary already processed for this month'], 400);
    }
    
    // Get staff details
    $stmt = $db->prepare("SELECT basic_salary, allowances, deductions FROM staff WHERE staff_id = ?");
    $stmt->execute([$data['staff_id']]);
    $staff = $stmt->fetch();
    
    if (!$staff) {
        jsonResponse(['error' => 'Staff not found'], 404);
    }
    
    $netSalary = $staff['basic_salary'] + $staff['allowances'] - $staff['deductions'];
    
    $stmt = $db->prepare("
        INSERT INTO salaries (staff_id, month_year, basic_salary, allowances, deductions, net_salary, paid_by, status, payment_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', datetime('now'))
    ");
    
    $stmt->execute([
        $data['staff_id'],
        $monthYear,
        $staff['basic_salary'],
        $staff['allowances'],
        $staff['deductions'],
        $netSalary,
        $user['id'] ?? null
    ]);
    
    // Record as expense
    $stmt = $db->prepare("
        INSERT INTO expenses (category, description, amount, expense_date, recorded_by)
        VALUES ('Salary', ?, ?, date('now'), ?)
    ");
    $stmt->execute(["Salary payment for {$data['staff_id']} - {$monthYear}", $netSalary, $user['id'] ?? null]);
    
    logActivity('salary_paid', "Salary paid to {$data['staff_id']} for {$monthYear}");
    jsonResponse(['success' => true, 'message' => 'Salary processed successfully']);
}

function processAllSalaries() {
    global $auth;
    $auth->requireRole(['Bursar', 'Director']);
    
    $db = getDB();
    $user = $auth->getCurrentUser();
    $monthYear = date('Y-m');
    
    // Get all active staff
    $stmt = $db->query("SELECT staff_id, basic_salary, allowances, deductions FROM staff WHERE status = 'active'");
    $staffList = $stmt->fetchAll();
    
    $processed = 0;
    $totalAmount = 0;
    
    foreach ($staffList as $staff) {
        // Check if already paid
        $check = $db->prepare("SELECT id FROM salaries WHERE staff_id = ? AND month_year = ?");
        $check->execute([$staff['staff_id'], $monthYear]);
        if ($check->fetch()) continue;
        
        $netSalary = $staff['basic_salary'] + $staff['allowances'] - $staff['deductions'];
        
        $stmt = $db->prepare("
            INSERT INTO salaries (staff_id, month_year, basic_salary, allowances, deductions, net_salary, paid_by, status, payment_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', datetime('now'))
        ");
        $stmt->execute([
            $staff['staff_id'],
            $monthYear,
            $staff['basic_salary'],
            $staff['allowances'],
            $staff['deductions'],
            $netSalary,
            $user['id'] ?? null
        ]);
        
        $totalAmount += $netSalary;
        $processed++;
    }
    
    // Record total as expense
    if ($totalAmount > 0) {
        $stmt = $db->prepare("
            INSERT INTO expenses (category, description, amount, expense_date, recorded_by)
            VALUES ('Salary', ?, ?, date('now'), ?)
        ");
        $stmt->execute(["Bulk salary payment for {$monthYear}", $totalAmount, $user['id'] ?? null]);
    }
    
    logActivity('bulk_salary_paid', "Bulk salary payment processed for {$processed} staff, total: {$totalAmount}");
    jsonResponse(['success' => true, 'message' => "Processed salaries for {$processed} staff members", 'total_amount' => $totalAmount]);
}

function getSalaries() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $month = $_GET['month'] ?? date('Y-m');
    
    $stmt = $db->prepare("
        SELECT s.*, st.name, st.role 
        FROM salaries s 
        JOIN staff st ON s.staff_id = st.staff_id 
        WHERE s.month_year = ?
        ORDER BY st.name
    ");
    $stmt->execute([$month]);
    $salaries = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'salaries' => $salaries]);
}

function getStaffSalary() {
    global $auth;
    $auth->requireAuth();
    
    $staffId = $_GET['staff_id'] ?? null;
    if (!$staffId) {
        // Get current user's salary
        $user = $auth->getCurrentUser();
        $staffId = $user['staff_id'] ?? null;
    }
    
    if (!$staffId) {
        jsonResponse(['error' => 'Staff ID required'], 400);
    }
    
    $history = getSalaryHistory($staffId);
    
    // Get current month status
    $db = getDB();
    $monthYear = date('Y-m');
    $stmt = $db->prepare("SELECT * FROM salaries WHERE staff_id = ? AND month_year = ?");
    $stmt->execute([$staffId, $monthYear]);
    $current = $stmt->fetch();
    
    jsonResponse(['success' => true, 'history' => $history, 'current' => $current]);
}

function addExpense() {
    global $auth;
    $auth->requireRole(['Bursar', 'Director']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $user = $auth->getCurrentUser();
    
    $stmt = $db->prepare("
        INSERT INTO expenses (category, description, amount, expense_date, recorded_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        sanitize($data['category']),
        sanitize($data['description']),
        $data['amount'],
        $data['expense_date'] ?? date('Y-m-d'),
        $user['id'] ?? null
    ]);
    
    logActivity('expense_added', "Expense added: {$data['category']} - {$data['amount']}");
    jsonResponse(['success' => true, 'message' => 'Expense added successfully']);
}

function getExpenses() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $from = $_GET['from'] ?? null;
    $to = $_GET['to'] ?? null;
    $category = $_GET['category'] ?? null;
    
    $sql = "SELECT * FROM expenses WHERE 1=1";
    $params = [];
    
    if ($from) {
        $sql .= " AND expense_date >= ?";
        $params[] = $from;
    }
    
    if ($to) {
        $sql .= " AND expense_date <= ?";
        $params[] = $to;
    }
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY expense_date DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'expenses' => $expenses]);
}

function saveAttendance() {
    global $auth;
    $auth->requireRole(['Teacher', 'Principal', 'Headmaster']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $user = $auth->getCurrentUser();
    $attendanceDate = $data['date'] ?? date('Y-m-d');
    
    $db->beginTransaction();
    
    try {
        foreach ($data['attendance'] as $record) {
            // Check if record exists
            $stmt = $db->prepare("SELECT id FROM attendance WHERE reg_no = ? AND attendance_date = ?");
            $stmt->execute([$record['reg_no'], $attendanceDate]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $stmt = $db->prepare("
                    UPDATE attendance SET status = ?, remarks = ?, marked_by = ?
                    WHERE reg_no = ? AND attendance_date = ?
                ");
                $stmt->execute([
                    $record['status'],
                    $record['remarks'] ?? '',
                    $user['id'] ?? null,
                    $record['reg_no'],
                    $attendanceDate
                ]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO attendance (reg_no, class, attendance_date, status, remarks, marked_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $record['reg_no'],
                    $data['class'],
                    $attendanceDate,
                    $record['status'],
                    $record['remarks'] ?? '',
                    $user['id'] ?? null
                ]);
            }
        }
        
        $db->commit();
        logActivity('attendance_saved', "Attendance saved for {$data['class']} on {$attendanceDate}");
        jsonResponse(['success' => true, 'message' => 'Attendance saved successfully']);
        
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Failed to save attendance: ' . $e->getMessage()], 500);
    }
}

function getAttendance() {
    global $auth;
    $auth->requireAuth();
    
    $class = $_GET['class'] ?? null;
    $date = $_GET['date'] ?? date('Y-m-d');
    
    if (!$class) {
        jsonResponse(['error' => 'Class required'], 400);
    }
    
    $db = getDB();
    
    // Get students in class
    $students = getClassStudents($class);
    
    // Get attendance for date
    $stmt = $db->prepare("SELECT * FROM attendance WHERE class = ? AND attendance_date = ?");
    $stmt->execute([$class, $date]);
    $attendance = $stmt->fetchAll();
    
    $attendanceMap = [];
    foreach ($attendance as $record) {
        $attendanceMap[$record['reg_no']] = $record;
    }
    
    // Merge with students
    foreach ($students as &$student) {
        if (isset($attendanceMap[$student['reg_no']])) {
            $student['attendance'] = $attendanceMap[$student['reg_no']];
        } else {
            $student['attendance'] = null;
        }
    }
    
    jsonResponse(['success' => true, 'students' => $students, 'date' => $date]);
}

function saveGrades() {
    global $auth;
    $auth->requireRole(['Teacher', 'Principal', 'Exam Officer']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $user = $auth->getCurrentUser();
    
    $db->beginTransaction();
    
    try {
        foreach ($data['grades'] as $grade) {
            $total = ($grade['ca_score'] ?? 0) + ($grade['exam_score'] ?? 0);
            $gradeInfo = calculateGrade($total);
            
            // Check if record exists
            $stmt = $db->prepare("
                SELECT id FROM grades 
                WHERE reg_no = ? AND subject = ? AND term = ? AND session = ?
            ");
            $stmt->execute([
                $grade['reg_no'],
                $data['subject'],
                $data['term'],
                $data['session'] ?? CURRENT_SESSION
            ]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $stmt = $db->prepare("
                    UPDATE grades SET
                        ca_score = ?,
                        exam_score = ?,
                        total_score = ?,
                        grade = ?,
                        remark = ?,
                        entered_by = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $grade['ca_score'],
                    $grade['exam_score'],
                    $total,
                    $gradeInfo['grade'],
                    $gradeInfo['remark'],
                    $user['id'] ?? null,
                    $existing['id']
                ]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO grades (reg_no, class, subject, term, session, ca_score, exam_score, total_score, grade, remark, entered_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $grade['reg_no'],
                    $data['class'],
                    $data['subject'],
                    $data['term'],
                    $data['session'] ?? CURRENT_SESSION,
                    $grade['ca_score'],
                    $grade['exam_score'],
                    $total,
                    $gradeInfo['grade'],
                    $gradeInfo['remark'],
                    $user['id'] ?? null
                ]);
            }
        }
        
        $db->commit();
        logActivity('grades_saved', "Grades saved for {$data['class']} - {$data['subject']}");
        jsonResponse(['success' => true, 'message' => 'Grades saved successfully']);
        
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Failed to save grades: ' . $e->getMessage()], 500);
    }
}

function getGrades() {
    global $auth;
    $auth->requireAuth();
    
    $regNo = $_GET['reg_no'] ?? null;
    $class = $_GET['class'] ?? null;
    $subject = $_GET['subject'] ?? null;
    $term = $_GET['term'] ?? CURRENT_TERM;
    $session = $_GET['session'] ?? CURRENT_SESSION;
    
    $db = getDB();
    
    if ($regNo) {
        // Get student results
        $results = getStudentResults($regNo, $term, $session);
        jsonResponse(['success' => true, 'grades' => $results]);
    } elseif ($class && $subject) {
        // Get grades for class/subject
        $stmt = $db->prepare("
            SELECT g.*, s.surname, s.first_name 
            FROM grades g
            JOIN students s ON g.reg_no = s.reg_no
            WHERE g.class = ? AND g.subject = ? AND g.term = ? AND g.session = ?
        ");
        $stmt->execute([$class, $subject, $term, $session]);
        $grades = $stmt->fetchAll();
        jsonResponse(['success' => true, 'grades' => $grades]);
    } else {
        jsonResponse(['error' => 'Invalid parameters'], 400);
    }
}

function getClasses() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $stmt = $db->query("SELECT * FROM classes ORDER BY class_name");
    $classes = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'classes' => $classes]);
}

function addClass() {
    global $auth;
    $auth->requireRole(['Director', 'Principal']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $stmt = $db->prepare("INSERT INTO classes (class_name, section, capacity) VALUES (?, ?, ?)");
    $stmt->execute([
        sanitize($data['class_name']),
        sanitize($data['section']),
        $data['capacity'] ?? 40
    ]);
    
    jsonResponse(['success' => true, 'message' => 'Class added successfully']);
}

function getSubjects() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $class = $_GET['class'] ?? null;
    $teacher = $_GET['teacher'] ?? null;
    
    $sql = "SELECT s.*, st.name as teacher_name FROM subjects s LEFT JOIN staff st ON s.teacher_id = st.staff_id WHERE 1=1";
    $params = [];
    
    if ($class) {
        $sql .= " AND s.class = ?";
        $params[] = $class;
    }
    
    if ($teacher) {
        $sql .= " AND s.teacher_id = ?";
        $params[] = $teacher;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $subjects = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'subjects' => $subjects]);
}

function addSubject() {
    global $auth;
    $auth->requireRole(['Director', 'Principal']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $stmt = $db->prepare("INSERT INTO subjects (subject_name, class, teacher_id) VALUES (?, ?, ?)");
    $stmt->execute([
        sanitize($data['subject_name']),
        sanitize($data['class']),
        $data['teacher_id'] ?? null
    ]);
    
    jsonResponse(['success' => true, 'message' => 'Subject added successfully']);
}

function getTimetable() {
    global $auth;
    $auth->requireAuth();
    
    $class = $_GET['class'] ?? null;
    
    if (!$class) {
        jsonResponse(['error' => 'Class required'], 400);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM timetable WHERE class = ? ORDER BY day_of_week, time_slot");
    $stmt->execute([$class]);
    $timetable = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'timetable' => $timetable]);
}

function updateTimetable() {
    global $auth;
    $auth->requireRole(['Principal', 'Headmaster']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    // Clear existing timetable for class
    $stmt = $db->prepare("DELETE FROM timetable WHERE class = ?");
    $stmt->execute([$data['class']]);
    
    // Insert new entries
    $stmt = $db->prepare("
        INSERT INTO timetable (class, day_of_week, time_slot, subject, teacher_id, room)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($data['entries'] as $entry) {
        $stmt->execute([
            $data['class'],
            $entry['day'],
            $entry['time'],
            $entry['subject'],
            $entry['teacher_id'] ?? null,
            $entry['room'] ?? null
        ]);
    }
    
    jsonResponse(['success' => true, 'message' => 'Timetable updated successfully']);
}

function createAnnouncement() {
    global $auth;
    $auth->requireRole(['Director', 'Principal', 'Headmaster']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $user = $auth->getCurrentUser();
    
    $stmt = $db->prepare("
        INSERT INTO announcements (title, message, target_audience, posted_by)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        sanitize($data['title']),
        sanitize($data['message']),
        sanitize($data['target_audience'] ?? 'all'),
        $user['id'] ?? null
    ]);
    
    jsonResponse(['success' => true, 'message' => 'Announcement posted successfully']);
}

function getAnnouncements() {
    global $auth;
    $auth->requireAuth();
    
    $target = $_GET['target'] ?? 'all';
    $limit = $_GET['limit'] ?? 10;
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT a.*, s.name as posted_by_name 
        FROM announcements a
        LEFT JOIN staff s ON a.posted_by = s.id
        WHERE a.target_audience = ? OR a.target_audience = 'all'
        ORDER BY a.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$target, $limit]);
    $announcements = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'announcements' => $announcements]);
}

function createExam() {
    global $auth;
    $auth->requireRole(['Principal', 'Exam Officer']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $db = getDB();
    
    $stmt = $db->prepare("
        INSERT INTO exams (exam_name, term, session, start_date, end_date)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        sanitize($data['exam_name']),
        sanitize($data['term']),
        sanitize($data['session'] ?? CURRENT_SESSION),
        $data['start_date'],
        $data['end_date']
    ]);
    
    jsonResponse(['success' => true, 'message' => 'Exam created successfully']);
}

function getExams() {
    global $auth;
    $auth->requireAuth();
    
    $db = getDB();
    $stmt = $db->query("SELECT * FROM exams ORDER BY start_date DESC");
    $exams = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'exams' => $exams]);
}

function getDashboardStats() {
    global $auth;
    $auth->requireAuth();
    
    // This function should be defined in functions.php
    // Calling it here assuming it exists there
    if (function_exists('getDashboardStatsData')) {
        $stats = getDashboardStatsData();
        jsonResponse(['success' => true, 'stats' => $stats]);
    } else {
        jsonResponse(['error' => 'Dashboard stats function not found'], 500);
    }
}

function getTeacherDashboard() {
    global $auth;
    $auth->requireRole(['Teacher']);
    
    $user = $auth->getCurrentUser();
    $staffId = $user['staff_id'];
    
    // Get classes taught
    $classes = getTeacherClasses($staffId);
    
    // Get subjects taught
    $subjects = getTeacherSubjects($staffId);
    
    // Get total students
    $db = getDB();
    $totalStudents = 0;
    foreach ($classes as $class) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE class = ? AND status = 'active'");
        $stmt->execute([$class]);
        $totalStudents += $stmt->fetch()['count'];
    }
    
    // Get salary status
    $monthYear = date('Y-m');
    $stmt = $db->prepare("SELECT * FROM salaries WHERE staff_id = ? AND month_year = ?");
    $stmt->execute([$staffId, $monthYear]);
    $salary = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'classes' => $classes,
        'subjects' => $subjects,
        'total_students' => $totalStudents,
        'salary_status' => $salary ? 'paid' : 'pending',
        'salary_details' => $salary
    ]);
}

function getBursarDashboard() {
    global $auth;
    $auth->requireRole(['Bursar', 'Director']);
    
    $db = getDB();
    
    // Revenue this month
    $stmt = $db->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM payments 
        WHERE strftime('%Y-%m', payment_date) = strftime('%Y-%m', 'now') 
        AND status = 'completed'
    ");
    $revenue = $stmt->fetch()['total'];
    
    // Expenses this month
    $stmt = $db->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM expenses 
        WHERE strftime('%Y-%m', expense_date) = strftime('%Y-%m', 'now')
    ");
    $expenses = $stmt->fetch()['total'];
    
    // Pending fees
    $stmt = $db->query("
        SELECT COUNT(*) as count 
        FROM students s
        LEFT JOIN (
            SELECT reg_no, SUM(amount) as paid 
            FROM payments 
            WHERE payment_type = 'School Fees' AND status = 'completed'
            GROUP BY reg_no
        ) p ON s.reg_no = p.reg_no
        WHERE s.status = 'active'
    ");
    $totalStudents = $stmt->fetch()['count'];
    
    // Recent transactions
    $stmt = $db->query("
        SELECT p.*, s.surname, s.first_name 
        FROM payments p
        LEFT JOIN students s ON p.reg_no = s.reg_no
        ORDER BY p.payment_date DESC
        LIMIT 5
    ");
    $recentTransactions = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'revenue' => $revenue,
        'expenses' => $expenses,
        'balance' => $revenue - $expenses,
        'recent_transactions' => $recentTransactions
    ]);
}

function generateReport() {
    global $auth;
    $auth->requireRole(['Bursar', 'Director', 'Principal']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $type = $data['report_type'] ?? 'income';
    $from = $data['from_date'] ?? date('Y-m-01');
    $to = $data['to_date'] ?? date('Y-m-d');
    
    $db = getDB();
    $report = [];
    
    switch ($type) {
        case 'income':
            $stmt = $db->prepare("
                SELECT payment_type, COUNT(*) as count, SUM(amount) as total
                FROM payments
                WHERE date(payment_date) BETWEEN ? AND ? AND status = 'completed'
                GROUP BY payment_type
            ");
            $stmt->execute([$from, $to]);
            $report = $stmt->fetchAll();
            break;
            
        case 'expense':
            $stmt = $db->prepare("
                SELECT category, COUNT(*) as count, SUM(amount) as total
                FROM expenses
                WHERE expense_date BETWEEN ? AND ?
                GROUP BY category
            ");
            $stmt->execute([$from, $to]);
            $report = $stmt->fetchAll();
            break;
            
        case 'fees':
            $stmt = $db->prepare("
                SELECT s.class, COUNT(*) as total_students,
                    SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as collected
                FROM students s
                LEFT JOIN payments p ON s.reg_no = p.reg_no AND p.payment_type = 'School Fees'
                WHERE s.status = 'active'
                GROUP BY s.class
            ");
            $stmt->execute();
            $report = $stmt->fetchAll();
            break;
            
        case 'salary':
            $stmt = $db->prepare("
                SELECT month_year, COUNT(*) as staff_count, SUM(net_salary) as total_paid
                FROM salaries
                WHERE payment_date BETWEEN ? AND ?
                GROUP BY month_year
            ");
            $stmt->execute([$from, $to]);
            $report = $stmt->fetchAll();
            break;
    }
    
    jsonResponse(['success' => true, 'report_type' => $type, 'from' => $from, 'to' => $to, 'data' => $report]);
}

function getMonthlySummary() {
    global $auth;
    $auth->requireRole(['Bursar', 'Director']);
    
    if (function_exists('getMonthlySummaryData')) {
        $summary = getMonthlySummaryData();
        jsonResponse(['success' => true, 'summary' => $summary]);
    } else {
        jsonResponse(['error' => 'Monthly summary function not found'], 500);
    }
}

// ==================== PLACEHOLDER FUNCTIONS ====================
// These should be implemented in functions.php or defined here

function markAttendance() {
    // Placeholder - implement or include from functions.php
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function enterGrades() {
    // Placeholder - implement or include from functions.php
    jsonResponse(['error' => 'Function not implemented'], 501);
}

// Role-specific placeholder functions
function applyForTeaching() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function createStaff() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function approveStaffApplication() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getPendingApplications() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function rejectStaffApplication() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getRoleDashboard() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getMyPermissions() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getManagementTeam() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getAuditLogs() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function approveAdmission() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getPendingAdmissions() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getDisciplineCases() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function createDisciplineCase() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getDailyAttendance() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function awardMerit() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function publishResults() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

function getResultStatistics() {
    jsonResponse(['error' => 'Function not implemented'], 501);
}

// Helper function placeholders - should be in functions.php
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('generateRegNo')) {
    function generateRegNo($type) {
        $prefix = $type === 'student' ? 'STD' : 'STF';
        return $prefix . date('Y') . rand(1000, 9999);
    }
}

if (!function_exists('generateReceiptNo')) {
    function generateReceiptNo() {
        return 'RCP' . date('Ymd') . rand(1000, 9999);
    }
}

if (!function_exists('logActivity')) {
    function logActivity($action, $description) {
        // Implement logging
        error_log("[$action] $description");
    }
}

if (!function_exists('getDB')) {
    function getDB() {
        global $db;
        return $db;
    }
}
?>