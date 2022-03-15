<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Entity\TarifaEncomienda;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarifaEncomiendaEfectivoRepository")
* @ORM\Table(name="tarifas_encomienda_efectivo")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"importeMinimo" , "importeMaximo", "fechaEfectividad"}, 
* message="Ya existe una tarifa para la combinación especficada de importe mínimo, importe máximo y fecha de efectividad.")
*/
class TarifaEncomiendaEfectivo extends TarifaEncomienda{
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe mínimo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El importe mínimo no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe mínimo no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe mínimo debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $importeMinimo;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe máximo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El importe máximo no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe máximo no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe máximo debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $importeMaximo;
    
    /**
    * @Assert\NotBlank(message = "La fecha de efectividad no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime", nullable=false)
    */
    protected $fechaEfectividad;
    
    /**
    * @ORM\Column(type="boolean", nullable=false)
    */
    protected $tarifaPorcentual;
    
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
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El valor mínimo para tarifa porcentual solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El valor mínimo para tarifa porcentual no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor mínimo para tarifa porcentual no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor mínimo para tarifa porcentual debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $tarifaPorcentualValorMinimo; //Valor null o cero indica que no hay mínimo
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El valor máximo para tarifa porcentual solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El valor máximo para tarifa porcentual no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor máximo para tarifa porcentual no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor máximo para tarifa porcentual debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $tarifaPorcentualValorMaximo; //Valor null o cero indica que no hay máximo
    
   public function __toString() {
        $str = "Efectivo|ID: " . $this->id;
        if($this->importeMinimo === null && $this->importeMaximo === null){
            $str .= "|Rango Completo";
        }else{
            if($this->importeMinimo === null){
                $str .= "|Rango de 0 a " . $this->importeMaximo;
            }else if($this->importeMaximo === null){
                $str .= "|Rango de " . $this->importeMinimo . " a infinito";
            }else{
                $str .= "|Rango de " . $this->importeMinimo . " a " . $this->importeMaximo ;
            }
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
        
        if($this->importeMinimo !== null && $this->importeMaximo !== null){
            if($this->importeMinimo > $this->importeMaximo){
                 $context->addViolation("El importe mínimo no puede ser mayor que el importe máximo."); 
            }
        }
        
        if($this->tarifaPorcentual === true && $this->tarifaValor > 100){
             $context->addViolation("Si la tarifa es porcentual, el valor no puede ser mayor que 100."); 
        }
    }
    
    private $importe; //Valor interno
    public function calcularTarifa() {
        
        if($this->tarifaPorcentual === true){

            if($this->importe === null){
                throw new \RuntimeException("Para el calculo de una tarifa porcentual debe estar definido el importe.");
            }
            
            $valor = $this->importe * $this->tarifaValor * 0.01;
            
            if($this->tarifaPorcentualValorMinimo !== null && $valor < $this->tarifaPorcentualValorMinimo){
                $valor = $this->tarifaPorcentualValorMinimo;
            }
            
            if($this->tarifaPorcentualValorMaximo !== null && $valor > $this->tarifaPorcentualValorMaximo){
                $valor = $this->tarifaPorcentualValorMaximo;
            }
            
            return round($valor, 2, PHP_ROUND_HALF_UP);
            
        }else{
            return $this->tarifaValor;
        }
    }
    
    public function getImporte() {
        return $this->importe;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }
    
    function __construct() {
        parent::__construct();
        $this->fechaEfectividad = new \DateTime();
    }
    
    public function getImporteMinimo() {
        return $this->importeMinimo;
    }

    public function getImporteMaximo() {
        return $this->importeMaximo;
    }

    public function getFechaEfectividad() {
        return $this->fechaEfectividad;
    }

    public function getTarifaPorcentual() {
        return $this->tarifaPorcentual;
    }

    public function getTarifaValor() {
        return $this->tarifaValor;
    }

    public function getTarifaPorcentualValorMinimo() {
        return $this->tarifaPorcentualValorMinimo;
    }

    public function getTarifaPorcentualValorMaximo() {
        return $this->tarifaPorcentualValorMaximo;
    }

    public function setImporteMinimo($importeMinimo) {
        $this->importeMinimo = $importeMinimo;
    }

    public function setImporteMaximo($importeMaximo) {
        $this->importeMaximo = $importeMaximo;
    }

    public function setFechaEfectividad($fechaEfectividad) {
        $this->fechaEfectividad = $fechaEfectividad;
    }

    public function setTarifaPorcentual($tarifaPorcentual) {
        $this->tarifaPorcentual = $tarifaPorcentual;
    }

    public function setTarifaValor($tarifaValor) {
        $this->tarifaValor = $tarifaValor;
    }

    public function setTarifaPorcentualValorMinimo($tarifaPorcentualValorMinimo) {
        $this->tarifaPorcentualValorMinimo = $tarifaPorcentualValorMinimo;
    }

    public function setTarifaPorcentualValorMaximo($tarifaPorcentualValorMaximo) {
        $this->tarifaPorcentualValorMaximo = $tarifaPorcentualValorMaximo;
    }

}

?>