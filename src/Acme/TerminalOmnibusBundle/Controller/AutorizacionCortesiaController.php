<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\BackendBundle\Services\UtilService;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\TerminalOmnibusBundle\Entity\AutorizacionCortesia;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionCortesia\CrearBoletoAutorizacionCortesiaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionCortesia\CancelarAutorizacionCortesiaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionCortesia\CrearAutorizacionCortesiaMultipleType;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\Boleto;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Acme\TerminalOmnibusBundle\Form\Model\AutorizacionCortesiaMultiplesModel;
use Acme\TerminalOmnibusBundle\Form\Model\EmitirBoletoCortesiaModel;
use Acme\TerminalOmnibusBundle\Entity\EstadoSalida;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;

/**
*   @Route(path="/autorizacioncortesia")
*/
class AutorizacionCortesiaController extends Controller {

    /**
     * @Route(path="/", name="autorizacionCortesia-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_PROPIETARIO, ROLE_EMISOR_CORTESIA")
     */
    public function homeAutorizacionCortesiaAction($_route) {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:AutorizacionCortesia:listar.html.twig', array(
            "route" => $_route
        ));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/listarAutorizacionCortesia.json", name="autorizacionCortesia-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_PROPIETARIO, ROLE_EMISOR_CORTESIA")
    */
    public function listarAutorizacionCortesiaAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionCortesia');
                $result = $repository->getAutorizacionesCortesiasPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'fecha' => $item->getFechaCreacion()->format('d-m-Y H:i:s'),
                        'servicio' => $item->getServicioEstacion()->getNombre(),
                        'codigo' => $item->getCodigo(),
                        'motivo' => $item->getMotivo(),
                        'cliente' =>  $item->getRestriccionCliente() === null ? "" : $item->getRestriccionCliente()->getInfo2()
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (Exception $exc) {
            var_dump($exc->getMessage());
            $rows[] = array("id" => "Ha ocurrido un error.");
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
     * @Route(path="/creaBoletosAutorizacionCortesia.html", name="autorizacionCortesia-crear-boleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_PROPIETARIO, ROLE_EMISOR_CORTESIA")
     */
    public function crearBoletosAutorizacionCortesiaAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        
        $emitirBoletoCortesiaModel = new EmitirBoletoCortesiaModel();
        $form = $this->createForm(new CrearBoletoAutorizacionCortesiaType($this->getDoctrine()), $emitirBoletoCortesiaModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            
            $salida = $emitirBoletoCortesiaModel->getSalida();
            $estacionSubeEn = $emitirBoletoCortesiaModel->getEstacionSubeEn();
            if($salida->getEstado()->getId() === EstadoSalida::INICIADA){
                if(intval($estacionSubeEn->getId()) === intval($salida->getItinerario()->getRuta()->getEstacionOrigen()->getId())){ 
                    return UtilService::returnError($this, 'En salidas iniciadas no se puede emitir boletos donde el cliente suba en el origen de la ruta.');
                }    
            }
            
            $listaReservaciones = array();
            $idReservaciones = array();
            $listaBoletos = array();
            $numerosAsietos = array();
            $listaBoletoHidden = $emitirBoletoCortesiaModel->getListaBoleto();
            $listaBoletoJson = json_decode($listaBoletoHidden);
            if($listaBoletoJson !== null){
                foreach ($listaBoletoJson as $json) {
                    $boleto = new Boleto();
                    $boleto->setClienteDocumento($emitirBoletoCortesiaModel->getCliente());
                    $boleto->setClienteBoleto($emitirBoletoCortesiaModel->getCliente());
                    $boleto->setEstacionOrigen($estacionSubeEn);
                    $boleto->setEstacionDestino($emitirBoletoCortesiaModel->getEstacionBajaEn());
                    $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::EMITIDO));
                    $boleto->setSalida($salida);
                    
                    $boletoBitacora = new BoletoBitacora();
                    $boletoBitacora->setEstado($boleto->getEstado());
                    $boletoBitacora->setFecha(new \DateTime());
                    $boletoBitacora->setUsuario($this->getUser());
                    $boletoBitacora->setDescripcion("Emisión de boleto.");
                    $boleto->addBitacoras($boletoBitacora);
                    
                    $asientoBus = null;
                    if(is_numeric($json->id)){
                        $asientoBus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->find($json->id);
                        $boleto->setCamino(false);
                    }else{
                        return UtilService::returnError($this, "Debe seleccionar un asiento");
                    }
                    $boleto->setAsientoBus($asientoBus);
                    $boleto->setUsuarioCreacion($this->getUser());
                    $boleto->setFechaCreacion(new \DateTime());
                    $boleto->setTipoDocumento($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumentoBoleto')->find(TipoDocumentoBoleto::AUTORIZACION_CORTESIA));
                    $estacionCreacion = $this->getUser()->getEstacion();
                    if($estacionCreacion === null){
                        $estacionCreacion = $salida->getItinerario()->getRuta()->getEstacionOrigen();
                    }
                    $boleto->setEstacionCreacion($estacionCreacion);
                    
                    $idReservacion = $json->idReservacion;
                    if($idReservacion !== null && trim($idReservacion) !== "" && trim($idReservacion) !== "0") {
                         $reservacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->find($idReservacion);
                         if($reservacion === null){
                            return UtilService::returnError($this, "No se encontro la reservación con identificador: " .$idReservacion. ".");
                         }else{
                            $reservacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::VENDIDA));
                            $reservacion->setUsuarioActualizacion($this->getUser());
                            $reservacion->setFechaActualizacion(new \DateTime());
                            $listaReservaciones[] = $reservacion;
                         }
                    }
                    
                    $autorizacionCortesia = new AutorizacionCortesia();
                    $autorizacionCortesia->setServicioEstacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ServicioEstacion')->find(ServicioEstacion::BOLETO));
                    $autorizacionCortesia->setMotivo($emitirBoletoCortesiaModel->getMotivo());
                    $autorizacionCortesia->setActivo(true);
                    $autorizacionCortesia->setUsuarioCreacion($this->getUser());
                    $autorizacionCortesia->setFechaCreacion(new \DateTime());
                    $ping = $this->get('acme_backend_util')->generatePin();
                    $autorizacionCortesia->setCodigo($ping);
                    $autorizacionCortesia->setUsuarioUtilizacion($this->getUser());
                    $autorizacionCortesia->setFechaUtilizacion(new \DateTime());
                    $erroresItems = $this->get('validator')->validate($autorizacionCortesia);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $boleto->setAutorizacionCortesia($autorizacionCortesia);
                    $listaBoletos[] = $boleto;
                }
            }
            
            foreach ($listaBoletos as $item) {
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                $numerosAsietos[] = $item->getAsientoBus()->getNumero();
            }
            
            foreach ($listaReservaciones as $item) {
                $erroresItems = $this->get('validator')->validate($item);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                $idReservaciones[] = $item->getId();
            }
            
            $idSalida = $emitirBoletoCortesiaModel->getSalida()->getId();
            $result = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientoOcupadosPorNumero($idSalida, $numerosAsietos, $idReservaciones);
            foreach ($result as $asientoBus) {
                return UtilService::returnError($this, "El asiento con el número: " . $asientoBus->getNumero() . " acaba de ser ocupado.");
            }
            
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    foreach ($listaBoletos as $item) {
                        $em->persist($item->getAutorizacionCortesia());
                        $em->persist($item);
                    }
                    foreach ($listaReservaciones as $item) {
                        $em->persist($item);
                    }
                    $em->flush();
                    $ids = array();
                    foreach ($listaBoletos as $boleto) {
                        $ids[] = $boleto->getId();
                    }
                    $info = "Se crearon los boletos con identificadores: " . implode(",", $ids) . ".";
                    
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this, array(
                        'data' => implode(",", $ids),
                        'info' => $info
                    ));
                    
                } catch (\RuntimeException $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionCortesia:crearBoletoCortesia.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    
    /**
     * @Route(path="/crearPinesAutorizacionCortesia.html", name="autorizacionCortesia-crear-pines")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_PROPIETARIO, ROLE_EMISOR_CORTESIA")
     */
    public function crearPinesAutorizacionCortesiaAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $autorizacionCortesiaMultiplesModel = new AutorizacionCortesiaMultiplesModel();
        $form = $this->createForm(new CrearAutorizacionCortesiaMultipleType($this->getDoctrine()), $autorizacionCortesiaMultiplesModel, array(
            "user" => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $cortesias = array();
                for ($index = 0; $index < $autorizacionCortesiaMultiplesModel->getCantidad(); $index++) {
                    $autorizacionCortesia = new AutorizacionCortesia();
                    $autorizacionCortesia->setServicioEstacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ServicioEstacion')->find(ServicioEstacion::BOLETO));
                    $autorizacionCortesia->setMotivo($autorizacionCortesiaMultiplesModel->getMotivo());
                    $autorizacionCortesia->setActivo(true);
                    $autorizacionCortesia->setUsuarioCreacion($this->getUser());
                    $autorizacionCortesia->setFechaCreacion(new \DateTime());
                    $ping = $this->get('acme_backend_util')->generatePin();
                    $autorizacionCortesia->setCodigo($ping);
                    $erroresItems = $this->get('validator')->validate($autorizacionCortesia);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $cortesias[] = $autorizacionCortesia;
                }

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    foreach ($cortesias as $item) {
                        $em->persist($item);
                    }
                    $em->flush();
                    if($autorizacionCortesiaMultiplesModel->getUsuarioNotificacion() !== null){
                        $this->notificarPinesAutorizacionCortesia($autorizacionCortesiaMultiplesModel->getUsuarioNotificacion(), $autorizacionCortesiaMultiplesModel->getMotivo(), $cortesias);
                    }
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionCortesia:crearPinesCortesias.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    public function notificarPinesAutorizacionCortesia($usuario, $motivo, $cortesias)
    {
        $correos = array();
        if($usuario->getEmail() !== null && trim($usuario->getEmail()) !== ""){
            $correos[] = trim($usuario->getEmail());
        }
        $user = $this->getUser();
        if($user->getEmail() !== null && trim($user->getEmail()) !== ""){
            $correos[] = trim($user->getEmail());
        }
        $correos = array_unique($correos);
        if(count($correos) !== 0){
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $subject = "NOT_PIN_CORT_" . $now . ". Pines de cortesias."; 
            UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_pines_cortesias.html.twig', array(
                'motivo' => $motivo,
                'cortesias' => $cortesias
            )));
        }
    }
    
    /**
     * @Route(path="/cancelar.html", name="autorizacionCortesia-cancelar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_PROPIETARIO, ROLE_EMISOR_CORTESIA")
     */
    public function cancelarAutorizacionCortesiaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cancelar_autorizacion_cortesia_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }

        $autorizacionCortesia = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionCortesia')->find($id); 
        if (!$autorizacionCortesia) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1La autorización cortesía con id: ".$id." no existe."
            ));
        }
        
        $form = $this->createForm(new CancelarAutorizacionCortesiaType($this->getDoctrine()), $autorizacionCortesia);
        $mensajeServidor = "";
        
        if ($request->isMethod('POST') && $mensajeServidor === "") { 
            $erroresAux = new ConstraintViolationList();
            $autorizacionCortesia->setActivo(false);
            $boleto = $autorizacionCortesia->getBoleto();
            if($boleto != null){
                $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CANCELADO));
                $boleto->setFechaActualizacion(new \DateTime());
                $boleto->setUsuarioActualizacion($this->getUser());
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($boleto->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $boletoBitacora->setDescripcion("Cancelación de la cortesia.");
                $boleto->addBitacoras($boletoBitacora);
            }
            
            $form->bind($request);
           
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($autorizacionCortesia);
                    if($boleto !== null){
                        $em->persist($boleto);
                    }
                    $em->flush();
                    $em->getConnection()->commit();
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0"
                    ));
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               if($error !== null && $error !== ""){
                   $mensajeServidor = "m1" . $error;
                   return UtilService::returnError($this, $mensajeServidor);
               }else{
                   foreach ($erroresAux as $item) {
                      $mensajeServidor = $item->getMessage();
                      if(!UtilService::startsWith($mensajeServidor, "m1")){
                          $mensajeServidor = "m1" . $mensajeServidor;
                      }
                      return UtilService::returnError($this, $mensajeServidor);
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionCortesia:cancelar.html.twig', array(
            'autorizacionCortesia' => $autorizacionCortesia,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
}

?>
