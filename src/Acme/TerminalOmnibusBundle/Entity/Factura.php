<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\FacturaRepository")
* @ORM\Table(name="factura")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class Factura{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
   /**
    * @Assert\NotBlank(message = "La estación de la factura no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)
    */
    protected $estacion;
    
   /**
    * @Assert\NotBlank(message = "La empresa de la factura no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)
    */
    protected $empresa;
    
    /**
    * @Assert\NotBlank(message = "El servicio de estación de la factura no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ServicioEstacion")
    * @ORM\JoinColumn(name="servicio_estacion_id", referencedColumnName="id", nullable=false)
    */
    protected $servicioEstacion;
    
    /**
    * @Assert\NotBlank(message = "El nombre de la resolución de la factura no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "El nombre de la resolución de la factura por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre de la resolución de la factura no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255, nullable=false )
    */
    protected $nombreResolucionFactura;
    
    /**
    * @Assert\NotBlank(message = "La fecha de emisión de la resolución de la factura no debe estar en blanco")
    * @Assert\Date(message = "Fecha de emisión de la resolución de la factura no valida")
    * @ORM\Column(type="date", nullable=false )
    */
    protected $fechaEmisionResolucionFactura;

    /**
    * @Assert\NotBlank(message = "La fecha de vencimiento de la resolución de la factura no debe estar en blanco")
    * @Assert\Date(message = "Fecha de vencimiento de la resolución de la factura no valida")
    * @ORM\Column(type="date", nullable=false )
    */
    protected $fechaVencimientoResolucionFactura;
    
    /**
    * @Assert\NotBlank(message = "La serie de la resolución de la factura no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^[A-Z0-9]{1,6}$/",
    *     match=true,
    *     message="La serie de la resolución de la factura solo puede contener letras."
    * )
    * @Assert\Length(
    *      min = "1",
    *      max = "6",
    *      minMessage = "La serie de la resolución de la factura debe tener por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La serie de la resolución de la factura no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=6, nullable=false)
    */
    protected $serieResolucionFactura;
    
    /**
    * @Assert\NotBlank(message = "El mínimo de la resolución de la factura no debe estar en blanco")
    * @ORM\Column(type="bigint", nullable=false)
    */
    protected $minimoResolucionFactura;
    
    /**
    * @Assert\NotBlank(message = "El máximo de la resolución de la factura no debe estar en blanco")
    * @ORM\Column(type="bigint", nullable=false)
    */
    protected $maximoResolucionFactura;    
    
    /**
    * @Assert\NotBlank(message = "El valor de la resolución de la factura no debe estar en blanco")
    * @ORM\Column(type="bigint", nullable=false)
    */
    protected $valorResolucionFactura;  
    
    /**
    * @Assert\NotBlank(message = "El nombre de la resolución del sistema no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "El nombre de la resolución del sistema por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre de la resolución del sistema no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255, nullable=false )
    */
    protected $nombreResolucionSistema;
    
    /**
    * @Assert\NotBlank(message = "La fecha de emisión de la resolución del sistema no debe estar en blanco")
    * @Assert\Date(message = "Fecha de emisión de la resolución del sistema no valida")
    * @ORM\Column(type="date", nullable=false )
    */
    protected $fechaEmisionResolucionSistema;
    
    /**
    * @Assert\NotBlank(message = "La fecha de vencimiento de la resolución del sistema no debe estar en blanco")
    * @Assert\Date(message = "Fecha de vencimiento de la resolución del sistema no valida")
    * @ORM\Column(type="date", nullable=false )
    */
    protected $fechaVencimientoResolucionSistema;
    
    /**
    * @ORM\ManyToOne(targetEntity="Impresora")
    * @ORM\JoinColumn(name="impresora_id", referencedColumnName="id", nullable=true)
    */
    protected $impresora;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        $str = "";
        if($this->empresa !== null){
            $str .= $this->empresa->__toString() . "|";
        }
        if($this->estacion !== null){
            $str .= $this->estacion->__toString() . "|";
        }
        if($this->servicioEstacion !== null){
            $str .= $this->servicioEstacion->__toString() . "|";
        }
        if($this->serieResolucionFactura !== null){
            $str .= "SERIE:" . $this->serieResolucionFactura;
        }
        return $str;
    }
    
    function __construct() {
        $this->activo = true;
        $this->fechaEmisionResolucionFactura = new \DateTime();
        $this->fechaEmisionResolucionSistema = new \DateTime();
        $this->fechaVencimientoResolucionFactura = new \DateTime();
        $this->fechaVencimientoResolucionSistema = new \DateTime();
    }
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        if($this->minimoResolucionFactura > $this->maximoResolucionFactura){
            $context->addViolation("El valor mínimo debe ser menor que el valor máximo.");
        }
        
        if($this->valorResolucionFactura < $this->minimoResolucionFactura){
            $context->addViolation("El valor de la resolución de la factura es menor que el valor mínimo autorizado.");
        }
            
        if($this->activo === true){

            if($this->valorResolucionFactura > $this->maximoResolucionFactura){
                $context->addViolation("El valor de la resolución de la factura sobrepaso el valor máximo autorizado.");
            }
            
            if($this->impresora === null){
                $context->addViolation("Para activar la serie de factura debe asignar una impresora.");
            }else {
                if($this->impresora->getEstacion() !== null && 
                        $this->impresora->getEstacion() !== $this->getEstacion() ){
                  $context->addViolation("La estación de la serie de factura no coincide con la estación de la impresora.");
                }
            }
        }
    }
    
    public function getId() {
        return $this->id;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getNombreResolucionFactura() {
        return $this->nombreResolucionFactura;
    }

    public function getFechaEmisionResolucionFactura() {
        return $this->fechaEmisionResolucionFactura;
    }

    public function getFechaVencimientoResolucionFactura() {
        return $this->fechaVencimientoResolucionFactura;
    }

    public function getSerieResolucionFactura() {
        return $this->serieResolucionFactura;
    }

    public function getMinimoResolucionFactura() {
        return $this->minimoResolucionFactura;
    }

    public function getMaximoResolucionFactura() {
        return $this->maximoResolucionFactura;
    }

    public function getValorResolucionFactura() {
        return $this->valorResolucionFactura;
    }

    public function getNombreResolucionSistema() {
        return $this->nombreResolucionSistema;
    }

    public function getFechaEmisionResolucionSistema() {
        return $this->fechaEmisionResolucionSistema;
    }

    public function getFechaVencimientoResolucionSistema() {
        return $this->fechaVencimientoResolucionSistema;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setNombreResolucionFactura($nombreResolucionFactura) {
        $this->nombreResolucionFactura = $nombreResolucionFactura;
    }

    public function setFechaEmisionResolucionFactura($fechaEmisionResolucionFactura) {
        $this->fechaEmisionResolucionFactura = $fechaEmisionResolucionFactura;
    }

    public function setFechaVencimientoResolucionFactura($fechaVencimientoResolucionFactura) {
        $this->fechaVencimientoResolucionFactura = $fechaVencimientoResolucionFactura;
    }

    public function setSerieResolucionFactura($serieResolucionFactura) {
        $this->serieResolucionFactura = $serieResolucionFactura;
    }

    public function setMinimoResolucionFactura($minimoResolucionFactura) {
        $this->minimoResolucionFactura = $minimoResolucionFactura;
    }

    public function setMaximoResolucionFactura($maximoResolucionFactura) {
        $this->maximoResolucionFactura = $maximoResolucionFactura;
    }

    public function setValorResolucionFactura($valorResolucionFactura) {
        $this->valorResolucionFactura = $valorResolucionFactura;
    }

    public function setNombreResolucionSistema($nombreResolucionSistema) {
        $this->nombreResolucionSistema = $nombreResolucionSistema;
    }

    public function setFechaEmisionResolucionSistema($fechaEmisionResolucionSistema) {
        $this->fechaEmisionResolucionSistema = $fechaEmisionResolucionSistema;
    }

    public function setFechaVencimientoResolucionSistema($fechaVencimientoResolucionSistema) {
        $this->fechaVencimientoResolucionSistema = $fechaVencimientoResolucionSistema;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getServicioEstacion() {
        return $this->servicioEstacion;
    }
    
    public function setServicioEstacion($servicioEstacion) {
        $this->servicioEstacion = $servicioEstacion;
    }
    
    public function getImpresora() {
        return $this->impresora;
    }

    public function setImpresora($impresora) {
        $this->impresora = $impresora;
    }
}

?>