<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrarEncomiendaModel {
    
    protected $boleto;
    
     /**
    * @Assert\NotBlank(message = "El clinte remitente no puede estar en blanco.")     
    */
    protected $clienteRemitente;
    
    /**
    * @Assert\NotBlank(message = "El clinte destinatario no puede estar en blanco.")     
    */
    protected $clienteDestinatario;
    
    /**
    * @Assert\NotBlank(message = "La estación origen no debe estar en blanco.")     
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotBlank(message = "La estación destino no debe estar en blanco.")     
    */
    protected $estacionDestino;
    
    /**
    * @Assert\NotBlank(message = "Debe especificar al menos una ruta.")     
    */
    protected $listaEncomiendaRutas;
    
    /**
    * @Assert\NotBlank(message = "Debe especificar al menos una encomienda.")     
    */
    protected $listaEncomiendas;
    
    
    protected $impresorasDisponibles;
    
    /**
    * @Assert\NotBlank(message = "El tipo de documento no puede estar en blanco.")     
    */
    protected $tipoDocuemento;
    
    //Cortesias
    protected $autorizacionCortesia;
    
    //Interna
    protected $autorizacionInterna;
    
    //Factura
    protected $tipoPago;
    protected $totalNeto;
    protected $serieFactura;
    protected $monedaPago;
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El código de referencia externa no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/((^[a-zA-Z0-9]{1,20}$))/",
    *     match=true,
    *     message="El código de referencia externa solo puede tener números y letras."
    * )
    */
    protected $referenciaExterna;
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
    
    //Solo interno, primera ruta de la lista, es la que sse utiliza para facturar
    protected $ruta;
    
    protected $identificadorWeb;
    protected $codigoExternoCliente;
        
    public function __construct() { 
       
    }
    
    public function getBoleto() {
        return $this->boleto;
    }

    public function getClienteRemitente() {
        return $this->clienteRemitente;
    }

    public function getClienteDestinatario() {
        return $this->clienteDestinatario;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function getListaEncomiendaRutas() {
        return $this->listaEncomiendaRutas;
    }

    public function getListaEncomiendas() {
        return $this->listaEncomiendas;
    }

    public function getImpresorasDisponibles() {
        return $this->impresorasDisponibles;
    }

    public function getTipoDocuemento() {
        return $this->tipoDocuemento;
    }

    public function getAutorizacionCortesia() {
        return $this->autorizacionCortesia;
    }

    public function getAutorizacionInterna() {
        return $this->autorizacionInterna;
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

    public function setBoleto($boleto) {
        $this->boleto = $boleto;
    }

    public function setClienteRemitente($clienteRemitente) {
        $this->clienteRemitente = $clienteRemitente;
    }

    public function setClienteDestinatario($clienteDestinatario) {
        $this->clienteDestinatario = $clienteDestinatario;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }

    public function setListaEncomiendaRutas($listaEncomiendaRutas) {
        $this->listaEncomiendaRutas = $listaEncomiendaRutas;
    }

    public function setListaEncomiendas($listaEncomiendas) {
        $this->listaEncomiendas = $listaEncomiendas;
    }

    public function setImpresorasDisponibles($impresorasDisponibles) {
        $this->impresorasDisponibles = $impresorasDisponibles;
    }

    public function setTipoDocuemento($tipoDocuemento) {
        $this->tipoDocuemento = $tipoDocuemento;
    }

    public function setAutorizacionCortesia($autorizacionCortesia) {
        $this->autorizacionCortesia = $autorizacionCortesia;
    }

    public function setAutorizacionInterna($autorizacionInterna) {
        $this->autorizacionInterna = $autorizacionInterna;
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
    
    public function getRuta() {
        return $this->ruta;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }
    
    public function getReferenciaExterna() {
        return $this->referenciaExterna;
    }

    public function setReferenciaExterna($referenciaExterna) {
        $this->referenciaExterna = $referenciaExterna;
    }
    
    public function getSerieFactura() {
        return $this->serieFactura;
    }

    public function setSerieFactura($serieFactura) {
        $this->serieFactura = $serieFactura;
    }
    
    public function getIdentificadorWeb() {
        return $this->identificadorWeb;
    }

    public function setIdentificadorWeb($identificadorWeb) {
        $this->identificadorWeb = $identificadorWeb;
    }
    
    public function getCodigoExternoCliente() {
        return $this->codigoExternoCliente;
    }

    public function setCodigoExternoCliente($codigoExternoCliente) {
        $this->codigoExternoCliente = $codigoExternoCliente;
    }
}