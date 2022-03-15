<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\TerminalOmnibusBundle\Form\Model\ConfigurarFacturaModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Configuracion\ConfigurarFacturaType;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Symfony\Component\HttpFoundation\Request;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
*   @Route(path="/configuracion")
*/
class ConfiguracionController extends Controller {

     /**
     * @Route(path="/config.html", name="configuracion-home")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION")
     */
    public function homeFullAction($_route) {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Configuracion:config.html.twig', array(
            "route" => $_route
        ));
        return $respuesta;
    }
    
//     /**
//     * @Route(path="/configurarFacturaBoleto.html", name="configuracion-configurar-factura-boleto-case1")
//     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_SUPERVISOR_BOLETO")
//     */
//    public function configurarFacturaBoletoAction(Request $request, $_route) {
//        
//        $estacionUsuario = $this->getUser()->getEstacion();
//        if($estacionUsuario === null){
//            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
//                'mensajeServidor' => "m1Usted debe pertenecer a una estación para poder configurar la factura de boleto."
//            ));
//        }
//        
//        $configurarFacturaModel = new ConfigurarFacturaModel();
//        $configurarFacturaModel->setEstacion($estacionUsuario);
//        $configurarFacturaModel->setServicioEstacion(ServicioEstacion::BOLETO);        
//        $form = $this->createForm(new ConfigurarFacturaType($this->getDoctrine()), $configurarFacturaModel, array(
//            "user" => $this->getUser()
//        ));  
//        
//        $mensajeServidor = "";
//        
//        if ($request->isMethod('POST')  && $mensajeServidor === "") {
//            $erroresAux = new ConstraintViolationList();
//            $form->bind($request);
//            
//            $facturaActerior = $this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:Factura')
//                            ->getFacturaPorEstacionEmpresa($estacionUsuario, $configurarFacturaModel->getEmpresa(), ServicioEstacion::BOLETO);
//            $facturaActual = $configurarFacturaModel->getFactura();
//            if($facturaActerior !== null){
//                if($facturaActerior === $facturaActual){
//                    $error = new ConstraintViolation("m1La factura seleccionada ya esta activa." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//            }
//            
//            if($facturaActual === null){
//                $error = new ConstraintViolation("m1Debe seleccionar una factura." , '', array(), '', '', null);
//                $erroresAux->add($error);
//            }
//            else{
//                if($facturaActual->getEstacion() !==  $estacionUsuario){
//                    $error = new ConstraintViolation("m1Usted no pertenece a la estación " . $facturaActual->getEstacion() . "." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//                if($facturaActual->getServicioEstacion()->getId() !== ServicioEstacion::BOLETO){
//                    $error = new ConstraintViolation("m1.La factura seleccionada no pertenece al servicio de boleto." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//                if($facturaActual->getEmpresa()->getId() !== $configurarFacturaModel->getEmpresa()->getId()){
//                    $error = new ConstraintViolation("m1La factura seleccionada no pertenece a la empresa " . $configurarFacturaModel->getEmpresa() . "." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//            }
//            
//            if ($form->isValid() && count($erroresAux) === 0) {
//                
//                $em = $this->getDoctrine()->getManager();
//                $em->getConnection()->beginTransaction();
//                try {
//                    if($facturaActerior !== null){
//                        $facturaActerior->setActivo(false);
//                        $em->persist($facturaActerior);
//                    }
//                    $facturaActual->setActivo(true);
//                    $em->persist($facturaActual);
//                    $em->flush();
//                    $em->getConnection()->commit();
//                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
//                        'mensajeServidor' => "m0"
//                    ));
//                    
//                } catch (\RuntimeException $exc) {
//                    $em->getConnection()->rollback();
//                    $mensaje = $exc->getMessage();
//                    if(UtilService::startsWith($mensaje, 'm1')){
//                        $mensajeServidor = $mensaje;
//                    }else{
//                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
//                    }
//                } catch (\Exception $exc) {
//                    $em->getConnection()->rollback();
//                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
//                }
//                
//            }else{
//               $error = UtilService::getErrorsToForm($form);
//               if($error !== null && $error !== ""){
//                   $mensajeServidor = "m1" . $error;
//               }else{
//                   foreach ($erroresAux as $item) {
//                      $mensajeServidor = $item->getMessage();
//                      if(!UtilService::startsWith($mensajeServidor, "m1")){
//                          $mensajeServidor = "m1" . $mensajeServidor;
//                      }
//                      break;
//                   }
//               }
//            }
//        }
//        
//        return $this->render('AcmeTerminalOmnibusBundle:Configuracion:configurarFacturaBoleto.html.twig', array(
//            'form' => $form->createView(),
//            'configurarFacturaModel' => $configurarFacturaModel,
//            'route' => $_route,
//            'mensajeServidor' => $mensajeServidor
//        ));
//    }
//    
//    /**
//     * @Route(path="/configurarFacturaEncomienda.html", name="configuracion-configurar-factura-encomienda-case1")
//     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_SUPERVISOR_ENCOMIENDA")
//     */
//    public function configurarFacturaEncomiendaAction(Request $request, $_route) {
//        
//        $estacionUsuario = $this->getUser()->getEstacion();
//        if($estacionUsuario === null){
//            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
//                'mensajeServidor' => "m1Usted debe pertenecer a una estación para poder configurar la factura de encomienda."
//            ));
//        }
//        
//        $configurarFacturaModel = new ConfigurarFacturaModel();
//        $configurarFacturaModel->setEstacion($estacionUsuario);
//        $configurarFacturaModel->setServicioEstacion(ServicioEstacion::ENCOMIENDA);        
//        $form = $this->createForm(new ConfigurarFacturaType($this->getDoctrine()), $configurarFacturaModel, array(
//            "user" => $this->getUser()
//        ));  
//        
//        $mensajeServidor = "";
//        
//        if ($request->isMethod('POST')  && $mensajeServidor === "") {
//            $erroresAux = new ConstraintViolationList();
//            $form->bind($request);
//            
//            $facturaActerior = $this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:Factura')
//                            ->getFacturaPorEstacionEmpresa($estacionUsuario, $configurarFacturaModel->getEmpresa(), ServicioEstacion::ENCOMIENDA);
//            $facturaActual = $configurarFacturaModel->getFactura();
//            if($facturaActerior !== null){
//                if($facturaActerior === $facturaActual){
//                    $error = new ConstraintViolation("m1La factura seleccionada ya esta activa." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//            }
//            
//            if($facturaActual === null){
//                $error = new ConstraintViolation("m1Debe seleccionar una factura." , '', array(), '', '', null);
//                $erroresAux->add($error);
//            }
//            else{
//                if($facturaActual->getEstacion() !==  $estacionUsuario){
//                    $error = new ConstraintViolation("m1Usted no pertenece a la estación " . $facturaActual->getEstacion() . "." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//                if($facturaActual->getServicioEstacion()->getId() !== ServicioEstacion::ENCOMIENDA){
//                    $error = new ConstraintViolation("m1.La factura seleccionada no pertenece al servicio de encomienda." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//                if($facturaActual->getEmpresa()->getId() !== $configurarFacturaModel->getEmpresa()->getId()){
//                    $error = new ConstraintViolation("m1La factura seleccionada no pertenece a la empresa " . $configurarFacturaModel->getEmpresa() . "." , '', array(), '', '', null);
//                    $erroresAux->add($error);
//                }
//            }
//            
//            if ($form->isValid() && count($erroresAux) === 0) {
//                
//                $em = $this->getDoctrine()->getManager();
//                $em->getConnection()->beginTransaction();
//                try {
//                    if($facturaActerior !== null){
//                        $facturaActerior->setActivo(false);
//                        $em->persist($facturaActerior);
//                    }
//                    $facturaActual->setActivo(true);
//                    $em->persist($facturaActual);
//                    $em->flush();
//                    $em->getConnection()->commit();
//                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
//                        'mensajeServidor' => "m0"
//                    ));
//                    
//                } catch (\RuntimeException $exc) {
//                    $em->getConnection()->rollback();
//                    $mensaje = $exc->getMessage();
//                    if(UtilService::startsWith($mensaje, 'm1')){
//                        $mensajeServidor = $mensaje;
//                    }else{
//                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
//                    }
//                } catch (\Exception $exc) {
//                    $em->getConnection()->rollback();
//                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
//                }
//                
//            }else{
//               $error = UtilService::getErrorsToForm($form);
//               if($error !== null && $error !== ""){
//                   $mensajeServidor = "m1" . $error;
//               }else{
//                   foreach ($erroresAux as $item) {
//                      $mensajeServidor = $item->getMessage();
//                      if(!UtilService::startsWith($mensajeServidor, "m1")){
//                          $mensajeServidor = "m1" . $mensajeServidor;
//                      }
//                      break;
//                   }
//               }
//            }
//        }
//        
//        return $this->render('AcmeTerminalOmnibusBundle:Configuracion:configurarFacturaEncomienda.html.twig', array(
//            'form' => $form->createView(),
//            'configurarFacturaModel' => $configurarFacturaModel,
//            'route' => $_route,
//            'mensajeServidor' => $mensajeServidor
//        ));
//    }
    
    /**
     * @Route(path="/updateTiempos.html", name="configuracion-update-tiempos-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN_TIEMPOS")
     */
    public function updateTiemposAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $tiempoModel = new \Acme\TerminalOmnibusBundle\Form\Model\TiempoModel();
        $form = $this->createForm(new \Acme\TerminalOmnibusBundle\Form\Frontend\Configuracion\TiempoType($this->getDoctrine()), $tiempoModel); 
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if($form->isValid()){
                
                $ruta = $tiempoModel->getRuta();
                $claseBus = $tiempoModel->getClaseBus();
                $listaTiempos = array();
                $listaHidden = $tiempoModel->getListaItems();
                $listaJson = json_decode($listaHidden);
                $minutosBase = -1;
                foreach ($listaJson as $json) {
                    $idEstacion = $json->id;
                    $item = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tiempo')->getTiempo($ruta, $idEstacion, $claseBus);
                    if($item === null){
                        $item = new \Acme\TerminalOmnibusBundle\Entity\Tiempo();
                        $item->setRuta($ruta);
                        $item->setClaseBus($claseBus);
                        $item->setEstacionDestino($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($idEstacion));
                    }
                    $minutos = intval($json->minutos);
                    if($minutosBase >= $minutos){
                        return UtilService::returnError($this, "Los minutos de la estación " . $item->getEstacionDestino()->getNombre() . " deben ser mayor a " . $minutosBase . ".");
                    }
                    $minutosBase = $minutos;
                    $item->setMinutos($minutos);
                    $erroresItems = $this->get('validator')->validate($item);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $listaTiempos[] = $item;
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    foreach ($listaTiempos as $item) {
                        $em->persist($item);
                        if($item instanceof \Acme\BackendBundle\Entity\IJobSync){
                            if($item->isValidToSync()){
                                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                                $jobSync->setNivel($item->getNivelSync());
                                $jobSync->setType($item->getTypeSync());
                                $jobSync->setUsuarioCreacion($this->getUser());
                                $jobSync->setData($item->getDataArrayToSync());
                                $this->get('acme_job_sync')->createJobSync($jobSync, false);
                            }
                        }
                    }
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
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Configuracion:tiempo.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/updateUsuario.html", name="configuracion-update-usuario-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateUsuarioAction(Request $request, $_route) {
        
        $usuarioModel = new \Acme\TerminalOmnibusBundle\Form\Model\UsuarioModel();  
        $form = $this->createForm(new \Acme\TerminalOmnibusBundle\Form\Frontend\Configuracion\UpdateUsuarioType($this->getDoctrine()), $usuarioModel);  
        
        $mensajeServidor = "";
        
        if ($request->isMethod('POST')  && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);

            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $change = false;
                    $user = $form->get('user')->getData();
                    $plainPassword = $usuarioModel->getPlainPassword();
                    if($plainPassword !== null && trim($plainPassword) !== ""){
                        $user->setPlainPassword($plainPassword);
                        $user->setDateLastUdate(new \DateTime());
                        $credentialsExpireAt = new \DateTime();
                        $daysCredentialsExpire = $this->container->getParameter("days_credentials_expire");
                        if(!$daysCredentialsExpire){ $daysCredentialsExpire = 90; }
                        $credentialsExpireAt->modify("+" . $daysCredentialsExpire . " day");
                        $user->setCredentialsExpireAt($credentialsExpireAt);
                        $user->setCredentialsExpired(false);
                        $change = true;
                    }
                    
                    if($usuarioModel->getEstacion() !== null){
                        $user->setEstacion($usuarioModel->getEstacion());
                        $change = true;
                    }
                    
                    if($usuarioModel->getTodasEstaciones() === true || $usuarioModel->getTodasEstaciones() === 'true'){
                        $user->setEstacion(null);
                        $change = true;
                    }
                    
                    if($usuarioModel->getDesbloquear() === true || $usuarioModel->getDesbloquear() === 'true'){
                        $user->setIntentosFallidos(0);
                        $user->setLocked(false);
                        $change = true;
                    }
                    
                    if($change === false){
                        return UtilService::returnError($this, "m1Debe modificar algun dato del usuario.");
                    }
                    
                    $this->container->get('fos_user.user_manager')->updateUser($user);
                    
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Configuracion:updateUser.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
}

?>
