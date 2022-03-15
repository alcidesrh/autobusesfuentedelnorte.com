<?php

class ManisfiestoBoletoFullReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "manisfiestoBoletoFull";
    public $alias = "manisfiestoBoletoFull";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        if($this->container !== null){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $now = new DateTime();
            $parameters->put('FECHA_DIA', $now->format('d/m/Y H:i:s'));
            $parameters->put('USUARIO_ID', intval($user->getId()));
            $parameters->put('USUARIO_NOMBRE', $user->getUsername());
            
            $request = $this->container->get("request");
            $command = $request->get('manisfiestoBoletoFull');
            if($command === null){
                $salida = $request->get('id');
                if($salida !== null && trim($salida) !== ""){
                    $parameters->put('SALIDA_ID', intval($salida));
                }
            }else{
                $salida = $command["salida"];
                if($salida !== null && trim($salida) !== ""){
                    $parameters->put('SALIDA_ID', intval($salida));
                }
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
