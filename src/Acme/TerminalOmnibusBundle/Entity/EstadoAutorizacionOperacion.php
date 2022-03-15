<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity
* @ORM\Table(name="autorizacion_operacion_estado")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
*/
class EstadoAutorizacionOperacion{
    
    const REGISTRADO = 1;
    const AUTORIZADO = 2;
    const DENEGADO = 3;
    
     /**
    * @ORM\Id
    * @ORM\Column(type="smallint")
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "40",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=40, unique=true)
    */
    protected $nombre;
    
    public function __toString() {
        return $this->nombre;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
}

?>