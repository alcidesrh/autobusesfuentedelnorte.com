<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PendienteEntregarModel {
    
    /**
    * @Assert\NotBlank(message = "La estación no puede estar en blanco.")     
    */
    protected $estacion;
    /**
    * @Assert\NotBlank(message = "La empresa no puede estar en blanco.")     
    */
    protected $empresa;

    protected $serieFactura;
    
    /**
    * @Assert\NotBlank(message = "El clinte no puede estar en blanco.")     
    */
    protected $cliente;
    
    /**
    * @Assert\NotBlank(message = "La fecha de entrega no puede estar en blanco.")     
    */
    protected $fecha;
    
    /**
    * @Assert\NotBlank(message = "El importe total no puede estar en blanco.")     
    */
    protected $importeTotal;
    
    protected $numeroFactura;
    protected $listaIdEncomiendas;
    
    public function __construct() { 
       
    }
    
    public function getEstacion() {
        return $this->estacion;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getNumeroFactura() {
        return $this->numeroFactura;
    }

    public function getListaIdEncomiendas() {
        return $this->listaIdEncomiendas;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setNumeroFactura($numeroFactura) {
        $this->numeroFactura = $numeroFactura;
    }

    public function setListaIdEncomiendas($listaIdEncomiendas) {
        $this->listaIdEncomiendas = $listaIdEncomiendas;
    }    
    
    public function getImporteTotal() {
        return $this->importeTotal;
    }

    public function setImporteTotal($importeTotal) {
        $this->importeTotal = $importeTotal;
    }
    
    public function getEmpresa() {
        return $this->empresa;
    }

    public function getSerieFactura() {
        return $this->serieFactura;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setSerieFactura($serieFactura) {
        $this->serieFactura = $serieFactura;
    }
}