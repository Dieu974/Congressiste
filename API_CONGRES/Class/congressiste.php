<?php
    class congressiste {
        private $id_congressiste;
        private $nom;
        private $prenom;
        private $adresse;
        private $email;
        private $id_hotel;
        private $acompte;
        private $password;
        private $supplement_petit_dejeuner;
        private $nb_etoile_souhaite;
        private $id_organisme;

        public function __construct($id_congressiste, $nom, $prenom, $adresse, $email, $id_hotel, $acompte, $password, $supplement_petit_dejeuner, $nb_etoile_souhaite, $id_organisme) {
            $this->setId($id_congressiste);
            $this->setNom($nom);
            $this->setPrenom($prenom);
            $this->setAdresse($adresse);
            $this->setEmail($email);
            $this->setIdHotel($id_hotel);
            $this->setAcompte($acompte);
            $this->setPassword($password);
            $this->setSupplementPetitDejeuner($supplement_petit_dejeuner);
            $this->setNbEtoileSouhaite($nb_etoile_souhaite);
            $this->setIdOrganisme($id_organisme);
        }

        // id
        public function getId() {
            return $this->id_congressiste;
        }
        public function setId($id) {
            $this->id_congressiste = $id;
        }

        // nom
        public function getNom() {
            return $this->nom;
        }
        public function setNom($nom) {
            $this->nom = $nom;
        }

        // prenom
        public function getPrenom() {
            return $this->prenom;
        }
        public function setPrenom($prenom) {
            $this->prenom = $prenom;
        }

        // adresse
        public function getAdresse() {
            return $this->adresse;
        }
        public function setAdresse($adresse) {
            $this->adresse = $adresse;
        }

        // email
        public function getEmail() {
            return $this->email;
        }
        public function setEmail($email) {
            $this->email = $email;
        }

        // id_hotel
        public function getIdHotel() {
            return $this->id_hotel;
        }
        public function setIdHotel($id_hotel) {
            $this->id_hotel = $id_hotel;
        }

        // acompte
        public function getAcompte() {
            return $this->acompte;
        }
        public function setAcompte($acompte) {
            $this->acompte = $acompte;
        }

        // password
        public function getPassword() {
            return $this->password;
        }
        public function setPassword($password) {
            $this->password = $password;
        }

        // supplement_petit_dejeuner
        public function getSupplementPetitDejeuner() {
            return $this->supplement_petit_dejeuner;
        }
        public function setSupplementPetitDejeuner($supplement) {
            $this->supplement_petit_dejeuner = $supplement;
        }

        // nb_etoile_souhaite
        public function getNbEtoileSouhaite() {
            return $this->nb_etoile_souhaite;
        }
        public function setNbEtoileSouhaite($nb) {
            $this->nb_etoile_souhaite = $nb;
        }

        // id_organisme
        public function getIdOrganisme() {
            return $this->id_organisme;
        }
        public function setIdOrganisme($id_organisme) {
            $this->id_organisme = $id_organisme;
        }
    }
