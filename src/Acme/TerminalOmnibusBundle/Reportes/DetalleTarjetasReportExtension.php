<?php

class DetalleTarjetasReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "detalleTarjetas";
    public $alias = "detalleTarjetas";
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
            $command = $request->get('detalleTarjetas');
            
            $fechaStr = $command["fecha"];
            if($fechaStr !== null && trim($fechaStr) !== ""){
                $fechaDateTime = DateTime::createFromFormat('d/m/Y', $fechaStr);
                if($fechaDateTime === false){
                    $fechaDateTime = DateTime::createFromFormat('d-m-Y', $fechaStr);
                }
                if($fechaDateTime === false){
                    throw new RuntimeException("No se pudo conventir la fecha:" . $fechaStr);
                }
                // Formato requerido por sql server: AAAA-MM-DD
                var_dump($fechaDateTime->format("Y-m-d"));
                $parameters->put('DATA_FECHA', $fechaDateTime->format("Y-m-d")); 
            }else{
                throw new RuntimeException("m1Debe definir la fecha.");
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
