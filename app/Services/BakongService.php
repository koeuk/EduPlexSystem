<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BakongService
{
    protected string $baseUrl;
    protected ?string $merchantId;
    protected string $merchantName;
    protected ?string $apiToken;
    protected ?string $accountId;
    protected string $acquiringBank;
    protected string $currency;
    protected bool $testMode;

    public function __construct()
    {
        $this->baseUrl = config('bakong.base_url', 'https://api-bakong.nbc.gov.kh');
        $this->merchantId = config('bakong.merchant_id');
        $this->merchantName = config('bakong.merchant_name', 'EduPlex');
        $this->apiToken = config('bakong.api_token');
        $this->accountId = config('bakong.account_id');
        $this->acquiringBank = config('bakong.acquiring_bank', 'EduPlex');
        $this->currency = config('bakong.currency', 'USD');
        $this->testMode = config('bakong.test_mode', true);
    }

    /**
     * Generate KHQR payment data
     */
    public function generateKHQR(array $params): array
    {
        $transactionId = $this->generateTransactionId();

        $qrData = [
            'merchant_id' => $this->merchantId,
            'merchant_name' => $this->merchantName,
            'account_id' => $this->accountId,
            'acquiring_bank' => $this->acquiringBank,
            'currency' => $params['currency'] ?? $this->currency,
            'amount' => $params['amount'],
            'transaction_id' => $transactionId,
            'bill_number' => $params['bill_number'] ?? $transactionId,
            'store_label' => $params['store_label'] ?? 'EduPlex Course',
            'terminal_label' => $params['terminal_label'] ?? 'ONLINE',
            'purpose' => $params['purpose'] ?? 'Course Payment',
        ];

        // In production, call Bakong API to generate KHQR
        if (!$this->testMode) {
            return $this->callBakongAPI('/v1/generate_khqr', $qrData);
        }

        // Test mode: Generate a simulated KHQR string
        return [
            'success' => true,
            'data' => [
                'qr_string' => $this->generateTestKHQRString($qrData),
                'transaction_id' => $transactionId,
                'amount' => $qrData['amount'],
                'currency' => $qrData['currency'],
                'merchant_name' => $qrData['merchant_name'],
                'expires_at' => now()->addMinutes(15)->toIso8601String(),
                'md5' => md5($transactionId . $qrData['amount']),
            ],
        ];
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $transactionId): array
    {
        if (!$this->testMode) {
            return $this->callBakongAPI('/v1/check_transaction_by_md5', [
                'md5' => $transactionId,
            ]);
        }

        // Test mode: Return pending status (can be overridden for testing)
        return [
            'success' => true,
            'data' => [
                'transaction_id' => $transactionId,
                'status' => 'PENDING', // PENDING, SUCCESS, FAILED, EXPIRED
                'message' => 'Waiting for payment',
            ],
        ];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('bakong.webhook_secret');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Generate unique transaction ID
     */
    public function generateTransactionId(): string
    {
        return 'EDU' . date('Ymd') . strtoupper(Str::random(8));
    }

    /**
     * Call Bakong API
     */
    protected function callBakongAPI(string $endpoint, array $data): array
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(config('bakong.timeout'))
                ->post($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Bakong API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Bakong API request failed',
                'message' => $response->json('message') ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('Bakong API Exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Bakong API connection failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate test KHQR string (EMVCo format simulation)
     */
    protected function generateTestKHQRString(array $data): string
    {
        // This is a simplified KHQR format for testing
        // Real KHQR follows EMVCo QR Code specification
        $parts = [
            '00020101', // Payload Format Indicator
            '010212', // Point of Initiation (Dynamic)
            '29' . $this->formatTLV('00', 'bakong'), // Merchant Account Info
            '52040000', // Merchant Category Code
            '5303' . ($data['currency'] === 'USD' ? '840' : '116'), // Transaction Currency
            '54' . str_pad(strlen($data['amount']), 2, '0', STR_PAD_LEFT) . $data['amount'], // Amount
            '5802KH', // Country Code
            '59' . str_pad(strlen($data['merchant_name']), 2, '0', STR_PAD_LEFT) . $data['merchant_name'],
            '62' . $this->formatTLV('05', $data['transaction_id']), // Additional Data
        ];

        $qrString = implode('', $parts);

        // Add CRC (simplified)
        $qrString .= '6304';
        $crc = strtoupper(dechex(crc32($qrString) & 0xFFFF));
        $qrString .= str_pad($crc, 4, '0', STR_PAD_LEFT);

        return $qrString;
    }

    /**
     * Format TLV (Tag-Length-Value)
     */
    protected function formatTLV(string $tag, string $value): string
    {
        $length = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    /**
     * Parse KHQR amount from QR string
     */
    public function parseKHQRAmount(string $qrString): ?float
    {
        // Extract amount from KHQR string (tag 54)
        if (preg_match('/54(\d{2})(\d+\.?\d*)/', $qrString, $matches)) {
            return (float) $matches[2];
        }
        return null;
    }

    /**
     * Check if service is in test mode
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }
}
