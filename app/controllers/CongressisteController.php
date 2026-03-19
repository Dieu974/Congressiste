<?php
require_once __DIR__ . "/../ApiClient.php";
class CongressisteController {

    private $apiClient;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->apiClient = new ApiClient();
    }
    
    public function list() {
        $resp = $this->apiClient->findAll();
        // $resp contains ['status'=>code, 'data'=>array]
        $items = $resp['data'] ?? [];
        include __DIR__ . '/../views/congressiste/list.php';
    }

    public function create() {
        $errors = [];
        $values = $_POST ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($values['nom'] ?? '');
            $prenom = trim($values['prenom'] ?? '');
            $email = trim($values['email'] ?? '');

            if ($nom === '') $errors[] = 'Nom requis';
            if ($prenom === '') $errors[] = 'Prénom requis';
            if ($email === '') $errors[] = 'Email requis';

            if (empty($errors)) {
                $payload = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'adresse' => $values['adresse'] ?? null,
                    'email' => $email,
                    'hotel_id' => $values['hotel_id'] ?? null,
                    'organisme_id' => $values['organisme_id'] ?? null,
                    'acompte' => isset($values['acompte']) ? (int)$values['acompte'] : null,
                    'supplement_petit_dejeuner' => $values['supplement_petit_dejeuner'] ?? null,
                    'nb_etoile_souhaite' => $values['nb_etoile_souhaite'] ?? null,
                    // password not in form by default — include if provided
                    'password' => $values['password'] ?? null
                ];

                $url = 'http://localhost/Congrès/API_CONGRES/api.php?endpoint=create';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                $resp = curl_exec($ch);
                $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $json = json_decode($resp, true) ?: [];

                if ($http === 201 || (isset($json['message']) && stripos($json['message'],'ajout') !== false)) {
                    header('Location: index.php?c=congressiste&a=list&created=1');
                    exit;
                } else {
                    $errors[] = $json['message'] ?? 'Erreur API lors de la création (HTTP '.$http.')';
                }
            }
        }

        $hotelsResp = $this->apiClient->get('hotel');
        $hotels = is_array($hotelsResp['data']) ? $hotelsResp['data'] : [];

        $orgResp = $this->apiClient->get('organisme');
        $organismes = is_array($orgResp['data']) ? $orgResp['data'] : [];

        include __DIR__ . '/../views/congressiste/form.php';
    }

    public function edit() {
        $errors = [];
        $values = $_POST ?? [];
        $id = $_GET['id'] ?? $values['id_congressiste'] ?? $values['id'] ?? null;
        $item = null;

        // si POST => appeler API update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $payload = [
                'id_congressiste' => (int)$id,
                'nom' => trim($values['nom'] ?? ''),
                'prenom' => trim($values['prenom'] ?? ''),
                'adresse' => $values['adresse'] ?? null,
                'email' => trim($values['email'] ?? ''),
                'hotel_id' => $values['hotel_id'] ?? null,
                'organisme_id' => $values['organisme_id'] ?? null,
                'acompte' => isset($values['acompte']) ? (int)$values['acompte'] : null,
                'supplement_petit_dejeuner' => $values['supplement_petit_dejeuner'] ?? null,
                'nb_etoile_souhaite' => $values['nb_etoile_souhaite'] ?? null,
                'password' => $values['password'] ?? null
            ];

            $url = 'http://localhost/Congrès/API_CONGRES/api.php?endpoint=update';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $resp = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $json = json_decode($resp, true) ?: [];

            if ($http === 200 || (isset($json['message']) && stripos($json['message'],'ajout') === false && stripos($json['message'],'non') === false)) {
                header('Location: index.php?c=congressiste&a=list&updated=1');
                exit;
            } else {
                $errors[] = $json['message'] ?? 'Erreur API lors de la mise à jour (HTTP '.$http.')';
                $values = $payload;
            }
        } else {
            // GET : charger l'item depuis la base directement pour préremplissage
            if ($id) {
                require_once __DIR__ . '/../../API_CONGRES/Config/database.php';
                $database = new Database();
                $db = $database->getConnexion();
                $stmt = $db->prepare('SELECT * FROM congressiste WHERE id_congressiste = :id LIMIT 1');
                $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
                $stmt->execute();
                $item = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                $values = $item ?: $values;
            }
        }

        $hotelsResp = $this->apiClient->get('hotel');
        $hotels = is_array($hotelsResp['data']) ? $hotelsResp['data'] : [];

        $orgResp = $this->apiClient->get('organisme');
        $organismes = is_array($orgResp['data']) ? $orgResp['data'] : [];

        include __DIR__ . '/../views/congressiste/form.php';
    }

    public function delete() {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: index.php?c=congressiste&a=list&deleted=0');
            exit;
        }

        // premier essai via ApiClient
        try {
            $resp = $this->apiClient->delete($id);
        } catch (Throwable $e) {
            error_log("ApiClient->delete exception: " . $e->getMessage());
            $resp = null;
        }

        // si ApiClient n'a pas renvoyé un tableau valide, fallback curl vers l'API
        if (!is_array($resp)) {
            $url = 'http://localhost/Congrès/API_CONGRES/api.php?endpoint=delete';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id_congressiste' => $id, 'id' => $id]));
            $body = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            error_log("Fallback delete curl http=$http body=" . substr($body ?? '',0,100) . " err=$curlErr");
            $resp = json_decode($body, true) ?: ['status' => $http, 'body' => $body];
        }

        $status = (int)($resp['status'] ?? $resp['code'] ?? 0);

        if ($status === 200 || $status === 204 || ($resp === true) || (isset($resp['message']) && stripos($resp['message'],'supprim') !== false)) {
            header('Location: index.php?c=congressiste&a=list&deleted=1');
            exit;
        }

        // défaut : échec — log pour debug et redirige avec flag
        error_log('Delete failed resp: ' . json_encode($resp));
        header('Location: index.php?c=congressiste&a=list&deleted=0');
        exit;
    }
    
}