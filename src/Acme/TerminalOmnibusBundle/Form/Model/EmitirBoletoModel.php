<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class EmitirBoletoModel {
    
    protected $agencia;
    
     /**
    * @Assert\NotBlank(message = "El clinte documento no puede estar en blanco.")     
    */
    protected $clienteDocumento;
    
    /**
    * @Assert\Date(message = "Fecha de salida no valida")
    */
    protected $fechaSalida;
    
    protected $estacionOrigen;
    
    protected $utilizarDesdeEstacionOrigenSalida;
    
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
    
    
    protected $observacionBajaEn;
    
    /**
    * @Assert\NotBlank(message = "Debe especificar al menos un cliente de boleto.")     
    */
    protected $listaClienteBoleto;
    
    
    protected $impresorasDisponibles;
    
    /**
    * @Assert\NotBlank(message = "El tipo de documento no puede estar en blanco.")     
    */
    protected $tipoDocuemento;
    
    protected $facturar;
    
    //VOUCHER Especial
    protected $estacionFacturacionEspecial;
    protected $serieFacturacionEspecial;
    protected $pingFacturacionEspecial;
    
    //Cortesias
    protected $autorizacionCortesia;
    
    //Factura
    protected $tipoPago;
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El código de autorización de tarjeta no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/((^[a-zA-Z0-9]{1,20}$))/",
    *     match=true,
    *     message="El código de autorización de tarjeta solo puede tener números y letras."
    * )
    */
    protected $autorizacionTarjeta;
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
    
    protected $monedaAgencia;
    protected $totalNetoAgencia;
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
    protected $referenciaExternaAgencia;
    protected $utilizarBonoAgencia;
    
    protected $movil;
    
    protected $identificadorWeb;
    
    public function __construct() { 
        $this->utilizarDesdeEstacionOrigenSalida = true;
        $this->agencia = false;
        $this->utilizarBonoAgencia = false;
        $this->facturar = true;
        $this->movil = false;
    }
    
    public function getImpresorasDisponibles() {
        return $this->impresorasDisponibles;
    }

    public function setImpresorasDisponibles($impresorasDisponibles) {
        $this->impresorasDisponibles = $impresorasDisponibles;
    }
    
    public function getClienteDocumento() {
        return $this->clienteDocumento;
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

    public function getObservacionBajaEn() {
        return $this->observacionBajaEn;
    }

    public function getListaClienteBoleto() {
        return $this->listaClienteBoleto;
    }

    public function getTipoDocuemento() {
        return $this->tipoDocuemento;
    }

    public function getAutorizacionCortesia() {
        return $this->autorizacionCortesia;
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

    public function setClienteDocumento($clienteDocumento) {
        $this->clienteDocumento = $clienteDocumento;
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

    public function setObservacionBajaEn($observacionBajaEn) {
        $this->observacionBajaEn = $observacionBajaEn;
    }

    public function setListaClienteBoleto($listaClienteBoleto) {
        $this->listaClienteBoleto = $listaClienteBoleto;
    }

    public function setTipoDocuemento($tipoDocuemento) {
        $this->tipoDocuemento = $tipoDocuemento;
    }

    public function setAutorizacionCortesia($autorizacionCortesia) {
        $this->autorizacionCortesia = $autorizacionCortesia;
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
    
    public function getUtilizarDesdeEstacionOrigenSalida() {
        return $this->utilizarDesdeEstacionOrigenSalida;
    }

    public function setUtilizarDesdeEstacionOrigenSalida($utilizarDesdeEstacionOrigenSalida) {
        $this->utilizarDesdeEstacionOrigenSalida = $utilizarDesdeEstacionOrigenSalida;
    }
    
    public function getAutorizacionTarjeta() {
        return $this->autorizacionTarjeta;
    }

    public function setAutorizacionTarjeta($autorizacionTarjeta) {
        $this->autorizacionTarjeta = $autorizacionTarjeta;
    }
    
    public function getEstacionFacturacionEspecial() {
        return $this->estacionFacturacionEspecial;
    }

    public function getPingFacturacionEspecial() {
        return $this->pingFacturacionEspecial;
    }
    
    public function setEstacionFacturacionEspecial($estacionFacturacionEspecial) {
        $this->estacionFacturacionEspecial = $estacionFacturacionEspecial;
    }

    public function setPingFacturacionEspecial($pingFacturacionEspecial) {
        $this->pingFacturacionEspecial = $pingFacturacionEspecial;
    }
    
    public function getReferenciaExterna() {
        return $this->referenciaExterna;
    }

    public function setReferenciaExterna($referenciaExterna) {
        $this->referenciaExterna = $referenciaExterna;
    }
    
    public function getAgencia() {
        return $this->agencia;
    }

    public function setAgencia($agencia) {
        $this->agencia = $agencia;
    }
    
    public function getTotalNetoAgencia() {
        return $this->totalNetoAgencia;
    }

    public function getReferenciaExternaAgencia() {
        return $this->referenciaExternaAgencia;
    }

    public function setTotalNetoAgencia($totalNetoAgencia) {
        $this->totalNetoAgencia = $totalNetoAgencia;
    }

    public function setReferenciaExternaAgencia($referenciaExternaAgencia) {
        $this->referenciaExternaAgencia = $referenciaExternaAgencia;
    }
    
    public function getMonedaAgencia() {
        return $this->monedaAgencia;
    }

    public function setMonedaAgencia($monedaAgencia) {
        $this->monedaAgencia = $monedaAgencia;
    }
    
    public function getUtilizarBonoAgencia() {
        return $this->utilizarBonoAgencia;
    }

    public function setUtilizarBonoAgencia($utilizarBonoAgencia) {
        $this->utilizarBonoAgencia = $utilizarBonoAgencia;
    }
    
    public function getMovil() {
        return $this->movil;
    }

    public function setMovil($movil) {
        $this->movil = $movil;
    }
    
    public function getSerieFactura() {
        return $this->serieFactura;
    }

    public function setSerieFactura($serieFactura) {
        $this->serieFactura = $serieFactura;
    }
    
    public function getSerieFacturacionEspecial() {
        return $this->serieFacturacionEspecial;
    }

    public function setSerieFacturacionEspecial($serieFacturacionEspecial) {
        $this->serieFacturacionEspecial = $serieFacturacionEspecial;
    }
    
    public function getFacturar() {
        return $this->facturar;
    }

    public function setFacturar($facturar) {
        $this->facturar = $facturar;
    }
    
    public function getIdentificadorWeb() {
        return $this->identificadorWeb;
    }

    public function setIdentificadorWeb($identificadorWeb) {
        $this->identificadorWeb = $identificadorWeb;
    }
}