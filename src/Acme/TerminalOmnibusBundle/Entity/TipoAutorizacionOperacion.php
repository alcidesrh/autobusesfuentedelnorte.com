<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity
* @ORM\Table(name="autorizacion_operacion_tipo")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
*/
class TipoAutorizacionOperacion{
    
    const REASIGNACION_POR_TIEMPO = 1;
    const ANULACION_POR_TIEMPO = 2;
    
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
    *      max = "50",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="nombre", type="string", length=50, unique=true)
    */
    protected $nombre;
    
    /**
    * @Assert\Length(
    *      max = "100",
    *      maxMessage = "La descripcion no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="descripcion", type="string", length=100, nullable=true)
    */
    protected $descripcion;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return $this->nombre;
    }
    
    public function getInfo() {
        $value = $this->nombre;
        if($this->descripcion !== null && trim($this->descripcion) !== ""){
            $value .= " ( " . $this->descripcion . " ) ";
        }
        return $value;
    }
    
    function __construct() {
        $this->activo = true;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }


}

?>