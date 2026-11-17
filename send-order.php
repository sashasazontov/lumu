<?php
header('Content-Type: application/json; charset=utf-8');

// ================== НАСТРОЙКИ ==================
$botToken = '9025764038:AAEeZ7PfgAnyu7569NchttNUxFXkuQoTdZc';  // токен от @BotFather вида 123456789:AAH...
$chatId   = '-1003907000620';          // id группы, с минусом
// ===============================================

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Нет данных']);
    exit;
}

$table = $data['table'] ?? '?';
$total = $data['total'] ?? 0;

$lines = "🛎 Заказ со стола {$table}\n\n";
foreach ($data['items'] as $item) {
    $name  = $item['name'] ?? '?';
    $size  = !empty($item['size']) ? " ({$item['size']})" : '';
    $note  = !empty($item['note']) ? " — {$item['note']}" : '';
    $qty   = $item['qty'] ?? 1;
    $price = $item['price'] ?? 0;
    $lines .= "• {$name}{$size}{$note} ×{$qty} — {$price} ₽\n";
}
$lines .= "\n💵 Итого: {$total} ₽";

$apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'chat_id' => $chatId,
    'text'    => $lines,
]));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['status' => 'error', 'message' => 'cURL: ' . $curlErr]);
    exit;
}

$telegram = json_decode($response, true);

if (!empty($telegram['ok'])) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => $telegram['description'] ?? 'Telegram не принял запрос'
    ]);
}
