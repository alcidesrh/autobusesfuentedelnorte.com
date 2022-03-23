<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\OperacionCajaRepository")
* @ORM\Table(name="caja_operacion")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class OperacionCaja{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Caja", inversedBy="operaciones")
    * @ORM\JoinColumn(name="caja_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    protected $caja;
    
    /**
    * @Assert\NotBlank(message = "El importe no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/((^[\-]{0,1}\d{0,8}$)|(^[\-]{0,1}\d{1,8}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "-99999999.99",
    *      max = "99999999.99",
    *      minMessage = "El valor no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
    */
    protected $importe;
    
    /**
    * @Assert\NotNull(message = "El tipo de operacion no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoOperacionCaja")
    * @ORM\JoinColumn(name="tipo_operacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $tipoOperacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=true)        
    */
    protected $empresa;
    
    /**
    * @Assert\NotBlank(message = "La fecha no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="datetime", nullable=false)
    */
    protected $fecha;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $descripcion;    
    
     /*
     * VALIDACIONES
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        if($this->caja !== null){
//            var_dump($this->caja);
            if($this->caja->getFechaApertura() === null){
                if($this->tipoOperacion->getId() !== TipoOperacionCaja::INICIAL){
                    $context->addViolation("La caja no tiene fecha de apertura.");
                }
            }else{
                $datetimefechaCaja = strtotime($this->caja->getFechaApertura()->format("Y-m-d H:i:s"));
                $datetimefechaOperacion = strtotime($this->fecha->format("Y-m-d H:i:s"));
                if( $datetimefechaOperacion - $datetimefechaCaja < 0){
                    $context->addViolation("La fecha de la operación:" . $this->fecha->format("d-m-Y H:i:s") . " es menor que la fecha de apertura de la caja:" . $this->caja->getFechaApertura()->format("d-m-Y H:i:s")); 
                }
            }
            
            $estado = $this->caja->getEstado();
            if(($this->id === null || $this->id === "" || $this->id === "0") && $estado->getId() !== EstadoCaja::ABIERTA){
                if($this->tipoOperacion->getId() !== TipoOperacionCaja::INICIAL){
                    $context->addViolation("No se puede adicionar operaciones nuevas a la caja porque no esta abierta, el estado actual es:" . $estado->getNombre()); 
                }
            }
            
            if($this->fecha !== null && $this->caja->getFechaApertura() !== null){
                $cantidadHoras = \Acme\BackendBundle\Services\UtilService::diffHours($this->fecha, $this->caja->getFechaApertura());
                if($cantidadHoras > 25){
                    $context->addViolation("La caja lleva abierta más de 25 horas. Debe cerrar la caja actual y abrir una nueva");
                }
            }
        }
        
        if($this->importe !== null && doubleval($this->importe) == 0){
            if($this->tipoOperacion->getId() !== TipoOperacionCaja::INICIAL){
                $context->addViolation("El importe no puede ser 0.");
            }
        }
        
        if($this->tipoOperacion->getId() === TipoOperacionCaja::INICIAL){
            if($this->empresa !== null){
                $context->addViolation("La operacion de apertura de caja no puede estar asociada a ninguna empresa.");
            }
        }else{
            if($this->empresa === null){
                $context->addViolation("La operacion de caja debe estar asociada a una empresa.");
            }
        }
    }
    
    public function __toString() {
        $str = "";
        if($this->caja !== null){
            $str .= "Caja" . $this->caja->getId() . "|";
        }
        if($this->importe !== null && $this->caja !== null && $this->caja->getMoneda() !== null){
            $str .= "Importe" . $this->caja->getMoneda()->getSigla() . $this->importe . "|";
        }
        if($this->fecha !== null){
            $str .= "Fecha" . $this->fecha->format('d-m-Y H:i:s') . "|";
        }
        if($this->descripcion !== null){
            $str .= "Descripción" . $this->descripcion;
        }
        return $str;
    }
    
    function __construct() {
        $this->fecha = new \DateTime();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCaja() {
        return $this->caja;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getTipoOperacion() {
        return $this->tipoOperacion;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCaja($caja) {
        $this->caja = $caja;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }

    public function setTipoOperacion($tipoOperacion) {
        $this->tipoOperacion = $tipoOperacion;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}
