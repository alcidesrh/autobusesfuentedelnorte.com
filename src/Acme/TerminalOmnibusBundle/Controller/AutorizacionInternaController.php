<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\BackendBundle\Services\UtilService;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\TerminalOmnibusBundle\Entity\AutorizacionInterna;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionInterna\CancelarAutorizacionInternaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionInterna\CrearAutorizacionInternaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionInterna\CrearAutorizacionInternaMultipleType;
use Acme\TerminalOmnibusBundle\Form\Model\AutorizacionInternaMultiplesModel;

/**
*   @Route(path="/autorizacioninterna")
*/
class AutorizacionInternaController extends Controller {

    /**
     * @Route(path="/", name="autorizacionInterna-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function homeAutorizacionInternaAction($_route) {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:AutorizacionInterna:listar.html.twig', array(
            "route" => $_route
        ));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/listarAutorizacionInterna.json", name="autorizacionInterna-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function listarAutorizacionesInternasAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionInterna');
                $result = $repository->getAutorizacionesInternasPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'fecha' => $item->getFechaCreacion()->format('d-m-Y H:i:s'),
                        'codigo' => $item->getCodigo(),
                        'motivo' => $item->getMotivo()
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (Exception $exc) {
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
     * @Route(path="/crear.html", name="autorizacionInterna-crear-case1")
     * @Route(path="/crear/", name="autorizacionInterna-crear-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function crearAutorizacionInternaAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        
        $autorizacionInterna = new AutorizacionInterna();
        $autorizacionInterna->setEstacion($this->getUser()->getEstacion());
        $autorizacionInterna->setActivo(true);
        $autorizacionInterna->setUsuarioCreacion($this->getUser());
        $autorizacionInterna->setFechaCreacion(new \DateTime());
        if (!$request->isMethod('POST')){
            $ping = $this->get('acme_backend_util')->generatePin();
            $autorizacionInterna->setCodigo($ping);
        }
        $form = $this->createForm(new CrearAutorizacionInternaType($this->getDoctrine()), $autorizacionInterna);

        if($this->getUser()->getEstacion() === null){
            $mensajeServidor = "m1El usuario debe pertenecer a una estación.";
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($autorizacionInterna);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionInterna:crear.html.twig', array(
            'autorizacionInterna' => $autorizacionInterna,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/cancelar.html", name="autorizacionInterna-cancelar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function cancelarAutorizacionInternaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cancelar_autorizacion_interna_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }

        $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionInterna');
        $autorizacionInterna = $repository->find($id); 
        if (!$autorizacionInterna) {
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m1La autorización interna con id: ".$id." no existe."
            ));
        }
        
        $form = $this->createForm(new CancelarAutorizacionInternaType($this->getDoctrine()), $autorizacionInterna);
        $mensajeServidor = "";
        
        if ($request->isMethod('POST') && $mensajeServidor === "") { 
            $erroresAux = new ConstraintViolationList();
            $autorizacionInterna->setActivo(false);
            $form->bind($request);
           
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($autorizacionInterna);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionInterna:cancelar.html.twig', array(
            'autorizacionInterna' => $autorizacionInterna,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/crearMultiples.html", name="autorizacionInterna-crear-multiples-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function crearAutorizacionInternaMultiplesAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        
        $autorizacionInternaMultiplesModel = new AutorizacionInternaMultiplesModel();
        $form = $this->createForm(new CrearAutorizacionInternaMultipleType($this->getDoctrine()), $autorizacionInternaMultiplesModel, array(
            "user" => $this->getUser()
        ));
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $autorizaciones = array();
                for ($index = 0; $index < $autorizacionInternaMultiplesModel->getCantidad(); $index++) {
                    $autorizacionInterna = new AutorizacionInterna();
                    $autorizacionInterna->setEstacion($this->getUser()->getEstacion());
                    $autorizacionInterna->setMotivo($autorizacionInternaMultiplesModel->getMotivo());
                    $autorizacionInterna->setActivo(true);
                    $autorizacionInterna->setUsuarioCreacion($this->getUser());
                    $autorizacionInterna->setFechaCreacion(new \DateTime());
                    $ping = $this->get('acme_backend_util')->generatePin();
                    $autorizacionInterna->setCodigo($ping);
                    
                    $erroresItems = $this->get('validator')->validate($autorizacionInterna);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        $erroresAux->addAll($erroresItems);
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $autorizaciones[] = $autorizacionInterna;
                }

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    foreach ($autorizaciones as $item) {
                        $em->persist($item);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:AutorizacionInterna:crearMultiples.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
}

?>
