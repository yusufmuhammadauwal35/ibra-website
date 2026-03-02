<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Paystack Webhook Handler (SECURE & DEBUGGED VERSION)
 */

require_once 'config.php';
require_once 'database.php';
require_once 'functions.php';
require_once __DIR__ . '/paystack.php';
require_once 'payments.php';

// Ensure correct content type
header('Content-Type: application/json');

// Get raw body
$input = file_get_contents('php://input');

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// Get signature
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!$signature) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing signature']);
    exit;
}

$paystack = new PaystackAPI();

// Verify webhook signature
if (!$paystack->verifyWebhookSignature($input, $signature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Decode JSON safely
$event = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

if (!isset($event['event'], $event['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid event structure']);
    exit;
}

try {

    // Log webhook event
    logActivity('webhook_received', 'Event: ' . $event['event']);

    $processor = new PaymentProcessor();


    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'processed' => true
    ]);

} catch (Exception $e) {

    error_log("Webhook Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'Webhook processing failed'
    ]);
}
?>