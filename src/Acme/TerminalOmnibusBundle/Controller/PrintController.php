<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Acme\BackendBundle\Services\UtilService;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\TipoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\EstadoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\Impresora;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;

/**
*   @Route(path="/ajax/print")
*/
class PrintController extends Controller {

    protected $impresoraDefault = "Foxit Reader PDF Printer"; 
            
    /**
     * @Route(path="/facturaBoleto.{_format}", name="ajaxPrintFacturaBoleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function printFacturaBoletoAction(Request $request, $_format = "html", $data = null, $info = null, $reprint = false) {
        
        try {
            $ids = $request->query->get('ids');
            if (is_null($ids)) {
                $ids = $request->request->get('ids');
            }
            
            if($data !== null){ $ids = $data; }
            
            if($ids === null || trim($ids) === "" || trim($ids) === "0"){
                return UtilService::returnError($this, "m1No se ha especificado los identificadores de los boletos para la impresión de factura.");
            }
            
            $salida = null;
            $boletos = array();
            $idsArray = explode(",", $ids);
            $empresa = null;
            $tipoDocumento = null;
            $serieFactura = null;
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                return UtilService::returnError($this, "m1Para reimprimir una factura de boleto el usuario debe estar asociado a la estación donde se creo el boleto.");
            }
            
            foreach ($idsArray as $id) {
                if($id !== null && trim($id) !== "" && trim($id) !== "0"){
                    $boleto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);                                     
                    
                    
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.
        
        
//    CREDENCIALES EN ENTORNO DE PRODUCCIÓN DE e-FORCON PARA ROSITA       
    $sUsuarioRosita = 'proper-transrosita';
    $sClaveRosita = 'Transrosita2021*';        

//    CREDENCIALES EN ENTORNO DE PRODUCCIÓN DE e-FORCON PARA MAYA DE ORO       
	$sUsuarioMitocha = 'operpro1-mayaoro';   
	$sClaveMitocha = 'M@y$2781%Qa5!!';		
        
        
//    CREDENCIALES EN ENTORNO DE PRODUCCIÓN DE e-FORCON PARA PIONERA             
    $sUsuario = 'operws1-fdn';
    $sClave = '$$FDN@2020Fel';     
    
         
    $snomReceptor = $boleto->getClienteDocumento()->__toString();
	
	$boletoId = $boleto->getId();

    $sNomPasajero = $boleto->getClienteBoleto()->__toString();
    
    $sSalida = $boleto->getSalida()->getFecha();
    
    $sFechaSalida = $sSalida->format('d/m/Y');
    
    $sHoraSalida = $sSalida->format('h:i A');
    
    $sFechaBoleto = $boleto->getFechaCreacion();
    
    $sFechaCreacion = $sFechaBoleto->format('Y-m-d H:i:s');
    
    $sEstacionCreacionNom = $boleto->getEstacionCreacion()->getNombre();
    
    $sEstacionOrigenNom = $boleto->getEstacionOrigen()->getNombre();
    
    $sEstacionDestinoNom = $boleto->getEstacionDestino()->getNombre();    
    
            $sAsientoNumero = "N/D";
            if($boleto->getAsientoBus() !== null){
                $sAsientoNumero = $boleto->getAsientoBus()->getNumero();
            }                    
                    
            $sNit = $boleto->getClienteDocumento()->getNit();
            $sClienteNit = preg_replace('([^A-Za-z0-9])', '', $sNit);
            
            $sImporteTotal = $boleto->getFacturaGenerada()->getImporteTotal();
            
            $sMonedaSigla = $boleto->getFacturaGenerada()->getMoneda()->getSigla();
            
            $sFacturaBoletoId = $boleto->getFacturaGenerada()->getId();
            
            $sCompanyNit = $boleto->getFacturaGenerada()->getFactura()->getEmpresa()->getNit();
            $sEmpresaNit = preg_replace('([^A-Za-z0-9])', '', $sCompanyNit);
                       
            $sNumEstablecimientoSatPionera = $boleto->getFacturaGenerada()->getEstacion()->getNumEstablecimientoSat();            
                        
            $sNumEstablecimientoSatMitocha = $boleto->getFacturaGenerada()->getEstacion()->getNumEstablecimientoSatMitocha();   

            $sNumEstablecimientoSatRosita = $boleto->getFacturaGenerada()->getEstacion()->getNumEstablecimientoSatRosita();			

    
    
        $sXmlDtePionera = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dte><encabezadoPrincipal><codigoInternoEmisor>'.$sFacturaBoletoId.'</codigoInternoEmisor><nitEmisor>'.$sEmpresaNit.'</nitEmisor><numeroEstablecimiento>'.$sNumEstablecimientoSatPionera.'</numeroEstablecimiento><tipoDTE>FACT</tipoDTE><usoComercialDTE>LOCAL</usoComercialDTE><tipoReceptor>N</tipoReceptor><idReceptor>'.$sClienteNit.'</idReceptor><fechaEmision>'.$sFechaCreacion.'</fechaEmision><nombreReceptor>'.$snomReceptor.'</nombreReceptor><moneda>'.$sMonedaSigla.'</moneda><frases><definicionFrase><codigoFrase>2</codigoFrase><codigoEscenario>1</codigoEscenario></definicionFrase><definicionFrase><codigoFrase>1</codigoFrase><codigoEscenario>1</codigoEscenario></definicionFrase></frases><complementos><numeroAbonosCAMB/><fechaInicialVenceCAMB/><montoAbonosCAMB/><diasEntreAbonosCAMB/><nombreConsignatarioEXP/><direccionConsignatarioEXP/><incotermEXP/><codigoConsignatarioEXP/><nombreCompradorEXP/><direccionCompradorEXP/><codigoCompradorEXP/><otraReferenciaEXP/><nombreExportadorEXP/><codigoExportadorEXP/><tipoRegimenDTE/><numeroAutorizacion/><motivoAjuste/><fechaEmisionOrigen/><numeroOrigenFace/><serieOrigenFace/></complementos></encabezadoPrincipal><detallePrincipal><definicionDP><numeroItem>1</numeroItem><bienServicio>S</bienServicio><nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto><cantidad>1</cantidad><metrica>UNI</metrica><valorTasaMunicipal>0</valorTasaMunicipal><descripcion>VENTA DE BOLETO DE TRANSPORTE</descripcion><precioUnitario>0</precioUnitario><descuento>0</descuento><totalPorLinea>'.$sImporteTotal.'</totalPorLinea></definicionDP></detallePrincipal><encabezadoExtra><definicionEE><codigoEtiquetaEE>127</codigoEtiquetaEE><valorEtiquetaEE>'.$sHoraSalida.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>128</codigoEtiquetaEE><valorEtiquetaEE>'.$sFechaSalida.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>129</codigoEtiquetaEE><valorEtiquetaEE>'.$sAsientoNumero.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>130</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>131</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>132</codigoEtiquetaEE><valorEtiquetaEE>'.$sNomPasajero.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>133</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionCreacionNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>135</codigoEtiquetaEE><valorEtiquetaEE>T</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>198</codigoEtiquetaEE><valorEtiquetaEE>'.$boletoId.'</valorEtiquetaEE></definicionEE></encabezadoExtra></dte></plantilla>';
    
    
    $sXmlDteMitocha = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dte><encabezadoPrincipal><codigoInternoEmisor>'.$sFacturaBoletoId.'</codigoInternoEmisor><nitEmisor>'.$sEmpresaNit.'</nitEmisor><numeroEstablecimiento>'.$sNumEstablecimientoSatMitocha.'</numeroEstablecimiento><tipoDTE>FACT</tipoDTE><usoComercialDTE>LOCAL</usoComercialDTE><tipoReceptor>N</tipoReceptor><idReceptor>'.$sClienteNit.'</idReceptor><fechaEmision>'.$sFechaCreacion.'</fechaEmision><nombreReceptor>'.$snomReceptor.'</nombreReceptor><moneda>'.$sMonedaSigla.'</moneda><frases><definicionFrase><codigoFrase>1</codigoFrase><codigoEscenario>1</codigoEscenario></definicionFrase></frases><complementos><numeroAbonosCAMB/><fechaInicialVenceCAMB/><montoAbonosCAMB/><diasEntreAbonosCAMB/><nombreConsignatarioEXP/><direccionConsignatarioEXP/><incotermEXP/><codigoConsignatarioEXP/><nombreCompradorEXP/><direccionCompradorEXP/><codigoCompradorEXP/><otraReferenciaEXP/><nombreExportadorEXP/><codigoExportadorEXP/><tipoRegimenDTE/><numeroAutorizacion/><motivoAjuste/><fechaEmisionOrigen/><numeroOrigenFace/><serieOrigenFace/></complementos></encabezadoPrincipal><detallePrincipal><definicionDP><numeroItem>1</numeroItem><bienServicio>S</bienServicio><nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto><cantidad>1</cantidad><metrica>UNI</metrica><valorTasaMunicipal>0</valorTasaMunicipal><descripcion>VENTA DE BOLETO DE TRANSPORTE</descripcion><precioUnitario>0</precioUnitario><descuento>0</descuento><totalPorLinea>'.$sImporteTotal.'</totalPorLinea></definicionDP></detallePrincipal><encabezadoExtra><definicionEE><codigoEtiquetaEE>127</codigoEtiquetaEE><valorEtiquetaEE>'.$sHoraSalida.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>128</codigoEtiquetaEE><valorEtiquetaEE>'.$sFechaSalida.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>129</codigoEtiquetaEE><valorEtiquetaEE>'.$sAsientoNumero.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>130</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>131</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>132</codigoEtiquetaEE><valorEtiquetaEE>'.$sNomPasajero.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>133</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionCreacionNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>135</codigoEtiquetaEE><valorEtiquetaEE>T</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>198</codigoEtiquetaEE><valorEtiquetaEE>'.$boletoId.'</valorEtiquetaEE></definicionEE></encabezadoExtra></dte></plantilla>';

    $sXmlDteRosita = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dte><encabezadoPrincipal><codigoInternoEmisor>'.$sFacturaBoletoId.'</codigoInternoEmisor><nitEmisor>'.$sEmpresaNit.'</nitEmisor><numeroEstablecimiento>'.$sNumEstablecimientoSatRosita.'</numeroEstablecimiento><tipoDTE>FACT</tipoDTE><usoComercialDTE>LOCAL</usoComercialDTE><tipoReceptor>N</tipoReceptor><idReceptor>'.$sClienteNit.'</idReceptor><fechaEmision>'.$sFechaCreacion.'</fechaEmision><nombreReceptor>'.$snomReceptor.'</nombreReceptor><moneda>'.$sMonedaSigla.'</moneda><frases><definicionFrase><codigoFrase>1</codigoFrase><codigoEscenario>1</codigoEscenario></definicionFrase></frases><complementos><numeroAbonosCAMB/><fechaInicialVenceCAMB/><montoAbonosCAMB/><diasEntreAbonosCAMB/><nombreConsignatarioEXP/><direccionConsignatarioEXP/><incotermEXP/><codigoConsignatarioEXP/><nombreCompradorEXP/><direccionCompradorEXP/><codigoCompradorEXP/><otraReferenciaEXP/><nombreExportadorEXP/><codigoExportadorEXP/><tipoRegimenDTE/><numeroAutorizacion/><motivoAjuste/><fechaEmisionOrigen/><numeroOrigenFace/><serieOrigenFace/></complementos></encabezadoPrincipal><detallePrincipal><definicionDP><numeroItem>1</numeroItem><bienServicio>S</bienServicio><nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto><cantidad>1</cantidad><metrica>UNI</metrica><valorTasaMunicipal>0</valorTasaMunicipal><descripcion>VENTA DE BOLETO DE TRANSPORTE</descripcion><precioUnitario>0</precioUnitario><descuento>0</descuento><totalPorLinea>'.$sImporteTotal.'</totalPorLinea></definicionDP></detallePrincipal><encabezadoExtra><definicionEE><codigoEtiquetaEE>127</codigoEtiquetaEE><valorEtiquetaEE>'.$sHoraSalida.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>128</codigoEtiquetaEE><valorEtiquetaEE>'.$sFechaSalida.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>129</codigoEtiquetaEE><valorEtiquetaEE>'.$sAsientoNumero.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>130</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>131</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>132</codigoEtiquetaEE><valorEtiquetaEE>'.$sNomPasajero.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>133</codigoEtiquetaEE><valorEtiquetaEE>'.$sEstacionCreacionNom.'</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>135</codigoEtiquetaEE><valorEtiquetaEE>T</valorEtiquetaEE></definicionEE><definicionEE><codigoEtiquetaEE>198</codigoEtiquetaEE><valorEtiquetaEE>'.$boletoId.'</valorEtiquetaEE></definicionEE></encabezadoExtra></dte></plantilla>';	
               

    
                require_once('lib/nusoap.php');

//                $soapClient = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/wsforconfel.asmx?WSDL','wsdl');
                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconFel.asmx?WSDL','wsdl');
//                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;                  
                $soapClient->debug_flag = true;
//                $soapClientMitochaTest->debug_flag = true;	



				
                $sCompanyId = $boleto->getFacturaGenerada()->getFactura()->getEmpresa()->getId();
                

                if($sCompanyId === 1 || $sCompanyId === "1"){
//                if($empresa->getId() === 1 || $empresa->getId() === "1"){                    
                
                
                $param = array('sUsuario' => $sUsuario, 'sClave' => $sClave, 'sXmlDte' => $sXmlDtePionera);
                $result = $soapClient->call('EmitirDteGenerico', $param);
            
                }else if($sCompanyId === 2 || $sCompanyId === "2"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    
                    
                $param = array('sUsuario' => $sUsuarioMitocha, 'sClave' => $sClaveMitocha, 'sXmlDte' => $sXmlDteMitocha);
                $result = $soapClient->call('EmitirDteGenerico', $param);                    
                    
                }else if($sCompanyId === 7 || $sCompanyId === "7"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    
                    
                $param = array('sUsuario' => $sUsuarioRosita, 'sClave' => $sClaveRosita, 'sXmlDte' => $sXmlDteRosita);
                $result = $soapClient->call('EmitirDteGenerico', $param);                    
                    
                }
            
    
    
                
                $sAutorizacionUUIDs = array('sAutorizacionUUIDok' =>$result['EmitirDteGenericoResult']['rwsAutorizacionUUID']);
                $sNumeroDTEs = array('sNumeroDTEok' =>$result['EmitirDteGenericoResult']['rwsNumeroDTE']);
                $sSerieDTEs = array('sSerieDTEok' =>$result['EmitirDteGenericoResult']['rwsSerieDTE']);
                $sFechaCertificaDTEs = array('sFechaCertificaDTEok' =>$result['EmitirDteGenericoResult']['rwsFechaCertificaDTE']);
                

                

                        
        
        
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.
                    
                    
                
                
                
                
                
                
                
                
                
                    if ($boleto === null){
                        return UtilService::returnError($this, "m1El boleto con id: ".$id. " no existe.");
                    }
                    if(intval($boleto->getEstado()->getId()) !== intval(EstadoBoleto::EMITIDO)){
                        return UtilService::returnError($this, "m1Solamente se puede reimprimir la factura de un boleto que este en estado emitido. El estado actual es: " . $boleto->getEstado()->getNombre() . ".");
                    }
                    if($boleto->getFechaCreacion() !== null){
                        if(UtilService::compararFechas($boleto->getFechaCreacion(), new \DateTime()) !== 0 ){
                            return UtilService::returnError($this, "m1Solamente se puede reimprimir una factura de boleto el mismo día que se creo.");
                        }
                    }
                    
                    $salida = $boleto->getSalida();
                    if($empresa === null){
                        $empresa = $salida->getEmpresa(); 
                    }else{
                        if($empresa !== $salida->getEmpresa()){
                            return UtilService::returnError($this, "m1No se puede imprimir boletos de diferentes empresas a la vez.");
                        }
                    }
                    if($tipoDocumento === null){
                        $tipoDocumento = $boleto->getTipoDocumento();
                    }else if ($tipoDocumento->getId() !== $boleto->getTipoDocumento()->getId()){
                        return UtilService::returnError($this, "m1No se pueden imprimir boletos de diferentes documentos.");
                    }
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA || $tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_ESPECIAL){
                        if($boleto->getFacturaGenerada() !== null && $boleto->getFacturaGenerada()->getFactura() !== null){
                            if($serieFactura === null){
                                $serieFactura = $boleto->getFacturaGenerada()->getFactura();
                            }else if($serieFactura->getId() !== $boleto->getFacturaGenerada()->getFactura()->getId()){
                                return UtilService::returnError($this, "m1No se pueden imprimir boletos de diferentes series de facturas.");
                            }
                        }
                    }
                    
                    if($tipoDocumento->getId() === TipoDocumentoBoleto::FACTURA_OTRA_ESTACION ){
                        return UtilService::returnError($this, "m1El boleto con identificador: ".$id." está asociado a una factura otra estación, no se puede imprimir.");
                    }
                    
                    if($tipoDocumento->getId() !== TipoDocumentoBoleto::FACTURA && 
                            $tipoDocumento->getId() !== TipoDocumentoBoleto::FACTURA_ESPECIAL ){
                        return UtilService::returnError($this, "m1El boleto con identificador: ".$id." no es facturado.");
                    }
                    
                    if($boleto->getEstacionCreacion()->getId() !==  $estacionUsuario->getId()){
                        return UtilService::returnError($this, "m1La factura del boleto con identificador: ".$id." solamente la puede reimprimir un usuario de la estación: " . $boleto->getEstacionCreacion() . ".");
                    }
                    
                    $boletos[] = $boleto;
                    
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                    
                    

                    
                    
                    
                    
                    
                    
                    
                    
                    if($reprint === false){                    
                   
                    
                    $sAutorizacionUUIDr[] = $sAutorizacionUUIDs;
                    $sNumeroDTEr[] = $sNumeroDTEs;
                    $sSerieDTEr[] = $sSerieDTEs;
                    $sFechaCertificaDTEr[] = $sFechaCertificaDTEs;
                    
                    
                    
                    
                    

                    
                            $boletoFacturaGenerada = $boleto->getFacturaGenerada();
                            //Entity Manager
                            $em = $this->getDoctrine()->getEntityManager();
                            $posts = $em->getRepository("AcmeTerminalOmnibusBundle:FacturaGenerada");
                                                        
                            $post = $posts->find($boletoFacturaGenerada);
                            $post->setSAutorizacionUUIDsat($result['EmitirDteGenericoResult']['rwsAutorizacionUUID']);
                            $post->setSNumeroDTEsat($result['EmitirDteGenericoResult']['rwsNumeroDTE']);
                            $post->setSSerieDTEsat($result['EmitirDteGenericoResult']['rwsSerieDTE']);
                            $post->setSFechaCertificaDTEsat($result['EmitirDteGenericoResult']['rwsFechaCertificaDTE']);
//                            $post->setSAutorizacionUUIDsat("yzx741-rqp953");

                            //Persistimos en el objeto
                            $em->persist($post);
                            
                            //Insertarmos en la base de datos
                            $em->flush();
                        

                 
                            }                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                    
                    
                }
            }
            
            if($reprint === true){
                
                
                
                
                
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                  
                   

                $sCodigoInterno = $boleto->getFacturaGenerada()->getId();
                          
    
                require_once('lib/nusoap.php');

                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconFel.asmx?WSDL','wsdl');
//                $soapClient = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/wsforconfel.asmx?WSDL','wsdl');
//                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;                   
                $soapClient->debug_flag = true;
//                $soapClientMitochaTest->debug_flag = true;
                
                
//                $sCompanyId = $boleto->getFacturaGenerada()->getFactura()->getEmpresa()->getId();
                

//                if($sCompanyId === 1 || $sCompanyId === "1"){
                if($empresa->getId() === 1 || $empresa->getId() === "1"){                
                
                
                $param = array('sUsuario' => $sUsuario, 'sClave' => $sClave, 'sCodigoInterno' => $sCodigoInterno);
                $result = $soapClient->call('ConsultarCertificacionDTE', $param);
            
//                }else if($sCompanyId === 2 || $sCompanyId === "2"){
                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    
                    
                $param = array('sUsuario' => $sUsuarioMitocha, 'sClave' => $sClaveMitocha, 'sCodigoInterno' => $sCodigoInterno);
                $result = $soapClient->call('ConsultarCertificacionDTE', $param);                    
                    
                }else if($empresa->getId() === 7 || $empresa->getId() === "7"){                    
                    
                $param = array('sUsuario' => $sUsuarioRosita, 'sClave' => $sClaveRosita, 'sCodigoInterno' => $sCodigoInterno);
                $result = $soapClient->call('ConsultarCertificacionDTE', $param);                    
                    
                }
                
                
                $sAutorizacionUUIDs = array('sAutorizacionUUIDok' =>$result['ConsultarCertificacionDTEResult']['rwsAutorizacionUUID']);
                $sNumeroDTEs = array('sNumeroDTEok' =>$result['ConsultarCertificacionDTEResult']['rwsNumeroDTE']);
                $sSerieDTEs = array('sSerieDTEok' =>$result['ConsultarCertificacionDTEResult']['rwsSerieDTE']);
                $sFechaCertificaDTEs = array('sFechaCertificaDTEok' =>$result['ConsultarCertificacionDTEResult']['rwsFechaCertificaDTE']);
				
				
				
                $factGenAutorizadoUUIDsat = $boleto->getFacturaGenerada()->getSAutorizacionUUIDsat();
                
                
                if($factGenAutorizadoUUIDsat === NULL || trim($factGenAutorizadoUUIDsat) === ""){
                                

                    
                            $boletoFacturaGenerada = $boleto->getFacturaGenerada();
                            //Entity Manager
                            $em = $this->getDoctrine()->getEntityManager();
                            $posts = $em->getRepository("AcmeTerminalOmnibusBundle:FacturaGenerada");
                                                        
                            $post = $posts->find($boletoFacturaGenerada);
                            $post->setSAutorizacionUUIDsat($result['ConsultarCertificacionDTEResult']['rwsAutorizacionUUID']);
                            $post->setSNumeroDTEsat($result['ConsultarCertificacionDTEResult']['rwsNumeroDTE']);
                            $post->setSSerieDTEsat($result['ConsultarCertificacionDTEResult']['rwsSerieDTE']);
                            $post->setSFechaCertificaDTEsat($result['ConsultarCertificacionDTEResult']['rwsFechaCertificaDTE']);
//                            $post->setSAutorizacionUUIDsat("yzx741-rqp953");

                            //Persistimos en el objeto
                            $em->persist($post);
                            
                            //Insertarmos en la base de datos
                            $em->flush();                    
                    
                }				
                
                
         
                    $sAutorizacionUUIDr[] = $sAutorizacionUUIDs;
                    $sNumeroDTEr[] = $sNumeroDTEs;
                    $sSerieDTEr[] = $sSerieDTEs;
                    $sFechaCertificaDTEr[] = $sFechaCertificaDTEs;                   
                
                
                
                
                                                
                
                
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                  
                
                
                
                
                
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    foreach ($boletos as $boleto) {
                        $boletoBitacora = new BoletoBitacora();
                        $boletoBitacora->setEstado($boleto->getEstado());
                        $boletoBitacora->setFecha(new \DateTime());
                        $boletoBitacora->setUsuario($this->getUser());
                        $boletoBitacora->setDescripcion("Se reimprimió la factura del boleto.");
                        $boleto->addBitacoras($boletoBitacora);
                        $em->persist($boleto);
                    }
                    $em->flush();
                    $em->getConnection()->commit();
                    
                 } catch (\RuntimeException $exc) {
                    $em->getConnection()->rollback();
                    $mensaje = $exc->getMessage();
                    if(UtilService::startsWith($mensaje, 'm1')){
                        $mensajeServidor = $mensaje;
                    }else{
                        $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    }
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\ErrorException $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                } catch (\Exception $exc) {
                    $em->getConnection()->rollback();
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                    return UtilService::returnError($this, $mensajeServidor);
                }
            }
            
            $impresora = null;
            if($serieFactura === null){
                return UtilService::returnError($this, "m1No se pudo determinar la serie de factura.");
            }else{
                $impresora = $serieFactura->getImpresora();
                if($impresora === null){
                    return UtilService::returnError($this, "m1No se pudo determinar la impresora a utilizar.");
                }
            }
            
            $horaSalida = clone $salida->getFecha();
            if(count($boletos) !== 0 && $salida->getItinerario()->getRuta()->getEstacionOrigen()->getId() !== $boletos[0]->getEstacionOrigen()->getId()){
                $tiempo = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tiempo')
                            ->getTiempo($salida->getItinerario()->getRuta(), $boletos[0]->getEstacionOrigen(), $salida->getItinerario()->getTipoBus()->getClase());
                if($tiempo !== null){
                    $horaSalida->modify("+".$tiempo->getMinutos()." minutes"); 
                }
            }
            
            $option = array(
                'mensajeServidor' => '',
                'data' => $data,
                'info' => $info,
                'format' => $_format,
                'boletos' => $boletos,
                'impresora' => $impresora,
                'espacioLetras' => $impresora->getEspacioLetras(),
                'tipoImpresora' => $impresora->getTipoImpresora(),
                'silentPrint' => 'true',
                'horaSalida' => $horaSalida,                  
                    
// INI - EmitirDTEGenerico - 11/10/2020 23:00 Hrs. - EEAR
                    
                'sAutorizacionUUIDr' => $sAutorizacionUUIDr,
                'sNumeroDTEr' => $sNumeroDTEr,
                'sSerieDTEr' => $sSerieDTEr,
                'sFechaCertificaDTEr' => $sFechaCertificaDTEr
                    
// END - EmitirDTEGenerico - 11/10/2020 23:00 Hrs. - EEAR                      
                      
            );

            $plantilla = "";
            if($estacionUsuario->getPluginJavaActivo() === true){
                if($empresa->getId() === 1 || $empresa->getId() === "1"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:boletoFacturaPionera.text.twig';
                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:boletoFacturaMitocha.text.twig';
                }else if($empresa->getId() === 7 || $empresa->getId() === "7"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:boletoFacturaRosita.text.twig';
                }
            }else {
                $plantilla = 'AcmeTerminalOmnibusBundle:Print:boletoFactura.html.twig';
            }
            return $this->render($plantilla, $option);
            
        } catch (\RuntimeException $exc) {
            var_dump($exc);
            $mensaje = $exc->getMessage();
            if(UtilService::startsWith($mensaje, 'm1')){
                $mensajeServidor = $mensaje;
            }else{
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
            }
            return UtilService::returnError($this, $mensajeServidor);
        } catch (\ErrorException $exc) {
            var_dump($exc);
            return UtilService::returnError($this, "m1Ha ocurrido un error en el sistema");
        } catch (\Exception $exc) {
            var_dump($exc);
            return UtilService::returnError($this, "m1Ha ocurrido un error en el sistema");
        }
    }
    
    /**
     * @Route(path="/reimprimirFacturaBoleto.{_format}", name="ajaxReprintFacturaBoleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_REIMPRIMIR_FACTURA")
     */
    public function printReimprimirFacturaBoletoAction(Request $request, $_format = "html") {
        return $this->printFacturaBoletoAction($request, $_format, null, null, true);
    }
    
    /**
     * @Route(path="/sendEmailVoucherBoleto.{_format}", name="ajaxSendEmailVoucherBoleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function sendEmailVoucherBoletoAction(Request $request, $_format = "html") {
        
        try {
            
            $ids = $request->query->get('ids');
            if (is_null($ids)) {
                $ids = $request->request->get('ids');
            }
            $view = $this->printVoucherBoletoInternal($ids, $_format, true, 
                    "Se generó el voucher del boleto y se le envió por correo al usuario.");
            return $this->sendEmail($view, "fdn_boleto_".$ids);
            
        } catch (\RuntimeException $exc) {
            var_dump($exc);
            $mensaje = $exc->getMessage();
            if(UtilService::startsWith($mensaje, 'm1')){
                $mensajeServidor = $mensaje;
            }else{
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
            }
            return UtilService::returnError($this, $mensajeServidor);
        } catch (\ErrorException $exc) {
            var_dump($exc);
            return UtilService::returnError($this, "m1Ha ocurrido un error en el sistema");
        } catch (\Exception $exc) {
            var_dump($exc);
            return UtilService::returnError($this, "m1Ha ocurrido un error en el sistema");
        }
    }
    
    /**
     * @Route(path="/voucherBoleto.{_format}", name="ajaxPrintVoucherBoleto")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_BOLETO, ROLE_VENDEDOR_BOLETOS, ROLE_AGENCIA")
     */
    public function printVoucherBoletoAction(Request $request, $_format = "html") {
        
        try {
            
            $ids = $request->query->get('ids');
            if (is_null($ids)) {
                $ids = $request->request->get('ids');
            }
            
            $view = $this->printVoucherBoletoInternal($ids, $_format);
            if($_format === "html"){
                return new Response($view);
            }else if($_format === "pdf"){
                return $this->getPdfView($view, "fdn_boleto_".$ids);
            }
            
        } catch (\RuntimeException $exc) {
            var_dump($exc);
            $mensaje = $exc->getMessage();
            if(UtilService::startsWith($mensaje, 'm1')){
                $mensajeServidor = $mensaje;
            }else{
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
            }
            return UtilService::returnError($this, $mensajeServidor);
        } catch (\ErrorException $exc) {
            var_dump($exc);
            return UtilService::returnError($this, "m1Ha ocurrido un error en el sistema");
        } catch (\Exception $exc) {
            var_dump($exc);
            return UtilService::returnError($this, "m1Ha ocurrido un error en el sistema");
        }
    }
    
    public function getPathVoucherBoletoInternal($ids = null, $_format = "html") {
        $view = $this->printVoucherBoletoInternal($ids, $_format);
        $result = $this->getPdfFile($view, "fdn_boleto_".$ids, "portal");
        return "pdf/". $result['name'];    
    }
    
    private function printVoucherBoletoInternal($ids = null, $_format = "html", $check = true, 
            $observacion = "Se generó e imprimió el voucher del boleto.") {    
    
        if($ids === null || trim($ids) === "" || trim($ids) === "0"){
            throw new \RuntimeException("m1No se ha especificado el identificador del boleto para la impresión del voucher.");
        }
        
        $boletos = array();
        $mapHorariosSalidaByBoleto = array();
        $idsArray = explode(",", $ids); 
        $estacionUsuario = $this->getUser() !== null ? $this->getUser()->getEstacion() : null; 

        foreach ($idsArray as $id) {
            $boleto = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->find($id);
            if ($boleto === null) {
                throw new \RuntimeException("m1El boleto con id: ".$id. " no existe.");
            }
            if(intval($boleto->getEstado()->getId()) !== intval(EstadoBoleto::EMITIDO)){
                throw new \RuntimeException("m1Solamente se puede imprimir el voucher de un boleto que este en estado emitido.".
                        " El estado actual del boleto con id: " .$id. " es " . $boleto->getEstado()->getNombre() . ".");
            }
                
            $salida = $boleto->getSalida();
            $horaSalida = clone $salida->getFecha();
            if($salida->getItinerario()->getRuta()->getEstacionOrigen()->getId() !== $boleto->getEstacionOrigen()->getId()){
                $tiempo = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Tiempo')
                                ->getTiempo($salida->getItinerario()->getRuta(), $boleto->getEstacionOrigen(), $salida->getItinerario()->getTipoBus()->getClase());
                if($tiempo !== null){
                    $horaSalida->modify("+".$tiempo->getMinutos()." minutes"); 
                }
            }
            $mapHorariosSalidaByBoleto[$boleto->getId()] = $horaSalida;
            $boletos[] = $boleto;
        }
        
        if($check === true){
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                foreach ($boletos as $boleto) {
                    $boletoBitacora = new BoletoBitacora();
                    $boletoBitacora->setEstado($boleto->getEstado());
                    $boletoBitacora->setFecha(new \DateTime());
                    $boletoBitacora->setUsuario($this->getUser());
                    $boletoBitacora->setDescripcion($observacion);
                    $boleto->addBitacoras($boletoBitacora);
                    $em->persist($boleto);
                }
                $em->flush();
                $em->getConnection()->commit();
                    
             } catch (\RuntimeException $exc) {
                $em->getConnection()->rollback();
                $mensaje = $exc->getMessage();
                if(UtilService::startsWith($mensaje, 'm1')){
                    $mensajeServidor = $mensaje;
                }else{
                    $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                }
                return UtilService::returnError($this, $mensajeServidor);
            } catch (\ErrorException $exc) {
                $em->getConnection()->rollback();
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $mensajeServidor);
            } catch (\Exception $exc) {
                $em->getConnection()->rollback();
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
                return UtilService::returnError($this, $mensajeServidor);
            }
        }
            
        $impresora = new Impresora();
        if($estacionUsuario !== null){
            $impresoraOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ImpresoraOperaciones')
                                    ->getImpresoraOperacionesPorEstacion($estacionUsuario);
            if($impresoraOperaciones === null){
                throw new \RuntimeException("m1No se pudo determinar la configuración de impresoras a utilizar en la estación.");
            }
            $impresora = $impresoraOperaciones->getImpresoraBoleto();            
            if($impresora === null){
                $impresora = new Impresora();
            }
        }
        
        return $this->renderView("AcmeTerminalOmnibusBundle:Print:boletoVoucher.html.twig", array(
            'mensajeServidor' => '',
            'data' => '',
            'info' => '',
            'format' => $_format,
            'boletos' => $boletos,
            'impresora' => $impresora,
            'silentPrint' => 'true',
            'mapHorariosSalidaByBoleto' => $mapHorariosSalidaByBoleto
        ));
    }
    
    /**
     * @Route(path="/facturaEncomienda.{_format}", name="ajaxPrintFacturaEncomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function printFacturaEncomiendaAction(Request $request, $_format = "html", $data = null, $info = null, $reprint = false) {
        
        try {

            $ids = $request->query->get('ids');
            if (is_null($ids)) {
                $ids = $request->request->get('ids');
            }
                
            if($data !== null){ $ids = $data; }
                
            if($ids === null || trim($ids) === "" || trim($ids) === "0"){
                return UtilService::returnError($this, "m1No se ha especificado los identificadores de las encomiendas para la impresión de factura."); 
            }
            
            $idsEncomiendas = array();
            $encomiendas = array();
            $empresa = null;
            $tipoDocumento = null;
            $facturaGenerada = null;
            $estacionUsuario = $this->getUser()->getEstacion();
            if($estacionUsuario === null){
                return UtilService::returnError($this, "m1Para reimprimir una factura de encomienda el usuario debe estar asociado a la estación donde se creo la factura.");
            }
            
            $idsEncomiendasAux = explode(",", $ids);
            foreach ($idsEncomiendasAux as $id) {
                if($id !== null && trim($id) !== "" && trim($id) !== "0"){
                    $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
                    if ($encomienda === null) {
                        return UtilService::returnError($this, "m1La encomienda con id: ".$id. " no existe."); 
                    }
                    
                    $idsEncomiendas[] = $encomienda->getId();
                    $encomiendas[] = $encomienda;
                    
                    if($empresa === null){
                        $empresa = $encomienda->getEmpresa(); 
                    }else{
                        if($empresa !== $encomienda->getEmpresa()){
                            return UtilService::returnError($this, "m1No se puede imprimir encomiendas de diferentes empresas a la vez.");
                        }
                    }
                    
                    $idTipoDocumento = $encomienda->getTipoDocumento()->getId();
                    if($tipoDocumento === null){
                        $tipoDocumento = $encomienda->getTipoDocumento();
                    }else if ($tipoDocumento->getId() !== $idTipoDocumento){
                        return UtilService::returnError($this, "m1No se puede imprimir encomiendas con diferentes documentos.");
                    }
                    
                    if($tipoDocumento->getId() !== TipoDocumentoEncomienda::FACTURA && $tipoDocumento->getId() !== TipoDocumentoEncomienda::POR_COBRAR){
                        return UtilService::returnError($this, "m1Solamente se puede reimprimir encomiendas facturadas o por cobrar.");
                    }
                    
                    if($facturaGenerada === null){
                        $facturaGenerada = $encomienda->getFacturaGenerada();
                        if( $facturaGenerada === null){
                            if($idTipoDocumento === TipoDocumentoEncomienda::FACTURA){
                                return UtilService::returnError($this, "m1La encomienda con identificador: ".$id." no está asociada a factura.");
                            }else{
                                return UtilService::returnError($this, "m1La encomienda por cobrar con identificador: ".$id." aún no ha generado factura.");
                            }
                        }
                        if(UtilService::compararFechas($facturaGenerada->getFecha(), new \DateTime()) !== 0 ){
                            return UtilService::returnError($this, "m1Solamente se puede imprimir una factura de encomienda el mismo día que se creo.");
                        } 
                        if($facturaGenerada->getEstacion()->getId() !== $estacionUsuario->getId()){
                            return UtilService::returnError($this, "m1La factura de encomienda con identificador: ".$id." solamente la puede imprimir un usuario de la estación: " . $facturaGenerada->getEstacion() . ".");
                        }
                    }else if($facturaGenerada !== $encomienda->getFacturaGenerada()){
                        return UtilService::returnError($this, "m1Las encomiendas no pertenecen a la misma factura.");
                    }

                    if($idTipoDocumento === TipoDocumentoEncomienda::FACTURA){
                        $ultimoEstado = $encomienda->getUltimoEstado();
                        if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA){
                            return UtilService::returnError($this, "m1Solamente se puede imprimir la factura de una encomienda que este en estado recibida. El estado actual es: " . $ultimoEstado->getNombre() . ".");
                        }
                        $encomiendasFacturas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->getEncomiendasPorFactura($facturaGenerada->getId());
                        foreach ($encomiendasFacturas as $itemEncomienda) {
                            $ultimoEstado = $itemEncomienda->getUltimoEstado();
                            if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA){
                                return UtilService::returnError($this, "m1La encomienda con identificador: " .$itemEncomienda->getId()." pertenece a la misma factura de la encomienda seleccionada y su estado acual es: " . $ultimoEstado->getNombre() . ". Solamente se puede imprimir la factura de encomiendas que esten en estado recibida.");
                            }
                            if(!in_array($itemEncomienda->getId(), $idsEncomiendas)){
                                 $idsEncomiendas[] = $itemEncomienda->getId();
                            }
                            if(!in_array($itemEncomienda, $encomiendas)){
                                 $encomiendas[] = $itemEncomienda;
                            }
                        }
                    }
                    else if($idTipoDocumento === TipoDocumentoEncomienda::POR_COBRAR){
                        $ultimoEstado = $encomienda->getUltimoEstado();
                        if($ultimoEstado->getId() !== EstadoEncomienda::ENTREGADA){
                            return UtilService::returnError($this, "m1Solamente se puede imprimir la factura de una encomienda por cobrar que este en estado entregada. El estado actual es: " . $ultimoEstado->getNombre() . ".");
                        }
                        $encomiendasFacturas = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->getEncomiendasPorFactura($facturaGenerada->getId());
                        foreach ($encomiendasFacturas as $itemEncomienda) {
                            $ultimoEstado = $itemEncomienda->getUltimoEstado();
                            if($ultimoEstado->getId() !== EstadoEncomienda::ENTREGADA){
                                return UtilService::returnError($this, "m1La encomienda con identificador: " .$itemEncomienda->getId()." pertenece a la misma factura de la encomienda seleccionada y su estado acual es: " . $ultimoEstado->getNombre() . ". Solamente se puede imprimir la factura de encomiendas que esten en estado entregada.");
                            }
                            if(!in_array($itemEncomienda->getId(), $idsEncomiendas)){
                                 $idsEncomiendas[] = $itemEncomienda->getId();
                            }
                            if(!in_array($itemEncomienda, $encomiendas)){
                                 $encomiendas[] = $itemEncomienda;
                            }
                        }
                    }else{
                        return UtilService::returnError($this, "m1La encomienda con identificador: ".$id." no está asociada a factura.");
                    }
                }
            }
            
            
            
            
            
            
            
            
            
            
            
            
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.
                    
                    
//    CREDENCIALES EN ENTORNO DE PRODUCCIÓN DE e-FORCON PARA ROSITA       
    $sUsuarioRosita = 'proper-transrosita';
    $sClaveRosita = 'Transrosita2021*';                     

 //    CREDENCIALES EN ENTORNO DE PRODUCCIÓN DE e-FORCON PARA MITOCHA       
	$sUsuarioMitocha = 'operpro1-mayaoro';   
	$sClaveMitocha = 'M@y$2781%Qa5!!';				
        
           
    $sUsuario = 'operws1-fdn';
    $sClave = '$$FDN@2020Fel';     
    
        
    
    $sFacturaId = $encomienda->getFacturaGenerada()->getId();
    
    $sCompanyNit = $encomienda->getFacturaGenerada()->getFactura()->getEmpresa()->getNit();
    $sEmpresaNit = preg_replace('([^A-Za-z0-9])', '', $sCompanyNit);
    
    $sNumEstablecimientoSatPionera = $encomienda->getFacturaGenerada()->getEstacion()->getNumEstablecimientoSat();       
      
    $sNumEstablecimientoSatMitocha = $encomienda->getFacturaGenerada()->getEstacion()->getNumEstablecimientoSatMitocha();    

    $sNumEstablecimientoSatRosita = $encomienda->getFacturaGenerada()->getEstacion()->getNumEstablecimientoSatRosita();	
    
    $sNitClienteDocumento = $encomienda->getClienteDocumento()->getNit();
    $sClienteDocumentoNit = preg_replace('([^A-Za-z0-9])', '', $sNitClienteDocumento);     
    
    $sNomClienteDocumento = $encomienda->getClienteDocumento()->__toString();    
    
    $sNomClienteDestinatario = $encomienda->getClienteDestinatario()->__toString();
    
    $sFechaFactGenerada = $encomienda->getFacturaGenerada()->getFecha();
    
    $sFechaFacturaGenerada = $sFechaFactGenerada->format('Y-m-d H:i:s');
    
    $sMonedaSigla = $encomienda->getFacturaGenerada()->getMoneda()->getSigla();          
    
    $sEstacionOrigenNom = $encomienda->getEstacionOrigen()->getNombre();
    
    $sEstacionDestinoNom = $encomienda->getEstacionDestino()->getNombre(); 
    
    
    $sUsuarioCreacion = $encomienda->getFacturaGenerada()->getUsuario()->getFullName();
    
   
           
            
    if(count($idsEncomiendasAux) === 1){    
        
        
        
        
    $sPrimeraEncomiendaCantidad = $encomiendas[0]->getCantidad();
    
    $sPrimeraEncomiendaId = $encomiendas[0]->getId();
    
    $sPrimeraEncomiendaTipoEncomienda = $encomiendas[0]->getTipoEncomienda()->getNombre();
    
    $sPrimeraEncomiendaDescripcion = $encomiendas[0]->getDescripcion();
    
    $sPrimeraEncomiendaPrecioCalculado = $encomiendas[0]->getPrecioCalculado();           
        
        
    
    
    $sXmlDtePionera = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatPionera.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>2</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>                                
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>
			</definicionDP>
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';
    
    
    
    $sXmlDteMitocha = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatMitocha.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>
			</definicionDP>
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';



    $sXmlDteRosita = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatRosita.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>
			</definicionDP>
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';
                        
                    
    }else if(count($idsEncomiendasAux) === 2){
        
        
        
        
        
        
    $sPrimeraEncomiendaCantidad = $encomiendas[0]->getCantidad();
    
    $sPrimeraEncomiendaId = $encomiendas[0]->getId();
    
    $sPrimeraEncomiendaTipoEncomienda = $encomiendas[0]->getTipoEncomienda()->getNombre();
    
    $sPrimeraEncomiendaDescripcion = $encomiendas[0]->getDescripcion();
    
    $sPrimeraEncomiendaPrecioCalculado = $encomiendas[0]->getPrecioCalculado();   
    
        
    
    $sSegundaEncomiendaCantidad = $encomiendas[1]->getCantidad();
    
    $sSegundaEncomiendaId = $encomiendas[1]->getId();
    
    $sSegundaEncomiendaTipoEncomienda = $encomiendas[1]->getTipoEncomienda()->getNombre();
    
    $sSegundaEncomiendaDescripcion = $encomiendas[1]->getDescripcion();
    
    $sSegundaEncomiendaPrecioCalculado = $encomiendas[1]->getPrecioCalculado();            
        
        
        
        
        
            $sXmlDtePionera = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatPionera.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>2</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>       
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>        
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';                
            
            
            
            $sXmlDteMitocha = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatMitocha.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>        
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';   



                     $sXmlDteRosita = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatRosita.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>        
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';
        
        
    }else if(count($idsEncomiendasAux) === 3){
        
        
        
        
    $sPrimeraEncomiendaCantidad = $encomiendas[0]->getCantidad();
    
    $sPrimeraEncomiendaId = $encomiendas[0]->getId();
    
    $sPrimeraEncomiendaTipoEncomienda = $encomiendas[0]->getTipoEncomienda()->getNombre();
    
    $sPrimeraEncomiendaDescripcion = $encomiendas[0]->getDescripcion();
    
    $sPrimeraEncomiendaPrecioCalculado = $encomiendas[0]->getPrecioCalculado();   
    
        
    
    $sSegundaEncomiendaCantidad = $encomiendas[1]->getCantidad();
    
    $sSegundaEncomiendaId = $encomiendas[1]->getId();
    
    $sSegundaEncomiendaTipoEncomienda = $encomiendas[1]->getTipoEncomienda()->getNombre();
    
    $sSegundaEncomiendaDescripcion = $encomiendas[1]->getDescripcion();
    
    $sSegundaEncomiendaPrecioCalculado = $encomiendas[1]->getPrecioCalculado();    
    
    
    
    $sTerceraEncomiendaCantidad = $encomiendas[2]->getCantidad();
    
    $sTerceraEncomiendaId = $encomiendas[2]->getId();
    
    $sTerceraEncomiendaTipoEncomienda = $encomiendas[2]->getTipoEncomienda()->getNombre();
    
    $sTerceraEncomiendaDescripcion = $encomiendas[2]->getDescripcion();
    
    $sTerceraEncomiendaPrecioCalculado = $encomiendas[2]->getPrecioCalculado();        
        
        
        
        
        
            $sXmlDtePionera = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatPionera.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>2</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>       
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>     
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
                                                            <definicionDP>
				<numeroItem>3</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sTerceraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sTerceraEncomiendaId.'. TIPO: '.$sTerceraEncomiendaTipoEncomienda.': '.$sTerceraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sTerceraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';
            
            
            
            $sXmlDteMitocha = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatMitocha.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>     
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
                                                            <definicionDP>
				<numeroItem>3</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sTerceraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sTerceraEncomiendaId.'. TIPO: '.$sTerceraEncomiendaTipoEncomienda.': '.$sTerceraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sTerceraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';  



            $sXmlDteRosita = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatRosita.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>     
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
                                                            <definicionDP>
				<numeroItem>3</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sTerceraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sTerceraEncomiendaId.'. TIPO: '.$sTerceraEncomiendaTipoEncomienda.': '.$sTerceraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sTerceraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';          
        
        
    }else if(count($idsEncomiendasAux) === 4){       
        
        
        
    $sPrimeraEncomiendaCantidad = $encomiendas[0]->getCantidad();
    
    $sPrimeraEncomiendaId = $encomiendas[0]->getId();
    
    $sPrimeraEncomiendaTipoEncomienda = $encomiendas[0]->getTipoEncomienda()->getNombre();
    
    $sPrimeraEncomiendaDescripcion = $encomiendas[0]->getDescripcion();
    
    $sPrimeraEncomiendaPrecioCalculado = $encomiendas[0]->getPrecioCalculado();   
    
        
    
    $sSegundaEncomiendaCantidad = $encomiendas[1]->getCantidad();
    
    $sSegundaEncomiendaId = $encomiendas[1]->getId();
    
    $sSegundaEncomiendaTipoEncomienda = $encomiendas[1]->getTipoEncomienda()->getNombre();
    
    $sSegundaEncomiendaDescripcion = $encomiendas[1]->getDescripcion();
    
    $sSegundaEncomiendaPrecioCalculado = $encomiendas[1]->getPrecioCalculado();    
    
    
    
    $sTerceraEncomiendaCantidad = $encomiendas[2]->getCantidad();
    
    $sTerceraEncomiendaId = $encomiendas[2]->getId();
    
    $sTerceraEncomiendaTipoEncomienda = $encomiendas[2]->getTipoEncomienda()->getNombre();
    
    $sTerceraEncomiendaDescripcion = $encomiendas[2]->getDescripcion();
    
    $sTerceraEncomiendaPrecioCalculado = $encomiendas[2]->getPrecioCalculado();
    
    
    
    $sCuartaEncomiendaCantidad = $encomiendas[3]->getCantidad();
    
    $sCuartaEncomiendaId = $encomiendas[3]->getId();
    
    $sCuartaEncomiendaTipoEncomienda = $encomiendas[3]->getTipoEncomienda()->getNombre();
    
    $sCuartaEncomiendaDescripcion = $encomiendas[3]->getDescripcion();
    
    $sCuartaEncomiendaPrecioCalculado = $encomiendas[3]->getPrecioCalculado();                 
        
        
        
        
    $sXmlDtePionera = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatPionera.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>2</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>       
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>      
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
                                                            <definicionDP>
				<numeroItem>3</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sTerceraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sTerceraEncomiendaId.'. TIPO: '.$sTerceraEncomiendaTipoEncomienda.': '.$sTerceraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sTerceraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>4</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sCuartaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sCuartaEncomiendaId.'. TIPO: '.$sCuartaEncomiendaTipoEncomienda.'. '.$sCuartaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>   
                                                                                <totalPorLinea>'.$sCuartaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>                        
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';             
    
    
    
    $sXmlDteMitocha = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatMitocha.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>      
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
                                                            <definicionDP>
				<numeroItem>3</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sTerceraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sTerceraEncomiendaId.'. TIPO: '.$sTerceraEncomiendaTipoEncomienda.': '.$sTerceraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sTerceraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>4</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sCuartaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sCuartaEncomiendaId.'. TIPO: '.$sCuartaEncomiendaTipoEncomienda.'. '.$sCuartaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>   
                                                                                <totalPorLinea>'.$sCuartaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>                        
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>';   



    $sXmlDteRosita = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<plantilla xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<dte>
		<encabezadoPrincipal>
			<codigoInternoEmisor>'.$sFacturaId.'</codigoInternoEmisor>
			<nitEmisor>'.$sEmpresaNit.'</nitEmisor>
			<numeroEstablecimiento>'.$sNumEstablecimientoSatRosita.'</numeroEstablecimiento>
			<tipoDTE>FACT</tipoDTE>
			<usoComercialDTE>LOCAL</usoComercialDTE>
			<tipoReceptor>N</tipoReceptor>
			<idReceptor>'.$sClienteDocumentoNit.'</idReceptor>
			<fechaEmision>'.$sFechaFacturaGenerada.'</fechaEmision>
			<nombreReceptor>'.$sNomClienteDocumento.'</nombreReceptor>
			<moneda>'.$sMonedaSigla.'</moneda>
			<frases>
				<definicionFrase>
					<codigoFrase>1</codigoFrase>
					<codigoEscenario>1</codigoEscenario>
				</definicionFrase>
			</frases>
			<complementos>
				<numeroAbonosCAMB/>
				<fechaInicialVenceCAMB/>
				<montoAbonosCAMB/>
				<diasEntreAbonosCAMB/>
				<nombreConsignatarioEXP/>
				<direccionConsignatarioEXP/>
				<incotermEXP/>
				<codigoConsignatarioEXP/>
				<nombreCompradorEXP/>
				<direccionCompradorEXP/>
				<codigoCompradorEXP/>
				<otraReferenciaEXP/>
				<nombreExportadorEXP/>
				<codigoExportadorEXP/>
				<tipoRegimenDTE/>
				<numeroAutorizacion/>
				<motivoAjuste/>
				<fechaEmisionOrigen/>
				<numeroOrigenFace/>
				<serieOrigenFace/>
			</complementos>
		</encabezadoPrincipal>
		<detallePrincipal>
			<definicionDP>
				<numeroItem>1</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sPrimeraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sPrimeraEncomiendaId.'. TIPO: '.$sPrimeraEncomiendaTipoEncomienda.': '.$sPrimeraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sPrimeraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>2</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sSegundaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sSegundaEncomiendaId.'. TIPO: '.$sSegundaEncomiendaTipoEncomienda.'. '.$sSegundaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>      
                                                                                <totalPorLinea>'.$sSegundaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>			
                                                            <definicionDP>
				<numeroItem>3</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sTerceraEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sTerceraEncomiendaId.'. TIPO: '.$sTerceraEncomiendaTipoEncomienda.': '.$sTerceraEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>
                                                                                <totalPorLinea>'.$sTerceraEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>
			<definicionDP>
				<numeroItem>4</numeroItem>
				<bienServicio>S</bienServicio>
				<nombreCortoImpuesto>IVA (AFECTO)</nombreCortoImpuesto>
				<cantidad>'.$sCuartaEncomiendaCantidad.'</cantidad>
				<metrica>UNI</metrica>
				<valorTasaMunicipal>0</valorTasaMunicipal>
				<descripcion>ID: '.$sCuartaEncomiendaId.'. TIPO: '.$sCuartaEncomiendaTipoEncomienda.'. '.$sCuartaEncomiendaDescripcion.'</descripcion>
				<precioUnitario>0</precioUnitario>
				<descuento>0</descuento>   
                                                                                <totalPorLinea>'.$sCuartaEncomiendaPrecioCalculado.'</totalPorLinea>                                
			</definicionDP>                        
		</detallePrincipal>
		<encabezadoExtra>
			<definicionEE>
				<codigoEtiquetaEE>35</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sUsuarioCreacion.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>134</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sNomClienteDestinatario.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>130</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionOrigenNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>131</codigoEtiquetaEE>
				<valorEtiquetaEE>'.$sEstacionDestinoNom.'</valorEtiquetaEE>
			</definicionEE>
			<definicionEE>
				<codigoEtiquetaEE>135</codigoEtiquetaEE>
				<valorEtiquetaEE>E</valorEtiquetaEE>
			</definicionEE>                        
		</encabezadoExtra>
	</dte>
</plantilla>'; 
    
    }            
            
            
            
            
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.            
            

            
            
            $impresora = $facturaGenerada->getFactura()->getImpresora();
            if($impresora === null){
                return UtilService::returnError($this, "m1No se pudo determinar la impresora para facturar.");
            }
            
            $clienteDestinatarioSTR = "";
            $nit = "";
            if($tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
                $clienteDestinatarioSTR = $encomiendas[0]->getClienteDestinatario()->getNombre();
                $telefonoClienteDestinatario = $encomiendas[0]->getClienteDestinatario()->getTelefono();
                
                
                
                
                
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                  
                
                
//   INI - EmitirDTEGenerico Encomienda FDN - RAEE - 16/12/2021 12:12 Hrs. -  rePrintFactEncomienda.            
                
                
                    if($reprint === false){                 
         
                
                require_once('lib/nusoap.php');
                
//                $soapClient = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/wsforconfel.asmx?WSDL','wsdl');
                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconFel.asmx?WSDL','wsdl');
//                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;                    
                $soapClient->debug_flag = true;
//                $soapClientMitochaTest->debug_flag = true;	



				
                $sCompanyId = $encomienda->getFacturaGenerada()->getFactura()->getEmpresa()->getId();
                

                if($sCompanyId === 1 || $sCompanyId === "1"){  
//                if($empresa->getId() === 1 || $empresa->getId() === "1"){                    
                
                $param = array('sUsuario' => $sUsuario, 'sClave' => $sClave, 'sXmlDte' => $sXmlDtePionera);
                $result = $soapClient->call('EmitirDteGenerico', $param);
            
                }else if($sCompanyId === 2 || $sCompanyId === "2"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    
                    
                $param = array('sUsuario' => $sUsuarioMitocha, 'sClave' => $sClaveMitocha, 'sXmlDte' => $sXmlDteMitocha);
                $result = $soapClient->call('EmitirDteGenerico', $param);                    
                    
                }else if($sCompanyId === 7 || $sCompanyId === "7"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    
                    
                $param = array('sUsuario' => $sUsuarioRosita, 'sClave' => $sClaveRosita, 'sXmlDte' => $sXmlDteRosita);
                $result = $soapClient->call('EmitirDteGenerico', $param);                    
                    
                }
            
    
    
                   
                               
                            $encomiendaFacturaGenerada = $encomiendas[0]->getFacturaGenerada();
                            //Entity Manager
                            $em = $this->getDoctrine()->getEntityManager();
                            $posts = $em->getRepository("AcmeTerminalOmnibusBundle:FacturaGenerada");
                                                        
                            $post = $posts->find($encomiendaFacturaGenerada);
                            $post->setSAutorizacionUUIDsat($result['EmitirDteGenericoResult']['rwsAutorizacionUUID']);
                            $post->setSNumeroDTEsat($result['EmitirDteGenericoResult']['rwsNumeroDTE']);
                            $post->setSSerieDTEsat($result['EmitirDteGenericoResult']['rwsSerieDTE']);
                            $post->setSFechaCertificaDTEsat($result['EmitirDteGenericoResult']['rwsFechaCertificaDTE']);

                            //Persistimos en el objeto
                            $em->persist($post);
                            
                            //Insertarmos en la base de datos
                            $em->flush();     
                        
       
                            }                
                
    
//   END - EmitirDTEGenerico Encomienda FDN - RAEE - 16/12/2021 12:12 Hrs. -  rePrintFactEncomienda
                              
                
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                  
                
                
                
                
                
                
                
                if(!is_null($telefonoClienteDestinatario) && trim($telefonoClienteDestinatario) !== ""){
                    $clienteDestinatarioSTR .= " - " . $telefonoClienteDestinatario;
                }
                $nit = $encomiendas[0]->getClienteRemitente()->getNit();
            }else if($tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
                $clienteDestinatarioSTR = $encomiendas[0]->getClienteDocumento()->getNombre();
                $nit = $encomiendas[0]->getClienteDocumento()->getNit();
                
                
                
                
                
                
                
//   INI - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                  
                
                
                
         
                
                require_once('lib/nusoap.php');
                
//                $soapClient = new \nusoap_client('http://pruebasfel.eforcon.com/feldev/wsforconfel.asmx?WSDL','wsdl');
                $soapClient = new \nusoap_client('https://fel.eforcon.com/feldev/WSForconFel.asmx?WSDL','wsdl');
//                *. nits
                $soapClient->soap_defencoding = 'UTF-8';
                $soapClient->decode_utf8 = false;                    
                $soapClient->debug_flag = true;
//                $soapClientMitochaTest->debug_flag = true;	




				
                $sCompanyId = $encomienda->getFacturaGenerada()->getFactura()->getEmpresa()->getId();
                

                if($sCompanyId === 1 || $sCompanyId === "1"){          
//                if($empresa->getId() === 1 || $empresa->getId() === "1"){                    
                
                    $param = array('sUsuario' => $sUsuario, 'sClave' => $sClave, 'sXmlDte' => $sXmlDtePionera);
                    $result = $soapClient->call('EmitirDteGenerico', $param);
            
                }else if($sCompanyId === 2 || $sCompanyId === "2"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    
                    
                    $param = array('sUsuario' => $sUsuarioMitocha, 'sClave' => $sClaveMitocha, 'sXmlDte' => $sXmlDteMitocha);
                    $result = $soapClient->call('EmitirDteGenerico', $param);
                    
                }else if($sCompanyId === 7 || $sCompanyId === "7"){
//                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){                    
                    
                    $param = array('sUsuario' => $sUsuarioRosita, 'sClave' => $sClaveRosita, 'sXmlDte' => $sXmlDteRosita);
                    $result = $soapClient->call('EmitirDteGenerico', $param);
                    
                }
            
    
    
    
                            
                            $encomiendaFacturaGenerada = $encomiendas[0]->getFacturaGenerada();
                            //Entity Manager
                            $em = $this->getDoctrine()->getEntityManager();
                            $posts = $em->getRepository("AcmeTerminalOmnibusBundle:FacturaGenerada");
                                                        
                            $post = $posts->find($encomiendaFacturaGenerada);
                            $post->setSAutorizacionUUIDsat($result['EmitirDteGenericoResult']['rwsAutorizacionUUID']);
                            $post->setSNumeroDTEsat($result['EmitirDteGenericoResult']['rwsNumeroDTE']);
                            $post->setSSerieDTEsat($result['EmitirDteGenericoResult']['rwsSerieDTE']);
                            $post->setSFechaCertificaDTEsat($result['EmitirDteGenericoResult']['rwsFechaCertificaDTE']);

                            //Persistimos en el objeto
                            $em->persist($post);
                            
                            //Insertarmos en la base de datos
                            $em->flush();                   
                
                
                              
                
                
//   END - EmitirDTEGenerico Boleto FDN - RAEE - 20/10/2020 01:00 Hrs. - Modified to integrate FEL Maya de Oro 28/05/2021 00:00Hrs.                              
                
                
                
                
                
                
                
            }
            if(trim($nit) === ""){
                
                
                
                $nit = "CF";                
                
                
                
            }

            $plantilla = "";
            if($estacionUsuario->getPluginJavaActivo() === true){
                if($empresa->getId() === 1 || $empresa->getId() === "1"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:encomiendaFacturaPionera.text.twig';
                }else if($empresa->getId() === 2 || $empresa->getId() === "2"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:encomiendaFacturaMitocha.text.twig';
                }else if($empresa->getId() === 7 || $empresa->getId() === "7"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:encomiendaFacturaRosita.text.twig';
                }
            }else {
               if($empresa->getId() === 1 || $empresa->getId() === "1"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:encomiendaFacturaPionera.html.twig';
               }else if($empresa->getId() === 2 || $empresa->getId() === "2"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:encomiendaFacturaMitocha.html.twig';
               }else if($empresa->getId() === 7 || $empresa->getId() === "7"){
                    $plantilla = 'AcmeTerminalOmnibusBundle:Print:encomiendaFacturaRosita.html.twig';
               }
            }
            
            $idsEncomiendas = array_unique($idsEncomiendas);
            $encomiendas = array_unique($encomiendas);
                    
            $option = array(
                'mensajeServidor' => '',
                'data' => $data,
                'info' => $info,
                'format' => $_format,
                'empresa' => $empresa,
                'idsEncomiendas' => implode(",", $idsEncomiendas),
                'encomiendas' => $encomiendas,
                'primeraEncomienda' => $encomiendas[0],
                'facturaGenerada' => $facturaGenerada,
                'clienteDestinatarioSTR' => $clienteDestinatarioSTR,
                'nitSTR' => $nit,
                'impresora' => $impresora,
                'espacioLetras' => $impresora->getEspacioLetras(),
                'tipoImpresora' => $impresora->getTipoImpresora(),
                'silentPrint' => 'true'
            );
            
            return $this->render($plantilla, $option);

        } catch (\RuntimeException $exc) {
            var_dump($exc);
            $mensaje = $exc->getMessage();
            if(UtilService::startsWith($mensaje, 'm1')){
                $mensajeServidor = $mensaje;
            }else{
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
            }
            return UtilService::returnError($this, $mensajeServidor);
        }  catch (\Exception $exc) {
            var_dump($exc);
            return UtilService::returnError($this);
        }
    }
    
    /**
     * @Route(path="/reimprimirFacturaEncomienda.{_format}", name="ajaxReprintFacturaEncomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_SUPERVISOR_ENCOMIENDA")
     */
    public function printReimprimirFacturaEncomiendaAction(Request $request, $_format = "html") {
        return $this->printFacturaEncomiendaAction($request, $_format, null, null, true);
    }
    
    /**
     * @Route(path="/datosEncomienda.{_format}", name="ajaxPrintDatosEncomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function printDatosEncomiendaAction(Request $request, $_format = 'html', $data = null, $info = null) {
        
        try {
            $ids = $request->query->get('ids');
            if (is_null($ids)) {
                $ids = $request->request->get('ids');
            }
            
            if($data !== null){ $ids = $data; }
             
            if($ids === null || trim($ids) === "" || trim($ids) === "0"){
                return UtilService::returnError($this, "m1No se ha especificado los identificadores de las encomiendas para la impresión.");
            }
            
            $idsEncomiendas = array();
            $encomiendas = array();
            $empresa = null;
            $existeEfectivo = false;
            $estacionUsuario = $this->getUser()->getEstacion();
            
            $idsEncomiendasAux = explode(",", $ids);
            foreach ($idsEncomiendasAux as $id) {
                if($id !== null && trim($id) !== "" && trim($id) !== "0"){
                    $encomienda = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->find($id);
                    if ($encomienda === null) {
                        return UtilService::returnError($this, "m1La encomienda con id: ".$id. " no existe.");
                    }
                    if($empresa === null){
                        $empresa = $encomienda->getEmpresa(); 
                    }else{
                        if($empresa !== $encomienda->getEmpresa()){
                            return UtilService::returnError($this, "m1No se puede imprimir encomiendas de diferentes empresas a la vez.");
                        }
                    }
                    
                    $ultimoEstado = $encomienda->getUltimoEstado();
                    if($ultimoEstado->getId() !== EstadoEncomienda::RECIBIDA){
                        return UtilService::returnError($this, "m1Solamente se puede imprimir una encomienda que este en estado recibida. El estado actual es: " . $ultimoEstado->getNombre() . ".");
                    }
                    if($encomienda->getFechaCreacion() !== null && UtilService::compararFechas($encomienda->getFechaCreacion(), new \DateTime()) !== 0){
                        return UtilService::returnError($this, "m1Solamente se puede imprimir una encomienda el mismo día que se creo.");
                    }
                    if($estacionUsuario !== null && $estacionUsuario->getId() !== $encomienda->getEstacionCreacion()->getId()){
                        return UtilService::returnError($this, "m1La encomienda con identificador: ".$id." solamente la puede imprimir un usuario de la estación: " . $encomienda->getEstacionCreacion() . ".");
                    }
                    //Si la encomienda es efectivo no hay que imprimirle sus datos ya que no es un paquete que se envie.
                    if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
                        $existeEfectivo = true;
                        continue;
                    }
                    
                    $idsEncomiendas[] = $encomienda->getId();
                    $encomiendas[] = $encomienda;
                }
            }
            
            $impresora = new Impresora();
            if($estacionUsuario !== null){
                $impresoraOperaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ImpresoraOperaciones')
                                    ->getImpresoraOperacionesPorEstacion($estacionUsuario);
                if($impresoraOperaciones === null){
                    return UtilService::returnError($this, "m1No se pudo determinar la configuración de impresoras a utilizar en la estación.");
                }
                $impresora = $impresoraOperaciones->getImpresoraEncomienda();            
                if($impresora === null){
                    $impresora = new Impresora();
                }
            }
            
            $idsEncomiendas = array_unique($idsEncomiendas);
            $encomiendas = array_unique($encomiendas);
            
            $option = array(
                'mensajeServidor' => '',
                'data' => $data,
                'info' => $info,
                'format' => $_format,
                'empresa' => $empresa,
                'idsEncomiendas' => $idsEncomiendas,
                'encomiendas' => $encomiendas,
                'impresora' => $impresora,
                'silentPrint' => 'true'
            );
            
            $plantilla = 'AcmeTerminalOmnibusBundle:Print:encomiendaDatos.html.twig';
            
            if($_format === "html"){
                return $this->render($plantilla, $option);
            }else if($_format === "pdf"){
                return $this->getPdfView($this->renderView($plantilla, $option), "impresion_datos_encomienda");
            }
            
        } catch (\RuntimeException $exc) {
            var_dump($exc);
            $mensaje = $exc->getMessage();
            if(UtilService::startsWith($mensaje, 'm1')){
                $mensajeServidor = $mensaje;
            }else{
                $mensajeServidor = "m1Ha ocurrido un error en el sistema";
            }
            return UtilService::returnError($this, $mensajeServidor);
        }  catch (\Exception $exc) {
            var_dump($exc);
            return UtilService::returnError($this);
        }
    }
    
    /**
     * @Route(path="/reimprimirDatosEncomienda.{_format}", name="ajaxReprintDatosEncomienda")
     * @Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_ADMIN_ESTACION, ROLE_ADMIN_EMPRESA, ROLE_SUPERVISOR_ENCOMIENDA, ROLE_RECEPTOR_ENCOMIENDAS")
     */
    public function printReimprimirDatosEncomiendaAction(Request $request, $_format = "html") {
        return $this->printDatosEncomiendaAction($request, $_format);
    }
    
    private function sendEmail($html, $name){
        $correos = array($this->getUser()->getEmailCanonical());
        if($this->getUser()->getEstacion() !== null){
            $listaCorreo = $this->getUser()->getEstacion()->getListaCorreo();
            foreach ($listaCorreo as $item) {
                if($item->getActivo() === true){
                    $correos[] = $item->getCorreo();
                }
            }
            $correos = array_unique($correos);
        }
        var_dump($correos);
        $result = $this->getPdfFile($html, $name);
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $subject = "FDN_" . $now . ". Boletos registrados en fuente del norte."; 
        UtilService::sendEmail($this->container, $subject, $correos, $this->container->get("templating")->render('AcmeTerminalOmnibusBundle:Email:notificacion_voucher_boleto.html.twig', array(
                
        )), array($result['path']));    
        
        return $this->render('AcmeTerminalOmnibusBundle:Commun:respuestaServidor.html.twig', array(
            'mensajeServidor' => "m0"
        ));
    }
    
    private function getPdfView($html, $name){
        $result = $this->getPdfFile($html, $name);
        return $this->render('AcmeTerminalOmnibusBundle:Commun:assetPath.html.twig', array(
            'path' => "pdf/". $result['name']
        ));
    }
    
    private function getPdfFile($html, $name, $prefijo = ""){
        $fecha = new \DateTime();
        $username = $this->getUser() !== null ? $this->getUser()->getUsername() : ""; 
        $outfilename = $prefijo . $username . "_" . $fecha->format('Y.m.d_H.i.s') . "_" . $name . ".pdf";
        $pathFile = $this->getRootDir() . "pdf\\" . $outfilename;
        $this->get('knp_snappy.pdf')->generateFromHtml($html, $pathFile, array(
            'lowquality' => false,
            'print-media-type' => true,
            'encoding' => 'utf-8',
            'page-size' => 'Letter',
            'margin-bottom' => 0,
            'margin-left' => 0,
            'margin-right' => 0,
            'margin-top' => 0,
            'zoom' => 1.3
        ));
        return array(
            'path' => $pathFile,
            'name' => $outfilename
        );
    }
    
    protected function getRootDir()
    {
        return __DIR__.'\\..\\..\\..\\..\\web\\';
    }
}

?>
