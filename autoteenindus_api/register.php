<?php
require_once "db.php";

header("Content-Type: application/json; charset=utf-8");

// Читаем JSON из тела запроса
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(array("error" => "Email ja parool on kohustuslikud."));
    exit;
}

// Проверим, нет ли уже такого пользователя
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(array($email));
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    http_response_code(409); // Conflict
    echo json_encode(array("error" => "Kasutaja sellise emailiga on juba olemas."));
    exit;
}

// Сохраняем пароль как есть (для простоты), можно было бы hash'ить
$stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->execute(array($email, $password));

http_response_code(201);
echo json_encode(array(
    "message" => "Kasutaja loodud edukalt.",
    "email"   => $email
));
