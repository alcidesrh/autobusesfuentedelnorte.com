<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class EntregarEncomiendaModel {
    
     /**
    * @Assert\NotBlank(message = "La encomienda original no puede estar en blanco.")     
    */
    protected $encomiendaOriginal;
    
    /**
    * @Assert\NotBlank(message = "El cliente que recibe la encomienda no puede estar en blanco.")     
    */
    protected $clienteReceptor;
    
    protected $clienteDocumento;
    
    //Factura
    protected $tipoPago;
    protected $totalNeto;
    protected $serieFactura;
    protected $monedaPago;
    protected $tasa;
    protected $totalPago;
    /**
    * @Assert\Regex(
    *     pattern="/((^[\-]{0,1}\d{0,8}$)|(^[\-]{0,1}\d{1,8}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El efectivo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0.00",
    *      max = "99999999.99",
    *      minMessage = "El valor no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor debe ser un número válido."
    * )
    */
    protected $efectivo;
    protected $vuelto;
    protected $facturar;
    
    protected $impresorasDisponibles;
    
    public function __construct() { 
       
    }
    
    public function getEncomiendaOriginal() {
        return $this->encomiendaOriginal;
    }

    public function getTipoPago() {
        return $this->tipoPago;
    }

    public function getTotalNeto() {
        return $this->totalNeto;
    }

    public function getMonedaPago() {
        return $this->monedaPago;
    }

    public function getTasa() {
        return $this->tasa;
    }

    public function getTotalPago() {
        return $this->totalPago;
    }

    public function getEfectivo() {
        return $this->efectivo;
    }

    public function getVuelto() {
        return $this->vuelto;
    }

    public function setEncomiendaOriginal($encomiendaOriginal) {
        $this->encomiendaOriginal = $encomiendaOriginal;
    }

    public function setTipoPago($tipoPago) {
        $this->tipoPago = $tipoPago;
    }

    public function setTotalNeto($totalNeto) {
        $this->totalNeto = $totalNeto;
    }

    public function setMonedaPago($monedaPago) {
        $this->monedaPago = $monedaPago;
    }

    public function setTasa($tasa) {
        $this->tasa = $tasa;
    }

    public function setTotalPago($totalPago) {
        $this->totalPago = $totalPago;
    }

    public function setEfectivo($efectivo) {
        $this->efectivo = $efectivo;
    }

    public function setVuelto($vuelto) {
        $this->vuelto = $vuelto;
    }
    
    public function getImpresorasDisponibles() {
        return $this->impresorasDisponibles;
    }

    public function setImpresorasDisponibles($impresorasDisponibles) {
        $this->impresorasDisponibles = $impresorasDisponibles;
    }
    
    public function getClienteReceptor() {
        return $this->clienteReceptor;
    }

    public function setClienteReceptor($clienteReceptor) {
        $this->clienteReceptor = $clienteReceptor;
    }
    
    public function getFacturar() {
        return $this->facturar;
    }

    public function setFacturar($facturar) {
        $this->facturar = $facturar;
    }
    
    public function getSerieFactura() {
        return $this->serieFactura;
    }

    public function setSerieFactura($serieFactura) {
        $this->serieFactura = $serieFactura;
    }
    
    public function getClienteDocumento() {
        return $this->clienteDocumento;
    }

    public function setClienteDocumento($clienteDocumento) {
        $this->clienteDocumento = $clienteDocumento;
    }
}