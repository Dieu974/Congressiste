<?php
require_once __DIR__."/../Class/organisme.php";
class organismeRepository {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function findAll() {
        $sql = "SELECT * FROM organisme_payeur";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $organismesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $organismes = [];
        foreach ($organismesData as $o) {
            $organismes[] = [
                'id' => (int)($o['id_organisme'] ?? 0),
                'nom' => $o['nom_organisme'] ?? '',
            ];
        }
        return $organismes;
    }
}