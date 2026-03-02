<?php
/**
 * Payment Processing Functions (FIXED & DEBUGGED VERSION)
 */

require_once 'database.php';
require_once 'paystack.php';
require_once 'functions.php';

class PaymentProcessor {
    private $db;
    private $paystack;

    public function __construct() {
        $this->db = getDB();
        $this->paystack = new PaystackAPI();
    }

    /**
     * Initialize a student fee payment
     */
    public function initializeFeePayment($regNo, $paymentType, $amount, $email, $callbackUrl = null) {

        $stmt = $this->db->prepare("SELECT * FROM students WHERE reg_no = ?");
        $stmt->execute([$regNo]);
        $student = $stmt->fetch();

        if (!$student) {
            return ['success' => false, 'message' => 'Student not found'];
        }

        // OPTIONAL: Validate amount from database (ANTI-TAMPERING)
        /*
        $validAmountStmt = $this->db->prepare("
            SELECT amount FROM fee_structure 
            WHERE class = ? AND payment_type = ?
        ");
        $validAmountStmt->execute([$student['class'], $paymentType]);
        $validAmount = $validAmountStmt->fetchColumn();

        if (!$validAmount || $validAmount != $amount) {
            return ['success' => false, 'message' => 'Invalid payment amount'];
        }
        */

        $reference = 'IBRA-' . uniqid() . '-' . time();

        $stmt = $this->db->prepare("
            INSERT INTO paystack_payments (
                reference, reg_no, payment_type, amount, email, status, created_at
            ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$reference, $regNo, $paymentType, $amount, $email]);

        $metadata = [
            'student_name' => $student['surname'] . ' ' . $student['first_name'],
            'student_class' => $student['class'],
            'payment_type' => $paymentType,
            'reg_no' => $regNo
        ];

        $result = $this->paystack->initializeTransaction(
            $email,
            $amount,
            $reference,
            $metadata
        );

        if ($result['status']) {

            $stmt = $this->db->prepare("
                UPDATE paystack_payments 
                SET access_code = ?, transaction_url = ?
                WHERE reference = ?
            ");
            $stmt->execute([
                $result['data']['access_code'],
                $result['data']['authorization_url'],
                $reference
            ]);

            return [
                'success' => true,
                'authorization_url' => $result['data']['authorization_url'],
                'access_code' => $result['data']['access_code'],
                'reference' => $reference
            ];
        }

        $stmt = $this->db->prepare("
            UPDATE paystack_payments 
            SET status = 'failed', gateway_response = ?
            WHERE reference = ?
        ");
        $stmt->execute([$result['message'], $reference]);

        return ['success' => false, 'message' => $result['message']];
    }

    /**
     * Verify and process payment
     */
    public function verifyPayment($reference) {

        $stmt = $this->db->prepare("SELECT * FROM paystack_payments WHERE reference = ?");
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        if ($payment['status'] === 'completed') {
            return ['success' => true, 'message' => 'Payment already processed'];
        }

        $result = $this->paystack->verifyTransaction($reference);

        if (!$result['status']) {
            return ['success' => false, 'message' => 'Verification failed'];
        }

        $transactionData = $result['data'];

        $gatewayStatus = $transactionData['status'];
        $status = ($gatewayStatus === 'success') ? 'completed' : 'failed';

        $this->db->beginTransaction();

        try {

            $stmt = $this->db->prepare("
                UPDATE paystack_payments SET
                    status = ?,
                    transaction_id = ?,
                    gateway_response = ?,
                    paid_at = ?,
                    channel = ?,
                    card_type = ?,
                    last4 = ?,
                    bank = ?,
                    verified_at = NOW()
                WHERE reference = ?
            ");

            $stmt->execute([
                $status,
                $transactionData['id'],
                $transactionData['gateway_response'] ?? null,
                $transactionData['paid_at'] ?? null,
                $transactionData['channel'] ?? null,
                $transactionData['authorization']['card_type'] ?? null,
                $transactionData['authorization']['last4'] ?? null,
                $transactionData['authorization']['bank'] ?? null,
                $reference
            ]);

            if ($status === 'completed') {

                // Prevent duplicate receipts
                $check = $this->db->prepare("SELECT id FROM payments WHERE gateway_reference = ?");
                $check->execute([$reference]);

                if (!$check->fetch()) {

                    $receiptNo = generateReceiptNo();

                    $stmt = $this->db->prepare("
                        INSERT INTO payments (
                            payment_type, reg_no, amount, description, payment_method,
                            receipt_no, status, payment_date, gateway_reference, transaction_id
                        ) VALUES (?, ?, ?, ?, 'Paystack', ?, 'completed', NOW(), ?, ?)
                    ");

                    $stmt->execute([
                        $payment['payment_type'],
                        $payment['reg_no'],
                        $payment['amount'],
                        $payment['payment_type'] . ' payment via Paystack',
                        $receiptNo,
                        $reference,
                        $transactionData['id']
                    ]);

                    $this->confirmStudentRegistration($payment['reg_no']);
                }
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error'];
        }

        return [
            'success' => ($status === 'completed'),
            'message' => ($status === 'completed') ? 'Payment verified successfully' : 'Payment failed'
        ];
    }

    /**
     * Confirm student registration
     */
    private function confirmStudentRegistration($regNo) {

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM payments 
            WHERE reg_no = ? AND payment_type = 'Registration Fee' AND status = 'completed'
        ");
        $stmt->execute([$regNo]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            $stmt = $this->db->prepare("
                UPDATE students SET status = 'active', confirmed_at = NOW() 
                WHERE reg_no = ?
            ");
            $stmt->execute([$regNo]);
        }
    }

    /**
     * Initialize salary transfer
     */
    public function initializeSalaryTransfer($staffId, $amount, $bankCode, $accountNumber, $narration) {

        $stmt = $this->db->prepare("SELECT * FROM staff WHERE staff_id = ?");
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch();

        if (!$staff) {
            return ['success' => false, 'message' => 'Staff not found'];
        }

        $recipientResult = $this->paystack->createTransferRecipient(
            'nuban',
            $staff['name'],
            $accountNumber,
            $bankCode
        );

        if (!$recipientResult['status']) {
            return ['success' => false, 'message' => 'Recipient creation failed'];
        }

        $recipientCode = $recipientResult['data']['recipient_code'];

        $reference = 'SALARY-' . $staffId . '-' . date('Y-m') . '-' . uniqid();

        $basic = $staff['basic_salary'] ?? 0;
        $allowances = $staff['allowances'] ?? 0;
        $deductions = $staff['deductions'] ?? 0;

        $netSalary = $basic + $allowances - $deductions;

        $stmt = $this->db->prepare("
            INSERT INTO salaries (
                staff_id, month_year, basic_salary, allowances, deductions, 
                net_salary, status, reference, recipient_code
            ) VALUES (?, ?, ?, ?, ?, ?, 'processing', ?, ?)
        ");

        $stmt->execute([
            $staffId,
            date('Y-m'),
            $basic,
            $allowances,
            $deductions,
            $netSalary,
            $reference,
            $recipientCode
        ]);

        $transferResult = $this->paystack->initiateTransfer(
            $netSalary,
            $recipientCode,
            $narration,
            $reference
        );

        if ($transferResult['status']) {

            $stmt = $this->db->prepare("
                UPDATE salaries SET transfer_code = ? WHERE reference = ?
            ");
            $stmt->execute([$transferResult['data']['transfer_code'], $reference]);

            return [
                'success' => true,
                'message' => 'Transfer initiated',
                'reference' => $reference
            ];
        }

        $stmt = $this->db->prepare("
            UPDATE salaries SET status = 'failed', failure_reason = ? WHERE reference = ?
        ");
        $stmt->execute([$transferResult['message'], $reference]);

        return ['success' => false, 'message' => 'Transfer failed'];
    }

    public function getPublicKey() {
        return $this->paystack->getPublicKey();
    }
    public function listBanks() {
    return $this->paystack->listBanks();
}
}

$paymentProcessor = new PaymentProcessor();
?>