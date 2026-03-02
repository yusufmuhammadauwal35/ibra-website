<?php
/**
 * Paystack Payment Gateway Integration
 */

require_once 'config.php';

class PaystackAPI {
    private $secretKey;
    private $baseUrl;
    private $publicKey;

    public function __construct() {
        $this->secretKey = PAYSTACK_SECRET_KEY;
        $this->publicKey = PAYSTACK_PUBLIC_KEY;
        $this->baseUrl = PAYSTACK_BASE_URL;
    }

    /**
     * Initialize a transaction
     */
    public function initializeTransaction($email, $amount, $reference, $metadata = []) {
        $url = $this->baseUrl . "/transaction/initialize";
        
        $fields = [
            'email' => $email,
            'amount' => $amount * 100, // Paystack expects amount in kobo
            'reference' => $reference,
            'callback_url' => PAYSTACK_CALLBACK_URL,
            'metadata' => json_encode($metadata),
            'currency' => PAYMENT_CURRENCY
        ];

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * Verify a transaction
     */
    public function verifyTransaction($reference) {
        $url = $this->baseUrl . "/transaction/verify/" . $reference;
        return $this->makeRequest('GET', $url);
    }

    /**
     * List all transactions
     */
    public function listTransactions($perPage = 50, $page = 1) {
        $url = $this->baseUrl . "/transaction?perPage=" . $perPage . "&page=" . $page;
        return $this->makeRequest('GET', $url);
    }

    /**
     * Fetch a specific transaction
     */
    public function fetchTransaction($id) {
        $url = $this->baseUrl . "/transaction/" . $id;
        return $this->makeRequest('GET', $url);
    }

    /**
     * Create a customer
     */
    public function createCustomer($email, $firstName, $lastName, $phone = '') {
        $url = $this->baseUrl . "/customer";
        
        $fields = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ];

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * Create a payment plan (for installment payments)
     */
    public function createPlan($name, $amount, $interval, $description = '') {
        $url = $this->baseUrl . "/plan";
        
        $fields = [
            'name' => $name,
            'amount' => $amount * 100,
            'interval' => $interval, // daily, weekly, monthly, annually
            'description' => $description
        ];

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * Create a subscription
     */
    public function createSubscription($customer, $plan, $startDate = null) {
        $url = $this->baseUrl . "/subscription";
        
        $fields = [
            'customer' => $customer,
            'plan' => $plan
        ];
        
        if ($startDate) {
            $fields['start_date'] = $startDate;
        }

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * Create a transfer recipient (for staff salary payments)
     */
    public function createTransferRecipient($type, $name, $accountNumber, $bankCode, $currency = 'NGN') {
        $url = $this->baseUrl . "/transferrecipient";
        
        $fields = [
            'type' => $type, // nuban for bank accounts
            'name' => $name,
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
            'currency' => $currency
        ];

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * Initiate a transfer (for staff salaries)
     */
    public function initiateTransfer($amount, $recipient, $reason, $reference) {
        $url = $this->baseUrl . "/transfer";
        
        $fields = [
            'source' => 'balance',
            'amount' => $amount * 100,
            'recipient' => $recipient,
            'reason' => $reason,
            'reference' => $reference
        ];

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * Finalize a transfer (if OTP is required)
     */
    public function finalizeTransfer($transferCode, $otp) {
        $url = $this->baseUrl . "/transfer/finalize_transfer";
        
        $fields = [
            'transfer_code' => $transferCode,
            'otp' => $otp
        ];

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * List banks
     */
    public function listBanks($country = 'nigeria') {
        $url = $this->baseUrl . "/bank?country=" . $country;
        return $this->makeRequest('GET', $url);
    }

    /**
     * Resolve account number
     */
    public function resolveAccount($accountNumber, $bankCode) {
        $url = $this->baseUrl . "/bank/resolve?account_number=" . $accountNumber . "&bank_code=" . $bankCode;
        return $this->makeRequest('GET', $url);
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($input, $signature) {
        $computedSignature = hash_hmac('sha512', $input, $this->secretKey);
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Get public key (for frontend)
     */
    public function getPublicKey() {
        return $this->publicKey;
    }

    /**
     * Make HTTP request to Paystack API
     */
    private function makeRequest($method, $url, $fields = null) {
        $ch = curl_init();
        
        $headers = [
            "Authorization: Bearer " . $this->secretKey,
            "Content-Type: application/json",
            "Cache-Control: no-cache"
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($fields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($fields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['status' => false, 'message' => 'Curl Error: ' . $error];
        }

        curl_close($ch);

        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['status' => true, 'data' => $result['data'] ?? $result, 'message' => $result['message'] ?? 'Success'];
        } else {
            return ['status' => false, 'message' => $result['message'] ?? 'Request failed', 'data' => $result];
        }
    }
}

// Initialize Paystack API
$paystack = new PaystackAPI();
?>