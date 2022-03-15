<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class EncomiendaEspecialModel {
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco.")     
    */
    protected $nombre;
    
    protected $descripcion;
    
    protected $permiteAutorizacionCortesia;
    
    protected $permiteAutorizacionInterna;
    
    protected $permitePorCobrar;

    protected $permiteFactura;
    
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
    */
    protected $tarifaValor;
    
    function __construct() {
        $this->permiteAutorizacionCortesia = true;
        $this->permiteAutorizacionInterna = true;
        $this->permiteFactura = true;
        $this->permitePorCobrar = true;
        $this->tarifaValor = 0;
    }
    
    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getPermiteAutorizacionCortesia() {
        return $this->permiteAutorizacionCortesia;
    }

    public function getPermiteAutorizacionInterna() {
        return $this->permiteAutorizacionInterna;
    }

    public function getPermitePorCobrar() {
        return $this->permitePorCobrar;
    }

    public function getPermiteFactura() {
        return $this->permiteFactura;
    }

    public function getTarifaValor() {
        return $this->tarifaValor;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setPermiteAutorizacionCortesia($permiteAutorizacionCortesia) {
        $this->permiteAutorizacionCortesia = $permiteAutorizacionCortesia;
    }

    public function setPermiteAutorizacionInterna($permiteAutorizacionInterna) {
        $this->permiteAutorizacionInterna = $permiteAutorizacionInterna;
    }

    public function setPermitePorCobrar($permitePorCobrar) {
        $this->permitePorCobrar = $permitePorCobrar;
    }

    public function setPermiteFactura($permiteFactura) {
        $this->permiteFactura = $permiteFactura;
    }

    public function setTarifaValor($tarifaValor) {
        $this->tarifaValor = $tarifaValor;
    }    
}