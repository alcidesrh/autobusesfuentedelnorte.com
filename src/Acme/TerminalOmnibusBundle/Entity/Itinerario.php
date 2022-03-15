<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ItinerarioRepository")
* @ORM\Table(name="itineario")
* @ORM\InheritanceType("JOINED")
* @ORM\DiscriminatorColumn(name="tipoItinerario", type="integer")
* @ORM\DiscriminatorMap({1 = "ItinerarioCiclico", 2 = "ItinerarioEspecial"})
* @ORM\HasLifecycleCallbacks
*/
class Itinerario{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "La ruta del itinerario no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Ruta")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo")
    */
    protected $ruta;
    
    /**
    * @Assert\NotNull(message = "El tipo de bus del itinerario no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoBus")
    * @ORM\JoinColumn(name="tipo_bus_id", referencedColumnName="id")        
    */
    protected $tipoBus;
    
    /**
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=true)        
    */
    protected $empresa;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    function __construct() {
        $this->activo = true;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getRuta() {
        return $this->ruta;
    }

    public function getTipoBus() {
        return $this->tipoBus;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setTipoBus($tipoBus) {
        $this->tipoBus = $tipoBus;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}

?>