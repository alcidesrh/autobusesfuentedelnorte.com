<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\Boleto;
use Acme\TerminalOmnibusBundle\Form\Model\EmitirBoletoModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\EmitirBoletoType;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;
use Acme\TerminalOmnibusBundle\Entity\FacturaGenerada;
use Acme\TerminalOmnibusBundle\Entity\OperacionCaja;
use Acme\TerminalOmnibusBundle\Entity\TipoPago;
use Acme\TerminalOmnibusBundle\Entity\TipoOperacionCaja;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Acme\TerminalOmnibusBundle\Entity\Moneda;
use Acme\TerminalOmnibusBundle\Entity\EstadoReservacion;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\CancelarBoletoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\RevertirCancelarBoletoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\AnularBoletoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\ChequearBoletoType;
use Acme\TerminalOmnibusBundle\Entity\EstadoSalida;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\ReasignarBoletoType;
use Acme\TerminalOmnibusBundle\Form\Model\ReasignarBoletoModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\ChequearBoletoPorSalidaType;
use Acme\TerminalOmnibusBundle\Form\Model\GenericModel;
use Acme\TerminalOmnibusBundle\Entity\ClaseAsiento;
use Acme\TerminalOmnibusBundle\Entity\VoucherAgencia;
use Acme\TerminalOmnibusBundle\Entity\VoucherEstacion;
use Acme\TerminalOmnibusBundle\Entity\VoucherInternet;
use Acme\TerminalOmnibusBundle\Entity\Salida;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;
use Acme\TerminalOmnibusBundle\Form\Frontend\Boleto\RegistrarAutorizacionBoletoType;
use Acme\TerminalOmnibusBundle\Entity\AutorizacionOperacion;
use Acme\TerminalOmnibusBundle\Entity\TipoAutorizacionOperacion;
use Acme\TerminalOmnibusBundle\Entity\EstadoAutorizacionOperacion;

/**
*   @Route(path="/boleto")
*/
class BoletoController extends Controller {

    /**
     * @Route(path="/", name="boletos-home", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA, ROLE_INSPECTOR_BOLETO")
     */
    public function homeBoletosAction(Request $request, $_route) {
        $response = UtilService::chechModifiedResponse($this, $request);
        if (!is_null($response)) {
            return $response;
        }
        $response = $this->render('AcmeTerminalOmnibusBundle:Boleto:listar.html.twig', array(
            "route" => $_route
        ));
        return UtilService::setTagResponse($this, $response);
    }
    
    /**
     * @Route(path="/listarBoletos.json", name="boletos-listarPaginado", requirements={"_format"="json"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA, ROLE_INSPECTOR_BOLETO")
    */
    public function listarBoletosAction($_route) {
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
                $result = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')
                               ->getBoletosPaginados($pageRequest, $rowsRequest, $sortRequest, $orderRequest, $mapFilters, $this->getUser());
                foreach($result['items'] as $item)
                {
                    $referenciaExterna = "N/D";
                    $autorizacionTarjeta = "N/D";
                    $documento = $item->getTipoDocumento()->getNombre();
                    if(($item->getTipoDocumento()->getId() === TipoDocumentoBoleto::FACTURA || 
                            $item->getTipoDocumento()->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL ||
                            $item->getTipoDocumento()->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION)){
                        if($item->getFacturaGenerada() !== null){
                            $documento .= " - " . $item->getFacturaGenerada()->getInfo2();
                            $referenciaExterna = $item->getFacturaGenerada()->getReferenciaExterna();
                            $autorizacionTarjeta = $item->getFacturaGenerada()->getAutorizacionTarjeta();
                        }else{
                            $documento .= " - N/D";
                        }
                    }
                    
                    $numeroAsiento = "N/D";
                    $claseAsiento = "N/D";
                    if($item->getAsientoBus() !== null){
                        $numeroAsiento = $item->getAsientoBus()->getNumero();
                        $claseAsiento = $item->getAsientoBus()->getClase()->getNombre();
                    }
                    
                    $row = array(
                        'id' => $item->getId(),
                        'fecha' => $item->getSalida()->getFecha()->format('d-m-Y h:i A'),
                        'numeroAsiento' => $numeroAsiento,
                        'claseAsiento' => $claseAsiento,
                        'ruta' => $item->getSalida()->getItinerario()->getRuta()->getCodigoName(),
                        'estado' => $item->getEstado()->getNombre(),
                        'clienteDocumento' => $item->getClienteDocumento()->__toString(),
                        'clienteBoleto' => $item->getClienteBoleto()->__toString(),
                        'estacionOrigen' => $item->getEstacionOrigen()->__toString(),
                        'estacionDestino' => $item->getEstacionDestino()->__toString(),
                        'observacionDestinoIntermedio' => $item->getObservacionDestinoIntermedio(),
                        'tipoDocumento' => $documento,
                        'referenciaExterna' => $referenciaExterna,
                        'autorizacionTarjeta' => $autorizacionTarjeta,
                        'tipoPago' => $item->getTipoPago() !== null ? $item->getTipoPago()->getNombre() : "",
                        'precioCalculado' => $item->getMoneda()  === null ? "" : $item->getMoneda()->getSigla() . " " . $item->getPrecioCalculado(),
                        'precioCalculadoMonedaBase' => $item->getPrecioCalculadoMonedaBase() === null ? "" : "GTQ " .  $item->getPrecioCalculadoMonedaBase(),
                        'utilizarDesdeEstacionOrigenSalida' => $item->getUtilizarDesdeEstacionOrigenSalida()  === true ? "Si" : "No",
                        'revendidoEnCamino' => $item->getRevendidoEnCamino()  === true ? "Si" : "No",
                        'reasignado' => $item->getReasignado() === null ? "" : $item->getReasignado()->getId(),
                        'fechaCreacion' => $item->getFechaCreacion()->format('d-m-Y h:i A'),
                        'usuarioCreacion' => $item->getUsuarioCreacion() !== null ? $item->getUsuarioCreacion()->getFullName() : "",
                        'estacionCreacion' => $item->getEstacionCreacion() !== null ? $item->getEstacionCreacion()->getNombre() : ""
                    );
                    $rows[] = $row;
                }
                $total = $result['total'];
            }

        } catch (\RuntimeException $exc) {
//            var_dump($exc);
            $this->get('logger')->error("Ha ocurrido un error en el sistema. " . $exc->getMessage());
        } catch (\Exception $exc) {
//            var_dump($exc);
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
     * @Route(path="/emitirBoletosCamino.html", name="emitirBoletosCamino-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS")
     */
    public function emitirBoletosCaminoAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $showVoucher = false;
        $emitirBoletoModel = new EmitirBoletoModel();
        if($this->getUser()->getEstacion() !== null){
            $showVoucher = $this->getUser()->getEstacion()->getPermitirVoucherBoleto();
        }
        
        $form = $this->createForm(new EmitirBoletoType($this->getDoctrine()), $emitirBoletoModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            
            //--------------------------------------------------------------------------------------------------------
            //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LOS BOLETOS - INIT
            //--------------------------------------------------------------------------------------------------------
            if(trim($emitirBoletoModel->getIdentificadorWeb()) !== ""){
                $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')
                                    ->getBoletosByIdentificadorWeb($emitirBoletoModel->getIdentificadorWeb());
                if(count($boletos) !== 0){
                    return $this->procesarOperacionFallida($boletos, $request);
                }
            }
            //--------------------------------------------------------------------------------------------------------
            //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LOS BOLETOS - END
            //--------------------------------------------------------------------------------------------------------
            
            $boletos = array();
            $estacionFacturacionEspecial = null;
            $importeTotalMonedabase = 0;
            $importeTotal = 0;
            $result = $this->convertirModeloEnBoletosReservaciones($emitirBoletoModel, $erroresAux);
            if($erroresAux !== null && count($erroresAux) != 0){
                return UtilService::returnError($this, $erroresAux->getIterator()->current()->getMessage());        
            }
            if(isset($result["estacionFacturacionEspecial"])){
                $estacionFacturacionEspecial = $result["estacionFacturacionEspecial"];
            }
            if(isset($result["importeTotal"])){
                $importeTotal = $result["importeTotal"];
            }
            if(isset($result["importeTotalMonedabase"])){
                $importeTotalMonedabase = $result["importeTotalMonedabase"];
            }
            if(isset($result["listaBoletos"]))
            {
                $boletos = $result["listaBoletos"];
                foreach ($boletos as $boleto) {
                    $erroresItems = $this->get('validator')->validate($boleto);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        $erroresAux->addAll($erroresItems);
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                }
            }
            
            $empresaCalendarioFacturaRuta = $emitirBoletoModel->getSalida()->getEmpresa();
            $estacionUsuario = $this->getUser()->getEstacion();
           
            // VALIDANDO QUE LA IMPRESORA EXISTA ------------------INIT
            $impresora = null;
            $tipoDocumento = $emitirBoletoModel->getTipoDocuemento();
            if(     $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || 
                    $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL){
                $impresora = $emitirBoletoModel->getSerieFactura()->getImpresora();
                if($impresora === null){
                    $error = "m1La impresora de facturación no está definida en el sistema.";
                    return UtilService::returnError($this, $error); 
                }else{
                    $impresorasDisponibles = $emitirBoletoModel->getImpresorasDisponibles();
                    if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                        $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                        return UtilService::returnError($this, $error); 
                    }
                }
            }else if($emitirBoletoModel->getMovil() === 'false' && (
                    $tipoDocumento->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA || 
                    $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER ||
                    $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA)){
                $impresoraOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ImpresoraOperaciones')
                                            ->getImpresoraOperacionesPorEstacion($estacionUsuario);
                if($impresoraOperaciones !== null){
                    $impresora = $impresoraOperaciones->getImpresoraBoleto();
                    if($impresora !== null){
                        $impresorasDisponibles = $emitirBoletoModel->getImpresorasDisponibles();
                        if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                            $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                            return UtilService::returnError($this, $error); 
                        }
                    }
                }
            }
            // VALIDANDO QUE LA IMPRESORA EXISTA ------------------END

            $operacionesCaja = array();
            if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA ||
               $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL ||
               $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER){
                
                //VALORES TOTALES DE LA FACTURA - INIT
                $tipoPago = $emitirBoletoModel->getTipoPago();
                $totalNeto = $emitirBoletoModel->getTotalNeto();
                $monedaPago = $emitirBoletoModel->getMonedaPago();
                $tasa = $emitirBoletoModel->getTasa();
                $totalPago = $emitirBoletoModel->getTotalPago();
                $efectivo = $emitirBoletoModel->getEfectivo();
                $vuelto = $emitirBoletoModel->getVuelto();
                //VALORES TOTALES DE LA FACTURA - END
                
                $user = $this->getUser();
                $cajaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaPago);
                if($cajaPago === null){
                    $error = "Para registrar el pago se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaPago->getSigla() . "." ;
                    $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                    return UtilService::returnError($this, $error); 
                }else{
                    $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::BOLETO);
                    $operacionCaja = new OperacionCaja();
                    $operacionesCaja[] = $operacionCaja;
                    $operacionCaja->setCaja($cajaPago);
                    $operacionCaja->setEmpresa($empresaCalendarioFacturaRuta);
                    $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                    $operacionCaja->setFecha(new \DateTime());
                    if($tipoPago->getId() === TipoPago::TARJETA_CREDITO || $tipoPago->getId() === TipoPago::TARJETA_DEBITO || $tipoPago->getId() === TipoPago::TARJETA){
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
                                    $error = "Para registrar el vuelto se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaVuelto->getSigla() . ".";
                                    $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                    return UtilService::returnError($this, $error);
                                }else{
                                    $operacionCajaVuelto = new OperacionCaja();
                                    $operacionesCaja[] = $operacionCajaVuelto;
                                    $operacionCajaVuelto->setCaja($cajaVuelto);
                                    $operacionCajaVuelto->setEmpresa($empresaCalendarioFacturaRuta);
                                    $operacionCajaVuelto->setTipoOperacion($tipoOperacionCaja);
                                    $operacionCajaVuelto->setFecha(new \DateTime());
                                    $operacionCajaVuelto->setImporte(abs($vuelto)); //+ es salida
                                    $descripcion = "Salida por entrega de vuelto por venta de boleto.";
                                    $operacionCajaVuelto->setDescripcion($descripcion);
                                }
                            }
                        }
                    }
                    $descripcion = "Ingreso por venta de Boletos.";
                    $operacionCaja->setDescripcion($descripcion);
                }
            }
            foreach ($operacionesCaja as $operacionCaja) {
                $erroresItems = $this->get('validator')->validate($operacionCaja);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }       
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {

                    foreach ($boletos as $boleto) {
                        if($boleto->getFacturaGenerada() !== null){
                            $em->persist($boleto->getFacturaGenerada());
                        }
                        $em->persist($boleto);
                    }
                    
                    foreach ($operacionesCaja as $operacionCaja) {
                        $em->persist($operacionCaja);
                    }
                    
                    if($estacionFacturacionEspecial !== null){
                        $estacionFacturacionEspecial->setPingFacturacionEspecial(UtilService::generateSimpleNumericPin());
                        $em->persist($estacionFacturacionEspecial);
                    }   
                    
                    $em->flush();
                    
                    $facturas = array();
                    $ids = array();
                    foreach ($boletos as $boleto) {
                        $facturaGenerada = $boleto->getFacturaGenerada();
                        if($facturaGenerada !== null){
                            $em->refresh($facturaGenerada); //Recargar el consecutivo de la factura que lo asigno un trigger 
                            $factura = $facturaGenerada->getFactura();
                            $em->refresh($factura);
                            $facturaGenerada->validar();
                            $facturas[] = $facturaGenerada->getInfo2();
                        }
                        $ids[] = $boleto->getId();
                    }
                    
                    $info = "";
                    if(count($facturas) != 0){
                        if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                            $info = "Se crearon las facturas: " . implode(",", $facturas) . ".";
                        }else{
                            $info = "Se van a imprimir las facturas: " . implode(",", $facturas) . ".";
                        }
                    }else{
                        $info = "Se crearon los boletos con identificadores: " . implode(",", $ids) . ".";
                    }
                    
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION || 
                            $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                        $info .= "El próximo ping de autorización de la estación " . $estacionFacturacionEspecial
                                . " es " . $estacionFacturacionEspecial->getPingFacturacionEspecial() . ".";
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
                        $descripcion = $operacionCaja->getDescripcion();
                        if(count($ids) != 0){
                           $descripcion .= " IDs: " . implode(",", $ids) . ".";
                        }
                        if(count($facturas) != 0){
                           $descripcion .= " Facturas: " . implode(",", $facturas) . ".";
                        }
                        $operacionCaja->setDescripcion($descripcion);
                        $em->persist($operacionCaja);
                        $em->flush();
                    }

                    $em->getConnection()->commit();
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || 
                            $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL){
                        return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaBoleto', array(
                            'request'  => $request,
                            'data' => implode(",", $ids),
                            'info' => $info
                        ));
                    }else{
                        return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                            'mensajeServidor' => "m0",
                            'data' => implode(",", $ids),
                            'info' => $info
                        ));
                    }
                    
                } catch (\RuntimeException $exc) {
//                    var_dump("error-x1");
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
//                    var_dump("error-x2");
                    var_dump($exc->getMessage());
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
//                    var_dump("error-x3");
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
//                      break;
                   }
               }
            }
        }

        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Boleto:emitirCamino.html.twig', array(
            'showVoucher' => $showVoucher,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
        
        if($request->isMethod('GET')){
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        }
        
        return $respuesta;
    }
    
    /**
     * @Route(path="/emitirBoletosAgencia.html", name="emitirBoletosAgencia-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_AGENCIA")
     */
    public function emitirBoletosAgenciaAction(Request $request, $_route) {
       
        $estacion = $this->getUser()->getEstacion();
        if($estacion === null){
            return UtilService::returnError($this, "m1El usuario no tiene estación asignada.");
        }else{
            if($estacion->getTipo()->getId() !== \Acme\TerminalOmnibusBundle\Entity\TipoEstacion::AGENCIA){
                return UtilService::returnError($this, "m1La estación del usuario no es una agencia.");
            }
        }
        
        return $this->emitirBoletosInternal($request, $_route, true);
    }
    
    /**
     * @Route(path="/emitirBoletos.html", name="emitirBoletos-case1")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS")
     */
    public function emitirBoletosAction(Request $request, $_route) {
        
        return $this->emitirBoletosInternal($request, $_route, false);
    }
    
    public function emitirBoletosInternal(Request $request, $_route, $agencia = false) {  
        $mensajeServidor = "";
        $showVoucher = false;
        $emitirBoletoModel = new EmitirBoletoModel();  
        $emitirBoletoModel->setAgencia($agencia);
        if($agencia === true){
            $emitirBoletoModel->setMonedaAgencia($this->getUser()->getEstacion()->getMonedaAgencia());
        }else{
            if($this->getUser()->getEstacion() !== null){
                $showVoucher = $this->getUser()->getEstacion()->getPermitirVoucherBoleto();
            }
        }
        $form = $this->createForm(new EmitirBoletoType($this->getDoctrine()), $emitirBoletoModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);

            //--------------------------------------------------------------------------------------------------------
            //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LOS BOLETOS - INIT
            //--------------------------------------------------------------------------------------------------------
            if(trim($emitirBoletoModel->getIdentificadorWeb()) !== ""){
                $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')
                                    ->getBoletosByIdentificadorWeb($emitirBoletoModel->getIdentificadorWeb());
                if(count($boletos) !== 0){
                    return $this->procesarOperacionFallida($boletos, $request);
                }
            }
            //--------------------------------------------------------------------------------------------------------
            //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LOS BOLETOS - END
            //--------------------------------------------------------------------------------------------------------
            
            $boletos = array();
            $numerosAsietos = array();
            $reservaciones = array();
            $idReservaciones = array();
            $estacionFacturacionEspecial = null;
            $importeTotalMonedabase = 0;
            $importeTotal = 0;
            $result = $this->convertirModeloEnBoletosReservaciones($emitirBoletoModel, $erroresAux);
            if($erroresAux !== null && count($erroresAux) != 0){
                return UtilService::returnError($this, $erroresAux->getIterator()->current()->getMessage());        
            }
            if(isset($result["estacionFacturacionEspecial"])){
                $estacionFacturacionEspecial = $result["estacionFacturacionEspecial"];
            }
            if(isset($result["importeTotalMonedabase"])){
                $importeTotalMonedabase = $result["importeTotalMonedabase"];
            }
            if(isset($result["importeTotal"])){
                $importeTotal = $result["importeTotal"];
            }
            if(isset($result["listaBoletos"]) && isset($result["listaReservaciones"]))
            {
                $boletos = $result["listaBoletos"];
                $reservaciones = $result["listaReservaciones"];
                foreach ($boletos as $boleto) {
                    $erroresItems = $this->get('validator')->validate($boleto);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        $erroresAux->addAll($erroresItems);
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $numerosAsietos[] = $boleto->getAsientoBus()->getNumero();
                }
                foreach ($reservaciones as $reservacion) {
                    $erroresItems = $this->get('validator')->validate($reservacion);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        $erroresAux->addAll($erroresItems);
                        return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                    }
                    $idReservaciones[] = $reservacion->getId();
                }
                //Se valida si existe un boleto o una reservacion activa asociada al numero de asiento de bus
                //Esta situacion se presenta cuando dos usuarios trabajan a la vez con el mismo objeto.
                $idSalida = $emitirBoletoModel->getSalida()->getId();
                $result = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientoOcupadosPorNumero($idSalida, $numerosAsietos, $idReservaciones);
                foreach ($result as $asientoBus) {
                    $error = "El asiento con el número: " . $asientoBus->getNumero() . " acaba de ser ocupado.";
                    $erroresAux->add(new ConstraintViolation($error , '', array(), '', '', null));
                    return UtilService::returnError($this, $error);
                }
            }
     
           $empresaCalendarioFacturaRuta = $emitirBoletoModel->getSalida()->getEmpresa();
           $estacionUsuario = $this->getUser()->getEstacion();
            
            // VALIDANDO QUE LA IMPRESORA EXISTA ------------------INIT
            $impresora = null;
            $tipoDocumento = $emitirBoletoModel->getTipoDocuemento();
            if(     $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || 
                    $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL){
                $impresora = $emitirBoletoModel->getSerieFactura()->getImpresora();
                if($impresora === null){
                    $error = "m1La impresora de facturación no está definida en el sistema.";
                    return UtilService::returnError($this, $error); 
                }else{
                    $impresorasDisponibles = $emitirBoletoModel->getImpresorasDisponibles();
                    if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                        $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                        return UtilService::returnError($this, $error); 
                    }
                }
            }else if($emitirBoletoModel->getMovil() === 'false' && (
                    $tipoDocumento->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA || 
                    $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER ||
                    $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA)){
                $impresoraOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ImpresoraOperaciones')
                                            ->getImpresoraOperacionesPorEstacion($estacionUsuario);
                if($impresoraOperaciones !== null){
                    $impresora = $impresoraOperaciones->getImpresoraBoleto();
                    if($impresora !== null){
                        $impresorasDisponibles = $emitirBoletoModel->getImpresorasDisponibles();
                        if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                            $error = "m1La impresora: " . $impresora->getPath() . " no está disponible.";
                            return UtilService::returnError($this, $error); 
                        }
                    }
                }
            }
            // VALIDANDO QUE LA IMPRESORA EXISTA ------------------END

            $operacionesCaja = array();
            if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA ||
               $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL ||
               $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER){
                
                //VALORES TOTALES DE LA FACTURA - INIT
                $tipoPago = $emitirBoletoModel->getTipoPago();
                $totalNeto = $emitirBoletoModel->getTotalNeto();
                $monedaPago = $emitirBoletoModel->getMonedaPago();
                $tasa = $emitirBoletoModel->getTasa();
                $totalPago = $emitirBoletoModel->getTotalPago();
                $efectivo = $emitirBoletoModel->getEfectivo();
                $vuelto = $emitirBoletoModel->getVuelto();
                //VALORES TOTALES DE LA FACTURA - END
                
                $user = $this->getUser();
                $cajaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaPago);
                if($cajaPago === null){
                    $error = "Para registrar el pago se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaPago->getSigla() . "." ;
                    $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                    return UtilService::returnError($this, $error); 
                }else{
                    $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::BOLETO);
                    $operacionCaja = new OperacionCaja();
                    $operacionesCaja[] = $operacionCaja;
                    $operacionCaja->setCaja($cajaPago);
                    $operacionCaja->setEmpresa($empresaCalendarioFacturaRuta);
                    $operacionCaja->setTipoOperacion($tipoOperacionCaja);
                    $operacionCaja->setFecha(new \DateTime());
                    if($tipoPago->getId() === TipoPago::TARJETA_CREDITO || $tipoPago->getId() === TipoPago::TARJETA_DEBITO || $tipoPago->getId() === TipoPago::TARJETA){
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
                                    $error = "Para registrar el vuelto se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaVuelto->getSigla() . ".";
                                    $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                                    return UtilService::returnError($this, $error);
                                }else{
                                    $operacionCajaVuelto = new OperacionCaja();
                                    $operacionesCaja[] = $operacionCajaVuelto;
                                    $operacionCajaVuelto->setCaja($cajaVuelto);
                                    $operacionCajaVuelto->setEmpresa($empresaCalendarioFacturaRuta);
                                    $operacionCajaVuelto->setTipoOperacion($tipoOperacionCaja);
                                    $operacionCajaVuelto->setFecha(new \DateTime());
                                    $operacionCajaVuelto->setImporte(abs($vuelto)); //+ es salida
                                    $descripcion = "Salida por entrega de vuelto por venta de boleto.";
                                    $operacionCajaVuelto->setDescripcion($descripcion);
                                }
                            }
                        }
                    }
                    $descripcion = "Ingreso por venta de Boletos.";
                    $operacionCaja->setDescripcion($descripcion);
                }
            }
            foreach ($operacionesCaja as $operacionCaja) {
                $erroresItems = $this->get('validator')->validate($operacionCaja);
                if($erroresItems !== null && count($erroresItems) != 0){
                    return UtilService::returnError($this, $erroresItems->getIterator()->current()->getMessage());
                }
            }
            
           if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                $estacionUsuario = $this->getUser()->getEstacion();
                if($estacionUsuario->getCheckAgenciaPrepago() === true){
                    if($emitirBoletoModel->getUtilizarBonoAgencia() === true || $emitirBoletoModel->getUtilizarBonoAgencia() === 'true'){
                        $importe = abs($estacionUsuario->getBonificacion()) - abs($importeTotal);
                        if($importe < 0){
                            return UtilService::returnError($this, "m1La agencia " . $estacionUsuario->getNombre() . " no tiene suficiente crédito en el saldo de bono.");
                        }
                        $estacionUsuario->setBonificacion($importe); 
                    }else{
                        $importe = abs($estacionUsuario->getSaldo()) - abs($importeTotal);
                        if($importe < 0){
                            return UtilService::returnError($this, "m1La agencia " . $estacionUsuario->getNombre() . " no tiene suficiente crédito en el saldo principal.");
                        }
                        $estacionUsuario->setSaldo($importe);
                    }
                }
            }
            
            $ruta = $emitirBoletoModel->getSalida()->getItinerario()->getRuta();
            if($ruta->getObligatorioClienteDetalle() === true){
                $this->validarClientesPorSalida($boletos, array(), $emitirBoletoModel->getSalida(), $erroresAux);
            }        
            
            if ($form->isValid() && count($erroresAux) === 0) {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    foreach ($boletos as $boleto) {
                        if($boleto->getFacturaGenerada() !== null){
                            $em->persist($boleto->getFacturaGenerada());
                        }
                        $em->persist($boleto);
                    }
                    foreach ($reservaciones as $reservacion) {
                        $em->persist($reservacion);
                    }
                    
                    foreach ($operacionesCaja as $operacionCaja) {
                        $em->persist($operacionCaja);
                    }
                    
                    if($estacionFacturacionEspecial !== null){
                        $estacionFacturacionEspecial->setPingFacturacionEspecial(UtilService::generateSimpleNumericPin());
                        $em->persist($estacionFacturacionEspecial);
                    }   
                    
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                        $em->persist($this->getUser()->getEstacion());
                    }
                    $em->flush();
                    $facturas = array();
                    $ids = array();
                    foreach ($boletos as $boleto) {
                        $facturaGenerada = $boleto->getFacturaGenerada();
                        if($facturaGenerada !== null){
                            $em->refresh($facturaGenerada); //Recargar el consecutivo de la factura que lo asigno un trigger 
                            $factura = $facturaGenerada->getFactura();
                            $em->refresh($factura);
                            $facturaGenerada->validar();
                            $facturas[] = $facturaGenerada->getInfo2();
                        }
                        $ids[] = $boleto->getId();
                    }
                    
                    $info = "";
                    if(count($facturas) != 0){
                        if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                            $info = "Se crearon las facturas: " . implode(",", $facturas) . ".";
                        }else{
                            $info = "Se van a imprimir las facturas: " . implode(",", $facturas) . ".";
                        }
                    }else{
                        $info = "Se crearon los boletos con identificadores: " . implode(",", $ids) . ".";
                    }
                    
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION || 
                            $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                        $info .= "El próximo ping de autorización de la estación " . $estacionFacturacionEspecial
                                . " es " . $estacionFacturacionEspecial->getPingFacturacionEspecial() . ".";
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
                        $descripcion = $operacionCaja->getDescripcion();
                        if(count($ids) != 0){
                           $descripcion .= " IDs: " . implode(",", $ids) . ".";
                        }
                        if(count($facturas) != 0){
                           $descripcion .= " Facturas: " . implode(",", $facturas) . ".";
                        }
                        $operacionCaja->setDescripcion($descripcion);
                        $em->persist($operacionCaja);
                        $em->flush();
                    }
                    $em->getConnection()->commit();

                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || 
                            $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL){
                        return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaBoleto', array(
                            'request'  => $request,
                            'data' => implode(",", $ids),
                            'info' => $info
                        ));
                    }else{
                        return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                            'mensajeServidor' => "m0",
                            'data' => implode(",", $ids),
                            'info' => $info
                        ));
                    }
                    
                }  catch (\RuntimeException $exc) {
//                    var_dump("error-x1");
                    var_dump("RuntimeException:".$exc->getMessage());
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\ErrorException $exc) {
//                    var_dump("error-x2");
                    var_dump("ErrorException:".$exc->getMessage());    
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this);
                } catch (\Exception $exc) {
//                    var_dump("error-x3");
                    var_dump("Exception:".$exc->getMessage()); 
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this, UtilService::checkError($exc->getMessage()));
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
//                      break;
                   }
               }
            }
        }
        
        $respuesta = $this->render('AcmeTerminalOmnibusBundle:Boleto:emitir.html.twig', array(
            'showVoucher' => $showVoucher,
            'agencia' => $agencia,
            'estacion' => $this->getUser()->getEstacion(),
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
    
    private function validarClientesPorSalida($nuevosBoletos, $omitirBoletos, $idSalida, ConstraintViolationList $erroresAux) {

        $clientes = array();
        foreach ($nuevosBoletos as $boleto) {
            $idCliente = $boleto->getClienteBoleto()->getId();
            if(in_array($idCliente, $clientes)){
                $error = new ConstraintViolation("El cliente ". $boleto->getClienteBoleto()->getNombre(). " no puede estar ubicado en más de dos asientos en la misma salida." , '', array(), '', '', null);
                $erroresAux->add($error);
                return array();
            }else{
                $clientes[] = $idCliente;
            }
        }
        
        $omitirIdBoletos = array();
        foreach ($omitirBoletos as $boleto) {
            $omitirIdBoletos[] = $boleto->getId();
        }
        
        $boletoConClientesRepetidos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getBoletoConClientesRepetidosPorSalida($idSalida, $clientes, $omitirIdBoletos);
        foreach ($boletoConClientesRepetidos as $boleto) {
            $error = new ConstraintViolation("El cliente ". $boleto->getClienteBoleto()->getNombre(). " no puede estar ubicado en más de dos asientos en la misma salida." , '', array(), '', '', null);
            $erroresAux->add($error);
            return array();
        }
    }
    
    private function convertirModeloEnBoletosReservaciones(EmitirBoletoModel $emitirBoletoModel, ConstraintViolationList $erroresAux) {
        
        $boletoOriginal = null;
        if($emitirBoletoModel instanceof ReasignarBoletoModel){
            $boletoOriginal = $emitirBoletoModel->getBoletoOriginal();
        }
        
        $listaBoletos = array();
        $listaReservaciones = array();
        $estacionFacturacionEspecial = null;
        $serieFacturacionEspecial = null;
        $importeTotalMonedabase = 0;
        $importeTotal = 0;
        $listaClienteBoletoHidden = $emitirBoletoModel->getListaClienteBoleto();
        $listaClienteBoletoJson = json_decode($listaClienteBoletoHidden);
        if($listaClienteBoletoJson !== null){
            
            $tipoDocumento = $emitirBoletoModel->getTipoDocuemento();
            if($boletoOriginal !== null){
                $tipoDocumentoOriginal = $boletoOriginal->getTipoDocumento();
                if($tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
                    if($tipoDocumento->getId() !== TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
                        $error = new ConstraintViolation('El tipo de documento para la reasignación debe ser cortesía.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                }else if($tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                    if($tipoDocumento->getId() !== TipoDocumentoBoleto::VOUCHER_AGENCIA){
                        $error = new ConstraintViolation('El tipo de documento para la reasignación debe ser agencia.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                }else if($tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET){
                    if($tipoDocumento->getId() !== TipoDocumentoBoleto::VOUCHER_INTERNET){
                        $error = new ConstraintViolation('El tipo de documento para la reasignación debe ser voucher de internet.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                }else if($tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::VOUCHER || $tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                    if($tipoDocumento->getId() !== TipoDocumentoBoleto::VOUCHER && $tipoDocumento->getId() !== TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                        $error = new ConstraintViolation('El tipo de documento para la reasignación debe ser voucher.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                }else if($tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::FACTURA || 
                        $tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL ||
                        $tipoDocumentoOriginal->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
                        $error = new ConstraintViolation('El tipo de documento para la reasignación no puede ser cortesía.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                        $error = new ConstraintViolation('El tipo de documento para la reasignación no puede ser agencia.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER || 
                             $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION || 
                             $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET){
                        $error = new ConstraintViolation('El tipo de documento para la reasignación no puede ser voucher.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                }else{
                    $error = new ConstraintViolation('Debe especificar un tipo de documento válido.', '', array(), '', '', null);
                    $erroresAux->add($error);
                    return array();
                }
            }
            
            //VALORES TOTALES DE LA FACTURA - INIT
            $serieFactura = $emitirBoletoModel->getSerieFactura();
            $tipoPago = $emitirBoletoModel->getTipoPago();
            $totalNeto = $emitirBoletoModel->getTotalNeto();
            $autorizacionTarjeta = $emitirBoletoModel->getAutorizacionTarjeta();
            $referenciaExterna = $emitirBoletoModel->getReferenciaExterna();
            $monedaPago = $emitirBoletoModel->getMonedaPago();
            $tasa = $emitirBoletoModel->getTasa();
            $totalPago = $emitirBoletoModel->getTotalPago();
            $efectivo = $emitirBoletoModel->getEfectivo();
            $vuelto = $emitirBoletoModel->getVuelto();
            //VALORES TOTALES DE LA FACTURA - END
            
            //VALORES DE AGENCIA - INIT
            $monedaAgencia = $emitirBoletoModel->getMonedaAgencia();
            $totalNetoAgencia = $emitirBoletoModel->getTotalNetoAgencia();
            $referenciaExternaAgencia = $emitirBoletoModel->getReferenciaExternaAgencia();
            //VALORES DE AGENCIA - END
            
            //VALORES TOTALES DE LA FACTURA VOUCHER ESPECIAL - INIT
            $estacionFacturacionEspecial = $emitirBoletoModel->getEstacionFacturacionEspecial();
            $serieFacturacionEspecial = $emitirBoletoModel->getSerieFacturacionEspecial();
            $pingFacturacionEspecial = $emitirBoletoModel->getPingFacturacionEspecial();
            //VALORES TOTALES DE LA FACTURA VOUCHER ESPECIAL- END

            if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION || 
                    $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                if($estacionFacturacionEspecial === null){
                    $error = new ConstraintViolation('Debe definir la estación que solicita el factura especial.', '', array(), '', '', null);
                    $erroresAux->add($error);
                    return array();
                }else{
                    if($estacionFacturacionEspecial->getFacturacionEspecial() === false){
                        $error = new ConstraintViolation('La estación no tiene permitido la facturación especial.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION && $serieFacturacionEspecial === null){
                        $error = new ConstraintViolation('Debe seleccionar una serie de facturación especial.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                    if($estacionFacturacionEspecial->getPingFacturacionEspecial() !== $pingFacturacionEspecial){
                        $error = new ConstraintViolation('Ping de autorización incorrecto.', '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                    }
                }
            }
           
            $salida = $emitirBoletoModel->getSalida();
            $estacionSubeEn = $emitirBoletoModel->getEstacionSubeEn();
            if($salida->getEstado()->getId() === EstadoSalida::INICIADA){
                if(intval($estacionSubeEn->getId()) === intval($salida->getItinerario()->getRuta()->getEstacionOrigen()->getId())){ 
                    $error = new ConstraintViolation('En salidas iniciadas no se puede emitir boletos donde el cliente suba en el origen de la ruta.', '', array(), '', '', null);
                    $erroresAux->add($error);
                    return array(); 
                }    
            }
            
            $autorizacionCortesiaRepository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionCortesia');
            foreach ($listaClienteBoletoJson as $json) {
                $boleto = new Boleto();
                $boleto->setIdentificadorWeb($emitirBoletoModel->getIdentificadorWeb());
                if($boletoOriginal !== null){
                    $boleto->setReasignado($boletoOriginal);
                }
                $boleto->setSalida($salida);
                $boleto->setEstacionOrigen($estacionSubeEn);
                $boleto->setEstacionDestino($emitirBoletoModel->getEstacionBajaEn());
                $boleto->setObservacionDestinoIntermedio($emitirBoletoModel->getObservacionBajaEn());
                $boleto->setUtilizarDesdeEstacionOrigenSalida($emitirBoletoModel->getUtilizarDesdeEstacionOrigenSalida());
                $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::EMITIDO));
                $boleto->setClienteDocumento($emitirBoletoModel->getClienteDocumento());
                $boleto->setClienteBoleto($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->find($json->idCliente));
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($boleto->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $boletoBitacora->setDescripcion("Emisión de boleto.");
                $boleto->addBitacoras($boletoBitacora);
                
                $asientoBus = null;
                if(is_numeric($json->id)){
                    $asientoBus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->find($json->id);
                    $boleto->setCamino(false);
                }else{
                    $boleto->setCamino(true);
                }
                $boleto->setAsientoBus($asientoBus);
                
                $boleto->setUsuarioCreacion($this->getUser());
                $boleto->setFechaCreacion(new \DateTime());

                $idReservacion = $json->idReservacion;
                if($idReservacion !== null && trim($idReservacion) !== "" && trim($idReservacion) !== "0") {
                     $reservacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->find($idReservacion);
                     if($reservacion === null){
                        $error = new ConstraintViolation("No se encontro la reservación con identificado: " .$idReservacion. "." , '', array(), '', '', null);
                        $erroresAux->add($error);
                        return array();
                     }else{
                        $reservacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->find(EstadoReservacion::VENDIDA));
                        $reservacion->setUsuarioActualizacion($this->getUser());
                        $reservacion->setFechaActualizacion(new \DateTime());
                        $listaReservaciones[] = $reservacion;
                     }
                }
                
                if($tipoDocumento->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
                    $boleto->setTipoDocumento($tipoDocumento);
                    $boleto->setEstacionCreacion($this->getUser()->getEstacion());
                    $pinAutorizacionCortesia = $emitirBoletoModel->getAutorizacionCortesia();
                    if($pinAutorizacionCortesia !== null && trim($pinAutorizacionCortesia) !== ""){
                        //Si se trae uno que ya este asociado a otro boleto, se valida cuando se chequea en boleto que la columna sea unica.
                        $autorizacionCortesia = $autorizacionCortesiaRepository->findOneBy(array(
                            'codigo' => $pinAutorizacionCortesia, 
                            'activo' => true
                        ));
                        if($autorizacionCortesia === null){
                            $error = new ConstraintViolation('El PIN de la autorización de cortesía no es válido.', '', array(), '', '', null);
                            $erroresAux->add($error);
                            return array();
                        }
                        $boleto->setAutorizacionCortesia($autorizacionCortesia);
                        $autorizacionCortesia->setUsuarioUtilizacion($this->getUser());
                        $autorizacionCortesia->setFechaUtilizacion(new \DateTime());
                    }else{
                        //Si es una reasignacion, no se le asigna nueva autorizacion cortesia, sino que utiliza la del padre.
                        $reasignado = $boleto->getReasignado();
                        if($reasignado === null){
                            $error = new ConstraintViolation('No se ha definido el PIN de la autorización de cortesía.', '', array(), '', '', null);
                            $erroresAux->add($error);
                            return array();
                        }
                    }
                }
                else if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || 
                        $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL ||
                        $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION ||  
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA || 
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER || 
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION ||
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET){
                    
                    $estacionOrigenRuta = $emitirBoletoModel->getSalida()->getItinerario()->getRuta()->getEstacionOrigen();
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION || 
                            $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                        $boleto->setTipoDocumento($tipoDocumento);
                        $boleto->setEstacionFacturacionEspecial($this->getUser()->getEstacion());
                        $boleto->setPingFacturacionEspecial($pingFacturacionEspecial);
                        $boleto->setEstacionCreacion($estacionFacturacionEspecial);
                    }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){  
                        $boleto->setEstacionCreacion($this->getUser()->getEstacion());
                        $boleto->setTipoDocumento($tipoDocumento);
                    }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER ||
                             $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET){
                        $boleto->setEstacionCreacion($this->getUser()->getEstacion());
                        $boleto->setTipoDocumento($tipoDocumento);
                    }else{
                        if($estacionOrigenRuta->getId() === $this->getUser()->getEstacion()->getId()){
                            $boleto->setTipoDocumento($tipoDocumento); //Factura Corriente
                        }else{
                            $boleto->setTipoDocumento($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumentoBoleto')->find(TipoDocumentoBoleto::FACTURA_ESPECIAL));
                        }                  
                        $boleto->setEstacionCreacion($this->getUser()->getEstacion());
                    }

                    //Si es factura y no esta definido, es pq es una reasignacion que no requiere de facturacion.
                    if($tipoPago === null){
                        if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET){
                            $tipoPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoPago')->find(TipoPago::TARJETA);
                        }else{
                            $tipoPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoPago')->find(TipoPago::EFECTIVO);
                        }
                    }else if($tipoPago instanceof TipoPago && ($tipoPago->getId() === TipoPago::TARJETA_CREDITO || $tipoPago->getId() === TipoPago::TARJETA_DEBITO)){
                        $tipoPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoPago')->find(TipoPago::TARJETA);
                    }
                    $boleto->setTipoPago($tipoPago);
                    $estacionOrigen = $emitirBoletoModel->getEstacionSubeEn()->getId();
                    if($emitirBoletoModel->getUtilizarDesdeEstacionOrigenSalida() === true){ //No quitar las comillas
                        $estacionOrigen = $estacionOrigenRuta;
                    }
                    
                    $claseAsiento = ClaseAsiento::A;
                    if($asientoBus !== null){
                        $claseAsiento = $asientoBus->getClase()->getId();
                    }

                    $idClaseBusItinerario = $emitirBoletoModel->getSalida()->getItinerario()->getTipoBus()->getClase()->getId();
                    $tarifaBoleto = $this->get("acme_backend_tarifa")
                                         ->getTarifaBoleto($estacionOrigen, 
                                            $emitirBoletoModel->getEstacionBajaEn()->getId(), 
                                            $tipoPago->getId(),
                                            $idClaseBusItinerario, 
                                            $claseAsiento,
                                            $emitirBoletoModel->getSalida()->getFecha());
                    if($tarifaBoleto === null){
                        
                        $idClaseBusSalida = $emitirBoletoModel->getSalida()->getTipoBus()->getClase()->getId();
                        if($idClaseBusItinerario != $idClaseBusSalida){
                            $tarifaBoleto = $this->get("acme_backend_tarifa")
                                         ->getTarifaBoleto($estacionOrigen, 
                                            $emitirBoletoModel->getEstacionBajaEn()->getId(), 
                                            $tipoPago->getId(),
                                            $idClaseBusSalida, 
                                            $claseAsiento,
                                            $emitirBoletoModel->getSalida()->getFecha());
                        }
                        if($tarifaBoleto === null){
                            $error = new ConstraintViolation("No se ha definido una tarifa en el sistema para el origen, destino, tipo de pago, clase de bus y clase de asiento seleccionados.", '', array(), '', '', null);
                            $erroresAux->add($error);
                            return array();
                        }
                    }
                    $boleto->setTarifa($tarifaBoleto);
                    
                    if($boletoOriginal === null){
                        $boleto->setPrecioCalculadoMonedaBase($tarifaBoleto->calcularTarifa());
                        $boleto->setTarifaAdicionalMonedaBase($tarifaBoleto->getTarifaAdicional());
                    }else{
                        /*
                         *  Reasignacion.......
                        *   Si el nuevo precio es mayor que el viejo, hay que facturar la diferencia.
                        *   Si es igual o menor, no se factura.
                        */
                       $diferenciaPrecio = $tarifaBoleto->calcularTarifa() - UtilService::calcularPrecioTotalReasignadoMonedaBase($boletoOriginal);
                       if($diferenciaPrecio < 0){
                           $diferenciaPrecio = 0;
                       }
                       $boleto->setPrecioCalculadoMonedaBase($diferenciaPrecio);
                       $diferenciaTarifaAdicional = $tarifaBoleto->getTarifaAdicional() - UtilService::calcularTarifaAdicionalTotalReasignadoMonedaBase($boletoOriginal);
                       if($diferenciaTarifaAdicional < 0){
                           $diferenciaTarifaAdicional = 0;
                       }
                       $boleto->setTarifaAdicionalMonedaBase($diferenciaTarifaAdicional);
                    }
                    $importeTotalMonedabase += $boleto->getPrecioCalculadoMonedaBase();
                    
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                        $boleto->setMoneda($monedaAgencia);
                        $tipoCambio = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaAgencia->getId());
                        if($tipoCambio === null){
                            $error = new ConstraintViolation("No se pudo obtener un tipo de cambio para la moneda:" . $monedaAgencia->getSigla() . ".", '', array(), '', '', null);
                            $erroresAux->add($error);
                            return array();
                        }
                        $boleto->setTipoCambio($tipoCambio);
                        $tasa = $tipoCambio->getTasa();
                        $precioCalculado =  $boleto->getPrecioCalculadoMonedaBase() / $tasa;
                        $precioCalculado = round($precioCalculado, 0, PHP_ROUND_HALF_UP);
                        $boleto->setPrecioCalculado($precioCalculado);
                        
                        $voucherAgencia = new VoucherAgencia();
                        $voucherAgencia->setEmpresa($emitirBoletoModel->getSalida()->getEmpresa());
                        if($boletoOriginal !== null){
                            $voucherAgencia->setBono($boletoOriginal->getVoucherAgencia()->getBono());
                            if($precioCalculado != 0){
                                $voucherAgencia->setReferenciaExterna($referenciaExternaAgencia);
                            }else{
                                $voucherAgencia->setReferenciaExterna($boletoOriginal->getVoucherAgencia()->getReferenciaExterna());
                            }
                        }else{
                            $voucherAgencia->setBono($emitirBoletoModel->getUtilizarBonoAgencia());
                            $voucherAgencia->setReferenciaExterna($referenciaExternaAgencia);
                        }
                        $voucherAgencia->setMoneda($monedaAgencia);
                        $voucherAgencia->setTipoCambio($tipoCambio);
                        $voucherAgencia->setImporteTotal($precioCalculado);
                        $voucherAgencia->setFecha(new \DateTime());
                        $voucherAgencia->setUsuario($this->getUser());
                        $voucherAgencia->setEstacion($this->getUser()->getEstacion());
                        $boleto->setVoucherAgencia($voucherAgencia);
                        
                    }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET){
                        
                        if($monedaPago === null){
                             if($boletoOriginal !== null || $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                                    //Es una reasignacion, o la emision de voucher especial que por defecto es que GTQ
                                $monedaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ);
                             }else if($monedaPago->getId() !== Moneda::GTQ){
                                 $error = new ConstraintViolation("La moneda de los voucher debe ser GTQ.", '', array(), '', '', null);
                                 $erroresAux->add($error);
                                 return array();
                             }
                        }
                        
                        $boleto->setMoneda($monedaPago);
                        $tipoCambio = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaPago);
                        if($tipoCambio === null){
                            $error = new ConstraintViolation("No se pudo obtener un tipo de cambio para la moneda:" . $monedaPago->getSigla() . ".", '', array(), '', '', null);
                            $erroresAux->add($error);
                            return array();
                        }
                         
                        $boleto->setTipoCambio($tipoCambio);
                        $tasa = $tipoCambio->getTasa();
                        $precioCalculado =  $boleto->getPrecioCalculadoMonedaBase() / $tasa;
                        $precioCalculado = round($precioCalculado, 0, PHP_ROUND_HALF_UP);
                        $boleto->setPrecioCalculado($precioCalculado);
                        
                        $voucherInternet = new VoucherInternet();
                        $voucherInternet->setEmpresa($emitirBoletoModel->getSalida()->getEmpresa());
                        $voucherInternet->setMoneda($monedaPago);
                        $voucherInternet->setImporteTotal($precioCalculado);
                        $voucherInternet->setFecha(new \DateTime());
                        $voucherInternet->setEstacion($this->getUser()->getEstacion());
                        $boleto->setVoucherInternet($voucherInternet);
                        
                        
                    }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER || 
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                        if($monedaPago === null){
                             if($boletoOriginal !== null || $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                                    //Es una reasignacion, o la emision de voucher especial que por defecto es que GTQ
                                $monedaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ);
                             }else if($monedaPago->getId() !== Moneda::GTQ){
                                 $error = new ConstraintViolation("La moneda de los voucher debe ser GTQ.", '', array(), '', '', null);
                                 $erroresAux->add($error);
                                 return array();
                             }
                        }
                        $boleto->setMoneda($monedaPago);
                        $tipoCambio = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaPago);
                        if($tipoCambio === null){
                            $error = new ConstraintViolation("No se pudo obtener un tipo de cambio para la moneda:" . $monedaPago->getSigla() . ".", '', array(), '', '', null);
                            $erroresAux->add($error);
                            return array();
                        }
                        
                        $boleto->setTipoCambio($tipoCambio);
                        $tasa = $tipoCambio->getTasa();
                        $precioCalculado =  $boleto->getPrecioCalculadoMonedaBase() / $tasa;
                        $precioCalculado = round($precioCalculado, 0, PHP_ROUND_HALF_UP);
                        $boleto->setPrecioCalculado($precioCalculado);

                        $voucherEstacion = new VoucherEstacion();
                        $voucherEstacion->setEmpresa($emitirBoletoModel->getSalida()->getEmpresa());
                        $voucherEstacion->setMoneda($monedaPago);
                        $voucherEstacion->setTipoCambio($tipoCambio);
                        $voucherEstacion->setImporteTotal($precioCalculado);
                        $voucherEstacion->setFecha(new \DateTime());
                        $voucherEstacion->setUsuario($this->getUser());
                        if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                            $voucherEstacion->setEstacion($estacionFacturacionEspecial);
                        }else{
                            $voucherEstacion->setEstacion($this->getUser()->getEstacion());
                        }
                        $boleto->setVoucherEstacion($voucherEstacion);  
                        
                    }else{
                        
                        $monedaFactura = null;
                        $tipoCambioFactura = null;
                        $importeTotalFactura = null;
                        if($tipoPago->getId() === TipoPago::EFECTIVO){
                            if($monedaPago === null){
                                if($boletoOriginal !== null || $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                                    //Es una reasignacion, que no requeriere factura o la emision de voucher especial que por defecto es que GTQ
                                    $monedaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ);
                                }else{
                                    $error = new ConstraintViolation("No se ha definido la moneda.", '', array(), '', '', null);
                                    $erroresAux->add($error);
                                    return array();
                                }
                            }
                            $monedaFactura = $monedaPago;
                            $boleto->setMoneda($monedaPago);
                            $idMonedaDestino = $monedaPago->getId();
                            $tipoCambio = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($idMonedaDestino);
                            if($tipoCambio === null){
                                $error = new ConstraintViolation("No se pudo obtener un tipo de cambio para la moneda:" . $monedaPago->getSigla() . ".", '', array(), '', '', null);
                                $erroresAux->add($error);
                                return array();
                            }
                            $tipoCambioFactura = $tipoCambio;
                            $boleto->setTipoCambio($tipoCambio);
                            $tasa = $tipoCambio->getTasa();
                            $precioCalculado =  $boleto->getPrecioCalculadoMonedaBase() / $tasa;
                            $precioCalculado = round($precioCalculado, 0, PHP_ROUND_HALF_UP);
                            $importeTotalFactura = $precioCalculado;
                            $boleto->setPrecioCalculado($precioCalculado);
                        }
                        else{  //TipoPago::TARJETA_CREDITO TipoPago::TARJETA_DEBITO TipoPago::TARJETA
                            $monedaFactura = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ);
                            $boleto->setMoneda($monedaFactura);
                            $tipoCambioFactura = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaFactura);
                            $boleto->setTipoCambio($tipoCambioFactura);
                            $importeTotalFactura = $boleto->getPrecioCalculadoMonedaBase();
                            $boleto->setPrecioCalculado($importeTotalFactura);
                        }

                        $estacionFactura = $this->getUser()->getEstacion();
                        if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                            $estacionFactura = $estacionFacturacionEspecial;
                            $serieFactura = $serieFacturacionEspecial;
                        }

                       //Cuando se comparan float no se utiliza !== sino !=
                       if($importeTotalFactura != 0){
    //                      var_dump("Generando factura con importe:" . $importeTotalFactura);
                            $facturaGenerada = new FacturaGenerada();
                            $facturaGenerada->setFactura($serieFactura);
                            $facturaGenerada->setAutorizacionTarjeta($autorizacionTarjeta);
                            $facturaGenerada->setReferenciaExterna($referenciaExterna);
                            $facturaGenerada->setMoneda($monedaFactura);
                            $facturaGenerada->setTipoCambio($tipoCambioFactura);
                            $facturaGenerada->setImporteTotal($importeTotalFactura);
                            $facturaGenerada->setUsuario($this->getUser());
                            $facturaGenerada->setEstacion($estacionFactura);
                            $facturaGenerada->setServicioEstacion($this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:ServicioEstacion')->find(ServicioEstacion::BOLETO));
                            $facturaGenerada->setConsecutivo(null); //Existe un triger en la BD que lo va a asignar
                            $boleto->setFacturaGenerada($facturaGenerada);
                        }else{
                            if($boletoOriginal === null){
                                $error = new ConstraintViolation("Se está intentando crear un boleto nuevo con una factura en importe cero.", '', array(), '', '', null);
                                $erroresAux->add($error);
                                return array();
                            }
                        }
                    }
                    
                    $importeTotal += $boleto->getPrecioCalculado();
                }
                
                $listaBoletos[] = $boleto;
            }
        }
        
        return array(
            "listaBoletos" => $listaBoletos,
            "listaReservaciones" => $listaReservaciones,
            "estacionFacturacionEspecial" => $estacionFacturacionEspecial,
            "importeTotal" => $importeTotal,
            "importeTotalMonedabase" => $importeTotalMonedabase
        );
    } 
    
     /**
     * @Route(path="/consultarBoleto.html", name="consultarBoleto-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_AGENCIA, ROLE_INSPECTOR_BOLETO")
     */
    public function consultarBoletoAction(Request $request) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        $boleto = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
        if (!$boleto) {
            return UtilService::returnError($this, "m1El boleto con id: ". $id . " no existe.");
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Boleto:consultar.html.twig', array(
            'boleto' => $boleto
        ));
    }  

     /**
     * @Route(path="/anularBoleto.html", name="anularBoleto-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function anularBoletoAction(Request $request, $_route) {
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('anular_boleto_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $boleto = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
        if ($boleto === null) {
            return UtilService::returnError($this, "m1El boleto con id: ". $id . " no existe.");
        }
        
        $agencia = false;
        $estacion = $this->getUser()->getEstacion();
        $monedaEstacion = null;
        if($estacion === null){
            return UtilService::returnError($this, "m1El usuario no tiene estación asignada.");
        }else{
            if($estacion->getTipo()->getId() === \Acme\TerminalOmnibusBundle\Entity\TipoEstacion::AGENCIA){
                $agencia = true;
                $monedaEstacion = $this->getUser()->getEstacion()->getMonedaAgencia();
            }else{
                $monedaEstacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Moneda')->find(Moneda::GTQ); //QUETSALES;
            }
        }
        
        $form = $this->createForm(new AnularBoletoType($this->getDoctrine()), $boleto);  
        
        $mensajeServidor = "";
        if($boleto->getEstado()->getId() !== EstadoBoleto::EMITIDO && $boleto->getEstado()->getId() !== EstadoBoleto::CHEQUEADO){
            $mensajeServidor = "m1Solamente se puede anular un boleto que este en estado emitido o chequeado. El estado actual es: " . $boleto->getEstado()->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        $tipoDocumento = $boleto->getTipoDocumento();
        if($tipoDocumento !== null){
            if($tipoDocumento->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
                $mensajeServidor = "m1Los boletos de cortesía no se pueden anular."; 
                return UtilService::returnError($this, $mensajeServidor);
            }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET){
                $mensajeServidor = "m1Los voucher de internet no se pueden anular."; 
                return UtilService::returnError($this, $mensajeServidor);
            }else if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER || $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                $mensajeServidor = "m1Los voucher no se pueden anular."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        if($boleto->getFechaCreacion() !== null){
            if(UtilService::compararFechas($boleto->getFechaCreacion(), new \DateTime()) !== 0 ){
                $mensajeServidor = "m1Los boletos solamente se pueden anular el mismo día que se crearon."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        $fechaLimiteSistema = new \DateTime();
        $fechaLimiteSistema->modify("+2 hour");
        $fechaSalida = $boleto->getSalida()->getFecha();
        if($fechaLimiteSistema > $fechaSalida){
            $existeAutorizacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')
                        ->checkExisteAutorizacion($boleto->getId(), $estacion->getId(), TipoAutorizacionOperacion::ANULACION_POR_TIEMPO);
            if($existeAutorizacion === false){
                $mensajeServidor = "m1El límite de tiempo para la anulación de un boleto son 2 horas antes del horario de la salida. El horario de la salida con identificador " . $boleto->getSalida()->getId() . " es " . $fechaSalida->format('Y-m-d H:i') . ". ";
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        $estacionBoleto = $boleto->getEstacionCreacion();
        if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
            $estacionBoleto = $boleto->getEstacionFacturacionEspecial();
        }
            
        $estacionUsuario = $this->getUser()->getEstacion();
        if($estacionUsuario === null){
            $mensajeServidor = "m1Para anular un boleto el usuario debe estar asociado a la estación donde se creo el boleto."; 
            return UtilService::returnError($this, $mensajeServidor);
        }else if($estacionBoleto->getId() !==  $estacionUsuario->getId()){
            $mensajeServidor = "m1El boleto solamente lo puede anular un usuario de la estación: " . $estacionBoleto->__toString() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        $boletosReasignados = UtilService::getBoletosReasignados($boleto);
        foreach ($boletosReasignados as $boletoReasignado) {
            
            if($boletoReasignado->getTipoDocumento() !== null && $boletoReasignado->getTipoDocumento()->getId() !== $tipoDocumento->getId()){
                $mensajeServidor = "m1El boleto no se puede anular porque es un reasignado con documento de: " . $tipoDocumento->getNombre() . 
                        " y el boleto original es: " . $boletoReasignado->getTipoDocumento()->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
            
            if($boletoReasignado->getEstado()->getId() !== EstadoBoleto::REASIGNADO){
                $mensajeServidor = "El boleto con identificador: " . $boletoReasignado->getId() . " debe estar reasignado."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
            
            if($boletoReasignado->getTipoDocumento() !== null && $boletoReasignado->getTipoDocumento()->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
                $mensajeServidor = "m1Los boletos de cortesía no se pueden anular."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
            
            if($boletoReasignado->getFechaCreacion() !== null){
                if(UtilService::compararFechas($boletoReasignado->getFechaCreacion(), new \DateTime()) !== 0 ){
                    $mensajeServidor = "m1Los boletos solamente se pueden anular el mismo día que se crearon."; 
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }
            
            $estacionBoletoReasignado = $boletoReasignado->getEstacionCreacion();
            if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                $estacionBoletoReasignado = $boletoReasignado->getEstacionFacturacionEspecial();
            }
            
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                $mensajeServidor = "m1Para anular un boleto el usuario debe estar asociado a la estación donde se creo el boleto."; 
                return UtilService::returnError($this, $mensajeServidor);
            }else if($estacionBoletoReasignado->getId() !==  $estacionUsuario->getId()){
                $mensajeServidor = "m1El boleto solamente lo puede anular un usuario de la estación: " . $estacionBoletoReasignado->__toString() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            
            $erroresAux = new ConstraintViolationList();
            $form->bind($request);
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $ids = array();
                $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::ANULADO));
                $boleto->setFechaActualizacion(new \DateTime());
                $boleto->setUsuarioActualizacion($this->getUser()); //Usuario que esta anulando el boleto
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($boleto->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $boletoBitacora->setDescripcion("Anulando boleto. Motivo: " . $boleto->getObservacion());
                $boleto->addBitacoras($boletoBitacora);
                
                $ids[] = $boleto->getId();
                foreach ($boletosReasignados as $boletoReasignado) {
                    $boletoReasignado->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::ANULADO));
                    $boletoReasignado->setFechaActualizacion(new \DateTime());
                    $boletoReasignado->setUsuarioActualizacion($this->getUser()); //Usuario que esta anulando el boleto
                    $boletoReasignado->setObservacion($boleto->getObservacion());
                    
                    $boletoBitacora = new BoletoBitacora();
                    $boletoBitacora->setEstado($boletoReasignado->getEstado());
                    $boletoBitacora->setFecha(new \DateTime());
                    $boletoBitacora->setUsuario($this->getUser());
                    $boletoBitacora->setDescripcion("Anulando boleto. Boleto hijo con identificador: " . $boleto->getId() . ". Motivo: " . $boleto->getObservacion());
                    $boletoReasignado->addBitacoras($boletoBitacora);
                
                    $ids[] = $boleto->getId();
                }
                
                $importeEntregar = $form->get("importeEntregar")->getData();
                if($importeEntregar === null || trim($importeEntregar) === ""){
                    $error = "m1El importe a entregar no puede ser 0.";
                    $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                    return UtilService::returnError($this, $error);
                }
                
                $facturas = array();
                $facturaGenerada = null;
                $facturasBoletosReasignados = array();
                if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                    $voucherAgencia = $boleto->getVoucherAgencia();
                    $voucherAgencia->getImporteTotal(); //Para cargar el objeto lassy... NO QUITAR
                    $voucherAgencia->setImporteTotal(0);
                    $voucherAgencia->setFechaAnulacion(new \DateTime());
                    $voucherAgencia->setUsuarioAnulacion($this->getUser());
                    $voucherBoletosReasignados = UtilService::getBoletosVoucherAgenciaBoletosReasignados($boleto);
                    foreach ($voucherBoletosReasignados as $voucherBoletoReasignado) {
                        $voucherBoletoReasignado->getImporteTotal(); //Para cargar el objeto lassy... NO QUITAR
                        $voucherBoletoReasignado->setImporteTotal(0);
                        $voucherBoletoReasignado->setFechaAnulacion(new \DateTime());
                        $voucherBoletoReasignado->setUsuarioAnulacion($this->getUser());
                    }

                    $estacionUsuario = $this->getUser()->getEstacion();
                    if($estacionUsuario->getCheckAgenciaPrepago() === true){
                        if($boleto->getVoucherAgencia()->getBono() === true || $boleto->getVoucherAgencia()->getBono() === 'true'){
                            $importe = abs($estacionUsuario->getBonificacion()) + abs($importeEntregar);
                            $estacionUsuario->setBonificacion($importe); 
                        }else{
                            $importe = abs($estacionUsuario->getSaldo()) + abs($importeEntregar);
                            $estacionUsuario->setSaldo($importe);
                        }
                    }

                }else{
                    $facturaGenerada = $boleto->getFacturaGenerada();
                    if($facturaGenerada != null){
                        $facturaGenerada->getImporteTotal(); //Para cargar el objeto lassy... NO QUITAR
                        $facturaGenerada->setImporteTotal('0.00');
                        $facturaGenerada->setFechaAnulacion(new \DateTime());
                        $facturaGenerada->setUsuarioAnulacion($this->getUser());
                        $facturaGenerada->setObservacion($boleto->getObservacion());
                        $facturas[] = $facturaGenerada->getInfo2();
                    }
                    $facturasBoletosReasignados = UtilService::getBoletosFacturasBoletosReasignados($boleto);
                    foreach ($facturasBoletosReasignados as $facturaBoletoReasignado) {
                        $facturaBoletoReasignado->getImporteTotal(); //Para cargar el objeto lassy... NO QUITAR
                        $facturaBoletoReasignado->setImporteTotal('0.00');
                        $facturaBoletoReasignado->setFechaAnulacion(new \DateTime());
                        $facturaBoletoReasignado->setUsuarioAnulacion($this->getUser());
                        $facturaBoletoReasignado->setObservacion($boleto->getObservacion());
                        $facturas[] = $facturaBoletoReasignado->getInfo2();
                    }
                    $facturas = array_unique($facturas);
                }
                
                
                
if($tipoDocumento->getId() !== TipoDocumentoBoleto::VOUCHER_AGENCIA){
                
                
                
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
    
            
            $sNumeroDTE = $boleto->getFacturaGenerada()->getSNumeroDTEsat();
            
            $sSerieDTE = $boleto->getFacturaGenerada()->getSSerieDTEsat();
            
            $sMotivo = $boleto->getObservacion();                             
    
    
                require_once('lib/nusoap.php');

//                $soapClientMitochaTest = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/wsforconfel.asmx?WSDL','wsdl');
                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconFel.asmx?WSDL','wsdl');
                $soapClient->debug_flag = true;
//                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;                
//                $soapClientMitochaTest->debug_flag = true;      
                
                
                
                
                $sCompanyId = $boleto->getFacturaGenerada()->getFactura()->getEmpresa()->getId();
                

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
                
                
                
}				
                
                
                
                $operacionesCaja = array();
                if($tipoDocumento->getId() !== TipoDocumentoBoleto::FACTURA_OTRA_ESTACION && 
                        $tipoDocumento->getId() !== TipoDocumentoBoleto::VOUCHER_AGENCIA){
                    $caja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($this->getUser(), $monedaEstacion);
                    if($caja === null){
                        $error = "m1Para registrar el vuelto se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaEstacion->getSigla() . "." ;
                        $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                        return UtilService::returnError($this, $error);
                    }else{
                        $operacionCaja = new OperacionCaja();
                        $operacionesCaja[] = $operacionCaja;
                        $operacionCaja->setCaja($caja);
                        $operacionCaja->setEmpresa($boleto->getSalida()->getEmpresa());
                        $operacionCaja->setTipoOperacion($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::BOLETO));
                        $operacionCaja->setFecha(new \DateTime());
                        $operacionCaja->setImporte(abs($importeEntregar)); //+ es salida
                        $descripcion = "Salida por anulación de boletos. IDs: " . implode(",", $ids) . ". Facturas: " . implode(",", $facturas) . ".";
                        $operacionCaja->setDescripcion($descripcion);
                   }
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
                    if($tipoDocumento->getId() !== TipoDocumentoBoleto::VOUCHER_AGENCIA){
                        if($facturaGenerada != null){
                            $em->persist($facturaGenerada);
                        }
                        foreach ($facturasBoletosReasignados as $facturaBoletoReasignado) {
                            $em->persist($facturaBoletoReasignado);
                        }
                    } 

                    $em->persist($boleto);
                    foreach ($boletosReasignados as $boletoReasignado) {
                        $em->persist($boletoReasignado);
                    }
                    
                    foreach ($operacionesCaja as $operacionCaja) {
                        $em->persist($operacionCaja);
                    }
                    
                    if($boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                        $em->persist($this->getUser()->getEstacion());
                    }
                    
                    $em->flush();
                    
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
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\ErrorException $exc) {
                    var_dump($exc);
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    var_dump($exc);
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
                      return UtilService::returnError($this, $mensajeServidor);
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Boleto:anular.html.twig', array(
            'agencia' => $agencia,
            'monedaEstacion' => $monedaEstacion,
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }

    /**
     * @Route(path="/chequearBoleto1.html", name="chequearBoleto-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_CHECK_BOLETO_WEB")
     */
    public function chequearBoleto1Action(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('chequear_boleto_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $boleto = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
        if (!$boleto) {
            return UtilService::returnError($this, "m1El boleto con id: ". $id . " no existe.");
        }
        
        $form = $this->createForm(new ChequearBoletoType($this->getDoctrine()), $boleto);  
        
        $mensajeServidor = "";
        if($boleto->getEstado()->getId() !== EstadoBoleto::EMITIDO){
            $mensajeServidor = "m1Solamente se puede chequear un boleto que este en estado emitido. El estado actual es: " . $boleto->getEstado()->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        else{
            if($boleto->getFechaCreacion() !== null){
                $minimo = 2;
                $maximo = 30;
                $fechaSalida = $boleto->getSalida()->getFecha();
                $fechaSalidaInit = clone $fechaSalida;
                $fechaSalidaInit->modify('- '.$minimo.' hour'); 
                $fechaSalidaEnd = clone $fechaSalida;
                $fechaSalidaEnd->modify('+ '.$maximo.' hour');
                $fechaHoy = new \DateTime();
                if($fechaHoy < $fechaSalidaInit){
                    $mensajeServidor = "m1Los boletos se empiezan a chequear ".$minimo." horas antes de la hora de salida."; 
                    return UtilService::returnError($this, $mensajeServidor);
                }
                else if($fechaHoy > $fechaSalidaEnd){
                    $mensajeServidor = "m1Los boletos solamente se pueden chequear hasta ".$maximo." horas posterior a la hora de salida.";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }

            if($boleto->getEstacionCreacion() !== null){
                $estacionUsuario = $this->getUser()->getEstacion();
                if($estacionUsuario === null){
                    $mensajeServidor = "m1Para chequear un boleto el usuario debe estar asociado a la estación de origen del boleto: " . $boleto->getEstacionOrigen()->__toString() . "."; 
                    return UtilService::returnError($this, $mensajeServidor);
                }
                else if($boleto->getEstacionOrigen()->getId() ===  $estacionUsuario->getId()){
                    /* Si el usuario pertenece a la estacion de origen ya todo esta ok. sino se revisa en las intermedias de la ruta */
                }else{
                    $listaEstacionesIntermedia = $boleto->getSalida()->getItinerario()->getRuta()->getListaEstacionesIntermediaOrdenadas();
                    $encontrada = false;
                    foreach ($listaEstacionesIntermedia as $item) {
                        if($item->getEstacion()->getId() ===  $estacionUsuario->getId()){
                            $encontrada = true;
                            break;
                        }
                    }
                    if($encontrada === false){
                        $mensajeServidor = "m1El boleto solamente lo puede chequear un usuario de la estación:" . $boleto->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias de la ruta.";   
                        return UtilService::returnError($this, $mensajeServidor);
                    }
                }
            }

            if($boleto->getSalida() !== null){
                $estadoSalida = $boleto->getSalida()->getEstado();
                if($estadoSalida->getId() !== EstadoSalida::ABORDANDO && $estadoSalida->getId() !== EstadoSalida::INICIADA){
                    $mensajeServidor = "m1Para chequear un boleto su salida debe estar en estado abordando o iniciada, el estado actual es: " . $estadoSalida->getNombre() . ".";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }
        }
        
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $boleto->setFechaActualizacion(new \DateTime());
            $boleto->setUsuarioActualizacion($this->getUser());
            $detalle = "";
            if($boleto->getSalida()->getEstado()->getId() === EstadoSalida::INICIADA){
                //El chequeo se utiliza de forma directa para poner el estado transitando si la salida ya inicio.
                $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::TRANSITO));
                $detalle = "Chequeando boleto por el buscador, quedo en estado Transito porque ya la salida habia iniciado.";
            }else{
                $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CHEQUEADO));
                $detalle = "Chequeando boleto por el buscador.";
            }
            
            $boletoBitacora = new BoletoBitacora();
            $boletoBitacora->setEstado($boleto->getEstado());
            $boletoBitacora->setFecha(new \DateTime());
            $boletoBitacora->setUsuario($this->getUser());
            $boletoBitacora->setDescripcion($detalle);
            $boleto->addBitacoras($boletoBitacora);
            
            $form->bind($request);

            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($boleto);
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
                } catch (\ErrorException $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
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
                      return UtilService::returnError($this, $mensajeServidor);
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Boleto:chequear.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }  
    
    /**
     * @Route(path="/chequearBoleto2.html", name="chequearBoleto-case3", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_CHECK_BOLETO_WEB")
     */
    public function chequearBoleto2Action(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            
            if($this->getUser()->getEstacion() === null){
                return UtilService::returnError($this, "m1El usuario debe pertenecer a una estación.");
            }
            
            $idBoletosSI = $request->query->get('idBoletosSI');
            if (is_null($idBoletosSI)) {
                $idBoletosSI = $request->request->get('idBoletosSI');
            }
            
            $idBoletosNO = $request->query->get('idBoletosNO');
            if (is_null($idBoletosNO)) {
                $idBoletosNO = $request->request->get('idBoletosNO');
            }
            
            if((is_null($idBoletosSI) || trim($idBoletosSI) === "") && (is_null($idBoletosNO) || trim($idBoletosNO) === "")){
                UtilService::returnError("m1Debe seleccionar un boleto");
            }
            
            $boletosSI = array();
            $idBoletosArray = explode(",", $idBoletosSI);
            foreach ($idBoletosArray as $id){
                if(trim($id) !== ""){
                    $result = $this->validarBoleto($id);
                    if($result instanceof Boleto){
                        $boletosSI[] = $result;
                    }else{
                        return $result;
                    }
                }
            }
            
            $boletosNO = array();
            $idBoletosArray = explode(",", $idBoletosNO);
            foreach ($idBoletosArray as $id){
                if(trim($id) !== ""){
                    $result = $this->validarBoleto($id);
                    if($result instanceof Boleto){
                        $boletosNO[] = $result;
                    }else{
                        return $result;
                    }
                }
            }
            
            if(count($boletosSI) <= 0 && count($boletosNO) <= 0){
                return UtilService::returnError($this, "m1Debe seleccionar un boleto valido.");
            }
            
            $primerBoletoSI = null;
            foreach ($boletosSI as $boleto) {
                if($primerBoletoSI === null){
                    $primerBoletoSI = $boleto;
                }
                if($boleto->getSalida()->getEstado()->getId() === EstadoSalida::INICIADA){
                    //El chequeo se utiliza de forma directa para poner el estado transitando si la salida ya inicio.
                    $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::TRANSITO));
                }else{
                    $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CHEQUEADO));
                }
                $boleto->setFechaActualizacion(new \DateTime());
                $boleto->setUsuarioActualizacion($this->getUser());
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($boleto->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $boletoBitacora->setDescripcion("Chequeando boleto en procesar por salida.");
                $boleto->addBitacoras($boletoBitacora);
            }
            
            foreach ($boletosNO as $boleto) {
                $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CANCELADO));
                $boleto->setFechaActualizacion(new \DateTime());
                $boleto->setUsuarioActualizacion($this->getUser());
                
                $boletoBitacora = new BoletoBitacora();
                $boletoBitacora->setEstado($boleto->getEstado());
                $boletoBitacora->setFecha(new \DateTime());
                $boletoBitacora->setUsuario($this->getUser());
                $boletoBitacora->setDescripcion("Se canceló el boleto porque el cliente no se presentó a abordar el bus.");
                $boleto->addBitacoras($boletoBitacora);
            }
            
            //Esto esta puesto aqui para no sobrecargar la transaccion
            $salida = null;
            $estacionOrigenRuta = null;
            $estacionUsuario = null;
            if($primerBoletoSI !== null){
                $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($primerBoletoSI->getSalida()->getId());
                $estacionOrigenRuta = $salida->getItinerario()->getRuta()->getEstacionOrigen();
                $estacionUsuario = $this->getUser()->getEstacion();
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                foreach ($boletosSI as $boleto) {
                    $em->persist($boleto);    
                }
                foreach ($boletosNO as $boleto) {
                    $em->persist($boleto);    
                }
                $em->flush();
                
                if($primerBoletoSI !== null){
                    //En el origen de la salida no se notifica pq se realiza cuando se inicia la salida
                    if($estacionOrigenRuta !== $estacionUsuario){
                        $this->sendEmailReporteActualizacionSalida($salida);
                    }
                }
                
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
            } catch (\ErrorException $exc) {
                var_dump($exc);
                $em->getConnection()->rollback();
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $mensajeServidor);
            } catch (\Exception $exc) {
                var_dump($exc);
                $em->getConnection()->rollback();
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $mensajeServidor);
            }
                
        }else{
            $genericModel = new GenericModel();       
            $form = $this->createForm(new ChequearBoletoPorSalidaType($this->getDoctrine()), $genericModel, array(
                "user" => $this->getUser()
            ));
            return $this->render('AcmeTerminalOmnibusBundle:Boleto:chequearBoletosPorSalida.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
        }
    } 
    
    public function validarBoleto($id) {
        
            $boleto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
            if (!$boleto) {
                return UtilService::returnError($this, "m1El boleto con id: ". $id . " no existe.");
            }

            $mensajeServidor = "";
            if($boleto->getEstado()->getId() !== EstadoBoleto::EMITIDO){
                $mensajeServidor = "m1Solamente se puede chequear un boleto que este en estado emitido. El estado actual del boleto " . $boleto->getId(). " es: " . $boleto->getEstado()->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }

            if($boleto->getFechaCreacion() !== null){
                $minimo = 2;
                $maximo = 30;
                $fechaSalida = $boleto->getSalida()->getFecha();
                $fechaSalidaInit = clone $fechaSalida;
                $fechaSalidaInit->modify('- '.$minimo.' hour'); 
                $fechaSalidaEnd = clone $fechaSalida;
                $fechaSalidaEnd->modify('+ '.$maximo.' hour');
                $fechaHoy = new \DateTime();
                if($fechaHoy < $fechaSalidaInit){
                    $mensajeServidor = "m1Los boletos se empiezan a chequear ".$minimo." horas antes de la hora de salida."; 
                    return UtilService::returnError($this, $mensajeServidor);
                }
                else if($fechaHoy > $fechaSalidaEnd){
                    $mensajeServidor = "m1Los boletos solamente se pueden chequear hasta ".$maximo." horas posterior a la hora de salida.";
                    return UtilService::returnError($this, $mensajeServidor);
                }
             }
             
             if($boleto->getEstacionCreacion() !== null){
                $estacionUsuario = $this->getUser()->getEstacion();
                if($estacionUsuario === null){
                    $mensajeServidor = "m1Para chequear un boleto el usuario debe estar asociado a la estación de origen del boleto: " . $boleto->getEstacionOrigen()->__toString() . "."; 
                    return UtilService::returnError($this, $mensajeServidor);
                }
                else if($boleto->getEstacionOrigen()->getId() ===  $estacionUsuario->getId()){
                    /* Si el usuario pertenece a la estacion de origen ya todo esta ok. sino se revisa en las intermedias de la ruta */
                }else{
                    $listaEstacionesIntermedia = $boleto->getSalida()->getItinerario()->getRuta()->getListaEstacionesIntermediaOrdenadas();
                    $encontrada = false;
                    foreach ($listaEstacionesIntermedia as $item) {
                        if($item->getEstacion()->getId() ===  $estacionUsuario->getId()){
                            $encontrada = true;
                            break;
                         }
                     }
                     if($encontrada === false){
                        $mensajeServidor = "m1El boleto solamente lo puede chequear un usuario de la estación:" . $boleto->getEstacionOrigen()->__toString() . " o de una de las estaciones intermedias de la ruta.";   
                        return UtilService::returnError($this, $mensajeServidor);
                     }
                 }
             }
             
             if($boleto->getSalida() !== null){
                $estadoSalida = $boleto->getSalida()->getEstado();
                if($estadoSalida->getId() !== EstadoSalida::ABORDANDO && $estadoSalida->getId() !== EstadoSalida::INICIADA){
                    $mensajeServidor = "m1Para chequear un boleto su salida debe estar en estado abordando o iniciada, el estado actual es: " . $estadoSalida->getNombre() . ".";
                    return UtilService::returnError($this, $mensajeServidor);
                }
             }
             
             return $boleto;
        
    }
    
     /**
     * @Route(path="/cancelarBoleto.html", name="cancelarBoleto-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Route(path="/cancelarBoleto/", name="cancelarBoleto-case2", defaults={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS")
     */
    public function cancelarBoletoAction(Request $request, $_route) {
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('cancelar_boleto_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $boleto = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
        if (!$boleto) {
            return UtilService::returnError($this, "m1El boleto con id: ". $id . " no existe.");
        }
        
        $form = $this->createForm(new CancelarBoletoType($this->getDoctrine()), $boleto);  
        
        $mensajeServidor = "";
        if($boleto->getEstado()->getId() !== EstadoBoleto::EMITIDO && $boleto->getEstado()->getId() !== EstadoBoleto::CHEQUEADO){
            $mensajeServidor = "m1Solamente se puede cancelar un boleto que este en estado emitido o chequeado. El estado actual es: " . $boleto->getEstado()->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($boleto->getSalida() !== null){
            $estadoSalida = $boleto->getSalida()->getEstado();
            if($estadoSalida->getId() !== EstadoSalida::PROGRAMADA && $estadoSalida->getId() !== EstadoSalida::ABORDANDO && $estadoSalida->getId() !== EstadoSalida::INICIADA && $estadoSalida->getId() !== EstadoSalida::CANCELADA){
                $mensajeServidor = "m1Para cancelar un boleto su salida debe estar en estado programada, abordando, iniciada o cancelada, el estado actual es: " . $estadoSalida->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::CANCELADO));
            $boleto->setFechaActualizacion(new \DateTime());
            $boleto->setUsuarioActualizacion($this->getUser());
            
            $boletoBitacora = new BoletoBitacora();
            $boletoBitacora->setEstado($boleto->getEstado());
            $boletoBitacora->setFecha(new \DateTime());
            $boletoBitacora->setUsuario($this->getUser());
            $boletoBitacora->setDescripcion("Cancelando boleto por el buscador.");
            $boleto->addBitacoras($boletoBitacora);
            
            $form->bind($request);
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($boleto);
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
                } catch (\ErrorException $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
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
                      return UtilService::returnError($this, $mensajeServidor);
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Boleto:cancelar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    } 
    
    /**
     * @Route(path="/revertirCancelacionBoleto.html", name="revertirCancelacionBoleto-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Route(path="/revertirCancelacionBoleto/", name="revertirCancelacionBoleto-case2", defaults={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS")
     */
    public function revertirCancelacionBoletoAction(Request $request, $_route) {
        
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
        }
        
        if (is_null($id)) {
            $command = $request->request->get('revertir_cancelar_boleto_command'); //Submit
            if($command !== null){
                $id = $command["id"];
            }
        }
        
        $boleto = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
        if (!$boleto) {
            return UtilService::returnError($this, "m1El boleto con id: ". $id . " no existe.");
        }
        
        $form = $this->createForm(new RevertirCancelarBoletoType($this->getDoctrine()), $boleto);  
        
        $mensajeServidor = "";
        if($boleto->getEstado()->getId() !== EstadoBoleto::CANCELADO){
            $mensajeServidor = "m1Solamente se le puede revertir el estado a los boletos cancelados. El estado actual es: " . $boleto->getEstado()->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($boleto->getSalida() !== null){
            $estadoSalida = $boleto->getSalida()->getEstado();
            if($estadoSalida->getId() !== EstadoSalida::PROGRAMADA && $estadoSalida->getId() !== EstadoSalida::ABORDANDO && $estadoSalida->getId() !== EstadoSalida::INICIADA){
                $mensajeServidor = "m1Para cancelar un boleto su salida debe estar en estado programada, abordando o iniciada, el estado actual es: " . $estadoSalida->getNombre() . ".";
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        if($boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA){
            return UtilService::returnError($this, "m1Las cortesias canceladas no se pueden revertir.");
        }
        
        if($boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
            return UtilService::returnError($this, "m1Las boletos de agencia cancelados no se pueden revertir.");
        }
        
        if ($request->isMethod('POST') && $mensajeServidor === "") {
            $erroresAux = new ConstraintViolationList();
            $estadoSalida = $boleto->getSalida()->getEstado();
            $boleto->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::EMITIDO));
            $boleto->setFechaActualizacion(new \DateTime());
            $boleto->setUsuarioActualizacion($this->getUser());
            
            $boletoBitacora = new BoletoBitacora();
            $boletoBitacora->setEstado($boleto->getEstado());
            $boletoBitacora->setFecha(new \DateTime());
            $boletoBitacora->setUsuario($this->getUser());
            $boletoBitacora->setDescripcion("Revirtiendo cancelación de boleto por el buscador.");
            $boleto->addBitacoras($boletoBitacora);
            
            $form->bind($request);
            
            if($boleto->getCamino() === false){
                //Se valida si existe un boleto o una reservacion activa asociada al numero de asiento de bus
                //Esta situacion se presenta cuando dos usuarios trabajan a la vez con el mismo objeto.
                //El chequeo hay que hacerlo pq se esta reactivando un boleto que ocupa un asiento fisico.
                $numerosAsietos = array(
                    $boleto->getAsientoBus()->getNumero()
                );
                $idSalida = $boleto->getSalida()->getId();
                $result = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientoOcupadosPorNumero($idSalida, $numerosAsietos, array());
                foreach ($result as $asientoBus) {
                    $numero = $asientoBus->getNumero();
                    $error = "m1El asiento con el número: " . $numero . " acaba de ser ocupado.";
                    $erroresAux->add(new ConstraintViolation($error, '', array(), '', '', null));
                    return UtilService::returnError($this, $error);
                }
            }
            
            if ($form->isValid() && count($erroresAux) === 0) {
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($boleto);
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
                } catch (\ErrorException $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this, UtilService::checkError($exc->getMessage()));
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    return UtilService::returnError($this, UtilService::checkError($exc->getMessage()));
                }
                
            }else{
               $error = UtilService::getErrorsToForm($form);
               if($error !== null && $error !== ""){
                   $mensajeServidor = "m1" . $error;
                   return UtilService::returnError($this, $mensajeServidor);
               }else{
                   foreach ($erroresAux as $item) {
                      $mensajeServidor = $item->getMessage();
                      return UtilService::returnError($this, $mensajeServidor);
//                      break;
                   }
               }
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Boleto:revertirCancelar.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
    
    /**
     * @Route(path="/reasignarBoleto.html", name="reasignarBoleto-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function reasignarBoletoAction(Request $request, $_route) {

        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('reasignar_boleto_command'); //Submit
                if($command !== null){
                    $id = $command["boletoOriginal"];
                }
            }
        }
        
        $boletoOriginal = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
        if ($boletoOriginal === null) {
            return UtilService::returnError($this, "m1El boleto con id: ". $id. " no existe.");
        }
        
        if($boletoOriginal->getEstado()->getId() === EstadoBoleto::REASIGNADO){
            $boletoOriginalAux = UtilService::getLastBoletoReasignado($boletoOriginal, $this);
            if ($boletoOriginalAux === null) {
                return UtilService::returnError($this, "m1No se pudo obtener la primera reasignación del boleto.");
            }else if($boletoOriginalAux instanceof Boleto){
                $boletoOriginal = $boletoOriginalAux;
            }else{
                $boletoOriginal = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($boletoOriginalAux);
                if ($boletoOriginal === null) {
                    return UtilService::returnError($this, "m1El boleto con id: ". $id. " no existe.");
                }
            }
        }

        if($boletoOriginal->getCamino() === true){
            return UtilService::returnError($this, "m1Los boletos de camino no se pueden reasignar.");
        }
        
        $agencia = false;
        $estacion = $this->getUser()->getEstacion();
        if($estacion === null){
            return UtilService::returnError($this, "m1El usuario no tiene estación asignada.");
        }else{
            if($estacion->getTipo()->getId() === \Acme\TerminalOmnibusBundle\Entity\TipoEstacion::AGENCIA){
                $agencia = true;
            }
        }
        
        $showVoucher = false;
        $reasignarBoletoModel = new ReasignarBoletoModel();
        $reasignarBoletoModel->setAgencia($agencia);
        if($agencia === true){
            $reasignarBoletoModel->setMonedaAgencia($this->getUser()->getEstacion()->getMonedaAgencia());
        }else{
            if($this->getUser()->getEstacion() !== null){
                $showVoucher = $this->getUser()->getEstacion()->getPermitirVoucherBoleto();
            }
        }
        
        $reasignarBoletoModel->setBoletoOriginal($boletoOriginal);
        $reasignarBoletoModel->setTipoDocuemento($boletoOriginal->getTipoDocumento());
        $reasignarBoletoModel->setClienteDocumento($boletoOriginal->getClienteDocumento());

        $form = $this->createForm(new ReasignarBoletoType($this->getDoctrine()), $reasignarBoletoModel, array(
            "user" => $this->getUser(),
            "em" => $this->getDoctrine()->getManager(),
        ));
        
        $mensajeServidor = "";
        if($boletoOriginal->getEstado()->getId() !== EstadoBoleto::EMITIDO){
            $mensajeServidor = "m1Solamente se puede reasignar un boleto que este en estado emitido. El estado actual de boleto ".
                    $boletoOriginal->getId()." es: " . $boletoOriginal->getEstado()->getNombre() . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if($boletoOriginal->getSalida() !== null){
            $fechaLimiteSistema = new \DateTime();
            $fechaLimiteSistema->modify("+2 hour");
            $fechaSalida = $boletoOriginal->getSalida()->getFecha();
            if($fechaLimiteSistema > $fechaSalida){
                
                $existeAutorizacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')
                        ->checkExisteAutorizacion($boletoOriginal->getId(), $estacion->getId(), TipoAutorizacionOperacion::REASIGNACION_POR_TIEMPO);
                if($existeAutorizacion === false){
                    $mensajeServidor = "m1El límite de tiempo para la resignación de un boleto son 2 horas antes del horario de la salida. El horario de la salida con identificador " . $boletoOriginal->getSalida()->getId() . " es " . $fechaSalida->format('Y-m-d H:i') . ". ";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }

            $estadoSalida = $boletoOriginal->getSalida()->getEstado();
            if($estadoSalida->getId() !== EstadoSalida::PROGRAMADA && $estadoSalida->getId() !== EstadoSalida::ABORDANDO && $estadoSalida->getId() !== EstadoSalida::INICIADA){
                $mensajeServidor = "m1Para reasignar un boleto su salida debe estar en estado programada, abordando o iniciada, el estado actual es: " . $estadoSalida->getNombre() . "."; 
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
        
        $maximoReasignaciones = 2;
        $boletosReasignados = array_merge(array($boletoOriginal), UtilService::getBoletosReasignados($boletoOriginal));
        $cantidadReasignaciones = count($boletosReasignados);
        $idReasignaciones = array();
        foreach ($boletosReasignados as $item) {
            $idReasignaciones[] = $item->getId();
        }
        if( $cantidadReasignaciones > $maximoReasignaciones){
            $mensajeServidor = "m1Ha superado el límite máximo de ".  strval($maximoReasignaciones)." reasignaciones. Secuencia de reasignaciones " . implode(", ", $idReasignaciones) . "."; 
            return UtilService::returnError($this, $mensajeServidor);
        }
        
        if ($request->isMethod('POST')) {
        
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                
                //El seteo del boleto no se puede quitar de aqui, debe estar delante del bind.
                $boletoOriginal->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->find(EstadoBoleto::REASIGNADO));
                $boletoOriginal->setFechaActualizacion(new \DateTime());
                $boletoOriginal->setUsuarioActualizacion($this->getUser());
                
                $form->bind($request);
                if (!$form->isValid()) {
                    throw new \RuntimeException("m1".UtilService::getErrorsToForm($form));
                }
                
                //--------------------------------------------------------------------------------------------------------
                //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LOS BOLETOS - INIT
                //--------------------------------------------------------------------------------------------------------
                if(trim($reasignarBoletoModel->getIdentificadorWeb()) !== ""){
                    $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')
                                        ->getBoletosByIdentificadorWeb($reasignarBoletoModel->getIdentificadorWeb());
                    if(count($boletos) !== 0){
                        return $this->procesarOperacionFallida($boletos, $request);
                    }
                }
                //--------------------------------------------------------------------------------------------------------
                //  PARCHE PARA CUANDO FALLE EN INTERNET NO DUPLICAR LOS BOLETOS - END
                //--------------------------------------------------------------------------------------------------------

                $boletos = array();
                $reservaciones = array();
                $estacionFacturacionEspecial = null;
                $importeTotalMonedabase = 0;
                $importeTotal = 0;
                $erroresAux = new ConstraintViolationList();
                $result = $this->convertirModeloEnBoletosReservaciones($reasignarBoletoModel, $erroresAux);
                if($erroresAux !== null && count($erroresAux) != 0){
                    throw new \RuntimeException("m1".$erroresAux->getIterator()->current()->getMessage());       
                }
                if(isset($result["estacionFacturacionEspecial"])){
                    $estacionFacturacionEspecial = $result["estacionFacturacionEspecial"];
                }
                if(isset($result["importeTotal"])){
                    $importeTotal = $result["importeTotal"];
                }
                if(isset($result["importeTotalMonedabase"])){
                    $importeTotalMonedabase = $result["importeTotalMonedabase"];
                }
                if(isset($result["listaBoletos"]) && isset($result["listaReservaciones"]))
                {
                    $boletos = $result["listaBoletos"];
                    if(count($boletos) <= 0){
                        throw new \RuntimeException("m1No se pudo generar el nuevo boleto de la reasignación.");
                    }
                    $reservaciones = $result["listaReservaciones"];
                    $numerosAsietos = array();
                    foreach ($boletos as $boleto) {
    //                    var_dump("validate...init");
                        $erroresItems = $this->get('validator')->validate($boleto);
                        if($erroresItems !== null && count($erroresItems) != 0){
                            throw new \RuntimeException("m1".$erroresItems->getIterator()->current()->getMessage());
                        }
                        $numerosAsietos[] = $boleto->getAsientoBus()->getNumero();
                    }
                    $idReservaciones = array();
                    foreach ($reservaciones as $reservacion) {
                        $erroresItems = $this->get('validator')->validate($reservacion);
                        if($erroresItems !== null && count($erroresItems) != 0){
                            throw new \RuntimeException("m1".$erroresItems->getIterator()->current()->getMessage());
                        }
                        $idReservaciones[] = $reservacion->getId();
                    }
                    //Se valida si existe un boleto o una reservacion activa asociada al numero de asiento de bus
                    //Esta situacion se presenta cuando dos usuarios trabajan a la vez con el mismo objeto.
                    $idSalida = $reasignarBoletoModel->getSalida()->getId();
                    $result = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientoOcupadosPorNumero($idSalida, $numerosAsietos, $idReservaciones);
                    foreach ($result as $asientoBus) {
                        $numero = $asientoBus->getNumero();
                        $boletoOriginal = $reasignarBoletoModel->getBoletoOriginal();
                        if($boletoOriginal !== null && $boletoOriginal->getAsientoBus() !== null && 
                                $boletoOriginal->getAsientoBus()->getNumero() !== $numero){
                            throw new \RuntimeException("m1El asiento con el número: " . $numero . " acaba de ser ocupado.");
                        }
                    }
                }

                // VALIDANDO QUE LA REASIGNACION SEA DE LA MISMA EMPRESA ------------------INIT
                $oldEmpresa = $boletoOriginal->getSalida()->getEmpresa();
                $newEmpresa = $reasignarBoletoModel->getSalida()->getEmpresa();
                if($oldEmpresa !== $newEmpresa){
                    throw new \RuntimeException("m1No se permiten reasignaciones entre empresas.");
                }
                // VALIDANDO QUE LA REASIGNACION SEA DE LA MISMA EMPRESA ------------------END

                $empresaCalendarioFacturaRuta = $reasignarBoletoModel->getSalida()->getEmpresa();
                $estacionUsuario = $this->getUser()->getEstacion();
                // VALIDANDO QUE LA IMPRESORA EXISTA ------------------INIT
                $impresora = null;
                $tipoDocumento = $reasignarBoletoModel->getTipoDocuemento();
                if( $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA ||
                    $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL){
                    if($reasignarBoletoModel->getSerieFactura() !== null){
                        $impresora = $reasignarBoletoModel->getSerieFactura()->getImpresora();
                        if($impresora === null){
                            throw new \RuntimeException("m1La impresora de facturación no está definida en el sistema.");
                        }else{
                            $impresorasDisponibles = $reasignarBoletoModel->getImpresorasDisponibles();
                            if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                                throw new \RuntimeException("m1La impresora: " . $impresora->getPath() . " no está disponible.");
                            }
                        }
                    }
                }else if($reasignarBoletoModel->getMovil() === 'false' && (
                        $tipoDocumento->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA || 
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA || 
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER)){
                    $impresoraOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ImpresoraOperaciones')
                                                ->getImpresoraOperacionesPorEstacion($estacionUsuario);
                    if($impresoraOperaciones !== null){
                        $impresora = $impresoraOperaciones->getImpresoraBoleto();
                        if($impresora !== null){
                            $impresorasDisponibles = $reasignarBoletoModel->getImpresorasDisponibles();
                            if(UtilService::checkExistImpresora($impresorasDisponibles, $impresora->getPath()) === false){
                                throw new \RuntimeException("m1La impresora: " . $impresora->getPath() . " no está disponible.");
                            }
                        }
                    }
                }
                // VALIDANDO QUE LA IMPRESORA EXISTA ------------------END

                $operacionesCaja = array();
                $totalNeto = doubleval($reasignarBoletoModel->getTotalNeto());
                if($totalNeto != 0 && (
                        $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA ||
                        $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL ||
                        $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER)){

                    //VALORES TOTALES DE LA FACTURA - INIT
                    $tipoPago = $reasignarBoletoModel->getTipoPago();
                    $totalNeto = $reasignarBoletoModel->getTotalNeto();
                    $monedaPago = $reasignarBoletoModel->getMonedaPago();
                    $tasa = $reasignarBoletoModel->getTasa();
                    $totalPago = $reasignarBoletoModel->getTotalPago();
                    $efectivo = $reasignarBoletoModel->getEfectivo();
                    $vuelto = $reasignarBoletoModel->getVuelto();
                    //VALORES TOTALES DE LA FACTURA - END

                    $user = $this->getUser();
                    $cajaPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->getCajaAbiertaPorMoneda($user, $monedaPago);
                    if($cajaPago === null){
                        throw new \RuntimeException("m1Para registrar el pago se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaPago->getSigla() . ".");
                    }else{
                        $tipoOperacionCaja = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoOperacionCaja')->find(TipoOperacionCaja::BOLETO);
                        $operacionCaja = new OperacionCaja();
                        $operacionesCaja[] = $operacionCaja;
                        $operacionCaja->setCaja($cajaPago);
                        $operacionCaja->setEmpresa($empresaCalendarioFacturaRuta);
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
                                        throw new \RuntimeException("m1Para registrar el vuelto se requiere que el usuario tenga una caja abierta en la moneda: " . $monedaVuelto->getSigla() . ".");
                                    }else{
                                        $operacionCajaVuelto = new OperacionCaja();
                                        $operacionesCaja[] = $operacionCajaVuelto;
                                        $operacionCajaVuelto->setCaja($cajaVuelto);
                                        $operacionCajaVuelto->setEmpresa($empresaCalendarioFacturaRuta);
                                        $operacionCajaVuelto->setTipoOperacion($tipoOperacionCaja);
                                        $operacionCajaVuelto->setFecha(new \DateTime());
                                        $operacionCajaVuelto->setImporte(abs($vuelto)); //+ es salida
                                        $descripcion = "Salida por entrega de vuelto por reajuste de boleto.";
                                        $operacionCajaVuelto->setDescripcion($descripcion);
                                    }
                                }
                            }
                        }
                        $descripcion = "Ingreso por reajuste de boleto.";
                        $operacionCaja->setDescripcion($descripcion);
                    }
                }
                foreach ($operacionesCaja as $operacionCaja) {
                    $erroresItems = $this->get('validator')->validate($operacionCaja);
                    if($erroresItems !== null && count($erroresItems) != 0){
                        throw new \RuntimeException("m1".$erroresItems->getIterator()->current()->getMessage());
                    }
                }

                if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                    $estacionUsuario = $this->getUser()->getEstacion();
                    if($estacionUsuario->getCheckAgenciaPrepago() === true){
                        if($boletoOriginal->getVoucherAgencia()->getBono() === true){
                            $importe = abs($estacionUsuario->getBonificacion()) - abs($importeTotal);
                            if($importe < 0){
                                throw new \RuntimeException("m1La agencia " . $estacionUsuario->getNombre() . " no tiene suficiente crédito en el saldo de bono.");
                            }
                            $estacionUsuario->setBonificacion($importe); 
                        }else{
                            $importe = abs($estacionUsuario->getSaldo()) - abs($importeTotal);
                            if($importe < 0){
                                throw new \RuntimeException("m1La agencia " . $estacionUsuario->getNombre() . " no tiene suficiente crédito en el saldo principal.");
                            }
                            $estacionUsuario->setSaldo($importe);
                        }
                    }
                }

                $omitirBoletos = array($boletoOriginal);
                $ruta = $reasignarBoletoModel->getSalida()->getItinerario()->getRuta();
                if($ruta->getObligatorioClienteDetalle() === true){
                    $erroresAux = new ConstraintViolationList();
                    $this->validarClientesPorSalida($boletos, $omitirBoletos, $reasignarBoletoModel->getSalida(), $erroresAux);
                    if($erroresAux !== null && count($erroresAux) != 0){
                        throw new \RuntimeException("m1".$erroresAux->getIterator()->current()->getMessage());     
                    }
                }
                
                foreach ($boletos as $boleto) {
                    if($boleto->getFacturaGenerada() !== null){
                        $em->persist($boleto->getFacturaGenerada());
                    }
                    $em->persist($boleto);
                }
                $em->persist($boletoOriginal);
                    
                foreach ($reservaciones as $reservacion) {
                    $em->persist($reservacion);
                }
                    
                foreach ($operacionesCaja as $operacionCaja) {
                    $em->persist($operacionCaja);
                }
                    
                if($estacionFacturacionEspecial !== null){
                    $estacionFacturacionEspecial->setPingFacturacionEspecial(UtilService::generateSimpleNumericPin());
                    $em->persist($estacionFacturacionEspecial);
                }   
                    
                if($tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
                    $em->persist($this->getUser()->getEstacion());
                }
                    
                $em->flush();
                    
                $facturas = array();
                $ids = array();
                foreach ($boletos as $boleto) {
                    $facturaGenerada = $boleto->getFacturaGenerada();
                    if($facturaGenerada !== null){
                        $em->refresh($facturaGenerada); //Recargar el consecutivo de la factura que lo asigno un trigger 
                        $factura = $facturaGenerada->getFactura();
                        $em->refresh($factura);
                        $facturaGenerada->validar();
                        $facturas[] = $facturaGenerada->getInfo2();
                    }    
                    $ids[] = $boleto->getId();
                    
                    $boletoBitacora1 = new BoletoBitacora();
                    $boletoBitacora1->setEstado($boletoOriginal->getEstado());
                    $boletoBitacora1->setFecha(new \DateTime());
                    $boletoBitacora1->setUsuario($this->getUser());
                    $boletoBitacora1->setDescripcion("Se reasignó el boleto hacia el id: " . $boleto->getId());
                    $boletoOriginal->addBitacoras($boletoBitacora1);
                    $em->persist($boletoBitacora1);
                    
                    if($boleto->getReasignado() === null){
                        throw new \RuntimeException("m1No se pudo obtener el boleto padre de la nueva reasignación.");
                    }
                    
                    $boletoBitacora2 = new BoletoBitacora();
                    $boletoBitacora2->setEstado($boleto->getEstado());
                    $boletoBitacora2->setFecha(new \DateTime());
                    $boletoBitacora2->setUsuario($this->getUser());
                    $boletoBitacora2->setDescripcion("Reasignó al id: " . $boleto->getReasignado()->getId());
                    $boleto->addBitacoras($boletoBitacora2);
                    $em->persist($boletoBitacora2);
                    
                    $em->flush();
                }
                    
                $info = "";
                if(count($facturas) != 0){
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                        $info = "Se crearon las facturas: " . implode(",", $facturas) . ".";
                    }else{
                        $info = "Se van a imprimir las facturas: " . implode(",", $facturas) . ".";
                    }
                }else{
                    $info = "Se crearon los boletos con identificadores: " . implode(",", $ids) . ".";
                }
                    
                $info .= " Reasignación número " . strval($cantidadReasignaciones) . " de un máximo de " . strval($maximoReasignaciones) . ".";
                $idReasignaciones = array_merge($ids, $idReasignaciones);
                $info .= " Secuencia de reasignaciones " . implode(", ", $idReasignaciones) . "."; 
                
                if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION || 
                            $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
                    $info .= "El próximo ping de autorización de la estación " . $estacionFacturacionEspecial
                                . " es " . $estacionFacturacionEspecial->getPingFacturacionEspecial() . ".";
                }
 
                //Por el diseño hasta 
                foreach ($operacionesCaja as $operacionCaja) {
                    $caja = $operacionCaja->getCaja();
                    $sobregirada = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->checkSobregiroCaja($caja);
                    if($sobregirada === true){
                        throw new \RuntimeException("m1No se puede realizar la operación porque la caja en la moneda: " . $caja->getMoneda()->getSigla() . " no cuenta con suficiente efectivo.");
                    }
                }
                    
                if(count($operacionesCaja) > 0){
                    $operacionCaja = $operacionesCaja[0];
                    $descripcion = $operacionCaja->getDescripcion();
                    if(count($ids) != 0){
                        $descripcion .= " IDs: " . implode(",", $ids) . ".";
                    }
                    if(count($facturas) != 0){
                        $descripcion .= " Facturas: " . implode(",", $facturas) . ".";
                    }
                    $operacionCaja->setDescripcion($descripcion);
                    $em->persist($operacionCaja);
                    $em->flush();
                }
                    
                $em->getConnection()->commit();
                    
                if(count($facturas) != 0 && ($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || 
                            $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL)){
                    return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaBoleto', array(
                        'request'  => $request,
                        'data' => implode(",", $ids),
                        'info' => $info
                    ));
                }else{
                    return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                        'mensajeServidor' => "m0",
                        'data' => implode(",", $ids),
                        'info' => $info
                    ));
                }
                    
            } catch (\RuntimeException $exc) {
//              var_dump($exc);
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                if(UtilService::startsWith($mensaje, 'm1')){ $mensajeServidor = $mensaje; }
                else{ $mensajeServidor = "m1Ha ocurrido un error en el sistema"; }
                return UtilService::returnError($this, $mensajeServidor);
            } catch (\ErrorException $exc) {
//              var_dump($exc);
                $em->getConnection()->rollback();
                return UtilService::returnError($this, UtilService::checkError($exc->getMessage()));
            } catch (\Exception $exc) {
//              var_dump($exc);
                $em->getConnection()->rollback();
                return UtilService::returnError($this, UtilService::checkError($exc->getMessage()));
            }
        }
        
        return $this->render('AcmeTerminalOmnibusBundle:Boleto:reasignar.html.twig', array(
            'showVoucher' => $showVoucher,
            'agencia' => $agencia,
            'estacion' => $this->getUser()->getEstacion(),
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor,
            'idTipoDocumento' => $boletoOriginal->getTipoDocumento()->getId()
        ));        
    }
    
    public function procesarOperacionFallida($boletos, $request){
        
        $ids = array();
        $facturas = array();
        $tipoDocumento = null;
        $estacionFacturacionEspecial = null;
        foreach ($boletos as $boleto) {
            $ids[] = $boleto->getId();
            if($boleto->getFacturaGenerada() !== null){
                $facturas[] = $boleto->getFacturaGenerada()->getInfo2();
            }
            if($tipoDocumento === null){
                $tipoDocumento = $boleto->getTipoDocumento();
            }
            if($estacionFacturacionEspecial === null){
                $estacionFacturacionEspecial = $boleto->getEstacionFacturacionEspecial();
            }
        }
        
        $info = "";
        if(count($facturas) != 0){
            if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION){
                $info = "Se crearon las facturas: " . implode(",", $facturas) . ".";
            }else{
                $info = "Se van a imprimir las facturas: " . implode(",", $facturas) . ".";
            }
        }else{
            $info = "Se crearon los boletos con identificadores: " . implode(",", $ids) . ".";
        }
        
        if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION || $tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION){
            $info .= "El próximo ping de autorización de la estación " . $estacionFacturacionEspecial
                . " es " . $estacionFacturacionEspecial->getPingFacturacionEspecial() . ".";
        }
                    
        if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL){
            return $this->forward('AcmeTerminalOmnibusBundle:Print:printFacturaBoleto', array(
                'request'  => $request,
                'data' => implode(",", $ids),
                'info' => $info
            ));
        }else{
            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
                'mensajeServidor' => "m0",
                'data' => implode(",", $ids),
                'info' => $info
            ));
        }
    }
    
    public function sendEmailReporteActualizacionSalida(Salida $salida)
    {
        
        $resumen = array();
        $valuesBoletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarDetalleBoletosBySalida($salida->getId());
        if(count($valuesBoletos) !== 0){
            foreach ($valuesBoletos as $item) {
                $resumen[$item['nombreEstacionCreacion']] = $item;
            }
        }
        
        $valuesEncomiendas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarDetalleEncomiendaBySalida($salida->getId());
        if(count($valuesEncomiendas) !== 0){
            foreach ($valuesEncomiendas as $item) {
                if(isset($resumen[$item['nombreEstacionCreacion']])){
                    $temp = array_merge($resumen[$item['nombreEstacionCreacion']], $item);
                    $resumen[$item['nombreEstacionCreacion']] = $temp;
                }else{
                    $resumen[$item['nombreEstacionCreacion']] = $item;
                }
            }
        }
        
        $itinerario = "Cíclico";
        if($salida->getItinerario() instanceof ItinerarioEspecial){
          $itinerario =  "Especial";
        }
        
         $correos = $salida->getBus()->getEmpresa()->getCorreos();
//         $correos = array("javiermarti84@gmail.com");
         if($correos !== null && count($correos) !== 0){
             $now = new \DateTime();
             $now = $now->format('Y-m-d H:i:s');
             $fechaYhora = $salida->getFecha()->format('d-m-Y H:i:s');
             $subject = "NSE_UPDATE_" .$now .". Notificación de salida del " . $fechaYhora . ", en la ruta " . $salida->getItinerario()->getRuta();
             UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_salida_update.html.twig', array(
                'salida' => $salida,
                'estacionActualizacion' => $this->getUser()->getEstacion(),
                'resumen' => $resumen,
                'itinerario' => $itinerario
             )));
         }
    }
    
    
    /**
     * @Route(path="/registrarAutorizacionBoleto.html", name="registrarAutorizacionBoleto-case1", defaults={"_format"="html"}, requirements={"_format"="html"})
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function registrarAutorizacionBoletoAction(Request $request, $_route) {
        
        $mensajeServidor = "";
        $id = $request->query->get('id');
        if (is_null($id)) {
            $id = $request->request->get('id');
            if (is_null($id)) {
                $command = $request->request->get('registrar_autorizacion_boleto_command'); //Submit
                if($command !== null){
                    $id = $command["idBoleto"];
                }
            }
        }
        
        if(is_null($id)){
            return UtilService::returnError($this, "No se pudo obtener el id del boleto");
        }
        
        $boleto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id); 
        if ($boleto === null) {
            return UtilService::returnError($this, "El boleto con id: ".$id." no existe.");
        }
        
        if($boleto->getEstado()->getId() !== EstadoBoleto::EMITIDO){
            return UtilService::returnError($this, "Solamente se puede registrar una solicitud de autorizacion de boletos en estado Emitido.");
        }
        
        $autorizacionOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')
                        ->listarAutorizacionesDeOperacionesPendientesByBoleto($boleto->getId());
        if(count($autorizacionOperaciones) > 0){
            $descripcion = "Existe una solicitud de autorización pendiente de revisión con ID: '"  . $autorizacionOperaciones[0]->getId(). "' " . 
                                    "asociado al boleto con ID: '" . $boleto->getId() . "'.";
            return UtilService::returnError($this, $descripcion);
        }
        
        $autorizacionOperacion = new AutorizacionOperacion();
        $autorizacionOperacion->setEstacion($this->getUser()->getEstacion());
        $autorizacionOperacion->setBoleto($boleto);
        $autorizacionOperacion->setEstado($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:EstadoAutorizacionOperacion')->find(EstadoAutorizacionOperacion::REGISTRADO));
        $autorizacionOperacion->setEstacionCreacion($this->getUser()->getEstacion());
        $autorizacionOperacion->setUsuarioCreacion($this->getUser());
        
        $form = $this->createForm(new RegistrarAutorizacionBoletoType($this->getDoctrine()), $autorizacionOperacion, array(
            'idBoleto' => $boleto->getId()
        ));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                
                $autorizacionOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionOperacion')
                        ->listarAutorizacionesDeOperacionesByBoletoByTipo($boleto->getId(), $autorizacionOperacion->getTipo()->getId());
                if(count($autorizacionOperaciones) > 0){
                    $descripcion = "Existe una solicitud de autorización con ID: '"  . $autorizacionOperaciones[0]->getId(). "' " . 
                                    "asociado al boleto con ID: '" . $boleto->getId() . "'" . 
                                    ", del tipo: '" . $autorizacionOperacion->getTipo()->getNombre() . "'" . 
                                    ". El estado actual es: '" . $autorizacionOperaciones[0]->getEstado()->getNombre() . "'.";
                    return UtilService::returnError($this, $descripcion);
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    
                    $em->persist($autorizacionOperacion);
                    $em->flush();
                    
                    $boletoBitacora = new BoletoBitacora();
                    $boletoBitacora->setEstado($boleto->getEstado());
                    $boletoBitacora->setFecha(new \DateTime());
                    $boletoBitacora->setUsuario($this->getUser());
                    $descripcion = "Se registro la solicitud de autorización ID: " . $autorizacionOperacion->getId() . 
                            ", tipo de operación: " . $autorizacionOperacion->getTipo()->getNombre() . 
                            ", con el motivo: " . $autorizacionOperacion->getMotivo() . ".";
                    $boletoBitacora->setDescripcion($descripcion);
                    $boleto->addBitacoras($boletoBitacora);
                    $em->persist($boletoBitacora);
                    
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
        
        return $this->render('AcmeTerminalOmnibusBundle:Boleto:registrarAutorizacion.html.twig', array(
            'form' => $form->createView(),
            'route' => $_route,
            'mensajeServidor' => $mensajeServidor
        ));
    }
}

?>