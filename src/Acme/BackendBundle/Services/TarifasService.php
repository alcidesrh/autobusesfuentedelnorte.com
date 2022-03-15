<?php

namespace Acme\BackendBundle\Services;

use Acme\TerminalOmnibusBundle\Entity\Cliente;
use Doctrine\DBAL\DBALException;
use Acme\BackendBundle\Exceptions\RuntimeExceptionCode;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class TarifasService {
      
   protected $container;
   protected $securityContext;
   
   public function __construct($container) { 
        $this->container = $container;
   }
   
   private function getSecurityContext(){
       if($this->securityContext === null){
           $this->securityContext = $this->container->get("security.context");
       }
       return $this->securityContext;
   }
   
   public function getTarifaBoleto($idEstacionOrigen, $idEstacionDestino, $idTipoPago, $idClaseBus, $idClaseAsiento, $fechaSalida ,$fechaEfectividad = null){
        if($fechaEfectividad === null){
            $fechaEfectividad = new \DateTime();
        }        
        $tarifaBoleto = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:TarifaBoleto')->
                getTarifaBoleto($idEstacionOrigen, $idEstacionDestino, $idClaseBus, $idClaseAsiento, $fechaSalida, $fechaEfectividad);     
        if($tarifaBoleto !== null){
            $tarifaBoleto->setUserEstacion($this->getSecurityContext()->getToken()->getUser());
            $tarifaBoleto->setTipoPago($idTipoPago);
        }
        return $tarifaBoleto;
    }
   
    public function getTarifaEncomiendaEfectivo($importe, $fechaEfectividad = null){
        
        try {
            if($fechaEfectividad === null){
                $fechaEfectividad = new \DateTime();
            }
            $repositorio = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:TarifaEncomiendaEfectivo');
            return $repositorio->getTarifaEncomiendaEfectivo($importe, $fechaEfectividad);
        } catch (DBALException $exc) {
            throw new \RuntimeException("El importe debe estar en el rango de 0 hasta 99999.99", RuntimeExceptionCode::VALIDACION);
        }
    }
    
    public function getTarifaEncomiendaEspeciales($idTipo, $fechaEfectividad = null){
        if($fechaEfectividad === null){
            $fechaEfectividad = new \DateTime();
        }
        $repositorio = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:TarifaEncomiendaEspeciales');
        $tarifa = $repositorio->getTarifaEncomiendaEspeciales($idTipo, $fechaEfectividad);
        return $tarifa;
    }
    
    public function getTarifaEncomiendaPaquetesVolumen($volumen, $fechaEfectividad = null){
        try {
            if($fechaEfectividad === null){
                $fechaEfectividad = new \DateTime();
            }
            $repositorio = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:TarifaEncomiendaPaquetesVolumen');
            $tarifa = $repositorio->getTarifaEncomiendaPaquetesVolumen($volumen, $fechaEfectividad);
            return $tarifa;
        } catch (DBALException $exc) {
            throw new \RuntimeException("El volumen debe estar en el rango de 0 hasta 99999.99", RuntimeExceptionCode::VALIDACION);
        }
    }
    
    public function getTarifaEncomiendaPaquetesPeso($peso, $fechaEfectividad = null){
        try {
            if($fechaEfectividad === null){
                $fechaEfectividad = new \DateTime();
            }
            $repositorio = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:TarifaEncomiendaPaquetesPeso');
            $tarifa = $repositorio->getTarifaEncomiendaPaquetesPeso($peso, $fechaEfectividad);
            return $tarifa;
        } catch (DBALException $exc) {
            throw new \RuntimeException("El peso debe estar en el rango de 0 hasta 99999.99", RuntimeExceptionCode::VALIDACION);
        }
    }
    
    public function getTarifaEncomiendaDistancia($estacionOrigen, $estacionDestino, $fechaEfectividad = null){
        
        if($fechaEfectividad === null){
            $fechaEfectividad = new \DateTime();
        }
        
        if($estacionOrigen instanceof Estacion){
            $estacionOrigen = $estacionOrigen->getId();
        }
        if($estacionDestino instanceof Estacion){
            $estacionDestino = $estacionDestino->getId();
        }
        $tarifa = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:TarifaEncomiendaDistancia')
                ->getTarifaEncomiendaDistancia($estacionOrigen, $estacionDestino, $fechaEfectividad);
        return $tarifa;
    }
}

?>
