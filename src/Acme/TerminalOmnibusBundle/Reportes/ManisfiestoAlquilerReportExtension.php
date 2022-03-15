<?php

class ManisfiestoAlquilerReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "manisfiestoAlquiler";
    public $alias = "manisfiestoAlquiler";
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
            $command = $request->get('manisfiestoAlquiler');
            $alquiler = null;
            if($command === null){
                $alquiler = $request->get('id');
            }else{
                $alquiler = $command["alquiler"];
            }
            
            if($alquiler === null){
                throw new RuntimeException("m1Debe seleccionar un alquiler.");
            }
            
            $alquilerEntity = $this->container->get("doctrine")->getRepository('AcmeTerminalOmnibusBundle:Alquiler')->find($alquiler); 
            if (!$alquilerEntity) {
                throw new RuntimeException("m1El alquiler con id: ".$alquiler." no existe.");
            }
            
            if( $alquilerEntity->getEstado()->getId() !== \Acme\TerminalOmnibusBundle\Entity\EstadoAlquiler::EFECTUADO){
                throw new RuntimeException("m1Solamente se puede generar manifiestos de alquileres iniciados.");
            }
            
            if($alquiler !== null && trim($alquiler) !== ""){
                $parameters->put('ALQUILER_ID', intval($alquiler));
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
