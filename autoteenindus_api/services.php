<?php
session_start();
require_once "db.php";

header("Content-Type: application/json; charset=utf-8");

$method = $_SERVER['REQUEST_METHOD'];

// маленький хелпер: проверка, что вошёл админ
function require_admin() {
    if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode(array("error" => "Ainult adminil on lubatud see toiming."));
        exit;
    }
}

switch ($method) {
    // -------------------------------------------------------
    // GET /services.php           → kõik teenused
    // GET /services.php?id=1      → üks teenus
    // -------------------------------------------------------
    case 'GET':
        if (isset($_GET['id']) && $_GET['id'] !== '') {
            $id = (int)$_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute(array($id));
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {
                http_response_code(404);
                echo json_encode(array("error" => "Teenust ei leitud."));
            } else {
                echo json_encode($service);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM services ORDER BY id");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($services);
        }
        break;

    // -------------------------------------------------------
    // POST /services.php          → lisa uus teenus (ainult admin)
    // Body JSON: { "name": "...", "description": "..." }
    // -------------------------------------------------------
    case 'POST':
        require_admin();

        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        $name = isset($data['name']) ? trim($data['name']) : '';
        $description = isset($data['description']) ? trim($data['description']) : '';

        if ($name === '') {
            http_response_code(400);
            echo json_encode(array("error" => "Teenuse nimi on kohustuslik."));
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
        $stmt->execute(array($name, $description));

        http_response_code(201);
        echo json_encode(array(
            "message" => "Teenuse lisamine õnnestus.",
            "id"      => $pdo->lastInsertId()
        ));
        break;

    // -------------------------------------------------------
    // PUT /services.php?id=1      → uuenda teenust (ainult admin)
    // Body JSON: { "name": "...", "description": "..." }
    // -------------------------------------------------------
    case 'PUT':
        require_admin();

        if (empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(array("error" => "ID on kohustuslik (services.php?id=...)."));
            exit;
        }

        $id = (int)$_GET['id'];

        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        $name = isset($data['name']) ? trim($data['name']) : '';
        $description = isset($data['description']) ? trim($data['description']) : '';

        if ($name === '') {
            http_response_code(400);
            echo json_encode(array("error" => "Teenuse nimi on kohustuslik."));
            exit;
        }

        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ? WHERE id = ?");
        $stmt->execute(array($name, $description, $id));

        echo json_encode(array("message" => "Teenuse uuendamine õnnestus."));
        break;


    case 'DELETE':
        require_admin();

        if (empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(array("error" => "ID on kohustuslik (services.php?id=...)."));
            exit;
        }

        $id = (int)$_GET['id'];

        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute(array($id));

        echo json_encode(array("message" => "Teenuse kustutamine õnnestus."));
        break;

    default:
        http_response_code(405);
        echo json_encode(array("error" => "Meetod ei ole lubatud."));
}
