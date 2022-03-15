<?php

class ManisfiestoEncomiendaFullReportExtension extends \ReportExtension{
    
    public $container = null;
    public $reportFileName = "manisfiestoEncomiendaFull";
    public $alias = "manisfiestoEncomiendaFull";
    public $enabled = true;
    public function getParam(){
        $parameters = new java ('java.util.HashMap');
        if($this->container !== null){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $now = new DateTime();
            $parameters->put('FECHA_DIA', $now->format('d/m/Y H:i:s'));
            $parameters->put('USUARIO_ID',  intval($user->getId()));
            $parameters->put('USUARIO_NOMBRE', $user->getUsername());
            
            $request = $this->container->get("request");
            $command = $request->get('manisfiestoEncomiendaFull');
            if($command === null){
                $salida = $request->get('id');
                if($salida === null || trim($salida) === ""){
                    throw new RuntimeException("m1Debe seleccionar una salida.");
                }
                $parameters->put('SALIDA_ID', intval(trim($salida)));
                
                $estacionOrigen = $user->getEstacion();
                if($estacionOrigen !== null){ 
                    $parameters->put('ESTACION_ORIGEN_ID', intval($estacionOrigen->getId()));
                }
            }else{
                $salida = $command["salida"];
                if($salida === null || trim($salida) === ""){
                    throw new RuntimeException("m1Debe seleccionar una salida.");
                }
                $parameters->put('SALIDA_ID', intval(trim($salida)));
                
                $estacionOrigen = $command["estacionOrigen"];
                if($estacionOrigen !== null && trim($estacionOrigen) !== ""){ 
                    $parameters->put('ESTACION_ORIGEN_ID', intval($estacionOrigen));
                }
                $estacionDestino = $command["estacionDestino"];
                if($estacionDestino !== null && trim($estacionDestino) !== ""){ 
                    $parameters->put('ESTACION_DESTINO_ID', intval($estacionDestino));
                }
            }
            
//            $key = $this->container->getParameter("encrypt_password");
//            if(!$key){
//                throw new \RuntimeException("No se pudo obtener la clave de encriptación.");
//            }
//            
//            $salidaEntity = $this->container->get("doctrine")->getRepository('AcmeTerminalOmnibusBundle:Salida')->find($salida); 
//            if (!$salidaEntity) {
//                throw new RuntimeException("m1La salida con id: ".$salida." no existe.");
//            }
//            
//            $idSalida = $salida;
//            
//            $piloto = "No definido";
//            if($salidaEntity->getPiloto() !== null){
//                $piloto = $salidaEntity->getPiloto()->__toString();
//            }  
//            $bus = "No definido";
//            if($salidaEntity->getBus() !== null){
//                $bus = $salidaEntity->getBus()->__toString();
//            }
//     
//            $text = "MANISFIESTO_ENCOMIENDA|FA:" . $now->format('d/m/Y H:i:s'). "|S:" + trim($idSalida) + "|FS:" + $salidaEntity->getFecha()->format('d/m/Y H:i:s') + "|P:" + $piloto + "|B:" + $bus+ "|U:" + $user->__toString();
//            $text = Acme\BackendBundle\Services\UtilService::generarBitChequeo($text);
//            $text = Acme\BackendBundle\Services\UtilService::encrypt($key, $text);  
//            $parameters->put('QR_DATA', $text);
//            $parameters->put('QR_SPECIAL', "");
//            $parameters->put('QR_SIZE', "Unit of Measure=2&Left Margin=1&Right Margin=1&Top Margin=1&Bottom Margin=1");
//           
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
