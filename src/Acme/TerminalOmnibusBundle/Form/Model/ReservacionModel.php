<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ReservacionModel {
    
    /**
    * @Assert\Date(message = "Fecha de salida no valida")
    */
    protected $fechaSalida;
    
    /**
    * @Assert\NotBlank(message = "La estación de origen no debe estar en blanco.")     
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotBlank(message = "La salida no debe estar en blanco.")     
    */
    protected $salida;
    
    /**
    * @Assert\NotBlank(message = "Debe especificar al menos un cliente de reservación.")     
    */
    protected $listaClienteReservacion;
    
    public function __construct() { 
       
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

    public function getListaClienteReservacion() {
        return $this->listaClienteReservacion;
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

    public function setListaClienteReservacion($listaClienteReservacion) {
        $this->listaClienteReservacion = $listaClienteReservacion;
    }
}