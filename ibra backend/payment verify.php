<?php
/**
 * Payment Verification Page
 * Handles callback from Paystack after payment
 */

require_once 'config.php';
require_once 'payments.php';

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    die('No reference provided');
}

$processor = new PaymentProcessor();
$result = $processor->verifyPayment($reference);

// Redirect based on result
if ($result['success']) {
    // Success - redirect to student dashboard with success message
    header('Location: /?page=student-dashboard&payment=success&receipt=' . $result['receipt_no']);
} else {
    // Failed - redirect with error
    header('Location: /?page=student-dashboard&payment=failed&message=' . urlencode($result['message']));
}
exit;
?>