<?php

define('TARGET_URL', base64_decode('aHR0cHM6Ly9mZmJkdG9wdXAtY29tLnZlcmNlbC5hcHAv'));
define('REQUEST_TIMEOUT', 15);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(405, array('ok' => false, 'message' => 'method_not_allowed'));
}

$uid = isset($_GET['uid']) ? trim($_GET['uid']) : '';

if (!preg_match('/^[0-9]{6,15}$/', $uid)) {
    respond(422, array('ok' => false, 'message' => 'uid_must_contain_6_to_15_digits'));
}

$token = 'c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2b9c1047a3c5f8e6d2';

$curl = curl_init(TARGET_URL);

if ($curl === false) {
    respond(500, array('ok' => false, 'message' => 'request_initialization_failed'));
}

curl_setopt_array($curl, array(
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(array(
        'id' => $uid,
        '_token' => $token
    )),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json'
    ),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => REQUEST_TIMEOUT,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
));

$response = curl_exec($curl);
$httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($response === '' || $httpStatus === 404 || $httpStatus === 403 || $httpStatus === 405) {
    respond(502, array(
        'ok' => false,
        'message' => 'target_blocked_request',
        'http_status' => $httpStatus
    ));
}

$result = json_decode($response, true);

if (!is_array($result)) {
    respond(502, array(
        'ok' => false,
        'message' => 'invalid_target_response',
        'raw_response' => $response
    ));
}

http_response_code($httpStatus);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>
