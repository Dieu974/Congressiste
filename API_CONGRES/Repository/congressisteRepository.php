<?php
require_once __DIR__."/../Class/congressiste.php";
class congressisteRepository {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function findAll() {
        $sql = "SELECT * FROM congressiste INNER JOIN hotel ON congressiste.id_hotel = hotel.id_hotel INNER JOIN organisme_payeur ON congressiste.id_organisme = organisme_payeur.id_organisme";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $congressistes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $congs = [];
        foreach ($congressistes as $congressiste) {
            // créer tableau associatif au lieu d'objet
            $congs[] = [
                'id_congressiste' => (int)($congressiste['id_congressiste'] ?? 0),
                'nom' => $congressiste['nom'] ?? '',
                'prenom' => $congressiste['prenom'] ?? '',
                'adresse' => $congressiste['adresse'] ?? '',
                'email' => $congressiste['email'] ?? '',
                'nom_hotel'=>$congressiste['nom_hotel'] ?? null,
                'acompte' => isset($congressiste['acompte']) ? (bool)$congressiste['acompte'] : null,
                'supplement_petit_dejeuner' => array_key_exists('supplement_petit_dejeuner', $congressiste)
                    ? ($congressiste['supplement_petit_dejeuner'] ? 'Oui' : 'Non')
                    : null,
                'nb_etoile_souhaite'=>$congressiste['nb_etoile_souhaite'] ?? null,
                'nom_organisme'=>$congressiste['nom_organisme'] ?? null,

                // Ne pas inclure 'password'
            ];
        }
        return $congs; 
    }
    public function findById($id) {
        $sql = "SELECT * FROM congressiste WHERE id_congressiste = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $congressiste = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($congressiste) {
            return [
                'id_congressiste' => (int)($congressiste['id_congressiste'] ?? 0),
                'nom' => $congressiste['nom'] ?? '',
                'prenom' => $congressiste['prenom'] ?? '',
                'adresse' => $congressiste['adresse'] ?? '',
                'email' => $congressiste['email'] ?? '',
                'nom_hotel'=>$congressiste['nom_hotel'] ?? null,
                'acompte' => isset($congressiste['acompte']) ? (bool)$congressiste['acompte'] : null,
                'supplement_petit_dejeuner' => array_key_exists('supplement_petit_dejeuner', $congressiste)
                    ? ($congressiste['supplement_petit_dejeuner'] ? 'Oui' : 'Non')
                    : null,
                'nb_etoile_souhaite'=>$congressiste['nb_etoile_souhaite'] ?? null,
                'nom_organisme'=>$congressiste['nom_organisme'] ?? null,
                
            ];
        }
        return null;
    }
    // create : accepte un tableau (depuis l'API) ou un objet Congressiste, retourne bool
    public function create($cong): bool {
        $sql = "INSERT INTO congressiste
            (nom, prenom, adresse, email, id_hotel, acompte, password, supplement_petit_dejeuner, nb_etoile_souhaite, id_organisme)
            VALUES (:nom, :prenom, :adresse, :email, :id_hotel, :acompte, :password, :supplement_petit_dejeuner, :nb_etoile_souhaite, :id_organisme)";
        $stmt = $this->conn->prepare($sql);

        // helper pour normaliser flag
        $toFlag = function($v) {
            if ($v === null || $v === '') return null;
            if (is_bool($v)) return $v ? 1 : 0;
            return in_array($v, [1,'1',true,'true','oui','Oui','YES','yes','Oui','Yes'], true) ? 1 : 0;
        };

        if (is_array($cong)) {
            $nom = $cong['nom'] ?? '';
            $prenom = $cong['prenom'] ?? '';
            $adresse = array_key_exists('adresse', $cong) ? $cong['adresse'] : null;
            $email = $cong['email'] ?? '';
            $id_hotel = !empty($cong['hotel_id'] ?? $cong['id_hotel'] ?? null) ? (int)($cong['hotel_id'] ?? $cong['id_hotel']) : null;
            $id_organisme = !empty($cong['organisme_id'] ?? $cong['id_organisme'] ?? null) ? (int)($cong['organisme_id'] ?? $cong['id_organisme']) : null;
            $acompte = $toFlag($cong['acompte'] ?? null);
            $supp_pd = $toFlag($cong['supplement_petit_dejeuner'] ?? $cong['supplement_petit_dej'] ?? null);
            $nb_etoile = isset($cong['nb_etoile_souhaite']) && $cong['nb_etoile_souhaite'] !== '' ? (int)$cong['nb_etoile_souhaite'] : null;
            $password = isset($cong['password']) && $cong['password'] !== '' ? password_hash($cong['password'], PASSWORD_DEFAULT) : null;

            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindValue(':adresse', $adresse === null ? null : $adresse, $adresse === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id_hotel', $id_hotel === null ? null : $id_hotel, $id_hotel === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':acompte', $acompte === null ? null : $acompte, $acompte === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':password', $password === null ? null : $password, $password === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':supplement_petit_dejeuner', $supp_pd === null ? null : $supp_pd, $supp_pd === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':nb_etoile_souhaite', $nb_etoile === null ? null : $nb_etoile, $nb_etoile === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id_organisme', $id_organisme === null ? null : $id_organisme, $id_organisme === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            try {
                return (bool)$stmt->execute();
            } catch (PDOException $e) {
                error_log('congressisteRepository::create (array) error: ' . $e->getMessage());
                return false;
            }
        }

        // compatibilité : objet Congressiste (tente différents getters)
        try {
            $get = function($obj, $names, $default = null) {
                foreach ((array)$names as $n) {
                    if (method_exists($obj, $n)) return $obj->$n();
                }
                return $default;
            };

            $nom = $get($cong, ['getNom', 'nom'], '');
            $prenom = $get($cong, ['getPrenom', 'prenom'], '');
            $adresse = $get($cong, ['getAdresse', 'adresse'], null);
            $email = $get($cong, ['getEmail', 'email'], '');
            $id_hotel = $get($cong, ['getIdHotel','getId_hotel','getHotelId'], null);
            $id_organisme = $get($cong, ['getIdOrganisme','getId_organisme','getOrganismeId'], null);
            $acompte = $toFlag($get($cong, ['getAcompte','acompte'], null));
            $supp_pd = $toFlag($get($cong, ['getSupplementPetitDejeuner','getSupplement','supplement_petit_dejeuner'], null));
            $nb_etoile = $get($cong, ['getNbEtoileSouhaite','getNb_etoile_souhaite','nb_etoile_souhaite'], null);
            $nb_etoile = $nb_etoile === null || $nb_etoile === '' ? null : (int)$nb_etoile;
            $passwordRaw = $get($cong, ['getPassword','password'], null);
            $password = $passwordRaw ? password_hash($passwordRaw, PASSWORD_DEFAULT) : null;

            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindValue(':adresse', $adresse === null ? null : $adresse, $adresse === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id_hotel', $id_hotel === null ? null : (int)$id_hotel, $id_hotel === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':acompte', $acompte === null ? null : $acompte, $acompte === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':password', $password === null ? null : $password, $password === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':supplement_petit_dejeuner', $supp_pd === null ? null : $supp_pd, $supp_pd === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':nb_etoile_souhaite', $nb_etoile === null ? null : $nb_etoile, $nb_etoile === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id_organisme', $id_organisme === null ? null : (int)$id_organisme, $id_organisme === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            error_log('congressisteRepository::create (object) error: ' . $e->getMessage());
            return false;
        }
    }
    // update : accepte un tableau (depuis l'API) ou un objet Congressiste, retourne bool
    public function update($cong): bool {
        $sql = "UPDATE congressiste SET
                    nom = :nom,
                    prenom = :prenom,
                    adresse = :adresse,
                    email = :email,
                    id_hotel = :id_hotel,
                    acompte = :acompte,
                    password = COALESCE(:password, password),
                    supplement_petit_dejeuner = :supplement_petit_dejeuner,
                    nb_etoile_souhaite = :nb_etoile_souhaite,
                    id_organisme = :id_organisme
                WHERE id_congressiste = :id";
        $stmt = $this->conn->prepare($sql);

        $toFlag = function($v) {
            if ($v === null || $v === '') return null;
            if (is_bool($v)) return $v ? 1 : 0;
            return in_array($v, [1,'1',true,'true','oui','Oui','YES','yes','Oui','Yes'], true) ? 1 : 0;
        };

        if (is_array($cong)) {
            $id = $cong['id_congressiste'] ?? $cong['id'] ?? null;
            if ($id === null) return false;

            $nom = $cong['nom'] ?? '';
            $prenom = $cong['prenom'] ?? '';
            $adresse = array_key_exists('adresse', $cong) ? $cong['adresse'] : null;
            $email = $cong['email'] ?? '';
            $id_hotel = !empty($cong['hotel_id'] ?? $cong['id_hotel'] ?? null) ? (int)($cong['hotel_id'] ?? $cong['id_hotel']) : null;
            $id_organisme = !empty($cong['organisme_id'] ?? $cong['id_organisme'] ?? null) ? (int)($cong['organisme_id'] ?? $cong['id_organisme']) : null;
            $acompte = $toFlag($cong['acompte'] ?? null);
            $supp_pd = $toFlag($cong['supplement_petit_dejeuner'] ?? $cong['supplement_petit_dej'] ?? null);
            $nb_etoile = isset($cong['nb_etoile_souhaite']) && $cong['nb_etoile_souhaite'] !== '' ? (int)$cong['nb_etoile_souhaite'] : null;
            $password = isset($cong['password']) && $cong['password'] !== '' ? password_hash($cong['password'], PASSWORD_DEFAULT) : null;

            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindValue(':adresse', $adresse === null ? null : $adresse, $adresse === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id_hotel', $id_hotel === null ? null : $id_hotel, $id_hotel === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':acompte', $acompte === null ? null : $acompte, $acompte === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':password', $password === null ? null : $password, $password === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':supplement_petit_dejeuner', $supp_pd === null ? null : $supp_pd, $supp_pd === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':nb_etoile_souhaite', $nb_etoile === null ? null : $nb_etoile, $nb_etoile === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id_organisme', $id_organisme === null ? null : $id_organisme, $id_organisme === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

            try {
                return (bool)$stmt->execute();
            } catch (PDOException $e) {
                error_log('congressisteRepository::update (array) error: ' . $e->getMessage());
                return false;
            }
        }

        // compatibilité objet Congressiste
        try {
            $get = function($obj, $names, $default = null) {
                foreach ((array)$names as $n) {
                    if (method_exists($obj, $n)) return $obj->$n();
                }
                return $default;
            };

            $id = $get($cong, ['getId','getId_congressiste','id'], null);
            if ($id === null) return false;

            $nom = $get($cong, ['getNom','nom'], '');
            $prenom = $get($cong, ['getPrenom','prenom'], '');
            $adresse = $get($cong, ['getAdresse','adresse'], null);
            $email = $get($cong, ['getEmail','email'], '');
            $id_hotel = $get($cong, ['getIdHotel','getId_hotel','getHotelId'], null);
            $id_organisme = $get($cong, ['getIdOrganisme','getId_organisme','getOrganismeId'], null);
            $acompte = $toFlag($get($cong, ['getAcompte','acompte'], null));
            $supp_pd = $toFlag($get($cong, ['getSupplementPetitDejeuner','getSupplement','supplement_petit_dejeuner'], null));
            $nb_etoile = $get($cong, ['getNbEtoileSouhaite','getNb_etoile_souhaite','nb_etoile_souhaite'], null);
            $nb_etoile = $nb_etoile === null || $nb_etoile === '' ? null : (int)$nb_etoile;
            $passwordRaw = $get($cong, ['getPassword','password'], null);
            $password = $passwordRaw ? password_hash($passwordRaw, PASSWORD_DEFAULT) : null;

            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindValue(':adresse', $adresse === null ? null : $adresse, $adresse === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id_hotel', $id_hotel === null ? null : (int)$id_hotel, $id_hotel === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':acompte', $acompte === null ? null : $acompte, $acompte === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':password', $password === null ? null : $password, $password === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':supplement_petit_dejeuner', $supp_pd === null ? null : $supp_pd, $supp_pd === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':nb_etoile_souhaite', $nb_etoile === null ? null : $nb_etoile, $nb_etoile === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id_organisme', $id_organisme === null ? null : (int)$id_organisme, $id_organisme === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            error_log('congressisteRepository::update (object) error: ' . $e->getMessage());
            return false;
        }
    }
    public function delete($id): bool {
        try {
            $id = (int)$id;
            if ($id <= 0) return false;

            // trouver contraintes référentes à la table congressiste dans la DB courante
            $sql = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE REFERENCED_TABLE_NAME = 'congressiste'
                      AND REFERENCED_TABLE_SCHEMA = DATABASE()";
            $q = $this->conn->prepare($sql);
            $q->execute();
            $refs = $q->fetchAll(PDO::FETCH_ASSOC);

            $this->conn->beginTransaction();

            // supprimer d'abord les lignes dépendantes de chaque table référente
            if (!empty($refs)) {
                foreach ($refs as $r) {
                    $table = $r['TABLE_NAME'];
                    $col = $r['COLUMN_NAME'];
                    // supprimer uniquement si des lignes existent
                    $cntStmt = $this->conn->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$col}` = :id");
                    $cntStmt->bindValue(':id', $id, PDO::PARAM_INT);
                    $cntStmt->execute();
                    $cnt = (int)$cntStmt->fetchColumn();
                    if ($cnt > 0) {
                        $delStmt = $this->conn->prepare("DELETE FROM `{$table}` WHERE `{$col}` = :id");
                        $delStmt->bindValue(':id', $id, PDO::PARAM_INT);
                        $delStmt->execute();
                        error_log("congressisteRepository::delete - deleted {$delStmt->rowCount()} rows from {$table} referencing congressiste {$id}");
                    } else {
                        error_log("congressisteRepository::delete - no rows in {$table} referencing congressiste {$id}");
                    }
                }
            }

            // enfin supprimer le congressiste
            $del = $this->conn->prepare('DELETE FROM congressiste WHERE id_congressiste = :id');
            $del->bindValue(':id', $id, PDO::PARAM_INT);
            $ok = $del->execute();

            $this->conn->commit();

            return (bool)$ok && $del->rowCount() > 0;
        } catch (PDOException $e) {
            try { $this->conn->rollBack(); } catch (Exception $ex) {}
            error_log('congressisteRepository::delete exception: ' . $e->getMessage());
            return false;
        }
    }
}