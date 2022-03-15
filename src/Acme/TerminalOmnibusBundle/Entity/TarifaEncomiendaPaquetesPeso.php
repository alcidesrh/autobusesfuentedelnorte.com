<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Entity\TarifaEncomienda;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarifaEncomiendaPaquetesPesoRepository")
* @ORM\Table(name="tarifas_encomienda_paquetes_peso")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields = {"pesoMinimo" , "pesoMaximo", "fechaEfectividad"}, 
* message="Ya existe una tarifa para la combinación específica de peso mínimo, peso máximo y fecha de efectividad.")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class TarifaEncomiendaPaquetesPeso extends TarifaEncomienda{
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El peso mínimo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El peso mínimo no debe ser menor que {{ limit }}.",
    *      maxMessage = "El peso mínimo no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El peso mínimo debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $pesoMinimo; //unidad de medida en cm
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El peso máximo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El peso máximo no debe ser menor que {{ limit }}.",
    *      maxMessage = "El peso máximo no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El peso máximo debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $pesoMaximo; //unidad de medida en cm
    
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
        $str = "PaquetePeso|ID: " . $this->id;
        if($this->pesoMinimo === null && $this->pesoMaximo === null){
            $str .= "|Rango Completo";
        }else{
            if($this->pesoMinimo === null){
                $str .= "|Rango de 0 a " . $this->pesoMaximo;
            }else if($this->pesoMaximo === null){
                $str .= "|Rango de " . $this->pesoMinimo . " a infinito";
            }else{
                $str .= "|Rango de " . $this->pesoMinimo . " a " . $this->pesoMaximo ;
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
        
        if($this->pesoMinimo !== null && $this->pesoMaximo !== null){
            if($this->pesoMinimo > $this->pesoMaximo){
                 $context->addViolation("El peso mínimo no puede ser mayor que el peso máximo."); 
            }
        }
        
        if($this->tarifaPorcentual === true && $this->tarifaValor > 100){
             $context->addViolation("Si la tarifa es porcentual, el valor no puede ser mayor que 100."); 
        }
    }
    
    private $peso; 
    public function calcularTarifa() {
        
        if($this->tarifaPorcentual === true){

            if($this->peso === null){
                throw new \RuntimeException("Para el calculo de una tarifa porcentual debe estar definido el peso.");
            }
            
            $valor = $this->peso * $this->tarifaValor * 0.01;
            
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
    
    public function getPesoMinimo() {
        return $this->pesoMinimo;
    }

    public function getPesoMaximo() {
        return $this->pesoMaximo;
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

    public function getPeso() {
        return $this->peso;
    }

    public function setPesoMinimo($pesoMinimo) {
        $this->pesoMinimo = $pesoMinimo;
    }

    public function setPesoMaximo($pesoMaximo) {
        $this->pesoMaximo = $pesoMaximo;
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

    public function setPeso($peso) {
        $this->peso = $peso;
    }
}

?>