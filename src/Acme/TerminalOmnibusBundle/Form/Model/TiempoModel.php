<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class TiempoModel {
    
     /**
    * @Assert\NotBlank(message = "La ruta no puede estar en blanco.")     
    */
    protected $ruta;
    
    /**
    * @Assert\NotBlank(message = "La clase de bus no puede estar en blanco.")     
    */
    protected $claseBus;
    
    
    protected $listaItems;
    
    public function __construct() { 
       
    }
    
    public function getRuta() {
        return $this->ruta;
    }

    public function getClaseBus() {
        return $this->claseBus;
    }

    public function getListaItems() {
        return $this->listaItems;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setClaseBus($claseBus) {
        $this->claseBus = $claseBus;
    }

    public function setListaItems($listaItems) {
        $this->listaItems = $listaItems;
    }
}