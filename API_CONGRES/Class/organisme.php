<?php
class organisme {
    private $id_organisme;
    private $nom_organisme;

    public function __construct($id_organisme, $nom_organisme) {
        $this->setIdOrganisme($id_organisme);
        $this->setNomOrganisme($nom_organisme);
    }

    public function getIdOrganisme() {
        return $this->id_organisme;
    }
    public function setIdOrganisme($id_organisme) {
        $this->id_organisme = $id_organisme;
    }
    public function getNomOrganisme() {
        return $this->nom_organisme;
    }
    public function setNomOrganisme($nom_organisme) {
        $this->nom_organisme = $nom_organisme;
    }
    
}