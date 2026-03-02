<?php
/**
 * Authentication with Role Management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database.php';
require_once 'function.php';
require_once 'role-permission.php';

class Auth {
    private $db;
    private $permissions;

    public function __construct() {
        $this->db = getDB();
        if (!$this->db instanceof PDO) {
            throw new Exception('Database connection failed');
        }
    }

    public function login($id, $password, $type = 'student') {
        try {
            if ($type === 'student') {
                $stmt = $this->db->prepare("SELECT * FROM students WHERE reg_no = ? AND status IN ('active', 'pending')");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    return ['success' => false, 'message' => 'Invalid credentials or account not activated'];
                }

                if (!password_verify($password, $user['password'])) {
                    return ['success' => false, 'message' => 'Invalid credentials'];
                }

                // Check if student has paid registration fee
                if ($user['status'] === 'pending') {
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) as count FROM payments 
                        WHERE reg_no = ? AND payment_type = 'Registration Fee' AND status = 'completed'
                    ");
                    $stmt->execute([$id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$result || $result['count'] == 0) {
                        return ['success' => false, 'message' => 'Registration fee payment required. Please complete payment to activate your account.'];
                    }
                    
                    // Activate student
                    $stmt = $this->db->prepare("UPDATE students SET status = 'active', confirmed_at = datetime('now') WHERE reg_no = ?");
                    $stmt->execute([$id]);
                    $user['status'] = 'active';
                }

                unset($user['password']);
                $_SESSION['user'] = $user;
                $_SESSION['user_type'] = 'student';
                $_SESSION['user_role'] = 'Student';
                
                $this->permissions = new RolePermissions('Student');
                
                logActivity('login', "Student {$id} logged in");
                return [
                    'success' => true,
                    'user' => $user,
                    'type' => 'student',
                    'role' => 'Student',
                    'permissions' => $this->permissions->getPermissions()
                ];
            } else {
                // Staff login
                $stmt = $this->db->prepare("SELECT * FROM staff WHERE staff_id = ? AND status = 'active'");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    return ['success' => false, 'message' => 'Invalid credentials or account not activated'];
                }

                if (!password_verify($password, $user['password'])) {
                    return ['success' => false, 'message' => 'Invalid credentials'];
                }

                unset($user['password']);
                $_SESSION['user'] = $user;
                $_SESSION['user_type'] = 'staff';
                $_SESSION['user_role'] = $user['role'];
                
                $this->permissions = new RolePermissions($user['role']);
                
                // Update last login
                $stmt = $this->db->prepare("UPDATE staff SET last_login = datetime('now') WHERE staff_id = ?");
                $stmt->execute([$id]);
                
                logActivity('login', "{$user['role']} {$id} logged in");
                return [
                    'success' => true,
                    'user' => $user,
                    'type' => 'staff',
                    'role' => $user['role'],
                    'permissions' => $this->permissions->getPermissions()
                ];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    /**
     * Register new student (self-registration)
     */
    public function registerStudent($data) {
        // Generate student ID
        $regNo = generateID('student');
        $password = password_hash($regNo, PASSWORD_DEFAULT); // Default password is the ID
        
        try {
            // FIXED: Changed $this->pdo to $this->db
            $stmt = $this->db->prepare("
                INSERT INTO students (
                    reg_no, surname, first_name, other_names, date_of_birth, gender, class,
                    parent_name, parent_phone, parent_email, address, password, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
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
            
            logActivity('student_registered', "New student registered: {$regNo}");
            
            return [
                'success' => true,
                'reg_no' => $regNo,
                'message' => 'Registration successful. Please pay the registration fee to activate your account.'
            ];
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * Apply for teaching position (pending approval)
     */
    public function applyForTeaching($data) {
        // Validate required fields
        if (empty($data['password'])) {
            return ['success' => false, 'message' => 'Password is required'];
        }

        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO pending_staff (
                    name, email, phone, role, department, proposed_salary, password
                ) VALUES (?, ?, ?, 'Teacher', ?, ?, ?)
            ");
            
            $stmt->execute([
                sanitize($data['name']),
                sanitize($data['email']),
                sanitize($data['phone']),
                sanitize($data['department']),
                $data['proposed_salary'] ?? 60000,
                $password
            ]);
            
            logActivity('teacher_application', "New teacher application: {$data['email']}");
            
            return [
                'success' => true,
                'message' => 'Application submitted successfully. The Principal will review your application.'
            ];
            
        } catch (PDOException $e) {
            error_log("Application error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Application failed: ' . $e->getMessage()];
        }
    }

    /**
     * Create staff by authorized user
     */
    public function createStaff($data, $createdBy) {
        // Validate required fields
        if (empty($data['role']) || empty($data['name']) || empty($data['email'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        // Check if creator has permission
        $creatorRole = $_SESSION['user_role'] ?? null;
        if (!$creatorRole) {
            return ['success' => false, 'message' => 'Creator role not found in session'];
        }

        $permissions = new RolePermissions($creatorRole);
        
        if (!$permissions->canCreateRole($data['role'])) {
            return ['success' => false, 'message' => 'You do not have permission to create this role'];
        }
        
        // Generate ID with start year
        $startYear = $data['start_year'] ?? date('Y');
        $staffId = generateID($data['role'], $startYear);
        $password = password_hash($data['password'] ?? 'password123', PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO staff (
                    staff_id, name, email, phone, role, department, 
                    basic_salary, start_year, password, created_by, hire_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $staffId,
                sanitize($data['name']),
                sanitize($data['email']),
                sanitize($data['phone']),
                sanitize($data['role']),
                sanitize($data['department']),
                $data['basic_salary'] ?? 0,
                $startYear,
                $password,
                $createdBy,
                $data['hire_date'] ?? date('Y-m-d')
            ]);
            
            logActivity('staff_created', "Staff {$staffId} ({$data['role']}) created by {$createdBy}");
            
            return [
                'success' => true,
                'staff_id' => $staffId,
                'message' => "{$data['role']} created successfully with ID: {$staffId}"
            ];
            
        } catch (PDOException $e) {
            error_log("Staff creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Creation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Approve pending staff application
     */
    public function approveStaffApplication($applicationId, $approvedBy, $startYear = null) {
        try {
            // Get application
            $stmt = $this->db->prepare("SELECT * FROM pending_staff WHERE id = ? AND status = 'pending'");
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$application) {
                return ['success' => false, 'message' => 'Application not found or already processed'];
            }
            
            // Generate ID
            $startYear = $startYear ?? date('Y');
            $staffId = generateID($application['role'], $startYear);
            
            // Create staff record
            $stmt = $this->db->prepare("
                INSERT INTO staff (
                    staff_id, name, email, phone, role, department,
                    basic_salary, start_year, password, created_by, hire_date
                ) SELECT ?, name, email, phone, role, department, proposed_salary, ?, password, ?, date('now')
                FROM pending_staff WHERE id = ?
            ");
            
            $stmt->execute([$staffId, $startYear, $approvedBy, $applicationId]);
            
            // Update application
            $stmt = $this->db->prepare("
                UPDATE pending_staff SET status = 'approved', reviewed_by = ?, reviewed_at = datetime('now')
                WHERE id = ?
            ");
            $stmt->execute([$approvedBy, $applicationId]);
            
            // Send email to new staff (if function exists)
            if (function_exists('sendEmail')) {
                sendEmail(
                    $application['email'],
                    'Welcome to Ibra Foundation School Mirnga',
                    "Your application has been approved. Your Staff ID is: {$staffId}. Please login to complete your profile."
                );
            }
            
            logActivity('staff_approved', "Staff {$staffId} approved by {$approvedBy}");
            
            return [
                'success' => true,
                'staff_id' => $staffId,
                'message' => "Application approved. Staff ID: {$staffId}"
            ];
            
        } catch (PDOException $e) {
            error_log("Approval error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()];
        }
    }

    public function logout() {
        // Clear all session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    public function isLoggedIn() {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    public function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }

    public function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
    }

    public function requirePermission($permission) {
        $this->requireAuth();
        $role = $this->getUserRole();
        
        if (!$role) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Role not found']);
            exit;
        }

        $permissions = new RolePermissions($role);
        
        if (!$permissions->hasPermission($permission)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Insufficient permissions for this action']);
            exit;
        }
    }

    public function requireRole($roles) {
        $this->requireAuth();
        $userRole = $this->getUserRole();
        
        if (!in_array($userRole, (array)$roles)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'This action requires specific authorization']);
            exit;
        }
    }

    public function changePassword($userId, $oldPassword, $newPassword, $type = 'student') {
        try {
            if ($type === 'student') {
                $stmt = $this->db->prepare("SELECT password FROM students WHERE reg_no = ?");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->prepare("SELECT password FROM staff WHERE staff_id = ?");
                $stmt->execute([$userId]);
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid old password'];
            }

            // Validate new password
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'New password must be at least 6 characters'];
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            if ($type === 'student') {
                $stmt = $this->db->prepare("UPDATE students SET password = ? WHERE reg_no = ?");
            } else {
                $stmt = $this->db->prepare("UPDATE staff SET password = ? WHERE staff_id = ?");
            }

            $stmt->execute([$hashedPassword, $userId]);
            
            logActivity('password_changed', "Password changed for {$type} {$userId}");
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }

    /**
     * Get user permissions
     */
    public function getPermissions() {
        $role = $this->getUserRole();
        if (!$role) {
            return [];
        }
        
        if (!$this->permissions) {
            $this->permissions = new RolePermissions($role);
        }
        
        return $this->permissions->getPermissions();
    }

    /**
     * Check if current user has a specific permission
     */
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $this->getUserRole();
        if (!$role) {
            return false;
        }
        
        $permissions = new RolePermissions($role);
        return $permissions->hasPermission($permission);
    }

    /**
     * Refresh session data from database
     */
    public function refreshSession() {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user = $this->getCurrentUser();
        $type = $this->getUserType();

        try {
            if ($type === 'student') {
                $stmt = $this->db->prepare("SELECT * FROM students WHERE reg_no = ?");
                $stmt->execute([$user['reg_no']]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM staff WHERE staff_id = ?");
                $stmt->execute([$user['staff_id']]);
            }

            $freshData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($freshData) {
                unset($freshData['password']);
                $_SESSION['user'] = $freshData;
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Session refresh error: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize auth instance
try {
    $auth = new Auth();
} catch (Exception $e) {
    error_log("Auth initialization failed: " . $e->getMessage());
    // Return error response if this is an API request
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false || 
        strpos($_SERVER['PHP_SELF'], 'api.php') !== false) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Authentication system unavailable']);
        exit;
    }
}
?>