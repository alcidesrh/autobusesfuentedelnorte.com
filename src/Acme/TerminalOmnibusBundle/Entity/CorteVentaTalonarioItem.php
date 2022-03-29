<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
* @ORM\Table(name="talonario_corte_venta_item")
* @ORM\HasLifecycleCallbacks
*/
class CorteVentaTalonarioItem{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="CorteVentaTalonario", inversedBy="listaItems")
    * @ORM\JoinColumn(name="corte_venta_talonario", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    protected $corteVentaTalonario;
    
    /**
    * @Assert\NotNull(message = "El numero del corte de venta no debe estar en blanco")
    * @ORM\Column(name="numero", type="bigint", nullable=false)
    */
    protected $numero;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe del corte de venta item solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El importe del corte de venta item no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe del corte de venta item no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe del corte de venta item debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=false)
    */
    protected $importe;
    
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - INIT
    //------------------------------------------------------------------------------  
    /**
    * @Assert\NotBlank(message = "La fecha de creacion no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
    */
    protected $fechaCreacion;
    
    /**
    * @Assert\NotNull(message = "El usuario de creacion no debe estar en null")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuarioCreacion;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_actualizacion", type="datetime", nullable=true)
    */
    protected $fechaActualizacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_actualizacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioActualizacion;
    
    function __construct($usuarioCreacion = null) {
        $this->fechaCreacion = new \DateTime();
        $this->usuarioCreacion = $usuarioCreacion;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCorteVentaTalonario() {
        return $this->corteVentaTalonario;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCorteVentaTalonario($corteVentaTalonario) {
        $this->corteVentaTalonario = $corteVentaTalonario;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }
    
    public function getFechaActualizacion() {
        return $this->fechaActualizacion;
    }

    public function getUsuarioActualizacion() {
        return $this->usuarioActualizacion;
    }

    public function setFechaActualizacion($fechaActualizacion) {
        $this->fechaActualizacion = $fechaActualizacion;
    }

    public function setUsuarioActualizacion($usuarioActualizacion) {
        $this->usuarioActualizacion = $usuarioActualizacion;
    }
}

?>