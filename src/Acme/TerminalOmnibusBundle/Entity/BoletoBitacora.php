<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Acme\TerminalOmnibusBundle\Entity\Boleto;

/**
* @ORM\Entity
* @ORM\Table(name="boleto_bitacora")
*/
class BoletoBitacora {
   
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Boleto", inversedBy="bitacoras")
    * @ORM\JoinColumn(name="boleto_id", referencedColumnName="id", nullable=false)
    */
    protected $boleto;
    
    /**
    * @Assert\NotBlank(message = "La fecha de la bitácora del boleto no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="datetime", nullable=false)
    */
    protected $fecha;    
    
    /**
    * @Assert\NotNull(message = "El estado de la bitácora del boleto no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoBoleto")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuario;
    
    /**
    * @Assert\Length(
    *      max = "255",
    *      maxMessage = "La descripción del cambio de estado de la salida no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="descripcion", type="string", length=255)
    */
    protected $descripcion;
    
    function __construct() {
        $this->fecha = new \DateTime();
    }
    
    public function __toString() {
        $str = "";
        if($this->fecha !== null){
            $str .= "Fecha:" . $this->fecha->format('d-m-Y H:i:s');
        }
        if($this->estado !== null){
            $str .= "|Estado:" . $this->estado->getNombre();
        }
        if($this->usuario !== null){
            $str .= "|Usuario:" . $this->usuario->getFullName();
        }
        return $str;
    }

    public function getId() {
        return $this->id;
    }

    public function getBoleto() {
        return $this->boleto;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getEstado() {
        return $this->estado;
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

    public function setBoleto($boleto) {
        $this->boleto = $boleto;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
}
