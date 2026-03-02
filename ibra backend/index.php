<?php
/**
 * Ibra Foundation School Management System
 * Main Entry Point - Serves the Frontend
 */

// If API request, route to api.php
if (isset($_GET['api']) || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    require_once 'api.php';
    exit;
}

// Serve the HTML frontend
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ibra Foundation School Mirnga - Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Paystack Inline JS -->
<script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#059669',
                        secondary: '#047857',
                        accent: '#10b981',
                        light: '#f0fdf4',
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); min-height: 100vh; }
        .page { display: none; animation: fadeIn 0.5s ease-in; }
        .page.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .sidebar { background: linear-gradient(180deg, #059669 0%, #047857 100%); min-height: 100vh; transition: all 0.3s ease; }
        .nav-item { transition: all 0.3s ease; cursor: pointer; }
        .nav-item:hover { background: rgba(255,255,255,0.2); transform: translateX(5px); }
        .card { background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(5, 150, 105, 0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(5, 150, 105, 0.2); }
        .btn-primary { background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.3s ease; }
        .btn-primary:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(5, 150, 105, 0.4); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-secondary { background: white; color: #059669; border: 2px solid #059669; padding: 10px 20px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
        .btn-secondary:hover { background: #059669; color: white; }
        .input-field { border: 2px solid #e5e7eb; border-radius: 8px; padding: 12px; width: 100%; transition: all 0.3s ease; }
        .input-field:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1); }
        .stat-card { background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border-left: 4px solid #059669; }
        .notification { position: fixed; top: 20px; right: 20px; background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); border-left: 4px solid #059669; transform: translateX(400px); transition: transform 0.3s ease; z-index: 2000; max-width: 350px; }
        .notification.show { transform: translateX(0); }
        .notification.error { border-left-color: #dc2626; }
        .notification.warning { border-left-color: #d97706; }
        .tab-btn { padding: 10px 20px; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.3s ease; }
        .tab-btn.active { color: #059669; border-bottom-color: #059669; }
        .dashboard-grid { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .login-container { background: linear-gradient(135deg, #059669 0%, #047857 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .login-box { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 450px; }
        .logo { width: 80px; height: 80px; background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 2em; }
        .progress-bar { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #059669 0%, #10b981 100%); transition: width 0.5s ease; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        .loading { display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #059669; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @media (max-width: 768px) { .dashboard-grid { grid-template-columns: 1fr; } .sidebar { display: none; } }
        .empty-state { text-align: center; padding: 40px; color: #9ca3af; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <!-- Notification Container -->
    <div id="notification" class="notification">
        <i class="fas fa-check-circle text-green-600 mr-2" id="notificationIcon"></i>
        <span id="notificationText">Operation successful!</span>
    </div>

    <!-- PAGE 1: LANDING/HOME PAGE -->
    <div id="page-home" class="page active">
        <nav style="background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: fixed; width: 100%; z-index: 100;">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center cursor-pointer" onclick="showPage('home')">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-full flex items-center justify-center text-white font-bold text-xl mr-3">IF</div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Ibra Foundation School</h1>
                        <p class="text-sm text-green-600">Mirnga</p>
                    </div>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="#" onclick="showPage('home')" class="text-gray-700 hover:text-green-600 font-medium">Home</a>
                    <a href="#" onclick="showPage('about')" class="text-gray-700 hover:text-green-600 font-medium">About</a>
                    <a href="#" onclick="showPage('admission')" class="text-gray-700 hover:text-green-600 font-medium">Admission</a>
                    <a href="#" onclick="showPage('login')" class="text-gray-700 hover:text-green-600 font-medium">Portal Login</a>
                </div>
                <button onclick="showPage('login')" class="btn-primary"><i class="fas fa-sign-in-alt mr-2"></i>Login</button>
            </div>
        </nav>

        <div class="pt-20">
            <div class="container mx-auto px-6 py-20">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 mb-10 md:mb-0">
                        <h1 class="text-5xl font-bold text-gray-800 mb-6 leading-tight">Excellence in <span class="text-green-600">Education</span></h1>
                        <p class="text-xl text-gray-600 mb-8">Nurturing minds from Nursery 1 to SS3. Building tomorrow's leaders through quality education and moral values at Ibra Foundation School Mirnga.</p>
                        <div class="flex space-x-4">
                            <button onclick="showPage('admission')" class="btn-primary text-lg"><i class="fas fa-user-plus mr-2"></i>Apply for Admission</button>
                            <button onclick="showPage('login')" class="btn-secondary text-lg"><i class="fas fa-user mr-2"></i>Student Portal</button>
                        </div>
                    </div>
                    <div class="md:w-1/2 flex justify-center">
                        <div class="relative">
                            <div class="w-96 h-96 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-9xl text-green-600"></i>
                            </div>
                            <div class="absolute -bottom-4 -right-4 bg-white p-4 rounded-xl shadow-lg">
                                <p class="text-2xl font-bold text-green-600">15+</p>
                                <p class="text-sm text-gray-600">Years of Excellence</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white py-20">
                <div class="container mx-auto px-6">
                    <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Our Academic Structure</h2>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="card p-8 text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-child text-2xl text-green-600"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Nursery Section</h3>
                            <p class="text-gray-600">Nursery 1 & 2 - Foundation years for early childhood development</p>
                        </div>
                        <div class="card p-8 text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-book-reader text-2xl text-green-600"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Primary Section</h3>
                            <p class="text-gray-600">Primary 1-6 - Building strong academic foundations</p>
                        </div>
                        <div class="card p-8 text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-school text-2xl text-green-600"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Secondary Section</h3>
                            <p class="text-gray-600">JSS 1-3 & SS 1-3 - Preparing for higher education</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-600 to-green-700 py-16 text-white">
                <div class="container mx-auto px-6">
                    <div class="grid md:grid-cols-4 gap-8 text-center">
                        <div>
                            <p class="text-4xl font-bold mb-2" id="statStudents">0</p>
                            <p>Students Enrolled</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold mb-2" id="statStaff">0</p>
                            <p>Qualified Staff</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold mb-2">95%</p>
                            <p>Exam Success Rate</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold mb-2">15+</p>
                            <p>Years Experience</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 2: LOGIN PAGE -->
    <div id="page-login" class="page">
        <div class="login-container">
            <div class="login-box">
                <div class="logo"><i class="fas fa-school"></i></div>
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Welcome Back</h2>
                <p class="text-center text-gray-600 mb-6">Ibra Foundation School Mirnga</p>
                
                <div class="mb-6">
                    <div class="flex border-b border-gray-200">
                        <button class="tab-btn active flex-1" onclick="switchLoginTab('student')">Student</button>
                        <button class="tab-btn flex-1" onclick="switchLoginTab('staff')">Staff</button>
                    </div>
                </div>

                <form id="loginForm" onsubmit="handleLogin(event)">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Registration Number</label>
                        <div class="relative">
                            <i class="fas fa-id-card absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" id="loginRegNo" class="input-field pl-10" placeholder="Enter Reg. No (e.g., STD2024001)" required>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                            <input type="password" id="loginPassword" class="input-field pl-10" placeholder="Enter password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full text-lg" id="loginBtn">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-600">New Student? <a href="#" onclick="showPage('admission')" class="text-green-600 font-semibold hover:underline">Apply for Admission</a></p>
                </div>
                <div class="mt-4 text-center">
                    <button onclick="showPage('home')" class="text-gray-500 hover:text-green-600"><i class="fas fa-arrow-left mr-1"></i>Back to Home</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 3: ADMISSION -->
    <div id="page-admission" class="page">
        <div class="min-h-screen bg-gray-50">
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white py-8">
                <div class="container mx-auto px-6">
                    <button onclick="showPage('home')" class="mb-4 text-white hover:text-green-200"><i class="fas fa-arrow-left mr-2"></i>Back to Home</button>
                    <h1 class="text-3xl font-bold">Student Admission</h1>
                    <p class="mt-2">Apply for admission to Ibra Foundation School Mirnga</p>
                </div>
            </div>

            <div class="container mx-auto px-6 py-10">
                <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8">
                    <div id="admissionStep1">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">Student Registration Form</h2>
                        <form id="admissionForm" onsubmit="submitAdmission(event)">
                            <div class="grid md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-gray-700 mb-2">Surname *</label>
                                    <input type="text" id="admSurname" class="input-field" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">First Name *</label>
                                    <input type="text" id="admFirstName" class="input-field" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Other Names</label>
                                    <input type="text" id="admOtherNames" class="input-field">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Date of Birth *</label>
                                    <input type="date" id="admDOB" class="input-field" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Gender *</label>
                                    <select id="admGender" class="input-field" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Class Applying For *</label>
                                    <select id="admClass" class="input-field" required>
                                        <option value="">Select Class</option>
                                        <option value="Nursery 1">Nursery 1</option>
                                        <option value="Nursery 2">Nursery 2</option>
                                        <option value="Primary 1">Primary 1</option>
                                        <option value="Primary 2">Primary 2</option>
                                        <option value="Primary 3">Primary 3</option>
                                        <option value="Primary 4">Primary 4</option>
                                        <option value="Primary 5">Primary 5</option>
                                        <option value="Primary 6">Primary 6</option>
                                        <option value="JSS 1">JSS 1</option>
                                        <option value="JSS 2">JSS 2</option>
                                        <option value="JSS 3">JSS 3</option>
                                        <option value="SS 1">SS 1</option>
                                        <option value="SS 2">SS 2</option>
                                        <option value="SS 3">SS 3</option>
                                    </select>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold mb-4 mt-8 text-gray-800">Parent/Guardian Information</h3>
                            <div class="grid md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-gray-700 mb-2">Parent/Guardian Name *</label>
                                    <input type="text" id="admParentName" class="input-field" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Phone Number *</label>
                                    <input type="tel" id="admParentPhone" class="input-field" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Email Address</label>
                                    <input type="email" id="admParentEmail" class="input-field">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Address *</label>
                                    <input type="text" id="admAddress" class="input-field" required>
                                </div>
                            </div>

                            <div class="bg-green-50 p-4 rounded-lg mb-6">
                                <p class="text-green-800 font-semibold"><i class="fas fa-info-circle mr-2"></i>Registration Fee: ₦5,500</p>
                                <p class="text-sm text-green-700 mt-1">Payment required to complete registration and generate Reg. No.</p>
                            </div>

                            <button type="submit" class="btn-primary w-full text-lg" id="admSubmitBtn"><i class="fas fa-arrow-right mr-2"></i>Proceed to Payment</button>
                        </form>
                    </div>

                    <div id="admissionStep2" style="display: none;">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">Payment Details</h2>
                        <div class="bg-gray-50 p-6 rounded-xl mb-6">
                            <div class="flex justify-between mb-3"><span>Registration Fee</span><span class="font-bold">₦5,000.00</span></div>
                            <div class="flex justify-between mb-3"><span>Processing Fee</span><span class="font-bold">₦500.00</span></div>
                            <hr class="my-3">
                            <div class="flex justify-between text-lg font-bold text-green-600"><span>Total Amount</span><span>₦5,500.00</span></div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 mb-2">Bank Account Details</label>
                            <div class="bg-white border-2 border-dashed border-green-300 p-4 rounded-lg">
                                <p><strong>Bank:</strong> First Bank of Nigeria</p>
                                <p><strong>Account Name:</strong> Ibra Foundation School Mirnga</p>
                                <p><strong>Account Number:</strong> 0123456789</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 mb-2">Upload Payment Receipt *</label>
                            <input type="file" id="paymentReceipt" class="input-field" accept="image/*,.pdf" required>
                            <p class="text-sm text-gray-500 mt-1">For demo purposes, any file will be accepted</p>
                        </div>

                        <button onclick="completeAdmission()" class="btn-primary w-full text-lg" id="completeAdmBtn"><i class="fas fa-check mr-2"></i>Complete Registration</button>
                    </div>

                    <div id="admissionSuccess" style="display: none;" class="text-center py-10">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-4xl text-green-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Registration Successful!</h2>
                        <p class="text-gray-600 mb-4">Your registration number has been generated</p>
                        <div class="bg-green-50 p-4 rounded-lg mb-6 inline-block">
                            <p class="text-sm text-gray-600">Your Registration Number:</p>
                            <p class="text-2xl font-bold text-green-600" id="generatedRegNo">-</p>
                        </div>
                        <p class="text-gray-600 mb-6">Use this number to login to your student portal</p>
                        <button onclick="showPage('login')" class="btn-primary"><i class="fas fa-sign-in-alt mr-2"></i>Login to Portal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 4: STUDENT DASHBOARD -->
    <div id="page-student-dashboard" class="page">
        <div class="dashboard-grid">
            <div class="sidebar text-white p-6">
                <div class="mb-8 text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-graduate text-4xl text-green-600"></i>
                    </div>
                    <h3 class="font-bold text-lg">Student Portal</h3>
                    <p class="text-sm opacity-80" id="studentNameDisplay">Welcome</p>
                </div>
                <nav>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showStudentSection('overview')"><i class="fas fa-home w-6"></i><span>Dashboard</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showStudentSection('profile')"><i class="fas fa-user w-6"></i><span>My Profile</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showStudentSection('payments')"><i class="fas fa-money-bill w-6"></i><span>Fee Payments</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showStudentSection('results')"><i class="fas fa-chart-line w-6"></i><span>Results</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showStudentSection('timetable')"><i class="fas fa-calendar w-6"></i><span>Timetable</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center bg-white bg-opacity-20" onclick="logout()"><i class="fas fa-sign-out-alt w-6"></i><span>Logout</span></div>
                </nav>
            </div>

            <div class="p-8 bg-gray-50">
                <div id="studentSection-overview" class="student-section">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Student Dashboard</h1>
                    <div class="grid md:grid-cols-4 gap-6 mb-8">
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Reg. Number</p>
                                    <p class="text-xl font-bold text-gray-800" id="dashboardRegNo">-</p>
                                </div>
                                <i class="fas fa-id-card text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Current Class</p>
                                    <p class="text-xl font-bold text-gray-800" id="dashboardClass">-</p>
                                </div>
                                <i class="fas fa-graduation-cap text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Payment Status</p>
                                    <p class="text-xl font-bold text-green-600" id="dashboardPaymentStatus">-</p>
                                </div>
                                <i class="fas fa-check-circle text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Attendance</p>
                                    <p class="text-xl font-bold text-gray-800">95%</p>
                                </div>
                                <i class="fas fa-calendar-check text-3xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Recent Payments</h3>
                            <div id="studentPaymentsList"><div class="empty-state"><i class="fas fa-receipt"></i><p>No payments yet</p></div></div>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Upcoming Events</h3>
                            <div class="space-y-3">
                                <div class="flex items-center p-3 bg-green-50 rounded-lg">
                                    <div class="w-12 h-12 bg-green-600 text-white rounded-lg flex flex-col items-center justify-center mr-3">
                                        <span class="text-xs">MAR</span>
                                        <span class="font-bold">15</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold">Mid-Term Exams</p>
                                        <p class="text-sm text-gray-600">Starts Monday</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="studentSection-payments" class="student-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Fee Payments</h1>
                    <div class="card p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">Make Payment</h3>
                        <form onsubmit="makePayment(event)">
                            <div class="grid md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Payment Type</label>
                                    <select class="input-field" id="paymentType">
                                        <option value="School Fees">School Fees</option>
                                        <option value="Examination Fees">Examination Fees</option>
                                        <option value="PTA Levy">PTA Levy</option>
                                        <option value="Textbooks">Textbooks</option>
                                        <option value="Uniform">Uniform</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Amount (₦)</label>
                                    <input type="number" class="input-field" id="paymentAmount" placeholder="Enter amount" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Payment Method</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center"><input type="radio" name="payMethod" value="bank" class="mr-2" checked>Bank Transfer</label>
                                    <label class="flex items-center"><input type="radio" name="payMethod" value="cash" class="mr-2">Cash Deposit</label>
                                </div>
                            </div>
                            <button type="submit" class="btn-primary" id="payBtn"><i class="fas fa-credit-card mr-2"></i>Process Payment</button>
                        </form>
                    </div>

                    <div class="card p-6">
                        <h3 class="text-xl font-bold mb-4">Payment History</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Receipt No</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="studentPaymentHistory">
                                    <tr><td colspan="5" class="text-center py-4">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="studentSection-profile" class="student-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Profile</h1>
                    <div class="card p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-user text-4xl text-green-600"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold" id="profileName">-</h2>
                                <p class="text-gray-600" id="profileClass">-</p>
                                <p class="text-green-600 font-semibold" id="profileRegNo">-</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-bold mb-3 text-gray-700">Personal Information</h3>
                                <div class="space-y-2">
                                    <p><span class="text-gray-600">Date of Birth:</span> <span id="profileDOB">-</span></p>
                                    <p><span class="text-gray-600">Gender:</span> <span id="profileGender">-</span></p>
                                    <p><span class="text-gray-600">Address:</span> <span id="profileAddress">-</span></p>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold mb-3 text-gray-700">Guardian Information</h3>
                                <div class="space-y-2">
                                    <p><span class="text-gray-600">Name:</span> <span id="profileParent">-</span></p>
                                    <p><span class="text-gray-600">Phone:</span> <span id="profilePhone">-</span></p>
                                    <p><span class="text-gray-600">Email:</span> <span id="profileEmail">-</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="studentSection-results" class="student-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Academic Results</h1>
                    <div class="card p-6">
                        <div class="mb-4 flex justify-between items-center">
                            <select class="input-field w-auto" id="resultTerm" onchange="loadStudentResults()">
                                <option value="First Term">First Term 2023/2024</option>
                                <option value="Second Term">Second Term 2023/2024</option>
                                <option value="Third Term">Third Term 2022/2023</option>
                            </select>
                            <button class="btn-secondary" onclick="printResult()"><i class="fas fa-print mr-2"></i>Print Result</button>
                        </div>
                        <div id="studentResultsContainer">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>CA (40%)</th>
                                        <th>Exam (60%)</th>
                                        <th>Total</th>
                                        <th>Grade</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody id="studentResultsTable">
                                    <tr><td colspan="6" class="text-center py-4">Loading results...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="studentSection-timetable" class="student-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Class Timetable</h1>
                    <div class="card p-6 overflow-x-auto">
                        <div id="studentTimetableContainer">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Monday</th>
                                        <th>Tuesday</th>
                                        <th>Wednesday</th>
                                        <th>Thursday</th>
                                        <th>Friday</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTimetableBody">
                                    <tr><td colspan="6" class="text-center py-4">Loading timetable...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 5: TEACHER DASHBOARD -->
    <div id="page-teacher-dashboard" class="page">
        <div class="dashboard-grid">
            <div class="sidebar text-white p-6">
                <div class="mb-8 text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-chalkboard-teacher text-4xl text-green-600"></i>
                    </div>
                    <h3 class="font-bold text-lg">Teacher Portal</h3>
                    <p class="text-sm opacity-80" id="teacherNameDisplay">Welcome</p>
                </div>
                <nav>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showTeacherSection('dashboard')"><i class="fas fa-home w-6"></i><span>Dashboard</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showTeacherSection('students')"><i class="fas fa-users w-6"></i><span>My Students</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showTeacherSection('attendance')"><i class="fas fa-clipboard-check w-6"></i><span>Attendance</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showTeacherSection('grades')"><i class="fas fa-chart-bar w-6"></i><span>Enter Grades</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showTeacherSection('salary')"><i class="fas fa-money-bill-wave w-6"></i><span>Salary</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center bg-white bg-opacity-20" onclick="logout()"><i class="fas fa-sign-out-alt w-6"></i><span>Logout</span></div>
                </nav>
            </div>

            <div class="p-8 bg-gray-50">
                <div id="teacherSection-dashboard" class="teacher-section">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Teacher Dashboard</h1>
                    <div class="grid md:grid-cols-4 gap-6 mb-8">
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Total Students</p>
                                    <p class="text-2xl font-bold text-gray-800" id="teacherTotalStudents">0</p>
                                </div>
                                <i class="fas fa-users text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Classes</p>
                                    <p class="text-2xl font-bold text-gray-800" id="teacherTotalClasses">0</p>
                                </div>
                                <i class="fas fa-chalkboard text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Subjects</p>
                                    <p class="text-2xl font-bold text-gray-800" id="teacherTotalSubjects">0</p>
                                </div>
                                <i class="fas fa-book text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Salary Status</p>
                                    <p class="text-2xl font-bold text-green-600" id="teacherSalaryStatus">-</p>
                                </div>
                                <i class="fas fa-check-circle text-3xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Today's Classes</h3>
                            <div class="space-y-3" id="teacherTodayClasses">
                                <div class="empty-state"><i class="fas fa-calendar"></i><p>No classes scheduled</p></div>
                            </div>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Quick Actions</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <button onclick="showTeacherSection('attendance')" class="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition text-center">
                                    <i class="fas fa-clipboard-check text-2xl text-green-600 mb-2"></i>
                                    <p class="font-semibold">Mark Attendance</p>
                                </button>
                                <button onclick="showTeacherSection('grades')" class="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition text-center">
                                    <i class="fas fa-chart-bar text-2xl text-green-600 mb-2"></i>
                                    <p class="font-semibold">Enter Grades</p>
                                </button>
                                <button class="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition text-center">
                                    <i class="fas fa-file-alt text-2xl text-green-600 mb-2"></i>
                                    <p class="font-semibold">Upload Material</p>
                                </button>
                                <button class="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition text-center">
                                    <i class="fas fa-envelope text-2xl text-green-600 mb-2"></i>
                                    <p class="font-semibold">Message Parents</p>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="teacherSection-students" class="teacher-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Students</h1>
                    <div class="card p-6">
                        <div class="mb-4 flex justify-between items-center">
                            <input type="text" placeholder="Search students..." class="input-field w-64" onkeyup="searchTeacherStudents(this.value)">
                            <select class="input-field w-auto" id="teacherClassFilter" onchange="filterTeacherStudents()">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg. No</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Gender</th>
                                        <th>Parent Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="teacherStudentsTable">
                                    <tr><td colspan="6" class="text-center py-4">Loading students...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="teacherSection-attendance" class="teacher-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Mark Attendance</h1>
                    <div class="card p-6">
                        <div class="mb-4 flex gap-4">
                            <select class="input-field w-auto" id="attendanceClass" onchange="loadAttendanceStudents()">
                                <option value="">Select Class</option>
                            </select>
                            <input type="date" class="input-field w-auto" id="attendanceDate" value="">
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg. No</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTable">
                                    <tr><td colspan="4" class="text-center py-4">Select a class to view students</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <button onclick="saveAttendance()" class="btn-primary mt-4" id="saveAttendanceBtn"><i class="fas fa-save mr-2"></i>Save Attendance</button>
                    </div>
                </div>

                <div id="teacherSection-grades" class="teacher-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Enter Grades</h1>
                    <div class="card p-6">
                        <div class="mb-4 flex gap-4">
                            <select class="input-field w-auto" id="gradeClass" onchange="loadGradeStudents()">
                                <option value="">Select Class</option>
                            </select>
                            <select class="input-field w-auto" id="gradeSubject">
                                <option value="">Select Subject</option>
                            </select>
                            <select class="input-field w-auto" id="gradeTerm">
                                <option value="First Term">First Term</option>
                                <option value="Second Term">Second Term</option>
                                <option value="Third Term">Third Term</option>
                            </select>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg. No</th>
                                        <th>Name</th>
                                        <th>CA Score (40)</th>
                                        <th>Exam Score (60)</th>
                                        <th>Total</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody id="gradesTable">
                                    <tr><td colspan="6" class="text-center py-4">Select class and subject to enter grades</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <button onclick="saveGrades()" class="btn-primary mt-4" id="saveGradesBtn"><i class="fas fa-save mr-2"></i>Save Grades</button>
                    </div>
                </div>

                <div id="teacherSection-salary" class="teacher-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Salary Information</h1>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Current Month</h3>
                            <div class="text-center mb-6">
                                <p class="text-gray-600" id="salaryMonth">March 2024</p>
                                <p class="text-4xl font-bold text-green-600" id="teacherCurrentSalary">₦0.00</p>
                                <span class="badge badge-success mt-2" id="teacherSalaryBadge">-</span>
                            </div>
                            <div class="space-y-2 text-sm" id="teacherSalaryBreakdown">
                                <p class="text-gray-500">Loading salary details...</p>
                            </div>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Payment History</h3>
                            <div class="space-y-3" id="teacherSalaryHistory">
                                <p class="text-gray-500">Loading history...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 6: BURSAR DASHBOARD -->
    <div id="page-bursar-dashboard" class="page">
        <div class="dashboard-grid">
            <div class="sidebar text-white p-6">
                <div class="mb-8 text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-money-check-alt text-4xl text-green-600"></i>
                    </div>
                    <h3 class="font-bold text-lg">Bursar Portal</h3>
                    <p class="text-sm opacity-80">Financial Management</p>
                </div>
                <nav>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showBursarSection('dashboard')"><i class="fas fa-home w-6"></i><span>Dashboard</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showBursarSection('fees')"><i class="fas fa-coins w-6"></i><span>School Fees</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showBursarSection('salaries')"><i class="fas fa-hand-holding-usd w-6"></i><span>Staff Salaries</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showBursarSection('expenses')"><i class="fas fa-file-invoice-dollar w-6"></i><span>Expenses</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showBursarSection('reports')"><i class="fas fa-chart-pie w-6"></i><span>Reports</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center bg-white bg-opacity-20" onclick="logout()"><i class="fas fa-sign-out-alt w-6"></i><span>Logout</span></div>
                </nav>
            </div>

            <div class="p-8 bg-gray-50">
                <div id="bursarSection-dashboard" class="bursar-section">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Bursar Dashboard</h1>
                    <div class="grid md:grid-cols-4 gap-6 mb-8">
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Total Revenue</p>
                                    <p class="text-2xl font-bold text-gray-800" id="bursarRevenue">₦0</p>
                                </div>
                                <i class="fas fa-arrow-up text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Total Expenses</p>
                                    <p class="text-2xl font-bold text-gray-800" id="bursarExpenses">₦0</p>
                                </div>
                                <i class="fas fa-arrow-down text-3xl text-red-500"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Pending Payments</p>
                                    <p class="text-2xl font-bold text-orange-600" id="bursarPending">₦0</p>
                                </div>
                                <i class="fas fa-clock text-3xl text-orange-500"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Net Balance</p>
                                    <p class="text-2xl font-bold text-green-600" id="bursarBalance">₦0</p>
                                </div>
                                <i class="fas fa-wallet text-3xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Recent Transactions</h3>
                            <div class="space-y-3" id="bursarTransactions">
                                <p class="text-gray-500">Loading transactions...</p>
                            </div>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Fee Collection Status</h3>
                            <div class="space-y-4" id="bursarFeeStatus">
                                <p class="text-gray-500">Loading status...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="bursarSection-fees" class="bursar-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">School Fees Management</h1>
                    <div class="card p-6 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">Fee Payments</h3>
                            <div class="flex gap-2">
                                <input type="text" placeholder="Search student..." class="input-field w-48" onkeyup="searchFeePayments(this.value)">
                                <button class="btn-primary" onclick="exportFeeReport()"><i class="fas fa-download mr-2"></i>Export</button>
                            </div>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg. No</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Amount Due</th>
                                        <th>Amount Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="bursarFeesTable">
                                    <tr><td colspan="8" class="text-center py-4">Loading payments...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="bursarSection-salaries" class="bursar-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Staff Salary Management</h1>
                    <div class="card p-6 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">Process Salary Payments</h3>
                            <button class="btn-primary" onclick="processAllSalaries()" id="processAllSalariesBtn"><i class="fas fa-money-bill-wave mr-2"></i>Process All Salaries</button>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Staff ID</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net Pay</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="bursarSalariesTable">
                                    <tr><td colspan="9" class="text-center py-4">Loading staff...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="bursarSection-expenses" class="bursar-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Expense Management</h1>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Add New Expense</h3>
                            <form onsubmit="addExpense(event)">
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Expense Category</label>
                                    <select class="input-field" id="expenseCategory">
                                        <option value="Stationery">Stationery</option>
                                        <option value="Utilities">Utilities</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Transportation">Transportation</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Description</label>
                                    <input type="text" class="input-field" id="expenseDescription" placeholder="Enter description" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Amount (₦)</label>
                                    <input type="number" class="input-field" id="expenseAmount" placeholder="Enter amount" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Date</label>
                                    <input type="date" class="input-field" id="expenseDate" required>
                                </div>
                                <button type="submit" class="btn-primary w-full" id="addExpenseBtn"><i class="fas fa-plus mr-2"></i>Add Expense</button>
                            </form>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Recent Expenses</h3>
                            <div class="space-y-3" id="bursarExpensesList">
                                <p class="text-gray-500">Loading expenses...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="bursarSection-reports" class="bursar-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Financial Reports</h1>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Monthly Summary</h3>
                            <canvas id="financeChart" width="400" height="300"></canvas>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Generate Report</h3>
                            <form onsubmit="generateReport(event)">
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Report Type</label>
                                    <select class="input-field" id="reportType">
                                        <option value="income">Income Statement</option>
                                        <option value="expense">Expense Report</option>
                                        <option value="fees">Fee Collection Report</option>
                                        <option value="salary">Salary Report</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">From Date</label>
                                    <input type="date" class="input-field" id="reportFromDate" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">To Date</label>
                                    <input type="date" class="input-field" id="reportToDate" required>
                                </div>
                                <button type="submit" class="btn-primary w-full" id="generateReportBtn"><i class="fas fa-file-alt mr-2"></i>Generate Report</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 7: ADMIN DASHBOARD -->
    <div id="page-admin-dashboard" class="page">
        <div class="dashboard-grid">
            <div class="sidebar text-white p-6">
                <div class="mb-8 text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-tie text-4xl text-green-600"></i>
                    </div>
                    <h3 class="font-bold text-lg">Admin Portal</h3>
                    <p class="text-sm opacity-80" id="adminRoleDisplay">Admin</p>
                </div>
                <nav>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showAdminSection('dashboard')"><i class="fas fa-home w-6"></i><span>Dashboard</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showAdminSection('students')"><i class="fas fa-user-graduate w-6"></i><span>All Students</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showAdminSection('staff')"><i class="fas fa-users-cog w-6"></i><span>Staff Management</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showAdminSection('classes')"><i class="fas fa-chalkboard w-6"></i><span>Classes</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showAdminSection('exams')"><i class="fas fa-file-alt w-6"></i><span>Exams</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center" onclick="showAdminSection('announcements')"><i class="fas fa-bullhorn w-6"></i><span>Announcements</span></div>
                    <div class="nav-item p-3 rounded-lg mb-2 flex items-center bg-white bg-opacity-20" onclick="logout()"><i class="fas fa-sign-out-alt w-6"></i><span>Logout</span></div>
                </nav>
            </div>

            <div class="p-8 bg-gray-50">
                <div id="adminSection-dashboard" class="admin-section">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Administrative Dashboard</h1>
                    <div class="grid md:grid-cols-4 gap-6 mb-8">
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Total Students</p>
                                    <p class="text-2xl font-bold text-gray-800" id="adminTotalStudents">0</p>
                                </div>
                                <i class="fas fa-user-graduate text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Total Staff</p>
                                    <p class="text-2xl font-bold text-gray-800" id="adminTotalStaff">0</p>
                                </div>
                                <i class="fas fa-users text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Classes</p>
                                    <p class="text-2xl font-bold text-gray-800" id="adminTotalClasses">0</p>
                                </div>
                                <i class="fas fa-chalkboard text-3xl text-green-600"></i>
                            </div>
                        </div>
                        <div class="stat-card p-6 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm">Attendance Today</p>
                                    <p class="text-2xl font-bold text-green-600">96%</p>
                                </div>
                                <i class="fas fa-chart-line text-3xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Quick Statistics</h3>
                            <div class="space-y-4" id="adminStats">
                                <p class="text-gray-500">Loading statistics...</p>
                            </div>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Recent Activities</h3>
                            <div class="space-y-3" id="adminActivities">
                                <p class="text-gray-500">Loading activities...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="adminSection-students" class="admin-section" style="display: none;">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Student Management</h1>
                        <button class="btn-primary" onclick="showAddStudentModal()"><i class="fas fa-plus mr-2"></i>Add New Student</button>
                    </div>
                    <div class="card p-6">
                        <div class="mb-4 flex gap-4">
                            <input type="text" placeholder="Search by name or reg no..." class="input-field w-64" onkeyup="searchAdminStudents(this.value)">
                            <select class="input-field w-auto" onchange="filterAdminStudents(this.value)">
                                <option value="">All Classes</option>
                            </select>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Reg. No</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Gender</th>
                                        <th>Parent Contact</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="adminStudentsTable">
                                    <tr><td colspan="7" class="text-center py-4">Loading students...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="adminSection-staff" class="admin-section" style="display: none;">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Staff Management</h1>
                        <button class="btn-primary" onclick="showAddStaffModal()"><i class="fas fa-plus mr-2"></i>Add New Staff</button>
                    </div>
                    <div class="card p-6">
                        <div class="mb-4 flex gap-4">
                            <input type="text" placeholder="Search staff..." class="input-field w-64" onkeyup="searchAdminStaff(this.value)">
                            <select class="input-field w-auto" onchange="filterAdminStaff(this.value)">
                                <option value="">All Roles</option>
                                <option value="Director">Director</option>
                                <option value="Principal">Principal</option>
                                <option value="Vice Principal">Vice Principal</option>
                                <option value="Headmaster">Headmaster</option>
                                <option value="Exam Officer">Exam Officer</option>
                                <option value="Bursar">Bursar</option>
                                <option value="Teacher">Teacher</option>
                            </select>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Staff ID</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Phone</th>
                                        <th>Salary</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="adminStaffTable">
                                    <tr><td colspan="8" class="text-center py-4">Loading staff...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="adminSection-classes" class="admin-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Class Management</h1>
                    <div class="grid md:grid-cols-3 gap-6" id="adminClassesGrid">
                        <p class="text-gray-500 col-span-3">Loading classes...</p>
                    </div>
                </div>

                <div id="adminSection-exams" class="admin-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Examination Management</h1>
                    <div class="card p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">Create New Exam</h3>
                        <div class="grid md:grid-cols-4 gap-4">
                            <input type="text" placeholder="Exam Name" class="input-field" id="newExamName">
                            <select class="input-field" id="newExamTerm">
                                <option value="First Term">First Term</option>
                                <option value="Second Term">Second Term</option>
                                <option value="Third Term">Third Term</option>
                            </select>
                            <input type="date" class="input-field" id="newExamDate">
                            <button class="btn-primary" onclick="createExam()"><i class="fas fa-plus mr-2"></i>Create Exam</button>
                        </div>
                    </div>
                    <div class="card p-6">
                        <h3 class="text-xl font-bold mb-4">Exam Schedule</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Exam Name</th>
                                        <th>Term</th>
                                        <th>Start Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="adminExamsTable">
                                    <tr><td colspan="5" class="text-center py-4">Loading exams...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="adminSection-announcements" class="admin-section" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Announcements</h1>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Create Announcement</h3>
                            form onsubmit="createAnnouncement(event)">
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Title</label>
                                    <input type="text" class="input-field" id="announcementTitle" placeholder="Enter announcement title" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Message</label>
                                    <textarea class="input-field" rows="4" id="announcementMessage" placeholder="Enter message..." required></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Target Audience</label>
                                    <select class="input-field" id="announcementTarget">
                                        <option value="all">All</option>
                                        <option value="students">Students Only</option>
                                        <option value="parents">Parents Only</option>
                                        <option value="staff">Staff Only</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-primary w-full" id="postAnnouncementBtn"><i class="fas fa-paper-plane mr-2"></i>Post Announcement</button>
                            </form>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4">Recent Announcements</h3>
                            <div class="space-y-4" id="adminAnnouncementsList">
                                <p class="text-gray-500">Loading announcements...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 8: ABOUT -->
    <div id="page-about" class="page">
        <div class="min-h-screen bg-gray-50">
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white py-16">
                <div class="container mx-auto px-6">
                    <button onclick="showPage('home')" class="mb-4 text-white hover:text-green-200"><i class="fas fa-arrow-left mr-2"></i>Back to Home</button>
                    <h1 class="text-4xl font-bold mb-4">About Ibra Foundation School</h1>
                    <p class="text-xl opacity-90">Building Future Leaders Since 2009</p>
                </div>
            </div>
            <div class="container mx-auto px-6 py-12">
                <div class="max-w-4xl mx-auto">
                    <div class="card p-8 mb-8">
                        <h2 class="text-2xl font-bold mb-4 text-gray-800">Our History</h2>
                        <p class="text-gray-600 leading-relaxed mb-4">Ibra Foundation School Mirnga was established in 2009 with a vision to provide quality education to the children of Mirnga and surrounding communities.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-8 mb-8">
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4 text-gray-800">Our Vision</h3>
                            <p class="text-gray-600">To be a leading educational institution that nurtures future leaders through quality education and moral values.</p>
                        </div>
                        <div class="card p-6">
                            <h3 class="text-xl font-bold mb-4 text-gray-800">Our Mission</h3>
                            <p class="text-gray-600">To provide a conducive learning environment that promotes academic excellence and character development.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Global Variables
        let currentUser = null;
        let currentUserType = null;
        let currentRole = null;
        let allStudents = [];
        let allStaff = [];
        let allClasses = [];
        let tempAdmissionData = null;

        // API Base URL
        const API_URL = 'api.php';

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkSession();
            loadHomeStats();
            
            // Set today's date for attendance
            document.getElementById('attendanceDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('expenseDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('reportFromDate').value = new Date(new Date().setDate(1)).toISOString().split('T')[0];
            document.getElementById('reportToDate').value = new Date().toISOString().split('T')[0];
        });

        // API Helper Functions
        async function apiCall(action, method = 'GET', data = null) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    }
                };

                let url = `${API_URL}?action=${action}`;
                
                if (method === 'GET' && data) {
                    const params = new URLSearchParams(data).toString();
                    url += '&' + params;
                } else if (method === 'POST' && data) {
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(url, options);
                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                return result;
            } catch (error) {
                console.error('API Error:', error);
                showNotification(error.message || 'An error occurred', 'error');
                throw error;
            }
        }

        // Page Navigation
        function showPage(pageName) {
            document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
            document.getElementById('page-' + pageName).classList.add('active');
            window.scrollTo(0, 0);
            
            // Load data based on page
            if (pageName === 'student-dashboard' && currentUser) {
                loadStudentDashboard();
            } else if (pageName === 'teacher-dashboard' && currentUser) {
                loadTeacherDashboard();
            } else if (pageName === 'bursar-dashboard' && currentUser) {
                loadBursarDashboard();
            } else if (pageName === 'admin-dashboard' && currentUser) {
                loadAdminDashboard();
            }
        }

        // Notification System
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const icon = document.getElementById('notificationIcon');
            const text = document.getElementById('notificationText');
            
            text.textContent = message;
            
            notification.className = 'notification show';
            if (type === 'error') {
                notification.classList.add('error');
                icon.className = 'fas fa-times-circle text-red-600 mr-2';
            } else if (type === 'warning') {
                notification.classList.add('warning');
                icon.className = 'fas fa-exclamation-circle text-yellow-600 mr-2';
            } else {
                icon.className = 'fas fa-check-circle text-green-600 mr-2';
            }
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Login Functions
        function switchLoginTab(type) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            const regInput = document.getElementById('loginRegNo');
            if (type === 'student') {
                regInput.placeholder = 'Enter Reg. No (e.g., STD2024001)';
            } else {
                regInput.placeholder = 'Enter Staff ID (e.g., TCH001)';
            }
            regInput.dataset.type = type;
        }

        async function handleLogin(e) {
            e.preventDefault();
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span class="loading"></span> Logging in...';
            btn.disabled = true;
            
            const regNo = document.getElementById('loginRegNo').value;
            const password = document.getElementById('loginPassword').value;
            const type = document.getElementById('loginRegNo').dataset.type || 'student';
            
            try {
                const result = await apiCall('login', 'POST', {
                    reg_no: regNo,
                    password: password,
                    type: type
                });
                
                if (result.success) {
                    currentUser = result.user;
                    currentUserType = result.type;
                    currentRole = result.role || null;
                    
                    showNotification('Welcome back, ' + (result.user.first_name || result.user.name) + '!');
                    
                    if (result.type === 'student') {
                        showPage('student-dashboard');
                    } else if (result.role === 'Bursar') {
                        showPage('bursar-dashboard');
                    } else if (['Director', 'Principal', 'Vice Principal', 'Headmaster', 'Exam Officer'].includes(result.role)) {
                        document.getElementById('adminRoleDisplay').textContent = result.role;
                        showPage('admin-dashboard');
                    } else if (result.role === 'Teacher') {
                        showPage('teacher-dashboard');
                    }
                }
            } catch (error) {
                // Error handled by apiCall
            } finally {
                btn.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Login';
                btn.disabled = false;
            }
        }

        async function checkSession() {
            try {
                const result = await apiCall('check_session');
                if (result.logged_in) {
                    currentUser = result.user;
                    currentUserType = result.type;
                    currentRole = result.role || null;
                }
            } catch (error) {
                console.log('No active session');
            }
        }

        function logout() {
            apiCall('logout', 'POST').then(() => {
                currentUser = null;
                currentUserType = null;
                currentRole = null;
                showPage('home');
                showNotification('Logged out successfully');
            });
        }

        // Home Stats
        async function loadHomeStats() {
            try {
                const result = await apiCall('get_dashboard_stats');
                if (result.success) {
                    document.getElementById('statStudents').textContent = result.stats.students;
                    document.getElementById('statStaff').textContent = result.stats.staff;
                }
            } catch (error) {
                console.error('Failed to load stats');
            }
        }

        // Admission Functions
        async function submitAdmission(e) {
            e.preventDefault();
            const btn = document.getElementById('admSubmitBtn');
            btn.innerHTML = '<span class="loading"></span> Processing...';
            btn.disabled = true;
            
            tempAdmissionData = {
                surname: document.getElementById('admSurname').value,
                first_name: document.getElementById('admFirstName').value,
                other_names: document.getElementById('admOtherNames').value,
                date_of_birth: document.getElementById('admDOB').value,
                gender: document.getElementById('admGender').value,
                class: document.getElementById('admClass').value,
                parent_name: document.getElementById('admParentName').value,
                parent_phone: document.getElementById('admParentPhone').value,
                parent_email: document.getElementById('admParentEmail').value,
                address: document.getElementById('admAddress').value
            };
            
            document.getElementById('admissionStep1').style.display = 'none';
            document.getElementById('admissionStep2').style.display = 'block';
            
            btn.innerHTML = '<i class="fas fa-arrow-right mr-2"></i>Proceed to Payment';
            btn.disabled = false;
        }

        async function completeAdmission() {
            if (!document.getElementById('paymentReceipt').value) {
                showNotification('Please upload payment receipt', 'error');
                return;
            }
            
            const btn = document.getElementById('completeAdmBtn');
            btn.innerHTML = '<span class="loading"></span> Processing...';
            btn.disabled = true;
            
            try {
                const result = await apiCall('register_student', 'POST', tempAdmissionData);
                
                if (result.success) {
                    document.getElementById('admissionStep2').style.display = 'none';
                    document.getElementById('admissionSuccess').style.display = 'block';
                    document.getElementById('generatedRegNo').textContent = result.reg_no;
                    showNotification('Registration completed successfully!');
                }
            } finally {
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Complete Registration';
                btn.disabled = false;
            }
        }

        // Student Dashboard Functions
        async function loadStudentDashboard() {
            if (!currentUser) return;
            
            document.getElementById('studentNameDisplay').textContent = 'Welcome, ' + currentUser.first_name;
            document.getElementById('dashboardRegNo').textContent = currentUser.reg_no;
            document.getElementById('dashboardClass').textContent = currentUser.class;
            
            // Load profile
            document.getElementById('profileName').textContent = currentUser.surname + ' ' + currentUser.first_name;
            document.getElementById('profileClass').textContent = currentUser.class;
            document.getElementById('profileRegNo').textContent = currentUser.reg_no;
            document.getElementById('profileDOB').textContent = currentUser.date_of_birth;
            document.getElementById('profileGender').textContent = currentUser.gender;
            document.getElementById('profileAddress').textContent = currentUser.address;
            document.getElementById('profileParent').textContent = currentUser.parent_name;
            document.getElementById('profilePhone').textContent = currentUser.parent_phone;
            document.getElementById('profileEmail').textContent = currentUser.parent_email || 'N/A';
            
            // Load payments
            loadStudentPayments();
            
            // Load results
            loadStudentResults();
        }

        function showStudentSection(section) {
            document.querySelectorAll('.student-section').forEach(s => s.style.display = 'none');
            document.getElementById('studentSection-' + section).style.display = 'block';
            
            if (section === 'payments') loadStudentPayments();
            if (section === 'results') loadStudentResults();
            if (section === 'timetable') loadStudentTimetable();
        }

        async function loadStudentPayments() {
            try {
                const result = await apiCall('get_student_payments', 'GET', { reg_no: currentUser.reg_no });
                const tbody = document.getElementById('studentPaymentHistory');
                const list = document.getElementById('studentPaymentsList');
                
                if (result.payments.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No payments found</td></tr>';
                    list.innerHTML = '<div class="empty-state"><i class="fas fa-receipt"></i><p>No payments yet</p></div>';
                    return;
                }
                
                let html = '';
                let listHtml = '<table class="w-full"><thead><tr class="text-left text-sm text-gray-600"><th class="pb-2">Description</th><th class="pb-2">Amount</th><th class="pb-2">Date</th></tr></thead><tbody>';
                
                result.payments.forEach(payment => {
                    const date = new Date(payment.payment_date).toLocaleDateString();
                    html += `<tr>
                        <td>${payment.receipt_no}</td>
                        <td>${payment.payment_type}</td>
                        <td>₦${parseFloat(payment.amount).toLocaleString()}</td>
                        <td>${date}</td>
                        <td><span class="badge badge-success">${payment.status}</span></td>
                    </tr>`;
                    listHtml += `<tr><td class="py-2">${payment.payment_type}</td><td>₦${parseFloat(payment.amount).toLocaleString()}</td><td>${date}</td></tr>`;
                });
                
                tbody.innerHTML = html;
                listHtml += '</tbody></table>';
                list.innerHTML = listHtml;
                
                // Update payment status
                const totalPaid = result.payments.reduce((sum, p) => sum + parseFloat(p.amount), 0);
                document.getElementById('dashboardPaymentStatus').textContent = totalPaid > 0 ? 'Paid' : 'Pending';
                
            } catch (error) {
                console.error('Failed to load payments');
            }
        }

        async function makePayment(e) {
            e.preventDefault();
            const btn = document.getElementById('payBtn');
            btn.innerHTML = '<span class="loading"></span> Processing...';
            btn.disabled = true;
            
            try {
                const result = await apiCall('make_payment', 'POST', {
                    payment_type: document.getElementById('paymentType').value,
                    reg_no: currentUser.reg_no,
                    amount: document.getElementById('paymentAmount').value,
                    payment_method: document.querySelector('input[name="payMethod"]:checked').value,
                    description: 'Student payment'
                });
                
                showNotification('Payment processed successfully! Receipt: ' + result.receipt_no);
                document.getElementById('paymentAmount').value = '';
                loadStudentPayments();
            } finally {
                btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Process Payment';
                btn.disabled = false;
            }
        }

        async function loadStudentResults() {
            try {
                const term = document.getElementById('resultTerm').value;
                const result = await apiCall('get_grades', 'GET', { 
                    reg_no: currentUser.reg_no,
                    term: term
                });
                
                const tbody = document.getElementById('studentResultsTable');
                
                if (!result.grades || result.grades.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No results found for this term</td></tr>';
                    return;
                }
                
                let html = '';
                result.grades.forEach(grade => {
                    html += `<tr>
                        <td>${grade.subject}</td>
                        <td>${grade.ca_score}</td>
                        <td>${grade.exam_score}</td>
                        <td class="font-bold">${grade.total_score}</td>
                        <td class="font-bold text-green-600">${grade.grade}</td>
                        <td>${grade.remark}</td>
                    </tr>`;
                });
                
                tbody.innerHTML = html;
            } catch (error) {
                document.getElementById('studentResultsTable').innerHTML = '<tr><td colspan="6" class="text-center py-4">Failed to load results</td></tr>';
            }
        }

        async function loadStudentTimetable() {
            try {
                const result = await apiCall('get_timetable', 'GET', { class: currentUser.class });
                const tbody = document.getElementById('studentTimetableBody');
                
                // Simple timetable display
                const timeSlots = ['8:00 - 9:00', '9:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00', '12:00 - 1:00', '1:00 - 2:00'];
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                
                let html = '';
                timeSlots.forEach((time, idx) => {
                    if (idx === 2) {
                        html += `<tr><td class="font-bold">${time}</td><td colspan="5" class="text-center bg-gray-100 font-bold">BREAK TIME</td></tr>`;
                    } else {
                        html += `<tr><td class="font-bold">${time}</td>`;
                        days.forEach(day => {
                            const entry = result.timetable.find(t => t.day_of_week === day && t.time_slot === time);
                            if (entry) {
                                html += `<td class="bg-green-50 p-2">${entry.subject}<br><small>${entry.teacher_id || ''}</small></td>`;
                            } else {
                                html += `<td>-</td>`;
                            }
                        });
                        html += '</tr>';
                    }
                });
                
                tbody.innerHTML = html;
            } catch (error) {
                document.getElementById('studentTimetableBody').innerHTML = '<tr><td colspan="6" class="text-center py-4">Failed to load timetable</td></tr>';
            }
        }

        // Teacher Dashboard Functions
        async function loadTeacherDashboard() {
            if (!currentUser) return;
            
            document.getElementById('teacherNameDisplay').textContent = 'Welcome, ' + currentUser.name;
            
            try {
                const result = await apiCall('get_teacher_dashboard');
                
                document.getElementById('teacherTotalStudents').textContent = result.total_students;
                document.getElementById('teacherTotalClasses').textContent = result.classes.length;
                document.getElementById('teacherTotalSubjects').textContent = result.subjects.length;
                document.getElementById('teacherSalaryStatus').textContent = result.salary_status === 'paid' ? 'Paid' : 'Pending';
                
                // Populate class filter
                const classFilter = document.getElementById('teacherClassFilter');
                const attendanceClass = document.getElementById('attendanceClass');
                const gradeClass = document.getElementById('gradeClass');
                
                classFilter.innerHTML = '<option value="">All Classes</option>';
                attendanceClass.innerHTML = '<option value="">Select Class</option>';
                gradeClass.innerHTML = '<option value="">Select Class</option>';
                
                result.classes.forEach(cls => {
                    classFilter.innerHTML += `<option value="${cls}">${cls}</option>`;
                    attendanceClass.innerHTML += `<option value="${cls}">${cls}</option>`;
                    gradeClass.innerHTML += `<option value="${cls}">${cls}</option>`;
                });
                
                // Populate subjects
                const gradeSubject = document.getElementById('gradeSubject');
                gradeSubject.innerHTML = '<option value="">Select Subject</option>';
                result.subjects.forEach(subj => {
                    gradeSubject.innerHTML += `<option value="${subj}">${subj}</option>`;
                });
                
                // Load salary info
                if (result.salary_details) {
                    document.getElementById('teacherCurrentSalary').textContent = '₦' + parseFloat(result.salary_details.net_salary).toLocaleString();
                    document.getElementById('teacherSalaryBadge').textContent = 'Paid';
                    document.getElementById('teacherSalaryBadge').className = 'badge badge-success mt-2';
                } else {
                    document.getElementById('teacherCurrentSalary').textContent = '₦0.00';
                    document.getElementById('teacherSalaryBadge').textContent = 'Pending';
                    document.getElementById('teacherSalaryBadge').className = 'badge badge-warning mt-2';
                }
                
                // Load students
                loadTeacherStudents();
                
                // Load salary history
                loadTeacherSalaryHistory();
                
            } catch (error) {
                console.error('Failed to load teacher dashboard');
            }
        }

        function showTeacherSection(section) {
            document.querySelectorAll('.teacher-section').forEach(s => s.style.display = 'none');
            document.getElementById('teacherSection-' + section).style.display = 'block';
            
            if (section === 'students') loadTeacherStudents();
            if (section === 'salary') loadTeacherSalaryHistory();
        }

        async function loadTeacherStudents() {
            const classFilter = document.getElementById('teacherClassFilter').value;
            
            try {
                const result = await apiCall('get_students', 'GET', classFilter ? { class: classFilter } : {});
                allStudents = result.students;
                renderTeacherStudents(result.students);
            } catch (error) {
                document.getElementById('teacherStudentsTable').innerHTML = '<tr><td colspan="6" class="text-center py-4">Failed to load students</td></tr>';
            }
        }

        function renderTeacherStudents(students) {
            const tbody = document.getElementById('teacherStudentsTable');
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No students found</td></tr>';
                return;
            }
            
            let html = '';
            students.forEach(student => {
                html += `<tr>
                    <td>${student.reg_no}</td>
                    <td>${student.surname} ${student.first_name}</td>
                    <td>${student.class}</td>
                    <td>${student.gender}</td>
                    <td>${student.parent_phone}</td>
                    <td>
                        <button class="text-green-600 hover:text-green-800" onclick="viewStudent('${student.reg_no}')"><i class="fas fa-eye"></i></button>
                    </td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }

        function searchTeacherStudents(query) {
            const filtered = allStudents.filter(s => 
                s.surname.toLowerCase().includes(query.toLowerCase()) ||
                s.first_name.toLowerCase().includes(query.toLowerCase()) ||
                s.reg_no.toLowerCase().includes(query.toLowerCase())
            );
            renderTeacherStudents(filtered);
        }

        function filterTeacherStudents() {
            loadTeacherStudents();
        }

        async function loadAttendanceStudents() {
            const cls = document.getElementById('attendanceClass').value;
            const date = document.getElementById('attendanceDate').value;
            
            if (!cls) return;
            
            try {
                const result = await apiCall('get_attendance', 'GET', { class: cls, date: date });
                const tbody = document.getElementById('attendanceTable');
                
                let html = '';
                result.students.forEach(student => {
                    const status = student.attendance ? student.attendance.status : 'present';
                    const remarks = student.attendance ? student.attendance.remarks : '';
                    
                    html += `<tr>
                        <td>${student.reg_no}</td>
                        <td>${student.surname} ${student.first_name}</td>
                        <td>
                            <select class="input-field w-auto attendance-status" data-regno="${student.reg_no}">
                                <option value="present" ${status === 'present' ? 'selected' : ''}>Present</option>
                                <option value="absent" ${status === 'absent' ? 'selected' : ''}>Absent</option>
                                <option value="late" ${status === 'late' ? 'selected' : ''}>Late</option>
                            </select>
                        </td>
                        <td><input type="text" class="input-field attendance-remarks" data-regno="${student.reg_no}" value="${remarks}" placeholder="Optional"></td>
                    </tr>`;
                });
                
                tbody.innerHTML = html;
            } catch (error) {
                document.getElementById('attendanceTable').innerHTML = '<tr><td colspan="4" class="text-center py-4">Failed to load students</td></tr>';
            }
        }

        async function saveAttendance() {
            const btn = document.getElementById('saveAttendanceBtn');
            btn.innerHTML = '<span class="loading"></span> Saving...';
            btn.disabled = true;
            
            const cls = document.getElementById('attendanceClass').value;
            const date = document.getElementById('attendanceDate').value;
            
            const attendance = [];
            document.querySelectorAll('.attendance-status').forEach(select => {
                attendance.push({
                    reg_no: select.dataset.regno,
                    status: select.value,
                    remarks: document.querySelector(`.attendance-remarks[data-regno="${select.dataset.regno}"]`).value
                });
            });
            
            try {
                const result = await apiCall('save_attendance', 'POST', {
                    class: cls,
                    date: date,
                    attendance: attendance
                });
                
                showNotification('Attendance saved successfully');
            } finally {
                btn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Attendance';
                btn.disabled = false;
            }
        }

        async function loadGradeStudents() {
            const cls = document.getElementById('gradeClass').value;
            const subject = document.getElementById('gradeSubject').value;
            const term = document.getElementById('gradeTerm').value;
            
            if (!cls || !subject) return;
            
            try {
                // Get students and existing grades
                const [studentsResult, gradesResult] = await Promise.all([
                    apiCall('get_students', 'GET', { class: cls }),
                    apiCall('get_grades', 'GET', { class: cls, subject: subject, term: term })
                ]);
                
                const gradesMap = {};
                gradesResult.grades.forEach(g => {
                    gradesMap[g.reg_no] = g;
                });
                
                const tbody = document.getElementById('gradesTable');
                let html = '';
                
                studentsResult.students.forEach(student => {
                    const grade = gradesMap[student.reg_no] || {};
                    const total = (grade.ca_score || 0) + (grade.exam_score || 0);
                    
                    html += `<tr>
                        <td>${student.reg_no}</td>
                        <td>${student.surname} ${student.first_name}</td>
                        <td><input type="number" class="input-field w-20 grade-ca" data-regno="${student.reg_no}" max="40" value="${grade.ca_score || ''}"></td>
                        <td><input type="number" class="input-field w-20 grade-exam" data-regno="${student.reg_no}" max="60" value="${grade.exam_score || ''}"></td>
                        <td class="font-bold grade-total">${total}</td>
                        <td class="font-bold text-green-600 grade-letter">${grade.grade || '-'}</td>
                    </tr>`;
                });
                
                tbody.innerHTML = html;
                
                // Add input listeners for auto-calculation
                document.querySelectorAll('.grade-ca, .grade-exam').forEach(input => {
                    input.addEventListener('input', calculateGrade);
                });
                
            } catch (error) {
                document.getElementById('gradesTable').innerHTML = '<tr><td colspan="6" class="text-center py-4">Failed to load students</td></tr>';
            }
        }

        function calculateGrade(e) {
            const regNo = e.target.dataset.regno;
            const ca = parseFloat(document.querySelector(`.grade-ca[data-regno="${regNo}"]`).value) || 0;
            const exam = parseFloat(document.querySelector(`.grade-exam[data-regno="${regNo}"]`).value) || 0;
            const total = ca + exam;
            
            let grade = 'F';
            if (total >= 70) grade = 'A';
            else if (total >= 60) grade = 'B';
            else if (total >= 50) grade = 'C';
            else if (total >= 45) grade = 'D';
            else if (total >= 40) grade = 'E';
            
            const row = e.target.closest('tr');
            row.querySelector('.grade-total').textContent = total;
            row.querySelector('.grade-letter').textContent = grade;
        }

        async function saveGrades() {
            const btn = document.getElementById('saveGradesBtn');
            btn.innerHTML = '<span class="loading"></span> Saving...';
            btn.disabled = true;
            
            const cls = document.getElementById('gradeClass').value;
            const subject = document.getElementById('gradeSubject').value;
            const term = document.getElementById('gradeTerm').value;
            
            const grades = [];
            document.querySelectorAll('.grade-ca').forEach(input => {
                const regNo = input.dataset.regno;
                grades.push({
                    reg_no: regNo,
                    ca_score: parseFloat(input.value) || 0,
                    exam_score: parseFloat(document.querySelector(`.grade-exam[data-regno="${regNo}"]`).value) || 0
                });
            });
            
            try {
                const result = await apiCall('save_grades', 'POST', {
                    class: cls,
                    subject: subject,
                    term: term,
                    grades: grades
                });
                
                showNotification('Grades saved successfully');
            } finally {
                btn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Grades';
                btn.disabled = false;
            }
        }

        async function loadTeacherSalaryHistory() {
            try {
                const result = await apiCall('get_staff_salary');
                const container = document.getElementById('teacherSalaryHistory');
                
                if (!result.history || result.history.length === 0) {
                    container.innerHTML = '<p class="text-gray-500">No salary history</p>';
                    return;
                }
                
                let html = '';
                result.history.forEach(salary => {
                    const date = new Date(salary.payment_date).toLocaleDateString();
                    html += `<div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold">${salary.month_year}</p>
                            <p class="text-sm text-gray-600">Paid on ${date}</p>
                        </div>
                        <span class="badge badge-success">₦${parseFloat(salary.net_salary).toLocaleString()}</span>
                    </div>`;
                });
                
                container.innerHTML = html;
                
                // Update breakdown
                if (result.current) {
                    document.getElementById('teacherSalaryBreakdown').innerHTML = `
                        <div class="flex justify-between"><span>Basic Salary</span><span>₦${parseFloat(result.current.basic_salary).toLocaleString()}</span></div>
                        <div class="flex justify-between"><span>Allowances</span><span>₦${parseFloat(result.current.allowances).toLocaleString()}</span></div>
                        <div class="flex justify-between"><span>Deductions</span><span>₦${parseFloat(result.current.deductions).toLocaleString()}</span></div>
                        <hr class="my-2">
                        <div class="flex justify-between font-bold"><span>Net Pay</span><span>₦${parseFloat(result.current.net_salary).toLocaleString()}</span></div>
                    `;
                }
                
            } catch (error) {
                document.getElementById('teacherSalaryHistory').innerHTML = '<p class="text-gray-500">Failed to load history</p>';
            }
        }

        // Bursar Dashboard Functions
        async function loadBursarDashboard() {
            if (!currentUser) return;
            
            try {
                const result = await apiCall('get_bursar_dashboard');
                
                document.getElementById('bursarRevenue').textContent = '₦' + parseFloat(result.revenue).toLocaleString();
                document.getElementById('bursarExpenses').textContent = '₦' + parseFloat(result.expenses).toLocaleString();
                document.getElementById('bursarBalance').textContent = '₦' + (result.revenue - result.expenses).toLocaleString();
                
                // Recent transactions
                const transContainer = document.getElementById('bursarTransactions');
                if (result.recent_transactions.length === 0) {
                    transContainer.innerHTML = '<p class="text-gray-500">No recent transactions</p>';
                } else {
                    let html = '';
                    result.recent_transactions.forEach(trans => {
                        const date = new Date(trans.payment_date).toLocaleDateString();
                        const isPositive = trans.payment_type !== 'Salary';
                        html += `<div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-semibold">${trans.payment_type}</p>
                                <p class="text-sm text-gray-600">${trans.reg_no ? trans.reg_no + ' - ' + trans.surname : 'Staff Payment'} (${date})</p>
                            </div>
                            <span class="${isPositive ? 'text-green-600' : 'text-red-500'} font-bold">${isPositive ? '+' : '-'}₦${parseFloat(trans.amount).toLocaleString()}</span>
                        </div>`;
                    });
                    transContainer.innerHTML = html;
                }
                
                // Load fees and salaries
                loadBursarFees();
                loadBursarSalaries();
                loadBursarExpenses();
                
            } catch (error) {
                console.error('Failed to load bursar dashboard');
            }
        }

        function showBursarSection(section) {
            document.querySelectorAll('.bursar-section').forEach(s => s.style.display = 'none');
            document.getElementById('bursarSection-' + section).style.display = 'block';
            
            if (section === 'fees') loadBursarFees();
            if (section === 'salaries') loadBursarSalaries();
            if (section === 'expenses') loadBursarExpenses();
        }

        async function loadBursarFees() {
            try {
                const result = await apiCall('get_students');
                const tbody = document.getElementById('bursarFeesTable');
                
                let html = '';
                result.students.forEach(student => {
                    const statusClass = student.payment_status === 'paid' ? 'badge-success' : (student.payment_status === 'partial' ? 'badge-warning' : 'badge-danger');
                    
                    html += `<tr>
                        <td>${student.reg_no}</td>
                        <td>${student.surname} ${student.first_name}</td>
                        <td>${student.class}</td>
                        <td>₦${parseFloat(student.fee_due).toLocaleString()}</td>
                        <td>₦${parseFloat(student.fee_paid).toLocaleString()}</td>
                        <td>₦${parseFloat(student.fee_balance).toLocaleString()}</td>
                        <td><span class="badge ${statusClass}">${student.payment_status}</span></td>
                        <td><button class="text-green-600 hover:text-green-800" onclick="viewStudent('${student.reg_no}')"><i class="fas fa-eye"></i></button></td>
                    </tr>`;
                });
                
                tbody.innerHTML = html;
            } catch (error) {
                document.getElementById('bursarFeesTable').innerHTML = '<tr><td colspan="8" class="text-center py-4">Failed to load fees</td></tr>';
            }
        }

        async function loadBursarSalaries() {
            try {
                const result = await apiCall('get_salaries');
                const tbody = document.getElementById('bursarSalariesTable');
                
                let html = '';
                result.salaries.forEach(salary => {
                    const statusClass = salary.status === 'paid' ? 'badge-success' : 'badge-warning';
                    
                    html += `<tr>
                        <td>${salary.staff_id}</td>
                        <td>${salary.name}</td>
                        <td>${salary.role}</td>
                        <td>₦${parseFloat(salary.basic_salary).toLocaleString()}</td>
                        <td>₦${parseFloat(salary.allowances).toLocaleString()}</td>
                        <td>₦${parseFloat(salary.deductions).toLocaleString()}</td>
                        <td>₦${parseFloat(salary.net_salary).toLocaleString()}</td>
                        <td><span class="badge ${statusClass}">${salary.status}</span></td>
                        <td>${salary.status === 'pending' ? `<button class="btn-primary text-sm py-1 px-3" onclick="paySalary('${salary.staff_id}')">Pay</button>` : '-'}</td>
                    </tr>`;
                });
                
                tbody.innerHTML = html;
            } catch (error) {
                document.getElementById('bursarSalariesTable').innerHTML = '<tr><td colspan="9" class="text-center py-4">Failed to load salaries</td></tr>';
            }
        }

        async function paySalary(staffId) {
            try {
                const result = await apiCall('process_salary', 'POST', { staff_id: staffId });
                showNotification('Salary paid successfully');
                loadBursarSalaries();
            } catch (error) {
                showNotification('Failed to pay salary', 'error');
            }
        }

        async function processAllSalaries() {
            const btn = document.getElementById('processAllSalariesBtn');
            btn.innerHTML = '<span class="loading"></span> Processing...';
            btn.disabled = true;
            
            try {
                const result = await apiCall('process_all_salaries', 'POST', {});
                showNotification(`Processed salaries for ${result.processed} staff members`);
                loadBursarSalaries();
            } finally {
                btn.innerHTML = '<i class="fas fa-money-bill-wave mr-2"></i>Process All Salaries';
                btn.disabled = false;
            }
        }

        async function loadBursarExpenses() {
            try {
                const result = await apiCall('get_expenses');
                const container = document.getElementById('bursarExpensesList');
                
                if (result.expenses.length === 0) {
                    container.innerHTML = '<p class="text-gray-500">No expenses recorded</p>';
                    return;
                }
                
                let html = '';
                result.expenses.forEach(expense => {
                    const date = new Date(expense.expense_date).toLocaleDateString();
                    html += `<div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold">${expense.category}</p>
                            <p class="text-sm text-gray-600">${expense.description} (${date})</p>
                        </div>
                        <span class="text-red-500 font-bold">-₦${parseFloat(expense.amount).toLocaleString()}</span>
                    </div>`;
                });
                
                container.innerHTML = html;
            } catch (error) {
                document.getElementById('bursarExpensesList').innerHTML = '<p class="text-gray-500">Failed to load expenses</p>';
            }
        }

        async function addExpense(e) {
            e.preventDefault();
            const btn = document.getElementById('addExpenseBtn');
            btn.innerHTML = '<span class="loading"></span> Adding...';
            btn.disabled = true;
            
            try {
                const result = await apiCall('add_expense', 'POST', {
                    category: document.getElementById('expenseCategory').value,
                    description: document.getElementById('expenseDescription').value,
                    amount: document.getElementById('expenseAmount').value,
                    expense_date: document.getElementById('expenseDate').value
                });
                
                showNotification('Expense added successfully');
                e.target.reset();
                loadBursarExpenses();
                loadBursarDashboard();
            } finally {
                btn.innerHTML = '<i class="fas fa-plus mr-2"></i>Add Expense';
                btn.disabled = false;
            }
        }

        async function generateReport(e) {
            e.preventDefault();
            const btn = document.getElementById('generateReportBtn');
            btn.innerHTML = '<span class="loading"></span> Generating...';
            btn.disabled = true;
            
            try {
                const result = await apiCall('generate_report', 'POST', {
                    report_type: document.getElementById('reportType').value,
                    from_date: document.getElementById('reportFromDate').value,
                    to_date: document.getElementById('reportToDate').value
                });
                
                showNotification('Report generated successfully');
                console.log('Report data:', result.data);
                // Could display report in a modal or download as CSV
            } finally {
                btn.innerHTML = '<i class="fas fa-file-alt mr-2"></i>Generate Report';
                btn.disabled = false;
            }
        }

        // Admin Dashboard Functions
        async function loadAdminDashboard() {
            if (!currentUser) return;
            
            document.getElementById('adminRoleDisplay').textContent = currentRole;
            
            try {
                const result = await apiCall('get_dashboard_stats');
                document.getElementById('adminTotalStudents').textContent = result.stats.students;
                document.getElementById('adminTotalStaff').textContent = result.stats.staff;
                
                // Load classes
                const classesResult = await apiCall('get_classes');
                allClasses = classesResult.classes;
                document.getElementById('adminTotalClasses').textContent = allClasses.length;
                
                // Populate class filters
                const filters = document.querySelectorAll('select[onchange*="filterAdminStudents"]');
                filters.forEach(filter => {
                    filter.innerHTML = '<option value="">All Classes</option>';
                    allClasses.forEach(cls => {
                        filter.innerHTML += `<option value="${cls.class_name}">${cls.class_name}</option>`;
                    });
                });
                
                // Load students and staff
                loadAdminStudents();
                loadAdminStaff();
                loadAdminClasses();
                loadAdminExams();
                loadAdminAnnouncements();
                
            } catch (error) {
                console.error('Failed to load admin dashboard');
            }
        }

        function showAdminSection(section) {
            document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
            document.getElementById('adminSection-' + section).style.display = 'block';
            
            if (section === 'students') loadAdminStudents();
            if (section === 'staff') loadAdminStaff();
            if (section === 'classes') loadAdminClasses();
            if (section === 'exams') loadAdminExams();
            if (section === 'announcements') loadAdminAnnouncements();
        }

        async function loadAdminStudents() {
            try {
                const result = await apiCall('get_students');
                allStudents = result.students;
                renderAdminStudents(result.students);
            } catch (error) {
                document.getElementById('adminStudentsTable').innerHTML = '<tr><td colspan="7" class="text-center py-4">Failed to load students</td></tr>';
            }
        }

        function renderAdminStudents(students) {
            const tbody = document.getElementById('adminStudentsTable');
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No students found</td></tr>';
                return;
            }
            
            let html = '';
            students.forEach(student => {
                const statusClass = student.status === 'active' ? 'badge-success' : 'badge-danger';
                
                html += `<tr>
                    <td>${student.reg_no}</td>
                    <td>${student.surname} ${student.first_name}</td>
                    <td>${student.class}</td>
                    <td>${student.gender}</td>
                    <td>${student.parent_phone}</td>
                    <td><span class="badge ${statusClass}">${student.status}</span></td>
                    <td>
                        <button class="text-green-600 hover:text-green-800 mr-2" onclick="viewStudent('${student.reg_no}')"><i class="fas fa-eye"></i></button>
                        <button class="text-blue-600 hover:text-blue-800 mr-2" onclick="editStudent('${student.reg_no}')"><i class="fas fa-edit"></i></button>
                        <button class="text-red-600 hover:text-red-800" onclick="deleteStudent('${student.reg_no}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }

        function searchAdminStudents(query) {
            const filtered = allStudents.filter(s => 
                s.surname.toLowerCase().includes(query.toLowerCase()) ||
                s.first_name.toLowerCase().includes(query.toLowerCase()) ||
                s.reg_no.toLowerCase().includes(query.toLowerCase())
            );
            renderAdminStudents(filtered);
        }

        function filterAdminStudents(className) {
            if (!className) {
                renderAdminStudents(allStudents);
            } else {
                const filtered = allStudents.filter(s => s.class === className);
                renderAdminStudents(filtered);
            }
        }

        async function loadAdminStaff() {
            try {
                const result = await apiCall('get_staff');
                allStaff = result.staff;
                renderAdminStaff(result.staff);
            } catch (error) {
                document.getElementById('adminStaffTable').innerHTML = '<tr><td colspan="8" class="text-center py-4">Failed to load staff</td></tr>';
            }
        }

        function renderAdminStaff(staff) {
            const tbody = document.getElementById('adminStaffTable');
            
            if (staff.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No staff found</td></tr>';
                return;
            }
            
            let html = '';
            staff.forEach(member => {
                const statusClass = member.status === 'active' ? 'badge-success' : 'badge-danger';
                
                html += `<tr>
                    <td>${member.staff_id}</td>
                    <td>${member.name}</td>
                    <td>${member.role}</td>
                    <td>${member.department || '-'}</td>
                    <td>${member.phone || '-'}</td>
                    <td>₦${parseFloat(member.basic_salary).toLocaleString()}</td>
                    <td><span class="badge ${statusClass}">${member.status}</span></td>
                    <td>
                        <button class="text-green-600 hover:text-green-800 mr-2" onclick="viewStaff('${member.staff_id}')"><i class="fas fa-eye"></i></button>
                        <button class="text-blue-600 hover:text-blue-800" onclick="editStaff('${member.staff_id}')"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }

        function searchAdminStaff(query) {
            const filtered = allStaff.filter(s => 
                s.name.toLowerCase().includes(query.toLowerCase()) ||
                s.staff_id.toLowerCase().includes(query.toLowerCase())
            );
            renderAdminStaff(filtered);
        }

        function filterAdminStaff(role) {
            if (!role) {
                renderAdminStaff(allStaff);
            } else {
                const filtered = allStaff.filter(s => s.role === role);
                renderAdminStaff(filtered);
            }
        }

        async function loadAdminClasses() {
            const container = document.getElementById('adminClassesGrid');
            
            if (allClasses.length === 0) {
                container.innerHTML = '<p class="text-gray-500 col-span-3">No classes found</p>';
                return;
            }
            
            let html = '';
            allClasses.forEach(cls => {
                html += `<div class="card p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">${cls.class_name}</h3>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <p class="text-gray-600 mb-2">Section: ${cls.section}</p>
                    <p class="text-gray-600 mb-4">Capacity: ${cls.capacity}</p>
                    <button class="btn-secondary w-full">View Details</button>
                </div>`;
            });
            
            container.innerHTML = html;
        }

        async function loadAdminExams() {
            try {
                const result = await apiCall('get_exams');
                const tbody = document.getElementById('adminExamsTable');
                
                if (result.exams.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No exams scheduled</td></tr>';
                    return;
                }
                
                let html = '';
                result.exams.forEach(exam => {
                    const statusClass = exam.status === 'completed' ? 'badge-success' : (exam.status === 'ongoing' ? 'badge-warning' : 'badge-warning');
                    
                    html += `<tr>
                        <td>${exam.exam_name}</td>
                        <td>${exam.term}</td>
                        <td>${exam.start_date}</td>
                        <td><span class="badge ${statusClass}">${exam.status}</span></td>
                        <td>
                            <button class="text-green-600 hover:text-green-800 mr-2"><i class="fas fa-eye"></i></button>
                            <button class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>`;
                });
                
                tbody.innerHTML = html;
            } catch (error) {
                document.getElementById('adminExamsTable').innerHTML = '<tr><td colspan="5" class="text-center py-4">Failed to load exams</td></tr>';
            }
        }

        async function createExam() {
            try {
                const result = await apiCall('create_exam', 'POST', {
                    exam_name: document.getElementById('newExamName').value,
                    term: document.getElementById('newExamTerm').value,
                    start_date: document.getElementById('newExamDate').value
                });
                
                showNotification('Exam created successfully');
                loadAdminExams();
            } catch (error) {
                showNotification('Failed to create exam', 'error');
            }
        }

        async function loadAdminAnnouncements() {
            try {
                const result = await apiCall('get_announcements');
                const container = document.getElementById('adminAnnouncementsList');
                
                if (result.announcements.length === 0) {
                    container.innerHTML = '<p class="text-gray-500">No announcements</p>';
                    return;
                }
                
                let html = '';
                result.announcements.forEach(ann => {
                    const date = new Date(ann.created_at).toLocaleDateString();
                    html += `<div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-600">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold">${ann.title}</h4>
                            <span class="text-xs text-gray-500">${date}</span>
                        </div>
                        <p class="text-sm text-gray-700">${ann.message}</p>
                    </div>`;
                });
                
                container.innerHTML = html;
            } catch (error) {
                document.getElementById('adminAnnouncementsList').innerHTML = '<p class="text-gray-500">Failed to load announcements</p>';
            }
        }

        async function createAnnouncement(e) {
            e.preventDefault();
            const btn = document.getElementById('postAnnouncementBtn');
            btn.innerHTML = '<span class="loading"></span> Posting...';
            btn.disabled = true;
            
            try {
                const result = await apiCall('create_announcement', 'POST', {
                    title: document.getElementById('announcementTitle').value,
                    message: document.getElementById('announcementMessage').value,
                    target_audience: document.getElementById('announcementTarget').value
                });
                
                showNotification('Announcement posted successfully');
                e.target.reset();
                loadAdminAnnouncements();
            } finally {
                btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Post Announcement';
                btn.disabled = false;
            }
        }

        // Utility Functions
        function viewStudent(regNo) {
            showNotification('View student: ' + regNo);
            // Could open a modal with full student details
        }

        function editStudent(regNo) {
            showNotification('Edit student: ' + regNo);
        }

        async function deleteStudent(regNo) {
            if (!confirm('Are you sure you want to remove this student?')) return;
            
            try {
                const result = await apiCall('delete_student', 'POST', { reg_no: regNo });
                showNotification('Student removed successfully');
                loadAdminStudents();
            } catch (error) {
                showNotification('Failed to remove student', 'error');
            }
        }

        function viewStaff(staffId) {
            showNotification('View staff: ' + staffId);
        }

        function editStaff(staffId) {
            showNotification('Edit staff: ' + staffId);
        }

        function showAddStudentModal() {
            showNotification('Add student modal - implement as needed');
        }

        function showAddStaffModal() {
            showNotification('Add staff modal - implement as needed');
        }

        function exportFeeReport() {
            showNotification('Exporting fee report...');
        }

        function printResult() {
            window.print();
        }
        // Paystack Payment Integration
let paystackPublicKey = '';

// Load Paystack public key on startup
async function loadPaystackKey() {
    try {
        const result = await apiCall('get_paystack_key');
        if (result.success) {
            paystackPublicKey = result.public_key;
        }
    } catch (error) {
        console.error('Failed to load Paystack key');
    }
}

// Initialize Paystack inline payment
function payWithPaystack(regNo, email, amount, paymentType, onSuccess, onError) {
    if (!paystackPublicKey) {
        showNotification('Payment system not ready. Please try again.', 'error');
        return;
    }

    const handler = PaystackPop.setup({
        key: paystackPublicKey,
        email: email,
        amount: amount * 100, // in kobo
        currency: 'NGN',
        ref: 'IBRA-' + Math.floor((Math.random() * 1000000000) + 1),
        metadata: {
            custom_fields: [
                {
                    display_name: "Student Registration Number",
                    variable_name: "reg_no",
                    value: regNo
                },
                {
                    display_name: "Payment Type",
                    variable_name: "payment_type",
                    value: paymentType
                }
            ]
        },
        callback: function(response) {
            // Verify payment on server
            verifyPaystackPayment(response.reference, onSuccess, onError);
        },
        onClose: function() {
            showNotification('Payment window closed', 'warning');
        }
    });

    handler.openIframe();
}

// Alternative: Server-side initialization (more secure)
async function initializeServerPayment(regNo, email, amount, paymentType) {
    try {
        const result = await apiCall('initialize_payment', 'POST', {
            reg_no: regNo,
            email: email,
            amount: amount,
            payment_type: paymentType
        });

        if (result.success) {
            // Redirect to Paystack checkout
            window.location.href = result.authorization_url;
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Failed to initialize payment', 'error');
    }
}

// Verify payment after callback
async function verifyPaystackPayment(reference, onSuccess, onError) {
    try {
        const result = await apiCall('verify_payment', 'GET', { reference: reference });
        
        if (result.success) {
            showNotification('Payment successful! Receipt: ' + result.receipt_no);
            if (onSuccess) onSuccess(result);
        } else {
            showNotification(result.message, 'error');
            if (onError) onError(result);
        }
    } catch (error) {
        showNotification('Payment verification failed', 'error');
        if (onError) onError(error);
    }
}

// Load Paystack key on startup
document.addEventListener('DOMContentLoaded', function() {
    loadPaystackKey();
});
    </script>  
</body>
</html>