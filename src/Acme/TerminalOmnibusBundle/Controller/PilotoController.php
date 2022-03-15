<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Form\Frontend\Piloto\CrearPilotoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Piloto\ActualizarPilotoType;
use Acme\TerminalOmnibusBundle\Entity\Piloto;

/**
*   @Route(path="/piloto")
*/
class PilotoController extends Controller {
    
    /**
     * @Route(path="/", name="pilotos-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_ADMIN_PILOTOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function homePilotosAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Pilotos:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarPilotos.json", name="pilotos-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_ADMIN_PILOTOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
    */
    public function listarPilotosAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Piloto');
                $result = $repository->getPilotosPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $row = array(
                        'id' => $item->getId(),
                        'codigo' => $item->getCodigo(),
                        'nombre' => $item->getFullName(),
                        'fechaNacimiento' => $item->getFechaNacimiento() != null ? $item->getFechaNacimiento()->format('d-m-Y') : "",
                        'numeroLicencia' => $item->getNumeroLicencia(),
                        'fechaVencimientoLicencia' => $item->getFechaVencimientoLicencia() != null ? $item->getFechaVencimientoLicencia()->format('d-m-Y') : "",
                        'dpi' => $item->getDpi(),
                        'seguroSocial' => $item->getSeguroSocial(),
                        'telefono' => $item->getTelefono(),
                        'nacionalidad' => ($item->getNacionalidad() !== null) ? $item->getNacionalidad()->getNombre() : "",
                        'sexo' => ($item->getSexo() !== null) ? $item->getSexo()->getSigla() : "",
                        'empresa' => $item->getEmpresa() != null ? $item->getEmpresa()->getAlias() : "",
                        'activo' => $item->getActivo() === true ? "SI" : "NO"
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\Exception $exc) {
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
     * @Route(path="/crearPiloto.html", name="pilotos-crear-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_PILOTOS")
     */
    public function crearPilotoAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $piloto = new Piloto();        
        $form = $this->createForm(new CrearPilotoType($this->getDoctrine()), $piloto, array(
            'user' => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($piloto);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Pilotos:crear.html.twig', array(
            'piloto' => $piloto,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/actualizarPiloto.html", name="pilotos-actualizar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_PILOTOS")
     */
    public function actualizarPilotoAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('actualizar_piloto_command'); //Submit
                if($command !== null){
                    $id = $command["id"];
                }
            }
        }
      
        if (is_null($id)) {
            return UtilService::returnError($this, "No se pudo obtener el identificador del piloto."); 
        }

        $piloto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Piloto')->find($id); 
        if ($piloto === null) {
            return UtilService::returnError($this, "m1El piloto con id: ". $id. " no existe.");
        }
        
        $form = $this->createForm(new ActualizarPilotoType($this->getDoctrine()), $piloto, array(
            'user' => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($piloto);
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
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                }
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Pilotos:actualizar.html.twig', array(
            'piloto' => $piloto,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
}

?>
