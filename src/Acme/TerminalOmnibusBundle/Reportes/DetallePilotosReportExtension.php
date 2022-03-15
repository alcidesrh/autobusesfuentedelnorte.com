<?php

class DetallePilotosReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "detallePilotos";
    public $alias = "detallePilotos";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        if($this->container !== null){
            $now = new DateTime();
            $parameters->put('FECHA_DIA', $now->format('d/m/Y H:i:s'));
            $token = $this->container->get('security.context')->getToken();
            if(!is_null($token)){
                $user = $token->getUser();
                $parameters->put('USUARIO_ID',  intval(trim($user->getId())));
                $parameters->put('USUARIO_NOMBRE', $user->getUsername());
            }else{
                $parameters->put('USUARIO_ID',  0);
                $parameters->put('USUARIO_NOMBRE', "FDN");
            }
            
            $request = $this->container->get("request");
            $command = $request->get('detallePilotos');
            
            $empresa = $command["empresa"];
            if($empresa === null || trim($empresa) === ""){
                throw new RuntimeException("m1Debe definir una empresa.");
            }
            $parameters->put('DATA_EMPRESA_ID', intval(trim($empresa)));
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
