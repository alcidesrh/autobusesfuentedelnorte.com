<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\Process\Process;

class TestPHPCommand extends ContainerAwareCommand{
   
    protected function configure()
    {
        $this
            ->setName('SISTEMA:MyTest')
            ->setDefinition(array(
                new InputArgument(
                    'nameArgument',
                    InputArgument::OPTIONAL,
                    'descripcionArgument',
                    'defaultValueArgument'
                ),
                new InputOption(
                     'nameOption',
                     null,
                     InputOption::VALUE_OPTIONAL,
                     'descripcionOption',
                     'defaultValueOption'
                 ),
            ))
            ->setDescription('Comando para probar metodos.')
            ->setHelp(<<<EOT
Comando para probar metodos.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Testing method ----- init');
        $contenedor = $this->getContainer();
        $contenedor->enterScope('request');
        $server = array('REMOTE_ADDR' => "127.0.0.1");
        $request =  new Request();
        $request->initialize(array(), array(), array(), array(), array(), $server);
        $request->setMethod("None");
        $request->setSession(new Session(new MockArraySessionStorage()));
        $contenedor->set('request', $request, 'request');
        $em = $contenedor->get('doctrine')->getManager();
        
//        $items = $contenedor->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getEstacionesWithTiemposByRutaByClaseBus("RUT019", 5);
//        var_dump($items);
//        
        
//        $contenedor->get("acme_backend_tareas_diarias")->checkEstadisticas();
        
//        var_dump(json_decode("") === null);
        
        
//        var_dump(UtilService::encrypt("1234567890", "sistema_1"));
//        var_dump(UtilService::encrypt("1234567890", "sistema_1"));
//        var_dump(UtilService::encrypt("1234567890", "sistema_1"));
//        var_dump(UtilService::encrypt("1234567890", "sistema_1"));
//        var_dump(UtilService::encrypt("1234567890", "sistema_1"));
//        var_dump(UtilService::encrypt("1234567890", "sistema_1"));
        
//        $process = new Process('php app/console SISTEMA:MyTest2');
//        $process->setTimeout(null); //60 segundos
//        $process->start();        
//        while ($process->isRunning()) {
//            $process->checkTimeout();
//            usleep(1000);
//        }
//        $process->stop(0);
//        echo $process->getOutput();
//        
//        
//            
//        var_dump(UtilService::getEANCheckDigit("312966"));
//        
        
//        var_dump("starting...");
//        $process->wait(function ($type, $buffer) {
//            var_dump("waiting...");
//            var_dump($type);
//            var_dump($buffer);
//            if (Process::ERR === $type) {
//                echo 'ERR > '.$buffer;
//            } else {
//                echo 'OUT > '.$buffer;
//            }
//        });
        
        
//        pclose(popen("start php.exe C:\wamp\www\TerminalOmnibus\src\Acme\BackendBundle\Command\TestPHP2Command.php","r"));
//
//        $processes[] = new Process("SISTEMA:MyTest2 > /dev/null", null, null, null, 2);
////        $processes[] = new Process("date > /dev/null", null, null, null, 2);
//
//        $sleep = 0;
//        do {
//            $count = count($processes);
//            for($i = 0; $i < $count; $i++) {
//                if (!$processes[$i]->isStarted()) {
//                    $processes[$i]->start();
//                    continue;
//                }
//
//                try {
//                    $processes[$i]->checkTimeout();
//                } catch (\Exception $e) { 
//                    // Don't stop main thread execution
//                }
//
////                if (!$processes[$i]->isRunning()) { $processes[$i]->restart(); }
//                gc_collect_cycles();
//            }
//            usleep($sleep * 1000000);
//        } while ($count > 0);
//        
        
        
        
//        proc_close(proc_open("SISTEMA:MyTest2", Array ()));

 
//       $contenedor->get("acme_backend_reportar_facturas")->setScheduledJob();
        
//        $resumen = array();
//        $valuesBoletos = $contenedor->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarDetalleBoletosBySalida(11968);
//        if(count($valuesBoletos) !== 0){
//            foreach ($valuesBoletos as $item) {
//                $resumen[$item['nombreEstacionCreacion']] = $item;
//            }
//        }
//        
//        $valuesEncomiendas = $contenedor->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarDetalleEncomiendaBySalida(11968);
//        if(count($valuesEncomiendas) !== 0){
//            foreach ($valuesEncomiendas as $item) {
//                if(isset($resumen[$item['nombreEstacionCreacion']])){
//                    $temp = array_merge($resumen[$item['nombreEstacionCreacion']], $item);
//                    $resumen[$item['nombreEstacionCreacion']] = $temp;
//                }else{
//                    $resumen[$item['nombreEstacionCreacion']] = $item;
//                }
//            }
//        }
//        
//        var_dump($resumen);
        
//        
//        $a = array(
//            "a1" => "a",
//            "a2" => "b"
//        );
//        echo ($a["a"."1"]);

        
//        var_dump(copy("C:/wamp/www/TerminalOmnibus/app/cache/prod/jms_diextra/metadata/.cache.php", "D:/TEST/"));
        
//        $result = $contenedor->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Cliente')->listarClientesPaginandoNativo(5, "j");
//        var_dump($result);
        
        
//        var_dump(UtilService::roundBy(150.00, 1));
//        var_dump(UtilService::roundBy(150.49, 1));
//        var_dump(UtilService::roundBy(150.50, 1));
//        var_dump(UtilService::roundBy(150.51, 1));
//        var_dump(UtilService::roundBy(150.99, 1));
//        var_dump(UtilService::roundBy(151.00, 1));
        
        
//        $a = 3; 
//        $b = 5;
//        var_dump((int)($a / $b + 0.5) * $b);
//        
        
//        $result = $contenedor->get('doctrine')->getRepository('AcmeBackendBundle:User')->findEmailSuperAdmin();
//        var_dump($result);

//        
//        $contenedor->get("acme_scheduler.scheduler")->executeJobs();
//        $em->flush();
        
//        $contenedor->get("acme_backend_reportar_facturas")->setScheduledJob();
//        $em->flush();
////        
//        var_dump(\Acme\BackendBundle\Services\UtilService::isValidIpRequestOfUser(array('10.0.0.254'), '10.0.0.255'));
//        
        
//        $dt = \DateTime::createFromFormat('m/d/Y h:i A', '01/01/2013 10:27 PM');
//        var_dump($dt->format('H:i:s'));
//        var_dump(strpos("xxholass", "n"));
        
//        $key = "g sgsdgsdfgsdfgs dfgs";
//        $data = "30f gsgsdfjg sdkfj ghsdjkf ghsdjfgh sddfsdf56456345kjfgh  sdkjf ghsdjkfgh ";
//        $text = \Acme\BackendBundle\Services\UtilService::generarBitChequeo($data);
//        var_dump($text);
//        var_dump(\Acme\BackendBundle\Services\UtilService::checkBitChequeo($text));
//        $info = \Acme\BackendBundle\Services\UtilService::decrypt($key, $text);
//        var_dump($info);
        
//        $a = array();
//        $a[] = 12;
//        $a[] = 13;  
//        $a[] = 12;
//        var_dump(implode(",", array_unique($a)));
        
//        $impresorasDisponibles = "\\\\12.3.2.1\\as-32";
//        var_dump($impresorasDisponibles);
//        var_dump(substr_count($impresorasDisponibles, "\\"));
//        if(substr_count($impresorasDisponibles, "\\") >= 3){
//            //existe un direccion de ip.
//            $posInit = strpos($impresorasDisponibles, "\\", 3) + 1;
//            $name = substr($impresorasDisponibles, $posInit , 100);
//            var_dump($name);
//         }
        
//        $qrCode = new \Endroid\QrCode\QrCode();
//        $qrCode->setSize(50);
//        $qrCode->setText("hola");
//        $qrCode = $qrCode->get("png");
//        $base64 = base64_encode($qrCode);
//        var_dump($base64);
//        
        
//        
//        
//        $contenedor->get("acme_backend_reservacion")->vencerReservacionesFueraTiempo();
//        $em->flush();
        
//        $map = \Acme\BackendBundle\Services\UtilService::getMapsParametrosQuery("identificador=&fechaEnd=16%2F12%2F2013");
//        var_dump($map);
        
//        
//        $date1 = strtotime('2013-07-03 18:30:00');
//        $date2 = strtotime('2013-07-03 18:45:00');
//        var_dump($date2 - $date1);
        
//        $barcode = $contenedor->get('acme_backend_barcode');
//        var_dump($barcode->barcodeToHtmlAction("mi id"));
            
//          $list = array('1');
//          echo implode(",", $list);
        
//          $resutl = $contenedor->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->getAsientoOcupadosPorNumero(434, array(
//              1,2,3,4, 46, 47, 48, 49, 50, 37
//          ));
//          foreach ($resutl as $item) {
//              var_dump("ID:" . $item->getId() . ", NUMERO:" . $item->getNumero());
//          }
//          var_dump($resutl);
          
//        foreach ($lista as $item) {
//            var_dump($item->getNumero());
//        }
//        $contenedor->enterScope('request');
//        $contenedor->set('request', new Request(), 'request');
//        
         
          $contenedor->get('acme_backend_salida')->procesarSalidaPorItinerarioCiclicoFormaPeriodica();
         
        
                
//         $fecha = new \DateTime();
//         var_dump($fecha);
//         var_dump($fecha->format("l"));
//         
////         $fecha->modify('last ' . DiaSemana::DOMINGO);
////         var_dump($fecha);
////         var_dump($fecha->format("l"));
//         $fecha->modify('previous ' . DiaSemana::SABADO);
//         var_dump($fecha);
//         var_dump($fecha->format("l"));
////        $fecha->modify('next thursday');
//        var_dump($fecha);
//        $fecha->modify('next thursday');
//        var_dump($fecha);
//        $fechaSalida = new \DateTime('31-08-2014 8:30:00');
//        $tarifa = $contenedor->get('acme_backend_tarifa')->getTarifaBoleto(1, 2, 1, 1, 1, $fechaSalida);
//        var_dump($tarifa->getId()); //Volumen
// 
//        
//        $tarifa = $contenedor->get('acme_backend_tarifa')->getTarifaEncomiendaEfectivo(1, 4);
//        if($tarifa !== null){
//            var_dump($tarifa->calcularTarifa()); //Volumen
////            var_dump($tarifa);
//        }else{
//            var_dump("null");
//        }
//        
//        
//                $a = array("id" => "2");
//        $str = "";
//        foreach ($a as $key => $value) {
////            $str .= "<" . $key . "|" . $value . "|>"; 
//        }
//        var_dump($str);
//        
        
//        $date = date("Ymdhis");
//        var_dump($date);
//        var_dump(crypt(date("Ymd")));
//        var_dump(crypt(date("Ymd")));
//        var_dump(crypt(date("his")));
//        var_dump(crypt(date("his")));
//        var_dump(crypt(date("Ymdhis")));
//        var_dump(crypt(date("Ymdhis")));
//        
//        var_dump(uniqid());
//        var_dump(uniqid());
//        var_dump(uniqid("about"));
//        var_dump(uniqid("about"));
//        var_dump(uniqid(rand(), true));
//        var_dump(uniqid(rand(), true));
//        
//        $random_id_length = 10; 
//        $rnd_id = crypt(uniqid(rand(),1)); 
//        $rnd_id = strip_tags(stripslashes($rnd_id)); 
//        $rnd_id = str_replace(".","",$rnd_id); 
//        $rnd_id = strrev(str_replace("/","",$rnd_id)); 
//        $rnd_id = substr($rnd_id,0,$random_id_length); 
//        var_dump($rnd_id);
//        
//        $random_id_length = 10; 
//        $rnd_id = crypt(uniqid(rand(),1)); 
//        $rnd_id = strip_tags(stripslashes($rnd_id)); 
//        $rnd_id = str_replace(".","",$rnd_id); 
//        $rnd_id = strrev(str_replace("/","",$rnd_id)); 
//        $rnd_id = substr($rnd_id,0,$random_id_length); 
//        var_dump($rnd_id);
//        
//        function NewGuid() { 
//            $a1 = rand();
//            var_dump($a1);
//            $a2 = uniqid($a1);
//            var_dump($a2);
//            $a3 = md5($a2);
//            var_dump($a3);
//            
//            $s = strtoupper(md5(uniqid(rand(),true))); 
//            $guidText = 
//                substr($s,0,4) . '-' . 
//                substr($s,4,4) . '-' . 
//                substr($s,8,4);
//            return $guidText;
//        }
//        $Guid = NewGuid();
//         var_dump($Guid);
//        
//        $date = new \DateTime();
//        function generatePin($date){
//            $fecha = date("Ymdhis");
//            $username = "jmmunoz";
//            $random = uniqid(rand(), true);
//            $ping = strtoupper(substr(process(crypt($fecha)),0,4) . "-" . substr(process(crypt($username)),0,4) . "-" . substr(process(crypt($random)),0,4) );  
//            return $ping;
//        }
//        function process($rnd_id){
//            $rnd_id = strip_tags(stripslashes($rnd_id)); 
//            $rnd_id = str_replace(".","",$rnd_id); 
//            $rnd_id = strrev(str_replace("/","",$rnd_id));
//            return $rnd_id;
//        }
//                 
//        
//         var_dump(generatePin($date));
//         var_dump(generatePin($date));
//         var_dump(generatePin($date));
//         var_dump(generatePin($date));
//         var_dump(generatePin($date));
//         var_dump(generatePin($date));
//         
//        $logger = $contenedor->get('logger');
//        $logger->addInfo('Mi primer loge en db');
//       
//        $message = \Swift_Message::newInstance()
//            ->setSubject('Hello Email')
//            ->setFrom('fuentedelnorte.gt@gmail.com')
//            ->setTo('javiermarti84@gmail.com')
//            ->setBody("hola test")
//        ;
//        $contenedor->get('mailer')->send($message);
        
//        $lista = $contenedor->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaFecha')
//                ->getAllByRangoFecha('111111', new \DateTime('01-09-2013'), new \DateTime('31-08-2014'));
//         
//        var_dump($lista);
        
//        if (preg_match("/^[A-Z\_]{1,8}$/", "AA_bb_CC")) {
//            $output->writeln('okok');
//        } else {
//             $output->writeln('fails');
//        }
        
        
//         $pos = strpos("/admin/bus/111111/create", "edit");
//         if($pos){
//              var_dump($pos);
//         }
         
//        $extension = $contenedor->get('twig.extension.rehabilitacion');
//        var_dump($extension->renderImg('IMGP3697'));
        
        //var_dump($input);
        
//        $value = "%lang%";
//        var_dump($value);
        
//        $value = str_ireplace("%", "",  $value);
//        var_dump($value);
        
        
//        $result = '<div><label for="usuario_command_password_first" class="required">Contraseña</label><input type="password" id="usuario_command_password_first" name="usuario_command[password][first]" required="required"    class="password" accesskey="c" tabindex="7" size="25" /></div><div><label for="usuario_command_password_second" class="required">Contraseña</label><input type="password" id="usuario_command_password_second" name="usuario_command[password][second]" required="required"    class="password" accesskey="c" ta';
//        $items = explode("<div>", $result);
//        var_dump($items);
//        $keyAux = "c";
//        $result = array();
//        foreach ($items as $item){          
//           $value = $item;           
//           $key = $this->extraerAccesskey($value);
//            if($key === null){
//                if($keyAux === null)
//                    throw new \RuntimeException('Debe especificar una letra para el accesskey para:' . $value . '.');
//                else
//                    $key = $keyAux;   
//            }else{
//                if($keyAux !== null && StringUtils::equals(strtolower($key), strtolower($keyAux)) === false){
//                    throw new \RuntimeException('El accesskey del componente ('.$key.') no coincide con el parametro ('.$keyAux.').');
//                }
//            }
//            
//           $posLabel = stripos($value, "<label");
//            if( $posLabel !== false)
//            {
//                $contLabelInit = null;
//                $contLabelValue = null;
//                $contLabelEnd = null;
//                if($posLabel != 0)
//                {
//                    $posLabelInit = strpos($value, "<label");
//                    $posLabelEnd = strpos($value, "</label>");
//                    $contLabelInit = substr($value, 0, $posLabelInit); 
//                    $contLabelValue = substr($value, $posLabelInit, $posLabelEnd-$posLabelInit+8);
//                    $contLabelEnd = substr($value, $posLabelEnd+8);
//                    $value = $contLabelValue;
//                }
//                
//                $posInit = strpos($value, ">");
//                $posEnd = strpos($value, "</");
//                $contInit = substr($value, 0, $posInit+1); 
//                $contValue = substr($value, $posInit+1, $posEnd-$posInit-1);
//                $contEnd = substr($value, $posEnd);
//                $pos = stripos($contValue, $key);
//                if($pos !== false){
//                    $keylenght = strlen($key);
//                    $contValueInit = substr($contValue, 0, $pos); 
//                    $contValueValue = substr($contValue, $pos, $keylenght);
//                    $contValueEnd = substr($contValue, $pos+$keylenght);
//                    $contValue = $contValueInit . "<u>" . $contValueValue . "</u>" . $contValueEnd;  
//                }else{
//                    throw new \RuntimeException('El accesskey ('.$key.') no se puede marcar en el texto del componente ('.$value.')');
//                }
//                $value = $contInit . $contValue . $contEnd; 
//                
//                if($posLabel != 0)
//                {
//                    $value = $contLabelInit . $value . $contLabelEnd;
//                }
//            }
//            $result[] = $value;
//        }
//        $result = implode("<div>", $result);
//        var_dump($result);
        
        
        
//        $result1 = substr($result, strpos($result, "accesskey")); 
//        var_dump($result1);
//        $result2 = substr($result1, strpos($result1, '"') + 1); 
//        var_dump($result2);
//        $result3 = substr($result2, 0, strpos($result2, '"')); 
//        var_dump($result3);
//        
//        $posLabelInit = strpos($result, "accesskey=");
//        $posLabelEnd = strpos($result, "</label>");
//        $contLabelInit = substr($result, 0, $posLabelInit); 
//        $contLabelValue = substr($result, $posLabelInit, $posLabelEnd-$posLabelInit+8);
//        $contLabelEnd = substr($result, $posLabelEnd+8);
//        
//        var_dump($contLabelInit);
//        var_dump($contLabelValue);
//        var_dump($contLabelEnd);
//        
//        $posInit = strpos($result, ">");
//        $posEnd = strpos($result, "</");
//        var_dump($posInit);       
//        var_dump($posEnd);
//        $contInit = substr($result, 0, $posInit+1); 
//        $contValue = substr($result, $posInit+1, $posEnd-$posInit-1);
//        $contEnd = substr($result, $posEnd);
//        var_dump($contInit);
//        var_dump($contValue);
//        var_dump($contEnd);
//        
//        $key = "n";
//        $pos = stripos($contValue, $key);
//        if($pos !== false){
//            $keylenght = strlen($key);
//            $contValueInit = substr($contValue, 0, $pos); 
//            $contValueValue = substr($contValue, $pos, $keylenght);  
//            $contValueEnd = substr($contValue, $pos+$keylenght);
//            $contValue = $contValueInit . "<u>" . $contValueValue . "</u>" . $contValueEnd;
//            
//            var_dump($contValueInit);
//            var_dump($contValueValue);
//            var_dump($contValueEnd);
//        }
//        
//        $value = $contInit . $contValue . $contEnd;
//        var_dump($value); 
        
        
//        $count = (int)1;
//        $contValueOverride = str_ireplace($key, "<u>".$key."</u>",  $contValue, $count);
        //$value = $contInit . $contValueOverride . $contEnd;  
        
//        var_dump($value);       
        $output->writeln('Testing method ----- end');
    }
    
//    private function extraerAccesskey($value){
//        $pos = strpos($value, "accesskey");
//        if($pos === false)
//            return null;
//        
//        $value1 = substr($value, strpos($value, "accesskey"));
//        $value2 = substr($value1, strpos($value1, '"') + 1); 
//        return substr($value2, 0, strpos($value2, '"')); 
//    }
}

?>
