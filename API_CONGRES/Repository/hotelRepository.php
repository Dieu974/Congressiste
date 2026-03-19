<?php
require_once __DIR__."/../Class/hotel.php";
class hotelRepository {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function findAll() {
        $sql = "SELECT * FROM hotel";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $hotelsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hotels = [];
        foreach ($hotelsData as $h) {
            $hotels[] = [
                'id' => (int)($h['id_hotel'] ?? 0),
                'nom' => $h['nom_hotel'] ?? '',
            ];
        }
        return $hotels;
    }
}