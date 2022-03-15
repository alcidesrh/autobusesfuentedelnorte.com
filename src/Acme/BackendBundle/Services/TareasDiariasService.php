<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\HttpFoundation\Request;

class TareasDiariasService implements ScheduledServiceInterface{
    
    protected $container;
    protected $doctrine;
    protected $utilService;
    protected $logger;
    protected $options;
    protected $job;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->utilService = $this->container->get('acme_backend_util');
        $this->logger = $this->container->get('logger');
        $this->options = array();
        $this->job = null;
    }   
    
    private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }

    public function checkSeriesFacturas($options = null){
        $this->logger->warn("checkSeriesFacturas - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaLimiteSistema = $this->getCurrentFecha();
        $fechaLimiteSistema->modify("+45 day");
        $this->logger->warn("Buscando series de factura que esten por expirar: " . $fechaLimiteSistema->format('d-m-Y H:i:s'));
        $facturas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Factura')->listarSeriesFacturasExpiradas($fechaLimiteSistema);
        if(count($facturas) === 0){
            $this->logger->warn("No existen facturas próximas a expirar.");
        }else{
            $em = $this->doctrine->getManager();
            $mapEmpresas = array();
            $mapEmpresasFacturas = array();
            foreach ($facturas as $factura) {
                $mapEmpresas[$factura->getEmpresa()->getId()] = $factura->getEmpresa();
                $mapEmpresasFacturas[$factura->getEmpresa()->getId()][] = $factura;
            }
            
            foreach ($mapEmpresasFacturas as $idEmpresa => $facturas) {
                $empresa = $mapEmpresas[$idEmpresa];
                $correos = $empresa->getCorreos();
                $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAdministrativosByEmpresa($idEmpresa);
                foreach ($usuarios as $usuario) {
                    $email = $usuario->getEmail();
                    if($email !== null && trim($email) !== ""){
                        $correos[] = trim($email);
                    }
                }
                $series = array();
                foreach ($facturas as $factura) {
                    $series[] = $factura->getSerieResolucionFactura();
                }
                $correos = array_unique($correos);
                if($correos !== null && count($correos) !== 0){
                   $this->logger->warn("Enviando correo notificando vencimiento de series de facturas: " . implode(", ", $series) . ".");
                   $subject = "VCF_" . $now . ". Notificación de vencimiento de las series de facturas: " . implode(", ", $series) . "."; 
                   UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_serie_factura.html.twig', array(
                          'titulo' => 'Las siguientes series de facturas venceran próximamente.',
                          'facturas' => $facturas
                   )));
                }
            }
        }
        
        $cantidad = 200;
        $this->logger->warn("Buscando series de factura que esten por agotarse.");
        $facturas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Factura')->listarSeriesFacturasPorTerminar($cantidad);
        if(count($facturas) === 0){
            $this->logger->warn("No existen facturas próximas por agotarse.");
        }else{
            $em = $this->doctrine->getManager();
            $mapEmpresas = array();
            $mapEmpresasFacturas = array();
            foreach ($facturas as $factura) {
                $mapEmpresas[$factura->getEmpresa()->getId()] = $factura->getEmpresa();
                $mapEmpresasFacturas[$factura->getEmpresa()->getId()][] = $factura;
            }
            
            foreach ($mapEmpresasFacturas as $idEmpresa => $facturas) {
                $empresa = $mapEmpresas[$idEmpresa];
                $correos = $empresa->getCorreos();
                $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAdministrativosByEmpresa($idEmpresa);
                foreach ($usuarios as $usuario) {
                    $email = $usuario->getEmail();
                    if($email !== null && trim($email) !== ""){
                        $correos[] = trim($email);
                    }
                }
                $series = array();
                foreach ($facturas as $factura) {
                    $series[] = $factura->getSerieResolucionFactura();
                }
                $correos = array_unique($correos);
                if($correos !== null && count($correos) !== 0){
                   $this->logger->warn("Enviando correo notificando agotamiento de series de facturas: " . implode(", ", $series) . ".");
                   $subject = "ACF_" . $now . ". Notificación de agotamiento de las series de facturas: " . implode(", ", $series) . "."; 
                   UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_serie_factura.html.twig', array(
                          'titulo' => 'Las siguientes series de facturas se agotaran próximamente.',
                          'facturas' => $facturas
                   )));
                }
            }
        }
        
       $this->logger->warn("checkSeriesFacturas - end");
    }
    
    public function sendTotalesFacturados($options = null){
        $this->logger->warn("sendTotalesFacturados - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("-1 day");
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
                $this->logger->warn("Buscando ventas totales del " . $fechaDia->format('d-m-Y') . " para la empresa " . $empresa->getAlias());
                $estaciones = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getEstacionesEmitieronOperaciones($fechaDia, $empresa);
                $resumenByEstacion = array();
                $valuesBoletos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarTotalesBoletos($fechaDia, $empresa, $estaciones);
                foreach ($valuesBoletos as $item) {
                    $id = $item['idEstacionCreacion'];
                    $resumenByEstacion[$id] = $item;
                }
                $valuesEncomiendas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarTotalesEncomienda($fechaDia, $empresa, $estaciones);
                foreach ($valuesEncomiendas as $item) {
                    $id = $item['idEstacionCreacion'];
                    if(array_key_exists($id, $resumenByEstacion)){
                        $temp = array_merge($resumenByEstacion[$id], $item);
                        $resumenByEstacion[$id] = $temp;
                    }else{
                        $resumenByEstacion[$id] = $item;
                    }
                }
            
                $correos = $empresa->getCorreos();
//                $correos = array("javiermarti84@gmail.com");
                if($correos !== null && count($correos) !== 0){
                    $now = new \DateTime();
                    $now = $now->format('Y-m-d H:i:s');
                    $this->logger->warn("Enviando correo notificando totales del " . $fechaDia->format('d-m-Y') . " para la empresa " . $empresa->getAlias() . ".");
                    $subject = "NVT_" . $now . ". Notificación de ventas totales del " . $fechaDia->format('d-m-Y') . " en la empresa: " . $empresa->getAlias() . "."; 
                    UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_parciales.html.twig', array(
                        'title' => 'Totales',
                        'empresa' => $empresa,
                        'fechaDia' => $fechaDia,
                        'resumenByEstacion' => $resumenByEstacion
                    )));
                }            
        }
        $this->logger->warn("sendTotalesFacturados - end");
    }
    
    public function sendTotalesCortesias($options = null){
        $this->logger->warn("sendTotalesCortesias - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("-1 day");
        
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            $this->logger->warn("Buscando total de cortesias el " . $fechaDia->format('d-m-Y') . " para la empresa " . $empresa->getAlias());
            $items = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarCortesiaPorEstacion($fechaDia, $empresa);
            $correos = $empresa->getCorreos();
            if($correos !== null && count($correos) !== 0){
                $this->logger->warn("Enviando correo notificando de cortesias para el " . $fechaDia->format('d-m-Y') . " en la empresa " . $empresa->getAlias() . ".");
                $subject = "NTC_" .$now . ". Notificación de cortesías del día: " . $fechaDia->format('d-m-Y') . " en la empresa: " . $empresa->getAlias() . "."; 
                UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_cortesia.html.twig', array(
                    'empresa' => $empresa,
                    'fechaDia' => $fechaDia,
                    'items' => $items
                )));
            }            
        }
        $this->logger->warn("sendTotalesCortesias - end");
    }
    
    public function checkFechaVencimientoTarjetaOperaciones($options = null){
        $this->logger->warn("checkFechaVencimientoTarjetaOperaciones - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("+30 day");
        
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            $this->logger->warn("Buscando buses proximos a vencer su tarjeta de operaciones para la fecha: " . $fechaDia->format('d-m-Y') . ", en la empresa: " . $empresa->__toString());
            $buses = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Bus')->listarBusesVencimientoTarjetaOperaciones($fechaDia, $empresa);
            if($buses !== null && count($buses) !== 0){
                $correos = $empresa->getCorreos();
                $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAdministrativosByEmpresa($empresa);
                foreach ($usuarios as $usuario) {
                    $email = $usuario->getEmail();
                    if($email !== null && trim($email) !== ""){
                        $correos[] = trim($email);
                    }
                }

                if($correos !== null && count($correos) !== 0){
                    $correos = array_unique($correos);
                    $this->logger->warn("Enviando correo notificando buses proximos a vencer su tarjeta de operaciones el " . $fechaDia->format('d-m-Y') . " en la empresa " . $empresa->__toString() . ".");
                    $subject = "VTO_" .$now . ". Notificación de próximo vencimiento de tarjeta de operaciones de buses de la empresa " . $empresa->__toString() . "."; 
                    UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_buses.html.twig', array(
                        'empresa' => $empresa,
                        'fechaDia' => $fechaDia,
                        'buses' => $buses
                    )));
                }  
            }
        }
        $this->logger->warn("checkFechaVencimientoTarjetaOperaciones - end");
    }
    
    public function checkFechaVencimientoLicenciaPilotos($options = null){
        $this->logger->warn("checkFechaVencimientoLicenciaPilotos - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("+30 day");
        
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            $this->logger->warn("Buscando pilotos proximos a vencer su licencia: " . $fechaDia->format('d-m-Y') . ", en la empresa: " . $empresa->__toString());
            $pilotos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Piloto')->listarPilotosVencimientoLicencia($fechaDia, $empresa);
            if($pilotos !== null && count($pilotos) !== 0){
                $correos = $empresa->getCorreos();
                $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAdministrativosByEmpresa($empresa);
                foreach ($usuarios as $usuario) {
                    $email = $usuario->getEmail();
                    if($email !== null && trim($email) !== ""){
                        $correos[] = trim($email);
                    }
                }

                if($correos !== null && count($correos) !== 0){
                    $correos = array_unique($correos);
                    $this->logger->warn("Enviando correo notificando pilotos proximos a vencer su licencia el dia " . $fechaDia->format('d-m-Y') . " en la empresa " . $empresa->__toString() . ".");
                    $subject = "VLP_" .$now . ". Notificación de próximo vencimiento de licencias de pilotos de la empresa " . $empresa->__toString() . "."; 
                    UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_pilotos.html.twig', array(
                        'empresa' => $empresa,
                        'fechaDia' => $fechaDia,
                        'pilotos' => $pilotos
                    )));
                }  
            }
        }
        $this->logger->warn("checkFechaVencimientoLicenciaPilotos - end");
    }
    
    public function sendTotalesTarjetas($options = null){
        $this->logger->warn("sendTotalesTarjetas - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("-1 day");
        
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            $this->logger->warn("Buscando boletos facturados con tarjetas " . $fechaDia->format('d-m-Y') . " para la empresa " . $empresa->__toString());
            $boletos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Boleto')->listarBoletosFacturadosConTarjeta($fechaDia, $empresa);
            
            $mapEstaciones = array();
            $mapEstacionesBoletos = array();
            
            foreach ($boletos as $boleto) {
                 $mapEstaciones[$boleto->getEstacionCreacion()->getId()] = $boleto->getEstacionCreacion();
                 $mapEstacionesBoletos[$boleto->getEstacionCreacion()->getId()][] = $boleto;
            }
            
            $correos = $empresa->getCorreos();
            $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAdministrativosByEmpresa($empresa);
            foreach ($usuarios as $usuario) {
                $email = $usuario->getEmail();
                if($email !== null && trim($email) !== ""){
                    $correos[] = trim($email);
                }
            }
            
//            $idEstaciones = array_keys($mapEstaciones);
//            $supervisores = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserSupervisoresByEmpresaAndEstaciones($empresa, $idEstaciones);
//            foreach ($supervisores as $usuario) {
//                $email = $usuario->getEmail();
//                if($email !== null && trim($email) !== ""){
//                    $correos[] = trim($email);
//                }
//            }
            
            if($correos !== null && count($correos) !== 0){
                $this->logger->warn("Enviando correo notificando boletos facturados con tarjetas para el " . $fechaDia->format('d-m-Y') . " en la empresa " . $empresa->__toString() . ".");
                $subject = "NTT_" .$now . ". Notificación de los boletos facturados con tarjetas del día: " . $fechaDia->format('d-m-Y') . " en la empresa: " . $empresa->__toString() . "."; 
                UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_tarjeta.html.twig', array(
                    'empresa' => $empresa,
                    'fechaDia' => $fechaDia,
                    'mapEstaciones' => $mapEstaciones,
                    'mapEstacionesBoletos' => $mapEstacionesBoletos
                )));
            }            
        }
        $this->logger->warn("sendTotalesTarjetas - end");
    }
    
    public function checkExpiracionCredencialesPorUsuario($options = null){
        $this->logger->warn("checkExpiracionCredencialesPorUsuario - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }

        $fechaFutura = $this->getCurrentFecha();
        $fechaFutura->modify("+20 day");
        
        $this->logger->warn("Buscando usuarios que sus credenciales expiran proximamente");
        $usuariosProximosExpirar = $this->doctrine->getRepository('AcmeBackendBundle:User')->findExpiredCredentialsUser($fechaFutura);
        if(count($usuariosProximosExpirar) !== 0){
//            $superAdmin = $this->doctrine->getRepository('AcmeBackendBundle:User')->findEmailSuperAdmin();
            $superAdmin = array();
            foreach ($usuariosProximosExpirar as $usuario) {
                $correos = array();
                $email = $usuario->getEmail();
                if($email !== null && trim($email) !== ""){
                    $correos[] = trim($email);
                }
                $result = array_merge($correos, $superAdmin);
                $result = array_unique($result);
                $this->logger->warn("Enviando correo notificando proxima expiracion de contraseña.");
                $now = new \DateTime();
                $now = $now->format('Y-m-d H:i:s');
                $subject = "NEC_" .$now . ". Notificación de expiración de contraseña."; 
                UtilService::sendEmail($this->container, $subject, $result, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_expiracion_contrasena.html.twig', array(
                    'usuario' => $usuario
                )));
            }   
        }
        $this->logger->warn("checkExpiracionCredencialesPorUsuario - end");
    }
    
    public function checkCajasNoCerradas($options = null){
        $this->logger->warn("checkCajasNoCerradas - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("-1 day");
        
        $this->logger->warn("Buscando cajas no cerradas hasta el " . $fechaDia->format('d-m-Y'));
        $cajas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Caja')->listarCajasPendientes($fechaDia);
        
        if(count($cajas) !== 0){
            $mapEstaciones = array();
            $mapEstacionesCajas = array();
            $mapEstacionesCorreos = array();
            foreach ($cajas as $caja) {
                $mapEstaciones[$caja->getEstacion()->getId()] = $caja->getEstacion();
                $mapEstacionesCajas[$caja->getEstacion()->getId()][] = $caja;
                $mapEstacionesCorreos[$caja->getEstacion()->getId()][] = $caja->getUsuario()->getEmail();
            }
            
            //SUPER ADMINS
            $correos = $this->doctrine->getRepository('AcmeBackendBundle:User')->findEmailSuperAdmin();
            
            //ADMINISTRATIVOS
//            $administrativos = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAllAdministrativos();
//            foreach ($administrativos as $usuario) {
//                $email = $usuario->getEmail();
//                if($email !== null && trim($email) !== ""){
//                    $correos[] = trim($email);
//                }
//            }
            
            foreach ($mapEstaciones as $estacion) {
                //SUPERVISORES
                $supervisores = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserSupervisoresByEstacion($estacion);
                foreach ($supervisores as $usuario) {
                    $email = $usuario->getEmail();
                    if($email !== null && trim($email) !== ""){
                        $result[] = trim($email);
                    }
                }
                
                //USUARIO DE LA CAJA
                $result = array_merge($correos, $mapEstacionesCorreos[$estacion->getId()]);
                
                $result = array_unique($result);
                $this->logger->warn("Enviando correo notificando cajas no cerradas hasta el " . $fechaDia->format('d-m-Y') . ", en la estación " . $estacion->__toString() . ".");
                $subject = "ALERTA_" .$now . ". Notificación de cajas no cerradas hasta el día: " . $fechaDia->format('d-m-Y') . " en la estación: " . $estacion->__toString() . "."; 
                UtilService::sendEmail($this->container, $subject, $result, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_cajas_pendientes.html.twig', array(
                    'fechaDia' => $fechaDia,
                    'estacion' => $estacion,
                    'cajas' => $mapEstacionesCajas[$estacion->getId()]
                )));
            }
        }
        
        $this->logger->warn("checkCajasNoCerradas - end");
    }
    
    public function checkBoletosSalidasEspeciales($options = null){
        $this->logger->warn("checkBoletosSalidasEspeciales - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaDia = $this->getCurrentFecha();
        $fechaDia->modify("-1 day");
        
        $this->logger->warn("Buscando boletos emitidos en salidas extras en la fecha " . $fechaDia->format('d-m-Y'));
        $salidas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Salida')->listarSalidasEspeciales($fechaDia);
        
        if(count($salidas) !== 0){
            $mapEmpresa = array();
            $mapEmpresaSalidas = array();
            $mapSalidaBoletos = array();
            foreach ($salidas as $salida) {
                $mapEmpresa[$salida->getEmpresa()->getId()] = $salida->getEmpresa();
                $mapEmpresaSalidas[$salida->getEmpresa()->getId()][] = $salida;
                $boletos = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Boleto')->getBoletosPorSalida($salida, true);
                $mapSalidaBoletos[$salida->getId()] = $boletos;
            }
            
            foreach ($mapEmpresa as $empresa) {
                
                //SUPER ADMINS
                $correos = $this->doctrine->getRepository('AcmeBackendBundle:User')->findEmailSuperAdmin();
            
                //ADMINISTRATIVOS
                $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAdministrativosByEmpresa($empresa);
                foreach ($usuarios as $usuario) {
                    $email = $usuario->getEmail();
                    if($email !== null && trim($email) !== ""){
                            $correos[] = trim($email);
                    }
                }
                
                $result = array_unique($correos);
                $this->logger->warn("Enviando correo notificando salidas especiales en la empresa " . $empresa->getAlias() . ".");
                $subject = "ALERTA_" .$now . ". Notificación de salidas extras del día: " . $fechaDia->format('d-m-Y') . " en la empresa: " . $empresa->getAlias() . "."; 
                UtilService::sendEmail($this->container, $subject, $result, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_salidas_extras.html.twig', array(
                    'fechaDia' => $fechaDia,
                    'empresa' => $empresa->getAlias(),
                    'salidas' => $mapEmpresaSalidas[$empresa->getId()],
                    'mapSalidaBoletos' => $mapSalidaBoletos
                )));
            }
        }
        
        $this->logger->warn("checkBoletosSalidasEspeciales - end");
    }
    
    public function checkReportarInventarioEncomiendas($options = null){
        $this->logger->warn("checkReportarInventarioEncomiendas - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        
        $fechaDia = $this->getCurrentFecha();
        
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            $this->logger->warn("Buscando encomiendas penientes hasta la fecha: " . $fechaDia->format('d-m-Y') . ", en la empresa: " . $empresa->getAlias());
            $data1 = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendaPendienteEntrega($empresa);
            $data2 = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->listarEncomiendaPendienteEnvio($empresa);
            $data = array();
            foreach ($data1 as $item){
                $key = $item["idEstacion"];
                $data[$key][] = $item;
            }
            foreach ($data2 as $item){
                $key = $item["idEstacion"];
                $data[$key][] = $item;
            }
//            var_dump($data);
            if($data !== null && count($data) !== 0){
                $correos = $empresa->getCorreos();
                $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findUserAdministrativosByEmpresa($empresa);
                foreach ($usuarios as $usuario) {
                    $email = $usuario->getEmail();
                    if($email !== null && trim($email) !== ""){
                        $correos[] = trim($email);
                    }
                }

                if($correos !== null && count($correos) !== 0){
                    $correos = array_unique($correos);
//                    $correos = array('javiermarti84@gmail.com');
                    $subject = "ENCO_INV_" . $now . ". Inventario de encomiendas pendientes " . $empresa->getAlias() . "."; 
                    UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_inventario_encomiendas_pendientes.html.twig', array(
                        'empresa' => $empresa,
                        'fechaDia' => $fechaDia,
                        'data' => $data
                    )));
                }  
            }
        }
        $this->logger->warn("checkReportarInventarioEncomiendas - end");
    }
    
    public function checkReportarBusesPilotosActivos($options = null){
        $this->logger->warn("checkReportarBusesPilotosActivos - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $fechaDia = $this->getCurrentFecha();
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            try {
                $this->logger->warn("Ejecutando reporte de buses activos en la empresa: " . $empresa->getAlias());
                $resultBuses = $this->generarReporte("AcmeTerminalOmnibusBundle:Reporte:reporteDetalleBusesInternal", array(
                    'nameReporte' => "BUSES",
                    'type' => "EXCEL",
                    'detalleBuses' => array(
                        'empresa' => $empresa->getId()
                     )
                ));
                
                $this->logger->warn("Ejecutando reporte de pilotos activos en la empresa: " . $empresa->getAlias());
                $resultPilotos = $this->generarReporte("AcmeTerminalOmnibusBundle:Reporte:reporteDetallePilotosInternal", array(
                    'nameReporte' => "PILOTOS",
                    'type' => "EXCEL",
                    'detallePilotos' => array(
                        'empresa' => $empresa->getId()
                     )
                ));
                
                $correos = $empresa->getCorreos();
                $correos = array("sistemasfdn@gmail.com");
                if($correos !== null && count($correos) !== 0){
                    var_dump("Enviando correo...");
                    $subject = "DOCS_BUSES_PILOTOS" . $now . ". LISTADO DE BUSES Y PILOTOS ACTIVOS DE " . $empresa->getAlias() . "."; 
                    UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_buses_pilotos.html.twig', 
                            array()), array($resultBuses, $resultPilotos));
                }

            } catch (\RuntimeException $ex) {
                var_dump($ex->getMessage());
                $this->logger->warn("checkReportarBusesPilotosActivos - ERROR: " . $ex->getMessage());
            }
        }
        $this->logger->warn("checkReportarBusesPilotosActivos - end");
    }
    
    public function checkEstadisticas($options = null){
        $this->logger->warn("checkEstadisticas - init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $fechaDia = $this->getCurrentFecha();
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $empresa) {
            try {
                $fechaInicial = clone $fechaDia ;
                $fechaInicial->modify("-1 month");
                $fechaFinal = clone $fechaDia;
                $fechaFinal->modify("-1 day");
                $rangoFecha = $fechaInicial->format('d/m/Y')." - ".$fechaFinal->format('d/m/Y');
                var_dump($rangoFecha);
                $this->logger->warn("Ejecutando reporte historico de ventas en la empresa: " . $empresa->getAlias());
                $resultVentasTotales = $this->generarReporte("AcmeTerminalOmnibusBundle:Reporte:reporteEstadisticaVentaTotalesInternal", array(
                    'nameReporte' => "ESTADISTICA_VENTA_TOTALES",
                    'type' => "PDF",
                    'estadisticaVentaTotales' => array(
                        'rangoFecha' => $rangoFecha,
                        'estacion' => "",
                        'empresa' => strval($empresa->getId())
                     )
                ));
                
                $this->logger->warn("Ejecutando reporte historico de venta de agencias en la empresa: " . $empresa->getAlias());
                $resultVentasAgencias = $this->generarReporte("AcmeTerminalOmnibusBundle:Reporte:reporteDetalleAgenciaGraficoInternal", array(
                    'nameReporte' => "ESTADISTICA_VENTA_AGENCIA",
                    'type' => "PDF",
                    'detalleAgenciaGrafico' => array(
                        'rangoFecha' => $rangoFecha,
                        'estacion' => "",
                        'empresa' => ""
                     )
                ));
                
                $correos = $empresa->getCorreos();
//                $correos = array("javiermarti84@gmail.com");
                if($correos !== null && count($correos) !== 0){
                    var_dump("Enviando correo...");
                    $subject = "ESTADISTICAS_" . $now . ". EMPRESA: " . $empresa->getAlias() . "."; 
                    UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_estadisticas.html.twig', 
                    array()), array($resultVentasTotales, $resultVentasAgencias));
                }

            } catch (\RuntimeException $ex) {
                var_dump($ex->getMessage());
                $this->logger->warn("checkReportarBusesPilotosActivos - ERROR: " . $ex->getMessage());
            }
        }
        $this->logger->warn("checkEstadisticas - end");
    }
    
    public function setScheduledJob(Job $job) {
        $this->logger->warn("TareasDiariasService - init");
        
        $this->job = $job;
        
        try {
            $this->logger->warn("start-checkSeriesFacturas");
            $this->checkSeriesFacturas();
            $this->logger->warn("end-checkSeriesFacturas");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso checkSeriesFacturas.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-sendTotalesFacturados");
            $this->sendTotalesFacturados();
            $this->logger->warn("end-sendTotalesFacturados");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso sendTotalesFacturados.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-sendTotalesCortesias");
            $this->sendTotalesCortesias();
            $this->logger->warn("end-sendTotalesCortesias");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso sendTotalesFacturados.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-checkFechaVencimientoTarjetaOperaciones");
            $this->checkFechaVencimientoTarjetaOperaciones();
            $this->logger->warn("end-checkFechaVencimientoTarjetaOperaciones");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso sendTotalesFacturados.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-checkFechaVencimientoLicenciaPilotos");
            $this->checkFechaVencimientoLicenciaPilotos();
            $this->logger->warn("end-checkFechaVencimientoLicenciaPilotos");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso sendTotalesFacturados.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-sendTotalesTarjetas");
            $this->sendTotalesTarjetas();
            $this->logger->warn("end-sendTotalesTarjetas");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso sendTotalesTarjetas.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-checkExpiracionCredencialesPorUsuario");
            $this->checkExpiracionCredencialesPorUsuario();
            $this->logger->warn("end-checkExpiracionCredencialesPorUsuario");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso checkExpiracionCredencialesPorUsuario.");
            throw $ex;
        }

//        try {
//            $this->logger->warn("start-checkCajasNoCerradas");
//            $this->checkCajasNoCerradas();
//            $this->logger->warn("end-checkCajasNoCerradas");
//        } catch (\Exception $ex) {
//            $this->logger->warn("Ocurrio una exception en el proceso checkCajasNoCerradas.");
//            throw $ex;
//        }
        
//        try {
//            $this->logger->warn("start-checkBoletosSalidasEspeciales");
//            $this->checkBoletosSalidasEspeciales();
//            $this->logger->warn("end-checkBoletosSalidasEspeciales");
//        } catch (\Exception $ex) {
//            $this->logger->warn("Ocurrio una exception en el proceso checkBoletosSalidasEspeciales.");
//            throw $ex;
//        }
        
        try {
            $this->logger->warn("start-checkReportarInventarioEncomiendas");
            $this->checkReportarInventarioEncomiendas();
            $this->logger->warn("end-checkReportarInventarioEncomiendas");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso checkReportarInventarioEncomiendas.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-checkReportarBusesPilotosActivos");
            $this->checkReportarBusesPilotosActivos();
            $this->logger->warn("end-checkReportarBusesPilotosActivos");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso checkReportarBusesPilotosActivos.");
            throw $ex;
        }
        
        try {
            $this->logger->warn("start-checkEstadisticas");
            $this->checkEstadisticas();
            $this->logger->warn("end-checkEstadisticas");
        } catch (\Exception $ex) {
            $this->logger->warn("Ocurrio una exception en el proceso checkEstadisticas.");
            throw $ex;
        }
        
        $this->logger->warn("TareasDiariasService - end");
    }
    
    public function generarReporte($methodController, $optionPost){
        $request =  new Request();
        $request->initialize(array(), $optionPost, array(), array(), array(), array('REMOTE_ADDR' => "127.0.0.1"));
        $request->setMethod("POST");
        $request->setSession(new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()));
        $this->container->set('request', $request, 'request');
        $subRequest = $this->container->get('request')->duplicate(array(), null, array(
            '_controller' => $methodController,
            'request'  => $request,
            '_route'  => 'route',
        ));
        $respose = $this->container->get('http_kernel')->handle($subRequest, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST);
        $result = $respose->getContent();
        if(file_exists($result)){
            var_dump("Reporte generado satisfactoriamente. Path:".$result);
            $this->logger->error("Reporte generado satisfactoriamente. Path:".$result);
            return $result;
        }else{
            throw new \RuntimeException("Fayo generacion de reporte. Error:".$result);
        }
    }
    
}
