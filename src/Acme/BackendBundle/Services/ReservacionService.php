<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\TerminalOmnibusBundle\Entity\EstadoReservacion;

class ReservacionService implements ScheduledServiceInterface{
      
    protected $container;
    protected $doctrine;
    protected $utilService;
    protected $logger;
    protected $options;
    protected $tiempoCancelacion;
    protected $diasCancelacion;
    protected $job;
      
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->utilService = $this->container->get('acme_backend_util');
        $this->logger = $this->container->get('logger');
        $this->options = array(      
            
        );
        $this->tiempoCancelacion = 1;
        $this->diasCancelacion = 3;
        $this->job = null;
    }    
   
    private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
    
    public function vencerReservacionesFueraTiempo($options = null){
        
        $this->logger->warn("vencerReservacionesFueraTiempo ----- INIT -------");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $fechaLimiteSistema = new \DateTime();
        $this->logger->warn("Buscando reservaciones internas a expirar.");
        $reservaciones = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->getReservacionesInternasFueraTiempo($fechaLimiteSistema, $this->diasCancelacion, $this->tiempoCancelacion);
        if(count($reservaciones) === 0){
            $this->logger->warn("No existen reservaciones por vencer.");
        }else{
            $this->logger->warn("Existen ".  count($reservaciones)." reservaciones por vencer.");
            $estadoVencida = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::VENCIDA);
            $em = $this->doctrine->getManager();
            foreach ($reservaciones as $reservacion) {
                $this->logger->warn("Venciendo reservacion:" . $reservacion->getId());
                $reservacion->setEstado($estadoVencida);
                $em->persist($reservacion);
            } 
        }
        
        $this->logger->warn("Buscando reservaciones de portales web a expirar.");
        $reservaciones = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->getReservacionesExternasFueraTiempo($fechaLimiteSistema);
        if(count($reservaciones) === 0){
            $this->logger->warn("No existen reservaciones por vencer.");
        }else{
            $this->logger->warn("Existen ".  count($reservaciones)." reservaciones por vencer.");
            $em = $this->doctrine->getManager();
            foreach ($reservaciones as $reservacion) {
                $this->logger->warn("Venciendo reservacion:" . $reservacion->getId());
                $estadoVencida = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::VENCIDA);
                $reservacion->setEstado($estadoVencida);
                $em->persist($reservacion);
            } 
        }
        
        $this->logger->warn("vencerReservacionesFueraTiempo ----- END -------");
    }
     
    public function setScheduledJob(Job $job = null) {
        $this->logger->warn("setScheduledJob - init");
        $this->job = $job;
        try {
            $this->logger->warn("start-vencerReservacionesFueraTiempo");
            $this->vencerReservacionesFueraTiempo();
            $this->logger->warn("end-vencerReservacionesFueraTiempo");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso vencerReservacionesFueraTiempo.");
            throw $ex;
        }
        $this->logger->warn("setScheduledJob - end");
    }

}

?>
