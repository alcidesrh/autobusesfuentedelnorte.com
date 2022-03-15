<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\BackendBundle\Entity\User;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarifaBoletoRepository")
* @ORM\Table(name="tarifas_boleto")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"estacionOrigen" , "estacionDestino", "claseBus", "claseAsiento" , "fechaEfectividad"}, 
* message="Ya existe una tarifa para la combinación especficada de estación de origen, de destino, la clase de bus, la clase del asiento y la fecha de efectividad.")
*/
class TarifaBoleto implements Tarifa, \Acme\BackendBundle\Entity\IJobSync{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "La estación de origen no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotNull(message = "La estación de destino no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacionDestino;
    
    /**
    * @Assert\NotNull(message = "La clase del bus no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseBus")
    * @ORM\JoinColumn(name="clase_bus_id", referencedColumnName="id", nullable=false)   
    */
    protected $claseBus;
    
    /**
    * @Assert\NotNull(message = "La clase del asiento no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseAsiento")
    * @ORM\JoinColumn(name="clase_asiento_id", referencedColumnName="id", nullable=false)   
    */
    protected $claseAsiento;
    
    /**
    * @Assert\Time(message = "Hora inicial no valida")
    * @ORM\Column(type="time", nullable=true)
    */
    protected $horaInicialSalida;
    
    /**
    * @Assert\Time(message = "Hora final no valida")
    * @ORM\Column(type="time", nullable=true)
    */
    protected $horaFinalSalida;
    
    /**
    * @Assert\NotBlank(message = "La fecha de efectividad no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime", nullable=false)
    */
    protected $fechaEfectividad;
    
    /**
    * @Assert\NotBlank(message = "El valor no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El precio solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El valor no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=false)
    */
    protected $tarifaValor;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El valor de la tarifa adicional de la agencia solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El valor de la tarifa adicional de la agencia no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor de la tarifa adicional de la agencia no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor de la tarifa adicional de la agencia debe ser un número válido."
    * )   
    * @ORM\Column(name="agencia_tarifa_moneda_base", type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $tarifaAdicionalAgencia;
    
    // ----------------- DATOS INTERNOS -------------------------
    /**
    * @Assert\NotBlank(message = "La fecha de creacion no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime")
    */
    protected $fechaCreacion;
    
    /**
    * @Assert\NotNull(message = "El usuario de creacion no debe estar en null")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion", referencedColumnName="id", nullable=false)        
    */
    protected $usuarioCreacion;
    // ----------------- DATOS INTERNOS -------------------------
    
    function __construct() {
        $this->fechaEfectividad = new \DateTime();
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["estacionOrigen"] = $this->estacionOrigen->getId();
        $data["estacionDestino"] = $this->estacionDestino->getId();
        $data["claseBus"] = $this->claseBus->getId();
        $data["claseAsiento"] = $this->claseAsiento->getId();
        $data["horaInicialSalida"] = $this->horaInicialSalida;
        $data["horaFinalSalida"] = $this->horaFinalSalida;
        $data["fechaEfectividad"] = $this->fechaEfectividad->format('d-m-Y H:i:s');
        $data["tarifaValor"] = $this->tarifaValor;
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
    
    public function getNivelSync(){
        return 3;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_TARIFA_BOLETO;
    }
    
    public function __toString() {
        $str  = "Tarifa Boleto: " . $this->id;
        $str .= ", Valor: " . $this->tarifaValor;
        return $str;
    }
    
    public function getInfo1() {
        $str  = "ID: " . $this->id;
        $str .= ", Valor: " . $this->tarifaValor;
        return $str;
    }
    
     /*
     * VALIDACION QUE LA ESTACION DE ORIGEN NO SEA IGUAL QUE LA DE DESTINO.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
       
        if($this->estacionOrigen === $this->estacionDestino){
             $context->addViolation("La estación de origen no puede ser igual a la estación destino.");   
        } 
    }
    
    protected $tarifaAdicional;
    protected $tipoPago;
    protected $userEstacion;
    public function calcularTarifa() {      
        $this->tarifaAdicional = $this->calcularTarifaAdicional();
        if($this->tarifaAdicional !== null){
            return $this->tarifaValor + $this->tarifaAdicional;
        }
        return $this->tarifaValor;
    }
    
    public function calcularTarifaAdicional() {
        if($this->userEstacion !== null && $this->userEstacion instanceof User && $this->userEstacion->getEstacion() !== null && 
                $this->userEstacion->getEstacion()->getTipo()->getId() === TipoEstacion::AGENCIA && 
                $this->userEstacion->getEstacion()->getAplicarPorcientoTarifaAgencia() === true){
            if($this->tarifaAdicionalAgencia !== null && doubleval($this->tarifaAdicionalAgencia) !== 0){
                return $this->tarifaAdicionalAgencia;
            }else{
                $porcientoTarifaAgencia = $this->userEstacion->getEstacion()->getPorcientoTarifaAgencia();
                if($porcientoTarifaAgencia !== null && doubleval($porcientoTarifaAgencia) !== 0){
                    return $porcientoTarifaAgencia * $this->tarifaValor;
                }
            }
        }else{
            if($this->tipoPago !== null && $this->tipoPago !== TipoPago::EFECTIVO){
                if($this->tarifaValor < 100 ){
                    return 3;
                }else if($this->tarifaValor < 200){
                    return 5;
                }else if($this->tarifaValor < 300){
                    return 10;
                }else{
                    return 15;
                }
            }
        }
        return null;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function getTipoPago() {
        return $this->tipoPago;
    }

    public function getClaseBus() {
        return $this->claseBus;
    }

    public function getClaseAsiento() {
        return $this->claseAsiento;
    }

    public function getHoraInicialSalida() {
        return $this->horaInicialSalida;
    }

    public function getHoraFinalSalida() {
        return $this->horaFinalSalida;
    }

    public function getFechaEfectividad() {
        return $this->fechaEfectividad;
    }

    public function getTarifaValor() {
        return $this->tarifaValor;
    }

    public function getTarifaAdicionalAgencia() {
        return $this->tarifaAdicionalAgencia;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUserEstacion() {
        return $this->userEstacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }

    public function setTipoPago($tipoPago) {
        $this->tipoPago = $tipoPago;
    }

    public function setClaseBus($claseBus) {
        $this->claseBus = $claseBus;
    }

    public function setClaseAsiento($claseAsiento) {
        $this->claseAsiento = $claseAsiento;
    }

    public function setHoraInicialSalida($horaInicialSalida) {
        $this->horaInicialSalida = $horaInicialSalida;
    }

    public function setHoraFinalSalida($horaFinalSalida) {
        $this->horaFinalSalida = $horaFinalSalida;
    }

    public function setFechaEfectividad($fechaEfectividad) {
        $this->fechaEfectividad = $fechaEfectividad;
    }

    public function setTarifaValor($tarifaValor) {
        $this->tarifaValor = $tarifaValor;
    }

    public function setTarifaAdicionalAgencia($tarifaAdicionalAgencia) {
        $this->tarifaAdicionalAgencia = $tarifaAdicionalAgencia;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUserEstacion($userEstacion) {
        $this->userEstacion = $userEstacion;
    }
    
    public function getTarifaAdicional() {
        return $this->tarifaAdicional;
    }

    public function setTarifaAdicional($tarifaAdicional) {
        $this->tarifaAdicional = $tarifaAdicional;
    }
}

?>