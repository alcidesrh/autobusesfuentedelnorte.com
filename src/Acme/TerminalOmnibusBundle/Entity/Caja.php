<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\OperacionCaja;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\CajaRepository")
* @ORM\Table(name="caja")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class Caja{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La fecha de creación no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
    */
    protected $fechaCreacion;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_apertura", type="datetime", nullable=true)
    */
    protected $fechaApertura;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_cierre", type="datetime", nullable=true)
    */
    protected $fechaCierre;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_cancelacion", type="datetime", nullable=true)
    */
    protected $fechaCancelacion;
    
    /**
    * @Assert\NotNull(message = "La moneda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Moneda")
    * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id", nullable=false)        
    */
    protected $moneda;
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoCaja")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @Assert\NotNull(message = "La estacion no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $estacion;
    
    /**
    * @Assert\NotNull(message = "El usuario no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuario;
    
    /**
    * @ORM\OneToMany(targetEntity="OperacionCaja", mappedBy="caja", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $operaciones;
    
     /*
     * VALIDACIONES
     */
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        if($this->estado->getId() === EstadoCaja::CREADA){
            $caja = $container->get("doctrine")->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaPendiente($this->usuario, $this->moneda);
            if($caja !== null){
                $context->addViolation("El usuario tiene una caja pendiente en la moneda: " . $this->moneda->getSigla() . "."); 
            }
        }
        
        //Se supone que esa operacion es la de inicializacion
        if($this->estado->getId() === EstadoCaja::CANCELADA){
            if(count($this->operaciones) !== 1){
                $context->addViolation("Para cancelar una caja no puede tener operaciones."); 
            }
        }
    }
    
    public function __toString() {
        $str = "";
        if($this->moneda !== null){
            $str .= $this->moneda->getSigla() . "|";
        }
        if($this->usuario !== null){
            $str .= $this->usuario . "|";
        }
        if($this->estado !== null){
            $str .= $this->estado->getNombre();
        }
        return $str;
    }
    
    function __construct() {
        $this->fechaCreacion = new \DateTime();
        $this->operaciones = new ArrayCollection();
    }
    
     public function addOperacion(OperacionCaja $operacionCaja) {
         $operacionCaja->setCaja($this);
         $this->operaciones->add($operacionCaja);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getMoneda() {
        return $this->moneda;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getFechaApertura() {
        return $this->fechaApertura;
    }

    public function getFechaCierre() {
        return $this->fechaCierre;
    }

    public function getFechaCancelacion() {
        return $this->fechaCancelacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setMoneda($moneda) {
        $this->moneda = $moneda;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setFechaApertura($fechaApertura) {
        $this->fechaApertura = $fechaApertura;
    }

    public function setFechaCierre($fechaCierre) {
        $this->fechaCierre = $fechaCierre;
    }

    public function setFechaCancelacion($fechaCancelacion) {
        $this->fechaCancelacion = $fechaCancelacion;
    }   
    
    public function getOperaciones() {
        return $this->operaciones;
    }

    public function setOperaciones($operaciones) {
        $this->operaciones = $operaciones;
    }
    
    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }
}

?>