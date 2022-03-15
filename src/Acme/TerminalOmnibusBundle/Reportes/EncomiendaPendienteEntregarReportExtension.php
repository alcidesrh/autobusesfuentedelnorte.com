<?php

class EncomiendaPendienteEntregarReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "encomiendaPendienteEntregar";
    public $alias = "encomiendaPendienteEntregar";
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
            $command = $request->get('encomiendaPendienteEntregar');
            
            $empresa = $command["empresa"];
            if($empresa === null || trim($empresa) === ""){
                throw new RuntimeException("m1Debe definir una empresa.");
            }
            $parameters->put('DATA_EMPRESA_ID', intval(trim($empresa)));
            
            $estacion = $command["estacion"];
            if($estacion !== null && trim($estacion) !== ""){
                $parameters->put('DATA_ESTACION_ID', intval(trim($estacion)));
            }
            
            if(isset($command["mostrarSoloFacturado"]) && $command["mostrarSoloFacturado"] === "1"){
                $parameters->put('DATA_MOSTRAR_SOLO_FACTURADO', strval(true));
            }else{
                $parameters->put('DATA_MOSTRAR_SOLO_FACTURADO', strval(false));
            }
            
            if(isset($command["mostrarSoloPorCobrar"]) && $command["mostrarSoloPorCobrar"] === "1"){
                $parameters->put('DATA_MOSTRAR_SOLO_POR_COBRAR', strval(true));
            }else{
                $parameters->put('DATA_MOSTRAR_SOLO_POR_COBRAR', strval(false));
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
