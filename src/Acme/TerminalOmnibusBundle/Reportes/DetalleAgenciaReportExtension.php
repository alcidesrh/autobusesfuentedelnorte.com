<?php

class DetalleAgenciaReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "detalleAgencia";
    public $alias = "detalleAgencia";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        if($this->container !== null){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $now = new DateTime();
            $parameters->put('FECHA_DIA', $now->format('d/m/Y H:i:s'));
            $parameters->put('USUARIO_ID',  intval(trim($user->getId())));
            $parameters->put('USUARIO_NOMBRE', $user->getUsername());
            
            $request = $this->container->get("request");
            $command = $request->get('detalleAgencia');
               
            $estacion = $command["estacion"];
            if($estacion !== null && trim($estacion) !== ""){
               $parameters->put('DATA_ESTACION_ID', intval(trim($estacion)));
            }
            
            $empresa = $command["empresa"];
            if($empresa !== null && trim($empresa) !== ""){
                $parameters->put('DATA_EMPRESA_ID', intval(trim($empresa)));
            }
            
            $referenciaExterna = $command["referenciaExterna"];
            if($referenciaExterna !== null && trim($referenciaExterna) !== ""){
                $parameters->put('DATA_REFERENCIA_EXTERNA', $referenciaExterna);
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
                        throw new RuntimeException("Rango de fecha invalido");
                    }                    
                }else{
                    throw new RuntimeException("Rango de fecha invalido");
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
