<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ConfigurarFacturaModel {
    
    /**
    * @Assert\NotBlank(message = "La estación no debe estar en blanco.")     
    */
    protected $estacion;
    
    /**
    * @Assert\NotBlank(message = "El servicio de estación no debe estar en blanco.")     
    */
    protected $servicioEstacion;
    
    /**
    * @Assert\NotBlank(message = "La empresa no debe estar en blanco.")     
    */
    protected $empresa;
    
    /**
    * @Assert\NotBlank(message = "La factura no debe estar en blanco.")     
    */
    protected $factura;
    
    public function getEstacion() {
        return $this->estacion;
    }

    public function getServicioEstacion() {
        return $this->servicioEstacion;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getFactura() {
        return $this->factura;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setServicioEstacion($servicioEstacion) {
        $this->servicioEstacion = $servicioEstacion;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setFactura($factura) {
        $this->factura = $factura;
    }


}