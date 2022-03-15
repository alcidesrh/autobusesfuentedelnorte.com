<?php

class TestReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "test";
    public $alias = "test";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        $parameters->put('TITULO', "Listado de buses de ejemplo");
        if($this->container !== null){
            $request = $this->container->get("request");
            $codigo = $request->query->get('codigo');
            $parameters->put('CODIGO', $codigo);
        }
//        $parameters->put('REPORT_LOCALE', new Java('java.util.Locale','es', 'VE'));
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
