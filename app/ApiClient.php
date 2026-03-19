<?php
// Classe à compléter pour communiquer avec l’API REST
class ApiClient {
    private $baseUrl = "http://localhost/Congrès/API_CONGRES/api.php?endpoint=";
    private function request(string $endpoint, string $method = "GET", $data = null): array {
        $url = $this->baseUrl . urlencode($endpoint);
        // si GET avec params -> construire query
        if ($method === 'GET' && is_array($data) && !empty($data)) {
            $url .= '&' . http_build_query($data);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                $payload = json_encode($data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            }
        }

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
        $err = curl_error($ch);
        curl_close($ch);

        $decoded = null;
        if ($raw !== false && $raw !== null && $raw !== '') {
            $tmp = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = $tmp;
            } else {
                $decoded = $raw;
            }
        }

        return [
            'status' => (int)$httpCode,
            'data'   => $decoded,
            'raw'    => $raw,
            'error'  => $err ?: null,
        ];
    }

    // public helper to call GET endpoints
    public function get(string $endpoint, array $params = []): array {
        return $this->request($endpoint, 'GET', $params);
    }

    public function findAll(): array {
        return $this->request('congressiste', 'GET');
    }

    public function find(int $id): array {
        return $this->request('congressiste', 'POST', ['id_congressiste' => $id]);
    }

    public function create(array $data): array {
        return $this->request('create', 'POST', $data);
    }

    public function update(array $data): array {
        return $this->request('update', 'POST', $data);
    }

    public function delete(int $id): array {
        return $this->request('delete', 'POST', ['id_congressiste' => $id]);
    }
}