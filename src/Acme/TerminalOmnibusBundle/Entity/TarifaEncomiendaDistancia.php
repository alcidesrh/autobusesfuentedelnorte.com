<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Entity\TarifaEncomienda;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarifaEncomiendaDistanciaRepository")
* @ORM\Table(name="tarifas_encomienda_distancia")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"estacionOrigen" , "estacionDestino", "fechaEfectividad"}, 
* message="Ya existe una tarifa para la combinación especificada de estación de origen, estación destino y fecha de efectividad.")
*/
class TarifaEncomiendaDistancia extends TarifaEncomienda{
    
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
    *     message="El valor solo puede contener números"
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
    
   public function __toString() {
        $str = "Distancia|ID: " . $this->id;
        if($this->estacionOrigen !== null && $this->estacionDestino !== null){
            $str .= "|" . $this->estacionOrigen->getAlias() . " - " . $this->estacionDestino->getAlias();
        }
        if($this->fechaEfectividad !== null){
            $str .= "|Fecha:" . $this->fechaEfectividad->format('d-m-Y H:i:s');
        }
        return $str;
    }
    
     /*
     * VALIDACION QUE EL IMPORTE INICIAL NO SEA MAYOR QUE EL IMPORTE FINAL.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        parent::validacionesGenerales($context);
        
        if($this->estacionOrigen === $this->estacionDestino){
             $context->addViolation("La estación de origen no puede ser igual a la estación destino.");   
        } 
    }

    public function calcularTarifa() {
        return $this->tarifaValor;
    }
    
    function __construct() {
        parent::__construct();
        $this->fechaEfectividad = new \DateTime();
    }
    
    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function getFechaEfectividad() {
        return $this->fechaEfectividad;
    }

    public function getTarifaValor() {
        return $this->tarifaValor;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }

    public function setFechaEfectividad($fechaEfectividad) {
        $this->fechaEfectividad = $fechaEfectividad;
    }

    public function setTarifaValor($tarifaValor) {
        $this->tarifaValor = $tarifaValor;
    }
}

?>