<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TelefonoEstacionRepository")
* @ORM\Table(name="estacion_telefono")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="telefono", message="El teléfono ya existe")
*/
class TelefonoEstacion{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El telefono no debe estar en blanco")
    * @Assert\Length(
    *      min = "8",
    *      max = "25",
    *      minMessage = "El teléfono por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El teléfono no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=25, unique=true)
    */
    protected $telefono;
    
    /**
    * @ORM\ManyToOne(targetEntity="Estacion", inversedBy="listaTelefono")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id")
    */
    protected $estacion;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return $this->telefono;
    }
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }


   
}

?>