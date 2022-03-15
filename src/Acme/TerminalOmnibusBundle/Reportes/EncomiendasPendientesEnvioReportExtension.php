<?php

class EncomiendasPendientesEnvioReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "encomiendasPendientesEnvio";
    public $alias = "encomiendasPendientesEnvio";
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
            $estacion = $request->get('id');
            if($estacion !== null && trim($estacion) !== ""){
                $parameters->put('DATA_ESTACION_ID', intval(trim($estacion)));
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
