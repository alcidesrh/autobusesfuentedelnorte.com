<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Form\Frontend\Cliente\ClienteType;
use Acme\TerminalOmnibusBundle\Entity\Cliente;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
*   @Route(path="/cliente")
*/
class ClienteController extends Controller {

    /**
     * @Route(path="/", name="cliente-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_USER")
     */
    public function homeClienteAction($_route) {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Cliente:listar.html.twig', array(
            "route" => $_route
        ));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/buscador.html", name="cliente-buscador-case1")
     * @Secure(roles="ROLE_USER")
     */
    public function buscadorClienteAction($_route) {
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Cliente:buscador.html.twig', array(
            "route" => $_route
        ));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
    /**
     * @Route(path="/listarClientes.json", name="cliente-listarPaginado")
     * @Secure(roles="ROLE_USER")
    */
    public function listarClientesAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Cliente');
                $result = $repository->getClientesPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
//                    var_dump($item);
                    $row = array(
                        'id' => $item->getId(),
                        'nit' => $item->getNit(),
                        'documento' => $item->getDpi() !== null ? $item->getTipoDocumento()->getSigla() .' '. $item->getDpi() : "",
                        'nacionalidad' => ($item->getNacionalidad() !== null) ? $item->getNacionalidad()->getNombre() : "",
                        'nombre' => $item->getNombre(),
                        'telefono' => $item->getTelefono(),
                        'detallado' => $item->getDetallado() === true ? "Si" : "No",
                        'fechaNacimiento' => ($item->getFechaNacimiento() !== null) ? $item->getFechaNacimiento()->format('d-m-Y') : "",
                        'fechaVencimientoDocumento' => ($item->getFechaVencimientoDocumento() !== null) ? $item->getFechaVencimientoDocumento()->format('d-m-Y') : "",
                        'empleado' => $item->getEmpleado() === true ? "Si" : "No",
                        'sexo' => ($item->getSexo() !== null) ? $item->getSexo()->getSigla() : ""
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
     * @Route(path="/crear.html", name="cliente-crear-case1")
     * @Secure(roles="ROLE_USER")
     */
    public function crearClienteAction(Request $request, $_route) {
        
        $cliente = new Cliente();
        $form = $this->createForm(new ClienteType($this->getDoctrine()), $cliente);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $nit = $cliente->getNit();
                
                
                
                
                
                
                
                
                
                
                
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.
                        
//    CREDENCIALES EN ENTORNO DE PRUEBAS DE e-FORCON PARA MITOCHA            
	$sUsuarioMitocha = 'toper1-mayaoro';   
	$sClaveMitocha = 'Maya0$o@2021';             
                
//    $sUsuario = 'ti-20336837';
//    $sClave = 'Pionera/12';                
                
                
    $sUsuario = 'operws1-fdn';
    $sClave = '$$FDN@2020Fel';
//    
//    
//    $termNitCliente = trim($term);
    $termNitCliente = preg_replace('([^A-Za-z0-9])', '', $nit);    
//    
//
    $sNitReceptor = $termNitCliente;    
//    
//        
                require_once('lib/nusoap.php');

                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconReceptoresFel.asmx?WSDL','wsdl');
                $soapClientTest = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/WSForconReceptoresFel.asmx?WSDL','wsdl');
//                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;      				
                $soapClientTest->soap_defencoding = 'UTF-8';
                $soapClientTest->decode_utf8 = false;                 
                $soapClient->debug_flag = true;
                $soapClientTest->debug_flag = true;                
                $param = array('sUsuario' => $sUsuario, 'sClave' => $sClave, 'sNitReceptor' => $sNitReceptor);
                $paramTest = array('sUsuario' => $sUsuarioMitocha, 'sClave' => $sClaveMitocha, 'sNitReceptor' => $sNitReceptor);
                $result = $soapClient->call('ObtenerIdReceptor', $param);
                $resultTest = $soapClientTest->call('ObtenerIdReceptor', $paramTest);
            
            
                $WSResultadoReceptor = $result['ObtenerIdReceptorResult']['WSResultado'];
                $WSResultadoReceptorTest = $resultTest['ObtenerIdReceptorResult']['WSResultado'];
                
                
                if($WSResultadoReceptor === "true"){                      
            
//                        $clienteSat = $cliente->findOneByNit($sNitReceptor);
//                        $clienteSat = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->findOneByNit($nit);
                        $clienteSat = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->findOneByNit($sNitReceptor);
//                        
//                        
//                        
                        if($clienteSat === null){
//                            
//                            
//                            

//            
//        //END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs.                   
                
                
                
                
                
                
                
                
                
                
                
                
                
                if($sNitReceptor === null || trim($sNitReceptor) === "" || strtoupper(trim($sNitReceptor)) === "CF"){
                    $cliente->setNit("CF");         //AHORA SE GUARDA COMO CF SIN DIAGONAL EN MEDIO POR REQUERIMIENTO DE LA SAT
                    
                    
                    
                    
                    
                    
                    
                    
                }else{
                    $cliente->setNit(strtoupper($sNitReceptor));
                    $cliente->setNitCreacionCopia(strtoupper($sNitReceptor));
                    
                    
                    
                    
                    
                    
                    
                    
                    
                }
                $dpi = $cliente->getDpi();
                if($dpi !== null && is_string($dpi) && trim($dpi) !== ""){
                    $cliente->setDpi(strtoupper($dpi));
                }
                if($cliente->getDetallado()){
                    $cliente->setNombre1(strtoupper(trim($cliente->getNombre1())));
                    $cliente->setNombre2(strtoupper(trim($cliente->getNombre2())));
                    $cliente->setApellido1(strtoupper(trim($cliente->getApellido1())));
                    $cliente->setApellido2(strtoupper(trim($cliente->getApellido2())));
                    $fullname = $cliente->getNombre1();
                    if($cliente->getNombre2() !== null && trim($cliente->getNombre2()) !== ""){
                        $fullname .= " " . $cliente->getNombre2();
                    }
                    if($cliente->getApellido1() !== null && trim($cliente->getApellido1()) !== ""){
                        $fullname .= " " . $cliente->getApellido1();
                    }
                    if($cliente->getApellido2() !== null && trim($cliente->getApellido2()) !== ""){
                        $fullname .= " " . $cliente->getApellido2();
                    }
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    $cliente->setNombre($result['ObtenerIdReceptorResult']['WSRazonSocial']);
                    $cliente->setNombreCreacionCopia($result['ObtenerIdReceptorResult']['WSRazonSocial']);
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
//                    $cliente->setNombre($fullname);
                }else{
                    
                    
                    
                    
                    
                    $cliente->setNombre($result['ObtenerIdReceptorResult']['WSRazonSocial']);
                    $cliente->setNombreCreacionCopia($result['ObtenerIdReceptorResult']['WSRazonSocial']);                    
                    
                    
                    
                    
                    
//                    $cliente->setNombre(strtoupper(trim($cliente->getNombre())));
                }
                $cliente->setCorreo(strtolower($cliente->getCorreo()));
                $cliente->setUsuarioCreacion($this->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($cliente);
                $em->flush();
                
                
                
                
                return UtilService::returnSuccess($this, array(
                    'data' => $cliente->getId()
                ));
                
                
                
                
                
                
                
                
                
                
                
                        }else{
                            
                             if($sNitReceptor === null || trim($sNitReceptor) === "" || strtoupper(trim($sNitReceptor)) === "CF"){
                            $cliente->setNit("CF");         //AHORA SE GUARDA COMO CF SIN DIAGONAL EN MEDIO POR REQUERIMIENTO DE LA SAT
                            
                             }else{
                            
                            return UtilService::returnError($this, "m1Ya existe un cliente con Nit: ".$sNitReceptor. " registrado en el sistema.");
                            
                            }
                        }                       
                        
                }else if($WSResultadoReceptorTest === "true"){
                    
                    
//                        $clienteSat = $cliente->findOneByNit($sNitReceptor);
//                        $clienteSat = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->findOneByNit($nit);
                        $clienteSat = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->findOneByNit($sNitReceptor);
//                        
//                        
//                        
                        if($clienteSat === null){
//                            
//                            
//                            

//            
//        //END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs.                        
                
                
                
                
                
                
                
                
                if($sNitReceptor === null || trim($sNitReceptor) === "" || strtoupper(trim($sNitReceptor)) === "CF"){
                    $cliente->setNit("CF");         //AHORA SE GUARDA COMO CF SIN DIAGONAL EN MEDIO POR REQUERIMIENTO DE LA SAT
                    
                    
                    
                    
                    
                }else{
                    $cliente->setNit(strtoupper($sNitReceptor));
                    $cliente->setNitCreacionCopia(strtoupper($sNitReceptor));
                    
                    
                    
                    
                    
//                throw new \RuntimeException("Cliente FDN grabado satisfactoriamente");                        
                    
                    
                    
                    
                    
                }
                $dpi = $cliente->getDpi();
                if($dpi !== null && is_string($dpi) && trim($dpi) !== ""){
                    $cliente->setDpi(strtoupper($dpi));
                }
                if($cliente->getDetallado()){
                    $cliente->setNombre1(strtoupper(trim($cliente->getNombre1())));
                    $cliente->setNombre2(strtoupper(trim($cliente->getNombre2())));
                    $cliente->setApellido1(strtoupper(trim($cliente->getApellido1())));
                    $cliente->setApellido2(strtoupper(trim($cliente->getApellido2())));
                    $fullname = $cliente->getNombre1();
                    if($cliente->getNombre2() !== null && trim($cliente->getNombre2()) !== ""){
                        $fullname .= " " . $cliente->getNombre2();
                    }
                    if($cliente->getApellido1() !== null && trim($cliente->getApellido1()) !== ""){
                        $fullname .= " " . $cliente->getApellido1();
                    }
                    if($cliente->getApellido2() !== null && trim($cliente->getApellido2()) !== ""){
                        $fullname .= " " . $cliente->getApellido2();
                    }
                    
                    
                                        
                    
                    
                    $cliente->setNombre($resultTest['ObtenerIdReceptorResult']['WSRazonSocial']);
                    $cliente->setNombreCreacionCopia($resultTest['ObtenerIdReceptorResult']['WSRazonSocial']);
//                    $cliente->setNombre($fullname);
                    
                    
                    
                    
                    
                }else{
                    
                    
                    
                    
                    $cliente->setNombre($resultTest['ObtenerIdReceptorResult']['WSRazonSocial']);
                    $cliente->setNombreCreacionCopia($resultTest['ObtenerIdReceptorResult']['WSRazonSocial']);
//                    $cliente->setNombre(strtoupper(trim($cliente->getNombre())));
                    
                    
                    
                    
                    
                }
                $cliente->setCorreo(strtolower($cliente->getCorreo()));
                $cliente->setUsuarioCreacion($this->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($cliente);
                $em->flush();
                
                
                
                
                
//                throw new \RuntimeException("Cliente FDN grabado satisfactoriamente");                    
                
                
                
                
                
                return UtilService::returnSuccess($this, array(
                    'data' => $cliente->getId()
                ));
                
                
                
                
                
                        }else{
                            
                             if($sNitReceptor === null || trim($sNitReceptor) === "" || strtoupper(trim($sNitReceptor)) === "CF"){
                            $cliente->setNit("CF");         //AHORA SE GUARDA COMO CF SIN DIAGONAL EN MEDIO POR REQUERIMIENTO DE LA SAT
                            
                             }else{
                            
                            return UtilService::returnError($this, "m1Ya existe un cliente con Nit: ".$sNitReceptor. " registrado en el sistema.");
                            
                            }
                        }                    
                        
                        
                }else{    
                    
                    
                    
                    
                    
                    
                    $clienteSat = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->findOneByNombre($cliente->getNombre());    
//                    $clienteSat = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->find($cliente->getId());    
//                    $clienteSat = $cliente->getId();
//                        
//                        
//                        
                        if($clienteSat === null){                    
                    
                    
                    
                    
                    
                    
                if($sNitReceptor === null || trim($sNitReceptor) === "" || strtoupper(trim($sNitReceptor)) === "CF"){
                    $cliente->setNit("CF");         //AHORA SE GUARDA COMO "CF" SIN DIAGONAL EN MEDIO POR REQUERIMIENTO DE LA SAT
                    
                }else{
                    $cliente->setNit("CF");         //AHORA SE GUARDA COMO "CF" SIN DIAGONAL EN MEDIO POR REQUERIMIENTO DE LA SAT
                    
                }
                $dpi = $cliente->getDpi();
                if($dpi !== null && is_string($dpi) && trim($dpi) !== ""){
                    $cliente->setDpi(strtoupper($dpi));
                }
                if($cliente->getDetallado()){
                    $cliente->setNombre1(strtoupper(trim($cliente->getNombre1())));
                    $cliente->setNombre2(strtoupper(trim($cliente->getNombre2())));
                    $cliente->setApellido1(strtoupper(trim($cliente->getApellido1())));
                    $cliente->setApellido2(strtoupper(trim($cliente->getApellido2())));
                    $fullname = $cliente->getNombre1();
                    if($cliente->getNombre2() !== null && trim($cliente->getNombre2()) !== ""){
                        $fullname .= " " . $cliente->getNombre2();
                    }
                    if($cliente->getApellido1() !== null && trim($cliente->getApellido1()) !== ""){
                        $fullname .= " " . $cliente->getApellido1();
                    }
                    if($cliente->getApellido2() !== null && trim($cliente->getApellido2()) !== ""){
                        $fullname .= " " . $cliente->getApellido2();
                    }
                    $cliente->setNombre($fullname);
                }else{
                    $cliente->setNombre(strtoupper(trim($cliente->getNombre())));
                }
                $cliente->setCorreo(strtolower($cliente->getCorreo()));
                $cliente->setUsuarioCreacion($this->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($cliente);
                $em->flush();
                
                
                
                
                
//                throw new \RuntimeException("Cliente FDN grabado satisfactoriamente");                    
                
                
                
                
                
                return UtilService::returnSuccess($this, array(
                    'data' => $cliente->getId()
                ));                    
                    
                    
                    
                    
                    
                    
                        }else{
                            return UtilService::returnError($this, "m1Ya existe un cliente con los mismos datos registrado en el sistema.");
                        }    
            
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.   
                
                }    
                
                
            }else{
                return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Cliente:crear-update.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ''
        ));
        
        if($request->isMethod('GET')){
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        }
        
        return $respuesta;
    }
    
    /**
     * @Route(path="/actualizar.html", name="cliente-actualizar-case1")
     * @Secure(roles="ROLE_USER")
     */
    public function actualizarClienteAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cliente_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $cliente = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->find($id); 
        if ($cliente === null) {
            return UtilService::returnError($this, "El cliente con id: ".$id. " no existe.");
        }
        
        $form = $this->createForm(new ClienteType($this->getDoctrine()), $cliente);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $nit = $cliente->getNit();
                
                
                
                
                
                
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.               

                
                $NitCreacionCopia = $cliente->getNitCreacionCopia();
                $NombreCreacionCopia = $cliente->getNombreCreacionCopia();

    $termNitCliente = preg_replace('([^A-Za-z0-9])', '', $nit);    
    $sNitReceptor = $termNitCliente;                   
                
                
                if($sNitReceptor === null || trim($sNitReceptor) === "" || strtoupper(trim($sNitReceptor)) === "CF"){
                    $cliente->setNit("CF");
                }else if($sNitReceptor !== $NitCreacionCopia && $NitCreacionCopia !== null){
                    
//                    return UtilService::returnError($this, "m1No puede modificar el Nit autorizado por SAT.");
                    $cliente->setNit(strtoupper($NitCreacionCopia));
                }else if($sNitReceptor !== "CF" && $NitCreacionCopia === null){
                    
//                    return UtilService::returnError($this, "m1No puede modificar el Nit autorizado por SAT.");
                    $cliente->setNit("CF");
                }
                
                
                
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                
                
                
                
                
                
                
                $dpi = $cliente->getDpi();
                if($dpi !== null && is_string($dpi) && trim($dpi) !== ""){
                    $cliente->setDpi(strtoupper($dpi));
                }
                if($cliente->getDetallado()){
                    $cliente->setNombre1(strtoupper(trim($cliente->getNombre1())));
                    $cliente->setNombre2(strtoupper(trim($cliente->getNombre2())));
                    $cliente->setApellido1(strtoupper(trim($cliente->getApellido1())));
                    $cliente->setApellido2(strtoupper(trim($cliente->getApellido2())));
                    $fullname = $cliente->getNombre1();
                    if($cliente->getNombre2() !== null && trim($cliente->getNombre2()) !== ""){
                        $fullname .= " " . $cliente->getNombre2();
                    }
                    if($cliente->getApellido1() !== null && trim($cliente->getApellido1()) !== ""){
                        $fullname .= " " . $cliente->getApellido1();
                    }
                    if($cliente->getApellido2() !== null && trim($cliente->getApellido2()) !== ""){
                        $fullname .= " " . $cliente->getApellido2();
                    }
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    if($NombreCreacionCopia !== null && $fullname !== $NombreCreacionCopia){
                                            $cliente->setNombre($NombreCreacionCopia);
                    }else{
                        $cliente->setNombre($fullname);
                    }                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
//                    $cliente->setNombre($fullname);
                }else{
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    if($NombreCreacionCopia !== null && $fullname !== $NombreCreacionCopia){
                        $cliente->setNombre($NombreCreacionCopia);
                    }else{
                    $cliente->setNombre(strtoupper(trim($cliente->getNombre())));
                    }                    
                    
                    
                    
                    
                    
                    
                    
                    
//                    $cliente->setNombre(strtoupper(trim($cliente->getNombre())));
                }
                $cliente->setCorreo(strtolower($cliente->getCorreo()));
                $em = $this->getDoctrine()->getManager();
                $em->persist($cliente);
                $em->flush();
                return UtilService::returnSuccess($this, array(
                    'data' => $cliente->getId()
                ));
                
            }else{
               return UtilService::returnError($this, UtilService::getErrorsToForm($form));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Cliente:crear-update.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
}

?>
