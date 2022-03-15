<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Form\Model\ModelWebModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\MovilWeb\DesembarcarType;
use Acme\TerminalOmnibusBundle\Form\Frontend\MovilWeb\EmbarcarType;
use Acme\TerminalOmnibusBundle\Form\Frontend\MovilWeb\ListarDesembarcarType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\TerminalOmnibusBundle\Exception\FailValidationRuntimeException;

/**
*   @Route(path="/movilweb")
*/
class MovilWebController extends Controller {
    
    
    /**
     * @Route(path="/embarcar.html", name="movilweb-embarcar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function embarcarAction(Request $request, $_route) {
        if ($request->isMethod('POST')) {
           return  $this->forward('AcmeTerminalOmnibusBundle:Movil:embarcarEncomienda', array(
                '_route' => $_route,
                'keyEncomiendaEncritado' => false,
           ));
        }else{
            $modelWebModel = new ModelWebModel();       
            $form = $this->createForm(new EmbarcarType($this->getDoctrine()), $modelWebModel, array(
                "user" => $this->getUser()
            ));
            return $this->render('AcmeTerminalOmnibusBundle:MovilWeb:embarcar.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
        }
    }
    
    /**
     * @Route(path="/desembarcar.html", name="movilweb-desembarcar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function desembarcarAction(Request $request, $_route) {
        if ($request->isMethod('POST')) {
           return  $this->forward('AcmeTerminalOmnibusBundle:Movil:desembarcarEncomienda', array(
                '_route' => $_route,
                'keyEncomiendaEncritado' => false,
           ));
        }else{
            $modelWebModel = new ModelWebModel();       
            $form = $this->createForm(new DesembarcarType($this->getDoctrine()), $modelWebModel, array(
                "user" => $this->getUser()
            ));
            return $this->render('AcmeTerminalOmnibusBundle:MovilWeb:desembarcar.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));            
        }
    }
    
     /**
     * @Route(path="/listarEncomiendasDesembarcar.html", name="movilweb-listar-desembarcar-case1")
     * @Route(path="/listarEncomiendasDesembarcar/", name="movilweb-listar-desembarcar-case2")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function listarEncomiendasDesembarcarAction($_route) {
        $modelWebModel = new ModelWebModel();       
        $form = $this->createForm(new ListarDesembarcarType($this->getDoctrine()), $modelWebModel, array(
            "user" => $this->getUser()
        ));
        return $this->render('AcmeTerminalOmnibusBundle:MovilWeb:listarDesembarcar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
     /**
     * @Route(path="/listarEncomiendasDesembarcarPorSalida.json", name="movilweb-listar-desembarcar-encomienda-salida-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function listarEncomiendasDesembarcarInternalAction(Request $request, $_route) {
        
        $pageRequest = 1;
        $total = 0;
        $rows = array();
        
        try {
            $query = $this->get('request')->request->get('query');
            $mapFilters = UtilService::getMapsParametrosQuery($query);
            $idSalida = UtilService::getValueToMap($mapFilters, "salida");

            if(is_numeric($idSalida) === false){
                throw new FailValidationRuntimeException("El identificador de la salida es incorrecto.");
            }
            
            $estacionDestino = $this->getUser()->getEstacion();
            $encomiendas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasDesembarcarForMovil($idSalida, $estacionDestino);
            foreach ($encomiendas as $encomienda) {
                $estaciones = $encomienda->getEstacionOrigen()->__toString();
                foreach ($encomienda->getRutas() as $ruta) {
                    $estaciones .= " / " . $ruta->getEstacionDestino()->__toString();
                }
                
                $rows[] = array(
                    'id' => $encomienda->getId(), 
                    'idPadre' => "",
                    'tipoEncomienda' =>  $encomienda->getTipoEncomienda()->getNombre(),
                    'descripcion' =>  $encomienda->getDescripcion(),
                    'clienteRemitente' =>  $encomienda->getClienteRemitente()->__toString(),
                    'clienteDestinatario' =>  $encomienda->getClienteDestinatario()->__toString(),
                    'estaciones' => $estaciones
                );
            }
            
            $total = count($rows);
       
         } catch (\RuntimeException $exc) {
//            var_dump($exc->getTraceAsString());
//            echo $exc->getTraceAsString();
            $rows[] = array("id" => "Ha ocurrido un error.");
        } catch (\Exception $exc) {
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
    
}

?>
