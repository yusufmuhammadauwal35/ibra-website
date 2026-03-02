<?php
/**
 * Role-Based Permission System
 */

require_once 'config.php';

class RolePermissions {
    private $roles;
    private $userRole;
    private $userLevel;

    public function __construct($userRole = null) {
        $this->roles = ROLES;
        $this->userRole = $userRole;
        $this->userLevel = $userRole ? $this->roles[$userRole]['level'] : 999;
    }
<?php
class RolePermissions {
    private $role;
    private $permissions;
    
    public function __construct($role) {
        $this->role = $role;
        $this->loadPermissions();
    }
    
    private function loadPermissions() {
        $roleHierarchy = [
            'Director' => ['create_staff', 'delete_staff', 'view_all', 'manage_finance', 'approve_admission'],
            'Principal' => ['create_staff', 'view_all', 'approve_admission', 'manage_academics'],
            'Vice Principal' => ['view_all', 'manage_discipline', 'manage_academics'],
            'Headmaster' => ['view_students', 'manage_attendance', 'award_merit'],
            'Exam Officer' => ['manage_exams', 'enter_grades', 'publish_results'],
            'Bursar' => ['manage_payments', 'process_salary', 'view_finance'],
            'Teacher' => ['enter_grades', 'view_students', 'mark_attendance'],
            'Student' => ['view_own_results', 'view_own_payments']
        ];
        
        $this->permissions = $roleHierarchy[$this->role] ?? [];
    }
    
    public function getPermissions() {
        return $this->permissions;
    }
    
    public function hasPermission($permission) {
        return in_array($permission, $this->permissions);
    }
    
    public function canCreateRole($role) {
        // Director can create all roles
        if ($this->role === 'Director') return true;
        
        // Principal can create teachers and below
        if ($this->role === 'Principal' && in_array($role, ['Teacher', 'Exam Officer', 'Bursar'])) {
            return true;
        }
        
        return false;
    }
}
?>
    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission) {
        if (!$this->userRole) return false;
        
        $permissions = $this->roles[$this->userRole]['permissions'];
        
        // Super admin check
        if (in_array('all', $permissions)) return true;
        if (in_array('all_except_director', $permissions) && $permission !== 'manage_director') return true;
        
        return in_array($permission, $permissions);
    }

    /**
     * Check if user can create specific role
     */
    public function canCreateRole($targetRole) {
        if (!$this->userRole) return false;
        
        $creators = $this->roles[$targetRole]['can_be_created_by'] ?? [];
        return in_array($this->userRole, $creators);
    }

    /**
     * Get all permissions for role
     */
    public function getPermissions() {
        return $this->roles[$this->userRole]['permissions'] ?? [];
    }

    /**
     * Get roles that current user can create
     */
    public function getCreatableRoles() {
        $creatable = [];
        foreach ($this->roles as $role => $config) {
            if ($this->canCreateRole($role)) {
                $creatable[] = $role;
            }
        }
        return $creatable;
    }

    /**
     * Check if user has higher or equal level
     */
    public function hasLevelAccess($requiredLevel) {
        return $this->userLevel <= $requiredLevel;
    }

    /**
     * Get dashboard widgets for role
     */
    public function getDashboardWidgets() {
        $widgets = [
            'Director' => [
                ['title' => 'Total Students', 'icon' => 'fa-user-graduate', 'color' => 'blue'],
                ['title' => 'Total Staff', 'icon' => 'fa-users', 'color' => 'green'],
                ['title' => 'Monthly Revenue', 'icon' => 'fa-money-bill', 'color' => 'purple'],
                ['title' => 'Pending Approvals', 'icon' => 'fa-clock', 'color' => 'orange']
            ],
            'Principal' => [
                ['title' => 'Students', 'icon' => 'fa-user-graduate', 'color' => 'blue'],
                ['title' => 'Staff', 'icon' => 'fa-users', 'color' => 'green'],
                ['title' => 'Exam Results', 'icon' => 'fa-chart-line', 'color' => 'purple'],
                ['title' => 'Attendance Rate', 'icon' => 'fa-calendar-check', 'color' => 'orange']
            ],
            'Vice_Principal' => [
                ['title' => 'My Classes', 'icon' => 'fa-chalkboard', 'color' => 'blue'],
                ['title' => 'Student Discipline', 'icon' => 'fa-gavel', 'color' => 'red'],
                ['title' => 'Academic Performance', 'icon' => 'fa-chart-bar', 'color' => 'green'],
                ['title' => 'Teacher Evaluation', 'icon' => 'fa-star', 'color' => 'yellow']
            ],
            'Headmaster' => [
                ['title' => 'Class Attendance', 'icon' => 'fa-clipboard-check', 'color' => 'blue'],
                ['title' => 'Student Behavior', 'icon' => 'fa-smile', 'color' => 'green'],
                ['title' => 'Parent Complaints', 'icon' => 'fa-comments', 'color' => 'orange'],
                ['title' => 'Merit/Demerit', 'icon' => 'fa-award', 'color' => 'purple']
            ],
            'Exam_Officer' => [
                ['title' => 'Exams Scheduled', 'icon' => 'fa-calendar', 'color' => 'blue'],
                ['title' => 'Results Processed', 'icon' => 'fa-file-alt', 'color' => 'green'],
                ['title' => 'Grades Entered', 'icon' => 'fa-pen', 'color' => 'purple'],
                ['title' => 'Transcripts', 'icon' => 'fa-graduation-cap', 'color' => 'orange']
            ],
            'Bursar' => [
                ['title' => 'Fees Collected', 'icon' => 'fa-coins', 'color' => 'green'],
                ['title' => 'Pending Payments', 'icon' => 'fa-clock', 'color' => 'orange'],
                ['title' => 'Expenses', 'icon' => 'fa-money-bill-wave', 'color' => 'red'],
                ['title' => 'Salaries Paid', 'icon' => 'fa-hand-holding-usd', 'color' => 'blue']
            ],
            'Teacher' => [
                ['title' => 'My Students', 'icon' => 'fa-users', 'color' => 'blue'],
                ['title' => 'Classes Today', 'icon' => 'fa-clock', 'color' => 'green'],
                ['title' => 'Pending Grades', 'icon' => 'fa-tasks', 'color' => 'orange'],
                ['title' => 'My Salary', 'icon' => 'fa-money-bill', 'color' => 'purple']
            ],
            'Student' => [
                ['title' => 'My Class', 'icon' => 'fa-chalkboard', 'color' => 'blue'],
                ['title' => 'Fee Balance', 'icon' => 'fa-money-bill', 'color' => 'green'],
                ['title' => 'Attendance', 'icon' => 'fa-calendar-check', 'color' => 'orange'],
                ['title' => 'Current GPA', 'icon' => 'fa-chart-line', 'color' => 'purple']
            ]
        ];

        return $widgets[$this->userRole] ?? [];
    }

    /**
     * Get menu items for role
     */
    public function getMenuItems() {
        $menus = [
            'Director' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard'],
                    ['icon' => 'fa-chart-pie', 'label' => 'Analytics', 'action' => 'analytics']
                ]],
                ['section' => 'Management', 'items' => [
                    ['icon' => 'fa-user-graduate', 'label' => 'All Students', 'action' => 'students'],
                    ['icon' => 'fa-users', 'label' => 'All Staff', 'action' => 'staff'],
                    ['icon' => 'fa-user-tie', 'label' => 'Management Team', 'action' => 'management']
                ]],
                ['section' => 'Finance', 'items' => [
                    ['icon' => 'fa-money-bill', 'label' => 'Financial Overview', 'action' => 'finance'],
                    ['icon' => 'fa-file-invoice', 'label' => 'Audit Reports', 'action' => 'audit']
                ]],
                ['section' => 'Settings', 'items' => [
                    ['icon' => 'fa-cog', 'label' => 'School Settings', 'action' => 'settings'],
                    ['icon' => 'fa-history', 'label' => 'Activity Log', 'action' => 'logs']
                ]]
            ],
            
            'Principal' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard']
                ]],
                ['section' => 'Academics', 'items' => [
                    ['icon' => 'fa-user-graduate', 'label' => 'Students', 'action' => 'students'],
                    ['icon' => 'fa-chalkboard-teacher', 'label' => 'Teachers', 'action' => 'teachers'],
                    ['icon' => 'fa-book', 'label' => 'Subjects', 'action' => 'subjects'],
                    ['icon' => 'fa-calendar', 'label' => 'Exams', 'action' => 'exams']
                ]],
                ['section' => 'Administration', 'items' => [
                    ['icon' => 'fa-users', 'label' => 'Staff Management', 'action' => 'staff'],
                    ['icon' => 'fa-chalkboard', 'label' => 'Classes', 'action' => 'classes'],
                    ['icon' => 'fa-clock', 'label' => 'Timetable', 'action' => 'timetable']
                ]],
                ['section' => 'Finance', 'items' => [
                    ['icon' => 'fa-money-bill', 'label' => 'View Finances', 'action' => 'finance']
                ]]
            ],
            
            'Vice_Principal' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard']
                ]],
                ['section' => 'Academic Oversight', 'items' => [
                    ['icon' => 'fa-user-graduate', 'label' => 'Student Records', 'action' => 'students'],
                    ['icon' => 'fa-chart-line', 'label' => 'Performance', 'action' => 'performance'],
                    ['icon' => 'fa-chalkboard-teacher', 'label' => 'Teacher Supervision', 'action' => 'teachers']
                ]],
                ['section' => 'Discipline', 'items' => [
                    ['icon' => 'fa-gavel', 'label' => 'Disciplinary Actions', 'action' => 'discipline'],
                    ['icon' => 'fa-comments', 'label' => 'Parent Complaints', 'action' => 'complaints']
                ]],
                ['section' => 'Operations', 'items' => [
                    ['icon' => 'fa-calendar', 'label' => 'Exam Coordination', 'action' => 'exams'],
                    ['icon' => 'fa-clipboard-check', 'label' => 'Attendance Review', 'action' => 'attendance']
                ]]
            ],
            
            'Headmaster' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard']
                ]],
                ['section' => 'Student Welfare', 'items' => [
                    ['icon' => 'fa-user-graduate', 'label' => 'My Students', 'action' => 'students'],
                    ['icon' => 'fa-clipboard-check', 'label' => 'Daily Attendance', 'action' => 'attendance'],
                    ['icon' => 'fa-smile', 'label' => 'Behavior Records', 'action' => 'behavior']
                ]],
                ['section' => 'Classroom', 'items' => [
                    ['icon' => 'fa-chalkboard', 'label' => 'Class Management', 'action' => 'classes'],
                    ['icon' => 'fa-clock', 'label' => 'Timetable', 'action' => 'timetable']
                ]],
                ['section' => 'Communication', 'items' => [
                    ['icon' => 'fa-bullhorn', 'label' => 'Announcements', 'action' => 'announcements'],
                    ['icon' => 'fa-envelope', 'label' => 'Parent Messages', 'action' => 'messages']
                ]]
            ],
            
            'Exam_Officer' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard']
                ]],
                ['section' => 'Examination', 'items' => [
                    ['icon' => 'fa-calendar', 'label' => 'Exam Schedule', 'action' => 'exams'],
                    ['icon' => 'fa-file-alt', 'label' => 'Question Papers', 'action' => 'papers'],
                    ['icon' => 'fa-pen', 'label' => 'Grade Entry', 'action' => 'grades']
                ]],
                ['section' => 'Results', 'items' => [
                    ['icon' => 'fa-chart-bar', 'label' => 'Process Results', 'action' => 'process_results'],
                    ['icon' => 'fa-print', 'label' => 'Print Reports', 'action' => 'print'],
                    ['icon' => 'fa-graduation-cap', 'label' => 'Transcripts', 'action' => 'transcripts']
                ]]
            ],
            
            'Bursar' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard']
                ]],
                ['section' => 'Student Fees', 'items' => [
                    ['icon' => 'fa-coins', 'label' => 'Fee Collection', 'action' => 'fees'],
                    ['icon' => 'fa-clock', 'label' => 'Pending Payments', 'action' => 'pending'],
                    ['icon' => 'fa-history', 'label' => 'Payment History', 'action' => 'history']
                ]],
                ['section' => 'Staff Payments', 'items' => [
                    ['icon' => 'fa-hand-holding-usd', 'label' => 'Process Salaries', 'action' => 'salaries'],
                    ['icon' => 'fa-money-check', 'label' => 'Salary History', 'action' => 'salary_history']
                ]],
                ['section' => 'Accounting', 'items' => [
                    ['icon' => 'fa-money-bill-wave', 'label' => 'Expenses', 'action' => 'expenses'],
                    ['icon' => 'fa-file-invoice', 'label' => 'Financial Reports', 'action' => 'reports']
                ]]
            ],
            
            'Teacher' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard']
                ]],
                ['section' => 'My Work', 'items' => [
                    ['icon' => 'fa-users', 'label' => 'My Students', 'action' => 'students'],
                    ['icon' => 'fa-chalkboard', 'label' => 'My Classes', 'action' => 'classes'],
                    ['icon' => 'fa-clipboard-check', 'label' => 'Attendance', 'action' => 'attendance']
                ]],
                ['section' => 'Academics', 'items' => [
                    ['icon' => 'fa-pen', 'label' => 'Enter Grades', 'action' => 'grades'],
                    ['icon' => 'fa-file-alt', 'label' => 'View Results', 'action' => 'results']
                ]],
                ['section' => 'Personal', 'items' => [
                    ['icon' => 'fa-money-bill', 'label' => 'My Salary', 'action' => 'salary'],
                    ['icon' => 'fa-user', 'label' => 'My Profile', 'action' => 'profile']
                ]]
            ],
            
            'Student' => [
                ['section' => 'Overview', 'items' => [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'action' => 'dashboard']
                ]],
                ['section' => 'Academics', 'items' => [
                    ['icon' => 'fa-chart-line', 'label' => 'My Results', 'action' => 'results'],
                    ['icon' => 'fa-calendar', 'label' => 'Timetable', 'action' => 'timetable'],
                    ['icon' => 'fa-book', 'label' => 'Subjects', 'action' => 'subjects']
                ]],
                ['section' => 'Finance', 'items' => [
                    ['icon' => 'fa-money-bill', 'label' => 'Fee Payments', 'action' => 'payments'],
                    ['icon' => 'fa-receipt', 'label' => 'Receipts', 'action' => 'receipts']
                ]],
                ['section' => 'Personal', 'items' => [
                    ['icon' => 'fa-user', 'label' => 'My Profile', 'action' => 'profile'],
                    ['icon' => 'fa-cog', 'label' => 'Settings', 'action' => 'settings']
                ]]
            ]
        ];

        return $menus[$this->userRole] ?? [];
    }
}
?>