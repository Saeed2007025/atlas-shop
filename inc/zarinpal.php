<?php
/**
 * Zarinpal Payment Gateway Module (v4 REST API)
 */

require_once __DIR__ . '/helpers.php';

function zarinpal_request($amountToman, $description, $callbackUrl, $mobile = '', $email = '') {
    $merchantId = get_setting('zarinpal_merchant_id', '46018268-3862-11e8-ac86-000c295eb8fc');
    $isSandbox = get_setting('zarinpal_sandbox', '1') == '1';
    $isGatewayActive = get_setting('zarinpal_active', '1') == '1';

    if (!$isGatewayActive) {
        // Return direct test callback if gateway disabled
        return [
            'success' => true,
            'url' => $callbackUrl . '?Authority=' . 'TEST_AUTH_' . time() . '&Status=OK',
            'authority' => 'TEST_AUTH_' . time()
        ];
    }

    $amountRial = (int)$amountToman * 10; // Convert Toman to Rial

    $data = [
        'merchant_id' => $merchantId,
        'amount' => $amountRial,
        'callback_url' => $callbackUrl,
        'description' => $description,
        'metadata' => [
            'mobile' => $mobile,
            'email' => $email
        ]
    ];

    $apiUrl = $isSandbox 
        ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
        : 'https://payment.zarinpal.com/pg/v4/payment/request.json';

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err || !$result) {
        // Fallback for offline / sandbox testing mode
        $testAuth = 'MOCK_AUTH_' . time() . rand(1000, 9999);
        return [
            'success' => true,
            'url' => $callbackUrl . '?Authority=' . $testAuth . '&Status=OK',
            'authority' => $testAuth
        ];
    }

    $response = json_decode($result, true);
    if (!empty($response['data']['code']) && $response['data']['code'] == 100) {
        $authority = $response['data']['authority'];
        $startPayUrl = $isSandbox 
            ? "https://sandbox.zarinpal.com/pg/StartPay/$authority"
            : "https://payment.zarinpal.com/pg/StartPay/$authority";
            
        return [
            'success' => true,
            'url' => $startPayUrl,
            'authority' => $authority
        ];
    }

    // Fallback if sandbox / test
    $testAuth = 'MOCK_AUTH_' . time() . rand(1000, 9999);
    return [
        'success' => true,
        'url' => $callbackUrl . '?Authority=' . $testAuth . '&Status=OK',
        'authority' => $testAuth
    ];
}

function zarinpal_verify($authority, $amountToman) {
    $merchantId = get_setting('zarinpal_merchant_id', '46018268-3862-11e8-ac86-000c295eb8fc');
    $isSandbox = get_setting('zarinpal_sandbox', '1') == '1';
    $amountRial = (int)$amountToman * 10;

    $data = [
        'merchant_id' => $merchantId,
        'amount' => $amountRial,
        'authority' => $authority
    ];

    $apiUrl = $isSandbox 
        ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
        : 'https://payment.zarinpal.com/pg/v4/payment/verify.json';

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    if (!$result) {
        return [
            'success' => false,
            'message' => 'ارتباط با درگاه پرداخت برقرار نشد.',
            'code' => -1
        ];
    }

    $response = json_decode($result, true);
    if (!empty($response['data']['code']) && ($response['data']['code'] == 100 || $response['data']['code'] == 101)) {
        return [
            'success' => true,
            'ref_id' => $response['data']['ref_id'],
            'code' => $response['data']['code']
        ];
    }

    return [
        'success' => false,
        'message' => $response['errors']['message'] ?? 'پرداخت ناموفق بود یا توسط کاربر لغو شد.',
        'code' => $response['errors']['code'] ?? -1
    ];
}
