<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Entity\Caja;
use Acme\TerminalOmnibusBundle\Form\Frontend\Caja\CrearCajaType;
use Acme\TerminalOmnibusBundle\Entity\EstadoCaja;
use Acme\TerminalOmnibusBundle\Entity\OperacionCaja;
use Acme\TerminalOmnibusBundle\Entity\TipoOperacionCaja;
use Acme\TerminalOmnibusBundle\Form\Frontend\Caja\AbrirCajaType;
use Symfony\Component\Validator\ConstraintViolation;
use Acme\TerminalOmnibusBundle\Form\Frontend\Caja\PreCerrarCajaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Caja\RechazarCierreCajaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Caja\CerrarCajaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Caja\CancelarCajaType;

/**
*   @Route(path="/caja")
*/
class CajaController extends Controller {

    /**
     * @Route(path="/", name="cajas-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function homeCajasAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Caja:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarCajas.json", name="cajas-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
    */
    public function listarCajasAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Caja');
                $result = $repository->getCajasPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'moneda' => $item->getMoneda()->getSigla(),
                        'usuario' => $item->getUsuario()->__toString(),
                        'estacion' => $item->getEstacion()->__toString(),
                        'estado' => $item->getEstado()->getNombre(),
                        'fechaCreacion' => $item->getFechaCreacion() === null ? "" : $item->getFechaCreacion()->format('d-m-Y H:i:s'),
                        'fechaApertura' => $item->getFechaApertura() === null ? "" : $item->getFechaApertura()->format('d-m-Y H:i:s'),
//                        'fechaCierre' => $item->getFechaCreacion() === null ? "" : $item->getFechaCreacion()->format('d-m-Y H:i:s'),
//                        'fechaCancelacion' => $item->getFechaCreacion() === null ? "" : $item->getFechaCreacion()->format('d-m-Y H:i:s')
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\Exception $exc) {
//            var_dump($exc->getTraceAsString());
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
     * @Route(path="/crear.html", name="cajas-crear-case1")
     * @Route(path="/crear/", name="cajas-crear-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function crearCajaAction(Request $request, $_route) {
        
        $caja = new Caja();
        $caja->setEstacion($this->getUser()->getEstacion());
        $form = $this->createForm(new CrearCajaType($this->getDoctrine()), $caja, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $caja->setFechaCreacion(new \DateTime());
            $caja->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCaja')->find(EstadoCaja::CREADA));
            $form->bind($request);
            
            $importeInicial = $form->get('importe')->getData();
            if($importeInicial === null || trim($importeInicial) === ""){
                $error = "Debe definir el importe inicial.";
                $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                return UtilService::returnError($this, $error);
            }
            else{
                $importeInicial = abs(doubleval($importeInicial));
//                if($importeInicial == 0){
//                    $error = new ConstraintViolation("El importe inicial no puede ser 0." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }else{
                    $operacionCaja = new OperacionCaja();
                    $caja->addOperacion($operacionCaja);
                    $operacionCaja->setTipoOperacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::INICIAL));
                    $operacionCaja->setImporte(-1 * $importeInicial);
                    $operacionCaja->setFecha(new \DateTime());
                    $operacionCaja->setDescripcion("Saldo inicial para la apertura de caja");
                    $erroresItems = $this->get('validator')->validate($operacionCaja);
                    if($erroresItems !== null){
                        $erroresAux->addAll($erroresItems);
                    }
//                }
            }
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($caja);
                    $em->persist($operacionCaja);
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
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Caja:crear.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/abrir.html", name="cajas-abrir-case1")
     * @Route(path="/abrir/", name="cajas-abrir-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function abrirCajaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('abrir_caja_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        if (is_null($id) || trim($id) === "") {
            return UtilService::returnError($this, "m1Debe seleccionar una caja.");
        }
        
        $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->find($id); 
        if (!$caja) {
            return UtilService::returnError($this, "m1La caja con id: ".$id. " no existe.");
        }
        
        $form = $this->createForm(new AbrirCajaType($this->getDoctrine()), $caja);
        $mensajeServidor = "";
        
        if($caja->getEstado()->getId() !== EstadoCaja::CREADA){
            return UtilService::returnError($this, "m1Solamente se puede abrir cajas que esten en estado creada.");
        }
        
        if($caja->getUsuario() !== $this->getUser()){
            return UtilService::returnError($this, "m1No está autorizado para abrir la caja.");
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") { 
            $erroresAux = new ConstraintViolationList();
            $caja->setFechaApertura(new \DateTime());
            $caja->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCaja')->find(EstadoCaja::ABIERTA));
            $form->bind($request);
           
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($caja);
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
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Caja:abrir.html.twig', array(
            'caja' => $caja,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
            
    /**
     * @Route(path="/preCerrar.html", name="cajas-preCerrar-case1")
     * @Route(path="/preCerrar/", name="cajas-preCerrar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function preCerrarCajaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('precerrar_caja_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        if (is_null($id) || trim($id) === "") {
            return UtilService::returnError($this, "m1Debe seleccionar una caja.");
        }
        
        $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->find($id); 
        if (!$caja) {
            return UtilService::returnError($this, "m1La caja con id: ".$id. " no existe.");
        }
        
        $form = $this->createForm(new PreCerrarCajaType($this->getDoctrine()), $caja);
        $mensajeServidor = "";
        
        if($caja->getEstado()->getId() !== EstadoCaja::ABIERTA){
            $mensajeServidor = "m1Solamente se puede solicitar el cierre de cajas que esten en estado abierta.";
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($caja->getUsuario() !== $this->getUser()){
            $mensajeServidor = "m1No está autorizado para solicitar el cierre de la caja.";
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") { 
            $erroresAux = new ConstraintViolationList();
            $caja->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCaja')->find(EstadoCaja::PRE_CIERRE));
            $form->bind($request);
           
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($caja);
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
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Caja:preCierre.html.twig', array(
            'caja' => $caja,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/consultar.html", name="cajas-consultar-case1")
     * @Route(path="/consultar/", name="cajas-consultar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function consultarCajaAction(Request $request, $_route){
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('precerrar_caja_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        if (is_null($id) || trim($id) === "") {
            return UtilService::returnError($this, "m1Debe seleccionar una caja.");
        }
        
        $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->find($id); 
        if (!$caja) {
            return UtilService::returnError($this, "m1La caja con id: ".$id. " no existe.");
        }
        
        $mensajeServidor = "";

        $importeTotal = 0;
        $totalOperaciones = 0;
        if($mensajeServidor === ""){
            $importeTotal = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:OperacionCaja')->obtenerImporteTotal($caja);
            $totalOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:OperacionCaja')->obtenerTotalOperaciones($caja);
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Caja:consultar.html.twig', array(
            'caja' => $caja,
            'importeTotal' => abs($importeTotal),
            'totalOperaciones' => abs($totalOperaciones),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
        
    }
    
    /**
     * @Route(path="/rechazarCierre.html", name="cajas-rechazarCierre-case1")
     * @Route(path="/rechazarCierre/", name="cajas-rechazarCierre-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function rechazarCierreCajaAction(Request $request, $_route){
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('rechazarcierre_caja_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        if (is_null($id) || trim($id) === "") {
            return UtilService::returnError($this, "m1Debe seleccionar una caja.");
        }
        
        $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->find($id); 
        if (!$caja) {
            return UtilService::returnError($this, "m1La caja con id: ".$id. " no existe.");
        }
        
        $form = $this->createForm(new RechazarCierreCajaType($this->getDoctrine()), $caja);
        $mensajeServidor = "";
        
        if($caja->getEstado()->getId() !== EstadoCaja::PRE_CIERRE){
            $mensajeServidor = "m1Solamente se puede rechazar el cierre de cajas que esten en estado de pre cierre.";
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") { 
            $erroresAux = new ConstraintViolationList();
            $caja->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCaja')->find(EstadoCaja::ABIERTA));
            $form->bind($request);
           
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($caja);
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
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Caja:rechazarCierre.html.twig', array(
            'caja' => $caja,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/cerrar.html", name="cajas-cerrar-case1")
     * @Route(path="/cerrar/", name="cajas-cerrar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function cerrarCajaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cerrar_caja_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        if (is_null($id) || trim($id) === "") {
            return UtilService::returnError($this, "m1Debe seleccionar una caja.");
        }
        
        $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->find($id); 
        if (!$caja) {
            return UtilService::returnError($this, "m1La caja con id: ".$id. " no existe.");
        }
        
        $form = $this->createForm(new CerrarCajaType($this->getDoctrine()), $caja);
        $mensajeServidor = "";
        
        if($caja->getEstado()->getId() !== EstadoCaja::PRE_CIERRE){
            $mensajeServidor = "m1Solamente se puede cerrar cajas que esten en estado de pre cierre.";
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") { 
            $erroresAux = new ConstraintViolationList();
            $caja->setFechaCierre(new \DateTime());
            $caja->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCaja')->find(EstadoCaja::CERRADA));
            $form->bind($request);
           
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($caja);
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
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Caja:cerrar.html.twig', array(
            'caja' => $caja,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/cancelar.html", name="cajas-cancelar-case1")
     * @Route(path="/cancelar/", name="cajas-cancelar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function cancelarCajaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cancelar_caja_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        if (is_null($id) || trim($id) === "") {
            return UtilService::returnError($this, "m1Debe seleccionar una caja.");
        }
        
        $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->find($id); 
        if (!$caja) {
            return UtilService::returnError($this, "m1La caja con id: ".$id. " no existe.");
        }
        
        $form = $this->createForm(new CancelarCajaType($this->getDoctrine()), $caja);
        $mensajeServidor = "";
        
        if($caja->getEstado()->getId() !== EstadoCaja::CREADA){
            $mensajeServidor = "m1Solamente se puede cancelar cajas que esten en estado creada.";
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") { 
            $erroresAux = new ConstraintViolationList();
            $caja->setFechaCancelacion(new \DateTime());
            $caja->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCaja')->find(EstadoCaja::CANCELADA));
            $form->bind($request);
           
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($caja);
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
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Caja:cancelar.html.twig', array(
            'caja' => $caja,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
}

?>
