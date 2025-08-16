<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// URLパラメータからタイルのパスを取得
$z = $_GET['z'] ?? '';
$x = $_GET['x'] ?? '';
$y = $_GET['y'] ?? '';

if (empty($z) || empty($x) || empty($y)) {
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

// MVTファイルのURL
$url = "https://wata909.sakura.ne.jp/read-the-land/data/tile/veg2024_kanto/{$z}/{$x}/{$y}.mvt";

// cURLでリクエスト
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; open-hinata-proxy)');
curl_setopt($ch, CURLOPT_HEADER, false);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);
curl_close($ch);

if ($result === false || !empty($error)) {
    http_response_code(500);
    echo 'Proxy error: ' . $error;
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo 'HTTP error: ' . $httpCode;
    exit;
}

// MVTファイルかどうかを検証
$fileSize = strlen($result);
if ($fileSize < 10) {
    http_response_code(404);
    echo 'Empty or invalid MVT file';
    exit;
}

// ログを出力（デバッグ用）
error_log("MVT Proxy: z={$z}, x={$x}, y={$y}, size={$fileSize} bytes, content-type={$contentType}");

// 正しいContent-Typeを設定
header('Content-Type: application/x-protobuf');
header('Content-Length: ' . $fileSize);

// MVTデータを出力
echo $result;
?>
