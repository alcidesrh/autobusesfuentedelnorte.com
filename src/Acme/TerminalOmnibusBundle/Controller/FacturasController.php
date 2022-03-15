<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\TerminalOmnibusBundle\Form\Frontend\Factura\ConsultarSeriesFacturaType;
use Acme\TerminalOmnibusBundle\Form\Model\ConsultarSeriesFacturaModel;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Form\Frontend\Factura\CrearSeriesFacturaType;
use Acme\TerminalOmnibusBundle\Entity\Factura;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\TerminalOmnibusBundle\Form\Frontend\Factura\ActualizarSeriesFacturaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Factura\ActivarSeriesFacturaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Factura\DesactivarSeriesFacturaType;

/**
*   @Route(path="/factura")
*/
class FacturasController extends Controller {
    
    /**
     * @Route(path="/", name="facturas-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function homeFacturaAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Factura:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarFacturas.json", name="facturas-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
    */
    public function listarFacturasAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Factura');
                $result = $repository->getFacturasPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'estacion' => $item->getEstacion()->__toString(),
                        'empresa' => $item->getEmpresa()->getAlias(),
                        'servicio' => $item->getServicioEstacion()->__toString(),
                        'serieValor' => $item->getSerieResolucionFactura() . "-" . $item->getValorResolucionFactura(),
                        'serie' => $item->getSerieResolucionFactura(),
                        'valor' => $item->getValorResolucionFactura(),
                        'minimo' => $item->getMinimoResolucionFactura(),
                        'maximo' => $item->getMaximoResolucionFactura(),
                        'fechaEmision' => $item->getFechaEmisionResolucionFactura() === null ? "" : $item->getFechaEmisionResolucionFactura()->format('d-m-Y') ,
                        'fechaVencimiento' => $item->getFechaVencimientoResolucionFactura() === null ? "" : $item->getFechaVencimientoResolucionFactura()->format('d-m-Y') ,
                        'activo' => $item->getActivo() === true ? "SI" : "NO",
                        'impresora' => $item->getImpresora() !== null ? $item->getImpresora()->getNombre() : ''
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\Exception $exc) {
            var_dump($exc);
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
     * @Route(path="/crear.html", name="facturas-crear-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function crearAction(Request $request, $_route) {
        
        $factura = new Factura();
        $factura->setNombreResolucionSistema("temp");
        $factura->setFechaEmisionResolucionSistema(new \DateTime());
        $factura->setFechaVencimientoResolucionSistema(new \DateTime());
        $form = $this->createForm(new CrearSeriesFacturaType($this->getDoctrine()), $factura, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        
        if ($request->isMethod('POST')  && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $factura->setNombreResolucionSistema($factura->getNombreResolucionFactura());
                $factura->setFechaEmisionResolucionSistema($factura->getFechaEmisionResolucionFactura());
                $factura->setFechaVencimientoResolucionSistema($factura->getFechaVencimientoResolucionFactura());
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($factura);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               if($error !== null && $error !== ""){
                   $mensajeServidor = "m1" . $error;
               }else{
                   foreach ($erroresAux as $item) {
                      $mensajeServidor = $item->getMessage();
                      if(!UtilService::startsWith($mensajeServidor, "m1")){
                          $mensajeServidor = "m1" . $mensajeServidor;
                      }
                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Factura:crear.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/actualizar.html", name="facturas-actualizar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function actualizarAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('series_factura_command'); //Submit
            var_dump($command);
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $factura = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')->find($id);
        if (!$factura) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1La serie de factura con id: " .$id." no existe."
            ));
        }
        
        $form = $this->createForm(new ActualizarSeriesFacturaType($this->getDoctrine()), $factura, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        
        if ($request->isMethod('POST')  && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $factura->setNombreResolucionSistema($factura->getNombreResolucionFactura());
                $factura->setFechaEmisionResolucionSistema($factura->getFechaEmisionResolucionFactura());
                $factura->setFechaVencimientoResolucionSistema($factura->getFechaVencimientoResolucionFactura());
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($factura);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               if($error !== null && $error !== ""){
                   $mensajeServidor = "m1" . $error;
               }else{
                   foreach ($erroresAux as $item) {
                      $mensajeServidor = $item->getMessage();
                      if(!UtilService::startsWith($mensajeServidor, "m1")){
                          $mensajeServidor = "m1" . $mensajeServidor;
                      }
                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Factura:actualizar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/activar.html", name="facturas-activar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function activarAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('series_factura_command'); //Submit
            var_dump($command);
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $factura = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')->find($id);
        if (!$factura) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1La serie de factura con id: " .$id." no existe."
            ));
        }
        
        $form = $this->createForm(new ActivarSeriesFacturaType($this->getDoctrine()), $factura, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        
        if ($request->isMethod('POST')  && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $factura->setActivo(true);
            $form->bind($request);
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($factura);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               if($error !== null && $error !== ""){
                   $mensajeServidor = "m1" . $error;
               }else{
                   foreach ($erroresAux as $item) {
                      $mensajeServidor = $item->getMessage();
                      if(!UtilService::startsWith($mensajeServidor, "m1")){
                          $mensajeServidor = "m1" . $mensajeServidor;
                      }
                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Factura:activar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/desactivar.html", name="facturas-desactivar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function desactivarAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('series_factura_command'); //Submit
            var_dump($command);
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $factura = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')->find($id);
        if (!$factura) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1La serie de factura con id: " .$id." no existe."
            ));
        }
        
        $form = $this->createForm(new DesactivarSeriesFacturaType($this->getDoctrine()), $factura, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        
        if ($request->isMethod('POST')  && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $factura->setActivo(false);
            $form->bind($request);
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($factura);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               if($error !== null && $error !== ""){
                   $mensajeServidor = "m1" . $error;
               }else{
                   foreach ($erroresAux as $item) {
                      $mensajeServidor = $item->getMessage();
                      if(!UtilService::startsWith($mensajeServidor, "m1")){
                          $mensajeServidor = "m1" . $mensajeServidor;
                      }
                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Factura:desactivar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
     /**
     * @Route(path="/config.html", name="factura-consultar-series")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function consultarSeriesFacturaAction($_route) {
        
        $reporte = new ConsultarSeriesFacturaModel();       
        $form = $this->createForm(new ConsultarSeriesFacturaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
        ));
        
        $facturas = array();
        $estacion = $this->getUser()->getEstacion();
        if($estacion !== null){
            $facturas = $this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:Factura')
                               ->getFacturasPorEstacion($estacion, $this->getUser()->getEmpresas());
        }

        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Factura:consultarSeriesFactura.html.twig', array(
            'form' => $form->createView(),
            'facturas' => $facturas,
            'route' => $_route,
            'mensajeServidor' => ""
        ));
        return $respuesta;
    }
    
    
}

?>
