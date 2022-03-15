<?php
namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ConexionesRepository")
* @ORM\Table(name="conexiones")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
*/
class Conexiones{
    
    /**
    * @Assert\NotBlank(message = "El código no debe estar en blanco")
    * @Assert\Length(
    *      min = "6",
    *      max = "6",
    *      minMessage = "El código por lo menos debe tener {{ limit }} carácter.",
    *      maxMessage = "El código no puede tener más de {{ limit }} caracteres."
    * )
    * @ORM\Id
    * @ORM\Column(type="string", length=6, unique=true)
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $codigo;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100)
    */
    protected $nombre;
    
    /**
    * @Assert\NotBlank(message = "El horario no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "50",
    *      minMessage = "El horario por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El horario no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=50)
    */
    protected $horario;
    
    /**
    * @Assert\NotBlank(message = "El precio no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "50",
    *      minMessage = "El precio por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El precio no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=50)
    */
    protected $precio;
    
    /**
    * @Assert\NotBlank(message = "La descripción no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "500",
    *      minMessage = "La descripción por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=500)
    */
    protected $descripcion;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return strval($this->codigo) . " - " . $this->nombre;
    }
    
    function __construct() {
        $this->activo = true;
    }
    
    public function getCodigo() {
        return $this->codigo;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getHorario() {
        return $this->horario;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setHorario($horario) {
        $this->horario = $horario;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
}

?>