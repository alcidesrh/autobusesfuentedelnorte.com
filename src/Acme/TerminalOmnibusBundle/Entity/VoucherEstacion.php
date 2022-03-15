<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity
* @ORM\Table(name="boleto_voucher_estacion")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class VoucherEstacion{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\OneToOne(targetEntity="Boleto", mappedBy="voucherEstacion", cascade={"persist"})
    */
    protected $boleto;
    
    /**
    * @Assert\NotNull(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)        
    */
    protected $empresa;

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
    //   ----   DATOS DE CONTROL END ----
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        
        
    }
    
    public function __toString() {
        return strval($this->id);
    }
    
    function __construct() {
        $this->fecha = new \DateTime();
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
}

?>