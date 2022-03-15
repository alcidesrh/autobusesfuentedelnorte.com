<?php

namespace Acme\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
*   @Route(path="/admin/ajax")
*/
class MultiActionController extends Controller {

     /**
     * @Route(path="/listaAsientosBySalida", name="listaAsientosBySalida")
     */
    public function listaAsientosBySalidaAction() {

        try {
            $html = '';
            $idSalida = $this->get('request')->query->get('data');
            if($idSalida !== null && trim($idSalida) !== ""){
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus');
                $asientos = $repository->getAsientosBySalidaId($idSalida);
                foreach($asientos as $asiento)
                {
//                    $label  = "Nro:" . $asiento->getNumero();
//                    if($asiento->getNivel2() === false){
//                        $label .= ", Nivel:1";
//                    }else{
//                        $label .= ", Nivel:2";
//                    }
//                    $label .= ", Clase:" . $asiento->getClase()->getNombre();
                    $html = $html . sprintf("<option value=\"%d\">%s</option>",$asiento->getId(), $asiento->__toString());
                }
            }
            return new Response($html);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return new Response("error...");
        }
    }
    
    /**
     * @Route(path="/listarSalidas", name="listarSalidas")
     */
    public function listaSalidasAction() {

        try {
            $html = '<option value=""></option>';
            $day = $this->get('request')->request->get('day');
            $month = $this->get('request')->request->get('month');
            $year = $this->get('request')->request->get('year');
            $fecha = null;
            if($day !== null && trim($day) !== "" && $month !== null && trim($month) !== "" && $year !== null && trim($year) !== ""){
                $fecha = $day . "-" . $month . "-" . $year; 
            }
            $estacionOrigen = $this->get('request')->request->get('estacionOrigen');
            $estacionDestino= $this->get('request')->request->get('estacionDestino');
            if($fecha !== null && trim($fecha) !== "" && $estacionOrigen !== null && trim($estacionOrigen) !== "" && 
                    $estacionDestino !== null && trim($estacionDestino) !== ""){
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:Salida');
                $salidas = $repository->getSalidas($fecha, $estacionOrigen, $estacionDestino);
                foreach($salidas as $salida)
                {
                    $html = $html . sprintf("<option value=\"%d\">%s</option>",$salida->getId(), $salida->__toString());
                }
            }
            return new Response($html);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return new Response("error...");
        }
    }
    
    /**
     * @Route(path="/listaAsientosDisponiblesBySalida", name="listaAsientosDisponiblesBySalida")
     */
    public function listaAsientosDisponiblesBySalidaAction() {
        try {
            $html = '<option value=""></option>';
            $salida = $this->get('request')->request->get('salida');
            $claseAsiento = $this->get('request')->request->get('claseAsiento');
            $idCortesia = $this->get('request')->request->get('idCortesia');
           
            if($salida !== null && trim($salida) !== "" && $claseAsiento !== null && trim($claseAsiento) !== ""){
                $asientoActual = null;
                if($idCortesia !== null && trim($idCortesia) !== ""){
                    $cortesia = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AutorizacionCortesia')->find($idCortesia);
                    $asientoActual = $cortesia->getRestriccionAsientoBus();
                }
                $repository = $this->get('doctrine')->getManager()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus');
                $asientos = $repository->getAsientosDisponiblesBySalidaId($salida, $claseAsiento, $asientoActual);
                foreach($asientos as $asiento)
                {
                    $label  = "Nro:" . $asiento->getNumero();
                    if($asiento->getNivel2() === false){
                        $label .= ", Nivel:1";
                    }else{
                        $label .= ", Nivel:2";
                    }
                    $label .= ", Clase:" . $asiento->getClase()->getNombre();
                    $html = $html . sprintf("<option value=\"%d\">%s</option>",$asiento->getId(), $label);
                }
            }
            return new Response($html);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return new Response("error...");
        }
        
    }
    
    
    
    
    
//    /**
//     * @Route(path="/listEmpresas.{_format}", name="listEmpresas", defaults={"_format"="html"}, requirements={"_format"="html"})
//     */
//    public function listEmpresasAction() {
//       $html = "";
//        $empresas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findAll();
//        foreach ($empresas as $empresa) {
//            $html .= "<div id='empresa-".$empresa->getId()."' class='empresa'>"                  
//                  . "<div class='empresa-color' data-color='" . $empresa->getColor() . "' data-id='". strval($empresa->getId()) ."'>"
//                  . "<input type='checkbox' class='empresa-seleccionado' data-id='" . strval($empresa->getId()) . "'></input>"
//                  . "</div>"
//                  . "<label class='empresa-nombre'>" . $empresa->getNombre() . "</label>"  
//                  . "</div>";
//        }
//        
//        return new Response($html);
//    }
//    
//    /**
//     * @Route(path="/listarCalendarioFacturaxFecha", name="listarCalendarioFacturaxFecha")
//     */
//    public function listarCalendarioFacturaxFechaAction() {
////        var_dump("listarCalendarioFacturaxFechaAction");
//        $listaJson = array();
//        $request = $this->getRequest();
//        if($request->request->get('id') !== null && $request->request->get('fechaInicial') !== null && $request->request->get('fechaFinal') !== null){
//            
//            $rutaId = $request->request->get('id');
//            $fechaInicial = new \DateTime($request->request->get('fechaInicial'));
//            $fechaFinal = new \DateTime($request->request->get('fechaFinal'));
//            
//            $lista = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaFecha')->getAllByRangoFecha($rutaId, $fechaInicial, $fechaFinal);
//            foreach ($lista as $elemento) {
//                $item = new \stdClass();
//                $item->id = $elemento->getId();
//                $item->fecha = trim($elemento->getFecha()->format('d-m-Y'));
//                $item->empresa = $elemento->getEmpresa()->getId();
//                $listaJson[trim($elemento->getFecha()->format('d-m-Y'))] =  $item;
//            }
//            
////            $date = new \DateTime();
////            $item = new \stdClass();
////            $item->id = 5;
////            $item->fecha = trim($date->format('d-m-Y'));
////            $item->empresa = 1;
////            $listaJson[trim($date->format('d-m-Y'))] =  $item;
//        }
//        
//        $response = new JsonResponse();
//        $response->setData(array(
////            'fechaInit' => $fechaInicial->format('d-m-Y '),
////            'fechaEnd' => $fechaFinal->format('d-m-Y '),
//            'items' => $listaJson
//        ));
//        return $response;
//    }
    
}

?>
