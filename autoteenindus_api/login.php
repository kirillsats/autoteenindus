<?php
session_start();
require_once "db.php";

header("Content-Type: application/json; charset=utf-8");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(array("error" => "Email ja parool on kohustuslikud."));
    exit;
}

// Ищем пользователя по email
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(array($email));
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(array("error" => "Vale email või parool."));
    exit;
}

// Проверяем пароль (у нас хранится как текст)
if ($user['password'] !== $password) {
    http_response_code(401);
    echo json_encode(array("error" => "Vale email või parool."));
    exit;
}

// Всё ок – авторизован, запускаем сессию
$_SESSION['user_id'] = $user['id'];
$_SESSION['email']   = $user['email'];
$_SESSION['is_admin'] = ($user['email'] === 'admin'); // наш простой критерий админа

echo json_encode(array(
    "message" => "Login õnnestus.",
    "user"    => array(
        "id"      => $user['id'],
        "email"   => $user['email'],
        "is_admin"=> $_SESSION['is_admin']
    )
));
