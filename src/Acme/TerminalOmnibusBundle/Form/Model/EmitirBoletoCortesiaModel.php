<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class EmitirBoletoCortesiaModel {
    
    /**
    * @Assert\NotBlank(message = "El motivo no debe estar en blanco")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $motivo;
    
    /**
    * @Assert\NotBlank(message = "El clinte documento no puede estar en blanco.")     
    */
    protected $cliente;
    
    /**
    * @Assert\Date(message = "Fecha de salida no valida")
    */
    protected $fechaSalida;
    
    protected $estacionOrigen;
    
    /**
    * @Assert\NotBlank(message = "La salida no debe estar en blanco.")     
    */
    protected $salida;
    
    /**
    * @Assert\NotBlank(message = "La estación 'Sube en' no debe estar en blanco.")     
    */
    protected $estacionSubeEn;
    
    /**
    * @Assert\NotBlank(message = "La estación 'Baja en' no debe estar en blanco.")     
    */
    protected $estacionBajaEn;
    
    /**
    * @Assert\NotBlank(message = "Debe seleccionar al menos un boleto.")     
    */
    protected $listaBoleto;
    
    protected $impresorasDisponibles;
    
    protected $movil;
    
    function __construct() {
        
    }
    
    public function getMotivo() {
        return $this->motivo;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getFechaSalida() {
        return $this->fechaSalida;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getSalida() {
        return $this->salida;
    }

    public function getEstacionSubeEn() {
        return $this->estacionSubeEn;
    }

    public function getEstacionBajaEn() {
        return $this->estacionBajaEn;
    }

    public function getListaBoleto() {
        return $this->listaBoleto;
    }

    public function getImpresorasDisponibles() {
        return $this->impresorasDisponibles;
    }

    public function getMovil() {
        return $this->movil;
    }

    public function setMotivo($motivo) {
        $this->motivo = $motivo;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function setFechaSalida($fechaSalida) {
        $this->fechaSalida = $fechaSalida;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setSalida($salida) {
        $this->salida = $salida;
    }

    public function setEstacionSubeEn($estacionSubeEn) {
        $this->estacionSubeEn = $estacionSubeEn;
    }

    public function setEstacionBajaEn($estacionBajaEn) {
        $this->estacionBajaEn = $estacionBajaEn;
    }

    public function setListaBoleto($listaBoleto) {
        $this->listaBoleto = $listaBoleto;
    }

    public function setImpresorasDisponibles($impresorasDisponibles) {
        $this->impresorasDisponibles = $impresorasDisponibles;
    }

    public function setMovil($movil) {
        $this->movil = $movil;
    }


}