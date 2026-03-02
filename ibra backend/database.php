<?php
/**
 * Database Connection and Initialization
 */

require_once 'config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $this->pdo = new PDO('sqlite:' . DB_PATH);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initializeTables();
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function initializeTables() {
        // Students table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reg_no TEXT UNIQUE NOT NULL,
                surname TEXT NOT NULL,
                first_name TEXT NOT NULL,
                other_names TEXT,
                date_of_birth DATE,
                gender TEXT,
                class TEXT NOT NULL,
                parent_name TEXT,
                parent_phone TEXT,
                parent_email TEXT,
                address TEXT,
                password TEXT NOT NULL,
                admission_date DATE DEFAULT CURRENT_DATE,
                status TEXT DEFAULT 'pending', -- pending, active, suspended, graduated, expelled
                confirmed_at TIMESTAMP,
                created_by TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Staff table with proper roles
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS staff (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                staff_id TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                email TEXT UNIQUE,
                phone TEXT,
                role TEXT NOT NULL, -- Director, Principal, Vice_Principal, Headmaster, Exam_Officer, Bursar, Teacher
                department TEXT,
                basic_salary REAL DEFAULT 0,
                allowances REAL DEFAULT 0,
                deductions REAL DEFAULT 0,
                password TEXT NOT NULL,
                hire_date DATE DEFAULT CURRENT_DATE,
                start_year TEXT, -- For ID generation (e.g., 2019)
                status TEXT DEFAULT 'active', -- active, suspended, terminated, on_leave
                created_by TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP
            )
        ");

        // Staff bank details for salary
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS staff_bank_details (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                staff_id TEXT NOT NULL,
                bank_name TEXT,
                bank_code TEXT,
                account_number TEXT,
                account_name TEXT,
                is_verified INTEGER DEFAULT 0,
                verified_at TIMESTAMP,
                FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
            )
        ");

        // Pending staff registrations (for approval)
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS pending_staff (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT,
                role TEXT NOT NULL,
                department TEXT,
                proposed_salary REAL,
                password TEXT NOT NULL,
                application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status TEXT DEFAULT 'pending', -- pending, approved, rejected
                reviewed_by TEXT,
                reviewed_at TIMESTAMP,
                rejection_reason TEXT
            )
        ");

        // Payments table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                payment_type TEXT NOT NULL,
                reg_no TEXT,
                staff_id TEXT,
                amount REAL NOT NULL,
                description TEXT,
                payment_method TEXT,
                receipt_no TEXT UNIQUE,
                status TEXT DEFAULT 'completed',
                gateway_reference TEXT,
                transaction_id TEXT,
                payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                recorded_by INTEGER,
                FOREIGN KEY (reg_no) REFERENCES students(reg_no),
                FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
            )
        ");

        // Paystack payments
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS paystack_payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reference TEXT UNIQUE NOT NULL,
                reg_no TEXT,
                staff_id TEXT,
                payment_type TEXT NOT NULL,
                amount REAL NOT NULL,
                email TEXT,
                access_code TEXT,
                transaction_url TEXT,
                transaction_id TEXT,
                status TEXT DEFAULT 'pending',
                gateway_response TEXT,
                paid_at TEXT,
                channel TEXT,
                card_type TEXT,
                last4 TEXT,
                bank TEXT,
                verified_at TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Salaries
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS salaries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                staff_id TEXT NOT NULL,
                month_year TEXT NOT NULL,
                basic_salary REAL NOT NULL,
                allowances REAL DEFAULT 0,
                deductions REAL DEFAULT 0,
                net_salary REAL NOT NULL,
                payment_date TIMESTAMP,
                status TEXT DEFAULT 'pending',
                reference TEXT,
                transfer_code TEXT,
                recipient_code TEXT,
                failure_reason TEXT,
                paid_by INTEGER,
                FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
            )
        ");

        // Expenses
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS expenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category TEXT NOT NULL,
                description TEXT,
                amount REAL NOT NULL,
                expense_date DATE,
                recorded_by INTEGER,
                approved_by TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Attendance
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS attendance (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reg_no TEXT NOT NULL,
                class TEXT NOT NULL,
                attendance_date DATE NOT NULL,
                status TEXT NOT NULL, -- present, absent, late, excused
                remarks TEXT,
                marked_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(reg_no, attendance_date)
            )
        ");

        // Grades
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS grades (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reg_no TEXT NOT NULL,
                class TEXT NOT NULL,
                subject TEXT NOT NULL,
                term TEXT NOT NULL,
                session TEXT NOT NULL,
                ca_score REAL DEFAULT 0,
                exam_score REAL DEFAULT 0,
                total_score REAL,
                grade TEXT,
                remark TEXT,
                entered_by INTEGER,
                verified_by TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(reg_no, subject, term, session)
            )
        ");

        // Classes
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS classes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                class_name TEXT UNIQUE NOT NULL,
                section TEXT, -- Nursery, Primary, Junior Secondary, Senior Secondary
                class_teacher_id TEXT,
                capacity INTEGER DEFAULT 40,
                room TEXT,
                FOREIGN KEY (class_teacher_id) REFERENCES staff(staff_id)
            )
        ");

        // Subjects
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS subjects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                subject_name TEXT NOT NULL,
                class TEXT,
                teacher_id TEXT,
                FOREIGN KEY (teacher_id) REFERENCES staff(staff_id)
            )
        ");

        // Timetable
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS timetable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                class TEXT NOT NULL,
                day_of_week TEXT NOT NULL,
                time_slot TEXT NOT NULL,
                subject TEXT,
                teacher_id TEXT,
                room TEXT,
                FOREIGN KEY (teacher_id) REFERENCES staff(staff_id)
            )
        ");

        // Announcements
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS announcements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                message TEXT NOT NULL,
                target_audience TEXT DEFAULT 'all', -- all, students, parents, staff, specific_roles
                specific_roles TEXT, -- JSON array if targeting specific roles
                posted_by INTEGER,
                priority TEXT DEFAULT 'normal', -- low, normal, high, urgent
                expires_at TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Exams
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS exams (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                exam_name TEXT NOT NULL,
                term TEXT NOT NULL,
                session TEXT NOT NULL,
                class TEXT,
                subject TEXT,
                exam_date DATE,
                start_time TIME,
                duration INTEGER, -- in minutes
                max_marks REAL,
                exam_type TEXT DEFAULT 'theory', -- theory, practical, objective, mixed
                status TEXT DEFAULT 'upcoming', -- upcoming, ongoing, completed, cancelled
                created_by TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Discipline/Behavior records
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS discipline_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reg_no TEXT NOT NULL,
                incident_date DATE,
                incident_type TEXT, -- misconduct, absenteeism, fighting, cheating, etc.
                description TEXT,
                action_taken TEXT, -- warning, suspension, expulsion, community_service
                action_by TEXT,
                parent_notified INTEGER DEFAULT 0,
                parent_response TEXT,
                resolved INTEGER DEFAULT 0,
                resolution_date DATE,
                FOREIGN KEY (reg_no) REFERENCES students(reg_no),
                FOREIGN KEY (action_by) REFERENCES staff(staff_id)
            )
        ");

        // Merit/Award records
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS merit_awards (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reg_no TEXT NOT NULL,
                award_date DATE,
                award_type TEXT, -- academic, sports, behavior, leadership, special
                description TEXT,
                points INTEGER DEFAULT 0,
                awarded_by TEXT,
                FOREIGN KEY (reg_no) REFERENCES students(reg_no),
                FOREIGN KEY (awarded_by) REFERENCES staff(staff_id)
            )
        ");

        // ID sequences
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS id_sequences (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type TEXT NOT NULL,
                year TEXT NOT NULL,
                last_number INTEGER DEFAULT 0,
                UNIQUE(type, year)
            )
        ");

        // Activity log
        $this->pdo->exec("
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

        // Insert default staff if not exists
        $this->insertDefaultData();
    }

    private function insertDefaultData() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM staff");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Default Director (2009 - founding year)
            $directorId = generateID('Director', '2009');
            $stmt = $this->pdo->prepare("
                INSERT INTO staff (staff_id, name, email, phone, role, department, basic_salary, start_year, password)
                VALUES (?, 'Alhaji Ibrahim', 'director@ifsm.edu.ng', '08011111111', 'Director', 'Administration', 500000, '2009', ?)
            ");
            $stmt->execute([$directorId, password_hash('dir123', PASSWORD_DEFAULT)]);

            // Default Principal (2015)
            $principalId = generateID('Principal', '2015');
            $stmt = $this->pdo->prepare("
                INSERT INTO staff (staff_id, name, email, phone, role, department, basic_salary, start_year, password)
                VALUES (?, 'Dr. Mrs. Abdullahi', 'principal@ifsm.edu.ng', '08022222222', 'Principal', 'Administration', 300000, '2015', ?)
            ");
            $stmt->execute([$principalId, password_hash('prn123', PASSWORD_DEFAULT)]);

            // Default Bursar (2010)
            $bursarId = generateID('Bursar', '2010');
            $stmt = $this->pdo->prepare("
                INSERT INTO staff (staff_id, name, email, phone, role, department, basic_salary, start_year, password)
                VALUES (?, 'Mr. Accountant', 'bursar@ifsm.edu.ng', '08033333333', 'Bursar', 'Finance', 150000, '2010', ?)
            ");
            $stmt->execute([$bursarId, password_hash('bur123', PASSWORD_DEFAULT)]);

            // Insert default classes
            $classes = [
                ['Nursery 1', 'Nursery'],
                ['Nursery 2', 'Nursery'],
                ['Primary 1', 'Primary'],
                ['Primary 2', 'Primary'],
                ['Primary 3', 'Primary'],
                ['Primary 4', 'Primary'],
                ['Primary 5', 'Primary'],
                ['Primary 6', 'Primary'],
                ['JSS 1', 'Junior Secondary'],
                ['JSS 2', 'Junior Secondary'],
                ['JSS 3', 'Junior Secondary'],
                ['SS 1', 'Senior Secondary'],
                ['SS 2', 'Senior Secondary'],
                ['SS 3', 'Senior Secondary'],
            ];

            $stmt = $this->pdo->prepare("INSERT INTO classes (class_name, section) VALUES (?, ?)");
            foreach ($classes as $class) {
                $stmt->execute($class);
            }
        }
    }
}

// Helper function to get DB connection
function getDB() {
    return Database::getInstance()->getConnection();
}
?>