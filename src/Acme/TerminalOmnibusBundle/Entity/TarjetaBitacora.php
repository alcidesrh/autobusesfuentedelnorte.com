<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Acme\TerminalOmnibusBundle\Entity\Boleto;

/**
* @ORM\Entity
* @ORM\Table(name="tarjeta_bitacora")
*/
class TarjetaBitacora {
   
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Tarjeta", inversedBy="bitacoras")
    * @ORM\JoinColumn(name="tarjeta_id", referencedColumnName="id", nullable=false)
    */
    protected $tarjeta;
    
    /**
    * @Assert\NotBlank(message = "La fecha de la bitácora de la tarjeta no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="datetime", nullable=false)
    */
    protected $fecha;    
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuario;
    
    /**
    * @Assert\Length(
    *      max = "255",
    *      maxMessage = "La descripción del cambio en el tarjeta no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="descripcion", type="string", length=255)
    */
    protected $descripcion;
    
    function __construct($usuario = null) {
        $this->fecha = new \DateTime();
        $this->usuario = $usuario;
    }
    
    public function __toString() {
        $str = "";
        if($this->fecha !== null){
            $str .= "Fecha:" . $this->fecha->format('d-m-Y H:i:s');
        }
        if($this->usuario !== null){
            $str .= "|Usuario:" . $this->usuario->getFullName();
        }
        return $str;
    }

    public function getId() {
        return $this->id;
    }

    public function getTarjeta() {
        return $this->tarjeta;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTarjeta($tarjeta) {
        $this->tarjeta = $tarjeta;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
}
