<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Context\LegacyExecutionContext;

/**
* @ORM\Entity
* @ORM\Table(name="banco")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class Banco{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El alias no debe estar en blanco")
    * @Assert\Length(
    *      max = "40",
    *      maxMessage = "El alias no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=40, unique=true)
    */
    protected $alias;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="nombre", type="string", length=100, nullable=false)
    */
    protected $nombre;
    
    /**
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El teléfono solo puede contener números"
    * )
    * @Assert\Length(
    *      min = "8",
    *      max = "15",
    *      minMessage = "El teléfono por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El teléfono no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=15, nullable=true)
    */
    protected $telefono;
    
    /**
    * @Assert\Length(      
    *      max = "150",
    *      maxMessage = "La dirección no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(type="string", length=200, nullable=true)
    */
    protected $direccion;
    
    function __construct() {
             
    }
    
    public function getId() {
        return $this->id;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }
}

?>