<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    protected $merchantCode;
    protected $apiKey;
    protected $privateKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->merchantCode = config('services.tripay.merchant_code');
        $this->apiKey = config('services.tripay.api_key');
        $this->privateKey = config('services.tripay.private_key');
        $this->baseUrl = config('services.tripay.base_url');
    }

    /**
     * Get available payment channels
     */
    public function getPaymentChannels()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/merchant/payment-channel');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data']
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to get payment channels'
            ];
        } catch (\Exception $e) {
            Log::error('Tripay Get Channels Error', [
                'message' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create payment transaction
     */
    public function createTransaction($data)
    {
        // Generate unique merchant_ref
        $merchantRef = 'INV-' . time() . '-' . rand(1000, 9999);

        // IMPORTANT: Signature harus sesuai format Tripay
        // Format: merchantCode + merchantRef + amount
        $signatureString = $this->merchantCode . $merchantRef . $data['amount'];
        $signature = hash_hmac('sha256', $signatureString, $this->privateKey);

        Log::info('Tripay Signature Debug', [
            'merchant_code' => $this->merchantCode,
            'merchant_ref' => $merchantRef,
            'amount' => $data['amount'],
            'signature_string' => $signatureString,
            'signature' => $signature,
            'private_key' => substr($this->privateKey, 0, 10) . '...'
        ]);

        $payload = [
            'method' => $data['payment_method'],
            'merchant_ref' => $merchantRef,
            'amount' => (int) $data['amount'], // Harus integer
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'order_items' => $data['order_items'],
            'return_url' => $data['return_url'] ?? url('/payments/return'),
            'expired_time' => (time() + (24 * 60 * 60)), // 24 hours from now
            'signature' => $signature
        ];

        Log::info('Tripay Create Transaction Request', [
            'url' => $this->baseUrl . '/transaction/create',
            'payload' => $payload
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/transaction/create', $payload);

            Log::info('Tripay Create Transaction Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data']
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to create transaction'
            ];
        } catch (\Exception $e) {
            Log::error('Tripay Create Transaction Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get transaction detail
     */
    public function getTransactionDetail($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/transaction/detail', [
                'reference' => $reference
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data']
                ];
            }

            return ['success' => false, 'message' => 'Transaction not found'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate callback signature
     */
    public function validateCallbackSignature($callbackSignature, $data)
    {
        ksort($data); // Sort array by key
        $jsonData = json_encode($data);
        $signature = hash_hmac('sha256', $jsonData, $this->privateKey);

        return hash_equals($signature, $callbackSignature);
    }

    /**
     * Handle payment callback
     */
    public function handleCallback($request)
    {
        $callbackSignature = $request->header('X-Callback-Signature');
        $data = $request->all();

        Log::info('Tripay Callback Received', [
            'signature_header' => $callbackSignature,
            'data' => $data
        ]);

        // Validate signature
        if (!$this->validateCallbackSignature($callbackSignature, $data)) {
            Log::error('Tripay Callback Invalid Signature');
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        return [
            'success' => true,
            'reference' => $data['reference'],
            'merchant_ref' => $data['merchant_ref'],
            'status' => $data['status'],
            'amount' => $data['amount_received'],
            'paid_at' => $data['paid_at'] ?? null
        ];
    }
}
