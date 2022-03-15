<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\FechaAlquilerRepository")
* @ORM\Table(name="alquiler_fecha")
* @ORM\HasLifecycleCallbacks
*/
class FechaAlquiler{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="datetime", nullable=false)
    */
    protected $fecha;
   
    /**
    * @ORM\ManyToOne(targetEntity="Alquiler", inversedBy="listaFechas")
    * @ORM\JoinColumn(name="alquiler_id", referencedColumnName="id", nullable=false)
    */
    protected $alquiler;
    
    
    public function __toString() {
        return $this->id;
    }
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getAlquiler() {
        return $this->alquiler;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setAlquiler($alquiler) {
        $this->alquiler = $alquiler;
    }
}

?>