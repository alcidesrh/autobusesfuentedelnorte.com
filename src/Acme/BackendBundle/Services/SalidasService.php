<?php

namespace Acme\BackendBundle\Services;

use Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial;
use Acme\TerminalOmnibusBundle\Entity\ItinerarioCiclico;
use Acme\TerminalOmnibusBundle\Entity\Salida;
use Acme\TerminalOmnibusBundle\Entity\EstadoSalida;
use Acme\TerminalOmnibusBundle\Entity\Ruta;
use Acme\TerminalOmnibusBundle\Entity\DiaSemana;
use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\TerminalOmnibusBundle\Entity\SalidaBitacora;

class SalidasService implements ScheduledServiceInterface{
      
   protected $container;
   protected $doctrine;
   protected $utilService;
   protected $logger;
   protected $options;
   protected $cantidadMeses;
   protected $job;
      
   public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->utilService = $this->container->get('acme_backend_util');
        $this->logger = $this->container->get('logger');
        $this->options = array(      
            'flush' => false
        );
        $this->cantidadMeses = 3;
        $this->job = null;
   }
   
   private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
   
    //SE EJECUTA DESDE EL MODULO DE ITINERARIO ESPECIAL, SE PUEDE ACTUALIZAR SIN PROBLEMAS PORQUE LA RELACION ES 1 X 1.
    public function procesarSalidaPorItinerarioEspecial(ItinerarioEspecial $itinerario, $options = null){
        $this->logger->warn("procesarSalidaPorItinerarioEspecial ----- INIT -------");
        $this->logger->warn("idItinerario:" . $itinerario->getId());
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }

        $salida = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidaByIntinerarioEspecial($itinerario->getId());
        if($salida === null){
            $this->logger->warn("Generando salida para fecha:" . $itinerario->getFecha()->format('d-m-Y H:i:s'));
            $salida = new Salida();
            $salida->setItinerario($itinerario);
            $salida->setFecha($itinerario->getFecha());
            $salida->setTipoBus($itinerario->getTipoBus());
            $salida->setEmpresa($itinerario->getEmpresa());
            if($itinerario->getActivo() && $itinerario->getEmpresa()->getActivo()){
                $salida->setEstado($this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::PROGRAMADA));
            }else{
                $salida->setEstado($this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::CANCELADA));
            }
         }else{
            $this->logger->warn("Actualizando salida de fecha:" . $itinerario->getFecha()->format('d-m-Y H:i:s'));
            $salida->setFecha($itinerario->getFecha());
            $salida->setTipoBus($itinerario->getTipoBus());
            $salida->setEmpresa($itinerario->getEmpresa());
            if($itinerario->getActivo() && $itinerario->getEmpresa()->getActivo()){
                $salida->setEstado($this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::PROGRAMADA));
            }else{
                $salida->setEstado($this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::CANCELADA));
            }
         }
         
         $user = $options['user'];
         $salidaBitacora = new SalidaBitacora();
         $salidaBitacora->setEstado($salida->getEstado());
         $salidaBitacora->setUsuario($user);
         $salidaBitacora->setFecha(new \DateTime());
         $descripcion = "Adicionando salida especial. Motivo: " . $itinerario->getMotivo();
         $salidaBitacora->setDescripcion($descripcion);
         $salida->addBitacoras($salidaBitacora);
         
         $em = $this->doctrine->getManager();
         $em->persist($salida);
         
         if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
            if($salida->isValidToSync()){
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($salida->getNivelSync());
                $jobSync->setType($salida->getTypeSync());
                if($salida->getId() === null || $salida->getId() === ""){ 
                    $em->flush(); //Se hace flush para generar el ID
                }
                $jobSync->setData($salida->getDataArrayToSync());
                $this->container->get('acme_job_sync')->createJobSync($jobSync, false);
            }
         }
         
         if($options['flush'] === true){
            $em->flush();
         }
        
        $this->logger->warn("procesarSalidaPorItinerarioEspecial ----- END -------");
    }
    
    //SE EJECUTA DESDE EL MODULO DE ITINERARIO CICLICO, SOLO SE PUEDE MODIFICAR EL ATRIBUTO ACTIVO.
    public function procesarSalidaPorItinerarioCiclico(ItinerarioCiclico $itinerario, $options = null){
        $this->logger->warn("procesarSalidaPorItinerarioCiclico ----- INIT -------");
        $this->logger->warn("idItinerario:" . $itinerario->getId());
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $em = $this->doctrine->getManager();
        $fechaLimiteSistema = new \DateTime();
        $fechaLimiteSistema->modify('+'. $this->cantidadMeses.' month');
        $fechaLimiteSistema->setTime(23, 59, 0);
        $fechaActualSistema = new \DateTime();
        $fechaActual = clone $fechaActualSistema;
        $fechaActual->modify('-1 day');
        $nuevo = $itinerario->getId() === null || trim($itinerario->getId()) === "" || trim($itinerario->getId()) === "0";  
        $mapSalidas = array();
        if($nuevo === false){
            $salidas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidasByIntinerarioCiclico($itinerario->getId(), $fechaActual, $fechaLimiteSistema);           
            $mapSalidas = $this->getMapSalidas($salidas);
        }
        
        if($itinerario->getActivo() === true){
            
            $horario = $itinerario->getHorarioCiclico()->getHora();
            $hour = $horario->format('H');
            $minute = $horario->format('i');
            $diaSemana = $itinerario->getDiaSemana()->getPHPValue();
            $fechaActual->modify('next '.$diaSemana);
            $fechaActual->setTime($hour, $minute, 0);
            
            while ($this->utilService->compararFechas($fechaActual, $fechaLimiteSistema) <= 0) {
                //Buscar la salida que corresponde a la semana de de cada fecha
                $salida = $this->buscarSalida($fechaActual, $mapSalidas);
                if($salida === null){
                    $empresa = $this->getEmpresa($itinerario->getRuta(), $fechaActual);
                    if($empresa === null){
                        $this->logger->warn("No se pudo generar el itinerario cíclico pq no está definido el calendario de factura para la ruta: " . 
                                $itinerario->getRuta() . ", en la fecha: " . $fechaActual->format('d-m-Y H:i:s'). ".");
                    }else{
                        if($itinerario->getEmpresa() === null || $empresa === $itinerario->getEmpresa()){
                            if($empresa->getActivo()){
                                $this->logger->warn("Generando salida para fecha:" . $fechaActual->format('d-m-Y H:i:s'));
                                $salida = new Salida();
                                $salida->setEstado($this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::PROGRAMADA));
                                $salida->setFecha(clone $fechaActual);
                                $salida->setEmpresa($empresa);
                                $salida->setItinerario($itinerario);
                                $salida->setTipoBus($itinerario->getTipoBus()); 
                                
                                $salidaBitacora = new SalidaBitacora();
                                $salidaBitacora->setEstado($salida->getEstado());
                                $salidaBitacora->setFecha(new \DateTime());
                                $salidaBitacora->setDescripcion("Adicionando salida de itinerario ciclico con id: " . $itinerario->getId());
                                $salida->addBitacoras($salidaBitacora);
                                
                                $em->persist($salida);
                                
                                if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                                    if($salida->isValidToSync()){
                                        $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                                        $jobSync->setNivel($salida->getNivelSync());
                                        $jobSync->setType($salida->getTypeSync());
                                        if($salida->getId() === null || $salida->getId() === ""){ 
                                            $em->flush(); //Se hace flush para generar el ID
                                        }
                                        $jobSync->setData($salida->getDataArrayToSync());
                                        $this->container->get('acme_job_sync')->createJobSync($jobSync, false);
                                    }
                                }
                                
                            }else{
                                $this->logger->warn("No se pudo generar el itinerario cíclico para la empresa: " . $empresa->getAlias() . " pq no esta activa.");
                            }
                        }
                    }
                    
                 }else{
                    $this->logger->warn("Chequeando estado de la salida con id: " . $salida->getId(). ", estado: " . $salida->getEstado()->getNombre() . ".");   
                    if($salida->getEstado()->getId() === EstadoSalida::CANCELADA){
                        $this->logger->warn("Programado salida con id: " . $salida->getId(). " que estaba en estado cancelada.");    
                        
                        $salida->setEstado($this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::PROGRAMADA));
                        $salidaBitacora = new SalidaBitacora();
                        $salidaBitacora->setEstado($salida->getEstado());
                        $salidaBitacora->setFecha(new \DateTime());
                        $salidaBitacora->setDescripcion("Programando salida por activacion del itinerario.");
                        $salida->addBitacoras($salidaBitacora);
                        
                        $em->persist($salida);
                
                        if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                            if($salida->isValidToSync()){
                                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                                $jobSync->setNivel($salida->getNivelSync());
                                $jobSync->setType($salida->getTypeSync());
                                $jobSync->setData($salida->getDataArrayToSync());
                                $this->container->get('acme_job_sync')->createJobSync($jobSync, false);
                            }
                        }
                    }
                 }
                 
                 $fechaActual->modify('next '.$diaSemana);
                 $fechaActual->setTime($hour, $minute, 0);
           }
        }else{
            foreach ($mapSalidas as $fecha => $salida) {
                $salida->setEstado($this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::CANCELADA));
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setDescripcion("Cancelando salida por desactivacion del itinerario.");
                $salida->addBitacoras($salidaBitacora);
                
                $em->persist($salida);
                
                if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                    if($salida->isValidToSync()){
                        $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                        $jobSync->setNivel($salida->getNivelSync());
                        $jobSync->setType($salida->getTypeSync());
                        $jobSync->setData($salida->getDataArrayToSync());
                        $this->container->get('acme_job_sync')->createJobSync($jobSync, false);
                    }
                }
            }
        }
        
        if($options['flush'] === true){
            $em->flush();
        }
        $this->logger->warn("procesarSalidaPorItinerarioCiclico ----- END -------");
     }
     
     //SE EJECUTA DE FORMA PERIODICA, SE SUPONE QUE NO HAY CAMBIOS SOLO GENERAR LAS NUEVAS SALIDAS CICLICAS.
     public function procesarSalidaPorItinerarioCiclicoFormaPeriodica($options = null){
        $this->logger->warn("procesarSalidaPorItinerarioCiclicoFormaPeriodica ----- INIT -------");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $em = $this->doctrine->getManager();
        $fechaLimiteSistema = $this->getCurrentFecha();
        $fechaLimiteSistema->modify('+'.$this->cantidadMeses.' month');
//        $fechaLimiteSistema->modify('+2 month');
        $fechaLimiteSistema->setTime(23, 59, 0);
        $estadoProgramada = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::PROGRAMADA);
        $estadoCancelada = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::CANCELADA);
        $itinerarioCiclicos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:ItinerarioCiclico')->findByActivo(true);
        $total = count($itinerarioCiclicos);
        $this->logger->warn("Cantidad de itinerarios ciclicos detectados: " . $total . ".");
        var_dump("Cantidad de itinerarios ciclicos detectados: " . $total . ".");
        $pos = 1;
        $itemsProccessed = array();
        foreach ($itinerarioCiclicos as $itinerario) {
             $detalleLog = "ITINERARIO:".$itinerario->getId().". RUTA:".$itinerario->getRuta() . ". ";
             if($itinerario->getActivo() === true){
                 $this->logger->warn("PROCESANDO ITEM ".$pos." de " .$total. ". ID:".$itinerario->getId().". RUTA:".$itinerario->getRuta() . ". ");
                 var_dump("PROCESANDO ITEM ".$pos." de " .$total. ". ID:".$itinerario->getId().". RUTA:".$itinerario->getRuta() . ". ");
                 $fechaActualSistema = $this->getCurrentFecha();
                 $fechaActualSistema->modify('-1 day');
                 $salidas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidasByIntinerarioCiclico($itinerario->getId(), $fechaActualSistema, $fechaLimiteSistema);
                 $mapSalidas = $this->getMapSalidas($salidas); //Funciona siempre que exista una salida por dia
                 $horario = $itinerario->getHorarioCiclico()->getHora();
                 $hour = $horario->format('H');
                 $minute = $horario->format('i');
                 $diaSemana = $itinerario->getDiaSemana()->getPHPValue();
                 $fechaActualSistema->modify('next '.$diaSemana);
                 $fechaActualSistema->setTime($hour, $minute, 0);
                 while ($this->utilService->compararFechas($fechaActualSistema, $fechaLimiteSistema) <= 0) {
                    var_dump("Verificando fecha: " . $fechaActualSistema->format('d-m-Y H:i:s'));
                    $empresaCalendarioFacturacion = $this->getEmpresa($itinerario->getRuta(), $fechaActualSistema);
                    $keySalida = $fechaActualSistema->format('d-m-Y H:i:s');
                    if(!array_key_exists($keySalida, $mapSalidas)){
                        
                        //CASO PARA TODO LO NUEVO
                        if($empresaCalendarioFacturacion !== null && ($itinerario->getEmpresa() === null || $empresaCalendarioFacturacion === $itinerario->getEmpresa())){
                            if($empresaCalendarioFacturacion->getActivo()){
                                $salida = new Salida();
                                $salida->setEstado($estadoProgramada);
                                $salida->setFecha(clone $fechaActualSistema);
                                $salida->setEmpresa($empresaCalendarioFacturacion); //SIEMRPE SE CREA LA SALIDA CON EMPRESA DEL CALENDARIO DE FACTURACION
                                $salida->setItinerario($itinerario);
                                $salida->setTipoBus($itinerario->getTipoBus());
                                    
                                $salidaBitacora = new SalidaBitacora();
                                $salidaBitacora->setEstado($salida->getEstado());
                                $salidaBitacora->setFecha(new \DateTime());
                                $salidaBitacora->setDescripcion("Adicionando salida de itinerario ciclico con id: " . $itinerario->getId());
                                $salida->addBitacoras($salidaBitacora);
                                $em->persist($salida);
                                $this->logger->warn($detalleLog."Generando salida de fecha:" . $fechaActualSistema->format('d-m-Y H:i:s'));
                                $this->crearJobSync($salida);
                                $itemsProccessed[] = $salida;
                                
                            }else{
                                $this->logger->warn($detalleLog. "No se pudo generar el itinerario cíclico para la empresa: " . $empresaCalendarioFacturacion->getAlias() . " pq la empresa no esta activa.");
                            }
                            
                        }else if($empresaCalendarioFacturacion === null){
                            $this->logger->warn("No está definido el calendario de facturacion para la ruta: " . $itinerario->getRuta() . ", en la fecha: " . $fechaActualSistema->format('d-m-Y H:i:s'). ".");
                        }
                        
                     }else{
                         $this->logger->warn($detalleLog."Ya existe salida para fecha:" . $fechaActualSistema->format('d-m-Y H:i:s'));
                         $salida = $mapSalidas[$keySalida];
                         $cantidadBoletos = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->totalBoletosBySalida($salida->getId());
                         var_dump("Salida: ". $salida->getId(). ". Cantidad boletos: " . $cantidadBoletos);
                         if($cantidadBoletos == 0){
                             
                             if($empresaCalendarioFacturacion === null){  //Existio una empresa en el calendario pq se genero la salida, pero se quito y se dejo en blanco
                                 var_dump("CASO 1");
                                 if($salida->getCancelacionInterna() !== true){
                                    $salida->setEstado($estadoCancelada);
                                    $salidaBitacora = new SalidaBitacora();
                                    $salidaBitacora->setEstado($salida->getEstado());
                                    $salidaBitacora->setFecha(new \DateTime());
                                    $salidaBitacora->setDescripcion("CANCELACION INTERNA. Cancelando salida porque se elimino la empresa en el calendario de facturacion para la ruta: " . $itinerario->getRuta() . ", en la fecha: " . $fechaActualSistema->format('d-m-Y H:i:s'). ".");
                                    $salida->addBitacoras($salidaBitacora);
                                    $em->persist($salida);
                                    $this->logger->warn($detalleLog."Cancelando salida de fecha:" . $fechaActualSistema->format('d-m-Y H:i:s'));
                                    $this->crearJobSync($salida);
                                    $itemsProccessed[] = $salida;
                                 }
                                 
                             }else{
                                 
                                 if($empresaCalendarioFacturacion !== $salida->getEmpresa()){
                                     if($itinerario->getEmpresa() === null){
                                        var_dump("CASO 2");
                                        $salida->setEmpresa($empresaCalendarioFacturacion);
                                        $salidaBitacora = new SalidaBitacora();
                                        $salidaBitacora->setEstado($salida->getEstado());
                                        $salidaBitacora->setFecha(new \DateTime());
                                        $salidaBitacora->setDescripcion("Cambiando empresa de la salida por la empresa especificada en el calendario de facturacion para la ruta: " . $itinerario->getRuta() . ", en la fecha: " . $fechaActualSistema->format('d-m-Y H:i:s'). ".");
                                        $salida->addBitacoras($salidaBitacora);
                                        $em->persist($salida);
                                        $this->logger->warn($detalleLog."Cambiando empresa de la salida por la empresa especificada en el calendario de facturacion de fecha:" . $fechaActualSistema->format('d-m-Y H:i:s'));
                                        $this->crearJobSync($salida);
                                        $itemsProccessed[] = $salida;
                                        
                                     } else if($empresaCalendarioFacturacion !== $itinerario->getEmpresa()){
                                         //Este caso es el que cancela casi todas las salidas una vez que se cambio el calendario.
                                        var_dump("CASO 3");
                                        if($salida->getCancelacionInterna() !== true){
                                            $salida->setEstado($estadoCancelada);
                                            $salidaBitacora = new SalidaBitacora();
                                            $salidaBitacora->setEstado($salida->getEstado());
                                            $salidaBitacora->setFecha(new \DateTime());
                                            $salidaBitacora->setDescripcion("CANCELACION INTERNA. Cancelando salida porque no corresponde con la empresa especificada en el calendario de facturacion (".$empresaCalendarioFacturacion->getAlias().") para la ruta: " . $itinerario->getRuta() . ", en la fecha: " . $fechaActualSistema->format('d-m-Y H:i:s'). ".");
                                            $salida->addBitacoras($salidaBitacora);
                                            $em->persist($salida);
                                            $this->logger->warn($detalleLog."Cancelando salida porque no corresponde con la empresa especificada en el calendario de facturacion (" .$empresaCalendarioFacturacion->getAlias().") de fecha:" . $fechaActualSistema->format('d-m-Y H:i:s'));
                                            $this->crearJobSync($salida);
                                            $itemsProccessed[] = $salida;
                                        }
                                        
                                     }else{
                                         var_dump("CASO 4. ----------------------- ESTE NUNCA DEBE OCURRIR ---------------------");
                                         throw new \RuntimeException("CASO 4");
                                     }
                                     
                                 }else{
                                     var_dump("CASO 5");
                                     $this->logger->warn($detalleLog."La empresa de la salida coincide con la empresa del calendario de facturacion. Todo ok.");
                                 }
                             }
                             
                         }else{
                             
                             if($empresaCalendarioFacturacion === null || $empresaCalendarioFacturacion !== $salida->getEmpresa()){
                                var_dump("CASO 6");
                                if($salida->getCancelacionInterna() !== true){
                                    $salida->setEstado($estadoCancelada);
                                    $salidaBitacora = new SalidaBitacora();
                                    $salidaBitacora->setEstado($salida->getEstado());
                                    $salidaBitacora->setFecha(new \DateTime());
                                    $salidaBitacora->setDescripcion("Cancelando salida porque su empresa no corresponde con la empresa del calendario de facturacion para la ruta: " . $itinerario->getRuta() . ", en la fecha: " . $fechaActualSistema->format('d-m-Y H:i:s'). ".");
                                    $salida->addBitacoras($salidaBitacora);
                                    $em->persist($salida);
                                    $this->logger->warn($detalleLog."Cancelando salida porque su empresa no corresponde con la empresa del calendario de facturacion de fecha:" . $fechaActualSistema->format('d-m-Y H:i:s'));
                                    $this->notificandoCancelacionDeSalidaConBoletos($salida, $empresaCalendarioFacturacion, $cantidadBoletos);
                                    $this->crearJobSync($salida);
                                    $itemsProccessed[] = $salida;
                                }
                             }else{
                                var_dump("CASO 7");
                                $this->logger->warn($detalleLog."La empresa de la salida coincide con la empresa del calendario de facturacion. Todo ok.");
                             }
                         }
                         
                         //CHEQUEANDO SI HAY QUE ACTIVAR LA SALIDA, EN CASO DE QUE SE HUBIESE CANCELADO
                         if($empresaCalendarioFacturacion === $salida->getEmpresa() && 
                                 ($itinerario->getEmpresa() === null || $empresaCalendarioFacturacion === $itinerario->getEmpresa())){
                             if($salida->getCancelacionInterna()){
                                 var_dump("REPROGRAMANDO SALIDA.");
                                 $salida->setEstado($estadoProgramada);
                                 $salida->setCancelacionInterna(false);
                                 $salidaBitacora = new SalidaBitacora();
                                 $salidaBitacora->setEstado($salida->getEstado());
                                 $salidaBitacora->setFecha(new \DateTime());
                                 $salidaBitacora->setDescripcion("Reprogramando salida porque su empresa corresponde con la empresa del calendario de facturacion para la ruta: " . $itinerario->getRuta() . ", en la fecha: " . $fechaActualSistema->format('d-m-Y H:i:s'). ".");
                                 $salida->addBitacoras($salidaBitacora);
                                 $em->persist($salida);
                                 $this->logger->warn($detalleLog."Reprogramando salida porque su empresa corresponde con la empresa del calendario de facturacion en la fecha: ." . $fechaActualSistema->format('d-m-Y H:i:s'));
                                 $this->crearJobSync($salida);
                                 $itemsProccessed[] = $salida;
                             }
                         }
                     }
                     
                     $fechaActualSistema->modify('next '.$diaSemana);
                     $fechaActualSistema->setTime($hour, $minute, 0);
                 }
                         
             }else{
                 var_dump("CASO 10");
                 $this->logger->warn($detalleLog." El itinerario no esta activo.");
             }
             
             if($pos % 5 == 0){
                $this->persistObjects($itemsProccessed);
                $itemsProccessed = array();
             }
             $pos++;
         }
         
         $this->persistObjects($itemsProccessed);
         
         $this->logger->warn("procesarSalidaPorItinerarioCiclicoFormaPeriodica ----- END -------");
     }
     
     public function persistObjects($objects = array()){
        $objects = array_unique($objects);
        $this->logger->info("Persist Objects: " . count($objects));
        var_dump("Persist Objects: " . count($objects));
        
        if(count($objects) === 0){
            return;
        }        
        
        $em = $this->doctrine->getManager();
        $em->getConnection()->beginTransaction();
        try {
            
            foreach ($objects as $object) {
                $em->persist($object);
            }
            $em->flush();        
            $em->getConnection()->commit();
                
        }catch (\RuntimeException $exc) {
            $em->getConnection()->rollback();
            var_dump("Exception-----------------------------------------------------------------");
            var_dump($exc->getMessage());
            var_dump("ErrorException-----------------------------------------------------------------");
        }catch (\ErrorException $exc) {
            $em->getConnection()->rollback();
            var_dump("Exception-----------------------------------------------------------------");
            var_dump($exc->getMessage());
            var_dump("ErrorException-----------------------------------------------------------------");
        }catch (\Exception $exc) {
            $em->getConnection()->rollback();
            var_dump("Exception-----------------------------------------------------------------");
            var_dump($exc->getMessage());
            var_dump("ErrorException-----------------------------------------------------------------");
        }
    }
    
     public function notificandoCancelacionDeSalidaConBoletos($salida, $empresaCalendarioFacturacion = null, $cantidadBoletos){
         $correos = $salida->getEmpresa()->getCorreos();
         if($empresaCalendarioFacturacion !== null){
             $correos = array_merge($correos, $empresaCalendarioFacturacion->getCorreos());
         }
         $correos = array_merge($correos, $this->doctrine->getRepository('AcmeBackendBundle:User')->findEmailUserAdministrativosByEmpresa());
         $correos = array_merge($correos, $this->doctrine->getRepository('AcmeBackendBundle:User')->findEmailSuperAdmin());
         $correos = array_unique($correos);
//         var_dump($correos);
         if($correos !== null && count($correos) !== 0){
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $this->logger->warn("Enviando correo notificando cancelacion de la salida " . $salida->getId() . " con boletos.");
            $subject = "ALERT_NCSB_" . $now . ". Notificación de cancelacion de salidas con boletos."; 
            UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_salidas_canceladas_con_boletos.html.twig', array(
                'salida' => $salida,
                'empresaCalendarioFacturacion' => $empresaCalendarioFacturacion,
                'cantidadBoletos' => $cantidadBoletos
            )));
         }
     }
     
     public function crearJobSync($salida){
         $em = $this->doctrine->getManager();
         if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
            if($salida->isValidToSync()){
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($salida->getNivelSync());
                $jobSync->setType($salida->getTypeSync());
                if($salida->getId() === null || $salida->getId() === ""){ 
                    $em->flush(); //Se hace flush para generar el ID
                }
                $jobSync->setData($salida->getDataArrayToSync());
                $this->container->get('acme_job_sync')->createJobSync($jobSync, false);
            }
        }
     }
     
     public function buscarSalida($fechaActualSistema, $mapSalidas){
         $fechaRangoInit = clone $fechaActualSistema;
         if($fechaRangoInit->format("l") !== "Sunday"){
             $fechaRangoInit->modify('last ' . DiaSemana::DOMINGO);
         }
         $fechaRangoInit->setTime(0, 0, 0);
         $fechaRangoFin = clone $fechaActualSistema;
         if($fechaRangoFin->format("l") !== "Saturday"){
             $fechaRangoFin->modify('next ' . DiaSemana::SABADO);
         }
         $fechaRangoFin->setTime(23, 59, 0);
         foreach ($mapSalidas as $fecha => $salida) {
            if($this->utilService->compararFechas($fechaRangoInit, $fecha) <= 0 && 
                    $this->utilService->compararFechas($fecha, $fechaRangoFin) <= 0){
                return $salida;
            }   
         }
         return null;
     }
     
     public function getMapSalidas($salidas){
         $result = array();
         foreach ($salidas as $salida) {
             $clave = $salida->getFecha()->format('d-m-Y H:i:s');
//             $this->logger->warn("Salida:" . $salida->getId());
             if(!in_array($clave, $result)){
                 $result[$clave] = $salida;
             }else{
                 throw new \RuntimeException("La clave " . $clave ." esta duplicada en el array de salidas.");
             }
         }
         return $result;
     }
     
     public function getEmpresa(Ruta $ruta, \DateTime $fecha) {
         return $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                            ->getEmpresaQueFactura($ruta->getCodigo(), $fecha);
     }
     
    public function setScheduledJob(Job $job = null) {
        $this->logger->warn("Salidas Service - init");
        $this->job = $job;
        $this->procesarSalidaPorItinerarioCiclicoFormaPeriodica();
        $this->logger->warn("Salidas Service - end");
    }

}

?>
