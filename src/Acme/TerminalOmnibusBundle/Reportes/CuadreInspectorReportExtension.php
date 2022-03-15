<?php

class CuadreInspectorReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "cuadreInspector";
    public $alias = "cuadreInspector";
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
            $command = $request->get('cuadreInspector');
            
            $usuario = $command["usuario"];
            if($usuario === null || trim($usuario) === ""){
                throw new RuntimeException("m1Debe seleccionar un inspector");
            }
            $parameters->put('DATA_INSPECTOR_ID', $usuario);
            
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
