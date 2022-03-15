<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\ClaseAsiento;

/**
* @ORM\Entity
* @ORM\Table(name="boleto_voucher_agencia")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class VoucherAgencia{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\OneToOne(targetEntity="Boleto", mappedBy="voucherAgencia", cascade={"persist"})
    */
    protected $boleto;
    
    /**
    * @Assert\NotNull(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)        
    */
    protected $empresa;
    
    /**
    * @ORM\Column(name="bono", type="boolean", nullable=false)
    */
    protected $bono;

    /**
    * @ORM\ManyToOne(targetEntity="Moneda")
    * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id", nullable=false)   
    */
    protected $moneda;
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoCambio")
    * @ORM\JoinColumn(name="tipo_cambio_id", referencedColumnName="id", nullable=false)   
    */
    protected $tipoCambio;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe total solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999999.99",
    *      minMessage = "El importe total no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe total no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe total debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
    */
    protected $importeTotal;
    
    /**
    * @Assert\NotBlank(message = "La fecha no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="datetime", nullable=false)
    */
    protected $fecha;
    
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El código de referencia externa no puede tener más de {{ limit }} caracteres."
    * ) 
    * @Assert\Regex(
    *     pattern="/((^[a-zA-Z0-9]{1,20}$))/",
    *     match=true,
    *     message="El código de referencia externa solo puede tener números y letras."
    * )
    * @ORM\Column(type="string", length=20, nullable=true)
    */
    protected $referenciaExterna;
    
    //   ----   DATOS DE CONTROL INIT ----
    /**
    * @Assert\NotNull(message = "El usuario no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuario;
    
    /**
    * @Assert\NotNull(message = "La estacion no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $estacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_anulacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioAnulacion;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_anulacion", type="datetime", nullable=true)
    */
    protected $fechaAnulacion;
    
    //   ----   DATOS DE CONTROL END ----
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        if($this->bono === true){
            if($this->boleto->getAsientoBus()->getClase()->getId() === ClaseAsiento::B){
                $context->addViolation("No se puede utilizar el bono en un asiento clase B.");
            }
            if($this->boleto->getAsientoBus()->getNumero() <= 12){
                $context->addViolation("No se puede utilizar el bono en un asiento con número menor que 12.");
            }
        }
    }
    
    public function __toString() {
        return strval($this->id);
    }
    
    function __construct() {
        $this->fecha = new \DateTime();
        $this->bono = false;
        $this->importeTotal = 0;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getBoleto() {
        return $this->boleto;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getBono() {
        return $this->bono;
    }

    public function getMoneda() {
        return $this->moneda;
    }

    public function getTipoCambio() {
        return $this->tipoCambio;
    }

    public function getImporteTotal() {
        return $this->importeTotal;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getReferenciaExterna() {
        return $this->referenciaExterna;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setBoleto($boleto) {
        $this->boleto = $boleto;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setBono($bono) {
        $this->bono = $bono;
    }

    public function setMoneda($moneda) {
        $this->moneda = $moneda;
    }

    public function setTipoCambio($tipoCambio) {
        $this->tipoCambio = $tipoCambio;
    }

    public function setImporteTotal($importeTotal) {
        $this->importeTotal = $importeTotal;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setReferenciaExterna($referenciaExterna) {
        $this->referenciaExterna = $referenciaExterna;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function getUsuarioAnulacion() {
        return $this->usuarioAnulacion;
    }

    public function getFechaAnulacion() {
        return $this->fechaAnulacion;
    }

    public function setUsuarioAnulacion($usuarioAnulacion) {
        $this->usuarioAnulacion = $usuarioAnulacion;
    }

    public function setFechaAnulacion($fechaAnulacion) {
        $this->fechaAnulacion = $fechaAnulacion;
    }
}

?>