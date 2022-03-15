<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Acme\TerminalOmnibusBundle\Entity\EstadoBus;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\BusRepository")
* @ORM\Table(name="bus")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
* @DoctrineAssert\UniqueEntity(fields ="placa", message="La placa ya existe")
* @DoctrineAssert\UniqueEntity(fields ="numeroSeguro", message="El número de seguro ya existe")
* @DoctrineAssert\UniqueEntity(fields ="numeroTarjetaRodaje", message="El número de tarjeta de rodaje ya existe")
* @DoctrineAssert\UniqueEntity(fields ="numeroTarjetaOperaciones", message="El número de tarjeta de operaciones ya existe")
*/
class Bus{
    
    /**
    * @Assert\NotBlank(message = "El código no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "6",
    *      minMessage = "El código por lo menos debe tener {{ limit }} carácter.",
    *      maxMessage = "El código no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El código solo puede contener números"
    * )
    * @ORM\Id
    * @ORM\Column(type="string", length=6, unique=true)
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $codigo;
    
    /**
    * @Assert\NotBlank(message = "La placa no debe estar en blanco")
    * @Assert\Length(
    *      min = "3",
    *      max = "20",
    *      minMessage = "La placa por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La placa no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=10, unique=true)
    */
    protected $placa;
    
    /**
    * @Assert\NotNull(message = "La tipo no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoBus")
    * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id", nullable=false)        
    */
    protected $tipo;
    
    /**
    * @Assert\NotNull(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)        
    */
    protected $empresa;
    
    /**
    * @Assert\NotNull(message = "La marca no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="MarcaBus")
    * @ORM\JoinColumn(name="marca_id", referencedColumnName="id", nullable=false)        
    */
    protected $marca;
    
    /**
    * @Assert\NotBlank(message = "El año de fabricación no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El año solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "1950",
    *      max = "2014",
    *      minMessage = "El año de fabricación no debe ser menor de 1950.",
    *      maxMessage = "El año de fabricación no debe ser mayor que el 2014.",
    *      invalidMessage = "El año de fabricación debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $anoFabricacion;
    
   /**
    * @Assert\Length(
    *      min = "1",
    *      max = "30",
    *      minMessage = "El número de seguro por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El número de seguro no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=30, unique=true, nullable=true)
    */
    protected $numeroSeguro;
    
   /**
    * @Assert\Length(
    *      min = "1",
    *      max = "30",
    *      minMessage = "El número de tarjeta de rodaje por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El número de tarjeta de rodaje no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=30, unique=true, nullable=true)
    */
    protected $numeroTarjetaRodaje;    
    
    /**
    * @Assert\Length(
    *      min = "1",
    *      max = "30",
    *      minMessage = "El número de tarjeta de operaciones por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El número de tarjeta de operaciones no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=30, unique=true, nullable=true)
    */
    protected $numeroTarjetaOperaciones;       
    
    /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(type="date", nullable=true)
    */
    protected $fechaVencimientoTarjetaOperaciones;
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoBus")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @ORM\ManyToOne(targetEntity="Piloto")
    * @ORM\JoinColumn(name="piloto_id", referencedColumnName="id", nullable=true)        
    */
    protected $piloto;
    
    /**
    * @ORM\ManyToOne(targetEntity="Piloto")
    * @ORM\JoinColumn(name="piloto_aux_id", referencedColumnName="id", nullable=true)        
    */
    protected $pilotoAux;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $descripcion;
    
    public function __toString() {
        if ($this->descripcion != null && trim($this->descripcion) != "") {
            return trim($this->codigo) . "-" . $this->descripcion;
        } else {
            return $this->codigo;
        }
    }
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        
    }
    
    public function getBusCodigoClasePlaca() {
        return $this->codigo . " / " . $this->tipo->getClase()->getNombre() . " / " . $this->placa;
    }
    
    public function getBusCodigoTipoClasePlaca() {
        return $this->codigo . " / " . $this->tipo->getAlias() . " / " . $this->tipo->getClase()->getNombre() . " / " . $this->placa;
    }
    
    function __construct() {
        
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getPlaca() {
        return $this->placa;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getMarca() {
        return $this->marca;
    }

    public function getAnoFabricacion() {
        return $this->anoFabricacion;
    }

    public function getNumeroSeguro() {
        return $this->numeroSeguro;
    }

    public function getNumeroTarjetaRodaje() {
        return $this->numeroTarjetaRodaje;
    }

    public function getNumeroTarjetaOperaciones() {
        return $this->numeroTarjetaOperaciones;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getPiloto() {
        return $this->piloto;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setPlaca($placa) {
        $this->placa = $placa;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setMarca($marca) {
        $this->marca = $marca;
    }

    public function setAnoFabricacion($anoFabricacion) {
        $this->anoFabricacion = $anoFabricacion;
    }

    public function setNumeroSeguro($numeroSeguro) {
        $this->numeroSeguro = $numeroSeguro;
    }

    public function setNumeroTarjetaRodaje($numeroTarjetaRodaje) {
        $this->numeroTarjetaRodaje = $numeroTarjetaRodaje;
    }

    public function setNumeroTarjetaOperaciones($numeroTarjetaOperaciones) {
        $this->numeroTarjetaOperaciones = $numeroTarjetaOperaciones;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setPiloto($piloto) {
        $this->piloto = $piloto;
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
    
    public function getFechaVencimientoTarjetaOperaciones() {
        return $this->fechaVencimientoTarjetaOperaciones;
    }

    public function setFechaVencimientoTarjetaOperaciones($fechaVencimientoTarjetaOperaciones) {
        $this->fechaVencimientoTarjetaOperaciones = $fechaVencimientoTarjetaOperaciones;
    }
    
    public function getPilotoAux() {
        return $this->pilotoAux;
    }

    public function setPilotoAux($pilotoAux) {
        $this->pilotoAux = $pilotoAux;
    }
}

?>