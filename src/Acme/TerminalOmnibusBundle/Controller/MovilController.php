<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\BackendBundle\Entity\MovilCode;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Exception\EncryptPasswordRuntimeException;
use Acme\TerminalOmnibusBundle\Exception\QRInvalidoRuntimeException;
use Acme\TerminalOmnibusBundle\Exception\InvalidIDRuntimeException;
use Acme\TerminalOmnibusBundle\Exception\FailValidationRuntimeException;
use Acme\TerminalOmnibusBundle\Exception\ServerRuntimeException;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\EstadoSalida;
use Acme\TerminalOmnibusBundle\Entity\EstadoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\TipoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\EncomiendaBitacora;
use Acme\TerminalOmnibusBundle\Form\Model\CambiarContrasenaUsuarioModel;
use Acme\TerminalOmnibusBundle\Entity\Encomienda;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;
use Symfony\Component\Validator\ConstraintViolationList;

/**
*   @Route(path="/movil/test/aux")
*/
class MovilController extends Controller {

     private $metadataFactory;
     
    /**
     * @Route(path="/listarMenuPrincipal.json", name="movil-listar-menu-principal")
     */
    public function listarMenuPrincipalAction(Request $request, $_route) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $status = $this->checkAccessMethod("listarMenuPrincipalInternalAction");
            if($status === MovilCode::SERVIDOR_SATISFACTORIO){
                $data = $this->listarMenuPrincipalInternalAction($request, $_route);
                $result["data"] = $data;
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
      * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function listarMenuPrincipalInternalAction(Request $request, $_route) {
        $menu = array();
        if($this->checkAccessMethod("chequearBoletoInternalAction") === MovilCode::SERVIDOR_SATISFACTORIO){
            $menu[] = array( 'id' => 'chequearBoleto', 'name' => 'Chequear Boleto');
        }
        if($this->checkAccessMethod("embarcarEncomiendaInternalAction") === MovilCode::SERVIDOR_SATISFACTORIO){
            $menu[] = array( 'id' => 'embarcarEncomienda', 'name' => 'Embarcar Encomienda');
        }
        if($this->checkAccessMethod("desembarcarEncomiendaInternalAction") === MovilCode::SERVIDOR_SATISFACTORIO){
            $menu[] = array( 'id' => 'desembarcarEncomienda', 'name' => 'Desembarcar Encomienda');
        }
        if($this->checkAccessMethod("listarEncomiendasDesembarcarInternalAction") === MovilCode::SERVIDOR_SATISFACTORIO){
            $menu[] = array( 'id' => 'listarEncomiendasDesembarcar', 'name' => 'Listar Encomiendas Desembarcar');
        }
        if($this->checkAccessMethod("consultarEncomiendaInternalAction") === MovilCode::SERVIDOR_SATISFACTORIO){
            $menu[] = array( 'id' => 'consultarEncomienda', 'name' => 'Consultar Encomienda');
        }
        return $menu;
    }
    
    /**
     * @Route(path="/getVersion.json", name="movil-version")
     */
    public function getVersionAction(Request $request, $_route) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $version = $this->container->getParameter("version_app_android");
            $items[] = array(
                'version_app' => $version
            );
            $result["data"] = $items;
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/getPerfilUsuario.json", name="movil-perfil-usuario")
     */
    public function getPerfilUsuarioAction(Request $request, $_route) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $user = $this->getUser();
            $items[] = array( 'info' => "Nombre: " . $user->getFullName() );
            $items[] = array( 'info' => "Usuario: " . $user->getUsername() );
            $items[] = array( 'info' => "Teléfono: " . $user->getPhone() );
            $items[] = array( 'info' => "Correo: " . $user->getEmail() );
            $items[] = array( 'info' => "Estación: " . ($user->getEstacion() === null ? "Todas" : $user->getEstacion()->__toString()));
            $result["data"] = $items;
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/listarSalidas.json", name="movil-listar-salidas")
     */
    public function listarSalidasAction(Request $request, $_route) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $status = $this->checkAccessMethod("listarSalidasInternalAction");
            if($status === MovilCode::SERVIDOR_SATISFACTORIO){
                $data = $this->listarSalidasInternalAction($request, $_route);
                $result["data"] = $data;
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function listarSalidasInternalAction(Request $request, $_route) {
        $items = array();
        $user = $this->getUser();
        $user->getEstacion();
        $salidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidasParaMovil($user);
        foreach ($salidas as $salida) {
            $items[] = array(
                'id' => $salida->getId(), 
                'fecha' => $salida->getFecha()->format('d-m-Y h:i A'),
                'ruta' =>  $salida->getItinerario()->getRuta()->getNombre()   
            );
        }
        return $items;
    }
    
    /**
     * @Route(path="/chequearBoleto.json", name="movil-chequear-boleto")
     */
    public function chequearBoletoAction(Request $request, $_route) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $status = $this->checkAccessMethod("chequearBoletoInternalAction");
            if($status === MovilCode::SERVIDOR_SATISFACTORIO){
                try {
                    $this->chequearBoletoInternalAction($request, $_route);
                } catch (EncryptPasswordRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (QRInvalidoRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (InvalidIDRuntimeException $ex){
                     $status = MovilCode::IDENTIFICADOR_BOLETO_NO_EXISTE;
                } catch (FailValidationRuntimeException $ex){
                     $status = MovilCode::VALIDACION_ERROR;
                     $result["message"] = $ex->getMessage(); 
                } catch (ServerRuntimeException $ex){
                     $status = MovilCode::SERVIDOR_ERROR;
                }
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_CHECK_BOLETO_MOVIL")
     */
    public function chequearBoletoInternalAction(Request $request, $_route) {
        
        $keyBoleto = $request->query->get('keyBoleto');
        if (is_null($keyBoleto)) {
            $keyBoleto = $request->request->get('keyBoleto');
        }
        
        if(is_null($keyBoleto)){
            throw new InvalidIDRuntimeException();
        }
            
        $idSalida = $request->query->get('salida');
        if (is_null($idSalida)) {
            $idSalida = $request->request->get('salida');
        }
        
        if(is_numeric($idSalida) === false){
            throw new FailValidationRuntimeException("El identificador de la salida es incorrecto.");
        }
        
        $idBoletoStr = null;
        if(ctype_digit($keyBoleto)){
            $data = substr($keyBoleto, 0, 7);
            $digitChequeo1 = substr($keyBoleto, -1); //Ultimo digito
            $digitChequeo2 = UtilService::getEANCheckDigit($data);
            if(intval($digitChequeo1) !== intval($digitChequeo2)){
                throw new InvalidIDRuntimeException();
            }
            $idBoletoStr = $data;
        }else if(UtilService::startsWith($keyBoleto, "BOL_")){ 
            $idBoletoStr = str_replace("BOL_", "", $keyBoleto);
        }else{
            $key = $this->container->getParameter("encrypt_password");
            if(!$key){
                throw new EncryptPasswordRuntimeException();
            }
            $text = urldecode($keyBoleto);
            try {
                $text = UtilService::decrypt($key, $text);
            } catch (\RuntimeException $ex) {
                throw new QRInvalidoRuntimeException();
            } catch (\Exception $ex){
                throw new QRInvalidoRuntimeException();
            }
            if(!UtilService::checkBitChequeo($text)){
                throw new QRInvalidoRuntimeException();
            }
            $text = UtilService::removeBitChequeo($text);
            if(strpos($text, "BOL_") === false){
                throw new QRInvalidoRuntimeException();
            }
            $idBoletoStr = str_replace("BOL_", "", $text);
        }
        
        if (is_null($idBoletoStr)) {
            throw new InvalidIDRuntimeException();
        }
        
        $idBoleto = 0;
        try {
            $idBoleto = intval($idBoletoStr);
        } catch (\Exception $ex) {
            throw new InvalidIDRuntimeException();
        }
        if($idBoleto === 0){
            throw new InvalidIDRuntimeException();
        }
        $boleto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($idBoleto);
        
//        NO SE ESTA TRABAJANDO POR LA FACTURA SINO POR EL ID DEL BOLETO
//        if(UtilService::startsWith($keyBoleto, "SFB_")){
//            $keyBoleto = str_replace("SFB_", "", $keyBoleto);
//            $keys = explode("_", $keyBoleto);
//            if(count($keys) !== 2){
//                throw new InvalidIDRuntimeException();
//            }
//            $serie = $keys[0];
//            $consecutivo = $keys[1];
//            if(is_numeric($consecutivo) === false){
//                throw new InvalidIDRuntimeException();
//            }
//            $boleto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getBoletoByFactura($serie, $consecutivo);
//        }
        
        if (is_null($boleto)) {
            throw new InvalidIDRuntimeException();
        }
        
        if(intval($boleto->getEstado()->getId()) !== intval(EstadoBoleto::EMITIDO)){
            $mensajeServidor = "Solamente se puede chequear un boleto que este en estado EMITIDO. El estado actual es: " . $boleto->getEstado()->getNombre() . "."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }
        
        $estacionUsuario = $this->getUser()->getEstacion();
        if($estacionUsuario !== null){
            if(intval($boleto->getEstacionOrigen()->getId()) !==  intval($estacionUsuario->getId())){
                $mensajeServidor = "El boleto solamente lo puede chequear un usuario de la estación: " . $boleto->getEstacionOrigen()->getNombre() . ".";                    
                throw new FailValidationRuntimeException($mensajeServidor);
            }
        }else{
            $estacionUsuario = $boleto->getEstacionOrigen();
        }
        
        $salida = $boleto->getSalida();
        if(intval($salida->getId()) !== intval($idSalida)){
            $fechaSalida =  $salida->getFecha()->format('d-m-Y h:i A');
            $ruta = $salida->getItinerario()->getRuta()->getNombre();
            $mensajeServidor = "Salida del boleto incorrecta. El boleto tiene salida para la fecha " . $fechaSalida . ", en la ruta: " . $ruta . "."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }

        $estadoSalida = $salida->getEstado();
        if(!($estadoSalida->getId() === EstadoSalida::ABORDANDO || $estadoSalida->getId() === EstadoSalida::INICIADA)){
            $mensajeServidor = "La salida debe estar en estado ABORDANDO o INICIADA. El estado actual es " . $estadoSalida->getNombre() . "."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }
        
        $boleto->setFechaActualizacion(new \DateTime());
        $boleto->setUsuarioActualizacion($this->getUser());
        $detalle = "";
        if($estadoSalida->getId() === EstadoSalida::INICIADA){
            //El chequeo se utiliza de forma directa para poner el estado transitando si la salida ya inicio.
            $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::TRANSITO));
            $detalle = "Chequeando boleto por el movil, quedo en estado Transito porque ya la salida habia iniciado.";
        }else{
            $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CHEQUEADO));
            $detalle = "Chequeando boleto por el movil.";
        }
        
        $boletoBitacora = new BoletoBitacora();
        $boletoBitacora->setEstado($boleto->getEstado());
        $boletoBitacora->setFecha(new \DateTime());
        $boletoBitacora->setUsuario($this->getUser());
        $boletoBitacora->setDescripcion($detalle);
        $boleto->addBitacoras($boletoBitacora);
        
        $errores = $this->get('validator')->validate($boleto);
        foreach ($errores as $errorItem) {
            throw new FailValidationRuntimeException($errorItem->getMessage());
        }
               
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {            
            $em->persist($boleto);
            $em->flush();
            $em->getConnection()->commit();
            return true;
        } catch (\RuntimeException $exc) {
            $em->getConnection()->rollback();
            throw new ServerRuntimeException($exc->getMessage());
        } catch (\Exception $exc) {
            $em->getConnection()->rollback();
            throw new ServerRuntimeException("Ha ocurrido un error en el sistema");
        }
    }
    
    /**
     * @Route(path="/embarcarEncomienda.json", name="movil-embarcar-encomienda")
     */
    public function embarcarEncomiendaAction(Request $request, $_route, $keyEncomiendaEncritado = true) {
        $result = array();
        $user = $this->getUser();
        if($user === null){
            $status = $this->validarUsuario($request);
        }else{
            $status = MovilCode::SERVIDOR_SATISFACTORIO;
        }
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $status = $this->checkAccessMethod("embarcarEncomiendaInternalAction");
            if($status === MovilCode::SERVIDOR_SATISFACTORIO){
                try {
                    $this->embarcarEncomiendaInternalAction($request, $_route, $keyEncomiendaEncritado);
                } catch (EncryptPasswordRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (QRInvalidoRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (InvalidIDRuntimeException $ex){
                     $status = MovilCode::IDENTIFICADOR_ENCOMIENDA_NO_EXISTE;
                } catch (FailValidationRuntimeException $ex){
                     $status = MovilCode::VALIDACION_ERROR;
                     $result["message"] = $ex->getMessage(); 
                } catch (ServerRuntimeException $ex){
                     $status = MovilCode::SERVIDOR_ERROR;
                }
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
      * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function embarcarEncomiendaInternalAction(Request $request, $_route, $keyEncomiendaEncritado) {
        
        $idSalida = $request->query->get('salida');
        if (is_null($idSalida)) {
            $idSalida = $request->request->get('salida');
        }
        
        if(is_numeric($idSalida) === false){
            throw new FailValidationRuntimeException("El identificador de la salida es incorrecto.");
        }        
        
        $idEncomienda = 0;
        if($keyEncomiendaEncritado === true){
            
            $keyEncomienda = $request->query->get('keyEncomienda');
            if (is_null($keyEncomienda)) {
                $keyEncomienda = $request->request->get('keyEncomienda');
            }

            $key = $this->container->getParameter("encrypt_password");
            if(!$key){
                throw new EncryptPasswordRuntimeException();
            }
            
            $text = urldecode($keyEncomienda);
            try {
                $text = UtilService::decrypt($key, $text);
            } catch (\RuntimeException $ex) {
                throw new QRInvalidoRuntimeException();
            } catch (\Exception $ex){
                throw new QRInvalidoRuntimeException();
            }

            if(!UtilService::checkBitChequeo($text)){
                throw new QRInvalidoRuntimeException();
            }

            $text = UtilService::removeBitChequeo($text);
            
            if(strpos($text, "ENCOMIENDA_") === false){
                throw new QRInvalidoRuntimeException();
            }

            $text = str_replace("ENCOMIENDA_", "", $text);
            
            try {
                $idEncomienda = intval($text);
            } catch (\Exception $ex) {
                throw new InvalidIDRuntimeException();
            }
        
        }else{
            $idEncomienda = $request->query->get('id');
            if (is_null($idEncomienda)) {
                $idEncomienda = $request->request->get('id');
            }
        }
        
        $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
        if (!$encomienda) {
            throw new InvalidIDRuntimeException();
        }
       
        $ultimoEstado = $encomienda->getUltimoEstado();
        if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
            $mensajeServidor = "Solamente se puede embarcar una encomienda que este en estado recibida o desembarcada. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }
       
        //NOTA: LAS ESTACIONES QUE PUEDEN REALIZAR ESTA ACCION ESTA VALIDADA A NIVEL DE BUSCADOR QUE FILTRA TENIENDO EN CUENTA LA ESTACION
        if($ultimoEstado->getId() ===  EstadoEncomienda::RECIBIDA ){
            if($encomienda->getEstacionCreacion() !== $this->getUser()->getEstacion()){
                $mensajeServidor = "Solamente puede embarcar la encomienda un usuario de la estación: " . $encomienda->getEstacionCreacion()->__toString() . "."; 
                throw new FailValidationRuntimeException($mensajeServidor);
            }
        }
        
        if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
            $mensajeServidor = "Las encomiendas de efectivo solamente se pueden entregar."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }
        
        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida); 
        if (!$salida) {
            throw new FailValidationRuntimeException("La salida no existe en el sistema.");
        }
        
        $rutaSalida = $salida->getItinerario()->getRuta()->getCodigo();
        $secuenciaRutas = array();
        $rutaValida = false;
        foreach ($encomienda->getRutasIntermedias() as $ruta) {
            if($ruta->getCodigo() === $rutaSalida){
                $rutaValida = true;
            }else{
                $secuenciaRutas[] = $ruta->__toString();
            }
        }
        if($rutaValida === false){
            $mensajeServidor = "Ruta de la encomienda incorrecta. La encomienda solamente se puede embarcar en las rutas: " . implode(",", $secuenciaRutas) . "."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }
        
        if($encomienda->getEstacionCreacion() !== null){
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                $mensajeServidor = "Para embarcar una encomienda el usuario debe estar asociado a la estación de origen de la encomienda: " . $encomienda->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias del envío."; 
                throw new FailValidationRuntimeException($mensajeServidor);
            }
            else if($encomienda->getEstacionOrigen()->getId() ===  $estacionUsuario->getId()){
                    /* Si el usuario pertenece a la estacion de origen ya todo esta ok. sino se revisa en las intermedias de la ruta */
            }else{
                $estacionesIntermediasYFinal = $encomienda->getEstacionesIntermediasYFinal();
                $encontrada = false;
                foreach ($estacionesIntermediasYFinal as $estacion) {
                    if($estacion->getId() ===  $estacionUsuario->getId()){
                        $encontrada = true;
                        break;
                    }
                 }
                 if($encontrada === false){
                    $mensajeServidor = "La encomienda solamente la puede embarcar un usuario de la estación:" . $encomienda->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias del envío.";
                    throw new FailValidationRuntimeException($mensajeServidor);
                 }
            }
        }
        
        $encomiendaBitacora = new EncomiendaBitacora();
        $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
        $encomiendaBitacora->setUsuario($this->getUser());
        $encomiendaBitacora->setFecha(new \DateTime());
        $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::EMBARCADA));
        $encomiendaBitacora->setSalida($salida);
        $encomienda->addEventos($encomiendaBitacora);
        $errores = $this->get('validator')->validate($encomiendaBitacora);
        foreach ($errores as $errorItem) {
            throw new FailValidationRuntimeException($errorItem->getMessage());
        }
        
        if($salida->getEstado()->getId() === EstadoSalida::INICIADA){
            $encomiendaBitacora = new EncomiendaBitacora();
            $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
            $encomiendaBitacora->setUsuario($this->getUser());
            $encomiendaBitacora->setFecha(new \DateTime());
            $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::TRANSITO));
            $encomienda->addEventos($encomiendaBitacora);
            $errores = $this->get('validator')->validate($encomiendaBitacora);
            foreach ($errores as $errorItem) {
                throw new FailValidationRuntimeException($errorItem->getMessage());
            }
        }
        
        $errores = $this->get('validator')->validate($encomienda);
        foreach ($errores as $errorItem) {
            throw new FailValidationRuntimeException($errorItem->getMessage());
        }
               
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {            
            $em->persist($encomienda);
            $em->flush();
            $em->getConnection()->commit();
            return true;
        } catch (\RuntimeException $exc) {
            $em->getConnection()->rollback();
            throw new ServerRuntimeException($exc->getMessage());
        } catch (\Exception $exc) {
            $em->getConnection()->rollback();
            throw new ServerRuntimeException("Ha ocurrido un error en el sistema");
        }
    }
    
    /**
     * @Route(path="/desembarcarEncomienda.json", name="movil-desembarcar-encomienda")
     */
    public function desembarcarEncomiendaAction(Request $request, $_route, $keyEncomiendaEncritado = true) {
        $result = array();
        $user = $this->getUser();
        if($user === null){
            $status = $this->validarUsuario($request);
        }else{
            $status = MovilCode::SERVIDOR_SATISFACTORIO;
        }
        
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $status = $this->checkAccessMethod("desembarcarEncomiendaInternalAction");
            if($status === MovilCode::SERVIDOR_SATISFACTORIO){
                try {
                    $this->desembarcarEncomiendaInternalAction($request, $_route, $keyEncomiendaEncritado);
                } catch (EncryptPasswordRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (QRInvalidoRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (InvalidIDRuntimeException $ex){
                     $status = MovilCode::IDENTIFICADOR_ENCOMIENDA_NO_EXISTE;
                } catch (FailValidationRuntimeException $ex){
                     $status = MovilCode::VALIDACION_ERROR;
                     $result["message"] = $ex->getMessage(); 
                } catch (ServerRuntimeException $ex){
                     $status = MovilCode::SERVIDOR_ERROR;
                }
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
      * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function desembarcarEncomiendaInternalAction(Request $request, $_route, $keyEncomiendaEncritado) {
        
        $idEncomienda = 0;
        if($keyEncomiendaEncritado === true){
            $keyEncomienda = $request->query->get('keyEncomienda');
             if (is_null($keyEncomienda)) {
                 $keyEncomienda = $request->request->get('keyEncomienda');
             }

             $key = $this->container->getParameter("encrypt_password");
             if(!$key){
                 throw new EncryptPasswordRuntimeException();
             }

             $text = urldecode($keyEncomienda);
             try {
                 $text = UtilService::decrypt($key, $text);
             } catch (\RuntimeException $ex) {
                 throw new QRInvalidoRuntimeException();
             } catch (\Exception $ex){
                 throw new QRInvalidoRuntimeException();
             }

             if(!UtilService::checkBitChequeo($text)){
                 throw new QRInvalidoRuntimeException();
             }

             $text = UtilService::removeBitChequeo($text);

             if(strpos($text, "ENCOMIENDA_") === false){
                 throw new QRInvalidoRuntimeException();
             }

             $text = str_replace("ENCOMIENDA_", "", $text);

             try {
                 $idEncomienda = intval($text);
             } catch (\Exception $ex) {
                 throw new InvalidIDRuntimeException();
             }            
        }else{
            $idEncomienda = $request->query->get('id');
            if (is_null($idEncomienda)) {
                $idEncomienda = $request->request->get('id');
            }
        }
         
        $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
        if (!$encomienda) {
            throw new InvalidIDRuntimeException();
        }
        
        $ultimoEstado = $encomienda->getUltimoEstado();
        if($ultimoEstado->getId() !== EstadoEncomienda::EMBARCADA && $ultimoEstado->getId() !== EstadoEncomienda::TRANSITO){
            $mensajeServidor = "Solamente se puede desembarcar una encomienda que este en estado embarcada o en transito. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }

        if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
            $mensajeServidor = "Las encomiendas de efectivo solamente se pueden entregar."; 
            throw new FailValidationRuntimeException($mensajeServidor);
        }
        
        if($encomienda->getEstacionCreacion() !== null){
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                $mensajeServidor = "Para desembarcar una encomienda el usuario debe estar asociado a una estación."; 
                throw new FailValidationRuntimeException($mensajeServidor);
            }
            else if($encomienda->getEstacionOrigen()->getId() ===  $estacionUsuario->getId() || $encomienda->getEstacionDestino()->getId() ===  $estacionUsuario->getId()){
                    /* Si el usuario pertenece a la estacion de origen ya todo esta ok. sino se revisa en las intermedias de la ruta 
                     * Si el usuario pertenece a la ultima estacion de la rura todo esta ok. (Esta se adiciona pq no esta contemplada dentro de las estaciones intermedias)
                     */
            }else{
                $estacionesIntermediasYFinal = $encomienda->getEstacionesIntermediasYFinal();
                $encontrada = false;
                foreach ($estacionesIntermediasYFinal as $estacion) {
                    if($estacion->getId() ===  $estacionUsuario->getId()){
                        $encontrada = true;
                        break;
                    }
                 }
                 if($encontrada === false){
                    $mensajeServidor = "La encomienda solamente la puede desembarcar un usuario de la estación:" . $encomienda->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias del envío.";
                    throw new FailValidationRuntimeException($mensajeServidor);
                 }
            }
        }
        
        $encomiendaBitacora = new EncomiendaBitacora();
        $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
        $encomiendaBitacora->setUsuario($this->getUser());
        $encomiendaBitacora->setFecha(new \DateTime());
        $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::DESEMBARCADA));
        $encomienda->addEventos($encomiendaBitacora);
        $errores = $this->get('validator')->validate($encomiendaBitacora);
        foreach ($errores as $errorItem) {
            throw new FailValidationRuntimeException($errorItem->getMessage());
        }

        $errores = $this->get('validator')->validate($encomienda);
        foreach ($errores as $errorItem) {
            throw new FailValidationRuntimeException($errorItem->getMessage());
        }
               
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {            
            $em->persist($encomienda);
            $em->flush();
            $em->getConnection()->commit();
            return true;
        } catch (\RuntimeException $exc) {
            $em->getConnection()->rollback();
            throw new ServerRuntimeException($exc->getMessage());
        } catch (\Exception $exc) {
            $em->getConnection()->rollback();
            throw new ServerRuntimeException("Ha ocurrido un error en el sistema");
        }
    }
    
    /**
     * @Route(path="/listarEncomiendasDesembarcar.json", name="movil-listar-desembarcar-encomienda")
     */
    public function listarEncomiendasDesembarcarAction(Request $request, $_route) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $status = $this->checkAccessMethod("listarEncomiendasDesembarcarInternalAction");
            if($status === MovilCode::SERVIDOR_SATISFACTORIO){
                try {
                    $data = $this->listarEncomiendasDesembarcarInternalAction($request, $_route);
                    $result["data"] = $data;
                }catch (FailValidationRuntimeException $ex){
                     $status = MovilCode::VALIDACION_ERROR;
                     $result["message"] = $ex->getMessage(); 
                } catch (ServerRuntimeException $ex){
                     $status = MovilCode::SERVIDOR_ERROR;
                }
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
      * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function listarEncomiendasDesembarcarInternalAction(Request $request, $_route) {
        
        $idSalida = $request->query->get('salida');
        if (is_null($idSalida)) {
            $idSalida = $request->request->get('salida');
        }
        
        if(is_numeric($idSalida) === false){
            throw new FailValidationRuntimeException("El identificador de la salida es incorrecto.");
        }
        
        $items = array();
        $estacionDestino = $this->getUser()->getEstacion();
        $encomiendas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasDesembarcarForMovil($idSalida, $estacionDestino);
        foreach ($encomiendas as $encomienda) {
            $estaciones = $encomienda->getEstacionOrigen()->__toString();
            foreach ($encomienda->getRutas() as $ruta) {
                $estaciones .= " / " . $ruta->getEstacionDestino()->__toString();
            }
            $items[] = array(
                'id' => $encomienda->getId(), 
                'idPadre' => "",
                'tipoEncomienda' =>  $encomienda->getTipoEncomienda()->getNombre(),
                'descripcion' =>  $encomienda->getDescripcion(),
                'clienteRemitente' =>  $encomienda->getClienteRemitente()->__toString(),
                'clienteDestinatario' =>  $encomienda->getClienteDestinatario()->__toString(),
                'estaciones' => $estaciones
            );
        }
        return $items;
        
    }
    
    /**
     * @Route(path="/consultarEncomienda.json", name="movil-consultar-encomienda")
     */
    public function consultarEncomiendaAction(Request $request, $_route) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $status = $this->checkAccessMethod("consultarEncomiendaInternalAction");
            if($status === MovilCode::SERVIDOR_SATISFACTORIO){
                try {
                    $data = $this->consultarEncomiendaInternalAction($request, $_route);
                    $result["data"] = $data;
                } catch (EncryptPasswordRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (QRInvalidoRuntimeException $ex){
                     $status = MovilCode::CODIGO_BARRA_FALSO;
                } catch (InvalidIDRuntimeException $ex){
                     $status = MovilCode::IDENTIFICADOR_ENCOMIENDA_NO_EXISTE;
                } catch (FailValidationRuntimeException $ex){
                     $status = MovilCode::VALIDACION_ERROR;
                     $result["message"] = $ex->getMessage(); 
                } catch (ServerRuntimeException $ex){
                     $status = MovilCode::SERVIDOR_ERROR;
                }
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
      * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function consultarEncomiendaInternalAction(Request $request, $_route) {
        $keyEncomienda = $request->query->get('keyEncomienda');
        if (is_null($keyEncomienda)) {
            $keyEncomienda = $request->request->get('keyEncomienda');
        }
   
        $key = $this->container->getParameter("encrypt_password");
        if(!$key){
            throw new EncryptPasswordRuntimeException();
        }
        
        $text = urldecode($keyEncomienda);
        try {
            $text = UtilService::decrypt($key, $text);
        } catch (\RuntimeException $ex) {
            throw new QRInvalidoRuntimeException();
        } catch (\Exception $ex){
            throw new QRInvalidoRuntimeException();
        }
        
        if(!UtilService::checkBitChequeo($text)){
            throw new QRInvalidoRuntimeException();
        }
        
        $text = UtilService::removeBitChequeo($text);
        
        if(strpos($text, "ENCOMIENDA_") === false){
            throw new QRInvalidoRuntimeException();
        }
        
        $text = str_replace("ENCOMIENDA_", "", $text);
        
        $idEncomienda = 0;
        try {
            $idEncomienda = intval($text);
        } catch (\Exception $ex) {
            throw new InvalidIDRuntimeException();
        }
        
        $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
        if (!$encomienda) {
            throw new InvalidIDRuntimeException();
        }
        
        
        $data = array();
        if($encomienda instanceof Encomienda){
            $data["id"] = $encomienda->getId();
            $data["idGrupo"] = "";
            $data["cantidad"] = $encomienda->getCantidad();
            $data["tipoEncomienda"] = $encomienda->getTipoEncomienda()->getNombre();
            $data["tipoEncomiendaEspecial"] = ($encomienda->getTipoEncomiendaEspecial() !== null) ? $encomienda->getTipoEncomiendaEspecial()->getNombre() : "No Definido";
            $data["peso"] = ($encomienda->getPeso() !== null) ? $encomienda->getPeso() . " Kg " : "No Definido";
            $data["alto"] = ($encomienda->getAlto() !== null) ? $encomienda->getAlto() . " cm " : "No Definido";
            $data["ancho"] = ($encomienda->getAncho() !== null) ? $encomienda->getAncho() . " cm " : "No Definido";
            $data["profundidad"] = ($encomienda->getProfundidad() !== null) ? $encomienda->getProfundidad() . " cm " : "No Definido";
            $data["volumen"] = ($encomienda->getVolumen() !== null) ? $encomienda->getVolumen() . " cm3 " : "No Definido";
            $data["distacia"] = "";
            $data["descripcion"] = $encomienda->getDescripcion();
            $data["clienteRemitente"] = $encomienda->getClienteRemitente()->getNombre();
            $data["clienteDestinatario"] = $encomienda->getClienteDestinatario()->getNombre();
            $data["tipoDocumento"] = $encomienda->getTipoDocumento()->getNombre();
            
            $data["precioCalculadoMonedaBase"] = ($encomienda->getPrecioCalculadoMonedaBase() !== null) ? ("GTQ " . $encomienda->getPrecioCalculadoMonedaBase()) : "No Definido";
            $data["precioCalculado"] = ($encomienda->getMoneda() !== null) ?  ($encomienda->getMoneda()->getSigla() . " " . $encomienda->getPrecioCalculado()) : "No Definido";
            
            $data["facturaGenerada"] = ($encomienda->getFacturaGenerada() !== null) ? $encomienda->getFacturaGenerada()->getInfo1() : "No Definido";
            
            $data["autorizacionCortesia"] = ($encomienda->getAutorizacionCortesia() !== null) ? $encomienda->getAutorizacionCortesia()->getId() : "No Definido";
            $data["autorizacionCortesiaUsuario"] = ($encomienda->getAutorizacionCortesia() !== null) ? $encomienda->getAutorizacionCortesia()->getUsuarioCreacion()->getFullName() : "No Definido";
            
            $data["autorizacionInterna"] = ($encomienda->getAutorizacionInterna() !== null) ? $encomienda->getAutorizacionInterna()->getId() : "No Definido";
            $data["autorizacionInternaUsuario"] = ($encomienda->getAutorizacionInterna() !== null) ? $encomienda->getAutorizacionInterna()->getUsuarioCreacion()->getFullName() : "No Definido";
            
            $data["observacion"] = ($encomienda->getObservacion() !== null) ?  $encomienda->getObservacion() : "No Definido";
            $data["boleto"] = ($encomienda->getBoleto() !== null) ? $encomienda->getBoleto()->getId() : "No Definido";
            $data["empresa"] = ($encomienda->getEmpresa() !== null) ? $encomienda->getEmpresa()->getNombre() : "No Definido";
            $data["fechaCreacion"] = $encomienda->getFechaCreacion()->format('d-m-Y H:i:s');
            $data["usuarioCreacion"] = $encomienda->getUsuarioCreacion()->getFullName();
            $data["estaciones"] = $encomienda->getEstacionesStr();
            
            $bitacora = array();
            foreach ($encomienda->getEventos() as $item) {
                if($item instanceof EncomiendaBitacora){
                    $bitacora[] = array(
                        "fecha" => $item->getFecha()->format('d-m-Y h:i A'),
                        "estado" => $item->getEstado()->getNombre(),
                        "estación" => $item->getEstacion()->__toString(),
                        "usuario" => $item->getUsuario()->getFullName(),
                        "salida" => ($item->getSalida() !== null) ? $item->getSalida()->getId() : ""
                    );  
                }
            }
            
            $data["bitacora"] = $bitacora;
        }
        
        $items = array();
        $items[] = $data;
        return $items;
    }
    
    private function checkAccessMethod($method) {
        try {
            $metadata = $this->getMetadataFactory()->getMetadataForClass(get_class());   
            if (!isset($metadata->methodMetadata[$method])) {
                return MovilCode::CREDENCIALES_MAL;
            }
            foreach ($metadata->methodMetadata[$method]->roles as $role) {
                if ($this->get('security.context')->isGranted($role)) {
                    return MovilCode::SERVIDOR_SATISFACTORIO;
                }
            }
            return MovilCode::CREDENCIALES_MAL;
        } catch (\AccessDeniedException $ex) {
            return MovilCode::CREDENCIALES_MAL;
        }
    }
    
    private function validarUsuario(Request $request) {
        $status = MovilCode::CREDENCIALES_MAL;
        $username = $request->query->get('username');
        if (is_null($username)) {
            $username = $request->request->get('username');
        }
        $password = $request->query->get('password');
        if (is_null($password)) {
            $password = $request->request->get('password');
        }
        $context = array('code' => 'SEC005' );
        $this->container->get("logger")->notice('Intento de autenticación en el webservice, username: ' . $username . ", password: ". $password . ".", $context);
        try {
            if($username !== null && trim($username) !== "" && $password !== null && trim($password) !== ""){
                $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);
                if($user !== null){
                    if($user->isEnabled() === true && $user->isAccountNonLocked() === true &&  $user->isAccountNonExpired() && $user->isCredentialsNonExpired() === true ){
                        if($user->getAccessAppMovil() === true){
                            $ipRanges = $user->getIpRanges();
                            if(UtilService::isValidIpRequestOfUser($ipRanges, $request->getClientIp()) === true){
                                if($user->getPassword() !== ""){
                                    $encoderFactory = $this->get("security.encoder_factory");
                                    if ($encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                                        $em = $this->getDoctrine()->getManager();
                                        $em->getConnection()->beginTransaction();
                                        try {
                                            $user->setLastLogin(new \DateTime());
                                            $user->clearIntentosFallidos();
                                            $this->get('fos_user.user_manager')->updateUser($user);
                                            $em->flush();
                                            $em->getConnection()->commit();
                                            $usernamePasswordToken = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                                            $this->get('security.context')->setToken($usernamePasswordToken);
                                            $this->container->get("logger")->warn('Autenticado satisfactoriamente el usuario:'. $username . " en el webservice.", $context);
                                            $status = MovilCode::SERVIDOR_SATISFACTORIO;
                                        } catch (\RuntimeException $exc) {
                                            $em->getConnection()->rollback();
                                            $mensaje = 'Autenticado satisfactoriamente pero ocurrio un error actualizando el usuario: ' . $username . ".";
                                            $this->container->get("logger")->warn($mensaje, $context);
                                        } catch (\Exception $exc) {
                                            $em->getConnection()->rollback();
                                            $mensaje = 'Autenticado satisfactoriamente pero ocurrio un error actualizando el usuario: ' . $username . ".";
                                            $this->container->get("logger")->warn($mensaje, $context);
                                        }
                                    }else{
                                        $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). Contraseña no válida.';
                                        $this->container->get("logger")->warn($mensaje, $context);
                                    }
                                }else{
                                    $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). El usuario no tiene definido contraseña.';
                                    $this->container->get("logger")->warn($mensaje, $context);
                                }
                            }else{
                                $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). El usuario se intento conectar desde una ip no autorizada, ip: ' . $request->getClientIp() . ".";
                                $this->container->get("logger")->warn($mensaje, $context);
                            }
                            
                        }else{
                            $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). El usuario no está autorizado a acceder a la app movil.';
                            $this->container->get("logger")->warn($mensaje, $context);
                        }
                        
                    }else{
                        $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). El usuario está bloqueado o deshabilitado.';
                        $this->container->get("logger")->warn($mensaje, $context);
                    }
                    
                    if($status === MovilCode::CREDENCIALES_MAL){
                        $em = $this->getDoctrine()->getManager();
                        $em->getConnection()->beginTransaction();
                        try {
                            $user->addIntentosFallidos();
                            $this->get('fos_user.user_manager')->updateUser($user);
                            $em->flush();
                            $em->getConnection()->commit();
                         } catch (\RuntimeException $exc) {
                            $em->getConnection()->rollback();
                            $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). Ha ocurrido un error actualizando el usuario.';
                            $this->container->get("logger")->warn($mensaje, $context);
                         } catch (\Exception $exc) {
                            $em->getConnection()->rollback();
                            $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). Ha ocurrido un error actualizando el usuario.';
                            $this->container->get("logger")->warn($mensaje, $context);
                         }
                    }
                    
                }else{
                    $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). El username no existe.';
                    $this->container->get("logger")->warn($mensaje, $context);
                }
            }else{
                 $mensaje = 'Falló el intento de autenticación al webservice del usuario (' . $username . ') con la contraseña (' . $password . '). El username o el password esta en blanco.';
                 $this->container->get("logger")->warn($mensaje, $context);
            }
            
        } catch (\Exception $ex) {
             var_dump($ex);
             $this->get("logger")->error($ex->getMessage(), $context);
        }
        
        return $status;
    }
    
    public function getMetadataFactory() {
        if($this->metadataFactory === null){ $this->metadataFactory = $this->container->get('security.extra.metadata_factory'); }
        return $this->metadataFactory;
    }
    
    /**
     * @Route(path="/login.json", name="movil-check-user")
     */
    public function loginAction(Request $request) {
        $status = $this->validarUsuario($request);
        $response = new JsonResponse();
        $response->setData(array('status' => $status));
        return $response;
    }
    
    /**
     * @Route(path="/logout.json", name="movil-logout-user")
     */
    public function logoutAction(Request $request) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $context = array('code' => 'SEC005' );
            $securityContext = $this->get('security.context');
            $this->container->get("logger")->warn('El usuario: ' . $securityContext->getToken()->getUser()->getUsername() . ' salió del sistema.', $context);
            $securityContext->setToken(null); 
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/changePassword.json", name="movil-changePassword-user")
     */
    public function changePasswordAction(Request $request) {
        $result = array();
        $status = $this->validarUsuario($request);
        if($status === MovilCode::SERVIDOR_SATISFACTORIO){
            $passwordActual = $request->query->get('password');
            if (is_null($passwordActual)) {
                $passwordActual = $request->request->get('password');
            }
            $passwordNew = $request->query->get('newpassword');
            if (is_null($passwordNew)) {
                $passwordNew = $request->request->get('newpassword');
            }
            $cambiarContrasenaUsuarioModel = new CambiarContrasenaUsuarioModel();
            $cambiarContrasenaUsuarioModel->setContrasenaAnterior($passwordActual);
            $cambiarContrasenaUsuarioModel->setContrasenaNueva($passwordNew);
            $mensajeServidor = "";
            $erroresAux = $this->container->get('validator')->validate($cambiarContrasenaUsuarioModel);
            if (count($erroresAux) === 0) {
                $em = $this->container->get('doctrine')->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $user = $this->get('security.context')->getToken()->getUser();
                    $user->setPlainPassword($cambiarContrasenaUsuarioModel->getContrasenaNueva());
                    $user->setDateLastUdate(new \DateTime());
                    $credentialsExpireAt = new \DateTime();
                    $daysCredentialsExpire = $this->container->getParameter("days_credentials_expire");
                    if(!$daysCredentialsExpire){ $daysCredentialsExpire = 90; }
                    $credentialsExpireAt->modify("+" . $daysCredentialsExpire . " day");
                    $user->setCredentialsExpireAt($credentialsExpireAt);
                    $this->container->get('fos_user.user_manager')->updateUser($user);
                    $em->flush();
                    $em->getConnection()->commit();
                    $status = MovilCode::SERVIDOR_SATISFACTORIO;
                } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $status = MovilCode::SERVIDOR_ERROR;
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $status = MovilCode::SERVIDOR_ERROR;
                }     
            }else{
              foreach ($erroresAux as $item) {
                $status = MovilCode::VALIDACION_ERROR;
                $result["message"] = $item->getMessage();
                break;
              }
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
}

?>
