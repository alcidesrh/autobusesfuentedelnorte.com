<?php
namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionOperaciones\AutorizarAutorizacionOperacionesType;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionOperaciones\RechazarAutorizacionOperacionesType;
use Acme\TerminalOmnibusBundle\Entity\EstadoAutorizacionOperacion;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;

/**
*   @Route(path="/autorizacion-operacion")
*/
class AutorizacionOperacionController extends Controller {

    /**
     * @Route(path="/", name="autorizacion-operaciones-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA, ROLE_AUTORIZADOR")
     */
    public function homeAutorizacionAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:AutorizacionOperacion:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarAutorizaciones.json", name="autorizacion-operaciones-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA, ROLE_AUTORIZADOR")
    */
    public function listarAutorizacionesAction($_route) {
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
                $result = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')
                        ->getAutorizacionOperacionesPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'fecha' => $item->getFechaCreacion()->format('d-m-Y h:i A'),
                        'estacion' => $item->getEstacionCreacion()->getNombre(),
                        'usuario' => $item->getUsuarioCreacion()->getFullName(),
                        'idBoleto' => $item->getBoleto()->getId(),
                        'empresa' => $item->getBoleto()->getSalida()->getEmpresa()->getAlias(),
                        'tipo' => $item->getTipo()->getNombre(),
                        'estado' => $item->getEstado()->getNombre(),
                        'motivo' => substr($item->getMotivo(), 0, 80),
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\Exception $exc) {
            var_dump($exc->getTraceAsString());
//            echo $exc->getTraceAsString();
//            $rows[] = array("id" => "Ha ocurrido un error.");
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
     * @Route(path="/autorizar.html", name="autorizacion-operaciones-autorizar")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_AUTORIZADOR")
     */
    public function autorizarAutorizacionOperacionesAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('autorizar_autorizacion_operacion_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id de la autorizacion de operacion.");
                }
            }
        }        
        
        $autorizacionOperacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')->find($id);
        if ($autorizacionOperacion === null) {
            return UtilService::returnError($this, "m1La autorizacion de operacion con id: " .$id." no existe.");
        }
        
        if($autorizacionOperacion->getEstado()->getId() !== EstadoAutorizacionOperacion::REGISTRADO){
            return UtilService::returnError($this, "m1Solamente se puede autorizar una solicitud si esta en estado registrado.");
        }

        $form = $this->createForm(new AutorizarAutorizacionOperacionesType($this->getDoctrine()), $autorizacionOperacion, array(
            "user" => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $autorizacionOperacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoAutorizacionOperacion')->find(EstadoAutorizacionOperacion::AUTORIZADO));
                $autorizacionOperacion->setUsuarioActualizacion($this->getUser());
                $autorizacionOperacion->setFechaActualizacion(new \DateTime());
                
                $boleto = $autorizacionOperacion->getBoleto();
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($boleto->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $descripcion = "Se ha " . strtolower($autorizacionOperacion->getEstado()->getNombre()) .
                            " la solicitud de autorización ID: " . $autorizacionOperacion->getId() . ".";
                $boletoBitacora->setDescripcion($descripcion);
                $boleto->addBitacoras($boletoBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($autorizacionOperacion);
                    $em->persist($boletoBitacora);
                    
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){ $mensajeServidor = $mensaje; }
                    else{ $mensajeServidor = "m1Ha ocurrido un error en el sistema"; }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, "m1" . UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionOperacion:autorizar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor,
            'autorizacionOperacion' => $autorizacionOperacion
        ));
    }
    
    /**
     * @Route(path="/rechazar.html", name="autorizacion-operaciones-rechazar")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_AUTORIZADOR")
     */
    public function rechazarAutorizacionOperacionesAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('rechazar_autorizacion_operacion_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id de la autorizacion de operacion.");
                }
            }
        }        
        
        $autorizacionOperacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')->find($id);
        if ($autorizacionOperacion === null) {
            return UtilService::returnError($this, "m1La autorizacion de operacion con id: " .$id." no existe.");
        }
        
        if($autorizacionOperacion->getEstado()->getId() !== EstadoAutorizacionOperacion::REGISTRADO){
            return UtilService::returnError($this, "m1Solamente se puede rechazar una solicitud  si esta en estado registrado.");
        }

        $form = $this->createForm(new RechazarAutorizacionOperacionesType($this->getDoctrine()), $autorizacionOperacion, array(
            "user" => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $autorizacionOperacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoAutorizacionOperacion')->find(EstadoAutorizacionOperacion::DENEGADO));
                $autorizacionOperacion->setUsuarioActualizacion($this->getUser());
                $autorizacionOperacion->setFechaActualizacion(new \DateTime());
                        
                $boleto = $autorizacionOperacion->getBoleto();
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($boleto->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $descripcion = "Se ha " . strtolower($autorizacionOperacion->getEstado()->getNombre()) .
                            " la solicitud de autorización ID: " . $autorizacionOperacion->getId() . ".";
                $boletoBitacora->setDescripcion($descripcion);
                $boleto->addBitacoras($boletoBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($autorizacionOperacion);
                    $em->persist($boletoBitacora);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){ $mensajeServidor = $mensaje; }
                    else{ $mensajeServidor = "m1Ha ocurrido un error en el sistema"; }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, "m1" . UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionOperacion:rechazar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor,
            'autorizacionOperacion' => $autorizacionOperacion
        ));
    }
    
    /**
     * @Route(path="/consultar.html", name="autorizacion-operaciones-consultar")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AUTORIZADOR")
     */
    public function consultarAutorizacionOperacionesAction(Request $request, $_route){
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id) || trim($id) === "") {
            return UtilService::returnError($this, "m1Debe seleccionar una caja.");
        }
        
        $autorizacionOperacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')->find($id);
        if ($autorizacionOperacion === null) {
            return UtilService::returnError($this, "m1La autorizacion de operacion con id: " .$id." no existe.");
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionOperacion:consultar.html.twig', array(
            'autorizacionOperacion' => $autorizacionOperacion
        ));
    }
}

?>
