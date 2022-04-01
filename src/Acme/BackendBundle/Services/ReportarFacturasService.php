<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\TerminalOmnibusBundle\Entity\Empresa;

class ReportarFacturasService implements ScheduledServiceInterface{
    
    protected $container;
    protected $doctrine;
    protected $utilService;
    protected $logger;
    protected $options;
    protected $job;
    protected $cantidadMaxSend;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->utilService = $this->container->get('acme_backend_util');
        $this->logger = $this->container->get('logger');
        $this->options = array();
        $this->job = null;
        $this->cantidadMaxSend = 100;
    }   
    
    private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
    
    public function reportarBoletosFacturados($options = null){
        // $this->logger->warn("reportarFacturado - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("-1 day");
        if(isset($options["fecha"])){
            $fechaDia = $options["fecha"];
        }
        
        $estaciones = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getEstacionesReporting();
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            if($empresa instanceof Empresa){
                if($empresa->getReportarBoletoFacturado()){
                    foreach ($estaciones as $estacion) {
                        // $message = "BOLETO. CHEKING. ESTACION: " . $estacion->getIdAlias() . ", EXT (" . $estacion->getIdExternoBoleto() . ")" . ". Empresa: ". $empresa->getIdAlias().", EXT (".$empresa->getIdExterno().")";
                        // var_dump($message);
                        // $this->logger->warn($message);
                        if($estacion->getIdExternoBoleto() === null || $empresa->getIdExterno() === null){
                            continue;
                        }
                        
                        // $message = "BUSCANDO FACTURAS DE BOLETOS DEL DIA: " . $fechaDia->format("d-m-Y");
                        // $this->logger->warn($message);
                        // var_dump($message);
                
                        $boletos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarBoletosFacturado($estacion, $empresa, $fechaDia);
                        if(count($boletos) <= 0){
                            // $message = "No se encontraron facturas";
                            // $this->logger->warn($message);
                            // var_dump($message);
                            continue;
                        }
                
                        // $message = "Cantidad de Boletos: " . count($boletos) . ".";
                        // $this->logger->warn($message);
                        // var_dump($message);
                        
                        $listaDataItems = array();
                        foreach ($boletos as $boleto){
                            $facturaGenerada = $boleto->getFacturaGenerada();
                            $listaDataItems[] = array(
                                "importeReal" => $boleto->getPrecioCalculadoMonedaBase(),
                                "importeFactura" => $facturaGenerada->getImporteTotal(),
                                "fechaFactura" => $facturaGenerada->getFecha()->format("d-m-Y H:i:s"),
                                "fechaCreacion" => $facturaGenerada->getFechaCreacion()->format("d-m-Y H:i:s"),
                                "serieFactura" => $facturaGenerada->getFactura()->getSerieResolucionFactura(),
                                "consecutivo" => $facturaGenerada->getConsecutivo(),
                                "minimoSRF" => $facturaGenerada->getFactura()->getMinimoResolucionFactura(),
                                "maximoSRF" => $facturaGenerada->getFactura()->getMaximoResolucionFactura(),
                                "nombreSRF" => $facturaGenerada->getFactura()->getNombreResolucionFactura()
                            );
                        }
                        
                        // $message = "Existe un total de  " . count($listaDataItems) . " facturas de boletos.";
                        // $this->logger->warn($message);
                        // var_dump($message);
                        $listaItemsPaginada = array_chunk($listaDataItems, $this->cantidadMaxSend);
                        foreach ($listaItemsPaginada as $listaItem) {
                            // $message = "Enviando " . count($listaItem) . " facturas de boletos.";
                            // $this->logger->warn($message);
                            // var_dump($message);
                            $data = array(
                                "listaItems" => json_encode($listaItem),
                                "centroCosto" => $estacion->getIdExternoBoleto(),
                                "empresa" => $empresa->getIdExterno(),
                                "producto" => $empresa->getIdProductoBoletoExterno(),
                                "usuario" => $empresa->getIdUsuarioExterno(),
                                "cliente" => $empresa->getIdClienteExterno(),
                            );
                            
                            $response = $this->sendHttpPost($empresa->getUrlExterno(), $data);
                            $state = $response->mensajeServidor;
                            if($state !== "m0"){
                                throw new \RuntimeException($response->mensajeServidor);
                            }
                        }
                    }
                }
            }
        }
        
        // $this->logger->warn("reportarFacturado - end");
    }
    
    public function reportarEncomiendasFacturadas($options = null){
        
        // $this->logger->warn("reportarEncomiendasFacturado - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("-1 day");
        if(isset($options["fecha"])){
            $fechaDia = $options["fecha"];
        }
        
        $estaciones = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getEstacionesReporting();
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            if($empresa instanceof Empresa){
                if($empresa->getReportarEncomiendaFacturado()){
                    foreach ($estaciones as $estacion) {
                        // $message = "ENCOMIENDA. CHEKING. ESTACION: " . $estacion->getIdAlias() . ", EXT (" . $estacion->getIdExternoEncomienda(). ")".". Empresa: ". $empresa->getIdAlias().", EXT (".$empresa->getIdExterno().")";
                        // $this->logger->warn($message);
                        // var_dump($message);
                        if($estacion->getIdExternoEncomienda() === null || $empresa->getIdExterno() === null){
                            continue;
                        }
                        
                        // $message = "BUSCANDO FACTURAS DE ENCOMIENDA DEL DIA: " . $fechaDia->format("d-m-Y");
                        // $this->logger->warn($message);
                        // var_dump($message);
                        $encomiendas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasFacturadas($estacion, $empresa, $fechaDia);
                        if(count($encomiendas) <= 0){
                            // $message = "No se encontraron facturas";
                            // $this->logger->warn($message);
                            // var_dump($message);
                            continue;
                        }
                        
                        // $message = "Cantidad de Encomiendas: " . count($encomiendas);
                        // $this->logger->warn($message);
                        // var_dump($message);
                        
                        $mapFacturasGeneradasByTotalImporteBase = array();
                        $mapFacturasGeneradas = array();
                        foreach ($encomiendas as $encomienda){
                            if(isset($mapFacturasGeneradasByTotalImporteBase[$encomienda->getFacturaGenerada()->getId()])){
                                $importe = $mapFacturasGeneradasByTotalImporteBase[$encomienda->getFacturaGenerada()->getId()];
                                $importe += $encomienda->getPrecioCalculadoMonedaBase();
                                $mapFacturasGeneradasByTotalImporteBase[$encomienda->getFacturaGenerada()->getId()] = $importe;
                            }else{
                                $mapFacturasGeneradas[$encomienda->getFacturaGenerada()->getId()] = $encomienda->getFacturaGenerada();
                                $mapFacturasGeneradasByTotalImporteBase[$encomienda->getFacturaGenerada()->getId()] = $encomienda->getPrecioCalculadoMonedaBase();
                            }
                        }
                        
                        $listaDataItems = array();               
                        foreach ($mapFacturasGeneradasByTotalImporteBase as $idFacturaGenerada => $totalImporteBase) {
                            $facturaGenerada = $mapFacturasGeneradas[$idFacturaGenerada];
                            $listaDataItems[] = array(
                                "importeReal" => $totalImporteBase,
                                "importeFactura" => $facturaGenerada->getImporteTotal(),
                                "fechaFactura" => $facturaGenerada->getFecha()->format("d-m-Y H:i:s"),
                                "fechaCreacion" => $facturaGenerada->getFechaCreacion()->format("d-m-Y H:i:s"),
                                "serieFactura" => $facturaGenerada->getFactura()->getSerieResolucionFactura(),
                                "consecutivo" => $facturaGenerada->getConsecutivo(),
                                "minimoSRF" => $facturaGenerada->getFactura()->getMinimoResolucionFactura(),
                                "maximoSRF" => $facturaGenerada->getFactura()->getMaximoResolucionFactura(),
                                "nombreSRF" => $facturaGenerada->getFactura()->getNombreResolucionFactura()
                            );
                        }
                        
                        // $message = "Existe un total de  " . count($listaDataItems) . " facturas de encomiendas.";
                        // $this->logger->warn($message);
                        // var_dump($message);
                        $listaItemsPaginada = array_chunk($listaDataItems, $this->cantidadMaxSend);
                        foreach ($listaItemsPaginada as $listaItem) {
                            // $message = "Enviando " . count($listaItem) . " facturas de encomiendas.";
                            // $this->logger->warn($message);
                            // var_dump($message);
                            $data = array(
                                "listaItems" => json_encode($listaItem),
                                "centroCosto" => $estacion->getIdExternoEncomienda(),
                                "empresa" => $empresa->getIdExterno(),
                                "producto" => $empresa->getIdProductoEncomiendaExterno(),
                                "usuario" => $empresa->getIdUsuarioExterno(),
                                "cliente" => $empresa->getIdClienteExterno()
                            );
                            
                            $response = $this->sendHttpPost($empresa->getUrlExterno(), $data);
                            $state = $response->mensajeServidor;
                            if($state !== "m0"){
                                throw new \RuntimeException($response->mensajeServidor);
                            }
                        }          
                    }
                }
            }
        }
        
        // $this->logger->warn("reportarEncomiendasFacturado - end");
    }
     
    public function sendHttpPost($url, $data){
        
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => http_build_query($data),
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n"
            )
        );
        
        $context  = stream_context_create( $options );
        $response = "";
                        
        try {
            $response = file_get_contents( $url, false, $context );
            // $this->logger->warn("RESPONSE-INIT");
            // $this->logger->warn($response);
            // $this->logger->warn("RESPONSE-END");
        } catch (\Exception $exc) {
            $this->logger->warn("FAYO FILE GET CONTENTS - INTI.");
            $this->logger->error($response);
            $this->logger->warn("DATOS QUE LANZARON EL ERROR:");
            var_dump($data);
            $this->logger->warn("FAYO FILE GET CONTENTS - END.");
            throw new \RuntimeException("Error01. No se obtuvo respueta del servidor.");
        }
        
        try {
            $response = json_decode( $response );
        } catch (\Exception $exc) {
            $this->logger->warn("RESPONSE-FAILT-JSON-DECODE-INIT");
            $this->logger->error($response);
            $this->logger->warn("DATOS QUE LANZARON EL ERROR:");
            var_dump($data);
            $this->logger->warn("RESPONSE-FAILT-END");
            throw new \RuntimeException("Error02. No se pudo decodificar la respuesta del servidor.");
        }
        
        if(!isset($response) || $response === null || $response === false || !isset($response->mensajeServidor))
        {
            $this->logger->warn("RESPONSE-FAILT-INIT");
            $this->logger->error($response);
            $this->logger->warn("DATOS QUE LANZARON EL ERROR:");
            var_dump($data);
            $this->logger->warn("RESPONSE-FAILT-END");
            throw new \RuntimeException("Error03. No se obtuvo respuesta del servidor.");
        }
        
        return $response;
    }
    
    public function setScheduledJob(Job $job = null) {
        
        $this->job = $job;
        
        $option = array();
//        $option["fecha"] = new \DateTime("2015-03-30");
//        for ($index = 1; $index <= 6; $index++) {
//            $option["fecha"] = new \DateTime("2015-03-".$index);
            try {
                // $this->logger->warn("start-reportarFacturado");
                $this->reportarBoletosFacturados($option);
                // $this->logger->warn("end-reportarFacturado");
            } catch (\Exception $ex) {
                $this->logger->warn("Ocurrio una exception en el proceso reportarFacturado.");
                throw $ex;
            }
            try {
                // $this->logger->warn("start-reportarFacturado");
                $this->reportarEncomiendasFacturadas($option);
                // $this->logger->warn("end-reportarFacturado");
            } catch (\Exception $ex) {
                $this->logger->warn("Ocurrio una exception en el proceso reportarFacturado.");
                throw $ex;
            } 
//        }
        
    }
    
}
