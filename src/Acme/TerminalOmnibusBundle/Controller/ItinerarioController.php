<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial;
use Acme\TerminalOmnibusBundle\Form\Frontend\ItinerarioEspecial\CrearItinerarioEspecialType;

/**
*   @Route(path="/itinerario")
*/
class ItinerarioController extends Controller {

    /**
     * @Route(path="/", name="itinerarioEspecial-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA_SALIDA")
     */
    public function homeItinerarioEspecialAction($_route) {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:ItinerarioEspecial:listar.html.twig', array(
            "route" => $_route
        ));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/listarItinerariosEspeciales.json", name="itinerarioEspecial-listarPaginado")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA_SALIDA")
    */
    public function listarItinerariosEspecialesAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:ItinerarioEspecial');
                $result = $repository->getItinerariosEspecialesPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'fecha' => $item->getFecha()->format('d-m-Y H:i:s'),
                        'ruta' => $item->getRuta()->getCodigo(),
                        'empresa' => $item->getEmpresa()->getAlias(),
                        'origen' => $item->getRuta()->getEstacionOrigen()->__toString(),
                        'destino' => $item->getRuta()->getEstacionDestino()->__toString(),
                        'tipoBus' => $item->getTipoBus()->__toString(),
                        'motivo' => $item->getMotivo(),
                        'activo' => $item->getActivo() === true ? "Si" : "No",
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (Exception $exc) {
//            var_dump($exc->getTraceAsString());
//            echo $exc->getTraceAsString();
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
     * @Route(path="/crear.html", name="itinerarioEspecial-crear-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_AGENCIA_SALIDA")
     */
    public function crearItinerarioEspecialAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $itinerarioEspecial = new ItinerarioEspecial();
        $form = $this->createForm(new CrearItinerarioEspecialType($this->getDoctrine()), $itinerarioEspecial, array(
            "user" => $this->getUser()
        ));  
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $itinerarioEspecial->setEstacionOrigen($itinerarioEspecial->getRuta()->getEstacionOrigen()); 
                $empresa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                                ->getEmpresaQueFactura($itinerarioEspecial->getRuta()->getCodigo(), $itinerarioEspecial->getFecha());
                if($empresa === null){
                    return UtilService::returnError($this, "m1No se pudo obtener la empresa que factura en la ruta: " . $itinerarioEspecial->getRuta() ." el día: " . $itinerarioEspecial->getFecha()->format('d-m-Y') . ".");
                }else{
                    $itinerarioEspecial->setEmpresa($empresa);
                }

                $erroresItems = $this->get('validator')->validate($itinerarioEspecial);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($itinerarioEspecial);
                    $em->flush();
                    
                    if($itinerarioEspecial instanceof \Acme\BackendBundle\Entity\IJobSync){
                        if($itinerarioEspecial->isValidToSync()){
                            $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                            $jobSync->setNivel($itinerarioEspecial->getNivelSync());
                            $jobSync->setType($itinerarioEspecial->getTypeSync());
                            $jobSync->setUsuarioCreacion($this->getUser());
                            $jobSync->setData($itinerarioEspecial->getDataArrayToSync());
                            $this->get('acme_job_sync')->createJobSync($jobSync);
                        }
                    }
                    
                    $this->get('acme_backend_salida')->procesarSalidaPorItinerarioEspecial($itinerarioEspecial, array(
                        'user' => $this->getUser()
                    ));
                    $em->flush();
                    
                    $this->sendEmail($itinerarioEspecial);
                    $em->getConnection()->commit();
                    
                    return UtilService::returnSuccess($this);
                    
                } catch (\RuntimeException $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:ItinerarioEspecial:crear.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    public function sendEmail(ItinerarioEspecial $itinerarioEspecial)
    {
         $correos = $itinerarioEspecial->getEmpresa()->getCorreos();
         if($correos !== null && count($correos) !== 0){
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $subject = "ALERTA_" . $now . ". Se ha creado un itinerario especial.";
            UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_itinerario_especial.html.twig', array(
                'itinerarioEspecial' => $itinerarioEspecial
            )));
         }
    }
    
}

?>
