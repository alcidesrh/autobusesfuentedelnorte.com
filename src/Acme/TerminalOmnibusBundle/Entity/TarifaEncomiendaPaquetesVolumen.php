<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Entity\TarifaEncomienda;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarifaEncomiendaPaquetesVolumenRepository")
* @ORM\Table(name="tarifas_encomienda_paquetes_volumen")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields = {"volumenMinimo" , "volumenMaximo", "fechaEfectividad"}, 
* message="Ya existe una tarifa para la combinación específica de volumen mínimo, volumen máximo y fecha de efectividad.")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class TarifaEncomiendaPaquetesVolumen extends TarifaEncomienda{
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,10}$)|(^\d{1,10}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El volumen mínimo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "9999999999.99",
    *      minMessage = "El volumen mínimo no debe ser menor que {{ limit }}.",
    *      maxMessage = "El volumen mínimo no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El volumen mínimo debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
    */
    protected $volumenMinimo; //unidad de medida en cm
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,10}$)|(^\d{1,10}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El volumen máximo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "9999999999.99",
    *      minMessage = "El volumen máximo no debe ser menor que {{ limit }}.",
    *      maxMessage = "El volumen máximo no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El volumen máximo debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
    */
    protected $volumenMaximo; //unidad de medida en cm
    
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
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,5}$))/",
    *     match=true,
    *     message="El valor solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99999",
    *      minMessage = "El valor no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=10, scale=5, nullable=false)
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
        $str = "PaqueteVolumen|ID: " . $this->id;
        if($this->volumenMinimo === null && $this->volumenMaximo === null){
            $str .= "|Rango Completo";
        }else{
            if($this->volumenMinimo === null){
                $str .= "|Rango de 0 a " . $this->volumenMaximo;
            }else if($this->volumenMaximo === null){
                $str .= "|Rango de " . $this->volumenMinimo . " a infinito";
            }else{
                $str .= "|Rango de " . $this->volumenMinimo . " a " . $this->volumenMaximo ;
            }
        }
        if($this->fechaEfectividad !== null){
            $str .= "|Fecha:" . $this->fechaEfectividad->format('d-m-Y H:i:s');
        }
        return $str;
    }
    
     /*
     * VALIDACION 
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        parent::validacionesGenerales($context);
        
        if($this->volumenMinimo !== null && $this->volumenMaximo !== null){
            if($this->volumenMinimo > $this->volumenMaximo){
                 $context->addViolation("El volumen mínimo no puede ser mayor que el volumen máximo."); 
            }
        }
        
        if($this->tarifaPorcentual === true && $this->tarifaValor > 100){
             $context->addViolation("Si la tarifa es porcentual, el valor no puede ser mayor que 100."); 
        }
    }
    
    private $volumen; 
    public function calcularTarifa() {
        
        if($this->tarifaPorcentual === true){

            if($this->volumen === null){
                throw new \RuntimeException("Para el calculo de una tarifa porcentual debe estar definido el volumen.");
            }
            
            $valor = $this->volumen * $this->tarifaValor * 0.01;
            
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
    
    function __construct() {
        parent::__construct();
        $this->fechaEfectividad = new \DateTime();
    }
    
    public function getVolumenMinimo() {
        return $this->volumenMinimo;
    }

    public function getVolumenMaximo() {
        return $this->volumenMaximo;
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

    public function getVolumen() {
        return $this->volumen;
    }

    public function setVolumenMinimo($volumenMinimo) {
        $this->volumenMinimo = $volumenMinimo;
    }

    public function setVolumenMaximo($volumenMaximo) {
        $this->volumenMaximo = $volumenMaximo;
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

    public function setVolumen($volumen) {
        $this->volumen = $volumen;
    }

}

?>