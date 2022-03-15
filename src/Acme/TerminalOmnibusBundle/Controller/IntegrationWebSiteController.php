<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\BackendBundle\Entity\WebCode;
use Acme\TerminalOmnibusBundle\Entity\Boleto;
use Acme\TerminalOmnibusBundle\Entity\VoucherInternet;
use Acme\TerminalOmnibusBundle\Entity\Reservacion;
use Acme\TerminalOmnibusBundle\Entity\EstadoReservacion;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\EstadoSalida;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;
use Acme\TerminalOmnibusBundle\Entity\TipoPago;
use Acme\TerminalOmnibusBundle\Entity\Moneda;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\TerminalOmnibusBundle\Entity\EstadoEncomienda;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;

/**
*   @Route(path="/internal/integrations/portal/test")
*/
class IntegrationWebSiteController extends Controller {

    private $claveInterna = "pf12 2,21-32kdz9¡-3(r)=dftenfrsadw3d8dk"; 
    
    /**
     * @Route(path="/ga.json", name="integrations-listar-changes")
    */
    public function getActualizacionesAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $jobs = $this->getDoctrine()->getRepository('AcmeBackendBundle:JobSync')->listarJobSyncPendientes($idWeb);
            $data = array();
            $todoError = true;
            foreach ($jobs as $job) {
                
                $item = array(
                     'nivel' => $job->getNivel(),
                     'id' => $job->getId(),
                     'data' =>  $job->getData(),
                     'estado' => $job->getWebEstado($idWeb)
                );
                
                if($todoError === true && intval($item['estado']) === 1){
                    $todoError = false;
                }
                $data[] = $item;
            }
            
            if($todoError === true){
                $data = array();
            }
            $result["data"] = json_encode($data);
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/eap.json", name="integrations-update-changes")
    */
    public function setEstadoActualizacionesPendientesAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $data = $request->query->get('data');
            if (is_null($data)) {
                $data = $request->request->get('data');
            }
            
            if($data !== null && trim($data) !== ""){
                $dataJSON = json_decode($data);
                foreach ($dataJSON as $item) {
                    $id = $item->id;
                    if($id !== null && trim($id) !== ""){
                        $job = $this->getDoctrine()->getRepository('AcmeBackendBundle:JobSync')->find($id);
                        if($job !== null){
                            if($idWeb === "1"){
                                $job->setWeb1estado($item->estado);
                            }else if($idWeb === "2"){
                                $job->setWeb2estado($item->estado);
                            }else if($idWeb === "3"){
                                $job->setWeb3estado($item->estado);
                            }else if($idWeb === "4"){
                                $job->setWeb4estado($item->estado);
                            }
                            $em = $this->getDoctrine()->getManager();
                            $em->getConnection()->beginTransaction();
                            try {
                                $em->persist($job);
                                $em->flush();
                                $em->getConnection()->commit();
                            } catch (\RuntimeException $exc) {
                                $this->get("logger")->error("ERROR:" . $exc->getTraceAsString());
                                $em->getConnection()->rollback();
                                break;
                            } catch (\Exception $exc) {
                                $this->get("logger")->error("ERROR:" . $exc->getTraceAsString());
                                $em->getConnection()->rollback();
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/is.json", name="integrations-info-salida")
    */
    public function getInformacionBySalidasAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            if($dataIn !== null && trim($dataIn) !== ""){
                $dataJSON = json_decode($dataIn);
                $dataOut = array();
                foreach ($dataJSON as $json) {
                    if(!isset($json->idSalida) || $json->idSalida === null || trim($json->idSalida) === ""){
                        $status = WebCode::VALIDACION_ERROR;
                        $result["message"] = "Debe definir la salida";
                        break;
                    }
                    $idSalida = $json->idSalida;
                    $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida);
                    if($salida === null){
                        $status = WebCode::VALIDACION_ERROR;
                        $result["message"] = "La salida con identificador " . $idSalida . " no existe.";
                        break;
                    }
                    
                    $asientos = array();
                    $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getDatosParcialesBoletosActivosPorSalida($idSalida);
                    foreach ($boletos as $item) {
                        $asientos[]  = $item->getAsientoBus()->getNumero();
                    }
                    $reservaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->getDatosParcialesReservacionesActivosPorSalida($idSalida);
                    foreach ($reservaciones as $item) {
                        $asientos[]  = $item->getAsientoBus()->getNumero();
                    }
                    $dataOut[$idSalida] = $asientos;
                }
//                $result["data"] = json_encode($dataOut);
                $result["data"] = $dataOut;
                
            }else{
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "No se recibio todos los datos requeridos.";
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Credentials','true');
        $response->headers->set('Access-Control-Max-Age','86400');
        $response->headers->set('Access-Control-Allow-Methods','GET, POST');
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/cr.json", name="integrations-reservar")
    */
    public function crearReservacionesAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            
            if($dataIn !== null && trim($dataIn) !== ""){
                $mapSalidaByAsientos = array();
                $listaReservaciones = array();
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $dataIn = json_decode($dataIn);
                    $nacionalidad = isset($dataIn->nacionalidad) ? trim($dataIn->nacionalidad) : "";
                    if($nacionalidad === ""){
                        throw new \RuntimeException("m1Debe definir la nacionalidad del cliente de la reservación.");
                    }
                    $tipoDocumento = isset($dataIn->tipoDocumento) ? trim($dataIn->tipoDocumento) : "";
                    if($tipoDocumento === ""){
                        throw new \RuntimeException("m1Debe definir el tipo de documento del cliente de la reservación.");
                    }
                    $nit = isset($dataIn->nit) ? trim($dataIn->nit) : "";
                    if($nit === ""){
                        throw new \RuntimeException("m1Debe definir el nit del cliente de la reservación.");
                    }
                    $numeroDocumento = isset($dataIn->numeroDocumento) ? trim($dataIn->numeroDocumento) : "";
                    if($numeroDocumento === ""){
                        throw new \RuntimeException("m1Debe definir el número de documento del cliente de la reservacion.");
                    }
                    
                    $detallado = (isset($dataIn->detallado) && $dataIn->detallado === 'true') ? true : false;
                    $primerNombre = isset($dataIn->primerNombre) ? trim($dataIn->primerNombre) : "";
                    $segundoNombre = isset($dataIn->segundoNombre) ? trim($dataIn->segundoNombre) : "";
                    $primerApellido = isset($dataIn->primerApellido) ? trim($dataIn->primerApellido) : "";
                    $segundoApellido = isset($dataIn->segundoApellido) ? trim($dataIn->segundoApellido) : "";
                    
                    $fullname = "";
                    if($detallado){    
                        $fullname = $primerNombre;
                        if($segundoNombre !== "") $fullname .= " " . $segundoNombre;
                        if($primerApellido !== "") $fullname .= " " . $primerApellido;
                        if($segundoApellido !== "") $fullname .= " " . $segundoApellido;
                    }else{
                        $fullname = isset($dataIn->fullname) ? trim($dataIn->fullname) : "";
                    }
                    
                    if($fullname === ""){
                        throw new \RuntimeException("m1Debe definir el nombre completo del pasajero.");
                    }
                    
                    $sexo = isset($dataIn->sexo) ? $dataIn->sexo : "";
                    $fechaNacimiento = ((isset($dataIn->fechaNacimiento) && $dataIn->fechaNacimiento !== null && trim($dataIn->fechaNacimiento) !== "") ? \DateTime::createFromFormat('d/m/Y', trim($dataIn->fechaNacimiento)) : null);
                    $fechaVencimientoDocumento = ((isset($dataIn->fechaVencimientoDocumento) && $dataIn->fechaVencimientoDocumento !== null && trim($dataIn->fechaVencimientoDocumento) !== "") ? \DateTime::createFromFormat('d/m/Y', trim($dataIn->fechaVencimientoDocumento)) : null);
                    
                    $clienteReservacion = null;
                    $resultCliente = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')
                                            ->getClienteByDocumento($nacionalidad, $tipoDocumento, $numeroDocumento);
                    if($resultCliente !== null){
                        $clienteReservacion = $resultCliente;
                        $clienteReservacion->setNit($nit);
                        $values = split(" ", $fullname);
                        if(isset($values[0])){
                            if(stripos($clienteReservacion->getNombre(), $values[0]) === false){
                                $nacionaliad = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->find($nacionalidad);
                                $tipoDocumento = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->find($tipoDocumento);
                                throw new \RuntimeException("m1Existe otro cliente registrado en el sistema con la nacionalidad " . 
                                            $nacionaliad->getNombre() . " y documento " . $tipoDocumento->getSigla() . ": " . $numeroDocumento . ".");
                            }
                        }
                        if($clienteReservacion->getDetallado() === false && $detallado === true){
                            $clienteReservacion->setNombre($fullname);
                            $clienteReservacion->setNombre1($primerNombre);
                            $clienteReservacion->setNombre2($segundoNombre);
                            $clienteReservacion->setApellido1($primerApellido);
                            $clienteReservacion->setApellido2($segundoApellido);
                            $clienteReservacion->setDetallado($detallado);
                        }
                    }else{
                        $clienteReservacion = new \Acme\TerminalOmnibusBundle\Entity\Cliente();
                        $clienteReservacion->setNacionalidad($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->find($nacionalidad));
                        $clienteReservacion->setTipoDocumento($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->find($tipoDocumento));
                        $clienteReservacion->setDpi($numeroDocumento);
                        $clienteReservacion->setNit($nit);
                        $clienteReservacion->setNombre($fullname);
                        $clienteReservacion->setNombre1($primerNombre);
                        $clienteReservacion->setNombre2($segundoNombre);
                        $clienteReservacion->setApellido1($primerApellido);
                        $clienteReservacion->setApellido2($segundoApellido);
                        $clienteReservacion->setDetallado($detallado);
                        $clienteReservacion->setFechaCreacion(new \DateTime());
                     }
                     
                     if($clienteReservacion->getDetallado() === true && $detallado === true){
                        $clienteReservacion->setSexo($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Sexo')->find($sexo));
                        $clienteReservacion->setFechaNacimiento($fechaNacimiento);
                        $clienteReservacion->setFechaVencimientoDocumento($fechaVencimientoDocumento);
                     }
                     
                     $erroresItems = $this->get('validator')->validate($clienteReservacion);
                     if($erroresItems !== null && count($erroresItems) != 0){
                        throw new \RuntimeException($erroresItems->getIterator()->current()->getMessage());
                     }
                    
                    $dataJSON = $dataIn->items;
                    foreach ($dataJSON as $json) {
                        $reservacion = new Reservacion();
                        $reservacion->setFechaCreacion(new \DateTime());
                        $reservacion->setExterna(true);
                        $idWeb = strval($idWeb);
                        if($idWeb === "1"){
                            $reservacion->setEstacionCreacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find(Estacion::ESTACION_PORTAL_INTERNET_PIONERA));
                        }else if($idWeb === "2"){
                            $reservacion->setEstacionCreacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find(Estacion::ESTACION_PORTAL_INTERNET_MITOCHA));
                        }else{
                            throw new \RuntimeException("Debe definir la identificación del servidor.");
                        }
                        $reservacion->setReferenciaExterna(UtilService::getRefereciaExternaReservacion($idWeb));
                        $reservacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::EMITIDA));
                        if(!isset($json->idAsiento) || $json->idAsiento === null || trim($json->idAsiento) === ""){
                            throw new \RuntimeException("Debe definir el asiento del bus de la reservación.");
                        }
                        $reservacion->setAsientoBus($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->find(trim($json->idAsiento)));
                        if(!isset($json->idSalida) || $json->idSalida === null || trim($json->idSalida) === ""){
                            throw new \RuntimeException("Debe definir la salida de la reservación.");
                        }
                        $reservacion->setSalida($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find(trim($json->idSalida)));
                        $reservacion->setCliente($clienteReservacion);
                        
                        $erroresItems = $this->get('validator')->validate($reservacion);
                        if($erroresItems !== null && count($erroresItems) != 0){
                            throw new \RuntimeException($erroresItems->getIterator()->current()->getMessage());
                        }
                        $listaReservaciones[] = $reservacion;
                        $mapSalidaByAsientos[$json->idSalida][] = $reservacion->getAsientoBus()->getNumero();
                    }
               
                    foreach ($mapSalidaByAsientos as $idSalida => $numerosAsientos) {
                        $result = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientoOcupadosPorNumero($idSalida, $numerosAsientos);
                        foreach ($result as $asientoBus) {
                            $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida);
                            $message = " m1El asiento con el número: " . $asientoBus->getNumero() . 
                                       " de la salida " . $salida->getItinerario()->getRuta()->getNombre() .
                                       " de las " . $salida->getFecha()->format('h:i A') . 
                                       " acaba de ser ocupado. Por favor seleccione otro asiento.";
                            throw new \RuntimeException($message);
                        }
                    }
                    
                    foreach ($listaReservaciones as $item) {
                        $em->persist($item->getCliente());
                        $em->persist($item);
                    }
                    $em->flush();
                    $em->getConnection()->commit();

                    $dataOut = array();
                    foreach ($listaReservaciones as $item) {
                        $idSalida = $item->getSalida()->getId();
                        if(!isset($dataOut[$idSalida])){
                            $dataOut[$idSalida] = array();
                        }
                        $dataOut[$idSalida][] = array(
                            'idAsiento' => $item->getAsientoBus()->getId(),
                            'idReservacion' => $item->getId()
                        );
                    }
//                    $result["data"] = json_encode($dataOut);
                    $result["data"] = $dataOut;

                } catch (\RuntimeException $exc) {
                    $this->get("logger")->error("ERROR:" . $exc->getMessage());
                    $status = WebCode::VALIDACION_ERROR;
                    $result["message"] = $exc->getMessage();
                    $em->getConnection()->rollback();
                } catch (\ErrorException $exc) {
                    $this->get("logger")->error("ERROR:" . $exc->getMessage());
                    $status = WebCode::SERVIDOR_ERROR;
                    $result["message"] = $exc->getMessage();
                    $em->getConnection()->rollback();
                } catch (\Exception $exc) {
                    $this->get("logger")->error("ERROR:" . $exc->getMessage());
                    $status = WebCode::SERVIDOR_ERROR;
                    $result["message"] = $exc->getMessage();
                    $em->getConnection()->rollback();
                }
                
            }else{
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "No se recibio todos los datos requeridos.";
            }
        }
        
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Credentials','true');
        $response->headers->set('Access-Control-Max-Age','86400');
        $response->headers->set('Access-Control-Allow-Methods','GET, POST');
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/canr.json", name="integrations-cancelar-reservaciones")
    */
    public function cancelarReservacionesAction(Request $request) {
        
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            
            if($dataIn !== null && trim($dataIn) !== ""){
                
                $listaReservaciones = array();
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                
                    $dataJSON = json_decode($dataIn);
                    foreach ($dataJSON as $json) {
                        if(!isset($json->idReservacion) || $json->idReservacion === null || trim($json->idReservacion) === ""){
                            throw new \RuntimeException("Debe definir la reservación");
                        }
                        $reservacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->find(trim($json->idReservacion));
                        if($reservacion->getExterna() === false){
                            throw new \RuntimeException("La reservación con identificador " . $json->idReservacion . " no fue emitida desde internet.");
                        }
                        if($reservacion->getReferenciaExterna() !== UtilService::getRefereciaExternaReservacion($idWeb)){
                            throw new \RuntimeException("La reservación con identificador " . $json->idReservacion . "no está disponible.");
                        }
                        if(intval($reservacion->getEstado()->getId()) !== intval(EstadoReservacion::EMITIDA)){
                            throw new \RuntimeException("La reservación con identificador " . $json->idReservacion . " no está emitida.");
                        }

                        $reservacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::CANCELADA));
                        $reservacion->setFechaActualizacion(new \DateTime());
                        $listaReservaciones[] = $reservacion;
                    }
                    
                    foreach ($listaReservaciones as $item) {
                        $em->persist($item);
                    }
                    $em->flush();
                    $em->getConnection()->commit();
                
                } catch (\RuntimeException $exc) {
                    $this->get("logger")->error("ERROR:" . $exc->getMessage());
                    $status = WebCode::VALIDACION_ERROR;
                    $result["message"] = $exc->getMessage();
                    $em->getConnection()->rollback();
                } catch (\Exception $exc) {
                    $this->get("logger")->error("ERROR:" . $exc->getMessage());
                    $status = WebCode::SERVIDOR_ERROR;
                    $result["message"] = $exc->getMessage();
                    $em->getConnection()->rollback();
                }
                
            }else{
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "No se recibio todos los datos requeridos.";
            }
        }
        
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Credentials','true');
        $response->headers->set('Access-Control-Max-Age','86400');
        $response->headers->set('Access-Control-Allow-Methods','GET, POST');
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/cb.json", name="integrations-crear-boletos")
    */
    public function crearBoletosAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            
            if($dataIn !== null && trim($dataIn) !== ""){
                $mapReservacionByBoleto = array();
                $mapSalidaByBoletos = array();
                $listaBoletos = array();
                $listaReservaciones = array();
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $dataJSON = json_decode($dataIn);
                    foreach ($dataJSON as $json) {
                        if(!isset($json->idReservacion) || $json->idReservacion === null || trim($json->idReservacion) === ""){
                            throw new \RuntimeException("Debe definir la reservación del boleto.");
                        }
                        $reservacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->find(trim($json->idReservacion));
                        if($reservacion->getExterna() === false){
                            throw new \RuntimeException("La reservación con identificador " . $json->idReservacion . " no fue emitida desde internet.");
                        }
                        if($reservacion->getReferenciaExterna() !== UtilService::getRefereciaExternaReservacion($idWeb)){
                            throw new \RuntimeException("La reservación con identificador " . $json->idReservacion . "no está disponible.");
                        }
                        if(intval($reservacion->getEstado()->getId()) !== intval(EstadoReservacion::EMITIDA)){
                            throw new \RuntimeException("La reservación con identificador " . $json->idReservacion . " no está emitida.");
                        }
                        if(!isset($json->idSubeEn) || $json->idSubeEn === null || trim($json->idSubeEn) === ""){
                            throw new \RuntimeException("Debe definir la estación donde sube el pasajero.");
                        }
                        $estacionSubeEn = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find(trim($json->idSubeEn));
                        if(!isset($json->idBajaEn) || $json->idBajaEn === null || trim($json->idBajaEn) === ""){
                            throw new \RuntimeException("Debe definir la estación donde baja el pasajero.");
                        }
                        $estacionBajaEn = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find(trim($json->idBajaEn));
                        if(!isset($json->idTarifa) || $json->idTarifa === null || trim($json->idTarifa) === ""){
                            throw new \RuntimeException("Debe definir la tarifa.");
                        }
                        $tarifa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TarifaBoleto')->find(trim($json->idTarifa));
                        if(!isset($json->precioBase) || $json->precioBase === null || trim($json->precioBase) === ""){
                            throw new \RuntimeException("Debe definir el precio del boleto.");
                        }
                        $precioBase = doubleval($json->precioBase);
                        $nacionalidad = isset($json->nacionalidad) ? trim($json->nacionalidad) : "";
                        if($nacionalidad === ""){
                            throw new \RuntimeException("Debe definir la nacionalidad del pasajero.");
                        }
                        $tipoDocumento = isset($json->tipoDocumento) ? trim($json->tipoDocumento) : "";
                        if($tipoDocumento === ""){
                            throw new \RuntimeException("Debe definir el tipo de documento del pasajero.");
                        }
                        $numeroDocumento = isset($json->numeroDocumento) ? trim($json->numeroDocumento) : "";
                        if($numeroDocumento === ""){
                            throw new \RuntimeException("Debe definir el numero de documento del pasajero.");
                        }
                        $primerNombre = isset($json->primerNombre) ? trim($json->primerNombre) : "";
                        $segundoNombre = isset($json->segundoNombre) ? trim($json->segundoNombre) : "";
                        $primerApellido = isset($json->primerApellido) ? trim($json->primerApellido) : "";
                        $segundoApellido = isset($json->segundoApellido) ? trim($json->segundoApellido) : "";
                        $fullname = $primerNombre;
                        if($segundoNombre !== "") $fullname .= " " . $segundoNombre;
                        if($primerApellido !== "") $fullname .= " " . $primerApellido;
                        if($segundoApellido !== "") $fullname .= " " . $segundoApellido;
                        if($fullname === ""){ 
                            throw new \RuntimeException("Debe definir el nombre completo del pasajero.");
                        }
                    
                        $sexo = isset($json->sexo) ? $json->sexo : "";
                        $fechaNacimiento = ((isset($json->fechaNacimiento) && $json->fechaNacimiento !== null && trim($json->fechaNacimiento) !== "") ? \DateTime::createFromFormat('d/m/Y', trim($json->fechaNacimiento)) : null);
                        $fechaVencimientoDocumento = ((isset($json->fechaVencimientoDocumento) && $json->fechaVencimientoDocumento !== null && trim($json->fechaVencimientoDocumento) !== "") ? \DateTime::createFromFormat('d/m/Y', trim($json->fechaVencimientoDocumento)) : null);
                        $detallado = (isset($json->detallado) && $json->detallado === 'true') ? true : false;
                    
                        $cliente = null;
                        $resultCliente = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')
                                ->getClienteByDocumento($nacionalidad, $tipoDocumento, $numeroDocumento);
                        if($resultCliente !== null){
                            $cliente = $resultCliente;
                            $values = split(" ", $fullname);
                            if(isset($values[0])){
                                if(stripos($cliente->getNombre(), $values[0]) === false){
                                    $nacionaliad = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->find($nacionalidad);
                                    $tipoDocumento = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->find($tipoDocumento);
                                    throw new \RuntimeException("m1Existe otro cliente registrado en el sistema con la nacionalidad " . 
                                            $nacionaliad->getNombre() . " y documento " . $tipoDocumento->getSigla() . ": " . $numeroDocumento . ".");
                                }
                            }
                            if($cliente->getDetallado() === false && $detallado === true){
                                $cliente->setNombre($fullname);
                                $cliente->setNombre1($primerNombre);
                                $cliente->setNombre2($segundoNombre);
                                $cliente->setApellido1($primerApellido);
                                $cliente->setApellido2($segundoApellido);
                                $cliente->setDetallado($detallado);
                            }
                        }else{
                            $cliente = new \Acme\TerminalOmnibusBundle\Entity\Cliente();
                            $cliente->setNacionalidad($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->find($nacionalidad));
                            $cliente->setTipoDocumento($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->find($tipoDocumento));
                            $cliente->setDpi($numeroDocumento);
                            $cliente->setNombre($fullname);
                            $cliente->setNombre1($primerNombre);
                            $cliente->setNombre2($segundoNombre);
                            $cliente->setApellido1($primerApellido);
                            $cliente->setApellido2($segundoApellido);
                            $cliente->setDetallado($detallado);
                            $cliente->setFechaCreacion(new \DateTime());
                        }
                        if($cliente->getDetallado() === true){
                            $cliente->setSexo($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Sexo')->find($sexo));
                            $cliente->setFechaNacimiento($fechaNacimiento);
                            $cliente->setFechaVencimientoDocumento($fechaVencimientoDocumento);
                        }
                    
                        $salida = $reservacion->getSalida();
                        if($salida->getEstado()->getId() === EstadoSalida::INICIADA){
                            if(intval($estacionSubeEn->getId()) === intval($salida->getItinerario()->getRuta()->getEstacionOrigen()->getId())){
                                throw new \RuntimeException("En salidas iniciadas no se puede emitir boletos donde el cliente suba en el origen de la ruta.");
                            }    
                        }
                    
                        $boleto = new Boleto();
                        $boleto->setFechaCreacion(new \DateTime());
                        $boleto->setSalida($salida);
                        $boleto->setEstacionOrigen($estacionSubeEn);
                        $boleto->setEstacionDestino($estacionBajaEn);
                        $idWeb = strval($idWeb);
                        if($idWeb === "1"){
                            $boleto->setEstacionCreacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find(Estacion::ESTACION_PORTAL_INTERNET_PIONERA));
                        }else if($idWeb === "2"){
                            $boleto->setEstacionCreacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find(Estacion::ESTACION_PORTAL_INTERNET_MITOCHA));
                        }else{
                            throw new \RuntimeException("Debe definir la identificación del servidor");
                        }
                        $boleto->setUtilizarDesdeEstacionOrigenSalida(true);
                        $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::EMITIDO));
                        $boleto->setClienteDocumento($cliente);
                        $boleto->setClienteBoleto($cliente);
                        $boleto->setAsientoBus($reservacion->getAsientoBus());
                        $boleto->setCamino(false);
                        $boleto->setTipoDocumento($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumentoBoleto')->find(TipoDocumentoBoleto::VOUCHER_INTERNET));
                        $boleto->setTipoPago($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoPago')->find(TipoPago::TARJETA));
                        $boleto->setTarifa($tarifa);
                        $tarifa->setTipoPago($boleto->getTipoPago()->getId());
                        $boleto->setPrecioCalculadoMonedaBase($precioBase);
                        $boleto->setTarifaAdicionalMonedaBase($tarifa->calcularTarifaAdicional());
                        $boleto->setMoneda($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ));
                        $boleto->setTipoCambio($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio(Moneda::GTQ));
                        $boleto->setPrecioCalculado($boleto->getPrecioCalculadoMonedaBase());
                        $observacion  = "Boleto comprado por internet. Referencia: " . $reservacion->getReferenciaExterna() . ".";
                        $observacion .= "Id de pre-reservación: RESER_ID_" . $reservacion->getId() . ".";
                        $boleto->setObservacion($observacion);
                        
                        $boletoBitacora = new BoletoBitacora();
                        $boletoBitacora->setEstado($boleto->getEstado());
                        $boletoBitacora->setFecha(new \DateTime());
                        $boletoBitacora->setUsuario($this->getUser());
                        $boletoBitacora->setDescripcion("Emisión de boleto por el portal web " . $boleto->getEstacionCreacion()->getNombre() . ". " . $observacion);
                        $boleto->addBitacoras($boletoBitacora);
                        
                        $voucherInternet = new VoucherInternet();
                        $voucherInternet->setEmpresa($salida->getEmpresa());
                        $voucherInternet->setMoneda($boleto->getMoneda());
                        $voucherInternet->setImporteTotal($boleto->getPrecioCalculadoMonedaBase());
                        $voucherInternet->setFecha(new \DateTime());
                        $voucherInternet->setEstacion($boleto->getEstacionCreacion());
                        $boleto->setVoucherInternet($voucherInternet);
                        
                        $reservacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::VENDIDA));
                        $reservacion->setFechaActualizacion(new \DateTime());
                    
                        $erroresItems = $this->get('validator')->validate($boleto);
                        if($erroresItems !== null && count($erroresItems) != 0){
                            throw new \RuntimeException($erroresItems->getIterator()->current()->getMessage());
                        }
                        $erroresItems = $this->get('validator')->validate($reservacion);
                        if($erroresItems !== null && count($erroresItems) != 0){
                            throw new \RuntimeException($erroresItems->getIterator()->current()->getMessage());
                        }
                    
                        $mapReservacionByBoleto[$reservacion->getId()] = $boleto;
                        $mapSalidaByBoletos[$salida->getId()][] = $boleto;
                        $listaBoletos[] = $boleto;
                        $listaReservaciones[] = $reservacion;
                    }
                    
                    foreach ($mapSalidaByBoletos as $idSalida => $boletos) {
                        $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida);
                        $ruta = $salida->getItinerario()->getRuta();
                        if($ruta->getObligatorioClienteDetalle() === true){
                            $clientes = array();
                            foreach ($boletos as $boleto) {
                                $idCliente = $boleto->getClienteBoleto()->getId();
                                if(in_array($idCliente, $clientes)){
                                    throw new \RuntimeException("El cliente ". $boleto->getClienteBoleto()->getNombre(). " no puede estar ubicado en más de dos asientos en la misma salida.");
                                }else{
                                    $clientes[] = $idCliente;
                                }
                            }
                            $boletoConClientesRepetidos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getBoletoConClientesRepetidosPorSalida($salida->getId(), $clientes, array());
                            foreach ($boletoConClientesRepetidos as $boleto) {
                                throw new \RuntimeException("El cliente ". $boleto->getClienteBoleto()->getNombre(). " no puede estar ubicado en más de dos asientos en la misma salida.");
                            }
                        }
                    }
                    
                    foreach ($listaBoletos as $item) {
                        $em->persist($item->getClienteBoleto());
                        $em->persist($item);
                    }
                    foreach ($listaReservaciones as $item) {
                        $em->persist($item);
                    }
                    $em->flush();
                    $em->getConnection()->commit();

                    $dataOut = array();
                    foreach ($mapReservacionByBoleto as $idReservacion => $boleto) {
                            $dataOut[] = array(
                                'idReservacion' => $idReservacion,
                                'idBoleto' => $boleto->getId()
                            );
                    }
//                    $result["data"] = json_encode($dataOut);
                    $result["data"] = $dataOut;

                } catch (\RuntimeException $exc) {
                    $this->get("logger")->error("ERROR:" . $exc->getMessage());
                    $status = WebCode::VALIDACION_ERROR;
                    $result["message"] = $exc->getMessage();
                    $em->getConnection()->rollback();
                } catch (\Exception $exc) {
                    $this->get("logger")->error("ERROR:" . $exc->getMessage());
                    $status = WebCode::SERVIDOR_ERROR;
                    $result["message"] = $exc->getMessage();
                    $em->getConnection()->rollback();
                }
                
            }else{
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "No se recibio todos los datos requeridos.";
            }
        }
        
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/ao.json", name="integrations-info-asientos-ocupados")
    */
    public function getAsientosOcupadosAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            
            $salidas = array();
            if($dataIn !== null && trim($dataIn) !== ""){
                $dataJSON = json_decode($dataIn);
                foreach ($dataJSON as $json) {
                    if(!isset($json->idSalida) || $json->idSalida === null || trim($json->idSalida) === ""){
                        $status = WebCode::VALIDACION_ERROR;
                        $result["message"] = "Debe definir la salida";
                        break;
                    }
                    $idSalida = $json->idSalida;
                    $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida);
                    if($salida === null){
                        $status = WebCode::VALIDACION_ERROR;
                        $result["message"] = "La salida con identificador " . $idSalida . " no existe.";
                        break;
                    }
                    $salidas[] = $salida;
                }
            }else{
                $salidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->listarSalidasFuturas("+3 day");
            }
            
            if($status === WebCode::SERVIDOR_SATISFACTORIO){
                $dataOut = array();
                foreach ($salidas as $salida) {
                    $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getDatosParcialesBoletosActivosPorSalida($salida->getId());
                    $reservaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->getDatosParcialesReservacionesActivosPorSalida($salida->getId());
                    $dataOut[$salida->getId()] = count($boletos) + count($reservaciones);
                }
                $result["data"] = $dataOut;
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/ie.json", name="integrations-informacion-encomienda")
    */
    public function getInformacionEncomiendaAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            
            if($dataIn !== null && trim($dataIn) !== ""){ 
                $dataOut = array();
                $idEncomienda = $dataIn;
                $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
                if($encomienda === null){
                    $dataOut[] = "La encomienda no existe";
                }else{
                    
                    $dataOut[] = "La encomienda fue recibida el "  . 
                            $encomienda->getFechaCreacion()->format('d-m-Y h:i A') . 
                            " en la oficina " .
                            $encomienda->getEstacionCreacion()->getInfo2();
                    
                    $ultimaBitacora = $encomienda->getBitacora();
                    $ultimoEstado = $ultimaBitacora->getEstado();
                    
                    if($ultimoEstado->getId() === EstadoEncomienda::ANULADA){
                        $dataOut[] = "La encomienda fue anulada el " .
                                     $ultimaBitacora->getFecha()->format('d-m-Y h:i A') .
                                     " en la oficina " .
                                     $ultimaBitacora->getEstacion()->getInfo2();
                    } else if($ultimoEstado->getId() === EstadoEncomienda::CANCELADA){
                        $dataOut[] = "La encomienda fue cancelada el " .
                                     $ultimaBitacora->getFecha()->format('d-m-Y h:i A') .
                                     " en la oficina " .
                                     $ultimaBitacora->getEstacion()->getInfo2();
                    } else if($ultimoEstado->getId() === EstadoEncomienda::TRANSITO){
                        $dataOut[] = "La encomienda se encuentra en tránsito.";
                    } else if($ultimoEstado->getId() === EstadoEncomienda::EMBARCADA || 
                            $ultimoEstado->getId() === EstadoEncomienda::DESEMBARCADA){
                        $dataOut[] = "La encomienda se encuentra en la oficina " .
                                     $ultimaBitacora->getEstacion()->getInfo2();
                    }else if($ultimoEstado->getId() === EstadoEncomienda::ENTREGADA){
                        $dataOut[] = "La encomienda fue entregada el " .
                                     $ultimaBitacora->getFecha()->format('d-m-Y h:i A') .
                                     " en la oficina " .
                                     $ultimaBitacora->getEstacion()->getInfo2();
                    }
                }
                
                $result["data"] = $dataOut;
                
            }else{
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "Debe definir el identificador";
            }
            
            
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Credentials','true');
        $response->headers->set('Access-Control-Max-Age','86400');
        $response->headers->set('Access-Control-Allow-Methods','GET, POST');
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/ieu.json", name="integrations-informacion-encomienda-por-usuario")
    */
    public function getInformacionEncomiendaPorUsuarioAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            
            if($dataIn !== null && trim($dataIn) !== ""){ 
                $dataOut = array();
                $codigoUsuario = $dataIn;
                $encomiendas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasByCliente($codigoUsuario);
                if(count($encomiendas) === 0){
                    $dataOut[] = "No existen encomiendas recientes.";
                }else{
                    foreach ($encomiendas as $encomienda) {
                        $infos = array();
                        $infos[] = "La encomienda fue recibida el "  . $encomienda->getFechaCreacion()->format('d-m-Y h:i A') . 
                            " en la oficina " . $encomienda->getEstacionCreacion()->getInfo2();
                        
                        $ultimaBitacora = $encomienda->getBitacora();
                        $ultimoEstado = $ultimaBitacora->getEstado();

                        if($ultimoEstado->getId() === EstadoEncomienda::ANULADA){
                            $infos[] = "La encomienda fue anulada el " .
                                         $ultimaBitacora->getFecha()->format('d-m-Y h:i A') .
                                         " en la oficina " .
                                         $ultimaBitacora->getEstacion()->getInfo2();
                        } else if($ultimoEstado->getId() === EstadoEncomienda::CANCELADA){
                            $infos[] = "La encomienda fue cancelada el " .
                                         $ultimaBitacora->getFecha()->format('d-m-Y h:i A') .
                                         " en la oficina " .
                                         $ultimaBitacora->getEstacion()->getInfo2();
                        } else if($ultimoEstado->getId() === EstadoEncomienda::TRANSITO){
                            $infos[] = "La encomienda se encuentra en tránsito.";
                        } else if($ultimoEstado->getId() === EstadoEncomienda::EMBARCADA || 
                                $ultimoEstado->getId() === EstadoEncomienda::DESEMBARCADA){
                            $infos[] = "La encomienda se encuentra en la oficina " .
                                         $ultimaBitacora->getEstacion()->getInfo2();
                        }else if($ultimoEstado->getId() === EstadoEncomienda::ENTREGADA){
                            $infos[] = "La encomienda fue entregada el " .
                                         $ultimaBitacora->getFecha()->format('d-m-Y h:i A') .
                                         " en la oficina " .
                                         $ultimaBitacora->getEstacion()->getInfo2();
                        }
                        $dataOut[$encomienda->getId()] = $infos;
                    }
                }
                $result["data"] = $dataOut;
                
            }else{
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "Debe definir el codigo del usuario";
            }
        }
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Credentials','true');
        $response->headers->set('Access-Control-Max-Age','86400');
        $response->headers->set('Access-Control-Allow-Methods','GET, POST');
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/vb.json", name="integrations-voucher-boletos")
    */
    public function getVoucherBoletosAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            
            try {
                
                if($dataIn !== null && trim($dataIn) !== ""){
                    $dataJSON = json_decode($dataIn);
                    $idsBoletosStr = implode(",", $dataJSON);
                    $path = $this->container->get("acme_terminal_omnibus_print")->getPathVoucherBoletoInternal($idsBoletosStr, "pdf");
                    $result["path"] = $path;
                }
            
            } catch (\RuntimeException $exc) {
                $status = WebCode::VALIDACION_ERROR;
                $mensaje = $exc->getMessage();
                if(UtilService::startsWith($mensaje, 'm1')){ $result["message"] = $mensaje; }
                else{ $result["message"] = "m1Ha ocurrido un error en el sistema"; }
                $this->get("logger")->error("ERROR_PORTAL: " . $mensaje);
            } catch (\ErrorException $exc) {
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "m1Ha ocurrido un error en el sistema";
                $this->get("logger")->error("ERROR_PORTAL: " . $exc->getMessage());
            } catch (\Exception $exc) {
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "m1Ha ocurrido un error en el sistema";
                $this->get("logger")->error("ERROR_PORTAL: " . $exc->getMessage());
            }
        }
        
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    /**
     * @Route(path="/vc.json", name="integrations-validar-clientes")
    */
    public function validarClientesAction(Request $request) {
        $result = array();
        $status = $this->validarCredencial($request);
        if($status === WebCode::SERVIDOR_SATISFACTORIO){
            $idWeb = $request->query->get('idWeb');
            if (is_null($idWeb)) {
                $idWeb = $request->request->get('idWeb');
            }
            $dataIn = $request->query->get('data');
            if (is_null($dataIn)) {
                $dataIn = $request->request->get('data');
            }
            if($dataIn !== null && trim($dataIn) !== ""){
                
                try{
                    
                    $dataJSON = json_decode($dataIn);
                    foreach ($dataJSON as $json) {

                        $nacionalidad = isset($json->nacionalidad) ? trim($json->nacionalidad) : "";
                        if($nacionalidad === ""){
                            throw new \RuntimeException("m1Debe definir la nacionalidad del pasajero.");
                        }
                        $tipoDocumento = isset($json->tipoDocumento) ? trim($json->tipoDocumento) : "";
                        if($tipoDocumento === ""){
                            throw new \RuntimeException("m1Debe definir el tipo de documento del pasajero.");
                        }
                        $numeroDocumento = isset($json->numeroDocumento) ? trim($json->numeroDocumento) : "";
                        if($numeroDocumento === ""){
                            throw new \RuntimeException("m1Debe definir el numero de documento del pasajero.");
                        }
                        $primerNombre = isset($json->primerNombre) ? trim($json->primerNombre) : "";
                        $segundoNombre = isset($json->segundoNombre) ? trim($json->segundoNombre) : "";
                        $primerApellido = isset($json->primerApellido) ? trim($json->primerApellido) : "";
                        $segundoApellido = isset($json->segundoApellido) ? trim($json->segundoApellido) : "";
                        $fullname = $primerNombre;
                        if($segundoNombre !== "") $fullname .= " " . $segundoNombre;
                        if($primerApellido !== "") $fullname .= " " . $primerApellido;
                        if($segundoApellido !== "") $fullname .= " " . $segundoApellido;
                        if($fullname === ""){ 
                            throw new \RuntimeException("m1Debe definir el nombre completo del pasajero.");
                        }

                        $resultCliente = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')
                                    ->getClienteByDocumento($nacionalidad, $tipoDocumento, $numeroDocumento);
                        if($resultCliente !== null){
                            $values = split(" ", $fullname);
                            if(isset($values[0])){
                                if(stripos($resultCliente->getNombre(), $values[0]) === false){
                                    $nacionaliad = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->find($nacionalidad);
                                    $tipoDocumento = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->find($tipoDocumento);
                                    throw new \RuntimeException("m1Existe otro cliente registrado en el sistema con la nacionalidad " . 
                                            $nacionaliad->getNombre() . " y documento " . $tipoDocumento->getSigla() . ": " . $numeroDocumento . ".");
                                }
                            }
                        }
                    }
                
                } catch (\RuntimeException $exc) {
                    $status = WebCode::VALIDACION_ERROR;
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){ $result["message"] = $mensaje; }
                    else{ $result["message"] = "m1Ha ocurrido un error en el sistema"; }
                    $this->get("logger")->error("ERROR_PORTAL: " . $mensaje);
                } catch (\ErrorException $exc) {
                    $status = WebCode::VALIDACION_ERROR;
                    $result["message"] = "m1Ha ocurrido un error en el sistema";
                    $this->get("logger")->error("ERROR_PORTAL: " . $exc->getMessage());
                } catch (\Exception $exc) {
                    $status = WebCode::VALIDACION_ERROR;
                    $result["message"] = "m1Ha ocurrido un error en el sistema";
                    $this->get("logger")->error("ERROR_PORTAL: " . $exc->getMessage());
                }
            
            }else{
                $status = WebCode::VALIDACION_ERROR;
                $result["message"] = "Debe definir los datos del cliente a validar.";
            }
        }
        
        $result["status"] = $status;
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }
    
    private function validarCredencial(Request $request) {
        
        return WebCode::SERVIDOR_SATISFACTORIO; //only for test
        
        $idWeb = $request->query->get('idWeb');
        if (is_null($idWeb)) {
            $idWeb = $request->request->get('idWeb');
            if(is_null($idWeb)){
                return WebCode::CREDENCIALES_MAL;
            }  
        }
        $tokenAutRemote = $request->query->get('tokenAut');
        if (is_null($tokenAutRemote)) {
            $tokenAutRemote = $request->request->get('tokenAut');
            if(is_null($tokenAutRemote)){
                return WebCode::CREDENCIALES_MAL;
            }
        }
        $now = new \DateTime();
        $data = $now->format('Y-m-d'). "_system_web_" . $idWeb;
        $tokenAutLocal = \Acme\BackendBundle\Services\UtilService::encrypt($this->claveInterna, $data);
        if($tokenAutLocal === $tokenAutRemote){
            return WebCode::SERVIDOR_SATISFACTORIO;
        }else{
            return WebCode::CREDENCIALES_MAL;
        }
    }
}

?>
