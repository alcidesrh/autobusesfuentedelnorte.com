<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Form\Frontend\Alquiler\CrearAlquilerType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Alquiler\ActualizarAlquilerType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Alquiler\IniciarAlquilerType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Alquiler\CancelarAlquilerType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Alquiler\ConsultarAlquilerType;
use Acme\TerminalOmnibusBundle\Entity\Alquiler;
use Acme\TerminalOmnibusBundle\Entity\EstadoAlquiler;
use Acme\TerminalOmnibusBundle\Entity\FechaAlquiler;

/**
*   @Route(path="/alquiler")
*/
class AlquilerController extends Controller {

    /**
     * @Route(path="/", name="alquiler-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function homeAlquilerAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Alquiler:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarAlquiler.json", name="alquiler-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
    */
    public function listarAlquilerAction($_route) {
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
                $result = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Alquiler')
                        ->getAlquilerPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'fechaInicial' => $item->getFechaInicial()->format('d-m-Y'),
                        'fechaFinal' => $item->getFechaFinal()->format('d-m-Y'),
                        'empresa' => $item->getEmpresa()->getAlias(),
                        'bus' => $item->getBus()  === null ? "" : $item->getBus()->__toString(),
                        'piloto1' => $item->getPiloto()  === null ? "" : $item->getPiloto()->__toString(),
                        'piloto2' => $item->getPilotoAux()  === null ? "" : $item->getPilotoAux()->__toString(),
                        'estado' => $item->getEstado()->__toString(),
                        'importe' => "GTQ " . strval($item->getImporte()),
                        'descripcion' => $item->getObservacion()
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
     * @Route(path="/crear.html", name="alquiler-crear-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function crearAlquilerAction(Request $request, $_route) {
        
        $alquiler = new Alquiler();
        $form = $this->createForm(new CrearAlquilerType($this->getDoctrine()), $alquiler, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $alquiler->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoAlquiler')->find(EstadoAlquiler::REGISTRADO));
            $alquiler->setFechaCreacion(new \DateTime());
            $alquiler->setUsuarioCreacion($this->getUser());
            $form->bind($request);
            
            $rangoFecha = $form->get('rangoFecha')->getData();
            if($rangoFecha !== null && trim($rangoFecha) !== ""){
                $rangoFechaArray = explode("-", $rangoFecha);
                if(count($rangoFechaArray) === 2){
                    $fechaInicialStr = trim($rangoFechaArray[0]);
                    $fechaFinalStr = trim($rangoFechaArray[1]);
                    if($fechaInicialStr !== "" && $fechaFinalStr !== ""){
                        $fechaInicialDateTime = \DateTime::createFromFormat('d/m/Y', $fechaInicialStr);
                        if($fechaInicialDateTime === false){
                            $fechaInicialDateTime = \DateTime::createFromFormat('d-m-Y', $fechaInicialStr);
                        }
                        if($fechaInicialDateTime === false){
                            return UtilService::returnError($this, "m1No se pudo conventir la fecha: " . $fechaInicialStr);
                        }
                        $fechaInicialDateTime->setTime(0, 0, 0);
                        $alquiler->setFechaInicial($fechaInicialDateTime);
                        
                        $fechaFinalDateTime = \DateTime::createFromFormat('d/m/Y', $fechaFinalStr);
                        if($fechaFinalDateTime === false){
                            $fechaFinalDateTime = \DateTime::createFromFormat('d-m-Y', $fechaFinalStr);
                        }
                        if($fechaFinalDateTime === false){
                            return UtilService::returnError($this, "m1No se pudo conventir la fecha: " . $fechaFinalStr);
                        }
                        $fechaFinalDateTime->setTime(0, 0, 0);
                        $alquiler->setFechaFinal($fechaFinalDateTime);
                        
                    }else{
                        return UtilService::returnError($this, "m1Debe definir un rango de fecha válido.");
                    }                  
                }else{
                    return UtilService::returnError($this, "m1Debe definir un rango de fecha válido.");
                }
            }else{
                return UtilService::returnError($this, "m1Debe definir un rango de fecha.");
            }
            
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($alquiler);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Alquiler:crear.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/actualizar.html", name="alquiler-actualizar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function actualizarAlquilerAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('actualizar_alquiler_command'); //Submit
//            var_dump($command);
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $alquiler = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Alquiler')->find($id);
        if (!$alquiler) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1El alquier de bus con id: " .$id." no existe."
            ));
        }
        
        if($alquiler->getEstado()->getId() !== EstadoAlquiler::REGISTRADO){
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1Solamente se puede actualizar un alquiler en estado registrado."
            ));
        }
        
        $form = $this->createForm(new ActualizarAlquilerType($this->getDoctrine()), $alquiler, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            $rangoFecha = $form->get('rangoFecha')->getData();
            if($rangoFecha !== null && trim($rangoFecha) !== ""){
                $rangoFechaArray = explode("-", $rangoFecha);
                if(count($rangoFechaArray) === 2){
                    $fechaInicialStr = trim($rangoFechaArray[0]);
                    $fechaFinalStr = trim($rangoFechaArray[1]);
                    if($fechaInicialStr !== "" && $fechaFinalStr !== ""){
                        $fechaInicialDateTime = \DateTime::createFromFormat('d/m/Y', $fechaInicialStr);
                        if($fechaInicialDateTime === false){
                            $fechaInicialDateTime = \DateTime::createFromFormat('d-m-Y', $fechaInicialStr);
                        }
                        if($fechaInicialDateTime === false){
                            return UtilService::returnError($this, "m1No se pudo conventir la fecha: " . $fechaInicialStr);
                        }
                        $fechaInicialDateTime->setTime(0, 0, 0);
                        $alquiler->setFechaInicial($fechaInicialDateTime);
                        
                        $fechaFinalDateTime = \DateTime::createFromFormat('d/m/Y', $fechaFinalStr);
                        if($fechaFinalDateTime === false){
                            $fechaFinalDateTime = \DateTime::createFromFormat('d-m-Y', $fechaFinalStr);
                        }
                        if($fechaFinalDateTime === false){
                            return UtilService::returnError($this, "m1No se pudo conventir la fecha: " . $fechaFinalStr);
                        }
                        $fechaFinalDateTime->setTime(0, 0, 0);
                        $alquiler->setFechaFinal($fechaFinalDateTime);
                        
                    }else{
                        return UtilService::returnError($this, "m1Debe definir un rango de fecha válido.");
                    }                  
                }else{
                    return UtilService::returnError($this, "m1Debe definir un rango de fecha válido.");
                }
            }else{
                return UtilService::returnError($this, "m1Debe definir un rango de fecha.");
            }
            
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($alquiler);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Alquiler:actualizar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/consultar.html", name="alquiler-consultar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function consultarAlquilerAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        $alquiler = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Alquiler')->find($id);
        if (!$alquiler) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1El alquier de bus con id: " .$id." no existe."
            ));
        }
        
        $form = $this->createForm(new ConsultarAlquilerType($this->getDoctrine()), $alquiler, array(
            "user" => $this->getUser()
        ));  
        
        return $this->render('AcmeTerminalOmnibusBundle:Alquiler:consultar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/iniciar.html", name="alquiler-iniciar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function iniciarAlquilerAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('iniciar_alquiler_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $alquiler = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Alquiler')->find($id);
        if (!$alquiler) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1El alquier de bus con id: " .$id." no existe."
            ));
        }
        
        if($alquiler->getEstado()->getId() !== EstadoAlquiler::REGISTRADO){
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1Solamente se puede iniciar un alquiler en estado registrado."
            ));
        }
        
        if($this->getUser()->getEstacion() === null){
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1Para iniciar un alquiler debe estar asignado a una estación."
            ));
        }
        
        $form = $this->createForm(new IniciarAlquilerType($this->getDoctrine()), $alquiler, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            $alquiler->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoAlquiler')->find(EstadoAlquiler::EFECTUADO));
            $alquiler->setFechaEfectuado(new \DateTime());
            $alquiler->setUsuarioEfectuado($this->getUser());
            $alquiler->setEstacionEfectuado($this->getUser()->getEstacion());
            
            //adicionando fechas para el control de asistencia
            $fechaInicial = clone $alquiler->getFechaInicial();
            $fechaInicial->setTime(0, 0, 0);
            $fechaFinal = clone $alquiler->getFechaFinal();
            $fechaFinal->setTime(0, 0, 0);
            while (UtilService::compararFechas($fechaInicial, $fechaFinal) <= 0) {
               $fechaAlquiler = new FechaAlquiler();
               $fechaAlquiler->setFecha(clone $fechaInicial);
               $alquiler->addListaFechaAlquiler($fechaAlquiler);
               $fechaInicial->modify('+1 day');
               $fechaInicial->setTime(0, 0, 0);
            }
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($alquiler);
                    $em->flush();
                    $em->getConnection()->commit();
                    
                    $correos = $alquiler->getEmpresa()->getCorreos();
                    if($correos !== null && count($correos) !== 0){
                        $now = new \DateTime();
                        $now = $now->format('Y-m-d H:i:s');
                        $subject = "NSA_" . $now . ". Notificación de salida de alquiler."; 
                        UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_alquiler.html.twig', array(
                            'alquiler' => $alquiler
                        )));
                    }
                    
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Alquiler:iniciar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/cancelar.html", name="alquiler-cancelar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function cancelarAlquilerAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cancelar_alquiler_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $alquiler = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Alquiler')->find($id);
        if (!$alquiler) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1El alquier de bus con id: " .$id." no existe."
            ));
        }
        
        if($alquiler->getEstado()->getId() !== EstadoAlquiler::REGISTRADO){
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1Solamente se puede cancelar un alquiler en estado registrado."
            ));
        }
        
        $form = $this->createForm(new CancelarAlquilerType($this->getDoctrine()), $alquiler, array(
            "user" => $this->getUser()
        ));  
        
        $mensajeServidor = "";
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            $alquiler->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoAlquiler')->find(EstadoAlquiler::CANCELADO));
            $alquiler->setFechaCancelado(new \DateTime());
            $alquiler->setUsuarioCancelado($this->getUser());
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($alquiler);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Alquiler:cancelar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
}

?>
