<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Form\Frontend\Bus\CambiarEstadoBusType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Bus\CrearBusType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Bus\ActualizarBusType;
use Acme\TerminalOmnibusBundle\Entity\Bus;

/**
*   @Route(path="/bus")
*/
class BusController extends Controller {
    
    /**
     * @Route(path="/", name="buses-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_ADMIN_BUSES, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function homeBusesAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Buses:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarBuses.json", name="buses-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_ADMIN_BUSES, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
    */
    public function listarBusesAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Bus');
                $result = $repository->getBusesPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getCodigo(),
                        'placa' => $item->getPlaca(),
                        'tipo' => $item->getTipo()->getInfo1(),
                        'empresa' => $item->getEmpresa() != null ? $item->getEmpresa()->getAlias() : "",
                        'estado' => $item->getEstado()->getNombre()
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\Exception $exc) {
            var_dump($exc->getTraceAsString());
            echo $exc->getTraceAsString();
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
     * @Route(path="/actualizarBus.html", name="buses-actualizar-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_BUSES")
     */
    public function actualizarBusAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('actualizar_bus_command'); //Submit
                if($command !== null){
                    $id = $command["codigo"];
                }
            }
        }
      
        if (is_null($id)) {
            return UtilService::returnError($this, "No se pudo obtener el identificador del bus."); 
        }

        $bus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Bus')->find($id); 
        if ($bus === null) {
            return UtilService::returnError($this, "El bus con id: ". $id. " no existe.");
        }
        
        $form = $this->createForm(new ActualizarBusType($this->getDoctrine()), $bus, array(
            'user' => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($bus);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Buses:actualizar.html.twig', array(
            'bus' => $bus,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/crearBus.html", name="buses-crear-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_BUSES")
     */
    public function crearBusAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $bus = new Bus();        
        $form = $this->createForm(new CrearBusType($this->getDoctrine()), $bus, array(
            'user' => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($bus);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Buses:crear.html.twig', array(
            'bus' => $bus,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/cambiarEstadoBus.html", name="buses-cambiarEstado-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_ADMIN_BUSES, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_PROPIETARIO")
     */
    public function cambiarEstadoBusAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('cambiar_estado_bus_command'); //Submit
                if($command !== null){
                    $id = $command["codigo"];
                }
            }
        }
      
        if (is_null($id)) {
            return UtilService::returnError($this, "No se pudo obtener el identificador del bus."); 
        }

        $bus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Bus')->find($id); 
        if ($bus === null) {
            return UtilService::returnError($this, "m1El bus con id: ". $id. " no existe.");
        }
        
        $form = $this->createForm(new CambiarEstadoBusType($this->getDoctrine()), $bus);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($bus);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Buses:cambiarEstadoBus.html.twig', array(
            'bus' => $bus,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
}

?>
