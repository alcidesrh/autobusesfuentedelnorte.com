<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Entity\DepositoAgencia;
use Acme\TerminalOmnibusBundle\Entity\EstadoDeposito;
use Acme\TerminalOmnibusBundle\Form\Frontend\Agencia\RegistrarDepositoAgenciaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Agencia\AutorizarDepositoAgenciaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Agencia\RechazarDepositoAgenciaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Agencia\ConsultarSaldosType;
use Acme\TerminalOmnibusBundle\Entity\TipoEstacion;
use Acme\TerminalOmnibusBundle\Entity\TipoPagoEstacion;
use Acme\TerminalOmnibusBundle\Form\Model\ConsultarSaldosAgenciaModel;

/**
*   @Route(path="/agencia")
*/
class AgenciaController extends Controller {

    /**
     * @Route(path="/", name="deposito-agencia-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA, ROLE_SUPERVISOR_AGENCIA")
     */
    public function homeDepositoAgenciaAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:DepositoAgencia:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarDepositoAgencia.json", name="deposito-agencia-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA, ROLE_SUPERVISOR_AGENCIA")
    */
    public function listarDepositoAgenciaAction($_route) {
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
                $result = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:DepositoAgencia')
                        ->getDepositoAgenciaPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'fecha' => $item->getFecha()->format('d-m-Y'),
                        'estacion' => $item->getEstacion()->getNombre(),
                        'estado' => $item->getEstado()->getNombre(),
                        'importe' => $item->getMoneda()->getSigla() . " " . strval($item->getImporte()),
                        'numeroBoleta' => $item->getNumeroBoleta(),
                        'aplicaBono' => $item->getAplicaBono() === false ? "No" : "Si",
                        'observacion' => $item->getObservacion(),
                        'motivoRechazo' => $item->getMotivoRechazo(),
                        'fechaCreacion' => $item->getFechaCreacion()->format('d-m-Y'),
                        'usuarioCreacion' => $item->getUsuarioCreacion()->__toString()
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\Exception $exc) {
            var_dump($exc->getMessage());
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
     * @Route(path="/registrar.html", name="deposito-agencia-registrar")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA")
     */
    public function registrarDepositoAgenciaAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $depositoAgencia = new DepositoAgencia();
        $depositoAgencia->setMoneda("1"); //Seteando moneda default
        $form = $this->createForm(new RegistrarDepositoAgenciaType($this->getDoctrine()), $depositoAgencia, array(
            "user" => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $depositoAgencia->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoDeposito')->find(EstadoDeposito::REGISTRADO));
            $depositoAgencia->setFechaCreacion(new \DateTime());
            $depositoAgencia->setUsuarioCreacion($this->getUser());
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $estacion = $depositoAgencia->getEstacion();
                if($estacion === null){
                    return UtilService::returnError($this, "m1Debe seleccionar la agencia.");
                }else if($estacion->getTipo()->getId() !== TipoEstacion::AGENCIA){
                    return UtilService::returnError($this, "m1La estación " .$estacion->getAlias(). " no es una agencia.");
                }else if($estacion->getTipoPago()->getId() !== TipoPagoEstacion::PREPAGO){
                    return UtilService::returnError($this, "m1La estación " .$estacion->getAlias(). " no es una agencia de prepago.");
                }else if($estacion->getMonedaAgencia() === null){
                    return UtilService::returnError($this, "m1La agencia no tiene definida la moneda.");
                }
                $depositoAgencia->setMoneda($estacion->getMonedaAgencia());
            
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($depositoAgencia);
                    $em->flush();
                    $em->getConnection()->commit();
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0"
                    ));
                    
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
               $error = UtilService::getErrorsToForm($form);
               return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:DepositoAgencia:registrar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/acreditar.html", name="deposito-agencia-acreditar")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_AGENCIA")
     */
    public function acreditarDepositoAgenciaAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('autorizar_deposito_agencia_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id del depósito.");
                }
            }
        }        
        
        $depositoAgencia = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:DepositoAgencia')->find($id);
        if ($depositoAgencia === null) {
            return UtilService::returnError($this, "m1El depósito de agencia con id: " .$id." no existe.");
        }
        
        if($depositoAgencia->getEstado()->getId() !== EstadoDeposito::REGISTRADO){
            return UtilService::returnError($this, "m1Solamente se puede acreditar un depósito en estado registrado.");
        }
        
        $estacion = $depositoAgencia->getEstacion();
        if($estacion->getTipo()->getId() !== TipoEstacion::AGENCIA){
            return UtilService::returnError($this, "m1La estación " .$estacion->getAlias(). " no es una agencia.");
        }

        if($estacion->getTipoPago()->getId() !== TipoPagoEstacion::PREPAGO){
            return UtilService::returnError($this, "m1La estación " .$estacion->getAlias(). " no es una agencia de prepago.");
        }

        if($estacion->getMonedaAgencia() === null){
            return UtilService::returnError($this, "m1La agencia no tiene definida la moneda.");
        }else if($estacion->getMonedaAgencia()->getId() !== $depositoAgencia->getMoneda()->getId()){
            return UtilService::returnError($this, "m1La agencia tiene configurada una moneda diferente a la del depósito.");
        }
        
        $form = $this->createForm(new AutorizarDepositoAgenciaType($this->getDoctrine()), $depositoAgencia, array(
            "user" => $this->getUser()
        ));
        
        $saldo = abs($depositoAgencia->getImporte());
        $bono = 0;
        if($depositoAgencia->getAplicaBono() === true || $depositoAgencia->getAplicaBono() === 'true'){
            $bono = round($saldo * abs($estacion->getPorcientoBonificacion()) * 0.01, 2) ;
        }
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $depositoAgencia->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoDeposito')->find(EstadoDeposito::ACREDITADO));
                
                $estacion->setSaldo($estacion->getSaldo() + $saldo);
                if($depositoAgencia->getAplicaBono() === true || $depositoAgencia->getAplicaBono() === 'true'){
                    $depositoAgencia->setBono($bono);
                    $estacion->setBonificacion($estacion->getBonificacion() + $bono);
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($estacion);
                    $em->persist($depositoAgencia);
                    $em->flush();
                    $em->getConnection()->commit();
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0"
                    ));
                    
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
               $error = UtilService::getErrorsToForm($form);
               return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:DepositoAgencia:autorizar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor,
            'estacion' => $estacion,
            'moneda' => $depositoAgencia->getMoneda(),
            'saldo' => $saldo,
            'bono' => $bono
        ));
    }
    
    /**
     * @Route(path="/rechazar.html", name="deposito-agencia-rechazar")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_AGENCIA")
     */
    public function rechazarDepositoAgenciaAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('rechazar_deposito_agencia_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
                
                if (is_null($id)) {
                    return UtilService::returnError($this, "m1No se pudo obtener el id del depósito.");
                }
            }
        }
        
        $depositoAgencia = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:DepositoAgencia')->find($id);
        if ($depositoAgencia === null) {
            return UtilService::returnError($this, "m1El depósito de agencia con id: " .$id." no existe.");
        }
        
        if($depositoAgencia->getEstado()->getId() !== EstadoDeposito::REGISTRADO){
            return UtilService::returnError($this, "m1Solamente se puede rechazar un depósito en estado registrado.");
        }
        
        $form = $this->createForm(new RechazarDepositoAgenciaType($this->getDoctrine()), $depositoAgencia, array(
            "user" => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);            
            if ($form->isValid()) {
                
                $depositoAgencia->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoDeposito')->find(EstadoDeposito::RECHAZADO));
                            
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($depositoAgencia);
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
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:DepositoAgencia:rechazar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/consultarSaldoAgencia.html", name="deposito-agencia-consultar-saldo")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA, ROLE_SUPERVISOR_AGENCIA")
     */
    public function consultarSaldoAgenciaAction(Request $request, $_route) {
        
        $consultarSaldosAgenciaModel = new ConsultarSaldosAgenciaModel();
        $form = $this->createForm(new ConsultarSaldosType($this->getDoctrine()), $consultarSaldosAgenciaModel, array(
            "user" => $this->getUser()
        ));
        
        $estado = "";
        $saldo = 0;
        $bono = 0;
        $total = 0;
        $totalDepositado = 0;
        $estacion = $this->getUser()->getEstacion();
        if($estacion !== null && $estacion->getCheckAgenciaPrepago() === true){
            $estado = $estacion->getActivo() === true ? "Activo" : "Bloqueado";
            $saldo = doubleval($estacion->getSaldo());
            $bono = doubleval($estacion->getBonificacion());
            $total = $saldo + $bono;
            $totalDepositado = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:DepositoAgencia')->totalDepositado($estacion);
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:DepositoAgencia:consultarSaldoAgencia.html.twig', array(
            'estacion' => $this->getUser()->getEstacion(),
            'estado' => $estado,
            'saldo' => $saldo,
            'bono' => $bono,
            'total' => $total,
            'totalDepositado' => $totalDepositado,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
}

?>
