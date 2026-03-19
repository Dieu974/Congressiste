<?php
class hotel{
    private $id_hotel;
    private $nom_hotel;

    public function __construct($id_hotel, $nom_hotel) {
        $this->setIdHotel($id_hotel);
        $this->setNomHotel($nom_hotel);
    }

    // id_hotel
    public function getIdHotel() {
        return $this->id_hotel;
    }
    public function setIdHotel($id_hotel) {
        $this->id_hotel = $id_hotel;
    }

    // nom_hotel
    public function getNomHotel() {
        return $this->nom_hotel;
    }
    public function setNomHotel($nom_hotel) {
        $this->nom_hotel = $nom_hotel;
    }
}