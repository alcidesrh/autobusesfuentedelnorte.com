<?php

class TarifasEncomiendasEspecialesReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "tarifasEncomiendasEspeciales";
    public $alias = "tarifasEncomiendasEspeciales";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        if($this->container !== null){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $now = new DateTime();
            $parameters->put('FECHA_DIA', $now->format('d/m/Y H:i:s'));
            $parameters->put('USUARIO_ID',  $user->getId());
            $parameters->put('USUARIO_NOMBRE', $user->getUsername());
            
            $parameters->put('FECHA_LIMITE', $now->format("Y-m-d H:i:s"));
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
