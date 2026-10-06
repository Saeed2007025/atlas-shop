<?php
/**
 * Customer Auth API
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/auth.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'send_otp') {
    check_honeypot();
    $mobile = $_POST['mobile'] ?? '';
    $res = send_otp($mobile);
    json_response($res);
}

if ($action === 'verify_otp') {
    check_honeypot();
    $mobile = $_POST['mobile'] ?? '';
    $code = $_POST['code'] ?? '';
    $res = verify_otp($mobile, $code);
    json_response($res);
}

if ($action === 'logout') {
    customer_logout();
    json_response(['success' => true, 'message' => 'خروج با موفقیت انجام شد.']);
}

json_response(['success' => false, 'message' => 'درخواست نامعتبر است.'], 400);
