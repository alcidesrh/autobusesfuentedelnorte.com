<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;

class ProcesarBoletosService implements ScheduledServiceInterface{
    
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
    
    public function procesarBoletosPendientes($options = null){
        $this->logger->warn("procesarBoletos - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $fechaEnd = $this->getCurrentFecha();
        if(isset($options["fecha"])){
            $fechaEnd = $options["fecha"];
        }
        $fechaEnd->modify("-12 hour");
        
        $boletos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarBoletosPendientes($fechaEnd);
//        var_dump("Listado: " .  count($boletos). " boletos pendientes.");
        if(count($boletos) === 0){
            $this->logger->warn("No existen boletos pendientes.");
        }else{
            $this->logger->warn("Existen " .  count($boletos). " boletos pendientes.");
            $em = $this->doctrine->getManager();
            $estado = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::TRANSITO);
            foreach ($boletos as $item) {
//                var_dump("Actualizando boleto con id: " . $item->getid());
                $item->setEstado($estado);
                $em->persist($item);
            }
        }
        $this->logger->warn("procesarBoletos - end");
    }
     
    public function setScheduledJob(Job $job = null) {
        
        $this->job = $job;
        
        $option = array();
        
//        $option["fecha"] = new \DateTime("2014-10-30");
        
        try {
            $this->logger->warn("start-procesarBoletos");
            $this->procesarBoletosPendientes($option);
            $this->logger->warn("end-procesarBoletos");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso procesarBoletos.");
            throw $ex;
        }
    }
    
}
