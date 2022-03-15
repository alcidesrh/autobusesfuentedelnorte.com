<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\TerminalOmnibusBundle\Form\Frontend\Usuario\CambiarContrasenaUsuarioType;
use Acme\TerminalOmnibusBundle\Form\Model\CambiarContrasenaUsuarioModel;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Form\Model\CambiarEstacionUsuarioModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Usuario\CambiarEstacionUsuarioType;

class HomeController extends Controller {

     /**
     * @Route(path="/", name="home-default", defaults={"_format"="html", "_locale": "es"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_USER")
     */
    public function homeFullAction($_route) {
//        $formato = $this->get('request')->getRequestFormat();
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Home:homeFull.html.twig', array(
            "route" => $_route
        ));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/homeInternal.html", name="homeInternal-default", requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_USER")
     */
    public function homeInternalAction($_route) {
        $formato = $this->get('request')->getRequestFormat();
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Home:homeInternal.'.$formato.'.twig', array(
            "route" => $_route
        ));
        $respuesta->setSharedMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes'));  //Cache del navegador
        return $respuesta;
    }
    
     /**
     * @Route(path="/configuracion", name="home-configuracion", defaults={"_format"="html", "_locale": "es"})
     * @Secure(roles="ROLE_USER")
     */
    public function configuracionAction($_route) {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Home:configuracion.html.twig', array(
            "route" => $_route
        ));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/consultarTarifaBoleto.html", name="home-consultar-tarifa-boleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_USER")
     */
    public function consultarTarifaBoletoAction() {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:TopMenu:consultarTarifaBoleto.html.twig');
        $respuesta->setSharedMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes'));  //Cache del navegador
        return $respuesta;
    }
    
   /**
     * @Route(path="/consultarTarifaEncomienda.html", name="home-consultar-tarifa-encomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_USER")
     */
    public function consultarTarifaEncomiendaAction() {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:TopMenu:consultarTarifaEncomienda.html.twig');
        $respuesta->setSharedMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes'));  //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/perfilUsuario.html", name="home-perfil-usuario")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_USER")
     */
    public function perfilUsuario() {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Usuario:perfil.html.twig', array(
            "customUser" => $this->getUser()
        ));
        return $respuesta;
    }
    
    /**
     * @Route(path="/cambiarContrasenaUsuario.html", name="home-cambiar-contrasena")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_USER")
     */
    public function cambiarContrasenaUsuarioAction(Request $request, $_route) {
              
        $user = $this->getUser();
        if($user === null){
            return UtilService::returnError($this, "No se pudo obtener el usuario.");
        }
        
        $cambiarContrasenaUsuarioModel = new CambiarContrasenaUsuarioModel();
        $form = $this->createForm(new CambiarContrasenaUsuarioType($this->getDoctrine()), $cambiarContrasenaUsuarioModel);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $user = $this->getUser();
                    $user->setPlainPassword($cambiarContrasenaUsuarioModel->getContrasenaNueva());
                    $user->setDateLastUdate(new \DateTime());
                    $credentialsExpireAt = new \DateTime();
                    $daysCredentialsExpire = $this->container->getParameter("days_credentials_expire");
                    if(!$daysCredentialsExpire){ $daysCredentialsExpire = 90; }
                    $credentialsExpireAt->modify("+" . $daysCredentialsExpire . " day");
                    $user->setCredentialsExpireAt($credentialsExpireAt);
                    $this->get('fos_user.user_manager')->updateUser($user);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this);
                    
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
                    return UtilService::returnError($this);
                }
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Usuario:cambiarContrasena.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/cambiarEstacionUsuario.html", name="home-cambiar-estacion")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_USER")
     */
    public function cambiarEstacionUsuarioAction(Request $request, $_route) {
               
        $user = $this->getUser();
        if($user === null){
            return UtilService::returnError($this, "No se pudo obtener el usuario.");
        }
        
        $cambiarEstacionUsuarioModel = new CambiarEstacionUsuarioModel();
        $form = $this->createForm(new CambiarEstacionUsuarioType($this->getDoctrine()), $cambiarEstacionUsuarioModel, array(
            "user" => $user
        ));  
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $user = $this->getUser();
                    $user->setEstacion($cambiarEstacionUsuarioModel->getEstacion());
                    $erroresItems = $this->get('validator')->validate($user);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $this->get('fos_user.user_manager')->updateUser($user);
                    $em->flush();
                    $em->getConnection()->commit();
                    return UtilService::returnSuccess($this, array(
                        'data' => $user->getEstacion()
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
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Usuario:cambiarEstacion.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
}

?>
