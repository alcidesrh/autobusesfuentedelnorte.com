<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TipoDocumentoEncomiendaRepository")
* @ORM\Table(name="encomienda_documento_tipo")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
*/
class TipoDocumentoEncomienda{
    
    const FACTURA = '1';
    const POR_COBRAR = '2';
    const AUTORIZACION_CORTESIA = '3';
    const AUTORIZACION_INTERNA = '4';
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
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
    
    /**
    * @ORM\Column(type="text", nullable=true)
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $descripcion;    
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        if ($this->descripcion != null && trim($this->descripcion) != "") {
            return trim($this->nombre) . " - " . $this->descripcion;
        } else {
            return $this->nombre;
        }
    }
    
    function __construct() {
        
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