<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Acme\BackendBundle\Services\UtilService;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\TerminalOmnibusBundle\Entity\Boleto;
use Symfony\Component\HttpFoundation\Response;
use Acme\TerminalOmnibusBundle\Entity\TipoEncomienda;
use Acme\BackendBundle\Exceptions\RuntimeExceptionCode;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Acme\TerminalOmnibusBundle\Entity\ClaseAsiento;
use Acme\TerminalOmnibusBundle\Entity\Encomienda;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\Cliente;

/**
 *   @Route(path="/ajax")
 */
class MultiActionController extends Controller
{

    /**
     * @Route(path="/checkExpiredPassword.json", name="ajaxCheckExpiredPassword")
     */
    public function checkExpiredPasswordAction()
    {
        //        var_dump("checkExpiredPasswordAction-init");
        $dias = 0;
        try {
            $credentialsExpired = $this->getUser()->getCredentialsExpireAt();
            //            var_dump($credentialsExpired);
            $hoy = new \DateTime();
            //            var_dump($hoy);
            $dias = UtilService::compararFechas($credentialsExpired, $hoy);
            if ($dias < 0) {
                $dias = 0;
            } else if ($dias > 19) {
                $dias = 20;
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            echo $exc->getTraceAsString();
        }

        $response = new JsonResponse();
        $response->setData(array(
            'dias' => $dias
        ));
        return $response;
    }

    /**
     * @Route(path="/working.html", name="ajaxWorking")
     */
    public function workingAction()
    {
        return new Response("ok");
    }

    /**
     * @Route(path="/listaBus.json", name="ajaxListarBus")
     * @Secure(roles="ROLE_USER")
     */
    public function listaBusAction()
    {

        $options = array();
        try {
            $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Bus');
            $buses = $repository->findAll();
            foreach ($buses as $bus) {
                $options[] = $bus->getCodigo();
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            echo $exc->getTraceAsString();
        }

        $response = new JsonResponse();
        $response->setData(array(
            'options' => $options
        ));
        return $response;
    }

    //    /**
    //     * @Route(path="/getPilotoByBus.json", name="ajaxGetPilotoByBus")
    //     * @Secure(roles="ROLE_USER")
    //    */
    //    public function getPilotoByBusAction(Request $request) {
    //        $options = array();
    //        try {
    //            $codigoBus = $request->query->get('codigoBus');
    //            if (is_null($codigoBus)) {
    //                $codigoBus = $request->request->get('codigoBus');
    //            }
    //            if($codigoBus === null || trim($codigoBus) !== ""){
    //                
    //                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Bus');
    //                $bus = $repository->find($codigoBus);
    //                if (!$bus) {
    //                    throw $this->createNotFoundException('No se encontro el bus con código:'.$codigoBus. ".");
    //                }
    //                $piloto = $bus->getPiloto();
    //                if($piloto !== null){
    //                    $item = array(
    //                        'id' => $piloto->getId(),
    //                        'text' => $piloto->__toString()
    //                    );
    //                    $options['piloto1'] = $item;
    //                }
    //                $pilotoAux = $bus->getPilotoAux();
    //                if($pilotoAux !== null){
    //                    $item = array(
    //                        'id' => $pilotoAux->getId(),
    //                        'text' => $pilotoAux->__toString()
    //                    );
    //                    $options['piloto2'] = $item;
    //                }
    //            }
    //            
    //        } catch (\Exception $exc) {
    //            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
    ////            echo $exc->getTraceAsString();
    //        }
    //        
    //        $response = new JsonResponse();
    //        $response->setData(array(
    //            'options' => $options
    //        ));
    //        return $response;
    //    }

    /**
     * @Route(path="/listarClientesPaginando.json", name="ajaxListarClientesPaginando")
     * @Secure(roles="ROLE_USER") 
     */
    public function listarClientesPaginandoAction(Request $request)
    {
        //        $this->get("logger")->warn("listarClientesPaginandoAction-init");
        $options = array();
        try {
            $pageLimit = $request->query->get('page_limit');
            if (is_null($pageLimit)) {
                $pageLimit = $request->request->get('page_limit');
            }
            $term = $request->query->get('term');
            if (is_null($term)) {
                $term = $request->request->get('term');
            }
            $id = $request->query->get('id');
            if (is_null($id)) {
                $id = $request->request->get('id');
            }

            //    CREDENCIALES EN ENTORNO DE PRUEBAS DE e-FORCON PARA MITOCHA            
            $sUsuarioMitocha = 'toper1-mayaoro';
            $sClaveMitocha = 'Maya0$o@2021';

            //    $sUsuario = 'ti-20336837';
            //    $sClave = 'Pionera/12';    

            $sUsuario = 'operws1-fdn';
            $sClave = '$$FDN@2020Fel';
            //    
            //    $termNitCliente = trim($term);
            $termNitCliente = preg_replace('([^A-Za-z0-9])', '', $term);
            //
            $sNitReceptor = $termNitCliente;
            //    
            //

            if (is_numeric(substr($sNitReceptor, 0, 1))) {

                require_once('lib/nusoap.php');

                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconReceptoresFel.asmx?WSDL', 'wsdl');
                //                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;
                $soapClient->debug_flag = true;
                $param = array('sUsuario' => $sUsuario, 'sClave' => $sClave, 'sNitReceptor' => $sNitReceptor);
                $paramTest = array('sUsuario' => $sUsuarioMitocha, 'sClave' => $sClaveMitocha, 'sNitReceptor' => $sNitReceptor);
                $result = $soapClient->call('ObtenerIdReceptor', $param);

                $WSResultadoReceptor = $result['ObtenerIdReceptorResult']['WSResultado'];

                if ($WSResultadoReceptor === "true") {

                    $cliente = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->findOneByNit($sNitReceptor);

                    //                        $nacionalidad = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->findOneByNombre('Guatemalteca');
                    //                        $nacionalidadCliente = $nacionalidad->getId();           
                    if ($cliente === null) {

                        $post = new Cliente();
                        $post->setNit($result['ObtenerIdReceptorResult']['WSIdReceptor']);
                        $post->setNitCreacionCopia($result['ObtenerIdReceptorResult']['WSIdReceptor']);
                        $post->setNombre($result['ObtenerIdReceptorResult']['WSRazonSocial']);
                        $post->setNombreCreacionCopia($result['ObtenerIdReceptorResult']['WSRazonSocial']);
                        $post->setNacionalidad($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->findOneByNombre('Guatemalteca'));
                        $post->setTipoDocumento($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->findOneBySigla('DPI'));
                        $post->setUsuarioCreacion($this->getUser());

                        //Entity Manager
                        $em = $this->getDoctrine()->getEntityManager();

                        //Persistimos en el objeto
                        $em->persist($post);

                        //Insertarmos en la base de datos
                        $em->flush();
                    }
                } else {

                    $soapClientTest = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/WSForconReceptoresFel.asmx?WSDL', 'wsdl');
                    $soapClientTest->soap_defencoding = 'UTF-8';
                    $soapClientTest->decode_utf8 = false;
                    $soapClientTest->debug_flag = true;

                    $resultTest = $soapClientTest->call('ObtenerIdReceptor', $paramTest);

                    $WSResultadoReceptorTest = $resultTest['ObtenerIdReceptorResult']['WSResultado'];

                    if ($WSResultadoReceptorTest === "true") {

                        $cliente = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->findOneByNit($sNitReceptor);

                        //                        $nacionalidad = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->findOneByNombre('Guatemalteca');
                        //                        $nacionalidadCliente = $nacionalidad->getId();

                        if ($cliente === null) {

                            $post = new Cliente();
                            $post->setNit($resultTest['ObtenerIdReceptorResult']['WSIdReceptor']);
                            $post->setNitCreacionCopia($resultTest['ObtenerIdReceptorResult']['WSIdReceptor']);
                            $post->setNombre($resultTest['ObtenerIdReceptorResult']['WSRazonSocial']);
                            $post->setNombreCreacionCopia($resultTest['ObtenerIdReceptorResult']['WSRazonSocial']);
                            $post->setNacionalidad($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->findOneByNombre('Guatemalteca'));
                            $post->setTipoDocumento($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->findOneBySigla('DPI'));
                            $post->setUsuarioCreacion($this->getUser());

                            //Entity Manager
                            $em = $this->getDoctrine()->getEntityManager();

                            //Persistimos en el objeto
                            $em->persist($post);

                            //Insertarmos en la base de datos
                            $em->flush();
                        }
                    }
                }
            }

            $items = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Cliente')->listarClientesPaginandoNativo($pageLimit, $term, $id);
            foreach ($items as $item) {
                $text = $item["nit"] . " / " . trim($item["nombre"]);
                $tipoDocumento = $item["siglaDocumento"];
                $dpi = $item["dpi"];
                if ($dpi !== null && trim($dpi) !== "") {
                    $text .= " / " . $tipoDocumento . ":" . $dpi;
                }
                $item = array(
                    "id" => $item["id"],
                    "text" => $text
                );
                $options[] = $item;
            }
        } catch (\RuntimeException $exc) {
            //            var_dump($exc);
            $this->get("logger")->warn("listarClientesPaginandoAction-exc1:" . $exc->getTraceAsString());
        } catch (\Exception $exc) {
            //            var_dump($exc);
            $this->get("logger")->warn("listarClientesPaginandoAction-exc2:" . $exc->getTraceAsString());
        }

        $response = new JsonResponse();
        $response->setData(array(
            'options' => $options
        ));
        return $response;
    }

    /**
     * @Route(path="/listarBoletosPaginando.json", name="ajaxListarBoletosPaginando")
     * @Secure(roles="ROLE_USER")
     */
    public function listarBoletosPaginandoAction(Request $request)
    {
        //        $this->get("logger")->warn("listarBoletosPaginandoAction-init");
        $options = array();
        try {
            $pageLimit = $request->query->get('page_limit');
            if (is_null($pageLimit)) {
                $pageLimit = $request->request->get('page_limit');
            }
            $term = $request->query->get('term');
            if (is_null($term)) {
                $term = $request->request->get('term');
            }
            $id = $request->query->get('id');
            if (is_null($term)) {
                $id = $request->request->get('id');
            }
            $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto');
            $boletos = $repository->listarBoletosPaginando($this->getUser(), $pageLimit, $term, $id);
            foreach ($boletos as $boleto) {
                $clienteBoleto = $boleto->getClienteBoleto();
                $item = array(
                    "id" => $boleto->getId(),
                    "text" => $boleto->getId() . " - " . $clienteBoleto->getNombre()
                );
                $options[] = $item;
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            var_dump($exc);
            //            echo $exc->getTraceAsString();
        }

        $response = new JsonResponse();
        $response->setData(array(
            'options' => $options
        ));
        return $response;
    }

    /**
     * @Route(path="/listarSalidas.json", name="ajaxlistarSalidas")
     * @Secure(roles="ROLE_USER")
     */
    public function listaSalidasAction()
    {
        $optionSalidas = array();

        $fecha = $this->get('request')->request->get('fecha');
        $estacion = $this->get('request')->request->get('estacion');
        if ($fecha !== null && trim($fecha) !== "" && $estacion !== null && trim($estacion) !== "") {
            $salidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')
                ->getSalidasByFechaEstacion($fecha, $estacion);
            foreach ($salidas as $item) {
                $optionSalidas[] = array(
                    "id" => $item->getId(),
                    "text" => $item->getInfo3()
                );
            }
        }
        $response = new JsonResponse();
        $response->setData(array(
            'optionSalidas' => $optionSalidas
        ));
        return $response;
    }

    /**
     * @Route(path="/listarSalidasByFecha.html", name="ajaxlistarSalidasByFecha")
     * @Secure(roles="ROLE_USER")
     */
    public function listaSalidasByFechaAction()
    {

        try {
            $results = "";
            $fecha = $this->get('request')->request->get('fecha');
            if ($fecha !== null && trim($fecha) !== "") {

                $salidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidasByFecha($fecha);
                foreach ($salidas as $salida) {
                    $results .= '<option value="' . $salida->getId() . '">' . $salida->getInfo3() . '</option>';
                }
            }
            return  new Response($results);
        } catch (\Exception $exc) {
            return UtilService::returnError($this);
        }
    }

    /**
     * @Route(path="/listarSalidasByFecha2.json", name="ajaxlistarSalidasByFecha2")
     * @Secure(roles="ROLE_USER")
     */
    public function listarSalidasByFecha2Action()
    {
        $optionSalidas = array();
        $fecha = $this->get('request')->request->get('fecha');
        if ($fecha !== null && trim($fecha) !== "") {
            $salidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->getSalidasByFechaByUser($fecha, $this->getUser());
            foreach ($salidas as $item) {
                $optionSalidas[] = array(
                    "id" => $item->getId(),
                    "hora" => $item->getFecha()->format('h:i A'),
                    "ruta" => $item->getItinerario()->getRuta()->getNombre(),
                    "clase" => $item->getItinerario()->getTipoBus()->getClase()->getNombre(),
                    "empresa" => $item->getEmpresa()->getAlias(),
                    "idEstado" => $item->getEstado()->getId(),
                    "estado" => $item->getEstado()->getNombre(),
                    "bus" => $item->getBus() === null ? "" : $item->getBus()->getCodigo(),
                    "piloto1" => $item->getPiloto() === null ? "" : $item->getPiloto()->getFullName(),
                    "piloto2" => $item->getPilotoAux() === null ? "" : $item->getPilotoAux()->getFullName()
                );
            }
        }
        $response = new JsonResponse();
        $response->setData(array(
            'optionSalidas' => $optionSalidas
        ));
        return $response;
    }

    //    /**
    //     * @Route(path="/listaAsientosDisponiblesBySalida.html", name="ajaxListaAsientosDisponiblesBySalida")
    //     * @Secure(roles="ROLE_USER")
    //     */
    //    public function listaAsientosDisponiblesBySalidaAction() {
    //        try {
    //            $results = "";
    //            $salida = $this->get('request')->request->get('salida');
    //            $claseAsiento = $this->get('request')->request->get('claseAsiento');
    //            $idCortesia = $this->get('request')->request->get('idCortesia');
    //           
    //            if($salida !== null && trim($salida) !== "" && $claseAsiento !== null && trim($claseAsiento) !== ""){
    //                $asientoActual = null;
    //                if($idCortesia !== null && trim($idCortesia) !== ""){
    //                    $cortesia = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionCortesia')->find($idCortesia);
    //                    $asientoActual = $cortesia->getRestriccionAsientoBus();
    //                }
    //                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus');
    //                $asientos = $repository->getAsientosDisponiblesBySalidaId($salida, $claseAsiento, $asientoActual);
    //                foreach($asientos as $asiento)
    //                {
    //                    $label  = "Nro:" . $asiento->getNumero();
    //                    if($asiento->getNivel2() === false){
    //                        $label .= ", Nivel:1";
    //                    }else{
    //                        $label .= ", Nivel:2";
    //                    }
    //                    $label .= ", Clase:" . $asiento->getClase()->getNombre();
    //                    $results .= '<option value="'.$asiento->getId().'">'.$label.'</option>';
    //                }
    //            }
    //            return  new Response($results);
    //            
    //        } catch (Exception $exc) {
    //            return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
    //                'mensajeServidor' => "m1Ha ocurrido un error en el sistema."
    //            ));
    //        }
    //        
    //    }

    //    /**
    //     * @Route(path="/listarSalidasPaginando.json", name="ajaxListarSalidasPaginando")
    //     * @Secure(roles="ROLE_VENDEDOR_BOLETOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPER_ADMIN, ROLE_ADMIN") 
    //   */
    //    public function listarSalidasPaginandoAction(Request $request) {
    //        $this->get("logger")->warn("listarSalidasPaginando-init");
    //        $options = array();
    //        try {
    //            $pageLimit = $request->query->get('page_limit');
    //            if (is_null($pageLimit)) {
    //                $pageLimit = $request->request->get('page_limit');
    //            }
    //            $term = $request->query->get('term');
    //            if (is_null($term)) {
    //                $term = $request->request->get('term');
    //            }
    //            $id = $request->query->get('id');
    //            if (is_null($id)) {
    //                $id = $request->request->get('id');
    //            }            
    //            $estacionOrigen = $request->query->get('estacionOrigen');
    //            if (is_null($estacionOrigen)) {
    //                $estacionOrigen = $request->request->get('estacionOrigen');
    //            }
    //            $estacionDestino = $request->query->get('estacionDestino');
    //            if (is_null($estacionDestino)) {
    //                $estacionDestino = $request->request->get('estacionDestino');
    //            }
    //            $fecha = $request->query->get('fecha');
    //            if (is_null($fecha)) {
    //                $fecha = $request->request->get('fecha');
    //            }
    //            if($estacionOrigen !== null && trim($estacionOrigen) !== "" && $estacionDestino !== null && trim($estacionDestino) !== ""
    //                && $fecha !== null && trim($fecha) !== "")
    //            {
    //                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Salida');
    //                $salidas = $repository->listarSalidasPaginando($fecha, $estacionOrigen, $estacionDestino, $pageLimit, $term, $id);
    //                foreach($salidas as $salida)
    //                {
    //                    $item = array(
    //                        "id" => $salida->getId(),
    //                        "text" => $salida->__toString()    
    //                    );
    //                    $options[] = $item;
    //                }
    //            }
    //            
    //        } catch (\Exception $exc) {
    //            $this->get("logger")->error("listarSalidasPaginando-exc", $exc);
    //            echo $exc->getTraceAsString();
    //        }
    //        
    //        $response = new JsonResponse();
    //        $response->setData(array(
    //            'options' => $options
    //        ));
    //        return $response;
    //    }

    /**
     * @Route(path="/getInformacionPorSalida.json", name="ajaxGetInformacionPorSalida")
     * @Secure(roles="ROLE_USER") 
     */
    public function getInformacionPorSalida(Request $request)
    {

        $message = "";
        $optionEstacionOrigen = array();
        $optionEstacionDestino = array();
        $optionsEstacionesIntermedias = array();
        $optionListaAsientos = array();
        $optionListaSenales = array();
        $optionBoletos = array();
        $optionReservaciones = array();
        $optionSeriesFacturas = array();

        try {
            $idSalida = $request->query->get('idSalida');
            if (is_null($idSalida)) {
                $idSalida = $request->request->get('idSalida');
            }
            $showTime = $request->query->get('showTime');
            if (is_null($showTime)) {
                $showTime = $request->request->get('showTime');
            }

            //            var_dump($idSalida);
            $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->getDatosParcialesSalida($idSalida);
            if ($salida === null) {
                throw new \RuntimeException("La salida con id: " . $idSalida . " no existe.");
            }
            $ruta = $salida->getItinerario()->getRuta();
            $estacionOrigen = $ruta->getEstacionOrigen();
            $estacionDestino = $ruta->getEstacionDestino();
            $listaEstacionesIntermedia = $ruta->getListaEstacionesIntermediaOrdenadas();

            $mapEstacionWithTime = array();
            if ($showTime === true || $showTime === 'true') {
                $mapEstacionWithTime = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getEstacionesWithTiemposByRutaByClaseBus($ruta, $salida->getItinerario()->getTipoBus()->getClase());
            }

            $optionEstacionOrigen[] = array(
                "id" => $estacionOrigen->getId(),
                "text" => $estacionOrigen->getAliasNombre(),
                "time" => $salida->getFecha()->format('h:iA'),
                "minutes" => 0
            );

            $minutosEnd = 9999;
            $timeEnd = "";
            if (isset($mapEstacionWithTime[$estacionDestino->getId()])) {
                $fechaAux = clone $salida->getFecha();
                $minutosEnd = intval($mapEstacionWithTime[$estacionDestino->getId()]);
                $fechaAux->modify("+" . $minutosEnd . " minutes");
                $timeEnd = $fechaAux->format('h:iA');
            }
            $optionEstacionDestino[] = array(
                "id" => $estacionDestino->getId(),
                "text" => $estacionDestino->getAliasNombre(),
                "time" => $timeEnd,
                "minutes" => $minutosEnd
            );

            foreach ($listaEstacionesIntermedia as $item) {
                $estacion = $item->getEstacion();
                $minutos = 9999;
                $time = "";
                if (isset($mapEstacionWithTime[$estacion->getId()])) {
                    $fechaAux = clone $salida->getFecha();
                    $minutos = intval($mapEstacionWithTime[$estacion->getId()]);
                    $fechaAux->modify("+" . $minutos . " minutes");
                    $time = $fechaAux->format('h:iA');
                }
                $optionsEstacionesIntermedias[] = array(
                    "id" => $estacion->getId(),
                    "text" => $estacion->getAliasNombre(),
                    "time" => $time,
                    "minutes" => $minutos
                );
            }

            if ($showTime === true || $showTime === 'true') {
                usort($optionsEstacionesIntermedias, function ($a, $b) {
                    return intval($a['minutes']) === intval($b['minutes']) ? 0 : (intval($a['minutes']) > intval($b['minutes'])) ? 1 : -1;
                });
            }

            $estacionUsuario = $this->getUser()->getEstacion();
            if ($estacionUsuario !== null) {
                $facturas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')
                    ->listarFacturaPorEstacionEmpresaServicio($estacionUsuario, $salida->getEmpresa(), ServicioEstacion::BOLETO, true);
                foreach ($facturas as $item) {
                    $optionSeriesFacturas[] = array(
                        "id" => $item->getId(),
                        "text" => $item->getSerieResolucionFactura() . ' (' . $item->getEmpresa()->getAlias() . ')(' .
                            $item->getMinimoResolucionFactura() . "-" . $item->getMaximoResolucionFactura() . ")",
                        "idEmpresa" => $salida->getEmpresa()->getId()
                    );
                }
            }

            $camino = $request->query->get('camino');
            if (is_null($camino)) {
                $camino = $request->request->get('camino');
            }
            if (strval($camino) !== 'true') {

                $tipoBus = $salida->getTipoBus();
                $listaAsientos = $tipoBus->getListaAsiento();
                foreach ($listaAsientos as $asiento) {
                    $optionListaAsientos[] = array(
                        "id" => $asiento->getId(),
                        "nivel2" => $asiento->getNivel2(),
                        "numero" => $asiento->getNumero(),
                        "clase" => $asiento->getClase()->getId(),
                        "coordenadaX" => $asiento->getCoordenadaX(),
                        "coordenadaY" => $asiento->getCoordenadaY(),
                    );
                }

                $listaSenal = $tipoBus->getListaSenal();
                foreach ($listaSenal as $senal) {
                    $optionListaSenales[] = array(
                        "id" => $senal->getId(),
                        "nivel2" => $senal->getNivel2(),
                        "tipo" => $senal->getTipo()->getId(),
                        "coordenadaX" => $senal->getCoordenadaX(),
                        "coordenadaY" => $senal->getCoordenadaY(),
                    );
                }

                $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getDatosParcialesBoletosActivosPorSalida($idSalida);
                foreach ($boletos as $boleto) {
                    $optionBoletos[] = array(
                        "id" => $boleto->getId(),
                        "tipo" => "B", //Boleto
                        "revendidoEnEstacion" => $boleto->getRevendidoEnEstacion(),
                        "revendidoEnCamino" => $boleto->getRevendidoEnCamino(),
                        "tipoDocumento" => $boleto->getTipoDocumento()->getId(),
                        "numero" => $boleto->getAsientoBus()->getNumero(),
                        "clase" => $boleto->getAsientoBus()->getClase()->getId()
                    );
                }

                $reservaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->getDatosParcialesReservacionesActivosPorSalida($idSalida);
                foreach ($reservaciones as $reservacion) {
                    $optionReservaciones[] = array(
                        "id" => $reservacion->getId(),
                        "tipo" => "R", //Reservacion
                        "numero" => $reservacion->getAsientoBus()->getNumero(),
                        "clase" => $reservacion->getAsientoBus()->getClase()->getId(),
                        "clinteId" => $reservacion->getCliente()->getId()
                    );
                }
            }
        } catch (\RuntimeException $exc) {
            //            var_dump($exc);
            $message = $exc->getMessage();
        } catch (\Exception $exc) {
            //            var_dump($exc);
            $message = $exc->getMessage();
        }
        $response = new JsonResponse();
        $response->setData(array(
            'optionEstacionOrigen' => $optionEstacionOrigen,
            'optionEstacionDestino' => $optionEstacionDestino,
            'optionsEstacionesIntermedias' => $optionsEstacionesIntermedias,
            'optionListaAsientos' => $optionListaAsientos,
            'optionListaSenales' => $optionListaSenales,
            'optionBoletos' => $optionBoletos,
            'optionReservaciones' => $optionReservaciones,
            'optionSeriesFacturas' => $optionSeriesFacturas,
            'error' => $message
        ));
        return $response;
    }

    /**
     * @Route(path="/getRutasPorEstacion.json", name="ajaxGetRutasPorEstacion")
     * @Secure(roles="ROLE_USER")  
     */
    public function getRutasPorEstacion(Request $request)
    {

        $optionRutas = array();
        $error = "";

        try {
            $idEstacionOrigen = $request->query->get('idEstacionOrigen');
            if (is_null($idEstacionOrigen)) {
                $idEstacionOrigen = $request->request->get('idEstacionOrigen');
            }
            $rutaInicial = $request->query->get('rutaInicial');
            if (is_null($rutaInicial)) {
                $rutaInicial = $request->request->get('rutaInicial');
            }
            if (is_null($rutaInicial)) {
                $rutaInicial = false;
            }

            if ($idEstacionOrigen !== null && is_numeric($idEstacionOrigen)) {

                $fechaDia = new \DateTime();
                $rutas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Ruta')->listarRutasPorEstaciones($idEstacionOrigen, null, $rutaInicial);
                foreach ($rutas as $ruta) {
                    $empresaStr = "";
                    $empresa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                        ->getEmpresaQueFactura($ruta, $fechaDia);
                    if ($empresa !== null) {
                        $empresaStr = " ( " . $empresa->getAlias() . " ) ";
                    }
                    $text = strval($ruta->getCodigo()) . " - " . $ruta->getNombre() . $empresaStr;
                    $optionRutas[] = array(
                        "id" => $ruta->getCodigo(),
                        "text" =>  $text,
                        "idEmpresa" => ($empresa !== null) ? $empresa->getId() : ""
                    );
                }
            }
        } catch (\RuntimeException $exc) {
            $error = $exc->getMessage();
            if (UtilService::startsWith($error, 'm1')) {
                $error = str_replace("m1", "", $error);
            } else {
                $error = "Ha ocurrido un error en el sistema";
            }
        } catch (\Exception $exc) {
            $error = "Ha ocurrido un error en el sistema.";
        }
        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'optionRutas' => $optionRutas,
        ));
        return $response;
    }

    /**
     * @Route(path="/getRutasAlternas.json", name="ajaxGetRutasAlternas")
     * @Secure(roles="ROLE_USER")  
     */
    public function getRutasAlternas(Request $request)
    {

        $optionRutas = array();
        $error = "";

        try {
            $idEstacionOrigen = $request->query->get('idEstacionOrigen');
            if (is_null($idEstacionOrigen)) {
                $idEstacionOrigen = $request->request->get('idEstacionOrigen');
            }

            $idEstacionDestino = $request->query->get('idEstacionDestino');
            if (is_null($idEstacionDestino)) {
                $idEstacionDestino = $request->request->get('idEstacionDestino');
            }

            $idEncomienda = $request->query->get('idEncomienda');
            if (is_null($idEncomienda)) {
                $idEncomienda = $request->request->get('idEncomienda');
            }

            if (
                $idEstacionOrigen !== null && is_numeric($idEstacionOrigen) && $idEstacionDestino !== null && is_numeric($idEstacionDestino) &&
                $idEncomienda !== null && is_numeric($idEncomienda)
            ) {

                $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
                if ($encomienda !== null) {
                    $fechaDia = new \DateTime();
                    $empresaEncomienda = $encomienda->getEmpresa();
                    $rutas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Ruta')->listarRutasPorEstaciones($idEstacionOrigen, $idEstacionDestino, true);
                    foreach ($rutas as $ruta) {
                        $empresaStr = "";
                        $empresaRuta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                            ->getEmpresaQueFactura($ruta, $fechaDia);
                        if ($empresaRuta !== null) {
                            if (
                                $empresaEncomienda === $empresaRuta ||
                                (($encomienda->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR ||
                                    $encomienda->getTipoDocumento()->getId() === TipoDocumentoEncomienda::AUTORIZACION_CORTESIA ||
                                    $encomienda->getTipoDocumento()->getId() === TipoDocumentoEncomienda::AUTORIZACION_INTERNA
                                ) && UtilService::compararFechas($fechaDia, $encomienda->getFechaCreacion()) > 0
                                )
                            ) {
                                $empresaStr = " ( " . $empresaRuta->getAlias() . " ) ";
                            } else {
                                continue;
                            }
                        } else {
                            continue;
                        }
                        $text = strval($ruta->getCodigo()) . " - " . $ruta->getNombre() . $empresaStr;
                        $optionRutas[] = array(
                            "id" => $ruta->getCodigo(),
                            "text" =>  $text
                        );
                    }
                }
            }
        } catch (\RuntimeException $exc) {
            $error = $exc->getMessage();
            if (UtilService::startsWith($error, 'm1')) {
                $error = str_replace("m1", "", $error);
            } else {
                $error = "Ha ocurrido un error en el sistema";
            }
        } catch (\Exception $exc) {
            $error = "Ha ocurrido un error en el sistema.";
        }
        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'optionRutas' => $optionRutas,
        ));
        return $response;
    }

    /**
     * @Route(path="/getEstacionesDestinosPorRuta.json", name="ajaxGetEstacionesDestinosPorRuta")
     * @Secure(roles="ROLE_USER")  
     */
    public function getEstacionesDestinosPorRuta(Request $request)
    {

        $optionEstacionesDestino = array();
        $error = "";

        try {
            $codigoRuta = $request->query->get('codigoRuta');
            if (is_null($codigoRuta)) {
                $codigoRuta = $request->request->get('codigoRuta');
            }

            $ruta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Ruta')->find($codigoRuta);
            if (!$ruta) {
                throw new \RuntimeException('m1La ruta con codigo:' . $codigoRuta . " no existe.");
            }

            $estacionDestino = $ruta->getEstacionDestino();
            $listaEstacionesIntermedia = $ruta->getListaEstacionesIntermediaOrdenadas();

            $optionEstacionesDestino[] = array(
                "id" => $estacionDestino->getId(),
                "text" => $estacionDestino->__toString()
            );

            foreach ($listaEstacionesIntermedia as $item) {
                $estacion = $item->getEstacion();
                $optionEstacionesDestino[] = array(
                    "id" => $estacion->getId(),
                    "text" => $estacion->__toString()
                );
            }
        } catch (\RuntimeException $exc) {
            $error = $exc->getMessage();
            if (UtilService::startsWith($error, 'm1')) {
                $error = str_replace("m1", "", $error);
            } else {
                $error = "Ha ocurrido un error en el sistema";
            }
        } catch (\Exception $exc) {
            $error = "Ha ocurrido un error en el sistema.";
        }
        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'optionEstacionesDestino' => $optionEstacionesDestino
        ));
        return $response;
    }

    /**
     * 
     *  LISTA DE SALIDAS DE DONDE SE PUEDEN EMITIR BOLETOS
     * 
     * @Route(path="/listarSalidasActivasEmitirBoleto.json", name="ajaxListarSalidasActivasEmitirBoletoPaginado")
     * @Secure(roles="ROLE_USER") 
     */
    public function listarSalidasActivasEmitirBoletoAction($_route)
    {
        $pageRequest = 1;
        $total = 0;
        $rows = array();
        try {
            $pageRequest = $this->get('request')->request->get('page');
            $rowsRequest = $this->get('request')->request->get('rp');
            if ($pageRequest !== null && is_numeric($pageRequest) && $rowsRequest !== null && is_numeric($rowsRequest)) {
                $query = $this->get('request')->request->get('query');
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Salida');
                $mapFilters = UtilService::getMapsParametrosQuery($query);
                $fechaSalidaFilter = UtilService::getValueToMap($mapFilters, "emitir_boleto_command[fechaSalida]", new \DateTime());
                $origenFilter = UtilService::getValueToMap($mapFilters, "emitir_boleto_command[estacionOrigen]");
                $result = $repository->getSalidasActivasPaginadas($pageRequest, $rowsRequest, $fechaSalidaFilter, $origenFilter, $this->getUser());
                foreach ($result['items'] as $salida) {
                    $piloto = "N/D";
                    if ($salida->getPiloto() !== null) {
                        $piloto = $salida->getPiloto()->getFullName();
                        $telefono = $salida->getPiloto()->getTelefono();
                        if ($telefono !== null && trim($telefono) !== "") {
                            $piloto .= ". TELEF: " . trim($telefono);
                        }
                    }
                    $itinerario = $salida->getItinerario();
                    $ruta = $itinerario->getRuta();
                    $item = array(
                        'id' => $salida->getId(),
                        'destino' => $ruta->getEstacionDestino()->__toString(),
                        'origen' => $ruta->getEstacionOrigen()->__toString(),
                        'fecha' => $salida->getFecha()->format('d-m-Y h:i A'),
                        //                        'tipoBus' => $salida->getItinerario()->getTipoBus()->getAlias(),
                        //                        'claseBus' => $salida->getItinerario()->getTipoBus()->getClase()->getNombre(),
                        'empresa' => $salida->getEmpresa()->getAlias(),
                        'itinerario' => $salida->getItinerario()->getTipoBus()->getAlias() . " - " . $salida->getItinerario()->getTipoBus()->getClase()->getNombre(),
                        'bus' => $salida->getBus() === null ? "N/D" : $salida->getBus()->getCodigo() . " - " . $salida->getBus()->getTipo()->getAlias() . " - " . $salida->getBus()->getTipo()->getClase()->getNombre(),
                        'piloto' => $piloto,
                        'estado' => $salida->getEstado()->getNombre()
                    );
                    $rows[] = $item;
                }
                $total = $result['total'];
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            var_dump($exc->getMessage());
            //            $rows[] = array("id" => "Ha ocurrido un error.");
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
     * 
     *  LISTA DE SALIDAS DE DONDE SE PUEDEN EMITIR BOLETOS
     * 
     * @Route(path="/listarSalidasActivasReasignarBoleto.json", name="ajaxListarSalidasActivasReasignarBoletoPaginado")
     * @Secure(roles="ROLE_USER") 
     */
    public function listarSalidasActivasReasignarBoletoAction($_route)
    {
        $pageRequest = 1;
        $total = 0;
        $rows = array();
        try {
            $pageRequest = $this->get('request')->request->get('page');
            $rowsRequest = $this->get('request')->request->get('rp');
            if ($pageRequest !== null && is_numeric($pageRequest) && $rowsRequest !== null && is_numeric($rowsRequest)) {
                $query = $this->get('request')->request->get('query');
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Salida');
                $mapFilters = UtilService::getMapsParametrosQuery($query);
                $fechaSalidaFilter = UtilService::getValueToMap($mapFilters, "reasignar_boleto_command[fechaSalida]", new \DateTime());
                $origenFilter = UtilService::getValueToMap($mapFilters, "reasignar_boleto_command[estacionOrigen]");
                $result = $repository->getSalidasActivasPaginadas($pageRequest, $rowsRequest, $fechaSalidaFilter, $origenFilter, $this->getUser());
                foreach ($result['items'] as $salida) {
                    $piloto = "N/D";
                    if ($salida->getPiloto() !== null) {
                        $piloto = $salida->getPiloto()->getFullName();
                        $telefono = $salida->getPiloto()->getTelefono();
                        if ($telefono !== null && trim($telefono) !== "") {
                            $piloto .= ". TELEF: " . trim($telefono);
                        }
                    }
                    $itinerario = $salida->getItinerario();
                    $ruta = $itinerario->getRuta();
                    $item = array(
                        'id' => $salida->getId(),
                        'destino' => $ruta->getEstacionDestino()->__toString(),
                        'origen' => $ruta->getEstacionOrigen()->__toString(),
                        'fecha' => $salida->getFecha()->format('d-m-Y h:i A'),
                        //                        'tipoBus' => $salida->getItinerario()->getTipoBus()->getAlias(),
                        //                        'claseBus' => $salida->getItinerario()->getTipoBus()->getClase()->getNombre(),
                        'empresa' => $salida->getEmpresa()->getAlias(),
                        'itinerario' => $salida->getItinerario()->getTipoBus()->getAlias() . " - " . $salida->getItinerario()->getTipoBus()->getClase()->getNombre(),
                        'bus' => $salida->getBus() === null ? "N/D" : $salida->getBus()->getCodigo() . " - " . $salida->getBus()->getTipo()->getAlias() . " - " . $salida->getBus()->getTipo()->getClase()->getNombre(),
                        'piloto' => $piloto,
                        'estado' => $salida->getEstado()->getNombre()
                    );
                    $rows[] = $item;
                }
                $total = $result['total'];
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            var_dump($exc->getMessage());
            //            $rows[] = array("id" => "Ha ocurrido un error.");
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
     * 
     *  LISTA DE SALIDAS DE DONDE SE PUEDEN EMITIR BOLETOS
     * 
     * @Route(path="/listarSalidasActivasReservacion.json", name="ajaxListarSalidasActivasReservacionPaginado")
     * @Secure(roles="ROLE_USER") 
     */
    public function listarSalidasActivasReservacionAction($_route)
    {
        $pageRequest = 1;
        $total = 0;
        $rows = array();
        try {
            $pageRequest = $this->get('request')->request->get('page');
            $rowsRequest = $this->get('request')->request->get('rp');
            if ($pageRequest !== null && is_numeric($pageRequest) && $rowsRequest !== null && is_numeric($rowsRequest)) {
                $query = $this->get('request')->request->get('query');
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Salida');
                $mapFilters = UtilService::getMapsParametrosQuery($query);
                $fechaSalidaFilter = UtilService::getValueToMap($mapFilters, "crear_reservacion_command[fechaSalida]", new \DateTime());
                $origenFilter = UtilService::getValueToMap($mapFilters, "crear_reservacion_command[estacionOrigen]");
                $result = $repository->getSalidasActivasPaginadas($pageRequest, $rowsRequest, $fechaSalidaFilter, $origenFilter, $this->getUser());
                foreach ($result['items'] as $salida) {
                    $piloto = "N/D";
                    if ($salida->getPiloto() !== null) {
                        $piloto = $salida->getPiloto()->getFullName();
                        $telefono = $salida->getPiloto()->getTelefono();
                        if ($telefono !== null && trim($telefono) !== "") {
                            $piloto .= ". TELEF: " . trim($telefono);
                        }
                    }
                    $itinerario = $salida->getItinerario();
                    $ruta = $itinerario->getRuta();
                    $item = array(
                        'id' => $salida->getId(),
                        'destino' => $ruta->getEstacionDestino()->__toString(),
                        'origen' => $ruta->getEstacionOrigen()->__toString(),
                        'fecha' => $salida->getFecha()->format('d-m-Y h:i A'),
                        'empresa' => $salida->getEmpresa()->getAlias(),
                        //                        'tipoBus' => $salida->getTipoBus()->getAlias(),
                        //                        'claseBus' => $salida->getTipoBus()->getClase()->getNombre(),
                        'itinerario' => $salida->getItinerario()->getTipoBus()->getAlias() . " - " . $salida->getItinerario()->getTipoBus()->getClase()->getNombre(),
                        'bus' => $salida->getBus() === null ? "N/D" : $salida->getBus()->getCodigo() . " - " . $salida->getBus()->getTipo()->getAlias() . " - " . $salida->getBus()->getTipo()->getClase()->getNombre(),
                        'piloto' => $piloto,
                        'estado' => $salida->getEstado()->getNombre()
                    );
                    $rows[] = $item;
                }
                $total = $result['total'];
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            var_dump($exc);
            //            $rows[] = array("id" => "Ha ocurrido un error.");
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
     * @Route(path="/calcularImporteTotalMonedaBase.json", name="ajaxCalcularImporteTotalMonedaBase")
     * @Secure(roles="ROLE_USER")
     */
    public function calcularImporteTotalMonedaBase(Request $request)
    {

        $totalSec = 0;
        $total = 0;
        $error = "";
        $boletos = array();

        try {
            $idEstacionOrigen = $request->query->get('idEstacionOrigen');
            if (is_null($idEstacionOrigen)) {
                $idEstacionOrigen = $request->request->get('idEstacionOrigen');
            }
            $idEstacionDestino = $request->query->get('idEstacionDestino');
            if (is_null($idEstacionDestino)) {
                $idEstacionDestino = $request->request->get('idEstacionDestino');
            }
            $idTipoPago = $request->query->get('idTipoPago');
            if (is_null($idTipoPago)) {
                $idTipoPago = $request->request->get('idTipoPago');
            }
            $idSalida = $request->query->get('idSalida');
            if (is_null($idSalida)) {
                $idSalida = $request->request->get('idSalida');
            }
            $listaClienteBoleto = $request->query->get('listaClienteBoleto');
            if (is_null($listaClienteBoleto)) {
                $listaClienteBoleto = $request->request->get('listaClienteBoleto');
            }
            $pagadoDesdeOrigenRuta = $request->query->get('pagadoDesdeOrigenRuta');
            if (is_null($pagadoDesdeOrigenRuta)) {
                $pagadoDesdeOrigenRuta = $request->request->get('pagadoDesdeOrigenRuta');
                if (is_null($pagadoDesdeOrigenRuta)) {
                    $pagadoDesdeOrigenRuta = false;
                }
            }
            $monedaSec = $request->query->get('monedaSec');
            if (is_null($monedaSec)) {
                $monedaSec = $request->request->get('monedaSec');
            }

            $facturar = $request->query->get('facturar');
            if (is_null($facturar)) {
                $facturar = $request->request->get('facturar');
                if (is_null($facturar)) {
                    $facturar = 'true';
                }
            }

            $showVoucher = $request->query->get('showVoucher');
            if (is_null($showVoucher)) {
                $showVoucher = $request->request->get('showVoucher');
                if (is_null($showVoucher)) {
                    $showVoucher = false;
                }
            }

            $detalle = $request->query->get('detalle');
            if (is_null($detalle)) {
                $detalle = $request->request->get('detalle');
                if (is_null($detalle)) {
                    $detalle = 'false';
                }
            }

            $estacionDetalle = $request->query->get('estacionDetalle');
            if (is_null($estacionDetalle)) {
                $estacionDetalle = $request->request->get('estacionDetalle');
            }

            if (
                $idEstacionOrigen !== null && trim($idEstacionOrigen) !== "" &&
                $idEstacionDestino !== null && trim($idEstacionDestino) !== "" &&
                $idTipoPago !== null && trim($idTipoPago) !== "" &&
                $idSalida !== null && trim($idSalida) !== "" &&
                $listaClienteBoleto !== null && trim($listaClienteBoleto) !== ""
            ) {

                $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida);
                if ($salida === null) {
                    throw new \RuntimeException("m1La salida con identificador " . $idSalida . " no existe.");
                }

                //               $idClaseBus = $salida->getTipoBus()->getClase()->getId();
                $idClaseBusItinerario = $salida->getItinerario()->getTipoBus()->getClase()->getId();
                $idClaseBusSalida = $salida->getTipoBus()->getClase()->getId();
                $listaClienteBoletoJson = json_decode($listaClienteBoleto);

                $serieFactura = "";
                $numeroFactura = 0;
                if ($detalle === 'true' && $facturar === 'true') {
                    $idSerieFacturacionEspecial = $request->query->get('serieFacturacionEspecial');
                    if (is_null($idSerieFacturacionEspecial)) {
                        $idSerieFacturacionEspecial = $request->request->get('serieFacturacionEspecial');
                    }
                    if ($idSerieFacturacionEspecial === null || trim($idSerieFacturacionEspecial) === "") {
                        throw new \RuntimeException("m1Debe especificar la serie de facturación especial.");
                    }
                    $factura = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')->find($idSerieFacturacionEspecial);
                    if ($factura === null) {
                        throw new \RuntimeException("m1No se ha encontrado en el sistema una serie de factura con id: " . $idSerieFacturacionEspecial . ".");
                    }
                    $serieFactura = $factura->getSerieResolucionFactura();
                    $numeroFactura = $factura->getValorResolucionFactura();
                }
                $tipoCambioSec = null;
                foreach ($listaClienteBoletoJson as $json) {
                    //                  $idCliente = $json->idCliente;
                    //                  $numeroAsiento = $json->numero;
                    $asiento = null;
                    $idClaseAsiento = ClaseAsiento::A;
                    $idAsiento = $json->id;
                    if (is_numeric($idAsiento)) {
                        $asiento = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->find($idAsiento);
                        $idClaseAsiento = $asiento->getClase()->getId();
                    }

                    if ($pagadoDesdeOrigenRuta === 'true') { //No quitar las comillas
                        $idEstacionOrigen = $salida->getItinerario()->getRuta()->getEstacionOrigen()->getId();
                    }
                    $importeTarifa = 0;
                    $tarifaBoleto = $this->get("acme_backend_tarifa")->getTarifaBoleto($idEstacionOrigen, $idEstacionDestino, $idTipoPago, $idClaseBusItinerario, $idClaseAsiento, $salida->getFecha());
                    if ($tarifaBoleto === null) {

                        if ($idClaseBusItinerario != $idClaseBusSalida) {
                            $tarifaBoleto = $this->get("acme_backend_tarifa")->getTarifaBoleto($idEstacionOrigen, $idEstacionDestino, $idTipoPago, $idClaseBusSalida, $idClaseAsiento, $salida->getFecha());
                        }

                        if ($tarifaBoleto === null) {
                            $estacionOrigen = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($idEstacionOrigen);
                            $estacionDestino = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($idEstacionDestino);
                            $claseBus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ClaseBus')->find($idClaseBusItinerario);
                            $claseAsiento = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ClaseAsiento')->find($idClaseAsiento);
                            $total = "";
                            $totalSec = "";
                            $error = "No se ha definido en el sistema una tarifa para el origen: " . $estacionOrigen
                                . ", el destino: " . $estacionDestino
                                . ", la clase de bus: " . $claseBus
                                . " y la clase de asiento: " . $claseAsiento
                                . ".";
                            break;
                        } else {
                            $importeTarifa = $tarifaBoleto->calcularTarifa();
                        }
                    } else {
                        $importeTarifa = $tarifaBoleto->calcularTarifa();
                    }
                    if ($detalle === 'true') {
                        $detalleFactura = "Voucher";
                        if ($serieFactura !== "") {
                            $detalleFactura = $serieFactura . " " . strval($numeroFactura);
                            $numeroFactura++;
                        }
                        $numero = "N/D";
                        if ($asiento !== null) {
                            $numero = $asiento->getNumero();
                        }
                        $boletos[] = array(
                            'numero' => $numero,
                            'factura' => $detalleFactura,
                            'importe' => $importeTarifa
                        );
                    }
                    $total = $total + $importeTarifa;

                    if (!is_null($monedaSec)) {
                        if ($tipoCambioSec === null) {
                            $tipoCambioSec = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($monedaSec);
                            if ($tipoCambioSec === null) {
                                $total = "";
                                $totalSec = "";
                                $error = "No se ha definido un tipo de cambio en el sistema para la moneda " . $monedaSec . ".";
                                break;
                            }
                        }
                        if ($tipoCambioSec !== null) {
                            $importeTarifaSec =  $importeTarifa / $tipoCambioSec->getTasa();
                            $importeTarifaSec = round($importeTarifaSec, 0, PHP_ROUND_HALF_UP);
                            $totalSec = $totalSec + $importeTarifaSec;
                        }
                    }
                }

                $idBoletoOriginal = $request->query->get('idBoletoOriginal');
                if (is_null($idBoletoOriginal)) {
                    $idBoletoOriginal = $request->request->get('idBoletoOriginal');
                }
                if ($idBoletoOriginal !== null && trim($idBoletoOriginal) !== "" && trim($idBoletoOriginal) !== "0") {
                    $boleto = $repository = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($idBoletoOriginal);
                    if (!$boleto) {
                        $total = "";
                        $totalSec = "";
                        $error = 'El boleto con id: ' . $idBoletoOriginal . " no existe.";
                    } else {
                        /*
                        *   Total: es el nuevo precio calculado
                        *   PrecioCalculadoMonedaBase: Es el precio que se cobro por el boleto anterior
                        *   Si el nuevo precio es mayor que el viejo, hay que facturar la diferencia.
                        *   Si es igual o menor, no se factura.
                        */
                        $precioCalculadoMonedaBase = UtilService::calcularPrecioTotalReasignadoMonedaBase($boleto);
                        $valor = $total - $precioCalculadoMonedaBase;
                        if ($valor >= 0) {
                            $total = $valor;
                            if (!is_null($monedaSec)) {
                                if ($tipoCambioSec !== null) {
                                    $valorSec =  $valor / $tipoCambioSec->getTasa();
                                    $valorSec = round($valorSec, 0, PHP_ROUND_HALF_UP);
                                    $totalSec = $valorSec;
                                }
                            }
                        } else {
                            $total = 0;
                            $totalSec = 0;
                        }

                        //Reasignando factura otra estacion
                        if ($detalle === 'true') {
                            $boletos[0]['importe'] = $total; //Es un solo boleto
                        }
                    }
                }
            } else {
                $total = "";
                $error = "Ha ocurrido un error en el sistema. Error 01";
            }
        } catch (\RuntimeException $exc) {
            //            var_dump($exc);
            $total = "";
            $error = $exc->getMessage();
            if (UtilService::startsWith($error, 'm1')) {
                $error = str_replace("m1", "", $error);
            } else {
                $error = "Ha ocurrido un error en el sistema. Error 02";
            }
        } catch (\Exception $exc) {
            var_dump($exc);
            $total = "";
            $error = "Ha ocurrido un error en el sistema. Error 03";
        }

        $options = array(
            'total' => $total,
            'totalSec' => $totalSec,
            'error' => $error,
            'boletos' => $boletos
        );

        if ($showVoucher === true || $showVoucher === 'true') {
            $user = $this->getUser();
            if ($user->getEstacion() !== null) {
                $options['showVoucher'] = $user->getEstacion()->getPermitirVoucherBoleto();
            } else {
                $options['showVoucher'] = false;
            }
        }

        $response = new JsonResponse();
        $response->setData($options);
        return $response;
    }

    /**
     * @Route(path="/calcularImporteTotalPorMoneda.json", name="ajaxCalcularImporteTotalPorMoneda")
     * @Secure(roles="ROLE_USER") 
     */
    public function calcularImporteTotalPorMoneda(Request $request)
    {

        $tasa = 0;
        $total = 0;
        $sigla = "???";
        $error = "";

        try {
            $totalNeto = $request->query->get('totalNeto');
            if (is_null($totalNeto)) {
                $totalNeto = $request->request->get('totalNeto');
            }
            $idMonedaDestino = $request->query->get('idMoneda');
            if (is_null($idMonedaDestino)) {
                $idMonedaDestino = $request->request->get('idMoneda');
            }
            $dir = $request->query->get('dir'); //Dirección del tipo de cambio
            if (is_null($dir)) {
                $dir = $request->request->get('dir');
            }

            if ($totalNeto !== null && trim($totalNeto) !== "" && $idMonedaDestino !== null && trim($idMonedaDestino) !== "") {
                $tipoCambio = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:TipoCambio')->getTipoCambio($idMonedaDestino);
                if ($tipoCambio === null) {
                    $tasa = "";
                    $total = "";
                    $error = "No se ha definido un tipo de cambio en el sistema para esa moneda.";
                } else {
                    $sigla = $tipoCambio->getMoneda()->getSigla();
                    $tasa = $tipoCambio->getTasa();
                    if ($dir !== null && trim($dir) === "false") {
                        $total =  $totalNeto * $tasa;
                        $total = floor($total);
                    } else {
                        $total =  $totalNeto / $tasa;
                        $total = round($total, 0, PHP_ROUND_HALF_UP);
                    }
                }
            } else {
                $tasa = "";
                $total = "";
                $error = "Ha ocurrido un error en el sistema.";
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            echo $exc->getTraceAsString();
            $tasa = "";
            $total = "";
            $error = "Ha ocurrido un error en el sistema.";
        }

        $response = new JsonResponse();
        $response->setData(array(
            'sigla' => $sigla,
            'tasa' => $tasa,
            'total' => $total,
            'error' => $error
        ));
        return $response;
    }


    /**
     * @Route(path="/calcularImporteTotalMonedaBaseEncomienda.json", name="ajaxCalcularImporteTotalMonedaBaseEncomienda")
     * @Secure(roles="ROLE_USER")
     */
    public function calcularImporteTotalMonedaBaseEncomienda(Request $request)
    {

        $total = 0;
        $descuento = 0;
        $error = "";

        try {
            $idEstacionOrigen = $request->query->get('idEstacionOrigen');
            if (is_null($idEstacionOrigen)) {
                $idEstacionOrigen = $request->request->get('idEstacionOrigen');
            }
            $idEstacionDestino = $request->query->get('idEstacionDestino');
            if (is_null($idEstacionDestino)) {
                $idEstacionDestino = $request->request->get('idEstacionDestino');
            }
            $listaEncomiendas = $request->query->get('listaEncomiendas');
            if (is_null($listaEncomiendas)) {
                $listaEncomiendas = $request->request->get('listaEncomiendas');
            }
            $idClienteRemitente = $request->query->get('idClienteRemitente');
            if (is_null($idClienteRemitente)) {
                $idClienteRemitente = $request->request->get('idClienteRemitente');
            }

            if (
                $idEstacionOrigen !== null && trim($idEstacionOrigen) !== "" &&
                $idEstacionDestino !== null && trim($idEstacionDestino) !== "" &&
                $idClienteRemitente !== null && trim($idClienteRemitente) !== "" &&
                $listaEncomiendas !== null && trim($listaEncomiendas) !== ""
            ) {

                $listaEncomiendasJson = json_decode($listaEncomiendas);
                $cantidadItems = 0;
                foreach ($listaEncomiendasJson as $json) {
                    if (strval($json->tipoEncomienda) === TipoEncomienda::EFECTIVO) {
                        $cantidadItems += 1;
                    } else {
                        $cantidadItems += intval($json->cantidad);
                    }
                }

                foreach ($listaEncomiendasJson as $json) {

                    $precio = 0;
                    if ($json->tipoEncomienda === TipoEncomienda::EFECTIVO) //Efectivo
                    {
                        $tarifa = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEfectivo($json->cantidad);
                        if ($tarifa === null) {
                            $total = "";
                            $error = "No se ha definido una tarifa en el sistema para esa cantidad de efectivo.";
                            break;
                        }
                        $precio = $tarifa->calcularTarifa();

                        $tarifa_efectivo_por_distancia = $this->container->getParameter("tarifa_efectivo_por_distancia");
                        if (isset($tarifa_efectivo_por_distancia) && $tarifa_efectivo_por_distancia === true) {
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia($idEstacionOrigen, $idEstacionDestino);
                            if ($tarifaDistancia === null) {
                                $total = "";
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación de origen a la de destino";
                                break;
                            }
                            $precio += $tarifaDistancia->calcularTarifa();
                        }
                    } else if ($json->tipoEncomienda === TipoEncomienda::ESPECIAL) //Especial
                    {
                        $tarifa = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEspeciales($json->tipoEncomiendaEspecial);
                        if ($tarifa === null) {
                            $total = 0;
                            $tipoEncomiendaEspecial = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoEncomiendaEspeciales')->find($json->tipoEncomiendaEspecial);
                            $error = "No se ha definido una tarifa en el sistema para la encomienda: " . $tipoEncomiendaEspecial->getNombre() . ".";
                            break;
                        }
                        //                        var_dump($tarifa->calcularTarifa());

                        $precio = $tarifa->calcularTarifa();

                        $tarifa_especial_por_distancia = $this->container->getParameter("tarifa_especial_por_distancia");
                        if (isset($tarifa_especial_por_distancia) && $tarifa_especial_por_distancia === true) {
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia($idEstacionOrigen, $idEstacionDestino);
                            if ($tarifaDistancia === null) {
                                $total = "";
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación de origen a la de destino";
                                break;
                            }
                            $precio += $tarifaDistancia->calcularTarifa();
                        }
                        $precio = intval($json->cantidad) * $precio;
                    } else if ($json->tipoEncomienda === TipoEncomienda::PAQUETE) //Paquete
                    {
                        $tarifaVolumen = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesVolumen($json->volumen);
                        if ($tarifaVolumen === null) {
                            $total = 0;
                            $error = "No se ha definido una tarifa en el sistema para el volumen " . $json->volumen . ".";
                            break;
                        }

                        $tarifaPeso = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesPeso($json->peso);
                        if ($tarifaPeso === null) {
                            $total = 0;
                            $error = "No se ha definido una tarifa en el sistema para el peso " . $json->peso . ".";
                            break;
                        }

                        $precio = $tarifaVolumen->calcularTarifa() + $tarifaPeso->calcularTarifa();

                        $tarifa_paquete_por_distancia = $this->container->getParameter("tarifa_paquete_por_distancia");
                        if (isset($tarifa_paquete_por_distancia) && $tarifa_paquete_por_distancia === true) {
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia($idEstacionOrigen, $idEstacionDestino);
                            if ($tarifaDistancia === null) {
                                $total = "";
                                $error = "No se ha definido una tarifa en el sistema para el envío de encomienda de la estación de origen a la de destino";
                                break;
                            }
                            $precio += $tarifaDistancia->calcularTarifa();
                        }
                        $precio = intval($json->cantidad) * $precio;
                    }

                    if ($json->valorDeclarado !== null && $json->valorDeclarado !== "") {
                        if (is_numeric($json->valorDeclarado)) {
                            $encomienda_porciento_valor_declarado = $this->container->getParameter("encomienda_porciento_valor_declarado");
                            if (isset($encomienda_porciento_valor_declarado) && is_numeric($encomienda_porciento_valor_declarado)) {
                                $seguro = intval($json->valorDeclarado) * floatval($encomienda_porciento_valor_declarado);
                                $precio = $precio + $seguro;
                            }
                        } else {
                            $total = "";
                            $error = "El valor declarado no es númerico.";
                            break;
                        }
                    }

                    $descuento = $descuento + UtilService::calcularDescuento($precio, $cantidadItems);
                    $total = $total + UtilService::aplicarDescuento($precio, $cantidadItems);
                }
            } else {
                $total = "";
                $error = "Ha ocurrido un error en el sistema.";
            }
        } catch (\RuntimeException $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            $total = "";
            $error = $exc->getCode() === RuntimeExceptionCode::VALIDACION ? $exc->getMessage() : "Ha ocurrido un error en el sistema";
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            echo $exc->getTraceAsString();
            $total = "";
            $error = $exc->getCode() === RuntimeExceptionCode::VALIDACION ? $exc->getMessage() : "Ha ocurrido un error en el sistema";
        }

        $response = new JsonResponse();
        $response->setData(array(
            'descuento' => $descuento,
            'total' => $total,
            'error' => $error
        ));
        return $response;
    }

    /**
     * @Route(path="/calcularImporteTotalMonedaBaseEntregarEncomienda.json", name="ajaxCalcularImporteTotalMonedaBaseEntregarEncomienda")
     * @Secure(roles="ROLE_USER")
     */
    public function calcularImporteTotalMonedaBaseEntregarEncomienda(Request $request)
    {

        $total = 0;
        $error = "";

        try {
            $idEncomiendaOriginal = $request->query->get('idEncomiendaOriginal');
            if (is_null($idEncomiendaOriginal)) {
                $idEncomiendaOriginal = $request->request->get('idEncomiendaOriginal');
            }
            $idTipoPago = $request->query->get('idTipoPago');
            if (is_null($idTipoPago)) {
                $idTipoPago = $request->request->get('idTipoPago');
            }

            if ($idEncomiendaOriginal !== null && trim($idEncomiendaOriginal) !== "" && $idTipoPago !== null && trim($idTipoPago) !== "") {

                $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomiendaOriginal);
                if ($encomienda === null) {
                    throw new \RuntimeException("m1No se encontro en el sistema la encomienda con identificador: " . $idEncomiendaOriginal);
                }
                $total = $encomienda->getPrecioCalculadoMonedaBase();
            } else {
                $total = "";
                $error = "Ha ocurrido un error en el sistema.";
            }
        } catch (\Exception $exc) {
            $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
            //            echo $exc->getTraceAsString();
            $total = "";
            $error = $exc->getCode() === RuntimeExceptionCode::VALIDACION ? $exc->getMessage() : "Ha ocurrido un error en el sistema";
        }

        $response = new JsonResponse();
        $response->setData(array(
            'total' => $total,
            'error' => $error
        ));
        return $response;
    }

    /**
     * @Route(path="/generatePin.html", name="ajaxGeneratePin")
     * @Secure(roles="ROLE_USER")
     */
    public function generatePinAction()
    {
        return new Response($this->get('acme_backend_util')->generatePin());
    }

    /**
     * @Route(path="/consultarAsientoSalida.html", name="consultarAsientoSalida-case1")
     * @Secure(roles="ROLE_USER")
     */
    public function consultarAsientoSalidaAction(Request $request, $_route)
    {

        $idSalida = $request->query->get('idSalida');
        if (is_null($idSalida)) {
            $idSalida = $request->request->get('idSalida');
        }
        $numeroAsiento = $request->query->get('numeroAsiento');
        if (is_null($numeroAsiento)) {
            $numeroAsiento = $request->request->get('numeroAsiento');
        }

        $items = array();
        $asientoBus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')
            ->getAsietoBusPorSalidaNroAsiento($idSalida, $numeroAsiento);
        if ($asientoBus !== null) {

            $agencia = false;
            $idAgencia = null;
            $estacionUsurio = $this->getUser()->getEstacion();
            if ($estacionUsurio !== null && $estacionUsurio->getTipo()->getId() === \Acme\TerminalOmnibusBundle\Entity\TipoEstacion::AGENCIA) {
                $agencia = true;
                $idAgencia = $estacionUsurio->getId();
            }

            $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')
                ->getBoletosActivosPorSalidaNroAsiento($idSalida, $numeroAsiento);
            foreach ($boletos as $boleto) {
                $clearData = false;
                if ($agencia === true && $boleto->getEstacionCreacion()->getId() !== $idAgencia) {
                    $clearData = true;
                }
                $imprimirVoucher = false;
                if ($boleto->getEstado()->getId() === EstadoBoleto::EMITIDO) {
                    $imprimirVoucher = true;
                }
                $items[] = array(
                    "fechaCreacion" => $clearData ? '-' : $boleto->getFechaCreacion()->format('d-m-Y H:i:s'),
                    "tipo" => $clearData ? '-' : "Boleto. " . $boleto->getDocumentoStr(),
                    "id" => $clearData ? '-' : $boleto->getId(),
                    "cliente" => $clearData ? '-' : $boleto->getClienteDocumento()->getInfo2() . ", " . $boleto->getClienteBoleto()->getInfo2(),
                    "origen" => $clearData ? '-' : $boleto->getEstacionOrigen()->__toString(),
                    "destino" => $clearData ? '-' : $boleto->getEstacionDestino()->__toString(),
                    "estacionVenta" => $clearData ? '-' : $boleto->getEstacionCreacion() === null ? "-" : $boleto->getEstacionCreacion()->__toString(),
                    "revendidoEnEstacion" => $clearData ? '-' : $boleto->getRevendidoEnEstacion(),
                    "revendidoEnCamino" => $clearData ? '-' : $boleto->getRevendidoEnCamino(),
                    "estado" => $clearData ? '-' : $boleto->getEstado()->getNombre(),
                    "imprimirVoucher" => $imprimirVoucher
                );
            }

            if (count($items) === 0) {
                $reservaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')
                    ->getReservacionesActivasPorSalidaNroAsiento($idSalida, $numeroAsiento);
                foreach ($reservaciones as $reservacion) {
                    $clearData = false;
                    if ($agencia === true && $reservacion->getEstacionCreacion()->getId() !== $idAgencia) {
                        $clearData = true;
                    }
                    $items[] = array(
                        "fechaCreacion" => $clearData ? '-' : $reservacion->getFechaCreacion()->format('d-m-Y H:i:s'),
                        "tipo" => $clearData ? '-' : "Reservación",
                        "id" => $clearData ? '-' : $reservacion->getId(),
                        "cliente" => $clearData ? '-' : $reservacion->getCliente()->getInfo2(),
                        "origen" => " - ",
                        "destino" => " - ",
                        "estacionVenta" => $clearData ? '-' : $reservacion->getEstacionCreacion() === null ? "-" : $reservacion->getEstacionCreacion()->__toString(),
                        "revendidoEnEstacion" => " - ",
                        "revendidoEnCamino" => " - ",
                        "estado" => $clearData ? '-' : $reservacion->getEstado()->getNombre(),
                        "imprimirVoucher" => false
                    );
                }
            }

            usort($items, function ($item1, $item2) {
                $v1 = strtotime($item1['fechaCreacion']);
                $v2 = strtotime($item2['fechaCreacion']);
                return $v1 - $v2; // $v2 - $v1 to reverse direction
            });
        }

        return $this->render('AcmeTerminalOmnibusBundle:AsientoBus:consultar.html.twig', array(
            'asientoBus' => $asientoBus,
            'items' => $items
        ));
    }

    /**
     * @Route(path="/consultarAsientoSalida.json", name="consultarAsientoSalida-case2")
     * @Secure(roles="ROLE_USER")
     */
    public function consultarAsientoSalida2Action(Request $request, $_route)
    {

        $idSalida = $request->query->get('idSalida');
        if (is_null($idSalida)) {
            $idSalida = $request->request->get('idSalida');
        }
        $numeroAsiento = $request->query->get('numeroAsiento');
        if (is_null($numeroAsiento)) {
            $numeroAsiento = $request->request->get('numeroAsiento');
        }

        $items = array();
        $asientoBus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsietoBusPorSalidaNroAsiento($idSalida, $numeroAsiento);
        if ($asientoBus !== null) {
            $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')
                ->getBoletosActivosPorSalidaNroAsiento($idSalida, $numeroAsiento);
            foreach ($boletos as $boleto) {
                $autorizadoPor = "";
                $motivoCortesia = "";
                $esCortesia = $boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA;
                $autorizacion = $boleto->getAutorizacionCortesia();
                if ($esCortesia && $autorizacion !== null) {
                    $autorizadoPor = $autorizacion->getUsuarioCreacion()->getFullName();
                    $motivoCortesia = $autorizacion->getMotivo();
                }

                $items[] = array(
                    "numeroAsiento" => $numeroAsiento,
                    "fechaCreacion" => $boleto->getFechaCreacion()->format('d-m-Y H:i:s'),
                    "tipo" => "Boleto. " . $boleto->getDocumentoStr(),
                    "cortesia" => $esCortesia,
                    "autorizadoPor" => $autorizadoPor,
                    "motivoCortesia" => $motivoCortesia,
                    "id" => $boleto->getId(),
                    "cliente" => $boleto->getClienteDocumento()->getNombre(),
                    "origen" => $boleto->getEstacionOrigen()->getNombre(),
                    "destino" => $boleto->getEstacionDestino()->getNombre(),
                    "estacionVenta" => $boleto->getEstacionCreacion() !== null ? $boleto->getEstacionCreacion()->getNombre() : "-",
                    "revendidoEnEstacion" => $boleto->getRevendidoEnEstacion(),
                    "revendidoEnCamino" => $boleto->getRevendidoEnCamino()
                );
            }
            $reservaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')
                ->getReservacionesActivasPorSalidaNroAsiento($idSalida, $numeroAsiento);
            foreach ($reservaciones as $reservacion) {
                $items[] = array(
                    "numeroAsiento" => $numeroAsiento,
                    "fechaCreacion" => $reservacion->getFechaCreacion()->format('d-m-Y H:i:s'),
                    "tipo" => "Reservación",
                    "cortesia" => false,
                    "id" => $reservacion->getId(),
                    "cliente" => $reservacion->getCliente()->getNombre(),
                    "origen" => " - ",
                    "destino" => " - ",
                    "estacionVenta" => $reservacion->getEstacionCreacion() === null ? "-" : $reservacion->getEstacionCreacion()->getNombre(),
                    "revendidoEnEstacion" => " - ",
                    "revendidoEnCamino" => " - "
                );
            }
            usort($items, function ($item1, $item2) {
                $v1 = strtotime($item1['fechaCreacion']);
                $v2 = strtotime($item2['fechaCreacion']);
                return $v1 - $v2; // $v2 - $v1 to reverse direction
            });
        }

        $response = new JsonResponse();
        $response->setData(array(
            'items' => $items,
        ));
        return $response;
    }

    /**
     * @Route(path="/loadDatosConsultarTarifaBoletoTopMenu.json", name="ajaxLoadDatosConsultarTarifaBoletoTopMenu")
     * @Secure(roles="ROLE_USER") 
     */
    public function loadDatosConsultarTarifaBoletoTopMenu(Request $request)
    {

        $optionEstaciones = array();
        $optionClasesAsiento = array();
        $optionsClasesBus = array();
        $optionsTipoPago = array();

        try {
            $itemsEstaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->findByDestino(true);
            foreach ($itemsEstaciones as $item) {
                $optionEstaciones[] = array(
                    "id" => $item->getId(),
                    "text" => $item->__toString()
                );
            }
            $itemsClasesAsiento = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ClaseAsiento')->findAll();
            foreach ($itemsClasesAsiento as $item) {
                $optionClasesAsiento[] = array(
                    "id" => $item->getId(),
                    "text" => $item->getNombre()
                );
            }
            $itemsClasesBus = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ClaseBus')->findAll();
            foreach ($itemsClasesBus as $item) {
                $optionsClasesBus[] = array(
                    "id" => $item->getId(),
                    "text" => $item->getNombre()
                );
            }
            $itemsTipoPago = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoPago')->findAllActive();
            foreach ($itemsTipoPago as $item) {
                $optionsTipoPago[] = array(
                    "id" => $item->getId(),
                    "text" => $item->getNombre()
                );
            }
        } catch (\Exception $exc) {
        }

        $response = new JsonResponse();
        $response->setData(array(
            'optionEstaciones' => $optionEstaciones,
            'optionClasesAsiento' => $optionClasesAsiento,
            'optionsClasesBus' => $optionsClasesBus,
            'optionsTipoPago' => $optionsTipoPago
        ));

        //12 horas
        $response->setSharedMaxAge(43200);  //Cache del servidor 
        $response->setVary('Accept-Encoding');  //Cache del servidor
        $response->setExpires(new \DateTime('now + 720 minutes'));  //Cache del navegador
        return $response;
    }

    /**
     * @Route(path="/getTarifaBoletoTopMenu.json", name="ajaxGetTarifaBoletoTopMenu")
     * @Secure(roles="ROLE_USER") 
     */
    public function getTarifaBoletoTopMenu(Request $request)
    {
        $result = "No definida";
        $estacionOrigen = $request->query->get('estacionOrigen');
        if (is_null($estacionOrigen)) {
            $estacionOrigen = $request->request->get('estacionOrigen');
        }
        $estacionDestino = $request->query->get('estacionDestino');
        if (is_null($estacionDestino)) {
            $estacionDestino = $request->request->get('estacionDestino');
        }
        $claseBus = $request->query->get('claseBus');
        if (is_null($claseBus)) {
            $claseBus = $request->request->get('claseBus');
        }
        $claseAsiento = $request->query->get('claseAsiento');
        if (is_null($claseAsiento)) {
            $claseAsiento = $request->request->get('claseAsiento');
        }
        $tipoPago = $request->query->get('tipoPago');
        if (is_null($tipoPago)) {
            $tipoPago = $request->request->get('tipoPago');
        }
        $horaInicialSalida = $request->query->get('horaInicialSalida');
        if (is_null($horaInicialSalida)) {
            $horaInicialSalida = $request->request->get('horaInicialSalida');
        }
        $fechaSalida = new \DateTime();
        if ($horaInicialSalida !== null) {
            try {
                $dt = \DateTime::createFromFormat('m/d/Y h:i A', '01/01/2013 ' . $horaInicialSalida);
                $fechaSalida->setTime($dt->format('H'), $dt->format('i'));
            } catch (\Exception $ex) {
            }
        }

        if (
            $estacionOrigen !== null && trim($estacionOrigen) !== "" &&
            $estacionDestino !== null && trim($estacionDestino) !== "" &&
            $tipoPago !== null && trim($tipoPago) !== "" &&
            $claseBus !== null && trim($claseBus) !== "" &&
            $claseAsiento !== null && trim($claseAsiento) !== "" && $fechaSalida !== null
        ) {
            try {
                $tarifaBoleto = $this->get("acme_backend_tarifa")->getTarifaBoleto($estacionOrigen, $estacionDestino, $tipoPago, $claseBus, $claseAsiento, $fechaSalida);
                if ($tarifaBoleto !== null) {
                    $result = $tarifaBoleto->calcularTarifa();
                }
            } catch (\Exception $ex) {
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'result' => $result
        ));
        return $response;
    }

    /**
     * @Route(path="/loadDatosConsultarTarifaEncomiendaTopMenu.json", name="ajaxLoadDatosConsultarTarifaEncomiendaTopMenu")
     * @Secure(roles="ROLE_USER")
     */
    public function loadDatosConsultarTarifaEncomiendaTopMenu(Request $request)
    {

        $optionEstaciones = array();
        $optionTipoEncomienda = array();
        $optionTipoEncomiendaEspecial = array();

        try {

            $itemsEstaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->findByDestino(true);
            foreach ($itemsEstaciones as $item) {
                $optionEstaciones[] = array(
                    "id" => $item->getId(),
                    "text" => $item->__toString()
                );
            }

            $itemsTipoEncomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoEncomienda')->findAll();
            foreach ($itemsTipoEncomienda as $item) {
                $optionTipoEncomienda[] = array(
                    "id" => $item->getId(),
                    "text" => $item->getNombre()
                );
            }

            $itemsTipoEncomiendaEspeciales = $this->getDoctrine()
                ->getRepository('AcmeTerminalOmnibusBundle:TipoEncomiendaEspeciales')->findBy(array('activo' => true));
            foreach ($itemsTipoEncomiendaEspeciales as $item) {
                $optionTipoEncomiendaEspecial[] = array(
                    "id" => $item->getId(),
                    "text" => $item->__toString()
                );
            }
        } catch (\Exception $exc) {
        }

        $response = new JsonResponse();
        $response->setData(array(
            'optionEstaciones' => $optionEstaciones,
            'optionTipoEncomienda' => $optionTipoEncomienda,
            'optionTipoEncomiendaEspecial' => $optionTipoEncomiendaEspecial
        ));

        //12 horas
        $response->setSharedMaxAge(43200);  //Cache del servidor 
        $response->setVary('Accept-Encoding');  //Cache del servidor
        $response->setExpires(new \DateTime('now + 720 minutes'));  //Cache del navegador
        return $response;
    }

    /**
     * @Route(path="/getTarifaEncomiendaTopMenu.json", name="ajaxGetTarifaEncomiendaTopMenu")
     * @Secure(roles="ROLE_USER")
     */
    public function getTarifaEncomiendaTopMenu(Request $request)
    {
        $result = "No definida";

        $estacionOrigen = $request->query->get('estacionOrigen');
        if (is_null($estacionOrigen)) {
            $estacionOrigen = $request->request->get('estacionOrigen');
        }
        $estacionDestino = $request->query->get('estacionDestino');
        if (is_null($estacionDestino)) {
            $estacionDestino = $request->request->get('estacionDestino');
        }
        $tipoEncomienda = $request->query->get('tipoEncomienda');
        if (is_null($tipoEncomienda)) {
            $tipoEncomienda = $request->request->get('tipoEncomienda');
        }
        $cantidad = $request->query->get('cantidad');
        if (is_null($cantidad)) {
            $cantidad = $request->request->get('cantidad');
        }
        $tipoEncomiendaEspecial = $request->query->get('tipoEncomiendaEspecial');
        if (is_null($tipoEncomiendaEspecial)) {
            $tipoEncomiendaEspecial = $request->request->get('tipoEncomiendaEspecial');
        }
        $peso = $request->query->get('peso');
        if (is_null($peso)) {
            $peso = $request->request->get('peso');
        }
        $alto = $request->query->get('alto');
        if (is_null($alto)) {
            $alto = $request->request->get('alto');
        }
        $ancho = $request->query->get('ancho');
        if (is_null($ancho)) {
            $ancho = $request->request->get('ancho');
        }
        $profundidad = $request->query->get('profundidad');
        if (is_null($profundidad)) {
            $profundidad = $request->request->get('profundidad');
        }

        if (
            $estacionOrigen !== null && trim($estacionOrigen) !== "" &&
            $estacionDestino !== null && trim($estacionDestino) !== "" &&
            $tipoEncomienda !== null && trim($tipoEncomienda) !== "" &&
            $cantidad !== null && trim($cantidad) !== ""
        ) {

            $cantidadItems = $cantidad;
            $tarifaValor = 0;
            if ($tipoEncomienda === TipoEncomienda::EFECTIVO) {
                $cantidadItems = 1;
                $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEfectivo($cantidad);
                if ($tarifaEncomienda1 !== null) {
                    $tarifaValor = $tarifaEncomienda1->calcularTarifa();
                    $tarifa_efectivo_por_distancia = $this->container->getParameter("tarifa_efectivo_por_distancia");
                    if (isset($tarifa_efectivo_por_distancia) && $tarifa_efectivo_por_distancia === true) {
                        $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia($estacionOrigen, $estacionDestino);
                        if ($tarifaDistancia !== null) {
                            $tarifaValor += $tarifaDistancia->calcularTarifa();
                            $result = $tarifaValor;
                        }
                    } else {
                        $result = $tarifaValor;
                    }
                }
            } else if ($tipoEncomienda === TipoEncomienda::ESPECIAL) {
                if ($tipoEncomiendaEspecial !== null && trim($tipoEncomiendaEspecial) !== "") {
                    $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaEspeciales($tipoEncomiendaEspecial);
                    if ($tarifaEncomienda1 !== null) {
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa();
                        $tarifa_especial_por_distancia = $this->container->getParameter("tarifa_especial_por_distancia");
                        if (isset($tarifa_especial_por_distancia) && $tarifa_especial_por_distancia === true) {
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia($estacionOrigen, $estacionDestino);
                            if ($tarifaDistancia !== null) {
                                $tarifaValor += $tarifaDistancia->calcularTarifa();
                                $result = $cantidad * $tarifaValor;
                            }
                        } else {
                            $result = $cantidad * $tarifaValor;
                        }
                    }
                }
            } else if ($tipoEncomienda === TipoEncomienda::PAQUETE) {
                if (
                    $peso !== null && trim($peso) !== "" &&
                    $alto !== null && trim($alto) !== "" &&
                    $ancho !== null && trim($ancho) !== "" &&
                    $profundidad !== null && trim($profundidad) !== ""
                ) {

                    $volumen = $alto * $ancho * $profundidad;
                    $tarifaEncomienda1 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesVolumen($volumen);
                    $tarifaEncomienda2 = $this->get("acme_backend_tarifa")->getTarifaEncomiendaPaquetesPeso($peso);
                    if ($tarifaEncomienda1 !== null && $tarifaEncomienda2 !== null) {
                        $tarifaValor = $tarifaEncomienda1->calcularTarifa() + $tarifaEncomienda2->calcularTarifa();
                        $tarifa_paquete_por_distancia = $this->container->getParameter("tarifa_paquete_por_distancia");
                        if (isset($tarifa_paquete_por_distancia) && $tarifa_paquete_por_distancia === true) {
                            $tarifaDistancia = $this->get("acme_backend_tarifa")->getTarifaEncomiendaDistancia($estacionOrigen, $estacionDestino);
                            if ($tarifaDistancia !== null) {
                                $tarifaValor += $tarifaDistancia->calcularTarifa();
                                $result = $cantidad * $tarifaValor;
                            }
                        } else {
                            $result = $cantidad * $tarifaValor;
                        }
                    }
                }
            }

            $result = UtilService::aplicarDescuento($result, $cantidadItems);
        }

        $response = new JsonResponse();
        $response->setData(array(
            'result' => $result
        ));
        return $response;
    }

    /**
     * @Route(path="/listarEstaciones.json", name="ajaxListarEstaciones")
     * @Secure(roles="ROLE_USER")
     */
    public function listarEstaciones(Request $request)
    {

        $optionEstaciones = array();

        try {
            $itemsEstaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->findAll();
            foreach ($itemsEstaciones as $item) {
                $optionEstaciones[] = array(
                    "id" => $item->getId(),
                    "text" => $item->__toString()
                );
            }
        } catch (\Exception $exc) {
        }

        $response = new JsonResponse();
        $response->setData(array(
            'optionEstaciones' => $optionEstaciones
        ));
        return $response;
    }

    /**
     * @Route(path="/listarFacturas.json", name="ajaxListarFacturas")
     * @Secure(roles="ROLE_USER")
     */
    public function listarFacturas(Request $request)
    {

        $optionFacturas = array();
        $estacion = $this->getUser()->getEstacion();
        $empresa = $request->query->get('empresa');
        if (is_null($empresa)) {
            $empresa = $request->request->get('empresa');
        }
        $servicio = $request->query->get('servicio');
        if (is_null($servicio)) {
            $servicio = $request->request->get('servicio');
        }
        $facturaSelected = "";
        if ($estacion !== null && $empresa !== null && trim($empresa) !== "" && $servicio !== null && trim($servicio) !== "") {
            try {
                $itemsFacturas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')
                    ->listarFacturaPorEstacionEmpresaServicio($estacion, $empresa, $servicio);
                foreach ($itemsFacturas as $item) {
                    $text = "SERIE: " . $item->getSerieResolucionFactura() .
                        ", VALOR: " . $item->getValorResolucionFactura() .
                        ", MIN: " . $item->getMinimoResolucionFactura() .
                        ", MAX: " . $item->getMaximoResolucionFactura() .
                        " ACTIVA: " . ($item->getActivo() === true ? "SI" : "NO");
                    $optionFacturas[] = array(
                        "id" => $item->getId(),
                        "text" => $text
                    );

                    if ($item->getActivo()) {
                        $facturaSelected = $item->getId();
                    }
                }
            } catch (\Exception $exc) {
            }
        }
        $response = new JsonResponse();
        $response->setData(array(
            'optionFacturas' => $optionFacturas,
            'facturaSelected' => $facturaSelected
        ));
        return $response;
    }

    /**
     * @Route(path="/listarFacturas2.json", name="ajaxListarFacturas2")
     * @Secure(roles="ROLE_USER")
     */
    public function listarFacturas2(Request $request)
    {

        $optionFacturas = array();
        $estacion = $this->getUser()->getEstacion();
        if ($estacion === null) {
            $estacion = $request->query->get('estacion');
            if (is_null($estacion)) {
                $estacion = $request->request->get('estacion');
            }
        }

        if ($estacion !== null && trim($estacion) !== "") {
            try {
                $facturas = array();
                $facturas = $this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:Factura')->getFacturasPorEstacion(
                    $estacion,
                    $this->getUser()->getEmpresas()
                );
                foreach ($facturas as $item) {
                    $optionFacturas[] = array(
                        "srf" => $item->getSerieResolucionFactura() . ' ' . $item->getValorResolucionFactura(),
                        "min" => $item->getMinimoResolucionFactura(),
                        "max" => $item->getMaximoResolucionFactura(),
                        "emp" => $item->getEmpresa()->getNombre(),
                        "servicio" => $item->getServicioEstacion()->getNombre(),
                        "factEspecial" => $item->getEstacion()->getFacturacionEspecial() === true ? "SI" : "NO",
                        "ping" => $item->getEstacion()->getPingFacturacionEspecial() === null ? "" : $item->getEstacion()->getPingFacturacionEspecial(),
                    );
                }
            } catch (\Exception $exc) {
            }
        }
        $response = new JsonResponse();
        $response->setData(array(
            'optionFacturas' => $optionFacturas
        ));
        return $response;
    }

    /**
     * @Route(path="/listarSalidasPaginando.json", name="ajaxListarSalidasPaginando")
     * @Secure(roles="ROLE_USER") 
     */
    public function listarSalidasPaginandoAction(Request $request)
    {
        $options = array();
        try {
            $pageLimit = $request->query->get('page_limit');
            if (is_null($pageLimit)) {
                $pageLimit = $request->request->get('page_limit');
            }
            $term = $request->query->get('term');
            if (is_null($term)) {
                $term = $request->request->get('term');
            }
            $salidas = $this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:Salida')->listarSalidasPaginando($pageLimit, $term);
            foreach ($salidas as $salida) {
                $text = $salida->getId() . " / " . $salida->getItinerario()->getRuta()->getNombre() . " / " . $salida->getFecha()->format('d-m-Y H:i:s');
                $options[] = array(
                    "id" => $salida->getId(),
                    "text" => $text
                );
            }
        } catch (\RuntimeException $exc) {
        } catch (\Exception $exc) {
        }

        $response = new JsonResponse();
        $response->setData(array(
            'options' => $options
        ));
        return $response;
    }

    /**
     * @Route(path="/listarBoletosAChequear.html", name="ajaxListarBoletosAChequear")
     * @Secure(roles="ROLE_USER")
     */
    public function listarBoletosAChequearAction(Request $request)
    {
        $html = "";

        $salida = $request->query->get('salida');
        if (is_null($salida)) {
            $salida = $request->request->get('salida');
        }

        if ($salida !== null && trim($salida) !== "") {

            try {

                $boletos = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')
                    ->listarBoletosEmitidosChequeadosTransitoBySalida($salida);
                foreach ($boletos as $boleto) {
                    if ($boleto instanceof Boleto) {
                        $tr = "<tr data-id='" . $boleto->getId() . "'>";
                        $tr .= "<td>" . strval($boleto->getId()) . "</td>";
                        if ($boleto->getEstado()->getId() !== EstadoBoleto::EMITIDO) {
                            //CHQUEADOS Y EN TRANSITO
                            $tr .= '<td><input type="checkbox" class="chequeadoSI" name="activo" value="1" disabled readonly checked /></td>';
                            $tr .= '<td><input type="checkbox" class="chequeadoNO" name="activo" value="1" disabled readonly /></td>';
                        } else {
                            if ($this->getUser()->getEstacion() !== null && $boleto->getEstacionOrigen()->getId() === $this->getUser()->getEstacion()->getId()) {
                                $tr .= '<td><input type="checkbox" class="chequeadoSI" name="activo" value="1"/></td>';
                                $tr .= '<td><input type="checkbox" class="chequeadoNO" name="activo" value="1"/></td>';
                            } else {
                                //Solamente puede chequear un boleto un usuario de la estacion donde sube el pasajero.
                                $tr .= '<td><input type="checkbox" class="chequeadoSI" name="activo" value="1" disabled readonly /></td>';
                                $tr .= '<td><input type="checkbox" class="chequeadoNO" name="activo" value="1" disabled readonly /></td>';
                            }
                        }

                        $asientoBus = "N/D";
                        if ($boleto->getAsientoBus() !== null) {
                            $asientoBus = strval($boleto->getAsientoBus()->getNumero());
                        }

                        $tr .= "<td>" . $asientoBus . "</td>";

                        if ($boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA) {
                            $tr .= "<td>Cortesía</td>";
                        } else if ($boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA) {
                            $tr .= "<td>Agencia</td>";
                        } else if ($boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::VOUCHER || $boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION) {
                            $tr .= "<td>Voucher</td>";
                        } else {
                            $values = array();
                            $facturas = UtilService::getBoletosFacturasBoletosReasignados($boleto);
                            foreach ($facturas as $factura) {
                                $values[] = $factura->getInfo2();
                            }
                            $tr .= "<td>" .  implode(",", $values) . "</td>";
                        }
                        $tr .= "<td>" . $boleto->getEstacionOrigen()->__toString() . "</td>";
                        $tr .= "<td>" . $boleto->getEstacionDestino()->__toString() . "</td>";
                        $tr .= "<td>" . $boleto->getClienteBoleto()->getNombre() . "</td>";
                        $tr .= "</tr>";
                        $html .= $tr;
                    }
                }
            } catch (\Exception $exc) {
                var_dump($exc);
                //                $this->get("logger")->warn("ERROR:" . $exc->getTraceAsString());
                //            var_dump($exc);
                //            echo $exc->getTraceAsString();
            }
        } else {
            return UtilService::returnError($this, "m1Debe seleccionar una salida.");
        }

        return new Response($html);
    }

    /**
     * @Route(path="/listarMonedasCajasAbiertas.json", name="ajaxMonedasCajasAbiertas")
     * @Secure(roles="ROLE_USER")
     */
    public function listarMonedasCajasAbiertasAction(Request $request)
    {
        $optionMonedas = array();
        try {
            $monedas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->listarMonedasCajasAbiertas($this->getUser());
            foreach ($monedas as $moneda) {
                $optionMonedas[] = array(
                    "id" => $moneda->getId(),
                    "text" => $moneda->getSigla()
                );
            }
        } catch (\Exception $exc) {
        }

        $response = new JsonResponse();
        $response->setData(array(
            'optionMonedas' => $optionMonedas
        ));
        return $response;
    }

    /**
     * @Route(path="/listarDatosIniciales.json", name="ajaxListarDatosIniciales")
     * @Secure(roles="ROLE_USER")
     */
    public function listarDatosInicialesAction(Request $request)
    {
        $showVoucher = false;
        $optionMonedas = array();

        try {

            $user = $this->getUser();
            if ($user->getEstacion() !== null) {
                $showVoucher = $user->getEstacion()->getPermitirVoucherBoleto();
            }

            $monedas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Caja')->listarMonedasCajasAbiertas($user);
            foreach ($monedas as $moneda) {
                $optionMonedas[] = array(
                    "id" => $moneda->getId(),
                    "text" => $moneda->getSigla()
                );
            }
        } catch (\Exception $exc) {
        }

        $response = new JsonResponse();
        $response->setData(array(
            'showVoucher' => $showVoucher,
            'optionMonedas' => $optionMonedas
        ));
        return $response;
    }

    /**
     * @Route(path="/listarSaldosAgencias.json", name="ajaxListarSaldosAgencias")
     * @Secure(roles="ROLE_USER")
     */
    public function listarSaldosAgencias(Request $request)
    {

        $estado = "";
        $saldo = "0.00";
        $bonif = "0.00";
        $total = "0.00";
        $totalDepositado = "0.00";
        $estacion = $this->getUser()->getEstacion();
        if ($estacion === null) {
            $estacion = $request->query->get('estacion');
            if (is_null($estacion)) {
                $estacion = $request->request->get('estacion');
            }
        }

        if ($estacion !== null && trim($estacion) !== "") {
            try {
                $estacion = $this->getDoctrine()->getManager()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($estacion);
                if ($estacion !== null) {
                    $estado = $estacion->getActivo() === true ? "Activo" : "Bloqueado";
                    $saldo = doubleval($estacion->getSaldo());
                    $bonif = doubleval($estacion->getBonificacion());
                    $total = (doubleval($estacion->getSaldo()) + doubleval($estacion->getBonificacion()));
                    $totalDepositado = doubleval($this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:DepositoAgencia')->totalDepositado($estacion));
                }
            } catch (\Exception $exc) {
            }
        }
        $response = new JsonResponse();
        $response->setData(array(
            'estado' => $estado,
            'saldo' => $saldo,
            'bonif' => $bonif,
            'total' => $total,
            'totalDepositado' => $totalDepositado
        ));
        return $response;
    }

    /**
     * @Route(path="/listarEncomiendasAProcesar.json", name="ajaxListarEncomiendasAProcesar")
     * @Secure(roles="ROLE_USER")
     */
    public function listarEncomiendasAProcesarAction(Request $request)
    {

        $error = "";
        $empresa = "";
        $encomiendasAbordadas = array();
        $encomiendasPendientes = array();
        $encomiendasPendientesOtrasRutas = array();

        $idSalida = $request->query->get('salida');
        if (is_null($idSalida)) {
            $idSalida = $request->request->get('salida');
        }

        if ($idSalida !== null && trim($idSalida) !== "") {

            try {

                $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($idSalida);
                if ($salida !== null) {
                    $estacionUsuario = $this->getUser()->getEstacion();
                    $empresa = $salida->getEmpresa()->getAlias();

                    //Buscando encomiendas asociadas a la salida
                    $items = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasEmbarcadasBySalidaParaProcesar($idSalida);
                    foreach ($items as $item) {
                        if ($item instanceof Encomienda) {
                            $puedeModificar = false;
                            if ($estacionUsuario == null) {
                                $puedeModificar = false;
                            } else if ($item->checkEstacionesOrigen($estacionUsuario)) {
                                if ($item->checkEstuboTransito()) {
                                    $puedeModificar = false;
                                } else {
                                    $puedeModificar = true;
                                }
                            } else if ($item->checkEstacionesIntermedias($estacionUsuario)) {
                                if ($item->checkEstuboTransito()) {
                                    $puedeModificar = true;
                                } else {
                                    $puedeModificar = false;
                                }
                            } else {
                                $puedeModificar = false;
                            }

                            $descripcion =
                                "Estado: " . $item->getUltimoEstado()->getNombre() . ". <BR>"
                                . "Tipo: " . $item->getTipoEncomienda()->getNombre() . ". Cantidad : " . $item->getCantidad() . ". <BR>"
                                . ($item->getDescripcion() !== null && trim($item->getDescripcion()) !== "" ? trim($item->getDescripcion()) . ". <BR>" : "")
                                //                                    . $item->getRutasIntermediasStr()
                            ;

                            $documento = $item->getTipoDocumento()->getNombre();
                            if (
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA ||
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR
                            ) {
                                if ($item->getFacturaGenerada() !== null) {
                                    $documento .= " - " . $item->getFacturaGenerada()->getInfo2();
                                } else {
                                    $documento .= " - N/D";
                                }
                            }

                            $encomiendasAbordadas[] = array(
                                "id" => $item->getId(),
                                "puedeModificar" => $puedeModificar,
                                "fecha" => $item->getFechaCreacion()->format('d-m-Y h:i A'),
                                "cant" => $item->getCantidad(),
                                "desc" => $descripcion,
                                "doc" => $documento,
                                "origen" => $item->getEstacionOrigen()->__toString(),
                                "destino" => $item->getEstacionDestino()->__toString(),
                                //                                "cliente" => $item->getClienteRemitente()->getNombre()
                            );
                        }
                    }

                    //Buscando encomiendas que se pueden asociar a la salida
                    $items = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasPendientesByRutaParaProcesar($salida->getEmpresa(), $salida->getItinerario()->getRuta(), $estacionUsuario);
                    foreach ($items as $item) {
                        if ($item instanceof Encomienda) {
                            $puedeModificar = false;
                            if ($estacionUsuario == null) {
                                $puedeModificar = false;
                            } else if ($item->checkEstacionesOrigen($estacionUsuario)) {
                                if ($item->checkEstuboTransito()) {
                                    continue;
                                } else {
                                    $puedeModificar = true;
                                }
                            } else if ($item->checkEstacionesIntermedias($estacionUsuario)) {
                                if ($item->checkEstuboTransito()) {
                                    if (!$item->checkEstacionDestino($estacionUsuario)) {
                                        $puedeModificar = true;     //Estacion intermedia
                                    } else {
                                        continue;                   //Estacion final, ya no hace falta embarcarla
                                    }
                                } else {
                                    continue; //No necesito mostrarla pq no ha salido del origen
                                }
                            } else {
                                continue;
                            }
                            $descripcion =
                                "Estado: " . $item->getUltimoEstado()->getNombre() . ". <BR>"
                                . "Tipo: " . $item->getTipoEncomienda()->getNombre() . ". Cantidad : " . $item->getCantidad() . ". <BR>"
                                . ($item->getDescripcion() !== null && trim($item->getDescripcion()) !== "" ? trim($item->getDescripcion()) . ". <BR>" : "")
                                //                                    . $item->getRutasIntermediasStr()
                            ;

                            $documento = $item->getTipoDocumento()->getNombre();
                            if (
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA ||
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR
                            ) {
                                if ($item->getFacturaGenerada() !== null) {
                                    $documento .= " - " . $item->getFacturaGenerada()->getInfo2();
                                } else {
                                    $documento .= " - N/D";
                                }
                            }

                            $encomiendasPendientes[] = array(
                                "id" => $item->getId(),
                                "puedeModificar" => $puedeModificar,
                                "fecha" => $item->getFechaCreacion()->format('d-m-Y h:i A'),
                                "cant" => $item->getCantidad(),
                                "desc" => $descripcion,
                                "doc" => $documento,
                                "origen" => $item->getEstacionOrigen()->__toString(),
                                "destino" => $item->getEstacionDestino()->__toString(),
                                //                                "cliente" => $item->getClienteRemitente()->getNombre()
                            );
                        }
                    }
                } else {
                    $error = "No se pudo obtener la salida con id: " . $idSalida;
                }
            } catch (\Exception $exc) {
                var_dump($exc);
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'salida' => $idSalida,
            'empresa' => $empresa,
            'encomiendasAbordadas' => $encomiendasAbordadas,
            'encomiendasPendientes' => $encomiendasPendientes,
            'encomiendasPendientesOtrasRutas' => $encomiendasPendientesOtrasRutas,
        ));
        return $response;
    }

    /**
     * @Route(path="/listarEncomiendasPendientesPorEstacion.json", name="ajaxListarEncomiendasPendientesPorEstacion")
     * @Secure(roles="ROLE_USER")
     */
    public function listarEncomiendasPendientesPorEstacionAction(Request $request)
    {

        $error = "";
        $encomiendas = array();

        $idEstacion = $request->query->get('estacion');
        if (is_null($idEstacion)) {
            $idEstacion = $request->request->get('estacion');
        }

        if ($idEstacion !== null && trim($idEstacion) !== "") {

            try {

                $estacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($idEstacion);
                if ($estacion !== null) {

                    $estacionUsuario = $this->getUser()->getEstacion();

                    $items = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasPendientesPorEstacion($estacion);

                    foreach ($items as $item) {
                        if ($item instanceof Encomienda) {
                            $puedeModificar = false;
                            if ($estacionUsuario == null)
                                $puedeModificar = false;
                            else if ($item->checkEstacionesOrigen($estacionUsuario)) {
                                if ($item->checkEstuboTransito()) {
                                    $puedeModificar = false;
                                } else {
                                    $puedeModificar = true;
                                }
                            } else {
                                $puedeModificar = false;
                            }

                            $descripcion = "Estado: " . $item->getUltimoEstado()->getNombre() . ". <BR>"
                                . "Tipo: " . $item->getTipoEncomienda()->getNombre() . ". Cantidad : " . $item->getCantidad() . ". <BR>"
                                //                                  . "Origen: " . $item->getEstacionOrigen()->__toString() . ". <BR>" 
                                //                                  . "Destino: " . $item->getEstacionDestino()->__toString() . ". <BR>" 
                                //                                  . "Remitente: " . $item->getClienteRemitente()->getNombre() . ". <BR>" 
                                . ($item->getDescripcion() !== null && trim($item->getDescripcion()) !== "" ? trim($item->getDescripcion()) . ". <BR>" : "")
                                //                                   . $item->getRutasIntermediasStr()
                            ;

                            $documento = $item->getTipoDocumento()->getNombre();
                            if (
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA ||
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR
                            ) {
                                if ($item->getFacturaGenerada() !== null) {
                                    $documento .= " - " . $item->getFacturaGenerada()->getInfo2();
                                } else {
                                    $documento .= " - N/D";
                                }
                            }

                            $rutas = array();
                            $primerDestino = $item->getRutas()->first()->getEstacionDestino();
                            $encomiendas[] = array(
                                "id" => $item->getId(),
                                "puedeModificar" => $puedeModificar,
                                "fecha" => $item->getFechaCreacion()->format('d-m-Y h:i A'),
                                "empresa" => $item->getEmpresa()->getAlias(),
                                "cant" => $item->getCantidad(),
                                "desc" => $descripcion,
                                "doc" => $documento,
                                "codigoPrimeraRuta" => $item->getRuta()->getCodigo(),
                                "nombrePrimeraRuta" => $item->getRuta()->getNombre(),
                                "proxDestino" => $primerDestino->__toString(),
                                "idEstacionOrigen" => $item->getEstacionOrigen()->getId(),
                                "idEstacionPrimerDestino" => $primerDestino->getId(),
                            );
                        }
                    }
                } else {
                    $error = "No se pudo obtener la estación con id: " . $idEstacion;
                }
            } catch (\Exception $exc) {
                var_dump($exc);
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'encomiendas' => $encomiendas,
        ));
        return $response;
    }

    /**
     * @Route(path="/getEncomiendaPendientesById.json", name="ajaxGetEncomiendaPendientesById")
     * @Secure(roles="ROLE_USER")
     */
    public function getEncomiendaPendientesByIdAction(Request $request)
    {

        $error = "";
        $encomienda = null;

        $idEncomienda = $request->query->get('idEncomienda');
        if (is_null($idEncomienda)) {
            $idEncomienda = $request->request->get('idEncomienda');
        }

        if ($idEncomienda !== null && trim($idEncomienda) !== "") {

            try {
                $estacionUsuario = $this->getUser()->getEstacion();
                $item = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($idEncomienda);
                if ($item instanceof Encomienda) {
                    $puedeModificar = false;
                    if ($estacionUsuario == null)
                        $puedeModificar = false;
                    else if ($item->checkEstacionesOrigen($estacionUsuario)) {
                        if ($item->checkEstuboTransito()) {
                            $puedeModificar = false;
                        } else {
                            $puedeModificar = true;
                        }
                    } else {
                        $puedeModificar = false;
                    }

                    $descripcion = "Estado: " . $item->getUltimoEstado()->getNombre() . ". <BR>"
                        . "Tipo: " . $item->getTipoEncomienda()->getNombre() . ". Cantidad : " . $item->getCantidad() . ". <BR>"
                        //                                  . "Origen: " . $item->getEstacionOrigen()->__toString() . ". <BR>" 
                        //                                  . "Destino: " . $item->getEstacionDestino()->__toString() . ". <BR>" 
                        //                                  . "Remitente: " . $item->getClienteRemitente()->getNombre() . ". <BR>" 
                        . ($item->getDescripcion() !== null && trim($item->getDescripcion()) !== "" ? trim($item->getDescripcion()) . ". <BR>" : "")
                        //                                   . $item->getRutasIntermediasStr()
                    ;

                    $documento = $item->getTipoDocumento()->getNombre();
                    if (
                        $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA ||
                        $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR
                    ) {
                        if ($item->getFacturaGenerada() !== null) {
                            $documento .= " - " . $item->getFacturaGenerada()->getInfo2();
                        } else {
                            $documento .= " - N/D";
                        }
                    }

                    $primerDestino = $item->getRutas()->first()->getEstacionDestino();
                    $encomienda = array(
                        "id" => $item->getId(),
                        "puedeModificar" => $puedeModificar,
                        "fecha" => $item->getFechaCreacion()->format('d-m-Y h:i A'),
                        "empresa" => $item->getEmpresa()->getAlias(),
                        "cant" => $item->getCantidad(),
                        "desc" => $descripcion,
                        "doc" => $documento,
                        "codigoPrimeraRuta" => $item->getRuta()->getCodigo(),
                        "nombrePrimeraRuta" => $item->getRuta()->getNombre(),
                        "proxDestino" => $primerDestino->__toString(),
                        "idEstacionOrigen" => $item->getEstacionOrigen()->getId(),
                        "idEstacionPrimerDestino" => $primerDestino->getId(),
                    );
                }
            } catch (\Exception $exc) {
                var_dump($exc);
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'encomienda' => $encomienda,
        ));
        return $response;
    }

    /**
     * @Route(path="/listarEncomiendasPendientesEntrega.json", name="ajaxListarEncomiendasPendientesEntrega")
     * @Secure(roles="ROLE_USER")
     */
    public function listarEncomiendasPendientesEntregaAction(Request $request)
    {

        $error = "";
        $idFactura = "";
        $serieFactura = "";
        $encomiendas = array();
        $optionSeriesFacturas = array();

        $idEstacion = $request->query->get('estacion');
        if (is_null($idEstacion)) {
            $idEstacion = $request->request->get('estacion');
        }
        $idEmpresa = $request->query->get('empresa');
        if (is_null($idEmpresa)) {
            $idEmpresa = $request->request->get('empresa');
        }

        $showClientes = $request->query->get('showClientes');
        if (is_null($showClientes)) {
            $showClientes = $request->request->get('showClientes');
        }

        if ($idEstacion !== null && trim($idEstacion) !== "" && $idEmpresa !== null && trim($idEmpresa) !== "") {

            try {

                $estacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($idEstacion);
                if ($estacion !== null) {

                    $items = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendasPendientesEntrega($estacion, $idEmpresa, $this->getUser());

                    foreach ($items as $item) {
                        if ($item instanceof Encomienda) {

                            if ($item->checkEstuboTransito() === false) {
                                continue;
                            }

                            $descripcion = "Tipo: " . $item->getTipoEncomienda()->getNombre() . ". " .
                                "Cantidad : " . $item->getCantidad() . ". <BR>" .
                                "Estado: " . $item->getUltimoEstadoById()->getNombre() . ". <BR>" .
                                //                                    "Origen: " . $item->getEstacionOrigen()->__toString() . ". <BR>" .
                                //                                    "Destino: " . $item->getEstacionDestino()->__toString() . ". <BR>" .
                                //                                    "Destinatario: " . $item->getClienteDestinatario()->getNombre() . ". <BR>" .
                                ($item->getDescripcion() !== null && trim($item->getDescripcion()) !== "" ? trim($item->getDescripcion()) . ". <BR>" : "");

                            $documento = $item->getTipoDocumento()->getNombre();
                            if (
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA ||
                                $item->getTipoDocumento()->getId() === TipoDocumentoEncomienda::POR_COBRAR
                            ) {
                                if ($item->getFacturaGenerada() !== null) {
                                    $documento .= " - " . $item->getFacturaGenerada()->getInfo2();
                                }
                            }

                            $encomienda = array(
                                "id" => $item->getId(),
                                "fecha" => $item->getFechaCreacion()->format('d-m-Y h:i A'),
                                "empresa" => $item->getEmpresa()->getAlias(),
                                "desc" => $descripcion,
                                "idDoc" => $item->getTipoDocumento()->getId(),
                                "doc" => $documento,
                                "importe" => $item->getPrecioCalculadoMonedaBase() === null ? 0 : $item->getPrecioCalculadoMonedaBase()
                            );
                            if (!is_null($showClientes) && ($showClientes === true || $showClientes === 'true')) {
                                $cli = "Remt: " . $item->getClienteRemitente()->getInfo2() . "<BR>" .
                                    "Dest: " . $item->getClienteDestinatario()->getInfo2();
                                $encomienda["cli"] = $cli;
                            }
                            $encomiendas[] = $encomienda;
                        }
                    }

                    $facturas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')
                        ->listarFacturaPorEstacionEmpresaServicio($estacion, $idEmpresa, ServicioEstacion::ENCOMIENDA, true);
                    foreach ($facturas as $item) {
                        $optionSeriesFacturas[] = array(
                            "id" => $item->getId(),
                            "text" => $item->getSerieResolucionFactura() . ' (' . $item->getEmpresa()->getAlias() . ')(' .
                                $item->getMinimoResolucionFactura() . "-" . $item->getMaximoResolucionFactura() . ")",
                            "idEmpresa" => $item->getEmpresa()->getId()
                        );
                    }
                } else {
                    $error = "No se pudo obtener la estación con id: " . $idEstacion;
                }
            } catch (\Exception $exc) {
                var_dump($exc);
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'encomiendas' => $encomiendas,
            'optionSeriesFacturas' => $optionSeriesFacturas
        ));
        return $response;
    }

    /**
     * @Route(path="/listarTipoEncomiendaEspecialPaginando.json", name="ajaxListarTipoEncomiendaEspecialPaginando")
     * @Secure(roles="ROLE_USER") 
     */
    public function listarTipoEncomiendaEspecialPaginando(Request $request)
    {
        $error = "";
        $options = array();
        try {
            $pageLimit = $request->query->get('page_limit');
            if (is_null($pageLimit)) {
                $pageLimit = $request->request->get('page_limit');
            }
            $term = $request->query->get('term');
            if (is_null($term)) {
                $term = $request->request->get('term');
            }
            $id = $request->query->get('id');
            if (is_null($id)) {
                $id = $request->request->get('id');
            }
            $items = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoEncomiendaEspeciales')->listarTipoEncomiendaEspecialesPaginandoNativo($pageLimit, $term, $id);
            foreach ($items as $item) {
                $text = trim($item["nombre"]);
                $descripcion = trim($item["descripcion"]);
                if ($descripcion !== "") {
                    $text .=  " / " . $descripcion;
                }
                $item = array(
                    "id" => $item["id"],
                    "text" => $text
                );
                $options[] = $item;
            }
        } catch (\RuntimeException $exc) {
            $error = $exc->getTraceAsString();
        } catch (\Exception $exc) {
            $error = $exc->getTraceAsString();
        }
        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'options' => $options
        ));
        return $response;
    }

    /**
     * @Route(path="/getInformacionBoleto.json", name="ajaxGetInformacionBoleto")
     * @Secure(roles="ROLE_USER") 
     */
    public function getInformacionBoleto(Request $request)
    {

        $optionCliente = array();
        $optionRuta = array();
        $optionDestino = array();

        $idBoleto = $request->query->get('idBoleto');
        if (is_null($idBoleto)) {
            $idBoleto = $request->request->get('idBoleto');
        }

        if ($idBoleto !== null && trim($idBoleto) !== "") {
            $boleto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($idBoleto);
            if ($boleto !== null) {
                $optionCliente[] = array(
                    "id" => $boleto->getClienteBoleto()->getId(),
                    "text" => $boleto->getClienteBoleto()->getInfo2()
                );
                $ruta = $boleto->getSalida()->getItinerario()->getRuta();
                $fechaDia = new \DateTime();
                $empresaStr = "";
                $empresa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                    ->getEmpresaQueFactura($ruta, $fechaDia);
                if ($empresa !== null) {
                    $empresaStr = " ( " . $empresa->getAlias() . " ) ";
                }
                $text = strval($ruta->getCodigo()) . " - " . $ruta->getNombre() . $empresaStr;
                $optionRuta[] = array(
                    "id" => $ruta->getCodigo(),
                    "text" =>  $text,
                    "idEmpresa" => ($empresa !== null) ? $empresa->getId() : ""
                );
                $optionDestino[] = array(
                    "id" => $boleto->getEstacionDestino()->getId(),
                    "text" => $boleto->getEstacionDestino()->__toString()
                );
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'optionCliente' => $optionCliente,
            'optionRuta' => $optionRuta,
            'optionDestino' => $optionDestino
        ));
        return $response;
    }

    /**
     * @Route(path="/getSeriesActivaPorEstacion.json", name="ajaxSeriesActivaPorEstacion")
     * @Secure(roles="ROLE_USER") 
     */
    public function getSeriesActivaPorEstacion(Request $request)
    {

        $optionSeriesFacturas = array();
        $showVoucher = false;

        $idEstacion = $request->query->get('idEstacion');
        if (is_null($idEstacion)) {
            $idEstacion = $request->request->get('idEstacion');
        }
        $idSalida = $request->query->get('idSalida');
        if (is_null($idSalida)) {
            $idSalida = $request->request->get('idSalida');
        }
        $idEmpresa = $request->query->get('idEmpresa');
        if (is_null($idEmpresa)) {
            $idEmpresa = $request->request->get('idEmpresa');
        }
        $tipoServicio = $request->query->get('tipoServicio');
        if (is_null($tipoServicio)) {
            $tipoServicio = $request->request->get('tipoServicio');
        }
        if ($idEstacion !== null && trim($idEstacion) !== "") {

            $estacion = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->find($idEstacion);
            if ($estacion !== null) {
                $showVoucher = $estacion->getPermitirVoucherBoleto();
            }

            if (is_null($tipoServicio)) {
                $tipoServicio = ServicioEstacion::BOLETO;
            }
            $empresa = null;
            if ($idSalida !== null) {
                $salida = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->getDatosParcialesSalida($idSalida);
                if ($salida !== null) {
                    $empresa = $salida->getEmpresa();
                }
            } else {
                $empresa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Empresa')->find($idEmpresa);
            }

            $facturas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Factura')
                ->listarFacturaPorEstacionEmpresaServicio($idEstacion, $empresa, $tipoServicio, true);
            foreach ($facturas as $item) {
                $optionSeriesFacturas[] = array(
                    "id" => $item->getId(),
                    "text" => $item->getSerieResolucionFactura() . ' (' . $item->getEmpresa()->getAlias() . ')(' .
                        $item->getMinimoResolucionFactura() . "-" . $item->getMaximoResolucionFactura() . ")",
                    "idEmpresa" => $item->getEmpresa()->getId()
                );
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'showVoucher' => $showVoucher,
            'optionSeriesFacturas' => $optionSeriesFacturas
        ));
        return $response;
    }

    /**
     * @Route(path="/getNotificaciones.html", name="ajaxGetNotificaciones")
     * @Secure(roles="ROLE_USER") 
     */
    public function getNotificaciones(Request $request)
    {

        $items = array();
        $user = $this->getUser();
        $idEmpresasUsuario = $user->getIdEmpresas();
        $mostrarAlertasAgencias = false;
        $mostrarAlertasEstaciones = false;
        $estacionUsuario = $user->getEstacion();
        if ($estacionUsuario !== null) {
            if ($estacionUsuario->getTipo()->getId() === \Acme\TerminalOmnibusBundle\Entity\TipoEstacion::AGENCIA) {
                $mostrarAlertasAgencias = true;
            } else {
                $mostrarAlertasEstaciones = true;
            }
        } else {
            $mostrarAlertasAgencias = true;
            $mostrarAlertasEstaciones = true;
        }

        $notificaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Notificacion')
            ->listarNotificacion($mostrarAlertasEstaciones, $mostrarAlertasAgencias);
        foreach ($notificaciones as $notificacion) {
            $items[] = array(
                'text' => $notificacion->getTexto(),
                'time' => intval($notificacion->getSegundos()) * 1000
            );
        }

        //SALIDAS PENDIENTES INIT
        $salidas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Salida')->listarSalidasPendientes($estacionUsuario, $idEmpresasUsuario);
        if ($salidas !== null && count($salidas) !== 0) {
            foreach ($salidas as $salida) {
                $info = " ALERTA DE SALIDA PENDIENTE DE INICIAR O CANCELAR >>> FECHA: " . $salida->getFecha()->format('d-m-Y h:i A') . ", RUTA: " .
                    $salida->getItinerario()->getRuta()->getCodigo() . "-" . strtoupper($salida->getItinerario()->getRuta()->getNombre()) .
                    ", ESTADO: " . strtoupper($salida->getEstado()->getNombre()) . " <<<  ";
                $items[] = array(
                    'text' => $info,
                    'time' => 9000
                );
            }
        }
        //SALIDAS PENDIENTES END

        $response = new JsonResponse();
        $response->setData(array(
            'items' => $items,
        ));
        return $response;
    }

    /**
     * @Route(path="/listarTiemposEstacionesByRuta.json", name="ajaxListarTiemposEstacionesByRuta")
     * @Secure(roles="ROLE_USER") 
     */
    public function listarTiemposEstacionesByRutaAction(Request $request)
    {

        $optionsEstaciones = array();

        $ruta = $request->query->get('ruta');
        if (is_null($ruta)) {
            $ruta = $request->request->get('ruta');
        }
        $claseBus = $request->query->get('claseBus');
        if (is_null($claseBus)) {
            $claseBus = $request->request->get('claseBus');
        }

        if ($ruta !== null && trim($ruta) !== "" && $claseBus !== null && trim($claseBus) !== "") {
            $ruta = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Ruta')->find($ruta);
            if ($ruta !== null) {
                $listaEstaciones = $ruta->getListaTodasEstaciones(true);
                $mapEstacionWithTime = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getEstacionesWithTiemposByRutaByClaseBus($ruta, $claseBus);
                foreach ($listaEstaciones as $estacion) {
                    $minutos = 9999;
                    if (isset($mapEstacionWithTime[$estacion->getId()])) {
                        $minutos = intval($mapEstacionWithTime[$estacion->getId()]);
                    }
                    $optionsEstaciones[] = array(
                        "id" => $estacion->getId(),
                        "text" => $estacion->getAliasNombre(),
                        "minutes" => $minutos
                    );
                }
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'optionsEstaciones' => $optionsEstaciones,
        ));
        return $response;
    }

    /**
     * @Route(path="/getDataImagen.jpg", name="ajaxDataImagen")
     * @Secure(roles="ROLE_USER") 
     */
    public function getDataImagenProducto(Request $request)
    {

        $idImagen = $request->query->get('id');
        if (is_null($idImagen)) {
            $idImagen = $request->request->get('id');
        }

        $full = $request->query->get('full');
        if (is_null($full)) {
            $full = $request->request->get('full');
            if (is_null($full)) {
                $full = false;
            }
        }

        $imagen = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Imagen')->find($idImagen);
        if ($imagen === null) {
            return UtilService::returnError($this, "La imagen con identificador " . $idImagen . " no existe");
        }
        if ($full === 'true' || $full === 1 || $full === '1') {
            $full = true;
        } else {
            $full = false;
        }

        $pathFile = $this->getGalleryRootDir() . 'image_' . $imagen->getId() . '_' . ($full === true ? 'max' : 'min') . "." . $imagen->getFormato();
        if (!file_exists($pathFile)) {
            $ifp = fopen($pathFile, "wb");
            if ($full) {
                fwrite($ifp, base64_decode($imagen->getImagenNormal()));
            } else {
                fwrite($ifp, base64_decode($imagen->getImagenPequena()));
            }
            fclose($ifp);
        }

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setCallback(function () use ($pathFile) {
            $bytes = @readfile($pathFile);
            if ($bytes === false || $bytes <= 0)
                throw new NotFoundHttpException();
        });
        $response->setMaxAge(43200);
        return $response;
    }

    protected function getGalleryRootDir()
    {
        return __DIR__ . '/../../../../web/images/gallery/';
    }

    /**
     * @Route(path="/getTarjetasRecientes.json", name="ajaxGetTarjetasRecientes")
     * @Secure(roles="ROLE_USER")  
     */
    public function getTarjetasRecientes(Request $request)
    {

        $optionTarjetas = array();
        $error = "";

        try {

            $tarjetas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tarjeta')->getTarjetasRecientes($this->getUser());
            foreach ($tarjetas as $tarjeta) {
                $talonarios = array();
                foreach ($tarjeta->getListaTalonarios() as $talonario) {
                    $talonarios[] = array(
                        "id" => $talonario->getId(),
                        "min" => $talonario->getInicial(),
                        "max" => $talonario->getFinal()
                    );
                }
                $fechaSalida = $tarjeta->getSalida()->getFecha();
                $optionTarjetas[] = array(
                    "id" => $tarjeta->getId(),
                    "text" =>  $tarjeta->getAlias() . " ( " . $fechaSalida->format('d-m-Y') . " ) ",
                    "talonarios" => $talonarios
                );
            }
        } catch (\ErrorException $exc) {
            $error = "Ha ocurrido un error en el sistema.";
        } catch (\Exception $exc) {
            $error = "Ha ocurrido un error en el sistema.";
        }

        $response = new JsonResponse();
        $response->setData(array(
            'error' => $error,
            'optionTarjetas' => $optionTarjetas,
        ));
        return $response;
    }
}
