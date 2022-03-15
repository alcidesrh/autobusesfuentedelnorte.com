<?php

class VentaBoletoPrepagadoOtrasEstacionesPorBusesReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "ventaBoletoPrepagadoOtrasEstacionesPorBuses";
    public $alias = "ventaBoletoPrepagadoOtrasEstacionesPorBuses";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        if($this->container !== null){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $now = new DateTime();
            $parameters->put('FECHA_DIA', $now->format('d/m/Y H:i:s'));
            $parameters->put('USUARIO_ID',  $user->getId());
            $parameters->put('USUARIO_NOMBRE', $user->getUsername());
            
            $request = $this->container->get("request");
            $command = $request->get('ventaBoletoPrepagadoOtrasEstacionesPorBuses');
            
            $empresa = $command["empresa"];
            if($empresa === null || trim($empresa) === ""){
                throw new RuntimeException("m1Debe definir una empresa.");
            }
            $parameters->put('EMPRESA_ID', trim($empresa));
            
            $estacion = $command["estacion"];
            if($estacion !== null && trim($estacion) !== ""){ 
                $parameters->put('ESTACION_ID', $estacion);
            }
             
            $bus = $command["bus"];
            if($bus !== null && trim($bus) !== ""){ 
                $parameters->put('BUS_CODIGO', $bus);
            }
            
            $moneda = $command["moneda"];
            if($moneda !== null && trim($moneda) !== ""){
                $parameters->put('MONEDA_ID', $moneda);
            }
            $rangoFecha = $command["rangoFecha"];
            if($rangoFecha !== null && trim($rangoFecha) !== ""){
                $rangoFechaArray = explode("-", $rangoFecha);
                if(count($rangoFechaArray) === 2){
                    $fechaInicialStr = trim($rangoFechaArray[0]);
                    $fechaFinalStr = trim($rangoFechaArray[1]);
                    if($fechaInicialStr !== "" && $fechaFinalStr !== ""){
                        $fechaInicialDateTime = DateTime::createFromFormat('d/m/Y', $fechaInicialStr);
                        if($fechaInicialDateTime === false){
                            $fechaInicialDateTime = DateTime::createFromFormat('d-m-Y', $fechaInicialStr);
                        }
                        if($fechaInicialDateTime === false){
                            throw new RuntimeException("No se pudo conventir la fecha:" . $fechaInicialStr);
                        }
                        // Formato requerido por sql server: AAAA-MM-DD
                        $parameters->put('FECHA_INICIAL', $fechaInicialDateTime->format("Y-m-d")); 
                        $fechaFinalDateTime = DateTime::createFromFormat('d/m/Y', $fechaFinalStr);
                        if($fechaFinalDateTime === false){
                            $fechaFinalDateTime = DateTime::createFromFormat('d-m-Y', $fechaFinalStr);
                        }
                        if($fechaFinalDateTime === false){
                            throw new RuntimeException("No se pudo conventir la fecha:" . $fechaFinalStr);
                        }
                        // Formato requerido por sql server: AAAA-MM-DD
                        $parameters->put('FECHA_FINAL', $fechaFinalDateTime->format("Y-m-d")); 
                    }else{
                        throw new RuntimeException("m1Debe definir un rango de fecha válido.");
                    }                    
                }else{
                    throw new RuntimeException("m1Debe definir un rango de fecha válido.");
                }
            }else{
                throw new RuntimeException("m1Debe definir un rango de fecha.");
            }
        }
        return $parameters;
    }
    public function getSqlSentence(){}
    public function getHtmlOptions(){}
    public function beforeRun(){}
    public function afterRun($outfilename){}
    public function getConexion(){}
    public function setContainer($container){
        $this->container = $container;
    }
}
