<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Form\Model\ReporteModel;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\VentaBoletoPropietarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\VentaBoletoUsuarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\BoletoAnuladoUsuarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\VentaBoletoPrepagadoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\VentaBoletoPrepagadoOtrasEstacionesPorBusesType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\VentaBoletoOtraEstacionType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\ManisfiestoBoletoFullType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\ManisfiestoBoletoPilotoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\ManisfiestoEncomiendaFullType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\EncomiendaAnuladoUsuarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\TarifasBoletosType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\CajaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\EncomiendaPropietarioType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\CuadreEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\EncomiendaPendienteEntregarType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleCajaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleFacturaBoletoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleFacturaEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleCortesiaBoletoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleGuiaInternaEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleCortesiaEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\CuadreVentaBoletoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleAlquilerType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\AsistenciaPilotosType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleAgenciaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\TarifasEncomiendasEspecialesType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleGeneralEncomiendaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\CuadreVentaBoletoVoucherType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleDepositoAgenciaType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleSalidasType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleBusesType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetallePilotosType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleAgenciaGraficoType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\EstadisticaVentaTotalesType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetallePortalType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\CuadreInspectorType;
use Acme\TerminalOmnibusBundle\Form\Frontend\Reporte\DetalleTarjetasType;

/**
*   @Route(path="/reportes")
*/
class ReporteController extends Controller {
    
    private $pathComponente;
    private $pathResportes;
    private $reportManager;
    
    function __construct() {
        
        if(!defined('JAVA_INC_URL')){ define('JAVA_INC_URL','http://localhost:8080/PHPJRU/java/Java.inc'); }
        if(!defined('PHP_JRU_VERSION')){ define('PHP_JRU_VERSION','1.0'); }
        if( ! function_exists('java')){
            if( ini_get("allow_url_include"))
                require_once(JAVA_INC_URL);
            else
                die ('necesita habilitar allow_url_include en php.ini para poder usar php-jru.');
        }
        
        if(!defined('PJRU_PDF')){ define('PJRU_PDF','pdf'); }
        if(!defined('PJRU_OPEN_DOCUMENT')){ define('PJRU_OPEN_DOCUMENT','odt'); }
        if(!defined('PJRU_EXCEL')){ define('PJRU_EXCEL','xls'); }
        if(!defined('PJRU_HTML')){ define('PJRU_HTML','html'); }
        if(!defined('PJRU_RICH_TEXT')){ define('PJRU_RICH_TEXT','rtf'); }
        if(!defined('PJRU_TXT')){ define('PJRU_TXT','txt'); }
        if(!defined('PJRU_DOCX')){ define('PJRU_DOCX','docx'); }
        if(!defined('PJRU_PPTX')){ define('PJRU_PPTX','pptx'); }
        if(!defined('PJRU_XML')){ define('PJRU_XML','xml'); }
        
        $this->pathComponente = $this->getPathComponente();
        $this->pathResportes = $this->getPathReportes();
        
        require_once $this->pathComponente . 'JdbcConnection.php';
        require_once($this->pathComponente. 'PJRU.php');
        require_once $this->pathComponente. 'JdbcAdapters\JdbcAdapterInterface.php';
        require_once $this->pathComponente. 'PJRUConexion.php';
        require_once($this->pathComponente. 'ReportManager\ReportManager.php');
        
        $reportManager = new \ReportManager();
        $this->reportManager = $reportManager;
        $reportManager->extensionFolder = $this->pathResportes; 
    }
    
    private function chechDefaultConnection() {
        $connetion = $this->reportManager->getConnetionDefalt();
        if($connetion === null){
            $container = $this->container;
            $type = $container->getParameter('database_type');
            $host = $container->getParameter('database_host');
            $port = $container->getParameter('database_port');
            $user = $container->getParameter('database_user');
            $pass = $container->getParameter('database_password');
            $database = $container->getParameter('database_name');
            $connetion = \PJRUConexion::get($type,$host,$port,$database,$user,$pass);
            $this->reportManager->setConnetionDefalt($connetion); 
        }
    }
    
    private function getPathComponente() {
        $clase = new \ReflectionClass("Acme\TerminalOmnibusBundle\PHPJRU\PHPJRU");
        $fileName = $clase->getFileName();
        $basePath = str_replace("PHPJRU.php", "", $fileName);
        return $basePath;
    }
    
    private function getPathReportes() {
        $clase = new \ReflectionClass("Acme\TerminalOmnibusBundle\Reportes\Reportes");
        $fileName = $clase->getFileName();
        $basePath = str_replace("\Reportes.php", "", $fileName);
        return $basePath;
    }
    
    protected function getRootDir()
    {
        return __DIR__.'\\..\\..\\..\\..\\web\\';
    }
    
    private function generarReporte($name, $request, $pathInternalFile = false) {
        try {
            
            if(!is_null($request)){
                $type = $request->query->get('type');
                if (is_null($type)) {
                    $type = $request->request->get('type');
                }
            }
            if (is_null($type)) {
               $type = PJRU_PDF;
            }else if($type === "PDF"){
               $type = PJRU_PDF;
            }else if($type === "DOCX"){
               $type = PJRU_DOCX;
            }else if($type === "EXCEL"){
               $type = PJRU_EXCEL;
            }else if($type === "HTML"){
               $type = PJRU_HTML;
            }else if($type === "RTF"){
               $type = PJRU_RICH_TEXT;
            }else if($type === "TXT"){
               $type = PJRU_TXT;
            }else if($type === "XML"){
               $type = PJRU_XML;
            }else if($type === "PPTX"){
               $type = PJRU_PPTX;
            }
            
            $this->chechDefaultConnection();        
            $pathFile = $this->reportManager->RunToFile($name, $type, $this->container);
            
            $nameFile = null;
            if(!is_null($request)){
                $nameFile = $request->query->get('nameReporte');
                if (is_null($nameFile)) {
                    $nameFile = $request->request->get('nameReporte');
                }
            }
            
            if(is_null($nameFile))
            {
                $lastSeparator = strrpos($pathFile, "\\"); 
                $nameFile = substr($pathFile, $lastSeparator+1);    
            }else{
                $fecha = new \DateTime();
                $nameFile .= "_" . $fecha->format('Y.m.d_H.i.s').".".$type;
            }
            
            if(file_exists($pathFile)){
                $newPathFile = $this->getRootDir() . "reporte\\" . $nameFile;
                if(copy($pathFile, $newPathFile)){
                    unlink($pathFile);
                    if($pathInternalFile){
                        return new Response($newPathFile);
                    }else{
                        return $this->render('AcmeTerminalOmnibusBundle:Commun:assetPath.html.twig', array(
                            'path' => "reporte/".$nameFile
                        ));
                    }
                    
                }else{
                    $mensajeServidor = "Ha ocurrido un error generando el reporte. ERROR:COD001.";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }else{
                $mensajeServidor = "Ha ocurrido un error generando el reporte. ERROR:COD002.";
                return UtilService::returnError($this, $mensajeServidor);
            }
            
        }catch (\ErrorException $ex) {
            var_dump($ex);
            $mensajeServidor = "Ha ocurrido un error generando el reporte. ERROR:COD003.";
            return UtilService::returnError($this, $mensajeServidor);
        }catch (\RuntimeException $ex) {
            $mensajeServidor = "Ha ocurrido un error generando el reporte. ERROR:COD004.";
            $mensaje = $ex->getMessage();
            if(UtilService::startsWith($mensaje, 'm1')){
                $mensajeServidor = $mensaje;
            }
            return UtilService::returnError($this, $mensajeServidor);
        }catch (\Exception $ex) {
            var_dump($ex);
            $mensajeServidor = "Ha ocurrido un error generando el reporte. ERROR:COD005.";  
            return UtilService::returnError($this, $mensajeServidor);
        }
        
    }
    
     /**
     * @Route(path="/ventaBoletoPropietario.html", name="reporte-venta_boleto_propietario")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteVentaBoletoPropietario(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("ventaBoletoPropietario", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new VentaBoletoPropietarioType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:ventaBoletoPropietario.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/ventaBoletoUsuario.html", name="reporte-venta_boleto_usuario")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteVentaBoletoUsuario(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("ventaBoletoUsuario", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new VentaBoletoUsuarioType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:ventaBoletoUsuario.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/boletoAnuladoUsuario.html", name="reporte-boleto_anulado_usuario")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteBoletoAnuladoUsuario(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("boletoAnuladoUsuario", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new BoletoAnuladoUsuarioType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:boletoAnuladoUsuario.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/ventaBoletoPrepagado.html", name="reporte-venta_boleto_prepagado")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteVentaBoletoPrepagado(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("ventaBoletoPrepagado", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new VentaBoletoPrepagadoType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:ventaBoletoPrepagado.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
        /**
     * @Route(path="/ventaBoletoPrepagadoPorBuses.html", name="reporte-venta_boleto_prepagado_otras_estaciones_por_buses")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteVentaBoletoPrepagadoOtrasEstacionesPorBuses(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("ventaBoletoPrepagadoOtrasEstacionesPorBuses", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new VentaBoletoPrepagadoOtrasEstacionesPorBusesType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:ventaBoletoPrepagadoOtrasEstacionesPorBuses.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/ventaBoletoOtrasEstaciones.html", name="reporte-venta_boleto_otra_estacion")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteVentaBoletoOtrasEstaciones(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("ventaBoletoOtrasEstaciones", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new VentaBoletoOtraEstacionType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:ventaBoletoOtrasEstaciones.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/manifiestoBoletoPiloto.html", name="reporte-manifiesto_boleto_piloto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA_SALIDA, ROLE_INSPECTOR_BOLETO")
     */
    public function reporteManifiestoBoletoPiloto(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("manisfiestoBoletoPiloto", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new ManisfiestoBoletoPilotoType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:manisfiestoBoletoPiloto.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/manifiestoBoletoFull.html", name="reporte-manifiesto_boleto_full")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA_SALIDA, ROLE_INSPECTOR_BOLETO")
     */
    public function reporteManifiestoBoletoFull(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("manisfiestoBoletoFull", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new ManisfiestoBoletoFullType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:manisfiestoBoletoFull.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/manifiestoBoletoEspecial.html", name="reporte-manifiesto_boleto_especial")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_MANIFIESTO_ESPECIAL")
     */
    public function reporteManifiestoBoletoEspecialAction(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("manisfiestoBoletoEspecial", $request); 
       }else{
           return UtilService::returnError($this);
       }
    }
    
    /**
     * @Route(path="/manifiestoEncomiendaFull.html", name="reporte-manifiesto_encomienda_full")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function reporteManifiestoEncomiendaFull(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("manisfiestoEncomiendaFull", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new ManisfiestoEncomiendaFullType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:manisfiestoEncomiendaFull.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/encomiendaAnuladoUsuario.html", name="reporte-encomienda_anulado_usuario")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function reporteEncomiendaAnuladoUsuario(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("encomiendaAnuladaUsuario", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new EncomiendaAnuladoUsuarioType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:encomiendaAnuladoUsuario.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    // RAEE 28/12/2019 11:30 A.M. - Agregado el rol ROLE_AGENCIA para que se muestre la opción para generar este reporte llamado Boleto Tarifas en esta función llamada reporteTarifasBoletos
    /**
     * @Route(path="/tarifasBoletos.html", name="reporte-tarifas_boletos")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_INSPECTOR_BOLETO, ROLE_AGENCIA")
     */
    public function reporteTarifasBoletos(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("tarifasBoletos", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new TarifasBoletosType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:tarifasBoletos.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/caja.html", name="reporte-caja")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteCaja(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("caja", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new CajaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:caja.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleCaja.html", name="reporte-detalle-caja")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteDetalleCaja(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleCaja", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleCajaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleCaja.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/encomiendaCuadreEncomienda.html", name="reporte-cuadre_encomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function reporteCuadreEncomienda(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("cuadreEncomienda", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new CuadreEncomiendaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:cuadreEncomienda.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/encomiendaPropietario.html", name="reporte-encomienda_propietario")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function reporteEncomiendaPropietario(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("encomiendaPropietario", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new EncomiendaPropietarioType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:encomiendaPropietario.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/encomiendaPendienteEntregar.html", name="reporte-encomienda_pendiente_entregar")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function reporteEncomiendaPendienteEntregar(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("encomiendaPendienteEntregar", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new EncomiendaPendienteEntregarType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:encomiendaPendienteEntregar.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleFacturaBoleto.html", name="reporte-detalle-factura-boleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteDetalleFacturaBoleto(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleFacturaBoleto", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleFacturaBoletoType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleFacturaBoleto.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleFacturaEncomienda.html", name="reporte-detalle-factura-encomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function reporteDetalleFacturaEncomienda(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleFacturaEncomienda", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleFacturaEncomiendaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleFacturaEncomienda.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
        /**
     * @Route(path="/detalleGeneralEncomienda.html", name="reporte-detalle-general-encomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function reporteDetalleGeneralEncomienda(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleGeneralEncomienda", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleGeneralEncomiendaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleGeneralEncomienda.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleCortesiaBoleto.html", name="reporte-detalle-cortesia-boleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteDetalleCortesiaBoleto(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleCortesiaBoleto", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleCortesiaBoletoType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleCortesiaBoleto.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleCortesiaEncomienda.html", name="reporte-detalle-cortesia-encomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function reporteDetalleCortesiaEncomienda(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleCortesiaEncomienda", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleCortesiaEncomiendaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleCortesiaEncomienda.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleGuiaInternaEncomienda.html", name="reporte-detalle-guia-encomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function reporteDetalleGuiaInternaEncomienda(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleGuiaInternaEncomienda", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleGuiaInternaEncomiendaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleGuiaEncomienda.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/tarifasEncomiendasEspeciales.html", name="reporte-tarifas_encomiendas_especiales")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function reporteTarifasEncomiendasEspeciales(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("tarifasEncomiendasEspeciales", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new TarifasEncomiendasEspecialesType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:tarifasEncomiendasEspeciales.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/cuadreVentaBoleto.html", name="reporte-cuadre-venta-boleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO")
     */
    public function reporteCuadreVentaBoleto(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("cuadreVentaBoleto", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new CuadreVentaBoletoType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:cuadreVentaBoleto.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/cuadreVentaBoletoVoucher.html", name="reporte-cuadre-venta-boleto-voucher")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_PROPIETARIO, ROLE_SUPERVISOR_BOLETO_VOUCHER")
     */
    public function CuadreVentaBoletoVoucherAction(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("cuadreVentaBoletoVoucher", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new CuadreVentaBoletoVoucherType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:cuadreVentaBoletoVoucher.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
     /**
     * @Route(path="/manifiestoAlquiler.html", name="reporte-manifiesto_alquiler")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function reporteManifiestoAlquiler(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("manisfiestoAlquiler", $request); 
       }else{
            return new Response("No soportado");
       }
    }
    
    /**
     * @Route(path="/detalleAlquiler.html", name="reporte-detalle-alquiler")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ALQUILER")
     */
    public function reporteDetalleAlquiler(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleAlquiler", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleAlquilerType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleAlquiler.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/asistenciaPilotos.html", name="reporte-asistencia-pilotos")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS")
     */
    public function reporteAsistenciaPilotos(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("asistenciaPilotos", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new AsistenciaPilotosType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:asistenciaPilotos.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleAgencia.html", name="reporte-detalle-agencia")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA, ROLE_SUPERVISOR_AGENCIA")
     */
    public function reporteDetalleAgencia(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleAgencia", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleAgenciaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleAgencia.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detallePortal.html", name="reporte-detalle-portal")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_PORTAL")
     */
    public function reporteDetallePortal(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detallePortal", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetallePortalType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detallePortal.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleAgenciaGrafico.html", name="reporte-detalle-agencia-grafico")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA, ROLE_SUPERVISOR_AGENCIA")
     */
    public function reporteDetalleAgenciaGraficoAction(Request $request, $_route) {
        return $this->reporteDetalleAgenciaGraficoInternalAction($request, $_route, false);
    }
    
    public function reporteDetalleAgenciaGraficoInternalAction(Request $request, $_route, $pathFile = true) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleAgenciaGrafico", $request, $pathFile); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleAgenciaGraficoType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleAgenciaGrafico.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleDepositoAgencia.html", name="reporte-detalle-deposito-agencia")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_AGENCIA, ROLE_SUPERVISOR_AGENCIA")
     */
    public function reporteDetalleDepositoAgencia(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleDepositoAgencia", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleDepositoAgenciaType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleDepositoAgencia.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleSalidas.html", name="reporte-detalle-salida")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_BOLETO, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_INSPECTOR_BOLETO")
     */
    public function reporteDetalleSalida(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleSalidas", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleSalidasType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleSalidas.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/reporteEncomiendasPendientesEnvio.html", name="reporte-encomiendas-pendientes-envio")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS, ROLE_INSPECTOR_ENCOMIENDA")
     */
    public function reporteEncomiendasPendientesEnvio(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("encomiendasPendientesEnvio", $request); 
       }else{
            return new Response("Fails");
       }
    }
    
    /**
     * @Route(path="/detalleBuses.html", name="reporte-detalle-buses")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ADMIN_BUSES")
     */
    public function reporteDetalleBusesAction(Request $request, $_route) {
        return $this->reporteDetalleBusesInternalAction($request, $_route, false);
    }
    
    public function reporteDetalleBusesInternalAction(Request $request, $_route, $pathFile = true) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleBuses", $request, $pathFile); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleBusesType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleBuses.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detallePilotos.html", name="reporte-detalle-pilotos")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_ADMIN_PILOTOS")
     */
    public function reporteDetallePilotosAction(Request $request, $_route) {
        return $this->reporteDetallePilotosInternalAction($request, $_route, false);
    }
    
    public function reporteDetallePilotosInternalAction(Request $request, $_route, $pathFile = true) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detallePilotos", $request, $pathFile); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetallePilotosType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detallePilotos.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/estadisticaVentaTotales.html", name="reporte-estadisticas-ventas-totales")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PROPIETARIO")
     */
    public function reporteEstadisticaVentaTotalesAction(Request $request, $_route) {
        return $this->reporteEstadisticaVentaTotalesInternalAction($request, $_route, false);
    }
    
    public function reporteEstadisticaVentaTotalesInternalAction(Request $request, $_route, $pathFile = true) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("estadisticaVentaTotales", $request, $pathFile); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new EstadisticaVentaTotalesType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:estadisticaVentaTotales.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    
    /**
     * @Route(path="/cuadreInspector.html", name="reporte-cuadre-inspector")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_INSPECTOR_BOLETO")
     */
    public function reporteCuadreInspector(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("cuadreInspector", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new CuadreInspectorType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:cuadreInspector.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
    
    /**
     * @Route(path="/detalleTarjetas.html", name="reporte-detalle-tarjetas")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_PROPIETARIO, ROLE_ADMINISTRATIVOS, ROLE_INSPECTOR_BOLETO, ROLE_REVISOR")
     */
    public function detalleTarjetasInspector(Request $request, $_route) {
        if ($request->isMethod('POST')) {
            return $this->generarReporte("detalleTarjetas", $request); 
       }else{
            $reporte = new ReporteModel();       
            $form = $this->createForm(new DetalleTarjetasType($this->getDoctrine()), $reporte, array(
                 "user" => $this->getUser()
            ));
            $respuesta = $this->render('AcmeTerminalOmnibusBundle:Reporte:detalleTarjetas.html.twig', array(
                'form' => $form->createView(),
                'route' => $_route,
                'mensajeServidor' => ""
            ));
            //12 horas
            $respuesta->setMaxAge(43200); //Cache del servidor
            $respuesta->setVary('Accept-Encoding'); //Cache del servidor
            $respuesta->setExpires(new \DateTime('now + 720 minutes')); //Cache del navegador
            return $respuesta;
       }
    }
}

?>
