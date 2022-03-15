<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity
* @ORM\Table(name="tipo_tarjeta")
* @DoctrineAssert\UniqueEntity(fields ="sigla", message="La sigla ya existe")
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
* @ORM\HasLifecycleCallbacks
*/
class TipoTarjeta{
    
    const MANUAL = 1;
    const AUTOMATICA = 2;
    
     /**
    * @ORM\Id
    * @ORM\Column(type="smallint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La sigla no debe estar en blanco")    
    * @ORM\Column(type="string", length=3, nullable=false, unique=true)
    */
    protected $sigla;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco") 
    * @ORM\Column(type="string", length=50, nullable=false, unique=true)
    */
    protected $nombre;
    
    public function __toString() {
        return $this->nombre;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getSigla() {
        return $this->sigla;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSigla($sigla) {
        $this->sigla = $sigla;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
}

?>