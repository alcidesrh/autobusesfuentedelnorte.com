<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\BackendBundle\Services\UtilService;

class ParcialesFacturadosService implements ScheduledServiceInterface{
    
    protected $container;
    protected $doctrine;
    protected $utilService;
    protected $logger;
    protected $options;
    protected $job;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->utilService = $this->container->get('acme_backend_util');
        $this->logger = $this->container->get('logger');
        $this->options = array();
        $this->job = null;
    }   

    private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
    
    public function sendVentasParciales($options = null){
        // $this->logger->warn("sendVentasParciales - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $fechaDia = $this->getCurrentFecha();
        $time = intval($fechaDia->format('H')); //Hora Militar
        if($time >= 8 && $time <= 22){
            $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
            foreach ($empresas as $empresa) {
                // $this->logger->warn("Buscando ventas parciales del " . $fechaDia->format('d-m-Y') . " para la empresa " . $empresa->getAlias());    
                $estaciones = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getEstacionesEmitieronOperaciones($fechaDia, $empresa);
                    
                $resumenByEstacion = array();
                $valuesBoletos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarTotalesBoletos($fechaDia, $empresa, $estaciones);
                foreach ($valuesBoletos as $item) {
                    $id = $item['idEstacionCreacion'];
                    $resumenByEstacion[$id] = $item;
                }
                $valuesEncomiendas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarTotalesEncomienda($fechaDia, $empresa, $estaciones);
                foreach ($valuesEncomiendas as $item) {
                    $id = $item['idEstacionCreacion'];
                    if(array_key_exists($id, $resumenByEstacion)){
                        $temp = array_merge($resumenByEstacion[$id], $item);
                        $resumenByEstacion[$id] = $temp;
                    }else{
                        $resumenByEstacion[$id] = $item;
                    }
                }
                    
                $correos = $empresa->getCorreos();
//                $correos = array("javiermarti84@gmail.com");
                if($correos !== null && count($correos) !== 0){
                    $now = new \DateTime();
                    $now = $now->format('Y-m-d H:i:s');
                    // $this->logger->warn("Enviando correo notificando parciales del " . $fechaDia->format('d-m-Y') . " para la empresa " . $empresa->getAlias() . ".");
                    $subject = "NVP_" . $now . ". Notificación parciales del " . $fechaDia->format('d-m-Y') . " hasta las " . $fechaDia->format('h:i A') . " en la empresa: " . $empresa->getAlias() . "."; 
                    UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_parciales.html.twig', array(
                        'title' => 'Parciales',
                        'empresa' => $empresa,
                        'fechaDia' => $fechaDia,
                        'resumenByEstacion' => $resumenByEstacion
                    )));
                }
            }
        }else{
            // $this->logger->warn("No se envia reporte de parciales porque son las " . $time . " en hora militar.");
        }
        
        // $this->logger->warn("sendVentasParciales - end");
    }
    
    public function setScheduledJob(Job $job = null) {
        // $this->logger->warn("setScheduledJob - init");
        $this->job = $job;
        try {
            // $this->logger->warn("start-sendVentasParciales");
            $this->sendVentasParciales();
            // $this->logger->warn("end-sendVentasParciales");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso sendVentasParciales.");
            throw $ex;
        }
        // $this->logger->warn("setScheduledJob - end");
    }
    
}
