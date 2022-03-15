<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\DepositoAgenciaRepository")
* @ORM\Table(name="agencia_deposito")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class DepositoAgencia{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(name="fecha", type="date", nullable=false)
    */
    protected $fecha;
    
    /**
    * @Assert\NotNull(message = "La estación del depósito no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacion;
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoDeposito")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @Assert\NotNull(message = "La moneda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Moneda")
    * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id", nullable=true)        
    */
    protected $moneda;
    
    /**
    * @Assert\NotNull(message = "El importe no debe estar en blanco")
    * @Assert\Range(
    *      min = "1",
    *      max = "99999.99",
    *      minMessage = "El importe no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe debe ser un número válido."
    * )  
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe solo puede contener números"
    * )
    * @ORM\Column(name="importe", type="decimal", precision=7, scale=2, nullable=false)
    */
    protected $importe;
    
    /**
    * @Assert\NotNull(message = "El numero de boleta no debe estar en blanco")
    * @Assert\Length(      
    *      max = "15",
    *      maxMessage = "El numero de boleta no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(name="numero_boleta", type="string", length=15, nullable=false)
    */
    protected $numeroBoleta;
    
    /**
    * @ORM\Column(name="aplica_bono", type="boolean", nullable=true)
    */
    protected $aplicaBono;
    
    /**
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El bono no debe ser menor que {{ limit }}.",
    *      maxMessage = "El bono no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El bono debe ser un número válido."
    * )  
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El bono solo puede contener números"
    * )
    * @ORM\Column(name="bono", type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $bono;
    
    /**
    * @Assert\Length(      
    *      max = "200",
    *      maxMessage = "La observación no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(name="observacion", type="string", length=200, nullable=true)
    */
    protected $observacion;
    
    /**
    * @Assert\Length(      
    *      max = "100",
    *      maxMessage = "El motivo de rechazo no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(name="motivo_rechazo", type="string", length=100, nullable=true)
    */
    protected $motivoRechazo;
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
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - END
    //------------------------------------------------------------------------------
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
       
    }
    
    function __construct() {
        $this->aplicaBono = true;
        $this->fecha = new \DateTime();
        $this->importe = 0;       
        $this->bono = 0;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getNumeroBoleta() {
        return $this->numeroBoleta;
    }

    public function getMotivoRechazo() {
        return $this->motivoRechazo;
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

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }

    public function setNumeroBoleta($numeroBoleta) {
        $this->numeroBoleta = $numeroBoleta;
    }

    public function setMotivoRechazo($motivoRechazo) {
        $this->motivoRechazo = $motivoRechazo;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }
    
    public function getAplicaBono() {
        return $this->aplicaBono;
    }

    public function setAplicaBono($aplicaBono) {
        $this->aplicaBono = $aplicaBono;
    }

    public function getObservacion() {
        return $this->observacion;
    }

    public function setObservacion($observacion) {
        $this->observacion = $observacion;
    }
    
    public function getMoneda() {
        return $this->moneda;
    }

    public function setMoneda($moneda) {
        $this->moneda = $moneda;
    }
    
    public function getBono() {
        return $this->bono;
    }

    public function setBono($bono) {
        $this->bono = $bono;
    }
}

?>