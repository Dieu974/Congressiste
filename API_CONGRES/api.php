<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "Config/database.php";
require_once "Repository/congressisteRepository.php";

$database = new Database();
$db = $database->getConnexion();

$endpoint = $_GET["endpoint"] ?? "";
$method = $_SERVER["REQUEST_METHOD"];

switch ($endpoint) {

    case "congressiste":
        if ($method == "GET") {
            $repo = new congressisteRepository($db);
            $congs = $repo->findAll();
            echo json_encode($congs, JSON_PRETTY_PRINT);
            break;
        } elseif ($method == "POST") {
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            // simple find by id when posted
            $id = $data['id_congressiste'] ?? null;
            if ($id !== null) {
                $repo = new congressisteRepository($db);
                $cong = $repo->findById($id);
                if ($cong === null) {
                    http_response_code(404);
                    echo json_encode(["message" => "Congressiste non trouvé"]);
                } else {
                    echo json_encode($cong, JSON_PRETTY_PRINT);
                }
                break;
            }
        }
        break;

    case "create":
        if ($method == "POST") {
            // attente: body JSON avec champs => appeler repository create
            require_once "Repository/congressisteRepository.php";
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            $repo = new congressisteRepository($db);
            $result = $repo->create($data);
            if ($result === false) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Erreur lors de la création du congressiste.'
                ], JSON_PRETTY_PRINT);
            } else {
                http_response_code(201);
                echo json_encode([
                    'success' => true
                ], JSON_PRETTY_PRINT);
            }
        }
        break;

    case "update":
        if ($method == "POST") {
            require_once "Repository/congressisteRepository.php";
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            $repo = new congressisteRepository($db);
            $ok = $repo->update($data);
            echo json_encode(['success' => (bool)$ok], JSON_PRETTY_PRINT);
        }
        break;

    case "delete":
        if ($method == "POST") {
            require_once "Repository/congressisteRepository.php";
            $data = json_decode(file_get_contents("php://input"), true) ?? [];
            $id = $data['id_congressiste'] ?? null;
            $repo = new congressisteRepository($db);
            $ok = $repo->delete($id);
            echo json_encode(['success' => (bool)$ok], JSON_PRETTY_PRINT);
        }
        break;

    case "hotel":
        if ($method == "GET") {
            $repoFile = __DIR__ . "/Repository/hotelRepository.php";
            if (!file_exists($repoFile)) {
                http_response_code(500);
                echo json_encode(['message' => "Repository hotel manquant"], JSON_PRETTY_PRINT);
                break;
            }
            require_once $repoFile;
            if (!class_exists('hotelRepository') && !class_exists('HotelRepository')) {
                http_response_code(500);
                echo json_encode(['message' => "Classe hotelRepository introuvable dans {$repoFile}"], JSON_PRETTY_PRINT);
                break;
            }
            $class = class_exists('hotelRepository') ? 'hotelRepository' : 'HotelRepository';
            $repo = new $class($db);
            $hotels = $repo->findAll();
            echo json_encode($hotels, JSON_PRETTY_PRINT);
        }
        break;

    case "organisme":
        if ($method == "GET") {
            $repoFile = __DIR__ . "/Repository/organismeRepository.php";
            if (!file_exists($repoFile)) {
                http_response_code(500);
                echo json_encode(['message' => "Repository organisme manquant"], JSON_PRETTY_PRINT);
                break;
            }
            require_once $repoFile;
            if (!class_exists('organismeRepository') && !class_exists('OrganismeRepository')) {
                http_response_code(500);
                echo json_encode(['message' => "Classe organismeRepository introuvable dans {$repoFile}"], JSON_PRETTY_PRINT);
                break;
            }
            $class = class_exists('organismeRepository') ? 'organismeRepository' : 'OrganismeRepository';
            $repo = new $class($db);
            $organismes = $repo->findAll();
            echo json_encode($organismes, JSON_PRETTY_PRINT);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['message' => 'Endpoint inconnu'], JSON_PRETTY_PRINT);
        break;
}
