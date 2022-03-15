<?php
namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Entity\EstadoTarjeta;
use Acme\TerminalOmnibusBundle\Entity\TarjetaBitacora;

/**
*   @Route(path="/tarjeta")
*/
class TarjetaController extends Controller {

    /**
     * @Route(path="/", name="tarjetas-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function homeTarjetasAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Tarjeta:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarTarjetas.json", name="tarjetas-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
    */
    public function listarTarjetasAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Tarjeta');
                $result = $repository->getTarjetasPaginadas($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'numero' => $item->getAlias(),
                        'salida' => $item->getSalida()->getId(),
                        'fecha' => $item->getSalida()->getFecha()->format('d-m-Y h:i A'),
                        'ruta' => $item->getSalida()->getItinerario()->getRuta()->__toString(),
                        'empresa' => $item->getSalida()->getEmpresa()->getAlias(),
                        'estado' => $item->getEstado()->getNombre(),
                        'cantidad' => count($item->getListCortesVenta()),
                        'fechaCreacion' => $item->getFechaCreacion() !== null ? $item->getFechaCreacion()->format('d-m-Y H:i:s') : "",
                        'usuarioCreacion' => $item->getUsuarioCreacion() !== null ? $item->getUsuarioCreacion()->getFullName() : "",
                        'estacionCreacion' => $item->getEstacionCreacion() !== null ? $item->getEstacionCreacion()->getNombre() : ""
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
     * @Route(path="/conciliarTarjeta.html", name="tarjeta-conciliar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function conciliarTarjetaAction(Request $request, $_route) {
        
        if ($request->isMethod('POST')) {
            
            //Procesar lista
            
            
            
        }
        else{
            $tarjetas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tarjeta')->getTarjetasListasParaConciliacion($this->getUser());
            return $this->render('AcmeTerminalOmnibusBundle:Tarjeta:conciliar.html.twig', array(
                'tarjetas' => $tarjetas,
                'route' => $_route,
                'mensajeServidor' => ""
            ));
        }
    }
    
     /**
     * @Route(path="/consultar.html", name="tarjeta-consultar-case1")
     * @Route(path="/consultar/{id}", name="tarjeta-consultar-case2")
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
            return UtilService::returnError($this, "No se pudo obtener el id de la tarjeta.");
        }
        
        $tarjeta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tarjeta')->find($id); 
        if ($tarjeta === null) {
            return UtilService::returnError($this, "La tarjeta con id: ".$id." no existe.");
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Tarjeta:consultar.html.twig', array(
            'entity' => $tarjeta,
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/conciliarsucess/{id}", name="tarjeta-conciliar-success-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function conciliarSuccessAction(Request $request, $id) {
        
        if (is_null($id)) {
           $id = $request->query->get('id');
            if (is_null($id)) {
                $id = $request->request->get('id');
            } 
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id de la tarjeta.");
        }
        
        $tarjeta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tarjeta')->find($id); 
        if ($tarjeta === null) {
            return UtilService::returnError($this, "La tarjeta con id: ".$id." no existe.");
        }
        
        if($tarjeta->getEstado()->getId() !== EstadoTarjeta::PENDIENTE_CONCILACION){
            return UtilService::returnError($this, "Solamente se puede conciliar una tarjeta tarjeta cuando este pendiente de conciliación.");
        }
        
        $tarjeta->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoTarjeta')->find(EstadoTarjeta::CONCILADO));
                
        $tarjeta->setFechaConciliacion(new \DateTime());
        $tarjeta->setUsuarioConciliacion($this->getUser());
                
        $tarjetaBitacora = new TarjetaBitacora($this->getUser());
        $tarjetaBitacora->setDescripcion("Tarjeta conciliada satisfactoriamente.");
        $tarjeta->addBitacoras($tarjetaBitacora);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

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
    
     /**
     * @Route(path="/conciliarConDiferencias/{id}", name="tarjeta-conciliar-diferencias-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function conciliarConDiferenciasAction(Request $request, $id) {
        
        if (is_null($id)) {
           $id = $request->query->get('id');
            if (is_null($id)) {
                $id = $request->request->get('id');
            } 
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id de la tarjeta.");
        }
        
        $tarjeta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tarjeta')->find($id); 
        if ($tarjeta === null) {
            return UtilService::returnError($this, "La tarjeta con id: ".$id." no existe.");
        }
        
        if($tarjeta->getEstado()->getId() !== EstadoTarjeta::PENDIENTE_CONCILACION){
            return UtilService::returnError($this, "Solamente se puede conciliar una tarjeta tarjeta cuando este pendiente de conciliación.");
        }
        
        $description = $request->request->get('description');
        $tarjeta->setObservacionConciliacion($description);
        
        $tarjeta->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoTarjeta')->find(EstadoTarjeta::CONCILADO_DIFERENCIAS));
                
        $tarjeta->setFechaConciliacion(new \DateTime());
        $tarjeta->setUsuarioConciliacion($this->getUser());
                
        $tarjetaBitacora = new TarjetaBitacora($this->getUser());
        $tarjetaBitacora->setDescripcion("Tarjeta conciliada con diferencias.");
        $tarjeta->addBitacoras($tarjetaBitacora);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

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
