<?php
namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Entity\CorteVentaTalonario;
use Acme\TerminalOmnibusBundle\Entity\EstadoCorteVentaTalonario;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\AdicionarCorteVentaTalonarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\IniciarRevisionCorteVentaTalonarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\FinalizarRevisionCorteVentaTalonarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\ActualizarRevisionCorteVentaTalonarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\AjustarCorteVentaTalonarioType;
use Acme\TerminalOmnibusBundle\Entity\CorteVentaTalonarioItem;
use Acme\TerminalOmnibusBundle\Entity\EstadoTarjeta;
use Acme\TerminalOmnibusBundle\Entity\TarjetaBitacora;
use Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta\AnularCorteVentaTalonarioType;

/**
*   @Route(path="/corteVentaTalonario")
*/
class CorteVentaTalonarioController extends Controller {

    /**
     * @Route(path="/", name="corteVentaTalonario-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_REVISOR")
     */
    public function homeCorteVentaTalonarioAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarCorteVentaTalonario.json", name="corteVentaTalonario-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_REVISOR")
    */
    public function listarCorteVentaTalonarioAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario');
                $result = $repository->getCorteVentaTalonarioPaginadas($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $talonario = $item->getTalonario();
                    $tarjeta = $talonario->getTarjeta();
                    $row = array(
                        'id' => $item->getId(),
                        'tarjeta' => $tarjeta->getAlias(),
                        'fecha' => $item->getFecha() !== null ? $item->getFecha()->format('d-m-Y') : "",
                        'inicio' => $item->getInicial(),
                        'fin' => $item->getFinal(),
                        'cantidad' => (($item->getFinal() - $item->getInicial()) + 1),
                        'estado' => $item->getEstado()->getNombre()
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\ErrorException $exc) {
            var_dump($exc->getMessage());
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
     * @Route(path="/adicionarCorteVenta.html", name="corteventa-crear-case1")
     * @Route(path="/adicionarCorteVenta/", name="corteventa-crear-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_INSPECTOR_BOLETO, ROLE_REVISOR")
     */
    public function adicionarCorteVentaAction(Request $request, $_route) {
        
        $corteVentaTalonario = new CorteVentaTalonario($this->getUser());
        $corteVentaTalonario->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCorteVentaTalonario')->find(EstadoCorteVentaTalonario::REGISTRADO));
        $form = $this->createForm(new AdicionarCorteVentaTalonarioType($this->getDoctrine()), $corteVentaTalonario, array(
            'em' => $this->getDoctrine()->getManager(),
            'user' => $this->getUser()
        ));  
        
        if ($request->isMethod('POST')) {
            
            $command = $request->get("adicionar_corte_venta_talonario_command");
            $idTalonario = $command["talonario"];
            if($idTalonario === null || trim($idTalonario) === ""){
                return UtilService::returnError($this, "Debe seleccionar un talonario");
            }
            $talonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Talonario')->find($idTalonario);
            if($talonario === null){
                return UtilService::returnError($this, "No se encontro un talonario con el ID: " . $idTalonario);
            }   
            $talonario->addListaCorteVentaTalonario($corteVentaTalonario);
                
            if($talonario->getTarjeta()->getEstado()->getId() !== EstadoTarjeta::CREADO){
                return UtilService::returnError($this, "Solamente se puede adicionar un corte de venta a una tarjeta en estado 'Creada'.");
            }
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $tarjeta = $corteVentaTalonario->getTalonario()->getTarjeta();
                
                if($this->getUser()->hasRole("ROLE_INSPECTOR_BOLETO")){
                    $salida = $tarjeta->getSalida();
                    $corteVentaTalonario->setFecha($salida->getFecha());
                }
                
                $erroresItems = $this->get('validator')->validate($corteVentaTalonario);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                
                $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                $tarjetaBitacora->setDescripcion("Adicionando corte de venta. "
                        . " Del " . $corteVentaTalonario->getInicial() . " al " . $corteVentaTalonario->getFinal() . "." 
                        . " Fecha: " . $corteVentaTalonario->getFecha()->format('d-m-Y') . "." 
                        . " Inspector: " . $corteVentaTalonario->getInspector()->getFullname() . ".");
                $tarjeta->addBitacoras($tarjetaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($corteVentaTalonario);
                    $em->persist($talonario);
                    $em->persist($tarjeta);
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
        
        return $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:adicionarCorteVenta.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/consultar/{id}", name="corte-venta-consultar-case1")
     * @Route(path="/consultar.html", name="corte-venta-consultar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function consultarAction(Request $request, $id = null, $_route) {
        
        if (is_null($id)) {
           $id = $request->query->get('id');
            if (is_null($id)) {
                $id = $request->request->get('id');
            } 
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del corte de venta.");
        }
        
        $corteVentaTalonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario')->find($id); 
        if ($corteVentaTalonario === null) {
            return UtilService::returnError($this, "El corte de venta con id: ".$id." no existe.");
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:consultar.html.twig', array(
            'entity' => $corteVentaTalonario,
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/iniciarRevision.html", name="corte-venta-iniciar-revision-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_REVISOR")
     */
    public function iniciarRevisionAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('iniciar_revision_corte_venta_talonario_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del corte de venta.");
        }
        
        $corteVentaTalonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario')->find($id); 
        if ($corteVentaTalonario === null) {
            return UtilService::returnError($this, "El corte de venta con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new IniciarRevisionCorteVentaTalonarioType(), $corteVentaTalonario, array(
            'em' => $this->getDoctrine()->getManager(),
        ));
        
        if($corteVentaTalonario->getEstado()->getId() !== EstadoCorteVentaTalonario::REGISTRADO){
            return UtilService::returnError($this, "Solamente se puede iniciar un corte de venta cuando este en estado registrado.");
        }
        
        if ($request->isMethod('POST')) {
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $corteVentaTalonario->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCorteVentaTalonario')->find(EstadoCorteVentaTalonario::INICIADO));
                
                $inicial = $corteVentaTalonario->getInicial();
                $final = $corteVentaTalonario->getFinal();
                for ($index = $inicial; $index <= $final; $index++) {
                    $corteVentaTalonarioItem = new CorteVentaTalonarioItem($this->getUser());
                    $corteVentaTalonarioItem->setNumero($index);
                    $corteVentaTalonarioItem->setImporte(0);
                    $corteVentaTalonario->addListaItem($corteVentaTalonarioItem);
                }
                
                $erroresItems = $this->get('validator')->validate($corteVentaTalonario);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                $tarjeta = $corteVentaTalonario->getTalonario()->getTarjeta();
                
                $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                $tarjetaBitacora->setDescripcion("Iniciada la revision del corte de venta ID: " . $corteVentaTalonario->getId() . ". Del " . $corteVentaTalonario->getInicial() . " al " . $corteVentaTalonario->getFinal() . ".");
                $tarjeta->addBitacoras($tarjetaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($corteVentaTalonario);
                    $em->persist($tarjeta);
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
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:iniciar.html.twig', array(
            'entity' => $corteVentaTalonario,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/anular.html", name="corte-venta-anular-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_REVISOR")
     */
    public function anularCorteVenta(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('anular_corte_venta_talonario_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del corte de venta.");
        }
        
        $corteVentaTalonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario')->find($id); 
        if ($corteVentaTalonario === null) {
            return UtilService::returnError($this, "El corte de venta con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new AnularCorteVentaTalonarioType(), $corteVentaTalonario, array(
            'em' => $this->getDoctrine()->getManager(),
        ));
        
        $estadoCorteVentaTalonario = $corteVentaTalonario->getEstado()->getId();
        if(!($estadoCorteVentaTalonario == EstadoCorteVentaTalonario::REGISTRADO || $estadoCorteVentaTalonario == EstadoCorteVentaTalonario::INICIADO)){
            return UtilService::returnError($this, "Solamente se puede anular un corte de venta cuando este en estado registrado o iniciado.");
        }
        
        if ($request->isMethod('POST')) {
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $corteVentaTalonario->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCorteVentaTalonario')->find(EstadoCorteVentaTalonario::ANULADO));
                $corteVentaTalonario->setImporteTotalItems(0);
                
                $listItems = $corteVentaTalonario->getListaItems();
                foreach ($listItems as $key => $item) {
                    $item->setImporte(0);
                    $item->setFechaActualizacion(new \DateTime());
                    $item->setUsuarioActualizacion($this->getUser());
                }
                
                $erroresItems = $this->get('validator')->validate($corteVentaTalonario);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                $tarjeta = $corteVentaTalonario->getTalonario()->getTarjeta();
                
                $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                $tarjetaBitacora->setDescripcion("Anulado el corte de venta ID: " . $corteVentaTalonario->getId() . ". Del " . $corteVentaTalonario->getInicial() . " al " . $corteVentaTalonario->getFinal() . ".");
                $tarjeta->addBitacoras($tarjetaBitacora);
                
                if($tarjeta->checkCortesVentaTerminados()){
                    $tarjeta->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoTarjeta')->find(EstadoTarjeta::PENDIENTE_CONCILACION));
                    
                    $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                    $tarjetaBitacora->setDescripcion("Tarjeta lista para conciliacion.");
                    $tarjeta->addBitacoras($tarjetaBitacora);
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($corteVentaTalonario);
                    $em->persist($tarjeta);
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
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:anular.html.twig', array(
            'entity' => $corteVentaTalonario,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/ajustar/{id}", name="corte-venta-ajustar-case1")
     * @Route(path="/ajustar.html", name="corte-venta-ajustar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function ajustarAction(Request $request, $id = null, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('ajustar_corte_venta_talonario_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del corte de venta.");
        }
        
        $corteVentaTalonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario')->find($id); 
        if ($corteVentaTalonario === null) {
            return UtilService::returnError($this, "El corte de venta con id: ".$id." no existe.");
        }
        
        if($corteVentaTalonario->getEstado()->getId() === EstadoCorteVentaTalonario::TERMINADO){
            return UtilService::returnError($this, "Solamente se puede ajutar un corte de venta sino esta terminado.");
        }
        
        $form = $this->createForm(new AjustarCorteVentaTalonarioType(), $corteVentaTalonario, array(
            'em' => $this->getDoctrine()->getManager(),
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                if($corteVentaTalonario->getEstado()->getId() === EstadoCorteVentaTalonario::INICIADO){
                    $corteVentaTalonario->clearListaItem();
                    $inicial = $corteVentaTalonario->getInicial();
                    $final = $corteVentaTalonario->getFinal();
                    for ($index = $inicial; $index <= $final; $index++) {
                        $corteVentaTalonarioItem = new CorteVentaTalonarioItem($this->getUser());
                        $corteVentaTalonarioItem->setNumero($index);
                        $corteVentaTalonarioItem->setImporte(0);
                        $corteVentaTalonario->addListaItem($corteVentaTalonarioItem);
                    }
                }
                
                $erroresItems = $this->get('validator')->validate($corteVentaTalonario);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                $tarjeta = $corteVentaTalonario->getTalonario()->getTarjeta();
                
                $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                $tarjetaBitacora->setDescripcion("Ajustando rango del corte de venta ID: " . $corteVentaTalonario->getId() . ". Nuevos valores: Del " . $corteVentaTalonario->getInicial() . " al " . $corteVentaTalonario->getFinal() . ".");
                $tarjeta->addBitacoras($tarjetaBitacora);
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($corteVentaTalonario);
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
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:ajustarCorteVenta.html.twig', array(
            'entity' => $corteVentaTalonario,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/actualizarRevision.html", name="corte-venta-actualizar-revision-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_REVISOR")
     */
    public function actualizarRevisionAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('actualizar_revision_corte_venta_talonario_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del corte de venta.");
        }
        
        $corteVentaTalonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario')->find($id); 
        if ($corteVentaTalonario === null) {
            return UtilService::returnError($this, "El corte de venta con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new ActualizarRevisionCorteVentaTalonarioType(), $corteVentaTalonario, array(
            'em' => $this->getDoctrine()->getManager(),
        ));
        
        if($corteVentaTalonario->getEstado()->getId() !== EstadoCorteVentaTalonario::INICIADO){
            return UtilService::returnError($this, "Solamente se puede actualizar un corte de venta cuando este en estado iniciado.");
        }
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $list = $corteVentaTalonario->getListaItems();
                foreach ($list as $item){
                    $item->setFechaActualizacion(new \DateTime());
                    $item->setUsuarioActualizacion($this->getUser());
                }
                
                $erroresItems = $this->get('validator')->validate($corteVentaTalonario);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($corteVentaTalonario);
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
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:actualizar.html.twig', array(
            'entity' => $corteVentaTalonario,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/finalizarRevision.html", name="corte-venta-finalizar-revision-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_REVISOR")
     */
    public function finalizarRevisionAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('finalizar_revision_corte_venta_talonario_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del corte de venta.");
        }
        
        $corteVentaTalonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario')->find($id); 
        if ($corteVentaTalonario === null) {
            return UtilService::returnError($this, "El corte de venta con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new FinalizarRevisionCorteVentaTalonarioType(), $corteVentaTalonario, array(
            'em' => $this->getDoctrine()->getManager(),
        ));
        
        if($corteVentaTalonario->getEstado()->getId() !== EstadoCorteVentaTalonario::INICIADO){
            return UtilService::returnError($this, "Solamente se puede terminar un corte de venta cuando este en estado iniciado.");
        }
        
        if ($request->isMethod('POST')) {
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $total = 0;
                $list = $corteVentaTalonario->getListaItems();
                foreach ($list as $item){
                    $importe = $item->getImporte();
                    if($importe === "" || floatval($importe) == 0){
                        return UtilService::returnError($this, "Debe definir el importe del talonario numero " . $item->getNumero() . ".");
                    }
                    $total += floatval($importe);
                }
                $corteVentaTalonario->setImporteTotalItems($total);
                $corteVentaTalonario->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCorteVentaTalonario')->find(EstadoCorteVentaTalonario::TERMINADO));
                
                $erroresItems = $this->get('validator')->validate($corteVentaTalonario);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                $tarjeta = $corteVentaTalonario->getTalonario()->getTarjeta();
                
                $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                $tarjetaBitacora->setDescripcion("Finalizada la revision del corte de venta ID: " . $corteVentaTalonario->getId() . ". Del " . $corteVentaTalonario->getInicial() . " al " . $corteVentaTalonario->getFinal() . ".");
                $tarjeta->addBitacoras($tarjetaBitacora);
                
                if($tarjeta->checkCortesVentaTerminados()){
                    $tarjeta->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoTarjeta')->find(EstadoTarjeta::PENDIENTE_CONCILACION));
                    
                    $tarjetaBitacora = new TarjetaBitacora($this->getUser());
                    $tarjetaBitacora->setDescripcion("Tarjeta lista para conciliacion.");
                    $tarjeta->addBitacoras($tarjetaBitacora);
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($corteVentaTalonario);
                    $em->persist($tarjeta);
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
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:CorteVentaTalonario:finalizar.html.twig', array(
            'entity' => $corteVentaTalonario,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/ponerRevision/{id}", name="corte-venta-poner-revision-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function ponerRevisionAction(Request $request, $id) {
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del corte de venta.");
        }
        
        $corteVentaTalonario = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CorteVentaTalonario')->find($id); 
        if ($corteVentaTalonario === null) {
            return UtilService::returnError($this, "El corte de venta con id: ".$id." no existe.");
        }
        
        if($corteVentaTalonario->getEstado()->getId() !== EstadoCorteVentaTalonario::TERMINADO){
            return UtilService::returnError($this, "Solamente se puede poner en revisión un corte de venta cuando este en estado terminado.");
        }
        
        $tarjeta = $corteVentaTalonario->getTalonario()->getTarjeta();
        if($tarjeta->getEstado()->getId() !== EstadoTarjeta::PENDIENTE_CONCILACION){
            return UtilService::returnError($this, "Solamente se puede poner en revisión un corte de venta cuando su tarjeta este pendiente de conciliación.");
        }

        $corteVentaTalonario->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoCorteVentaTalonario')->find(EstadoCorteVentaTalonario::INICIADO));
                
        $tarjeta->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoTarjeta')->find(EstadoTarjeta::CREADO));
                
        $tarjetaBitacora = new TarjetaBitacora($this->getUser());
        $tarjetaBitacora->setDescripcion("Se puso el corte de venta en revisión para rectificación de valores.");
        $tarjeta->addBitacoras($tarjetaBitacora);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $em->persist($corteVentaTalonario);
            $em->persist($tarjeta);
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
        } catch (\ErrorException $exc) {
            var_dump($exc->getMessage());
            $em->getConnection()->rollback();
            return UtilService::returnError($this);
        } catch (\Exception $exc) {
            var_dump($exc->getMessage());
            $em->getConnection()->rollback();
            return UtilService::returnError($this);
        }
    }
}

?>
