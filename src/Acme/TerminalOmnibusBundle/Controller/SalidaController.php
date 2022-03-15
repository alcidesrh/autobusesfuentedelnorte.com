<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Acme\TerminalOmnibusBundle\Entity\ItinerarioCiclico;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Form\Frontend\Salida\AsignarSalidaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Salida\AbordarSalidaType;
use Acme\TerminalOmnibusBundle\Entity\EstadoSalida;
use Acme\TerminalOmnibusBundle\Form\Frontend\Salida\IniciarSalidaType;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\EstadoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\EncomiendaBitacora;
use Acme\TerminalOmnibusBundle\Form\Frontend\Salida\CancelarSalidaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Salida\FinalizarSalidaType;
use Acme\TerminalOmnibusBundle\Entity\Salida;
use Acme\TerminalOmnibusBundle\Entity\Tarjeta;
use Acme\TerminalOmnibusBundle\Entity\EstadoTarjeta;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\TarjetaSalidaType;
use Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial;
use Acme\TerminalOmnibusBundle\Entity\Talonario;
use Acme\TerminalOmnibusBundle\Entity\EstadoReservacion;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;
use Acme\TerminalOmnibusBundle\Entity\SalidaBitacora;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\AdicionarTalonarioType;
use Acme\TerminalOmnibusBundle\Entity\TarjetaBitacora;

/**
*   @Route(path="/salida")
*/
class SalidaController extends Controller {

    /**
     * @Route(path="/", name="salidas-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_AGENCIA_SALIDA, ROLE_ASIGNADOR_BUSES_PILOTOS")
     */
    public function homeSalidaAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Salida:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }

    /**
     * @Route(path="/listarSalidas.json", name="salida-listarPaginado")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_AGENCIA_SALIDA, ROLE_ASIGNADOR_BUSES_PILOTOS")
    */
    public function listarSalidasAction($_route) {
        
        $pageRequest = 1;
        $total = 0;
        $rows = array();
        try {
            $pageRequest = $this->get('request')->request->get('page');
            $rowsRequest = $this->get('request')->request->get('rp');
            if($pageRequest !== null && is_numeric($pageRequest) && $rowsRequest !== null && is_numeric($rowsRequest)){
                $sortRequest = $this->get('request')->request->get('sortname');
                if($sortRequest === null){
                    $sortRequest = "";
                }
                $orderRequest = $this->get('request')->request->get('sortorder');
                if($orderRequest === null){
                    $orderRequest = "";
                }
                $query = $this->get('request')->request->get('query');
                $mapFilters = UtilService::getMapsParametrosQuery($query);
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Salida');
                $result = $repository->getSalidasPaginadas($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $salida)
                {
                    $codigoBus = "No definido";
                    $bus = $salida->getBus();
                    if($bus !== null){
                        $codigoBus = $bus->getCodigo();
                    }
                    $codigoPiloto1 = "No definido";
                    $piloto1 = $salida->getPiloto();
                    if($piloto1 !== null){
                        $codigoPiloto1 = $piloto1->__toString();
                    }
                    $codigoPiloto2 = "No definido";
                    $piloto2 = $salida->getPilotoAux();
                    if($piloto2 !== null){
                        $codigoPiloto2 = $piloto2->__toString();
                    }
                    $itinerario = $salida->getItinerario();
                    $ruta = $itinerario->getRuta();
                    $item = array(
                        'id' => $salida->getId(),
                        'idItinerario' => $itinerario->getId(),
                        'ciclico' => $itinerario instanceof ItinerarioCiclico ? "Si" : "No",
                        'ruta' => $ruta->__toString(),
                        'origen' => $ruta->getEstacionOrigen()->__toString(),
                        'destino' => $ruta->getEstacionDestino()->__toString(),
                        'fecha' => $salida->getFecha()->format('d-m-Y h:i A'),
                        'tipoBus' => $salida->getTipoBus()->__toString(),
                        'claseBus' => $salida->getTipoBus()->getClase()->getNombre(),
                        'empresa' => $salida->getEmpresa()->getAlias(),
                        'tarjeta' => $salida->getTarjeta() !== null ? $salida->getTarjeta()->getAlias() : " - ",
                        'estado' => $salida->getEstado()->getNombre(),
                        'bus' => $codigoBus,
                        'piloto1' => $codigoPiloto1,
                        'piloto2' => $codigoPiloto2,
                    );
                    $rows[] = $item;
                }
                $total = $result['total'];
            }

        } catch (\ErrorException $exc) {
            var_dump($exc->getMesage());
            $this->get('logger')->error("Ha ocurrido un error en el sistema. " . $exc->getMessage());
        } catch (\Exception $exc) {
            var_dump($exc->getMesage());
            $this->get('logger')->error("Ha ocurrido un error en el sistema. " . $exc->getMessage());
        }

        $response = new JsonResponse();
        $response->setData(array(
            'total' => $total,
            'page' => $pageRequest,
            'rows' => $rows
        ));
        return $response;
    }
    
    //No hay que llamar al servicio de salida, pq ya existe y se actulizo por el formulario
    //Lo que se hace es que su itinerario si es especial este sincronizado con el estado de la salida.
    private function sincronizarSalidaVsItineario(Salida $salida)
    {
        $itinerario = $salida->getItinerario();
        if($itinerario instanceof ItinerarioEspecial){
            if($salida->getEstado()->getId() === EstadoSalida::CANCELADA){
                $itinerario->setActivo(false);
            }else{
                $itinerario->setActivo(true);
            }
        }
    }
    
    /**
     * @Route(path="/asignarBusPilotos.html", name="salida-asignarBusPilotos-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_AGENCIA_SALIDA, ROLE_ASIGNADOR_BUSES_PILOTOS")
     */
    public function asignarBusPilotosAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('asignar_salida_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id de la salida");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($id); 
        if ($salida === null) {
            return UtilService::returnError($this, "La salida con id: ".$id." no existe.");
        }
        
        if($salida->getEstado()->getId() !== EstadoSalida::PROGRAMADA && $salida->getEstado()->getId() !== EstadoSalida::ABORDANDO){
            return UtilService::returnError($this, "Solamente se puede asignar un bus a una salida en estado programada o abordando.");
        }
        
        if($this->getUser()->getEstacion() !== null){
            $estacionOrigen = $salida->getItinerario()->getRuta()->getEstacionOrigen();
            if($this->getUser()->getEstacion() !== $estacionOrigen){
                return UtilService::returnError($this, "Solamente puede asignar el bus o los pilotos un usuario de la estacion " . $estacionOrigen->__toString() . " o un administrativo.");
            }
        }
        
        $form = $this->createForm(new AsignarSalidaType(), $salida, array(
            'em' => $this->getDoctrine()->getManager(),
            'user' => $this->getUser()
        ));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $result = $this->getValidPilotosSalida($salida);
                if($result !== null){
                   return UtilService::returnError($this, $result);
                }
            
                $tipoBus = null;
                if($salida->getBus() === null){
                    $tipoBus = $salida->getItinerario()->getTipoBus();
                }else{
                    $tipoBus = $salida->getBus()->getTipo();
                } 
                
                $boletos = array();
                $reservaciones = array();
                if($salida->getTipoBus() !== $tipoBus){
                    $salida->setTipoBus($tipoBus);
                    $mapAsientos = array();
                    $listaAsiento = $tipoBus->getListaAsiento();
                    foreach ($listaAsiento as $asiento) {
                        $mapAsientos[$asiento->getNumero()] = $asiento;
                    }
                    $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getBoletosPorSalida($id);
                    if(count($boletos) !== 0){
                        foreach ($boletos as $boleto){
                            $numero = $boleto->getAsientoBus()->getNumero();
                            if(array_key_exists($numero, $mapAsientos)){
                                $asiento = $mapAsientos[$numero];
                                $boleto->setAsientoBus($asiento);
                                if($boleto->getAutorizacionCortesia() !== null && $boleto->getAutorizacionCortesia()->getRestriccionAsientoBus() !== null){
                                    $boleto->getAutorizacionCortesia()->setRestriccionAsientoBus($asiento);
                                }
                            }
                        }
                    }
                    $reservaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->getReservacionesPorSalida($id);
                    if(count($reservaciones) !== 0){
                        foreach ($reservaciones as $reservacion){
                            $numero = $reservacion->getAsientoBus()->getNumero();
                            if(array_key_exists($numero, $mapAsientos)){
                                $asiento = $mapAsientos[$numero];
                                $reservacion->setAsientoBus($asiento);
                            }
                        }
                    }
                }
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setUsuario($this->getUser());
                $descripcion = "";
                if($salida->getBus() === null){
                    $descripcion .= "Eliminando bus asignado. ";
                }else{
                    $descripcion .= "Asignando bus: " . $salida->getBus()->getCodigo() . ". ";
                }
                if($salida->getPiloto() === null){
                    $descripcion .= "Eliminando primer piloto. ";
                }else{
                    $descripcion .= "Asignando primer piloto: " . $salida->getPiloto()->getCodigoFullName() . ". ";
                }
                if($salida->getPilotoAux() === null){
                    $descripcion .= "";
                }else{
                    $descripcion .= "Asignando segundo piloto: " . $salida->getPilotoAux()->getCodigoFullName() . ". ";
                }
                $salidaBitacora->setDescripcion($descripcion);
                $salida->addBitacoras($salidaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $this->sincronizarSalidaVsItineario($salida);
                    $em->persist($salida);
                    if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                        if($salida->isValidToSync()){
                            $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                            $jobSync->setNivel($salida->getNivelSync());
                            $jobSync->setType($salida->getTypeSync());
                            $jobSync->setUsuarioCreacion($this->getUser());
                            $jobSync->setData($salida->getDataArrayToSync());
                            $this->get('acme_job_sync')->createJobSync($jobSync, false);
                        }
                    }
                    foreach ($boletos as $item){
                        $em->persist($item);
                    }
                    foreach ($reservaciones as $item){
                        $em->persist($item);
                    }
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                    
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Salida:asignar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    private function getValidPilotosSalida($salida){
        if( $salida->getPiloto() !== null && $salida->getPiloto()->getFechaVencimientoLicencia() !== null){
            if(UtilService::compararFechas($salida->getPiloto()->getFechaVencimientoLicencia(), new \DateTime()) <= 0){
                return "m1El piloto " . $salida->getPiloto()->__toString() . " tiene la licencia vencida. Debe actualizarla en el módulo de pilotos.";
            }
        }
        if( $salida->getPilotoAux() !== null && $salida->getPilotoAux()->getFechaVencimientoLicencia() !== null){
            if(UtilService::compararFechas($salida->getPilotoAux()->getFechaVencimientoLicencia(), new \DateTime()) <= 0){
                return "m1El piloto " . $salida->getPilotoAux()->__toString() . " tiene la licencia vencida. Debe actualizarla en el módulo de pilotos.";
            }
        }
        return null;
    }
    
    /**
     * @Route(path="/abordar.html", name="salida-abordar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_AGENCIA_SALIDA")
     */
    public function abordarAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('abordar_salida_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id de la salida");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($id); 
        if ($salida === null) {
            return UtilService::returnError($this, "La salida con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new AbordarSalidaType(), $salida, array(
            'em' => $this->getDoctrine()->getManager(),
        ));
        
        if($salida->getEstado()->getId() !== EstadoSalida::PROGRAMADA){
            return UtilService::returnError($this, "Solamente se puede abordar una salida en estado programada.");
        }
        
        if($this->getUser()->getEstacion() !== null){
            $estacionOrigen = $salida->getItinerario()->getRuta()->getEstacionOrigen();
            if($this->getUser()->getEstacion() !== $estacionOrigen){
                return UtilService::returnError($this, "Solamente puede poner en estado abordadando la salida un usuario de la estacion " . $estacionOrigen->__toString() . " o un administrativo.");
            }
        }
        
        if ($request->isMethod('POST')) { 
            
            $salida->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::ABORDANDO));
            $form->bind($request);
            if ($form->isValid()) {
                
                $result = $this->getValidPilotosSalida($salida);
                if($result !== null){
                    return UtilService::returnError($this, $result);
                }
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setUsuario($this->getUser());
                $salidaBitacora->setDescripcion("Poniendo la salida en estado abordando.");
                $salida->addBitacoras($salidaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $this->sincronizarSalidaVsItineario($salida);
                    $em->persist($salida);
                    if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                        if($salida->isValidToSync()){
                            $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                            $jobSync->setNivel($salida->getNivelSync());
                            $jobSync->setType($salida->getTypeSync());
                            $jobSync->setUsuarioCreacion($this->getUser());
                            $jobSync->setData($salida->getDataArrayToSync());
                            $this->get('acme_job_sync')->createJobSync($jobSync, false);
                        }
                    }
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Salida:abordar.html.twig', array(
            'entity' => $salida,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/asignarTarjeta.html", name="salida-asignarTarjeta-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA_SALIDA")
     */
    public function asignarTarjetaAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $idSalida = $request->query->get('id');
        if ($request->isMethod('POST')) {
            $command = $request->request->get('tarjeta_command'); //Submit
            if($command !== null){
                $idSalida = $command["salida"];
            }
        }
        
        if(is_null($idSalida)){
            return UtilService::returnError($this, "No se pudo obtener el id de la salida");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida); 
        if ($salida === null) {
            return UtilService::returnError($this, "La salida con id: ".$idSalida." no existe.");
        }
        
        if($salida->getEstado()->getId() !== EstadoSalida::PROGRAMADA && $salida->getEstado()->getId() !== EstadoSalida::ABORDANDO){
            return UtilService::returnError($this, "Solamente se puede asignar una tarjeta a una salida en estado programada o abordando.");
        }
        
        if($this->getUser()->getEstacion() !== null){
            $estacionOrigen = $salida->getItinerario()->getRuta()->getEstacionOrigen();
            if($this->getUser()->getEstacion() !== $estacionOrigen){
                return UtilService::returnError($this, "Solamente puede asignar tarjeta a la salida un usuario de la estacion " . $estacionOrigen->__toString() . " o un administrativo.");
            }
        }
        
        $tarjeta = $salida->getTarjeta();
        if($tarjeta === null){
            $tarjeta = new Tarjeta($this->getUser());
            $tarjeta->setSalida($salida);
            $tarjeta->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoTarjeta')->find(EstadoTarjeta::CREADO));
            $salida->setTarjeta($tarjeta);
        }
        
        $form = $this->createForm(new TarjetaSalidaType(), $tarjeta, array(
            'em' => $this->getDoctrine()->getManager(),
            'user' => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $descripcion = "Asignando Tarjeta Nro: " . strval($tarjeta->getAlias()) . ". ";
                
                $command = $request->request->get('tarjeta_command');
                $item1init = isset($command['item1init']) ? $command['item1init'] : "";
                $item1end = isset($command['item1end']) ? $command['item1end'] : "";
                if(trim($item1init) === "" || trim($item1end) === "" || intval($item1init) === false 
                        || intval($item1end) === false){
                    return UtilService::returnError($this, "Debe definir el rango del primer talonario.");
                }else{
                    $item1init = intval($item1init);
                    $item1end = intval($item1end);
                    if($item1init >= $item1end){
                        return UtilService::returnError($this, "El valor 'Del' debe ser menor que valor 'Al' del primer talonario.");
                    }
                    $talonario = null;
                    if($tarjeta->getListaTalonarios()->containsKey(0)){
                        $talonario = $tarjeta->getListaTalonarios()->get(0);
                    }
                    if($talonario === null){
                        $talonario = new Talonario($this->getUser());
                        $talonario->setTarjeta($tarjeta);
                        $tarjeta->getListaTalonarios()->set(0, $talonario);
                    }
                    $talonario->setInicial($item1init);
                    $talonario->setFinal($item1end);
                    $descripcion .= "Talonario Nro:1. Del " . strval($item1init) . " al " . strval($item1end) . ". ";
                }
                
                $item2init = isset($command['item2init']) ? $command['item2init'] : "";
                $item2end = isset($command['item2end']) ? $command['item2end'] : "";
                if(trim($item2init) !== "" && trim($item2end) !== ""){
                    if(intval($item2init) === false || intval($item2end) === false){
                        return UtilService::returnError($this, "Debe definir un rango valido del segundo talonario.");
                    }else{
                        $item2init = intval($item2init);
                        $item2end = intval($item2end);
                        if($item2init >= $item2end){
                            return UtilService::returnError($this, "El valor 'Del' debe ser menor que valor 'Al' del segundo talonario.");
                        }
                        $talonario = null;
                        if($tarjeta->getListaTalonarios()->containsKey(1)){
                            $talonario = $tarjeta->getListaTalonarios()->get(1);
                        }
                        if($talonario === null){
                            $talonario = new Talonario($this->getUser());
                            $talonario->setTarjeta($tarjeta);
                            $tarjeta->getListaTalonarios()->set(1, $talonario);   
                        }
                        $talonario->setInicial($item2init);
                        $talonario->setFinal($item2end);
                        $descripcion .= "Talonario Nro:2. Del " . $item2init . " al " . $item2end. ". ";
                    }
                }else{
                    if($tarjeta->getListaTalonarios()->containsKey(1)){
                        $tarjeta->getListaTalonarios()->remove(1);
                    } 
                }
                
                $item3init = isset($command['item3init']) ? $command['item3init'] : "";
                $item3end = isset($command['item3end']) ? $command['item3end'] : "";
                if(trim($item3init) !== "" && trim($item3end) !== ""){
                    if(intval($item3init) === false || intval($item3end) === false){
                        return UtilService::returnError($this, "Debe definir un rango valido del tercer talonario.");
                    }else{
                        $item3init = intval($item3init);
                        $item3end = intval($item3end);
                        if($item3init >= $item3end){
                            return UtilService::returnError($this, "El valor 'Del' debe ser menor que valor 'Al' del tercer talonario.");
                        }
                        $talonario = null;
                        if($tarjeta->getListaTalonarios()->containsKey(2)){
                            $talonario = $tarjeta->getListaTalonarios()->get(2);
                        }
                        if($talonario === null){
                            $talonario = new Talonario($this->getUser());
                            $talonario->setTarjeta($tarjeta);
                            $tarjeta->getListaTalonarios()->set(2, $talonario);
                        }
                        $talonario->setInicial($item3init);
                        $talonario->setFinal($item3end);
                        $descripcion .= "Talonario Nro:3. Del " . $item3init . " al " . $item3end. ". ";
                    }
                }else{
                    if($tarjeta->getListaTalonarios()->containsKey(2)){
                        $tarjeta->getListaTalonarios()->remove(2);
                    } 
                }
                
                if(count($tarjeta->getListaTalonarios()) === 0){
                    return UtilService::returnError($this, "Debe definir al menos un talonario.");
                }
                
                $erroresItems = $this->get('validator')->validate($tarjeta);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                foreach ($tarjeta->getListaTalonarios() as $talonario) {
                    $erroresItems = $this->get('validator')->validate($talonario);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                }
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setUsuario($this->getUser());
                $salidaBitacora->setDescripcion($descripcion);
                $salida->addBitacoras($salidaBitacora);
 
                $descripcionTarjeta = "";
                if($tarjeta->getId() == null || $tarjeta->getId() == ""){
                    $descripcionTarjeta = "Tarjeta Creada. ";
                }else{
                    $descripcionTarjeta = "Tarjeta Actualizada. ";
                }
                $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                $tarjetaBitacora->setDescripcion($descripcionTarjeta . $descripcion);
                $tarjeta->addBitacoras($tarjetaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($tarjeta);
                    $em->persist($salida);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    var_dump($exc->getMessage());
                    return UtilService::returnError($this, $exc->getMessage());
                } catch (\ErrorException $exc) {
                    $em->getConnection()->rollback();
                    var_dump($exc->getMessage());
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    var_dump($exc->getMessage());
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Tarjeta:asignarTarjeta.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/iniciar.html", name="salida-iniciar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_AGENCIA_SALIDA, ROLE_ASIGNADOR_BUSES_PILOTOS")
     */
    public function iniciarAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('iniciar_salida_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if (is_null($id)) {
            return UtilService::returnError($this, "No se pudo obtener el identificador de la salida.");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($id); 
        if ($salida == null){
            return UtilService::returnError($this, "La salida con id: ".$id." no existe.");
        }
        
        if($salida->getEstado()->getId() !== EstadoSalida::ABORDANDO){
            return UtilService::returnError($this, "Solamente se puede iniciar una salida que este en estado abordando.");
        }
        
        if($this->getUser()->getEstacion() !== null){
            $estacionOrigen = $salida->getItinerario()->getRuta()->getEstacionOrigen();
            if($this->getUser()->getEstacion() !== $estacionOrigen){
                return UtilService::returnError($this, "Solamente puede iniciar la salida un usuario de la estacion " . $estacionOrigen->__toString() . " o un administrativo.");
            }
        }
        
        if($salida->getEmpresa()->getObligatorioControlTarjetas() === true && $salida->getTarjeta() === null){
            return UtilService::returnError($this, "La empresa " . $salida->getEmpresa()->getAlias() . " requiere control de tarjetas.");
        }
        
        $form = $this->createForm(new IniciarSalidaType($this->getDoctrine()), $salida);

        if ($request->isMethod('POST')) { 
            
            //Las reservaciones que aun esten activas se cancelan, ya no cumplen ningun objetivo
            $listarReservacionesEmitidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->listarReservacionesEmitidasBySalida($id);
            foreach ($listarReservacionesEmitidas as $item) {
                $item->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::CANCELADA));
                $observacion = $item->getObservacion();
                $observacion .= "Se cancela la reservación porque ya la salida inicio.";
                $item->setObservacion($observacion);
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
            
            $salida->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::INICIADA));
            $cantidadBoletosChequeadosFactura = 0;
            $cantidadBoletosChequeadosCortesias = 0;
            $boletosChequeados = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarBoletosChequeadosBySalida($id);
            foreach ($boletosChequeados as $item) {
                $item->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::TRANSITO));
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($item->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $boletoBitacora->setDescripcion("Inició la salida.");
                $item->addBitacoras($boletoBitacora);
            
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                if($item->getTipoDocumento()->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
                    $cantidadBoletosChequeadosCortesias++;
                }else{
                    $cantidadBoletosChequeadosFactura++;
                }
            }
            $boletosNOChequeados = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarBoletosEmitidosBySalida($id);
            foreach ($boletosNOChequeados as $item) {
                $item->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CANCELADO));
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($item->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $boletoBitacora->setDescripcion("Se canceló porque inició la salida y el boleto no se chequeó.");
                $item->addBitacoras($boletoBitacora);
                
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
            
            /*
                    NOTA. LOS BOLETOS EMITIDOS PERO QUE ABORDAN MAS ADELANTE, MANTIENEN SU ESTADO DE EMITIDO.
            */
            
            $encomiendasEmbarcadas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasEmbarcadasBySalida($id);
            foreach ($encomiendasEmbarcadas as $item) {
                $bitacora = new EncomiendaBitacora();
                $bitacora->setEncomienda($item);
                $bitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::TRANSITO));
                $bitacora->setFecha(new \DateTime());
                $bitacora->setUsuario($this->getUser());
                if($this->getUser()->getEstacion() !== null){
                    $bitacora->setEstacion($this->getUser()->getEstacion());
                }
                else{
                    $bitacora->setEstacion($item->getEstacionOrigen());
                }
                $bitacora->setSalida($salida);
                $item->addEventos($bitacora);
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                //SE VALIDA DE ESTA FORMA PQ ENCONTRE COMO VALIDAR EN CASCADA CON EL SERVICIO.
                $erroresItems = $this->get('validator')->validate($bitacora);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
            
            $totalBoletosChequeados = intval($form->get('totalBoletosChequeados')->getData());
            $totalBoletosNOChequeados = intval($form->get('totalBoletosNOChequeados')->getData());
            $totalBoletosNoChequeadosPendientes = intval($form->get('totalBoletosPendientes')->getData());
            $totalBoletosOK = $totalBoletosChequeados + $totalBoletosNoChequeadosPendientes;
            $totalBoletos = $totalBoletosOK + $totalBoletosNOChequeados;
//            $totalEncomiendasEmbarcadas = $form->get('totalEncomiendasEmbarcadas')->getData();
//            if($totalBoletosOK === 0 && intval($totalEncomiendasEmbarcadas) === 0){
//                $mensajeServidor = new ConstraintViolation('Para iniciar la salida debe tener como mínimo un boleto o una encomienda.', '', array(), '', '', null);
//                return UtilService::returnError($this, $mensajeServidor);
//            }
            
            //Esta validacion puede detener la salida del bus hasta que no se hayan reasignado los asientos que sobren.
            if($totalBoletos > $salida->getTipoBus()->getTotalAsientos()){
                return UtilService::returnError($this, "La salida tiene " . $totalBoletos . " boletos asociados, pero el bus solo tiene " . $salida->getTipoBus()->getTotalAsientos() . " asientos.");
            }
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $result = $this->getValidPilotosSalida($salida);
                if($result !== null){
                    return UtilService::returnError($this, $result); 
                }
                
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setUsuario($this->getUser());
                $salidaBitacora->setDescripcion("Iniciando salida.");
                $salida->addBitacoras($salidaBitacora);
                
                /////////////////////////////////////////////////////////////////////////////////////
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $this->sincronizarSalidaVsItineario($salida);
                    $em->persist($salida);
                    if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                        if($salida->isValidToSync()){
                            $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                            $jobSync->setNivel($salida->getNivelSync());
                            $jobSync->setType($salida->getTypeSync());
                            $jobSync->setUsuarioCreacion($this->getUser());
                            $jobSync->setData($salida->getDataArrayToSync());
                            $this->get('acme_job_sync')->createJobSync($jobSync, false);
                        }
                    }
                    foreach ($boletosChequeados as $item) {
                        $em->persist($item);
                    }
                    foreach ($boletosNOChequeados as $item) {
                        $em->persist($item);
                    }
                    foreach ($encomiendasEmbarcadas as $item) {
                        $em->persist($item);
                    }
                    foreach ($listarReservacionesEmitidas as $item) {
                        $em->persist($item);
                    }
                    $em->flush();
                    $this->sendEmailPropietariosSalidaIniciada($salida);
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Salida:iniciar.html.twig', array(
            'entity' => $salida,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/adicionarTalonario.html", name="salida-adicionarTalonario-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA_SALIDA")
     */
    public function adicionarTalonarioAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $idSalida = $request->query->get('id');
        if ($request->isMethod('POST')) {
            $command = $request->request->get('talonario_command'); //Submit
            if($command !== null){
                $idSalida = $command["salida"];
            }
        }
        
        if(is_null($idSalida)){
            return UtilService::returnError($this, "No se pudo obtener el id de la salida");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida); 
        if ($salida === null) {
            return UtilService::returnError($this, "La salida con id: ".$idSalida." no existe.");
        }
        
        if($salida->getEstado()->getId() !== EstadoSalida::INICIADA){
            return UtilService::returnError($this, "Solamente se puede adicionar un talonario a una salida en estado iniciada.");
        }
        
        if($salida->getTarjeta() === null){
            return UtilService::returnError($this, "La salida no tiene asignada tarjeta.");
        }
        
        if($this->getUser()->getEstacion() !== null){
            $estacionOrigen = $salida->getItinerario()->getRuta()->getEstacionOrigen();
            if($this->getUser()->getEstacion() !== $estacionOrigen && !$salida->getItinerario()->getRuta()->existeEnEstacionesIntermedia($this->getUser()->getEstacion())){
                return UtilService::returnError($this, "Solamente puede adicionar un talonario a la salida un usuario autorizado.");
            }
            
            if(!($this->getUser()->getEstacion()->getControlTarjetasEnRuta() === true)){
                return UtilService::returnError($this, "La estacion " . $this->getUser()->getEstacion() . " no esta habilitada para el control de tarjetas en ruta.");
            }
        }

        $talonario = new Talonario($this->getUser());
        $form = $this->createForm(new AdicionarTalonarioType(), $talonario, array(
            'em' => $this->getDoctrine()->getManager(),
            'salida' => $salida->getId(),
            'user' => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $tarjeta = $salida->getTarjeta();
                if($talonario->getInicial() >= $talonario->getFinal()){
                    return UtilService::returnError($this, "El valor 'Del' debe ser menor que valor 'Al'.");
                }
                
                $tarjeta->addListaTalonarios($talonario);
                
                $erroresItems = $this->get('validator')->validate($talonario);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setUsuario($this->getUser());
                $descripcion = "Talonario Nro: ".  strval(count($tarjeta->getListaTalonarios())).". Del " . strval($talonario->getInicial()) . " al " . strval($talonario->getFinal()) . ". ";
                $salidaBitacora->setDescripcion($descripcion);
                $salida->addBitacoras($salidaBitacora);
                
                $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                $tarjetaBitacora->setDescripcion("Tarjeta Actualizada. Adicionando " . $descripcion);
                $tarjeta->addBitacoras($tarjetaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($tarjeta);
                    $em->persist($salida);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    var_dump($exc->getMessage());
                    return UtilService::returnError($this, $exc->getMessage());
                } catch (\ErrorException $exc) {
                    $em->getConnection()->rollback();
                    var_dump($exc->getMessage());
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    var_dump($exc->getMessage());
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Tarjeta:adicionarTalonario.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/cancelar.html", name="salida-cancelar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_AGENCIA_SALIDA, ROLE_ASIGNADOR_BUSES_PILOTOS")
     */
    public function cancelarAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('cancelar_salida_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id de la salida");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($id); 
        if ($salida === null) {
            return UtilService::returnError($this, "La salida con id: ".$id." no existe.");
        }
        
        if($this->getUser()->getEstacion() !== null){
            $estacionOrigen = $salida->getItinerario()->getRuta()->getEstacionOrigen();
            if($this->getUser()->getEstacion() !== $estacionOrigen){
                return UtilService::returnError($this, "Solamente puede cancelar la salida un usuario de la estacion " . $estacionOrigen->__toString() . " o un administrativo.");
            }
        }
        
        $form = $this->createForm(new CancelarSalidaType($this->getDoctrine()), $salida);  

        if ($request->isMethod('POST')) {
            
            $salida->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::CANCELADA));
            
            $encomiendasEmbarcadas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasEmbarcadasBySalida($id);
            foreach ($encomiendasEmbarcadas as $item) {
                $bitacora = new EncomiendaBitacora();
                $bitacora->setEncomienda($item);
                $bitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::DESEMBARCADA));
                $bitacora->setFecha(new \DateTime());
                $bitacora->setUsuario($this->getUser());
                if($this->getUser()->getEstacion() !== null){
                    $bitacora->setEstacion($this->getUser()->getEstacion());
                }
                else{
                    $bitacora->setEstacion($item->getEstacionOrigen());
                }
                $item->addEventos($bitacora);
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                //SE VALIDA DE ESTA FORMA PQ ENCONTRE COMO VALIDAR EN CASCADA CON EL SERVICIO.
                foreach ($item->getEventos() as $evento) {
                    $erroresItems = $this->get('validator')->validate($evento);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                }
            }
            
            $listarReservacionesEmitidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->listarReservacionesEmitidasBySalida($id);
            foreach ($listarReservacionesEmitidas as $item) {
                $item->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::CANCELADA));
                $observacion = $item->getObservacion();
                $observacion .= "Se cancela la reservación porque la salida se cancelo.";
                $item->setObservacion($observacion);
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setUsuario($this->getUser());
                $salidaBitacora->setDescripcion("Cancelando salida.");
                $salida->addBitacoras($salidaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $this->sincronizarSalidaVsItineario($salida);
                    $em->persist($salida);
                    if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                        if($salida->isValidToSync()){
                            $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                            $jobSync->setNivel($salida->getNivelSync());
                            $jobSync->setType($salida->getTypeSync());
                            $jobSync->setUsuarioCreacion($this->getUser());
                            $jobSync->setData($salida->getDataArrayToSync());
                            $this->get('acme_job_sync')->createJobSync($jobSync, false);
                        }
                    }
                    foreach ($encomiendasEmbarcadas as $item) {
                        $em->persist($item);
                    }
                    foreach ($listarReservacionesEmitidas as $item) {
                        $em->persist($item);
                    }
                    
                    $em->flush();
                    
                    $this->sendEmailPropietariosSalidaCancelada($salida);
                    
                    $em->getConnection()->commit();
                    
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }  catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Salida:cancelar.html.twig', array(
            'entity' => $salida,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/finalizar.html", name="salida-finalizar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA_SALIDA, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function finalizarAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('finalizar_salida_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id de la salida");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($id); 
        if ($salida === null) {
            return UtilService::returnError($this, "La salida con id: ".$id." no existe.");
        }
        
        if($salida->getEstado()->getId() !== EstadoSalida::INICIADA){
            return UtilService::returnError($this, "Solamente se puede finalizar una salida que este en estado iniciada.");
        }
        
        if($this->getUser()->getEstacion() !== null){
            $estacionDestino = $salida->getItinerario()->getRuta()->getEstacionDestino();
            if($this->getUser()->getEstacion() !== $estacionDestino){
                return UtilService::returnError($this, "Solamente puede finalizar la salida un usuario de la estacion " . $estacionDestino->__toString() . " o un administrativo.");
            }
        }
        
        $form = $this->createForm(new FinalizarSalidaType($this->getDoctrine()), $salida);
        
        if ($request->isMethod('POST')) {
            $salida->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->find(EstadoSalida::FINALIZADA));
            $form->bind($request);
            if ($form->isValid()) {
                
                $salidaBitacora = new SalidaBitacora();
                $salidaBitacora->setEstado($salida->getEstado());
                $salidaBitacora->setFecha(new \DateTime());
                $salidaBitacora->setUsuario($this->getUser());
                $salidaBitacora->setDescripcion("Finalizando salida.");
                $salida->addBitacoras($salidaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $this->sincronizarSalidaVsItineario($salida);
                    $em->persist($salida);
                    if($salida instanceof \Acme\BackendBundle\Entity\IJobSync){
                        if($salida->isValidToSync()){
                            $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                            $jobSync->setNivel($salida->getNivelSync());
                            $jobSync->setType($salida->getTypeSync());
                            $jobSync->setUsuarioCreacion($this->getUser());
                            $jobSync->setData($salida->getDataArrayToSync());
                            $this->get('acme_job_sync')->createJobSync($jobSync, false);
                        }
                    }
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                    
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Salida:finalizar.html.twig', array(
            'entity' => $salida,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/consultar.html", name="salida-consultar-case1")
     * @Route(path="/consultar/{id}", name="salida-consultar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA_SALIDA, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function consultarAction(Request $request, $_route, $id = null) {
        
        if (is_null($id)) {
           $id = $request->query->get('id');
            if (is_null($id)) {
                $id = $request->request->get('id');
            } 
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id de la salida");
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($id); 
        if ($salida === null) {
            return UtilService::returnError($this, "La salida con id: ".$id." no existe.");
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Salida:consultar.html.twig', array(
            'salida' => $salida
        ));
    }
    
    /*
        SALIDA: Salida que inicia.
        boletosChequeadosFactura: Cantidad de boletos de factura con las que salio el bus.
        boletosChequeadosCortesias: Cantidad de boletos de cortesia con las que salio el bus.
        TotalEncomiendasEmbarcadas: Total de encomiendas que llevo la salida cuando inicio.
     
     */
    public function sendEmailPropietariosSalidaIniciada(Salida $salida)
    {
        
        $resumen = array();
        $valuesBoletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarDetalleBoletosBySalida($salida->getId());
        if(count($valuesBoletos) !== 0){
            foreach ($valuesBoletos as $item) {
                $resumen[$item['nombreEstacionCreacion']] = $item;
            }
        }
        
        $valuesEncomiendas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarDetalleEncomiendaBySalida($salida->getId());
        if(count($valuesEncomiendas) !== 0){
            foreach ($valuesEncomiendas as $item) {
                if(isset($resumen[$item['nombreEstacionCreacion']])){
                    $temp = array_merge($resumen[$item['nombreEstacionCreacion']], $item);
                    $resumen[$item['nombreEstacionCreacion']] = $temp;
                }else{
                    $resumen[$item['nombreEstacionCreacion']] = $item;
                }
            }
        }
        
        $itinerario = "Cíclico";
        if($salida->getItinerario() instanceof ItinerarioEspecial){
          $itinerario =  "Especial";
        }
        
         $correos = $salida->getEmpresa()->getCorreos();
//         $correos = array("javiermarti84@gmail.com");
         if($correos !== null && count($correos) !== 0){
             $now = new \DateTime();
             $now = $now->format('Y-m-d H:i:s');
             $subject = "NIS_" . $now . ". Notificación de inicio de salida en ruta " . $salida->getItinerario()->getRuta()->getCodigoName() . "."; 
             UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_salida_inicio.html.twig', array(
                'salida' => $salida,
                'resumen' => $resumen,
                'itinerario' => $itinerario
             )));
         }
    }
    
    public function sendEmailPropietariosSalidaCancelada(Salida $salida)
    {
         $correos = $salida->getEmpresa()->getCorreos();
         $correos = array("sistemasfdn@gmail.com");
         if($correos !== null && count($correos) !== 0){
             $now = new \DateTime();
             $now = $now->format('Y-m-d H:i:s');
             $subject = "NCS_" . $now . ". Notificación de cancelación de salida en ruta " . $salida->getItinerario()->getRuta()->getCodigoName() . "."; 
             UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_salida_cancelacion.html.twig', array(
                'salida' => $salida,
             )));
         }
    }
    
    /**
     * @Route(path="/consultar2.html", name="consultar-esquema-salida")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_INSPECTOR_BOLETO, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function consultarEsquemaAction(Request $request, $_route) {
        return $this->render('AcmeTerminalOmnibusBundle:Salida:consultarEsquema.html.twig');
    }
    
    /**
     * @Route(path="/consultar3.html", name="consultar-detalle-salida")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_INSPECTOR_BOLETO, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function consultarDetallesdeSalidasAction(Request $request, $_route) {
        return $this->render('AcmeTerminalOmnibusBundle:Salida:consultarDetalleSalida.html.twig');
    }
}

?>