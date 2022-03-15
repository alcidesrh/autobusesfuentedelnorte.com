<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Form\Model\ReservacionModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reservacion\CrearReservacionType;
use Acme\TerminalOmnibusBundle\Entity\Reservacion;
use Acme\TerminalOmnibusBundle\Entity\EstadoReservacion;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reservacion\CancelarReservacionType;

/**
*   @Route(path="/reservaciones")
*/
class ReservacionController extends Controller {

     /**
     * @Route(path="/", name="reservaciones-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function homeReservacionesAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Reservacion:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarReservaciones.json", name="reservaciones-listarPaginado")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
    */
    public function listarReservacionesAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Reservacion');
                $result = $repository->getReservacionesPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'fecha' => $item->getSalida()->getFecha()->format('d-m-Y h:i A'),
                        'externa' => $item->getExterna() === true ? "Si" : "No",
                        'referenciaExterna' => $item->getReferenciaExterna(),
                        'cliente' => $item->getCliente()->__toString(),
                        'numeroAsiento' => $item->getAsientoBus()->getNumero(),
                        'ruta' => $item->getSalida()->getItinerario()->getRuta()->getNombre(),
                        'fechaCreacion' => $item->getFechaCreacion() !== null ? $item->getFechaCreacion()->format('d-m-Y h:i A') : "",
                        'usuarioCreacion' => $item->getUsuarioCreacion() !== null ? $item->getUsuarioCreacion()->getFullName() : "",
                        'estacionCreacion' => $item->getEstacionCreacion() !== null ? $item->getEstacionCreacion()->__toString() : ""
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\RuntimeException $exc) {
//            var_dump($exc);
            $this->get('logger')->error("Ha ocurrido un error en el sistema. " . $exc->getMessage());
        } catch (\Exception $exc) {
//            var_dump($exc);
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
    
    /**
     * @Route(path="/crear.html", name="reservacion-crear-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function crearAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $reservacionModel = new ReservacionModel();       
        $form = $this->createForm(new CrearReservacionType($this->getDoctrine()), $reservacionModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            $erroresAux = new ConstraintViolationList();
            $reservaciones = $this->convertirModelToListReservaciones($reservacionModel, $erroresAux);
            if($erroresAux !== null && count($erroresAux) != 0){
                return UtilService::returnError($this, $erroresAux->getIterator()->current()->getMessage());        
            }
            $numerosAsietos = array();
            foreach ($reservaciones as $reservacion) {
                $erroresItems = $this->get('validator')->validate($reservacion);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                $numerosAsietos[] = $reservacion->getAsientoBus()->getNumero();
            }
            
            //Se valida si existe un boleto o una reservacion activa asociada al numero de asiento de bus
            $idSalida = $reservacionModel->getSalida()->getId();
            $result = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientoOcupadosPorNumero($idSalida, $numerosAsietos);
            foreach ($result as $asientoBus) {
                return UtilService::returnError($this, "El asiento con el número: " . $asientoBus->getNumero() . " acaba de ser ocupado.");
            }
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    foreach ($reservaciones as $reservacion) {
                        $em->persist($reservacion);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this, UtilService::checkError($exc->getMessage()));
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Reservacion:crear.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));        
    }
    
    /**
     * @Route(path="/consultar.html", name="reservacion-consultar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function consultarAction(Request $request, $_route) {
        
        $idReservacion = $request->query->get('id');
        if (is_null($idReservacion)) {
            $idReservacion = $request->request->get('id');
        }
        
        if(is_null($idReservacion)){
            return UtilService::returnError($this, "No se pudo determinar el identificador de la reservación.");
        }
        
        $reservacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->find($idReservacion);
        if ($reservacion === null) {
            return UtilService::returnError($this, "La reservación con id: ".$idReservacion. " no existe.");
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Reservacion:consultar.html.twig', array(
            'reservacion' => $reservacion
        ));
    }
    
    /**
     * @Route(path="/cancelar.html", name="reservacion-cancelar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function cancelarAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $idReservacion = $request->query->get('id');
        if(is_null($idReservacion)){
            $idReservacion = $request->request->get('id');
            if (is_null($idReservacion)) {
                $command = $request->request->get('cancelar_reservacion_command'); //Submit
                if($command !== null){
                    $idReservacion = $command["id"];
                }
            }
        }
        
        if(is_null($idReservacion)){
            return UtilService::returnError($this, "No se pudo determinar el identificador de la reservación.");
        }
        
        $reservacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->find($idReservacion);
        if ($reservacion === null) {
            return UtilService::returnError($this, "La reservación con id: ".$idReservacion. " no existe.");
        }
        
        if($reservacion->getExterna() === true){
            $user = $this->getUser();
            if(!($user->hasRole("ROLE_SUPER_ADMIN") ||
                 $user->hasRole("ROLE_ADMIN") || 
                 $user->hasRole("ROLE_SUPERVISOR_BOLETO") || 
                 $user->hasRole("ROLE_PROPIETARIO") || 
                 $user->hasRole("ROLE_ADMINISTRATIVOS"))){
                return UtilService::returnError($this, "Su usuario no tiene los permisos para cancelar una reservación externa.");
            }else{
                
                $now = new \DateTime();
                $minutos = ceil((strtotime($now->format('d-m-Y H:i:s')) - strtotime($reservacion->getFechaCreacion()->format('d-m-Y H:i:s'))) / 60);
                if($minutos < 12){
                    return UtilService::returnError($this, "La reservaciones externas no se pueden cancelar antes de los 12 minutos despues de creada. El tiempo actual son " . $minutos . " minutos.");
                }
            }
        }
        
        $form = $this->createForm(new CancelarReservacionType($this->getDoctrine()), $reservacion);
         if ($request->isMethod('POST')) { 
            $reservacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::CANCELADA));
            $reservacion->setUsuarioActualizacion($this->getUser());
            $reservacion->setFechaActualizacion(new \DateTime());
            $reservacion->setObservacion(trim($reservacion->getObservacion()));
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($reservacion);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Reservacion:cancelar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    private function convertirModelToListReservaciones(ReservacionModel $reservacionModel, ConstraintViolationList $erroresAux) {
        $listaReservaciones = array();
        $listaClienteReservacionHidden = $reservacionModel->getListaClienteReservacion();
        $listaClienteReservacionJson = json_decode($listaClienteReservacionHidden);
        if($listaClienteReservacionJson !== null){
            foreach ($listaClienteReservacionJson as $json) {
                $reservacion = new Reservacion();
                $reservacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::EMITIDA));
                $asientoBus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->find($json->id);
                $reservacion->setAsientoBus($asientoBus);
                $reservacion->setCliente($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->find($json->idCliente));
                $reservacion->setSalida($reservacionModel->getSalida());
                $reservacion->setUsuarioCreacion($this->getUser());
                $reservacion->setFechaCreacion(new \DateTime());
                $reservacion->setEstacionCreacion($this->getUser()->getEstacion());
                $listaReservaciones[] = $reservacion;
            }
        }
        return $listaReservaciones;
    }
    
}

?>
