<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Form\Model\RegistrarEncomiendaModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\RegistrarEncomiendaType;
use Acme\TerminalOmnibusBundle\Entity\TipoOperacionCaja;
use Acme\TerminalOmnibusBundle\Entity\OperacionCaja;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\Encomienda;
use Acme\TerminalOmnibusBundle\Entity\TipoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\TipoPago;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Acme\TerminalOmnibusBundle\Entity\EncomiendaBitacora;
use Acme\TerminalOmnibusBundle\Entity\EstadoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\Moneda;
use Acme\TerminalOmnibusBundle\Entity\FacturaGenerada;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\EmbarcarEncomiendaType;
use Acme\TerminalOmnibusBundle\Entity\EstadoSalida;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\DesembarcarEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\EntregarEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Model\EntregarEncomiendaModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\AnularEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\CancelarEncomiendaType;
use Acme\BackendBundle\Exceptions\RuntimeExceptionCode;
use Acme\TerminalOmnibusBundle\Entity\EncomiendaRuta;
use Acme\TerminalOmnibusBundle\Entity\Ruta;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\ProcesarEncomiendasPorSalidaType;
use Acme\TerminalOmnibusBundle\Form\Model\GenericModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\PendienteEnvioType;
use Acme\TerminalOmnibusBundle\Form\Model\PendienteEntregarModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\PendienteEntregaType;
use Acme\TerminalOmnibusBundle\Form\Model\EncomiendaEspecialModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\RegistrarEncomiendaEspecialType;
use Acme\TerminalOmnibusBundle\Entity\TipoEncomiendaEspeciales;
use Acme\TerminalOmnibusBundle\Entity\TarifaEncomiendaEspeciales;
use Acme\TerminalOmnibusBundle\Form\Model\EntregaMultipleModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda\EntregaMultipleEncomiendaType;

/**
*   @Route(path="/encomienda")
*/
class EncomiendaController extends Controller {

    /**
     * @Route(path="/", name="encomiendas-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function homeEncomiendasAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Encomienda:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarEncomiendas.json", name="encomiendas-listarPaginado")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_INSPECTOR_ENCOMIENDA")
    */
    public function listarEncomiendasAction($_route) {
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
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Encomienda');
                $result = $repository->getEncomiendasPaginadas($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $documento = $item->getTipoDocumento()->getNombre();
                    if($item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA || 
                            $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                        if($item->getFacturaGenerada() !== null){
                            $documento .= " - " . $item->getFacturaGenerada()->getInfo2();
                        }else{
                            $documento .= " - N/D";
                        }
                    }
                    
                    $row = array(
                        'id' => $item->getId(),
                        'fechaCreacion' => $item->getFechaCreacion()->format('d-m-Y H:i:s'),
                        'empresa' => $item->getEmpresa()->getAlias(),
                        'bus' => ($item->getEstuboTransito() === true && $item->getPrimeraSalida() !== null) ? $item->getPrimeraSalida()->getBus()->getCodigo() : "",
//                        'ruta' => $item->getRuta()->__toString(), //Seria la primera ruta
                        'cantidad' => $item->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO ? "GTQ " . $item->getCantidad() : $item->getCantidad(),
                        'tipoEncomienda' => $item->getTipoEncomienda()->getNombre(),
                        'clienteRemitente' => $item->getClienteRemitente()->__toString(),
                        'clienteDestinatario' => $item->getClienteDestinatario()->__toString(),
                        'estacionOrigen' => $item->getEstacionOrigen()->__toString(),
                        'estacionDestino' => $item->getEstacionDestino()->__toString(),
                        'estaciones' => $item->getEstacionesStr(),
                        'tipoDocumento' => $documento,
                        'estado' => $item->getUltimoEstado()->getNombre(),
                        'codigoExternoCliente' => $item->getCodigoExternoCliente(),
                        'precioCalculado' => $item->getMoneda()  === null ? "" : $item->getMoneda()->getSigla() . " " . $item->getPrecioCalculado(),
                        'precioCalculadoMonedaBase' => $item->getPrecioCalculadoMonedaBase() === null ? "" : "GTQ " .  $item->getPrecioCalculadoMonedaBase(),
                        'boleto' => $item->getBoleto() === null ? "" : $item->getBoleto()->getId(),
                        'descripcion' => $item->getDescripcion(),
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\Exception $exc) {
            var_dump($exc);
            $this->get('logger')->error("Ha ocurrido un error en el sistema. " . $exc->getMessage());
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
     * @Route(path="/registrarEncomienda.html", name="encomiendas-registrar-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function registrarEncomiendaAction(Request $request, $_route) {
        
        $registrarEncomiendaModel = new RegistrarEncomiendaModel();       
        $form = $this->createForm(new RegistrarEncomiendaType($this->getDoctrine()), $registrarEncomiendaModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));
        
        $mensajeServidor = "";
        if ($request->isMethod('POST')) {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            //--------------------------------------------------------------------------------------------------------
            //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LA ENCOMIENDA - INIT
            //--------------------------------------------------------------------------------------------------------
            if(trim($registrarEncomiendaModel->getIdentificadorWeb()) !== ""){
                $encomiendas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')
                                    ->getEncomiendasByIdentificadorWeb($registrarEncomiendaModel->getIdentificadorWeb());
                if(count($encomiendas) !== 0){
                    $ids = array();
                    $tipoDocumento = null;
                    foreach ($encomiendas as $encomienda) {
                        $ids[] = $encomienda->getId();
                        if($tipoDocumento === null){
                            $tipoDocumento = $encomienda->getTipoDocumento();
                        }
                    }
                    $info = "Se detectaron las encomiendas con identificadores: " . implode(",", $ids) . " como parte de la transacción. ";
                    if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                        return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaEncomienda', array(
                            'request'  => $request,
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }else{
                        return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                            'mensajeServidor' => "m0",
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }
                }
            }
            //--------------------------------------------------------------------------------------------------------
            //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LA ENCOMIENDA - END
            //--------------------------------------------------------------------------------------------------------
            
            $empresa = null;
            $encomiendas = array();
            $operacionesCaja = array();
            try {
                $result = $this->convertirModeloEnEncomiendas($registrarEncomiendaModel, $erroresAux);
                if($erroresAux !== null && count($erroresAux) != 0){
                    return UtilService::returnError($this, $erroresAux->getIterator()->current()->getMessage());        
                }
                if(isset($result["empresa"]))
                    $empresa = $result["empresa"];
                if(isset($result["listaEncomiendas"]))
                    $encomiendas = $result["listaEncomiendas"];
                if(isset($result["operacionesCaja"]))
                    $operacionesCaja = $result["operacionesCaja"];
                
            } catch (\RuntimeException $exc) {
                $textError = $exc->getCode() === RuntimeExceptionCode::VALIDACION ? $exc->getMessage() : "Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $textError);
            } catch (\Exception $exc) {
                var_dump($exc);
                $textError = $exc->getCode() === RuntimeExceptionCode::VALIDACION ? $exc->getMessage() : "Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $textError);
            } 
            
            if(is_null($empresa)){
                return UtilService::returnError($this, "No se pudo obtener la empresa.");
            }
            
//            var_dump("Cantidad de encomiendas:". count($encomiendas));
//            var_dump("Cantidad de cajas:". count($operacionesCaja));
            
            if($registrarEncomiendaModel->getTipoDocuemento()->getId() === TipoDocumentoEncomienda::FACTURA){
                
                //VALORES TOTALES DE LA FACTURA - INIT
                $serieFactura = $registrarEncomiendaModel->getSerieFactura();
                $tipoPago = $registrarEncomiendaModel->getTipoPago();
                $totalNeto = $registrarEncomiendaModel->getTotalNeto();
                $referenciaExterna = $registrarEncomiendaModel->getReferenciaExterna();
                $monedaPago = $registrarEncomiendaModel->getMonedaPago();
                $tasa = $registrarEncomiendaModel->getTasa();
                $totalPago = $registrarEncomiendaModel->getTotalPago();
                $efectivo = $registrarEncomiendaModel->getEfectivo();
                $vuelto = $registrarEncomiendaModel->getVuelto();
                //VALORES TOTALES DE LA FACTURA - END
                
                
                //--------------------------  FACTURA INIT ----------------------------------
                $facturaGenerada = new FacturaGenerada();
                $totalPrecioCalculadoMonedaBase = 0;
                foreach ($encomiendas as $encomienda) {
                    $encomienda->setFacturaGenerada($facturaGenerada);
                    $totalPrecioCalculadoMonedaBase += $encomienda->getPrecioCalculadoMonedaBase();
                }
                
                $monedaFactura = null;
                $tipoCambioFactura = null;
                $importeTotalFactura = null;
                if($tipoPago->getId() === TipoPago::EFECTIVO){
                    if($monedaPago === null){
                        return UtilService::returnError($this, "No se ha definido la moneda de pago.");
                    }
                    else{
                        $monedaFactura = $monedaPago;
                        $tipoCambio = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaPago->getId());
                        if($tipoCambio === null){
                            $error ="No se pudo obtener un tipo de cambio para la moneda:" . $monedaPago->getSigla() . ".";
                            return UtilService::returnError($this, $error);
                        }
                        else{
                            $tipoCambioFactura = $tipoCambio;
                            $tasa = $tipoCambio->getTasa();
                            $precioCalculado =  $totalPrecioCalculadoMonedaBase / $tasa;
                            $precioCalculado = round($precioCalculado, 2, PHP_ROUND_HALF_UP);
                            $importeTotalFactura = $precioCalculado;
                        }
                    }
                 }
                 else{  //TipoPago::TARJETA_CREDITO TipoPago::TARJETA_DEBITO  TipoPago::TARJETA
                    $monedaFactura = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ);
                    $tipoCambioFactura = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaFactura);
                    $importeTotalFactura = $totalPrecioCalculadoMonedaBase;
                 }
                    
                 $estacionUsuario = $this->getUser()->getEstacion();
                 //Cuando se comparan float no se utiliza !== sino !=
                 if($importeTotalFactura != 0){
//                    var_dump("Generando factura con importe:" . $importeTotalFactura);
                    $facturaGenerada->setFactura($serieFactura);
                    $facturaGenerada->setMoneda($monedaFactura);
                    $facturaGenerada->setReferenciaExterna($referenciaExterna);
                    $facturaGenerada->setTipoCambio($tipoCambioFactura);
                    $facturaGenerada->setImporteTotal($importeTotalFactura);
                    $facturaGenerada->setUsuario($this->getUser());
                    $facturaGenerada->setEstacion($this->getUser()->getEstacion());
                    $facturaGenerada->setServicioEstacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ServicioEstacion')->find(ServicioEstacion::ENCOMIENDA));
                    $facturaGenerada->setConsecutivo(null); //Existe un triger en la BD que lo va a asignar
                 }else{
                    $error = "Se está intentando crear un encomienda nueva con una factura en importe cero.";
                    return UtilService::returnError($this, $error);
                 }
                //--------------------------  FACTURA END ----------------------------------
                
                //--------------------------  CAJA INIT ----------------------------------
                $user = $this->getUser();
                $cajaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaPago);
                if($cajaPago === null){
                    $error = 'Para registrar el pago se requiere que el usuario tenga una caja abierta en la moneda:' . $monedaPago->getSigla() . ".";
                    return UtilService::returnError($this, $error);
                }else{
                    $em = $this->getDoctrine()->getManager();
                    $em->refresh($cajaPago);
                    $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA);
                    $operacionCaja = new OperacionCaja();
                    $operacionesCaja[] = $operacionCaja;
                    $operacionCaja->setCaja($cajaPago);
                    $operacionCaja->setEmpresa($empresa);
                    $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                    $operacionCaja->setFecha(new \DateTime());
                    if($tipoPago->getId() === TipoPago::TARJETA_CREDITO || $tipoPago->getId() === TipoPago::TARJETA_DEBITO  || $tipoPago->getId() === TipoPago::TARJETA){
                        $operacionCaja->setImporte(-1 * abs($totalNeto));
                    }else{
                        //Efectivo
                        if($monedaPago->getId() === Moneda::GTQ || $monedaPago->getId() === "1"){ //QUETSALES
                            $operacionCaja->setImporte(-1 * abs($totalNeto));
                        }else{
                            //USD Y EUR
                            $operacionCaja->setImporte(-1 * abs($efectivo)); //REGISTRO TODO EL EFECTIVO QUE ENTRO
                            $vuelto = floatval($vuelto);
                            if($vuelto != 0){
                                $monedaVuelto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
                                $cajaVuelto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaVuelto);
                                if($cajaVuelto === null){
                                    $error = 'Para registrar el vuelto se requiere que el usuario tenga una caja abierta en la moneda:' . $monedaVuelto->getSigla() . ".";
                                    return UtilService::returnError($this, $error);
                                }else{
                                    $em = $this->getDoctrine()->getManager();
                                    $em->refresh($cajaVuelto);
                                    $operacionCajaVuelto = new OperacionCaja();
                                    $operacionesCaja[] = $operacionCajaVuelto;
                                    $operacionCajaVuelto->setCaja($cajaVuelto);
                                    $operacionCajaVuelto->setEmpresa($empresa);
                                    $operacionCajaVuelto->setTipoOperacion($tipoOperacionCaja);
                                    $operacionCajaVuelto->setFecha(new \DateTime());
                                    $operacionCajaVuelto->setImporte(abs($vuelto)); //+ es salida
                                    $descripcion = "Salida por entrega de vuelto por registro de encomienda.";
                                    $operacionCajaVuelto->setDescripcion($descripcion);
                                }
                            }
                        }
                    }
                    $descripcion = "Ingreso por envio de encomienda.";
                    $operacionCaja->setDescripcion($descripcion);
                }
                
                //--------------------------  CAJA END ----------------------------------
            }
            
            //--------------------------  VALIDACIONES INIT ----------------------------------
            foreach ($encomiendas as $encomienda) {
                $erroresItems = $this->get('validator')->validate($encomienda);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                if($encomienda->getEventos() !== null){
                    foreach ($encomienda->getEventos() as $encomiendaBitacora) {
                        $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                        if($erroresItems !== null && count($erroresItems) != 0){
                            return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                        }
                    }
                }
            }
            foreach ($operacionesCaja as $operacionCaja) {
                $erroresItems = $this->get('validator')->validate($operacionCaja);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
            
            //--------------------------  VALIDACIONES END ----------------------------------
            
            // VALIDANDO QUE LA IMPRESORA EXISTA ------------------INIT----------------
            $estacionUsuario = $this->getUser()->getEstacion();
            $autoPrintEncomienda = false;
            
            $tipoDocumento = $registrarEncomiendaModel->getTipoDocuemento();
            if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                $impresora = $registrarEncomiendaModel->getSerieFactura()->getImpresora();
                if($impresora === null){
                    $error = "m1La impresora de facturación no está definida en el sistema.";
                    return UtilService::returnError($this, $error); 
                }else{
                    $autoPrintEncomienda = $impresora->getAutoPrint();
                    $impresorasDisponibles = $registrarEncomiendaModel->getImpresorasDisponibles();
                    if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                        $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                        return UtilService::returnError($this, $error); 
                    }
                }
            }
            
            //Siempre se chequea
            $impresoraOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ImpresoraOperaciones')
                                            ->getImpresoraOperacionesPorEstacion($estacionUsuario);
            if($impresoraOperaciones !== null){
                $impresora = $impresoraOperaciones->getImpresoraEncomienda();
                if($impresora !== null){
                    $impresorasDisponibles = $registrarEncomiendaModel->getImpresorasDisponibles();
                    if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                        $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                        return UtilService::returnError($this, $error); 
                    }
                }
            }
            // VALIDANDO QUE LA IMPRESORA EXISTA ------------------END
            
            
            if ($form->isValid() && count($erroresAux) === 0) {
//                var_dump("xxxx1");
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $facturaGenerada = null;
                    foreach ($encomiendas as $encomienda) {
                        if($encomienda->getFacturaGenerada() !== null){
                            $facturaGenerada = $encomienda->getFacturaGenerada();
                            $em->persist($facturaGenerada);
                        }
                        $em->persist($encomienda);
                    }
                    foreach ($operacionesCaja as $operacionCaja) {
                        $em->persist($operacionCaja);
                    }
                    $em->flush();
                    
                    $ids = array();
                    foreach ($encomiendas as $encomienda) {
                        $ids[] = $encomienda->getId();
                    }
                    
                    $info = "Se crearon las encomiendas con identificadores: " . implode(",", $ids) . ". ";
                    $facturaStr = "";
                    if($facturaGenerada !== null){
                        $em->refresh($facturaGenerada); //Recargar el consecutivo de la factura que lo asigno un trigger 
                        $factura = $facturaGenerada->getFactura();
                        $em->refresh($factura);
                        $facturaGenerada->validar();
                        $facturaStr = $facturaGenerada->getInfo2();
                        $info .= "Se va a imprimir la factura: " . $facturaStr . ".";
                    }

                    foreach ($operacionesCaja as $operacionCaja) {
                        $caja = $operacionCaja->getCaja();
                        $sobregirada = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->checkSobregiroCaja($caja);
                        if($sobregirada === true){
                            throw new \RuntimeException("m1No se puede realizar la operación porque la caja en la moneda: " . $caja->getMoneda()->getSigla() . " no cuenta con suficiente efectivo.");
                        }
                    }
                    
                    if(count($operacionesCaja) > 0){
                        $operacionCaja = $operacionesCaja[0];
                        $descripcion = $operacionCaja->getDescripcion() . " Factura: " . $facturaStr . ".";
                        $operacionCaja->setDescripcion($descripcion);
                        $em->persist($operacionCaja);
                        $em->flush();
                    }
                    
                    $em->getConnection()->commit();
                    
                    if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                        return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaEncomienda', array(
                            'request'  => $request,
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }else{
                        return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                            'mensajeServidor' => "m0",
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }
                    
                } catch (\RuntimeException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\ErrorException $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               if($error !== null && $error !== ""){
                   $mensajeServidor = "m1" . $error;
                   return UtilService::returnError($this, $mensajeServidor);
               }else{
                   foreach ($erroresAux as $item) {
                      $mensajeServidor = $item->getMessage();
                      if(!UtilService::startsWith($mensajeServidor, "m1")){
                          $mensajeServidor = "m1" . $mensajeServidor;
                      }
                      return UtilService::returnError($this, $mensajeServidor);
                   }
               }
            }
        }
        
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Encomienda:registrar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
        
        if($request->isMethod('GET')){
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        }
        
        return $respuesta;
    }
    
    private function convertirModeloEnEncomiendas(RegistrarEncomiendaModel $registrarEncomiendaModel, ConstraintViolationList $erroresAux) {
        
        $empresa = null;
        $encomiendas = array();
        $operacionesCaja = array();
        $listaRutasHidden = $registrarEncomiendaModel->getListaEncomiendaRutas();
         if($listaRutasHidden === null || trim($listaRutasHidden) === ""){
            $error = "Debe definir una ruta.";
            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
            return array();
        }
        $listaRutasJson = json_decode($listaRutasHidden);
        if($listaRutasJson === null){
            $error = "Debe definir una ruta.";
            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
            return array();
        }
        
        $listaEncomiendasHidden = $registrarEncomiendaModel->getListaEncomiendas();
        $listaEncomiendasJson = json_decode($listaEncomiendasHidden);
        if($listaEncomiendasJson !== null){
            
            //VALORES TOTALES DE LA FACTURA - INIT
            $tipoPago = $registrarEncomiendaModel->getTipoPago();
            $totalNeto = $registrarEncomiendaModel->getTotalNeto();
            $monedaPago = $registrarEncomiendaModel->getMonedaPago();
            $tasa = $registrarEncomiendaModel->getTasa();
            $totalPago = $registrarEncomiendaModel->getTotalPago();
            $efectivo = $registrarEncomiendaModel->getEfectivo();
            $vuelto = $registrarEncomiendaModel->getVuelto();
            //VALORES TOTALES DE LA FACTURA - END
            
            $estacionRepository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion');
            $rutaRepository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Ruta');
            $tipoEncomiendaRepository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoEncomienda');
            $autorizacionCortesiaRepository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionCortesia');
            $autorizacionInternaRepository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionInterna');
            $estadoEncomiendaRepository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda');
            $existeEfectivo = false;
            $cantidadItems = 0;
            foreach ($listaEncomiendasJson as $json) {
                if(strval($json->tipoEncomienda) === TipoEncomienda::EFECTIVO){
                    if($existeEfectivo === false){
                        $existeEfectivo = true;
                    }else{
                        $error = "Solamente puede registrar una encomienda de efectivo por operación.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                    $cantidadItems += 1;
                }else{
                    $cantidadItems += intval($json->cantidad);
                }
            }
//            var_dump("CantidadTotal: " . $cantidadItems);
            foreach ($listaEncomiendasJson as $json) {
                $tipoDocumento = $registrarEncomiendaModel->getTipoDocuemento();
                $tipoEncomienda = $tipoEncomiendaRepository->find($json->tipoEncomienda);
                $encomienda = new Encomienda();
                $encomienda->setIdentificadorWeb($registrarEncomiendaModel->getIdentificadorWeb());
                $encomienda->setTipoDocumento($tipoDocumento);
                $encomienda->setTipoEncomienda($tipoEncomienda);
                $encomienda->setCantidad(intval($json->cantidad));
                $encomienda->setDescripcion($json->descripcion);
                $encomienda->setCodigoExternoCliente($registrarEncomiendaModel->getCodigoExternoCliente());
                $encomienda->setClienteRemitente($registrarEncomiendaModel->getClienteRemitente());
                $encomienda->setClienteDestinatario($registrarEncomiendaModel->getClienteDestinatario());
                $encomienda->setEstacionOrigen($registrarEncomiendaModel->getEstacionOrigen());
                $encomienda->setEstacionDestino($registrarEncomiendaModel->getEstacionDestino());
                $encomienda->setUsuarioCreacion($this->getUser());
                $encomienda->setEstacionCreacion($this->getUser()->getEstacion());
                $encomienda->setBoleto($registrarEncomiendaModel->getBoleto());
                $encomienda->setFechaCreacion(new \DateTime());
                $encomiendaBitacora = new EncomiendaBitacora();
                $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                $encomiendaBitacora->setUsuario($this->getUser());
                $encomiendaBitacora->setFecha(new \DateTime());
                $encomiendaBitacora->setEstado($estadoEncomiendaRepository->find(EstadoEncomienda::RECIBIDA));
                $encomienda->addEventos($encomiendaBitacora);
                    
                $valorDeclarado = 0;
                if($json->valorDeclarado !== null && trim($json->valorDeclarado) !== "")
                {
                    if(!is_numeric($json->valorDeclarado)){
                        $error = "El valor declarado no es númerico.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                    $valorDeclarado = intval($json->valorDeclarado);
                }
                $encomienda->setValorDeclarado($valorDeclarado);
                
                $primeraRuta = null;
                foreach ($listaRutasJson as $rutaJson) {
                    $encomiendaRuta = new EncomiendaRuta();
                    $encomiendaRuta->setPosicion($rutaJson->posicion);
                    $ruta = $rutaRepository->find($rutaJson->rutaVirtual);
                    if($ruta === null){
                        $error = "La ruta con identificador: " . $rutaJson->rutaVirtual . " no se encontro en el sistema.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                    $encomiendaRuta->setRuta($ruta);
                    if(is_null($primeraRuta)){
                        $primeraRuta = $ruta;
                    }
                    $estacionDestino = $estacionRepository->find($rutaJson->estacionFinalVirtual);
                    if($estacionDestino === null){
                        $error = "La estación con identificador: " . $rutaJson->rutaVirtual . " no se encontro en el sistema.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                    $encomiendaRuta->setEstacionDestino($estacionDestino);
                    $encomienda->addRutas($encomiendaRuta);
                }
                
                if(is_null($primeraRuta) || !($primeraRuta instanceof Ruta)){
                    $error = "No se pudo determinar la ruta de la encomienda.";
                    $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                    return array();
                }
                
                $registrarEncomiendaModel->setRuta($primeraRuta);
                $encomienda->setRuta($primeraRuta);
                
                if(is_null($empresa))
                {
                    $fechaDia = new \DateTime(); //a Factura de la encomienda es con la fecha del dia.
                    $empresa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                            ->getEmpresaQueFactura($primeraRuta, $fechaDia);
                    if($empresa === null){
                        $error = "No se pudo determinar que empresa factura el día:". $fechaDia->format('d-m-Y') . ", en la ruta:" . $primeraRuta->__toString() . ".";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                }
                $encomienda->setEmpresa($empresa);
                
                if($tipoEncomienda->getId() === TipoEncomienda::EFECTIVO){
                    $encomienda->setCodigo($this->get('acme_backend_util')->generatePin("ENEF"));
                    //Se crea la operacion de caja donde se registra la entrada de efectivo
                    $user = $this->getUser();
                    $moneda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
                    $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $moneda);
                    if($caja === null){
                        $error = "Para enviar el efectivo se requiere que el usuario tenga una caja abierta en la moneda:" . $moneda->getSigla() . "." ;
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }else{
                        $em = $this->getDoctrine()->getManager();
                        $em->refresh($caja);
                        $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA);
                        $operacionCaja = new OperacionCaja();
                        $operacionCaja->setCaja($caja);
                        $operacionCaja->setEmpresa($empresa);
                        $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                        $operacionCaja->setFecha(new \DateTime());
                        $importe = doubleval($encomienda->getCantidad());
                        $operacionCaja->setImporte(-1 * abs($importe));
                        $operacionCaja->setDescripcion("Registro del envío de encomienda de efectivo.");
                        $operacionesCaja[] = $operacionCaja;    
                    }
                }
                else if($tipoEncomienda->getId() === TipoEncomienda::ESPECIAL){
                    $encomienda->setCodigo($this->get('acme_backend_util')->generatePin("ENES"));
                    $tipoEncomiendaEspecial = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoEncomiendaEspeciales')->find($json->tipoEncomiendaEspecial);
                    $encomienda->setTipoEncomiendaEspecial($tipoEncomiendaEspecial);
                    $descripcion = $encomienda->getDescripcion();
                    $descripcion = "Producto: " .$encomienda->getTipoEncomiendaEspecial()->getNombre() . ". ". $descripcion;
                    $encomienda->setDescripcion($descripcion);
                }
                else if($tipoEncomienda->getId() === TipoEncomienda::PAQUETE){
                    $encomienda->setCodigo($this->get('acme_backend_util')->generatePin("ENPA"));
                    $encomienda->setAlto($json->volumenAlto);
                    $encomienda->setAncho($json->volumenAncho);
                    $encomienda->setProfundidad($json->volumenProfundidad);
                    $encomienda->setVolumen($json->volumen);
                    $encomienda->setPeso($json->peso);
                    $descripcion = $encomienda->getDescripcion();
                    $descripcion = "Dimensiones(cm):".$json->volumenAlto."x".$json->volumenAncho."x".$json->volumenProfundidad.". Peso(lb): " . $json->peso . ". " . $descripcion;
                    $encomienda->setDescripcion($descripcion);
                }

                if($tipoDocumento->getId() === TipoDocumentoEncomienda::AUTORIZACION_CORTESIA){
                    $pinAutorizacionCortesia = $registrarEncomiendaModel->getAutorizacionCortesia();
                    if($pinAutorizacionCortesia !== null && trim($pinAutorizacionCortesia) !== ""){
                        //Si se trae uno que ya este asociado a otra encomienda, se valida cuando se chequea en encomienda que la columna sea unica.
                        $autorizacionCortesia = $autorizacionCortesiaRepository->findOneBy(array(
                            'codigo' => $pinAutorizacionCortesia, 
                            'activo' => true
                        ));
                        if($autorizacionCortesia === null){
                            $error = "El PIN de la autorización de cortesía no es válido.";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $encomienda->setAutorizacionCortesia($autorizacionCortesia);
                        $autorizacionCortesia->setUsuarioUtilizacion($this->getUser());
                        $autorizacionCortesia->setFechaUtilizacion(new \DateTime());
                    }else{
                        $error = "No se ha definido el PIN de la autorización de cortesía.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                }
                else if($tipoDocumento->getId() === TipoDocumentoEncomienda::AUTORIZACION_INTERNA){
                    $pinAutorizacionInterna = $registrarEncomiendaModel->getAutorizacionInterna();
                    if($pinAutorizacionInterna !== null && trim($pinAutorizacionInterna) !== ""){
                        //Si se trae uno que ya este asociado a otra encomienda, se valida cuando se chequea en encomienda que la columna sea unica.
                        $autorizacionInterna = $autorizacionInternaRepository->findOneBy(array(
                            'codigo' => $pinAutorizacionInterna, 
                            'activo' => true
                        ));
                        if($autorizacionInterna === null){
                            $error = "El PIN de la autorización interna no es válido.";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $encomienda->setAutorizacionInterna($autorizacionInterna);
                        $autorizacionInterna->setUsuarioUtilizacion($this->getUser());
                        $autorizacionInterna->setFechaUtilizacion(new \DateTime());
                    }else{
                        $error = "No se ha definido el PIN de la autorización de interna.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                }
                else if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                    
                    $tarifaValor = 0;
                    $tarifaEncomienda1 = null;
                    $tarifaEncomienda2 = null;
                    $tarifaDistancia = null;
                    if($tipoEncomienda->getId() === TipoEncomienda::EFECTIVO){
                        $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEfectivo($json->cantidad);
                        if($tarifaEncomienda1 === null){
                            $error = "No se ha definido una tarifa en el sistema para esa cantidad de efectivo.";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa();
                        $tarifa_efectivo_por_distancia = $this->container->getParameter("tarifa_efectivo_por_distancia");
                        if(isset($tarifa_efectivo_por_distancia) && $tarifa_efectivo_por_distancia === true){
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia(
                                $registrarEncomiendaModel->getEstacionOrigen(), 
                                $registrarEncomiendaModel->getEstacionDestino());
                            if($tarifaDistancia === null){
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación " . $registrarEncomiendaModel->getEstacionOrigen()->getNombre() .
                                            " a " . $registrarEncomiendaModel->getEstacionDestino()->getNombre();
                                $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                return array();
                            }
                            $tarifaValor += $tarifaDistancia->calcularTarifa();
                        }
                   }
                   else if($tipoEncomienda->getId() === TipoEncomienda::ESPECIAL){
                        $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEspeciales($json->tipoEncomiendaEspecial);
                        if($tarifaEncomienda1 === null){
                            $tipoEncomiendaEspecial = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoEncomiendaEspeciales')->find($json->tipoEncomiendaEspecial);
                            $error = "No se ha definido una tarifa en el sistema para la encomienda: " . $tipoEncomiendaEspecial->getNombre() . ".";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa();
                        $tarifa_especial_por_distancia = $this->container->getParameter("tarifa_especial_por_distancia");
                        if(isset($tarifa_especial_por_distancia) && $tarifa_especial_por_distancia === true){
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia( 
                                $registrarEncomiendaModel->getEstacionOrigen(), 
                                $registrarEncomiendaModel->getEstacionDestino());
                            if($tarifaDistancia === null){
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación " . $registrarEncomiendaModel->getEstacionOrigen()->getNombre() .
                                            " a " . $registrarEncomiendaModel->getEstacionDestino()->getNombre();
                                $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                return array();
                            }
                            $tarifaValor += $tarifaDistancia->calcularTarifa();
                        }
                        $tarifaValor = $encomienda->getCantidad() * $tarifaValor;
                    }
                    else if($tipoEncomienda->getId() === TipoEncomienda::PAQUETE){
                        $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesVolumen($json->volumen);
                        if($tarifaEncomienda1 === null){
                            $error = "No se ha definido una tarifa en el sistema para el volumen " . $json->volumen . ".";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $tarifaEncomienda2 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesPeso($json->peso);
                        if($tarifaEncomienda2 === null){
                            $error = "No se ha definido una tarifa en el sistema para el peso " . $json->peso . ".";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa() + $tarifaEncomienda2->calcularTarifa();
                        
                        $tarifa_paquete_por_distancia = $this->container->getParameter("tarifa_paquete_por_distancia");
                        if(isset($tarifa_paquete_por_distancia) && $tarifa_paquete_por_distancia === true){
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia(
                                $registrarEncomiendaModel->getEstacionOrigen(), 
                                $registrarEncomiendaModel->getEstacionDestino());
                            if($tarifaDistancia === null){
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación " . $registrarEncomiendaModel->getEstacionOrigen()->getNombre() .
                                            " a " . $registrarEncomiendaModel->getEstacionDestino()->getNombre();
                                $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                return array();
                            }
                            $tarifaValor += $tarifaDistancia->calcularTarifa();
                        }
                        $tarifaValor = $encomienda->getCantidad() * $tarifaValor;
                    }
                    
                    if($tarifaEncomienda1 === null){
                        $error = "No se ha definido una tarifa en el sistema.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                        
                    if($valorDeclarado !== 0){
                        $encomienda_porciento_valor_declarado = $this->container->getParameter("encomienda_porciento_valor_declarado");
                        if(isset($encomienda_porciento_valor_declarado) && is_numeric($encomienda_porciento_valor_declarado)){
                            $porcientoValorDeclarado = floatval($encomienda_porciento_valor_declarado);
                            $encomienda->setPorcientoValorDeclarado($porcientoValorDeclarado);
                            $seguro = $valorDeclarado * $porcientoValorDeclarado;
                            $tarifaValor = $tarifaValor + $seguro;
                        }
                    }
                    
                    $encomienda->setTarifa1($tarifaEncomienda1);
                    $encomienda->setTarifa2($tarifaEncomienda2);
                    $encomienda->setTarifaDistancia($tarifaDistancia);
                    
                    $tarifaValor = UtilService::aplicarDescuento($tarifaValor, $cantidadItems);
                    $encomienda->setPrecioCalculadoMonedaBase($tarifaValor);
                        
                    //No se completa mas ningun paramatro pq el resto de la informacion es de la factura, que es cuando el cliente va a recogerla.

                 }else if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                    
                    $encomienda->setClienteDocumento($encomienda->getClienteRemitente());
                     
                    if($tipoPago === null){
                        $tipoPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoPago')->find(TipoPago::EFECTIVO);
                    }
                    $encomienda->setTipoPago($tipoPago);
                        
                    $tarifaValor = 0;
                    $tarifaEncomienda1 = null;
                    $tarifaEncomienda2 = null;
                    $tarifaDistancia = null;
                    if($tipoEncomienda->getId() === TipoEncomienda::EFECTIVO){
                        $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEfectivo($json->cantidad);
                        if($tarifaEncomienda1 === null){
                            $error = "No se ha definido una tarifa en el sistema para esa cantidad de efectivo.";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa();
                        $tarifa_efectivo_por_distancia = $this->container->getParameter("tarifa_efectivo_por_distancia");
                        if(isset($tarifa_efectivo_por_distancia) && $tarifa_efectivo_por_distancia === true){
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia(
                                $registrarEncomiendaModel->getEstacionOrigen(), 
                                $registrarEncomiendaModel->getEstacionDestino());
                            if($tarifaDistancia === null){
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación " . $registrarEncomiendaModel->getEstacionOrigen()->getNombre() .
                                            " a " . $registrarEncomiendaModel->getEstacionDestino()->getNombre();
                                $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                return array();
                            }
                            $tarifaValor += $tarifaDistancia->calcularTarifa();
                        }
                   }
                   else if($tipoEncomienda->getId() === TipoEncomienda::ESPECIAL){
                        $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEspeciales($json->tipoEncomiendaEspecial);
                        if($tarifaEncomienda1 === null){
                            $tipoEncomiendaEspecial = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoEncomiendaEspeciales')->find($json->tipoEncomiendaEspecial);
                            $error = "No se ha definido una tarifa en el sistema para la encomienda: " . $tipoEncomiendaEspecial->getNombre() . ".";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa();
                        $tarifa_especial_por_distancia = $this->container->getParameter("tarifa_especial_por_distancia");
                        if(isset($tarifa_especial_por_distancia) && $tarifa_especial_por_distancia === true){
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia( 
                                $registrarEncomiendaModel->getEstacionOrigen(), 
                                $registrarEncomiendaModel->getEstacionDestino());
                            if($tarifaDistancia === null){
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación " . $registrarEncomiendaModel->getEstacionOrigen()->getNombre() .
                                            " a " . $registrarEncomiendaModel->getEstacionDestino()->getNombre();
                                $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                return array();
                            }
                            $tarifaValor += $tarifaDistancia->calcularTarifa();
                        }
                        $tarifaValor = $encomienda->getCantidad() * $tarifaValor;
                    }
                    else if($tipoEncomienda->getId() === TipoEncomienda::PAQUETE){
                        $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesVolumen($json->volumen);
                        if($tarifaEncomienda1 === null){
                            $error = "No se ha definido una tarifa en el sistema para el volumen " . $json->volumen . ".";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $tarifaEncomienda2 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesPeso($json->peso);
                        if($tarifaEncomienda2 === null){
                            $error = "No se ha definido una tarifa en el sistema para el peso " . $json->peso . ".";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa() + $tarifaEncomienda2->calcularTarifa();
                        
                        $tarifa_paquete_por_distancia = $this->container->getParameter("tarifa_paquete_por_distancia");
                        if(isset($tarifa_paquete_por_distancia) && $tarifa_paquete_por_distancia === true){
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia(
                                $registrarEncomiendaModel->getEstacionOrigen(), 
                                $registrarEncomiendaModel->getEstacionDestino());
                            if($tarifaDistancia === null){
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación " . $registrarEncomiendaModel->getEstacionOrigen()->getNombre() .
                                            " a " . $registrarEncomiendaModel->getEstacionDestino()->getNombre();
                                $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                return array();
                            }
                            $tarifaValor += $tarifaDistancia->calcularTarifa();
                        }
                        $tarifaValor = $encomienda->getCantidad() * $tarifaValor;
                    }
                    
                    if($tarifaEncomienda1 === null){
                        $error = "No se ha definido una tarifa en el sistema.";
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return array();
                    }
                        
                    if($valorDeclarado !== 0){
                        $encomienda_porciento_valor_declarado = $this->container->getParameter("encomienda_porciento_valor_declarado");
                        if(isset($encomienda_porciento_valor_declarado) && is_numeric($encomienda_porciento_valor_declarado)){
                            $porcientoValorDeclarado = floatval($encomienda_porciento_valor_declarado);
                            $encomienda->setPorcientoValorDeclarado($porcientoValorDeclarado);
                            $seguro = $valorDeclarado * $porcientoValorDeclarado;
                            $tarifaValor = $tarifaValor + $seguro;
                        }
                    }
                    
                    $encomienda->setTarifa1($tarifaEncomienda1);
                    $encomienda->setTarifa2($tarifaEncomienda2);
                    $encomienda->setTarifaDistancia($tarifaDistancia);
                    
                    $tarifaValor = UtilService::aplicarDescuento($tarifaValor, $cantidadItems);
                    $encomienda->setPrecioCalculadoMonedaBase($tarifaValor);
                    
                    if($tipoPago->getId() === TipoPago::EFECTIVO){
                        if($monedaPago === null){
                            $error = "No se ha definido la moneda de pago.";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $encomienda->setMoneda($monedaPago);
                        $tipoCambio = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaPago->getId());
                        if($tipoCambio === null){
                            $error = "No se pudo obtener un tipo de cambio para la moneda:" . $monedaPago->getSigla() . ".";
                            $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                            return array();
                        }
                        $encomienda->setTipoCambio($tipoCambio);
                        $tasa = $tipoCambio->getTasa();
                        $precioCalculado =  $encomienda->getPrecioCalculadoMonedaBase() / $tasa;
                        $precioCalculado = round($precioCalculado, 2, PHP_ROUND_HALF_UP);
                        $encomienda->setPrecioCalculado($precioCalculado);
                    }
                    else{  //TipoPago::TARJETA_CREDITO TipoPago::TARJETA_DEBITO TipoPago::TARJETA
                        $encomienda->setMoneda($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ));
                        $encomienda->setTipoCambio($this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($encomienda->getMoneda()));
                        $encomienda->setPrecioCalculado($encomienda->getPrecioCalculadoMonedaBase());
                    }
                }
                
                $encomiendas[] = $encomienda;
            }
            
        }
        
        return array(
            "empresa" => $empresa,
            "listaEncomiendas" => $encomiendas,
            "operacionesCaja" => $operacionesCaja,
        );
        
    }
    
    
    /**
     * @Route(path="/embarcarEncomienda.html", name="embarcarEncomienda-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function embarcarEncomiendaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('embarcar_encomienda_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $encomienda = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
        if (!$encomienda) {
            return UtilService::returnError($this, "m1La encomienda con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new EmbarcarEncomiendaType($this->getDoctrine()), $encomienda, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));  
        
        $mensajeServidor = "";
//        if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
//            $mensajeServidor = "m1Las encomiendas de efectivo solamente se pueden entregar.";  
//            return UtilService::returnError($this, $mensajeServidor);
//        }
        
        $ultimoEstado = $encomienda->getUltimoEstado();
        if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
            $mensajeServidor = "m1Solamente se puede embarcar una encomienda que este en estado recibida o desembarcada. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        //NOTA: LAS ESTACIONES QUE PUEDEN REALIZAR ESTA ACCION ESTA VALIDADA A NIVEL DE BUSCADOR QUE FILTRA TENIENDO EN CUENTA LA ESTACION
        if($ultimoEstado->getId() ===  EstadoEncomienda::RECIBIDA ){
            if($encomienda->getEstacionCreacion() !== $this->getUser()->getEstacion()){
                $mensajeServidor = "m1Solamente puede embarcar la encomienda un usuario de la estación: " . $encomienda->getEstacionCreacion()->__toString() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }

        if($encomienda->getEstacionCreacion() !== null){
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                $mensajeServidor = "Para embarcar una encomienda el usuario debe estar asociado a la estación de origen de la encomienda: " . $encomienda->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias del envío.";
                return UtilService::returnError($this, $mensajeServidor);
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
                    return UtilService::returnError($this, $mensajeServidor);
                 }
            }
        }
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            $salida = $form->get('salida')->getData();
            if($salida === null){
                return UtilService::returnError($this, "m1No se pudo obtener la salida.");
            }else{
                
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
                    $mensajeServidor = "m1Ruta de la encomienda incorrecta. La encomienda solamente se puede embarcar en las rutas: " . implode(",", $secuenciaRutas) . "."; 
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
                if($encomienda->getEmpresa()->getId() !== $salida->getEmpresa()->getId()){
                    $mensajeServidor = "Está intentando abordar la encomienda con identificador " . $id . " asociada a la empresa " .
                        $encomienda->getEmpresa()->getAlias() . " en una salida de la empresa: " . $salida->getEmpresa()->getAlias() . ".";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
                $encomiendaBitacora = new EncomiendaBitacora();
                $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                $encomiendaBitacora->setUsuario($this->getUser());
                $encomiendaBitacora->setFecha(new \DateTime());
                $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::EMBARCADA));
                $encomiendaBitacora->setSalida($salida);
                $encomienda->addEventos($encomiendaBitacora);
                
                $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                        
                if($salida->getEstado()->getId() === EstadoSalida::INICIADA){
                    $encomiendaBitacora = new EncomiendaBitacora();
                    $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                    $encomiendaBitacora->setUsuario($this->getUser());
                    $encomiendaBitacora->setFecha(new \DateTime());
                    $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::TRANSITO));
                    $encomiendaBitacora->setSalida($salida);
                    $encomienda->addEventos($encomiendaBitacora);
                    
                    $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                }
            }
            
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($encomienda);
                    $em->flush();
                    $em->getConnection()->commit();
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0"
                    ));
                    
                } catch (\RuntimeException $exc) {
//                    var_dump($exc);
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
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
                $error = UtilService::getErrorsToForm($form);       
                return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:embarcar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
     /**
     * @Route(path="/desembarcarEncomienda.html", name="desembarcarEncomienda-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function desembarcarEncomiendaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('desembarcar_encomienda_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $encomienda = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
        if (!$encomienda) {
            return UtilService::returnError($this, "m1La encomienda con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new DesembarcarEncomiendaType($this->getDoctrine()), $encomienda, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));  
        
        $mensajeServidor = "";
        $ultimoEstado = $encomienda->getUltimoEstado();
        if($ultimoEstado->getId() !== EstadoEncomienda::EMBARCADA && $ultimoEstado->getId() !== EstadoEncomienda::TRANSITO){
            $mensajeServidor = "m1Solamente se puede desembarcar una encomienda que este en estado embarcada o en transito. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
//        if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
//            $mensajeServidor = "m1Las encomiendas de efectivo solamente se pueden entregar.";
//            return UtilService::returnError($this, $mensajeServidor);
//        }
        
        if($encomienda->getEstacionCreacion() !== null){
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                $mensajeServidor = "Para desembarcar una encomienda el usuario debe estar asociado a una estación.";
                return UtilService::returnError($this, $mensajeServidor);
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
                    return UtilService::returnError($this, $mensajeServidor);
                 }
            }
        }
        
        //NOTA: LAS ESTACIONES QUE PUEDEN REALIZAR ESTA ACCION ESTA VALIDADA A NIVEL DE BUSCADOR QUE FILTRA TENIENDO EN CUENTA LA ESTACION
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $form->bind($request);
            
            $encomiendaBitacora = new EncomiendaBitacora();
            $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
            $encomiendaBitacora->setUsuario($this->getUser());
            $encomiendaBitacora->setFecha(new \DateTime());
            $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::DESEMBARCADA));
            $encomienda->addEventos($encomiendaBitacora);
                
            $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
            if($erroresItems !== null && count($erroresItems) != 0){
                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
            }
            
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($encomienda);
                    $em->flush();
                    $em->getConnection()->commit();
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0"
                    ));
                    
                } catch (\RuntimeException $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
                $error = UtilService::getErrorsToForm($form);
                return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:desembarcar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
     /**
     * @Route(path="/entregarEncomienda.html", name="entregarEncomienda-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function entregarEncomiendaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('entregar_encomienda_command'); //Submit
            if($command !== null){
                $id = $command["encomiendaOriginal"];
            }
        }
        
        $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
        if (!$encomienda) {
            return UtilService::returnError($this, "m1La encomienda con id: ".$id." no existe.");
        }
        
        $entregarEncomiendaModel = new EntregarEncomiendaModel();
        $entregarEncomiendaModel->setEncomiendaOriginal($encomienda);
        $form = $this->createForm(new EntregarEncomiendaType($this->getDoctrine()), $entregarEncomiendaModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));  
        
        $mensajeServidor = "";
        
        //SOLAMENTE SE PUEDEN ENTREGAR ENCOMIENDAS QUE ESTEN DESEMBARCADAS Y QUE SI SON DE EFECTIVO QUE ESTEN RECIBIDA        
        $ultimoEstado = $encomienda->getUltimoEstado();      
        if($ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
            $mensajeServidor = "m1Solamente se puede entregar la encomienda si está en estado desembarcada.";
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($encomienda->getEstacionDestino() !== $this->getUser()->getEstacion()){
            $mensajeServidor = "m1Solamente puede entregar la encomienda un usuario de la estación: " . $encomienda->getEstacionDestino()->__toString() . ".";
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $form->bind($request);
            
            $encomiendaBitacora = new EncomiendaBitacora();
            $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
            $encomiendaBitacora->setUsuario($this->getUser());
            $encomiendaBitacora->setFecha(new \DateTime());
            $encomiendaBitacora->setCliente($entregarEncomiendaModel->getClienteReceptor());
            $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::ENTREGADA));
            $encomienda->addEventos($encomiendaBitacora);
             
            $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
            if($erroresItems !== null && count($erroresItems) != 0){
                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
            }
            
            $operacionesCaja = array();
            $empresa = $encomienda->getEmpresa();
            
           if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
                //Se registra la salida de caja
                $user = $this->getUser();
                $moneda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
                $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $moneda);
                if($caja === null){
                    $mensajeServidor = "Para entregar el efectivo se requiere que el usuario tenga una caja abierta en la moneda: " . $moneda->getSigla() . ".";
                    return UtilService::returnError($this, $mensajeServidor);
                }else{
                    $em = $this->getDoctrine()->getManager();
                    $em->refresh($caja);
                    $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA);
                    $operacionCaja = new OperacionCaja();
                    $operacionCaja->setCaja($caja);
                    $operacionCaja->setEmpresa($empresa);
                    $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                    $operacionCaja->setFecha(new \DateTime());
                    $importe = doubleval($encomienda->getCantidad());
                    $operacionCaja->setImporte(abs($importe));
                    $operacionCaja->setDescripcion("Salida por el monto del envío de la encomienda de efectivo.");
                    $operacionesCaja[] = $operacionCaja;
                }
            }
                
            if($encomienda->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                    //VALORES TOTALES DE LA FACTURA - INIT
                    $serieFactura = $entregarEncomiendaModel->getSerieFactura();
                    $facturar = $entregarEncomiendaModel->getFacturar();
                    $tipoPago = $entregarEncomiendaModel->getTipoPago();
                    $totalNeto = $entregarEncomiendaModel->getTotalNeto();
                    $monedaPago = $entregarEncomiendaModel->getMonedaPago();
                    $tasa = $entregarEncomiendaModel->getTasa();
                    $totalPago = $entregarEncomiendaModel->getTotalPago();
                    $efectivo = $entregarEncomiendaModel->getEfectivo();
                    $vuelto = $entregarEncomiendaModel->getVuelto();
                    $clienteDocumento = $entregarEncomiendaModel->getClienteDocumento();
                    //VALORES TOTALES DE LA FACTURA - END

                    $totalPrecioCalculadoMonedaBase = $encomienda->getPrecioCalculadoMonedaBase();
                    $monedaFactura = null;
                    $tipoCambioFactura = null;
                    $importeTotalFactura = null;
                    if($tipoPago->getId() === TipoPago::EFECTIVO){
                        if($monedaPago === null){
                            $mensajeServidor = "No se ha definido la moneda de pago.";
                            return UtilService::returnError($this, $mensajeServidor);
                        }
                        else{
                            $monedaFactura = $monedaPago;
                            $tipoCambio = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaPago->getId());
                            if($tipoCambio === null){
                                $mensajeServidor = "No se pudo obtener un tipo de cambio para la moneda: " . $monedaPago->getSigla() . ".";
                                return UtilService::returnError($this, $mensajeServidor);
                            }
                            else{
                                $tipoCambioFactura = $tipoCambio;
                                $tasa = $tipoCambio->getTasa();
                                $precioCalculado =  $totalPrecioCalculadoMonedaBase / $tasa;
                                $precioCalculado = round($precioCalculado, 2, PHP_ROUND_HALF_UP);
                                $importeTotalFactura = $precioCalculado;
                            }
                        }
                     }
                     else{  //TipoPago::TARJETA_CREDITO TipoPago::TARJETA_DEBITO TipoPago::TARJETA
                        $monedaFactura = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ);
                        $tipoCambioFactura = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaFactura);
                        $importeTotalFactura = $totalPrecioCalculadoMonedaBase;
                     }
                     
                     $encomienda->setTipoPago($tipoPago);
                     $encomienda->setMoneda($monedaFactura);  
                     $encomienda->setTipoCambio($tipoCambioFactura);
                     $encomienda->setPrecioCalculado($importeTotalFactura);

                     if($facturar === true || $facturar === 'true'){
                         
                            if(is_null($clienteDocumento) || trim($clienteDocumento) === ""){
                                $error = "m1Debe definir el cliente documento.";
                                return UtilService::returnError($this, $error); 
                            }
                            //VALIDANDO QUE LA IMPRESORA PARA FACTURAR EXISTA ------------------INIT----------------
                            $impresora = $serieFactura->getImpresora();
                            if($impresora === null){
                                $error = "m1La impresora de facturación no está definida en el sistema.";
                                return UtilService::returnError($this, $error); 
                            }else{
                                $autoPrintEncomienda = $impresora->getAutoPrint();
                                $impresorasDisponibles = $entregarEncomiendaModel->getImpresorasDisponibles();
                                if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                                    $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                                    return UtilService::returnError($this, $error); 
                                }
                            }                          
                           // VALIDANDO QUE LA IMPRESORA PARA FACTURAR EXISTA ------------------END
                         
                           //--------------------------  FACTURA INIT ----------------------------------
                           //Cuando se comparan float no se utiliza !== sino !=
                           if($importeTotalFactura != 0){
//                                var_dump("Generando factura con importe:" . $importeTotalFactura);
                                $facturaGenerada = new FacturaGenerada();
                                $encomienda->setFacturaGenerada($facturaGenerada);
                                $facturaGenerada->setFactura($serieFactura);
                                $facturaGenerada->setMoneda($monedaFactura);
                                $facturaGenerada->setTipoCambio($tipoCambioFactura);
                                $facturaGenerada->setImporteTotal($importeTotalFactura);
                                $facturaGenerada->setUsuario($this->getUser());
                                $facturaGenerada->setEstacion($this->getUser()->getEstacion());
                                $facturaGenerada->setServicioEstacion($this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:ServicioEstacion')->find(ServicioEstacion::ENCOMIENDA));
                                $facturaGenerada->setConsecutivo(null); //Existe un triger en la BD que lo va a asignar
                           }else{
                                $mensajeServidor = "Se está intentando entregar un encomienda con una factura en importe cero.";
                                return UtilService::returnError($this, $mensajeServidor);
                           } 
                            //--------------------------  FACTURA END ----------------------------------
                         $encomienda->setPorCobrarSinFacturar(false);
                         $encomienda->setClienteDocumento($clienteDocumento);
                     }else{
                         $encomienda->setPorCobrarSinFacturar(true);
                         $encomienda->setClienteDocumento($encomienda->getClienteDestinatario());
                     }
                        
                        //--------------------------  CAJA INIT ----------------------------------
                        $user = $this->getUser();
                        $cajaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaPago);
                        if($cajaPago === null){
                            $mensajeServidor = "Para registrar el pago se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaPago->getSigla() . ".";
                            return UtilService::returnError($this, $mensajeServidor);
                        }else{
                            $em = $this->getDoctrine()->getManager();
                            $em->refresh($cajaPago);
                            $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA);
                            $operacionCaja = new OperacionCaja();
                            $operacionesCaja[] = $operacionCaja;
                            $operacionCaja->setCaja($cajaPago);
                            $operacionCaja->setEmpresa($empresa);
                            $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                            $operacionCaja->setFecha(new \DateTime());
                            if($tipoPago->getId() === TipoPago::TARJETA_CREDITO || $tipoPago->getId() === TipoPago::TARJETA_DEBITO  || $tipoPago->getId() === TipoPago::TARJETA){
                                $operacionCaja->setImporte(-1 * abs($totalNeto));
                            }else{
                                //Efectivo
                                if($monedaPago->getId() === Moneda::GTQ || $monedaPago->getId() === "1"){ //QUETSALES
                                    $operacionCaja->setImporte(-1 * abs($totalNeto));
                                }else{
                                    //USD Y EUR
                                    $operacionCaja->setImporte(-1 * abs($efectivo)); //REGISTRO TODO EL EFECTIVO QUE ENTRO
                                    $vuelto = doubleval($vuelto);
                                    if($vuelto != 0){
                                        $monedaVuelto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
                                        $cajaVuelto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaVuelto);
                                        if($cajaVuelto === null){
                                            $mensajeServidor = "Para registrar el vuelto se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaVuelto->getSigla() . ".";
                                            return UtilService::returnError($this, $mensajeServidor);
                                        }else{
                                            $em = $this->getDoctrine()->getManager();
                                            $em->refresh($cajaVuelto);
                                            $operacionCajaVuelto = new OperacionCaja();
                                            $operacionesCaja[] = $operacionCajaVuelto;
                                            $operacionCajaVuelto->setCaja($cajaVuelto);
                                            $operacionCajaVuelto->setEmpresa($empresa);
                                            $operacionCajaVuelto->setTipoOperacion($tipoOperacionCaja);
                                            $operacionCajaVuelto->setFecha(new \DateTime());
                                            $operacionCajaVuelto->setImporte(abs($vuelto)); //+ es salida
                                            $descripcion = "Salida de vuelto por entrega de encomienda.";
                                            $operacionCajaVuelto->setDescripcion($descripcion);
                                        }
                                    }
                                }
                            }
                            $descripcion = "Ingreso por entrega de encomienda.";
                            $operacionCaja->setDescripcion($descripcion);
                        }
                        //--------------------------  CAJA END ----------------------------------
            }
            
            
            //--------------------------  VALIDACIONES INIT ----------------------------------
            $erroresItems = $this->get('validator')->validate($encomienda);
            if($erroresItems !== null && count($erroresItems) != 0){
                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
            }
            
            if($encomienda->getEventos() !== null){
                foreach ($encomienda->getEventos() as $encomiendaBitacora) {
                    $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                }
            }
                
            foreach ($operacionesCaja as $operacionCaja) {
                $erroresItems = $this->get('validator')->validate($operacionCaja);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
           //--------------------------  VALIDACIONES END ----------------------------------  
                
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    if($encomienda->getFacturaGenerada() !== null){
                        $em->persist($encomienda->getFacturaGenerada());
                    }
                    $em->persist($encomienda);
                   
                    foreach ($operacionesCaja as $operacionCaja) {
                        $em->persist($operacionCaja);
                    }
                    
                    $em->flush();
                    
                    $facturas = array();
                    $facturaGenerada = $encomienda->getFacturaGenerada();
                    if($facturaGenerada !== null){
                        $em->refresh($facturaGenerada); //Recargar el consecutivo de la factura que lo asigno un trigger 
                        $factura = $facturaGenerada->getFactura();
                        $em->refresh($factura);
                        $facturaGenerada->validar();
                        $facturas[] = $facturaGenerada->getInfo2();
                    }    
                    
                    $ids = $encomienda->getId();
                    
                    $info = "Se va a actualizar la encomienda con identificador: " . implode(",", $ids) . ". ";
                    $facturas = array_unique($facturas);
                    if(count($facturas) != 0){
                        $info .= "Se van a imprimir la factura: " . implode(",", $facturas) . ".";
                    }
                    
                    foreach ($operacionesCaja as $operacionCaja) {
                        $caja = $operacionCaja->getCaja();
                        $sobregirada = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->checkSobregiroCaja($caja);
                        if($sobregirada === true){
                            throw new \RuntimeException("m1No se puede realizar la operación porque la caja en la moneda: " . $caja->getMoneda()->getSigla() . " no cuenta con suficiente efectivo.");
                        }
                    }
                    
                    $em->getConnection()->commit();
                    if(count($facturas) != 0){
                        return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaEncomienda', array(
                            'request'  => $request,
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }else{
                        return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                            'mensajeServidor' => "m0",
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }
                    
                } catch (\RuntimeException $exc) {
//                    var_dump($exc);
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
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
                $error = UtilService::getErrorsToForm($form);
                return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:entregar.html.twig', array(
            'form' => $form->createView(),
            'encomienda' => $encomienda,
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/consultarEncomienda.{_format}", name="consultarEncomienda-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Route(path="/consultarEncomienda/", name="consultarEncomienda-case2", defaults={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function consultarEncomiendaAction(Request $request, $_route) {
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        $encomienda = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
        if (!$encomienda) {
            return UtilService::returnError($this, "m1La encomienda con id: ".$id." no existe.");
        }

        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:consultar.html.twig', array(
            'encomienda' => $encomienda
        ));
    }
    
    /**
     * @Route(path="/anularEncomienda.{_format}", name="anularEncomienda-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function anularEncomiendaAction(Request $request, $_route) {
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('anular_encomienda_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
        if (!$encomienda) {
            return UtilService::returnError($this, "m1La encomienda con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new AnularEncomiendaType($this->getDoctrine()), $encomienda);  
        
        $mensajeServidor = "";
        $tipoDocumento = $encomienda->getTipoDocumento();
        if($tipoDocumento->getId() !== TipoDocumentoEncomienda::FACTURA && 
                $tipoDocumento->getId() !== TipoDocumentoEncomienda::POR_COBRAR){
            $mensajeServidor = "m1Solamente se pueden anular encomiendas facturadas."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        $facturaGenerada = $encomienda->getFacturaGenerada();
        if($facturaGenerada === null){
            $mensajeServidor = "m1La encomienda no se ha facturado."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if(UtilService::compararFechas($facturaGenerada->getFecha(), new \DateTime()) !== 0 ){
            $mensajeServidor = "m1Las facturas solamente se pueden anular el mismo día que se crearon."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        $estacionUsuario = $this->getUser()->getEstacion();
        if($estacionUsuario === null){
            $mensajeServidor = "m1Para anular una factura el usuario debe estar asociado a la estación donde se creo."; 
            return UtilService::returnError($this, $mensajeServidor);
        }else if($facturaGenerada->getEstacion()->getId() !==  $estacionUsuario->getId()){
            $mensajeServidor = "m1La factura solamente la puede anular un usuario de la estación:" . $facturaGenerada->getEstacion()->__toString() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
            if($encomienda->getEstuboTransito() === true){
                $mensajeServidor = "m1No se puede anular la factura de encominda con identificador : " . $encomienda->getId() . " porque estubo en tránsito."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
            
            $ultimoEstado = $encomienda->getUltimoEstado();
            if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
                $mensajeServidor = "m1Solamente se puede anular la factura de una encomienda que este en estado recibida o desembarcada. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }else if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
            $ultimoEstado = $encomienda->getUltimoEstado();
            if($ultimoEstado->getId() !== EstadoEncomienda::ENTREGADA){
                $mensajeServidor = "m1Solamente se puede anular la factura de una encomienda por cobrar que este en estado entregada. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }

        $cantidadEncomiendas = 0;
        $totalPrecioCalculadoMonedaBase = 0; //Valor de la factura de todas las encomiendas, en GTQ.
        $totalValorEncomiendasEfectivo = 0; //Valor de todas las encomiendas que sean de efectivo. Este valor siempre esta en GTQ.
        $encomiendasFacturas = array();
        $idEncomiendasFacturas = array();
        if($mensajeServidor === ""){
            $idFactura = $encomienda->getFacturaGenerada()->getId();
            $encomiendasFacturas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->getEncomiendasPorFactura($idFactura);
            $cantidadEncomiendas = count($encomiendasFacturas);
//            var_dump($cantidadEncomiendas);
            foreach ($encomiendasFacturas as $itemEncomienda) {
                
                if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                    $ultimoEstado = $itemEncomienda->getUltimoEstado();
                    if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
                        $mensajeServidor = "m1La encomienda " .$itemEncomienda->getId()." pertenece a la misma factura y su estado acual es: " . $ultimoEstado->getNombre() . ".Solamente se puede anular una encomienda que este en estado recibida o desembarcada."; 
                        return UtilService::returnError($this, $mensajeServidor);
                    }
                }else if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                    $ultimoEstado = $encomienda->getUltimoEstado();
                    if($ultimoEstado->getId() !== EstadoEncomienda::ENTREGADA){
                        $mensajeServidor = "m1La encomienda " .$itemEncomienda->getId()." pertenece a la misma factura y su estado acual es: " . $ultimoEstado->getNombre() . ".Solamente se puede anular la factura de una encomienda por cobrar que este en estado entregada."; 
                        return UtilService::returnError($this, $mensajeServidor);
                    }
                }
                
                $idEncomiendasFacturas[] = $itemEncomienda->getId();
                $totalPrecioCalculadoMonedaBase += abs($itemEncomienda->getPrecioCalculadoMonedaBase());
                if($itemEncomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
                    $totalValorEncomiendasEfectivo += abs($itemEncomienda->getCantidad());
                }
            }
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $facturaGenerada = $encomienda->getFacturaGenerada();
            $facturaGenerada->getImporteTotal(); //Para cargar el objeto lassy... NO QUITAR
            $facturaGenerada->setImporteTotal('0.00');
            $facturaGenerada->setFechaAnulacion(new \DateTime());
            $facturaGenerada->setUsuarioAnulacion($this->getUser());
            $operacionesCaja = array();
            foreach ($encomiendasFacturas as $itemEncomienda) {
                
                $encomiendaBitacora = new EncomiendaBitacora();
                $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                $encomiendaBitacora->setUsuario($this->getUser());
                $encomiendaBitacora->setFecha(new \DateTime());
                $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::ANULADA));
                $itemEncomienda->addEventos($encomiendaBitacora);
                    
                if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                    
                    // Se pone en estado desembarcada para poder hacer la entrega nuevamente, se le quita la referencia a la factura.
                    
                    $encomiendaBitacora = new EncomiendaBitacora();
                    $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                    $encomiendaBitacora->setUsuario($this->getUser());
                    $encomiendaBitacora->setFecha(new \DateTime());
                    $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::DESEMBARCADA));
                    $itemEncomienda->addEventos($encomiendaBitacora);
                    
                    $itemEncomienda->setFacturaGenerada(null); 
                }
                
                if($itemEncomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
                    //Se registra la salida de caja
                    $monedaGTQ = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
                    $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($this->getUser(), $monedaGTQ);
                    if($caja === null){
                        $mensajeServidor = "Para entregar el efectivo se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaGTQ->getSigla() . ".";
                        return UtilService::returnError($this, $mensajeServidor);
                    }else{
                        $em = $this->getDoctrine()->getManager();
                        $em->refresh($caja);
                        $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA);
                        $operacionCaja = new OperacionCaja();
                        $operacionCaja->setCaja($caja);
                        $operacionCaja->setEmpresa($facturaGenerada->getFactura()->getEmpresa());
                        $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                        $operacionCaja->setFecha(new \DateTime());
                        if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                            $operacionCaja->setImporte(abs($itemEncomienda->getCantidad()));
                            $operacionCaja->setDescripcion("Salida por la anulación de factura: " . $facturaGenerada->getInfo2() . ". Encomienda de efectivo con ID: " . $itemEncomienda->getId() . ".");
                        }else if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                            $operacionCaja->setImporte(-1 * abs($itemEncomienda->getCantidad()));
                            $operacionCaja->setDescripcion("Salida por la anulación de factura: " . $facturaGenerada->getInfo2() . ". Encomienda por cobrar de efectivo con ID: " . $itemEncomienda->getId() . ".");
                        }
                        $operacionesCaja[] = $operacionCaja;
                    }
                }
            }

            $monedaGTQ = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
            $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($this->getUser(), $monedaGTQ);
            if($caja === null){
                $mensajeServidor = "Para entregar el valor de la factura se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaGTQ->getSigla() . ".";
                return UtilService::returnError($this, $mensajeServidor);
            }else{
                $operacionCaja = new OperacionCaja();
                $operacionesCaja[] = $operacionCaja;
                $operacionCaja->setCaja($caja);
                $operacionCaja->setEmpresa($facturaGenerada->getFactura()->getEmpresa());
                $operacionCaja->setTipoOperacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA));
                $operacionCaja->setFecha(new \DateTime());
                $operacionCaja->setImporte(abs($totalPrecioCalculadoMonedaBase)); //+ es salida
                if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                    $descripcion = "Salida por la anulación de factura: " . $facturaGenerada->getInfo2() .
                            ". Encomiendas (VALOR TOTAL DE LA FACTURA EN GTQ) con IDS: " . implode(",", $idEncomiendasFacturas) .".";
                    $operacionCaja->setDescripcion($descripcion);
                }else if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                    $descripcion = "Salida por la anulación de factura: " . $facturaGenerada->getInfo2() .
                            ". Encomiendas por cobrar (VALOR TOTAL DE LA FACTURA EN GTQ) con IDS: " . implode(",", $idEncomiendasFacturas) .".";
                    $operacionCaja->setDescripcion($descripcion);
                }
           }

           $form->bind($request);
           
           //Solamente se le pone la descripcion si son facturadas, pq a las por cobrar solamente se anula la factura
           //pero las encomiendas se pueden intentar entregar nuevamente con un nuevo numero de factura.
           if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                foreach ($encomiendasFacturas as $itemEncomienda) {
                    //Aqui esta la justificacion de la anulacion. debe ir despues del bind
                    $itemEncomienda->setObservacion($encomienda->getObservacion()); 
                }
                $detalle = "Se anulo la factura que corresponde a las encomiendas con los identificadores " . implode(",", $idEncomiendasFacturas) . ".";
                $facturaGenerada->setObservacion($detalle . $encomienda->getObservacion());
           }else if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                $detalle = "Se anulo la factura que corresponde a las encomiendas por cobrar con los identificadores " . implode(",", $idEncomiendasFacturas) . ".";
                $facturaGenerada->setObservacion($detalle . $encomienda->getObservacion());
           }
           
          foreach ($operacionesCaja as $operacionCaja) {
                $erroresItems = $this->get('validator')->validate($operacionCaja);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
           
           if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($facturaGenerada);
                    foreach ($encomiendasFacturas as $itemEncomienda) {
                        $em->persist($itemEncomienda);
                    }
                    foreach ($operacionesCaja as $operacionCaja) {
                        $em->persist($operacionCaja);
                    }
                    $em->flush();
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    

//   INI - AnularDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.
            

//    CREDENCIALES EN ENTORNO DE PRODUCCIÓN DE e-FORCON PARA ROSITA       
    $sUsuarioRosita = 'proper-transrosita';
    $sClaveRosita = 'Transrosita2021*';              

//    CREDENCIALES EN ENTORNO DE PRODUCCIÓN DE e-FORCON PARA MITOCHA       
	$sUsuarioMitocha = 'operpro1-mayaoro';   
	$sClaveMitocha = 'M@y$2781%Qa5!!';                          
            
//    $sUsuario = 'ti-20336837';
//    $sClave = 'Pionera/12';                       
    
                    
    $sUsuario = 'operws1-fdn';
    $sClave = '$$FDN@2020Fel';               
    
            
            $sNumeroDTE = $encomienda->getFacturaGenerada()->getSNumeroDTEsat();
            
            $sSerieDTE = $encomienda->getFacturaGenerada()->getSSerieDTEsat();
            
            $sMotivo = $encomienda->getObservacion();                  
    
    
                require_once('lib/nusoap.php');

//                $soapClientMitochaTest = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/wsforconfel.asmx?WSDL','wsdl');
                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconFel.asmx?WSDL','wsdl');
//                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;                   
                $soapClient->debug_flag = true;
//                $soapClientMitochaTest->debug_flag = true;   
                
                
                
                
                $sCompanyId = $encomienda->getFacturaGenerada()->getFactura()->getEmpresa()->getId();
                

                if($sCompanyId === 1 || $sCompanyId === "1"){   
//                if($empresa->getId() === 1 || $empresa->getId() === "1"){                    
                
 

                $param = array('sUsuario' => $sUsuario, 'sClave' => $sClave, 'sNumeroDTE' => $sNumeroDTE, 'sSerieDTE' => $sSerieDTE, 'sMotivo' => $sMotivo);
                $result = $soapClient->call('AnularDteGenerico', $param);
                
                     
                
                }else if($sCompanyId === 2 || $sCompanyId === "2"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    


                $param = array('sUsuario' => $sUsuarioMitocha, 'sClave' => $sClaveMitocha, 'sNumeroDTE' => $sNumeroDTE, 'sSerieDTE' => $sSerieDTE, 'sMotivo' => $sMotivo);
                $result = $soapClient->call('AnularDteGenerico', $param);
                
                    
                }else if($sCompanyId === 7 || $sCompanyId === "7"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    


                $param = array('sUsuario' => $sUsuarioRosita, 'sClave' => $sClaveRosita, 'sNumeroDTE' => $sNumeroDTE, 'sSerieDTE' => $sSerieDTE, 'sMotivo' => $sMotivo);
                $result = $soapClient->call('AnularDteGenerico', $param);
                
                    
                }
                
                
                
                
            
//   END - AnularDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                      
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    foreach ($operacionesCaja as $operacionCaja) {
                        $caja = $operacionCaja->getCaja();
                        $sobregirada = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->checkSobregiroCaja($caja);
                        if($sobregirada === true){
                            throw new \RuntimeException("m1No se puede realizar la operación porque la caja en la moneda: " . $caja->getMoneda()->getSigla() . " no cuenta con suficiente efectivo.");
                        }
                    }
                    
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
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
                $error = UtilService::getErrorsToForm($form);
                return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:anular.html.twig', array(
            'form' => $form->createView(),
            'totalEntregar' => $totalPrecioCalculadoMonedaBase + $totalValorEncomiendasEfectivo, //Todo esta en GTQ
            'cantidadEncomiendas' => $cantidadEncomiendas,
            'idEncomiendasFacturas' => implode(",", $idEncomiendasFacturas),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    
    /**
     * @Route(path="/cancelarEncomienda.html", name="cancelarEncomienda-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function cancelarEncomiendaAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cancelar_encomienda_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $encomienda = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
        if (!$encomienda) {
            return UtilService::returnError($this, "m1La encomienda con id: ".$id." no existe.");
        }
        
        $form = $this->createForm(new CancelarEncomiendaType($this->getDoctrine()), $encomienda);
        
        $mensajeServidor = "";        
        $ultimoEstado = $encomienda->getUltimoEstado();
        if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
            $mensajeServidor = "m1Solamente se puede cancelar una encomienda que este en estado recibida o desembarcada. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($encomienda->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA){
            $mensajeServidor = "m1La encomienda facturadas no se pueden cancelar."; 
            return UtilService::returnError($this, $mensajeServidor); 
        }
        
        if($mensajeServidor === "" && $encomienda->getEstuboTransito() === true){
            $mensajeServidor = "m1La encominda con identificador : " . $encomienda->getId() . " estubo en tránsito."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($encomienda->getEstacionCreacion() !== null){
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                $mensajeServidor = "m1Para cancelar una encomienda el usuario debe estar asociado a la estación donde se creo la encomienda."; 
                return UtilService::returnError($this, $mensajeServidor);
            }else if($encomienda->getEstacionCreacion()->getId() !==  $estacionUsuario->getId()){
                $mensajeServidor = "m1La encomienda solamente la puede cancelar un usuario de la estación:" . $encomienda->getEstacionCreacion()->__toString() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        
        if ($request->isMethod('POST')) {
            $encomiendaBitacora = new EncomiendaBitacora();
            $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
            $encomiendaBitacora->setUsuario($this->getUser());
            $encomiendaBitacora->setFecha(new \DateTime());
            $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::CANCELADA));
            $encomienda->addEventos($encomiendaBitacora);
            
            $form->bind($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($encomienda);
                    $em->flush();
                    $em->getConnection()->commit();
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0"
                    ));
                    
                } catch (\RuntimeException $exc) {
//                    var_dump($exc);
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
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
                
            }else{
                $error = UtilService::getErrorsToForm($form);
                return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:cancelar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    } 
    
    /**
     * @Route(path="/procesarEncomienda.html", name="procesarEncomienda-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function procesarEncomiendaAction(Request $request, $_route) {
        
        if ($request->isMethod('POST')) {
            
            $idSalida = $request->query->get('salida');
            if (is_null($idSalida)) {
                $idSalida = $request->request->get('salida');
            }
            
            $listaIdEmbarcar = $request->query->get('listaIdEmbarcar');
            if (is_null($listaIdEmbarcar)) {
                $listaIdEmbarcar = $request->request->get('listaIdEmbarcar');
            }
            
            $listaIdDesembarcar = $request->query->get('listaIdDesembarcar');
            if (is_null($listaIdDesembarcar)) {
                $listaIdDesembarcar = $request->request->get('listaIdDesembarcar');
            }
            
            $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida);
            if($salida === null){
                UtilService::returnError("m1La salida con identificador " . $idSalida . " no existe.");
            }
            
            if((is_null($listaIdEmbarcar) || trim($listaIdEmbarcar) === "") && (is_null($listaIdDesembarcar) || trim($listaIdDesembarcar) === "")){
                UtilService::returnError("m1Debe seleccionar una encomienda");
            }
            
            $listaEncomiendaEmbarcar = array();
            $idEmbarcarArray = explode(",", $listaIdEmbarcar);
            foreach ($idEmbarcarArray as $id){
                if(trim($id) !== ""){
                    $result = $this->validarEncomienda($id, $salida, true);
                    if($result instanceof Encomienda){
                        $listaEncomiendaEmbarcar[] = $result;
                    }else{
                        return $result;
                    }
                }
            }
            
            $listaEncomiendaDesembarcar = array();
            $idDesembarcarArray = explode(",", $listaIdDesembarcar);
            foreach ($idDesembarcarArray as $id){
                if(trim($id) !== ""){
                    $result = $this->validarEncomienda($id, $salida, false);
                    if($result instanceof Encomienda){
                        $listaEncomiendaDesembarcar[] = $result;
                    }else{
                        return $result;
                    }
                }
            }
            
            if(count($listaEncomiendaEmbarcar) <= 0 && count($listaEncomiendaDesembarcar) <= 0){
                return UtilService::returnError($this, "m1Debe seleccionar una encomienda válida.");
            }
            
            foreach ($listaEncomiendaEmbarcar as $encomienda) {
                $encomiendaBitacora = new EncomiendaBitacora();
                $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                $encomiendaBitacora->setUsuario($this->getUser());
                $encomiendaBitacora->setFecha(new \DateTime());
                $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::EMBARCADA));
                $encomiendaBitacora->setSalida($salida);
                $encomienda->addEventos($encomiendaBitacora);
                $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                if($salida->getEstado()->getId() === EstadoSalida::INICIADA){
                    $encomiendaBitacora = new EncomiendaBitacora();
                    $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                    $encomiendaBitacora->setUsuario($this->getUser());
                    $encomiendaBitacora->setFecha(new \DateTime());
                    $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::TRANSITO));
                    $encomiendaBitacora->setSalida($salida);
                    $encomienda->addEventos($encomiendaBitacora);
                    $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                }
            }
            
            foreach ($listaEncomiendaDesembarcar as $encomienda) {
                $encomiendaBitacora = new EncomiendaBitacora();
                $encomiendaBitacora->setEstacion($this->getUser()->getEstacion());
                $encomiendaBitacora->setUsuario($this->getUser());
                $encomiendaBitacora->setFecha(new \DateTime());
                $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::DESEMBARCADA));
                $encomienda->addEventos($encomiendaBitacora);
                $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
           
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                foreach ($listaEncomiendaEmbarcar as $item) {
                    $em->persist($item);
                }
                foreach ($listaEncomiendaDesembarcar as $item) {
                    $em->persist($item);
                }
                $em->flush();
                $em->getConnection()->commit();
                return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                    'mensajeServidor' => "m0"
                ));
                    
            } catch (\RuntimeException $exc) {
                var_dump($exc);
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                if(UtilService::startsWith($mensaje, 'm1')){
                    $mensajeServidor = $mensaje;
                }else{
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                return UtilService::returnError($this, $mensajeServidor);
            } catch (\Exception $exc) {
                var_dump($exc);
                $em->getConnection()->rollback();
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $mensajeServidor);
            }
            
        }else{
            $genericModel = new GenericModel();       
            $form = $this->createForm(new ProcesarEncomiendasPorSalidaType($this->getDoctrine()), $genericModel, array(
                "user" => $this->getUser()
            ));
            return $this->render('AcmeTerminalOmnibusBundle:Encomienda:procesarEncomiendaPorSalida.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
        }
    }
    
    public function validarEncomienda($id, $salida, $abordar) {
        
        $encomienda = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
        if (!$encomienda) {
            return UtilService::returnError($this, "m1La encomienda con id: ".$id." no existe.");
        }

        if($abordar){
            $ultimoEstado = $encomienda->getUltimoEstado();
            if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
                $mensajeServidor = "m1Solamente se puede embarcar una encomienda con que este en estado recibida o desembarcada. El estado actual de la encomienda con ID " . $id . " es: " . $ultimoEstado->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }

            //NOTA: LAS ESTACIONES QUE PUEDEN REALIZAR ESTA ACCION ESTA VALIDADA A NIVEL DE BUSCADOR QUE FILTRA TENIENDO EN CUENTA LA ESTACION
            if($ultimoEstado->getId() ===  EstadoEncomienda::RECIBIDA ){
                if($encomienda->getEstacionCreacion() !== $this->getUser()->getEstacion()){
                    $mensajeServidor = "m1Solamente puede embarcar la encomienda con id " . $id . " un usuario de la estación: " . $encomienda->getEstacionCreacion()->__toString() . "."; 
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }
            
            if($encomienda->getEmpresa()->getId() !== $salida->getEmpresa()->getId()){
                $mensajeServidor = "Está intentando abordar la encomienda con identificador " . $id . " asociada a la empresa " .
                        $encomienda->getEmpresa()->getAlias() . " en una salida de la empresa: " . $salida->getEmpresa()->getAlias() . ".";
                return UtilService::returnError($this, $mensajeServidor);
            }
            
            if($encomienda->getEstacionCreacion() !== null){
                $estacionUsuario = $this->getUser()->getEstacion();
                if($estacionUsuario === null){
                    $mensajeServidor = "Para embarcar una encomienda el usuario debe estar asociado a la estación de origen de la encomienda: " . $encomienda->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias del envío.";
                    return UtilService::returnError($this, $mensajeServidor);
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
                        $mensajeServidor = "La encomienda con ID " . $id . " solamente la puede embarcar un usuario de la estación:" . $encomienda->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias del envío.";
                        return UtilService::returnError($this, $mensajeServidor);
                     }
                }
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
                $mensajeServidor = "m1Ruta de la encomienda incorrecta. La encomienda con ID " . $id . " solamente se puede embarcar en las rutas: " . implode(",", $secuenciaRutas) . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
            
        }else{
            
            $ultimoEstado = $encomienda->getUltimoEstado();
            if($ultimoEstado->getId() !== EstadoEncomienda::EMBARCADA && $ultimoEstado->getId() !== EstadoEncomienda::TRANSITO){
                $mensajeServidor = "m1Solamente se puede desembarcar una encomienda que este en estado embarcada o en transito. El estado actual es: " . $ultimoEstado->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }

            if($encomienda->getEstacionCreacion() !== null){
                $estacionUsuario = $this->getUser()->getEstacion();
                if($estacionUsuario === null){
                    $mensajeServidor = "Para desembarcar una encomienda el usuario debe estar asociado a una estación.";
                    return UtilService::returnError($this, $mensajeServidor);
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
                        $mensajeServidor = "La encomienda con ID " . $id . " solamente la puede desembarcar un usuario de la estación:" . $encomienda->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias del envío.";
                        return UtilService::returnError($this, $mensajeServidor);
                     }
                }
            }
            
        }
        
        return $encomienda;
    }

    /**
     * @Route(path="/pendientesEnvio.html", name="pendientesEnvio-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function pendientesEnvioAction(Request $request, $_route) {
        
        if ($request->isMethod('POST')) {
            
            $idEncomienda = $request->query->get('idEncomienda');
            if (is_null($idEncomienda)) {
                $idEncomienda = $request->request->get('idEncomienda');
            }
            
            $codigoRuta = $request->query->get('codigoRuta');
            if (is_null($codigoRuta)) {
                $codigoRuta = $request->request->get('codigoRuta');
            }
            
            if(is_null($idEncomienda) || trim($idEncomienda) === ""){
                UtilService::returnError("m1Debe especificar una encomienda.");
            }
            if(is_null($codigoRuta) || trim($codigoRuta) === ""){
                UtilService::returnError("m1Debe especificar un código de ruta.");
            }
            
            $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
            if(!$encomienda){
                UtilService::returnError("m1La encomienda con identificador " . $idEncomienda . " no existe.");
            }
            
            $ruta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Ruta')->find($codigoRuta);
            if(!$ruta){
                UtilService::returnError("m1La ruta con identificador " . $codigoRuta . " no existe.");
            }
            
            if($encomienda instanceof Encomienda){
                $encomienda->setRuta($ruta);
                $encomienda->getRutas()->first()->setRuta($ruta);
                $fechaDia = new \DateTime();
                $empresaRuta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                                ->getEmpresaQueFactura($ruta, $fechaDia);
                if($empresaRuta === null){
                    UtilService::returnError("m1No se pudo determinar la empresa en la ruta con identificador " . $codigoRuta . ".");
                }
                $encomienda->setEmpresa($empresaRuta);   
                
                $erroresItems = $this->get('validator')->validate($encomienda);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
               
                $em->persist($encomienda);
                $em->flush();
                $em->getConnection()->commit();
                return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                    'mensajeServidor' => "m0"
                ));
                    
            } catch (\RuntimeException $exc) {
                var_dump($exc);
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                if(UtilService::startsWith($mensaje, 'm1')){
                    $mensajeServidor = $mensaje;
                }else{
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                return UtilService::returnError($this, $mensajeServidor);
            } catch (\Exception $exc) {
                var_dump($exc);
                $em->getConnection()->rollback();
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $mensajeServidor);
            }
            
        }else{
            $genericModel = new GenericModel();       
            $form = $this->createForm(new PendienteEnvioType($this->getDoctrine()), $genericModel, array(
                "user" => $this->getUser()
            ));
            return $this->render('AcmeTerminalOmnibusBundle:Encomienda:pendienteEnvio.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
        }
    }
    
    /**
     * @Route(path="/pendientesEntrega.html", name="pendientesEntrega-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ENTREGA_MULTIPLE_ENCOMIENDA")
     */
    public function pendientesEntregaAction(Request $request, $_route) {
        
        $pendienteEntregarModel = new PendienteEntregarModel();       
        $form = $this->createForm(new PendienteEntregaType($this->getDoctrine()), $pendienteEntregarModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));
            
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()){
//                var_dump("pendientesEntregaAction-POST");
                $estacion = $pendienteEntregarModel->getEstacion();
                $fechaEntrega = $pendienteEntregarModel->getFecha();
                $fechaEntrega->setTime(23, 59, 59);
                $cliente = $pendienteEntregarModel->getCliente();
                $importeTotal = $pendienteEntregarModel->getImporteTotal();
                $numeroFactura = $pendienteEntregarModel->getNumeroFactura();
                $serieFactura = $pendienteEntregarModel->getSerieFactura();
                
                $listaEncomiendas = array();
                $listaIdEncomiendasHidden = $pendienteEntregarModel->getListaIdEncomiendas();
                if($listaIdEncomiendasHidden == null || trim($listaIdEncomiendasHidden) === ""){
                    return UtilService::returnError($this, "m1Debe seleccionar una encomienda.");
                }
                $listaIdEncomiendasJson = json_decode($listaIdEncomiendasHidden);
                if($listaIdEncomiendasJson !== null){
                    
                    if(count($listaIdEncomiendasJson) <= 0){
                        return UtilService::returnError($this, "m1Debe seleccionar una encomienda.");
                    }
                    
                    $tipoDocumento = null;
                    $facturaGenerada = null;
                    $empresa = null;
                    
                    foreach ($listaIdEncomiendasJson as $idEncomienda) {
                        $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
                        if($encomienda === null){
                            return UtilService::returnError($this, "m1La encomienda con identificador " . $idEncomienda . " no existe.");
                        }
                        
                        if($encomienda instanceof Encomienda){
//                            var_dump("Procesando encomienda: " . $encomienda->getId());
                            if(is_null($empresa)){
                                $empresa = $encomienda->getEmpresa();
                            }else if($empresa->getId() !== $encomienda->getEmpresa()->getId()){
                                return UtilService::returnError($this, "m1No se puede entregar encomiendas de diferentes empresas.");
                            }
                            
                            if(is_null($tipoDocumento)){
                                $tipoDocumento = $encomienda->getTipoDocumento();
                                
                                if($tipoDocumento->getId() !== TipoDocumentoEncomienda::POR_COBRAR && $numeroFactura !== null && trim($numeroFactura) !== ""){
                                    return UtilService::returnError($this, "m1Las encomiendas con documento '" . $encomienda->getTipoDocumento()->getNombre() . "' no admiten número de factura.");
                                }
                                
                                if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR && $numeroFactura !== null){
                                    
//                                    var_dump("Nro:". $numeroFactura);
                                    
                                    $existeFactura = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:FacturaGenerada')
                                            ->checkExisteFactura($serieFactura, $numeroFactura);
                                    if($existeFactura){
                                        return UtilService::returnError($this, "m1Ya existe en el sistema el documento de factura: " . 
                                                $numeroFactura . " asociado a la estación " . $estacion->__toString() . ".");
                                    }
                                    
                                    $facturaGenerada = new FacturaGenerada();
                                    $facturaGenerada->setFactura($serieFactura);
                                    $facturaGenerada->setMoneda($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ));
                                    $facturaGenerada->setTipoCambio($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($facturaGenerada->getMoneda()));
                                    $facturaGenerada->setImporteTotal($importeTotal);
                                    $facturaGenerada->setUsuario($this->getUser());
                                    $facturaGenerada->setEstacion($estacion);
                                    $facturaGenerada->setFecha($fechaEntrega);
                                    $facturaGenerada->setServicioEstacion($this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:ServicioEstacion')->find(ServicioEstacion::ENCOMIENDA));
                                    $facturaGenerada->setConsecutivo($numeroFactura);
                                }
                                
                            }else if($tipoDocumento->getId() !== $encomienda->getTipoDocumento()->getId()){
                                return UtilService::returnError($this, "m1No se puede entregar encomiendas con diferentes documentos.");
                            }
                            
                            if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR)
                            {
                                $encomienda->setTipoPago($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoPago')->find(TipoPago::EFECTIVO));
                                $encomienda->setPrecioCalculado($encomienda->getPrecioCalculadoMonedaBase());
                                if($facturaGenerada !== null){
                                    $encomienda->setPorCobrarSinFacturar(false);
                                    $encomienda->setFacturaGenerada($facturaGenerada);
                                }else{
                                    $encomienda->setPorCobrarSinFacturar(true);
                                }
                                $encomienda->setClienteDocumento($encomienda->getClienteDestinatario());
                            }
                            
                            $ultimaBitacora = $encomienda->getUltimaBitacoraById();
                            if(UtilService::compararFechas($fechaEntrega, $ultimaBitacora->getFecha()) < 0){
                                return UtilService::returnError($this, "m1La fecha de entrega no puede ser menor que " . 
                                        $ultimaBitacora->getFecha()->format("d/m/Y") . ".");
                            }
                            
                            $ultimoEstado = $ultimaBitacora->getEstado();
                            if($ultimoEstado->getId() !== EstadoEncomienda::TRANSITO && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
                                $mensajeServidor = "m1Solamente se puede entregar la encomienda si está en estado en transito o desembarcada.";
                                return UtilService::returnError($this, $mensajeServidor);
                            }
                            
                            if($ultimoEstado->getId() === EstadoEncomienda::TRANSITO){
                                $encomiendaBitacora = new EncomiendaBitacora();
                                $encomiendaBitacora->setEstacion($estacion);
                                $encomiendaBitacora->setUsuario($this->getUser());
                                $encomiendaBitacora->setFecha($fechaEntrega);
                                $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::DESEMBARCADA));
                                $encomienda->addEventos($encomiendaBitacora);
                                
                                $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                                if($erroresItems !== null && count($erroresItems) != 0){
                                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                                }
                            }
                            
                            $encomiendaBitacora = new EncomiendaBitacora();
                            $encomiendaBitacora->setEstacion($estacion);
                            $encomiendaBitacora->setUsuario($this->getUser());
                            $encomiendaBitacora->setFecha($fechaEntrega);
                            $encomiendaBitacora->setCliente($cliente);
                            $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::ENTREGADA));
                            $encomienda->addEventos($encomiendaBitacora);
                            
                            $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                            if($erroresItems !== null && count($erroresItems) != 0){
                                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                            }
                            
                            $erroresItems = $this->get('validator')->validate($encomienda);
                            if($erroresItems !== null && count($erroresItems) != 0){
                                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                            }
                            $listaEncomiendas[] = $encomienda;
                        }
                    }
                }

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $facturaGenerada = null;
                    foreach ($listaEncomiendas as $item) {
                        if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                            if($encomienda->getPorCobrarSinFacturar() == false){
                                $facturaGenerada = $item->getFacturaGenerada();
                                $em->persist($item->getFacturaGenerada());
                            }
                        }
                        $em->persist($item);
                    }
                    $em->flush();
                    
                    if($facturaGenerada !== null){
                        $em->refresh($facturaGenerada); //Recargar el consecutivo de la factura que lo asigno un trigger 
                        $factura = $facturaGenerada->getFactura();
                        $em->refresh($factura);
                        $facturaGenerada->validar();
                    }  
                    
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
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }else{
                $error = UtilService::getErrorsToForm($form);       
                return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:pendienteEntrega.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/entregaMultipleEncomienda.html", name="entregaMultipleEncomienda-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function entregaMultipleEncomiendaAction(Request $request, $_route) {
        
        $entregaMultipleModel = new EntregaMultipleModel();       
        $form = $this->createForm(new EntregaMultipleEncomiendaType($this->getDoctrine()), $entregaMultipleModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));
            
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()){
                
                $operacionesCaja = array();
                $listaEncomiendas = array();
                
                $listaIdEncomiendasHidden = $entregaMultipleModel->getListaIdEncomiendas();
                if($listaIdEncomiendasHidden == null || trim($listaIdEncomiendasHidden) === ""){
                    return UtilService::returnError($this, "m1Debe seleccionar una encomienda.");
                }
                $listaIdEncomiendasJson = json_decode($listaIdEncomiendasHidden);
                if($listaIdEncomiendasJson === null || count($listaIdEncomiendasJson) <= 0){
                    return UtilService::returnError($this, "m1Debe seleccionar una encomienda.");
                }
                    
                $estacion = $entregaMultipleModel->getEstacion();
                $empresa = $entregaMultipleModel->getEmpresa();
                $cliente = $entregaMultipleModel->getClienteReceptor();
                $importeTotal = $entregaMultipleModel->getImporteTotal();
                $idTipoDocumento = $entregaMultipleModel->getTipoDocumentoEncomienda(); //Solo el id
                
                //VALORES TOTALES DE LA FACTURA - INIT
                $serieFactura = $entregaMultipleModel->getSerieFactura();
                $facturar = $entregaMultipleModel->getFacturar();
                $tipoPago = $entregaMultipleModel->getTipoPago();
                $totalNeto = $entregaMultipleModel->getTotalNeto();
                $monedaPago = $entregaMultipleModel->getMonedaPago();
                $tasa = $entregaMultipleModel->getTasa();
                $totalPago = $entregaMultipleModel->getTotalPago();
                $efectivo = $entregaMultipleModel->getEfectivo();
                $vuelto = $entregaMultipleModel->getVuelto();
                $clienteDocumento = $entregaMultipleModel->getClienteDocumento();
                //VALORES TOTALES DE LA FACTURA - END
                
                $facturaGenerada = null;
                if(intval($idTipoDocumento) === intval(TipoDocumentoEncomienda::POR_COBRAR) && ($facturar === true || $facturar === "true")){
                    
                    if(is_null($clienteDocumento) || trim($clienteDocumento) === ""){
                        $error = "m1Debe definir el cliente documento.";
                        return UtilService::returnError($this, $error); 
                    }
                    
                    //VALIDANDO QUE LA IMPRESORA PARA FACTURAR EXISTA ------------------INIT----------------
                    $impresora = $serieFactura->getImpresora();
                    if($impresora === null){
                        $error = "m1La impresora de facturación no está definida en el sistema.";
                        return UtilService::returnError($this, $error); 
                    }else{
                        $autoPrintEncomienda = $impresora->getAutoPrint();
                        $impresorasDisponibles = $entregaMultipleModel->getImpresorasDisponibles();
                        if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                            $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                            return UtilService::returnError($this, $error); 
                        }
                    }                          
                    // VALIDANDO QUE LA IMPRESORA PARA FACTURAR EXISTA ------------------END
                    
                    $facturaGenerada = new FacturaGenerada();
                    $facturaGenerada->setFactura($serieFactura);
                    $facturaGenerada->setMoneda($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ));
                    $facturaGenerada->setTipoCambio($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($facturaGenerada->getMoneda()));
                    $facturaGenerada->setImporteTotal($importeTotal);
                    $facturaGenerada->setUsuario($this->getUser());
                    $facturaGenerada->setEstacion($estacion);
                    $facturaGenerada->setFecha(new \DateTime());
                    $facturaGenerada->setServicioEstacion($this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:ServicioEstacion')->find(ServicioEstacion::ENCOMIENDA));
                    $facturaGenerada->setConsecutivo(null);
                }
                
                $clienteRemitente = null;
                $ids = array();
                foreach ($listaIdEncomiendasJson as $idEncomienda) {
                    
                        $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
                        if($encomienda === null){
                            return UtilService::returnError($this, "m1La encomienda con identificador " . $idEncomienda . " no existe.");
                        }
                        if($encomienda instanceof Encomienda){
//                            var_dump("Procesando encomienda: " . $encomienda->getId());
                            $ids[] = $encomienda->getId();
                            
                            $tipoDocumento = $encomienda->getTipoDocumento();
                            if(intval($idTipoDocumento) !== intval($tipoDocumento->getId())){
                                return UtilService::returnError($this, "m1El tipo de documento de la encomienda no coincide.");
                            }
                            
                            if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR)
                            {
                                $encomienda->setTipoPago($tipoPago);
                                $encomienda->setPrecioCalculado($encomienda->getPrecioCalculadoMonedaBase());
                                if($facturaGenerada !== null){
                                    
                                    //Esto es importante para las porcobrar cuando se le imprimie la factura
                                    if($clienteRemitente === null){
                                        $clienteRemitente = $encomienda->getClienteRemitente();
                                    }else if($clienteRemitente !== $encomienda->getClienteRemitente()){
                                        return UtilService::returnError($this, "m1No se puede facturar la entrega de encomiendas de diferentes remitentes.");
                                    }
                                    $encomienda->setClienteDocumento($clienteDocumento);
                                    $encomienda->setPorCobrarSinFacturar(false);
                                    $encomienda->setFacturaGenerada($facturaGenerada);
                                }else{
                                    $encomienda->setPorCobrarSinFacturar(true);
                                    $encomienda->setClienteDocumento($encomienda->getClienteDestinatario());
                                }
                            }
                            
                            $ultimoEstado = $encomienda->getUltimaBitacora()->getEstado();
                            if($ultimoEstado->getId() !== EstadoEncomienda::TRANSITO && $ultimoEstado->getId() !== EstadoEncomienda::DESEMBARCADA){
                                $mensajeServidor = "m1Solamente se puede entregar la encomienda si está en estado en transito o desembarcada.";
                                return UtilService::returnError($this, $mensajeServidor);
                            }
                            
                            if($ultimoEstado->getId() === EstadoEncomienda::TRANSITO){
                                $encomiendaBitacora = new EncomiendaBitacora();
                                $encomiendaBitacora->setEstacion($estacion);
                                $encomiendaBitacora->setUsuario($this->getUser());
                                $encomiendaBitacora->setFecha(new \DateTime());
                                $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::DESEMBARCADA));
                                $encomienda->addEventos($encomiendaBitacora);
                                
                                $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                                if($erroresItems !== null && count($erroresItems) != 0){
                                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                                }
                            }
                            
                            $encomiendaBitacora = new EncomiendaBitacora();
                            $encomiendaBitacora->setEstacion($estacion);
                            $encomiendaBitacora->setUsuario($this->getUser());
                            $encomiendaBitacora->setFecha(new \DateTime());
                            $encomiendaBitacora->setCliente($cliente);
                            $encomiendaBitacora->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoEncomienda')->find(EstadoEncomienda::ENTREGADA));
                            $encomienda->addEventos($encomiendaBitacora);
                            
                            $erroresItems = $this->get('validator')->validate($encomiendaBitacora);
                            if($erroresItems !== null && count($erroresItems) != 0){
                                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                            }
                            
                            $erroresItems = $this->get('validator')->validate($encomienda);
                            if($erroresItems !== null && count($erroresItems) != 0){
                                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                            }
                            
                            if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
                                //Se registra la salida de caja
                                $user = $this->getUser();
                                $moneda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
                                $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $moneda);
                                if($caja === null){
                                    $mensajeServidor = "Para entregar el efectivo se requiere que el usuario tenga una caja abierta en la moneda: " . $moneda->getSigla() . ".";
                                    return UtilService::returnError($this, $mensajeServidor);
                                }else{
                                    $em = $this->getDoctrine()->getManager();
                                    $em->refresh($caja);
                                    $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA);
                                    $operacionCaja = new OperacionCaja();
                                    $operacionCaja->setCaja($caja);
                                    $operacionCaja->setEmpresa($empresa);
                                    $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                                    $operacionCaja->setFecha(new \DateTime());
                                    $importe = doubleval($encomienda->getCantidad());
                                    $operacionCaja->setImporte(abs($importe));
                                    $operacionCaja->setDescripcion("Salida por el monto del envío de la encomienda de efectivo con identificador: " . $encomienda->getId() . ".");
                                    $operacionesCaja[] = $operacionCaja;
                                }
                            }
                            
                            $erroresItems = $this->get('validator')->validate($encomienda);
                            if($erroresItems !== null && count($erroresItems) != 0){
                                return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                            }

                            $listaEncomiendas[] = $encomienda;
                        }
                 }
                 
                 //Si es por cobrar siempre hay moviento de caja
                 if(intval($idTipoDocumento) === intval(TipoDocumentoEncomienda::POR_COBRAR)){
                    //--------------------------  CAJA INIT ----------------------------------
                    $user = $this->getUser();
                    $cajaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaPago);
                    if($cajaPago === null){
                       $mensajeServidor = "Para registrar el pago se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaPago->getSigla() . ".";
                       return UtilService::returnError($this, $mensajeServidor);
                    }else{
                       $em = $this->getDoctrine()->getManager();
                       $em->refresh($cajaPago);
                       $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::ENCOMIENDA);
                       $operacionCaja = new OperacionCaja();
                       $operacionesCaja[] = $operacionCaja;
                       $operacionCaja->setCaja($cajaPago);
                       $operacionCaja->setEmpresa($empresa);
                       $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                       $operacionCaja->setFecha(new \DateTime());
                       if($tipoPago->getId() === TipoPago::TARJETA_CREDITO || $tipoPago->getId() === TipoPago::TARJETA_DEBITO  || $tipoPago->getId() === TipoPago::TARJETA){
                           $operacionCaja->setImporte(-1 * abs($totalNeto));
                       }else{
                           //Efectivo
                           if($monedaPago->getId() === Moneda::GTQ || $monedaPago->getId() === "1"){ //QUETSALES
                               $operacionCaja->setImporte(-1 * abs($totalNeto));
                           }else{
                                    //USD Y EUR
                                    $operacionCaja->setImporte(-1 * abs($efectivo)); //REGISTRO TODO EL EFECTIVO QUE ENTRO
                                    $vuelto = doubleval($vuelto);
                                    if($vuelto != 0){
                                        $monedaVuelto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES
                                        $cajaVuelto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaVuelto);
                                        if($cajaVuelto === null){
                                            $mensajeServidor = "Para registrar el vuelto se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaVuelto->getSigla() . ".";
                                            return UtilService::returnError($this, $mensajeServidor);
                                        }else{
                                            $em = $this->getDoctrine()->getManager();
                                            $em->refresh($cajaVuelto);
                                            $operacionCajaVuelto = new OperacionCaja();
                                            $operacionesCaja[] = $operacionCajaVuelto;
                                            $operacionCajaVuelto->setCaja($cajaVuelto);
                                            $operacionCajaVuelto->setEmpresa($empresa);
                                            $operacionCajaVuelto->setTipoOperacion($tipoOperacionCaja);
                                            $operacionCajaVuelto->setFecha(new \DateTime());
                                            $operacionCajaVuelto->setImporte(abs($vuelto)); //+ es salida
                                            $descripcion = "Salida de vuelto por entrega de encomienda.";
                                            $operacionCajaVuelto->setDescripcion($descripcion);
                                        }
                                    }
                           }
                        }
                        $descripcion = "Ingreso por entrega de encomienda por cobrar con identificadores " . implode(",", $ids) . ".";
                        $operacionCaja->setDescripcion($descripcion);
                    }
                //--------------------------  CAJA END ----------------------------------
                }
                
                foreach ($operacionesCaja as $operacionCaja) {
                    $erroresItems = $this->get('validator')->validate($operacionCaja);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                }

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    if($facturaGenerada !== null){
                        $em->persist($facturaGenerada);
                    }
                    foreach ($listaEncomiendas as $item) {
                        $em->persist($item);
                    }
                    foreach ($operacionesCaja as $operacionCaja) {
                        $em->persist($operacionCaja);
                    }
                    
                    $em->flush();
                    
                    $facturas = array();
                    if($facturaGenerada !== null){
                        $em->refresh($facturaGenerada); //Recargar el consecutivo de la factura que lo asigno un trigger 
                        $factura = $facturaGenerada->getFactura();
                        $em->refresh($factura);
                        $facturaGenerada->validar();
                        $facturas[] = $facturaGenerada->getInfo2();
                    }
                    
                    $info = "Se actualizaron las encomiendas con identificadores: " . implode(",", $ids) . ". ";
                    $facturas = array_unique($facturas);
                    if(count($facturas) != 0){
                        $info .= "Se van a imprimir las facturas : " . implode(",", $facturas) . ".";
                    }
                    
                    foreach ($operacionesCaja as $operacionCaja) {
                        $caja = $operacionCaja->getCaja();
                        $sobregirada = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->checkSobregiroCaja($caja);
                        if($sobregirada === true){
                            throw new \RuntimeException("m1No se puede realizar la operación porque la caja en la moneda: " . $caja->getMoneda()->getSigla() . " no cuenta con suficiente efectivo.");
                        }
                    }
                    
                    $em->getConnection()->commit();
                    if(count($facturas) != 0){
                        return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaEncomienda', array(
                            'request'  => $request,
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }else{
                        return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                            'mensajeServidor' => "m0",
                            'data' => implode(",", array_unique($ids)),  //Id de las encomiendas
                            'info' => $info
                        ));
                    }
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0",
                        'data' => implode(",", array_unique($ids)),
                        'info' => $info
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
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }else{
               $error = UtilService::getErrorsToForm($form);
               return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:entregaMultiple.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
    
    /**
     * @Route(path="/registrarEncomiendaEspecial.html", name="registrarEncomiendaEspecial-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ENCOMIENDA_REGISTRAR_ESPECIAL")
     */
    public function registrarEncomiendaEspecialAction(Request $request, $_route) {
        
        $encomiendaEspecialModel = new EncomiendaEspecialModel();       
        $form = $this->createForm(new RegistrarEncomiendaEspecialType($this->getDoctrine()), $encomiendaEspecialModel, array(
            "user" => $this->getUser()
        ));
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()){
                
                $tipoEncomiendaEspeciales = new TipoEncomiendaEspeciales();
                $tipoEncomiendaEspeciales->setActivo(true);
                $tipoEncomiendaEspeciales->setNombre($encomiendaEspecialModel->getNombre());
                $tipoEncomiendaEspeciales->setDescripcion($encomiendaEspecialModel->getDescripcion());
                $tipoEncomiendaEspeciales->setPermiteAutorizacionCortesia($encomiendaEspecialModel->getPermiteAutorizacionCortesia());
                $tipoEncomiendaEspeciales->setPermiteAutorizacionInterna($encomiendaEspecialModel->getPermiteAutorizacionInterna());
                $tipoEncomiendaEspeciales->setPermiteFactura($encomiendaEspecialModel->getPermiteFactura());
                $tipoEncomiendaEspeciales->setPermitePorCobrar($encomiendaEspecialModel->getPermitePorCobrar());
                
                $erroresItems = $this->get('validator')->validate($tipoEncomiendaEspeciales);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
                            
                $tarifaEspecial = new TarifaEncomiendaEspeciales();
                $tarifaEspecial->setFechaCreacion(new \DateTime());
                $tarifaEspecial->setFechaEfectividad(new \DateTime());
                $tarifaEspecial->setTarifaValor($encomiendaEspecialModel->getTarifaValor());
                $tarifaEspecial->setTipo($tipoEncomiendaEspeciales);
                $tarifaEspecial->setUsuarioCreacion($this->getUser());
                
                $erroresItems = $this->get('validator')->validate($tarifaEspecial);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($tipoEncomiendaEspeciales);
                    $em->persist($tarifaEspecial);
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
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }else{
                $error = UtilService::getErrorsToForm($form);
                return UtilService::returnError($this, "m1" . $error);
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Encomienda:registrarEncomiendaEspecial.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => ""
        ));
    }
}

?>
