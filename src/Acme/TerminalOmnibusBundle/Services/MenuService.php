<?php
namespace Acme\TerminalOmnibusBundle\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MenuService {
      
    private $router;       
    private $securityContext;        
    private $metadataFactory;
    protected $container;
    protected $logger = null;
    protected $ajax = true;
   
   public function __construct($container, $logger = null) { 
        $this->container = $container;
        $this->logger = $logger;
    }
    
    public function renderMenu($nameMenu){
        switch ($nameMenu) {
            case 'lateralIzq':
                return $this->renderMenuLateralIzq($nameMenu);
            case 'menuFacturas':
                return $this->renderMenuModuloFacturas($nameMenu);
            case 'menuSalida':
                return $this->renderMenuModuloSalida($nameMenu);
            case 'menuItinerarioEspecial':
                return $this->renderMenuModuloItinerarioEspecial($nameMenu);
            case 'menuTarjetas':
                return $this->renderMenuModuloTarjetas($nameMenu);  
            case 'menuCorteVentaTalonarios':
                return $this->renderMenuCorteVentaTalonarios($nameMenu);
            case 'menuClientes':
                return $this->renderMenuModuloClientes($nameMenu);  
            case 'menuReservacion':
                return $this->renderMenuModuloReservacion($nameMenu); 
            case 'menuBoletos':
                return $this->renderMenuModuloBoletos($nameMenu);
            case 'menuBuses':
                return $this->renderMenuModuloBuses($nameMenu); 
            case 'menuPilotos':
                return $this->renderMenuModuloPilotos($nameMenu); 
            case 'menuCajas':
                return $this->renderMenuModuloCajas($nameMenu);
            case 'menuAutorizacionInterna':
                return $this->renderMenuModuloAutorizacionInterna($nameMenu);
            case 'menuAutorizacionCortesia':
                return $this->renderMenuModuloAutorizacionCortesia($nameMenu);
            case 'menuEncomiendas':
                return $this->renderMenuModuloEncomiendas($nameMenu);
            case 'menuAlquiler':
                return $this->renderMenuModuloAlquiler($nameMenu);
            case 'menuDepositoAgencia':
                return $this->renderMenuModuloDepositoAgencia($nameMenu);
            case 'menuAutorizacionOperaciones':
                return $this->renderMenuModuloAutorizacionOperaciones($nameMenu);
            default:
                return '<li class="nav-header">Clave incorrecta del menu</li>';
        }
    }
    
    public function renderMenuModuloAutorizacionOperaciones($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemAutorizacionOperaciones = $this->checkItemsMenu(array(
            'Autorizar' => array("route" => 'autorizacion-operaciones-autorizar', "index" => 'true'),
            'Rechazar' => array("route" => 'autorizacion-operaciones-rechazar', "index" => 'true'),
            'Consultar' => array("route" => 'autorizacion-operaciones-consultar', "index" => 'true'),
        ), $nameMenu, $divider);
        
        if(trim($itemAutorizacionOperaciones) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . $itemAutorizacionOperaciones .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloDepositoAgencia($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemDepositoAgencia = $this->checkItemsMenu(array(
            'Acreditar' => array("route" => 'deposito-agencia-acreditar', "index" => 'true'),
            'Rechazar' => array("route" => 'deposito-agencia-rechazar', "index" => 'true')
        ), $nameMenu, $divider);
        
        if(trim($itemDepositoAgencia) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . $itemDepositoAgencia .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloAlquiler($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemAlquiler = $this->checkItemsMenu(array(
            'Consultar' => array("route" => 'alquiler-consultar-case1', "index" => 'true', "dialog" => 'false', "classCss" => 'hidden-phone'),
            'Actualizar' => array("route" => 'alquiler-actualizar-case1', "index" => 'true', "dialog" => 'false', "classCss" => 'hidden-phone'),
            'Iniciar' => array("route" => 'alquiler-iniciar-case1', "index" => 'true', "dialog" => 'true', "classCss" => 'hidden-phone'),
            'Cancelar' => array("route" => 'alquiler-cancelar-case1', "index" => 'true', "dialog" => 'true', "classCss" => 'hidden-phone'),
            'Manifiesto' => array("route" => 'reporte-manifiesto_alquiler', "index" => 'true', "autoOpenFile" => 'PDF'),
        ), $nameMenu, $divider);
        
        if(trim($itemAlquiler) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemAlquiler .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloFacturas($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemFacturas = $this->checkItemsMenu(array(
            'Actualizar' => array("route" => 'facturas-actualizar-case1', "index" => 'true', "dialog" => 'false'),
            'Activar' => array("route" => 'facturas-activar-case1', "index" => 'true'),
            'Desactivar' => array("route" => 'facturas-desactivar-case1', "index" => 'true'),
        ), $nameMenu, $divider);
        
        if(trim($itemFacturas) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemFacturas .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloEncomiendas($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemEncomiendas = $this->checkItemsMenu(array(
            'Embarcar' => array("route" => 'embarcarEncomienda-case1', "index" => 'true', "classCss" => 'hidden-phone'),
            'Desembarcar' => array("route" => 'desembarcarEncomienda-case1', "index" => 'true', "classCss" => 'hidden-phone'),
            'Entregar' => array("route" => 'entregarEncomienda-case1', "index" => 'true', "dialog" => 'false', "classCss" => 'hidden-phone'),
            'Anular' => array("route" => 'anularEncomienda-case1', "index" => 'true', "classCss" => 'hidden-phone'),
            'Cancelar' => array("route" => 'cancelarEncomienda-case1', "index" => 'true', "classCss" => 'hidden-phone'),
            'Consultar' => array("route" => 'consultarEncomienda-case1', "index" => 'true', "fullscreen" => 'true', "title" => 'Consultar encomienda', "classCss" => 'hidden-phone'),
            'Reimp-Factura' => array("route" => 'ajaxReprintFacturaEncomienda', "index" => 'true', "print" => 'method3', "classCss" => 'hidden-phone'),
            'Reimp-Enc' => array("route" => 'ajaxReprintDatosEncomienda', "index" => 'true', "print" => 'method4', "classCss" => 'hidden-phone'),
        ), $nameMenu, $divider);
        
        if(trim($itemEncomiendas) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemEncomiendas .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloAutorizacionCortesia($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemAutorizacionesCortesias = $this->checkItemsMenu(array(
            'Cancelar' => array("route" => 'autorizacionCortesia-cancelar-case1', "index" => 'true'),
        ), $nameMenu, $divider);
        
        if(trim($itemAutorizacionesCortesias) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemAutorizacionesCortesias .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloAutorizacionInterna($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemAutorizacionesInternas = $this->checkItemsMenu(array(
            'Cancelar' => array("route" => 'autorizacionInterna-cancelar-case1', "index" => 'true'),
        ), $nameMenu, $divider);
        
        if(trim($itemAutorizacionesInternas) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemAutorizacionesInternas .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloCajas($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemCajas = $this->checkItemsMenu(array(
            'Abrir' => array("route" => 'cajas-abrir-case1', "index" => 'true'),
            'Solicitar Cierre' => array("route" => 'cajas-preCerrar-case1', "index" => 'true'),
            'Consultar' => array("route" => 'cajas-consultar-case1', "index" => 'true'),
            'Rechazar Cierre' => array("route" => 'cajas-rechazarCierre-case1', "index" => 'true'),
            'Cerrar' => array("route" => 'cajas-cerrar-case1', "index" => 'true'),
            'Cancelar' => array("route" => 'cajas-cancelar-case1', "index" => 'true'),
        ), $nameMenu, $divider);
        
        if(trim($itemCajas) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemCajas .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloBuses($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemBuses = $this->checkItemsMenu(array(
            'Actualizar' => array("route" => 'buses-actualizar-case1', "index" => 'true', "dialog" => 'false'),
            'Cambiar Estado' => array("route" => 'buses-cambiarEstado-case1', "index" => 'true'),
        ), $nameMenu, $divider);
        
        if(trim($itemBuses) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemBuses .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloPilotos($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemPilotos = $this->checkItemsMenu(array(
            'Actualizar' => array("route" => 'pilotos-actualizar-case1', "index" => 'true', "dialog" => 'false'),
        ), $nameMenu, $divider);
        
        if(trim($itemPilotos) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemPilotos .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloBoletos($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemsBoletos = $this->checkItemsMenu(array(
            'Consultar' => array("route" => 'consultarBoleto-case1', "index" => 'true', "fullscreen" => 'true', "title" => 'Consultar boleto'),
            'Cancelar' => array("route" => 'cancelarBoleto-case1', "index" => 'true'),
            'Anular' => array("route" => 'anularBoleto-case1', "index" => 'true'),
            'Autorizacion' => array("route" => 'registrarAutorizacionBoleto-case1', "index" => 'true', "title" => 'Solicitar Autorización'),
            'Revertir' => array("route" => 'revertirCancelacionBoleto-case1', "index" => 'true', "classCss" => 'hidden-phone'),
            'Chequear' => array("route" => 'chequearBoleto-case1', "index" => 'true', "classCss" => 'hidden-phone'),
            'Reasignar' => array("route" => 'reasignarBoleto-case1', "index" => 'true', "dialog" => 'false'),
            'Correo' => array("route" => 'ajaxSendEmailVoucherBoleto', "index" => 'true', "email" => 'true'),
            'Imp.Voucher' => array("route" => 'ajaxPrintVoucherBoleto', "index" => 'true', "print" => 'method2'),
            'Imp.Factura' => array("route" => 'ajaxReprintFacturaBoleto', "index" => 'true', "print" => 'method1', "classCss" => 'hidden-phone'),
        ), $nameMenu, $divider);
        
        if(trim($itemsBoletos) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemsBoletos .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloSalida($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemsEncomienda = $this->checkItemsMenu(array(
            'Asignar' => array("route" => 'salida-asignarBusPilotos-case1', "index" => 'true'),
            'Abordar' => array("route" => 'salida-abordar-case1', "index" => 'true'),
            'Tarjeta' => array("route" => 'salida-asignarTarjeta-case1', "index" => 'true'),
            'Iniciar' => array("route" => 'salida-iniciar-case1', "index" => 'true'),
            'Adicionar Talonario' => array("route" => 'salida-adicionarTalonario-case1', "index" => 'true'),
            'Cancelar' => array("route" => 'salida-cancelar-case1', "index" => 'true'),
            'Finalizar' => array("route" => 'salida-finalizar-case1', "index" => 'true', "classCss" => 'hidden-phone'),
            'Consultar' => array("route" => 'salida-consultar-case1', "index" => 'true', "fullscreen" => 'true', "title" => 'Consultar salida', "classCss" => 'hidden-phone'),
            'Manif. Interno' => array("route" => 'reporte-manifiesto_boleto_full', "index" => 'true', "autoOpenFile" => 'PDF'),
            'Manif. Piloto' => array("route" => 'reporte-manifiesto_boleto_piloto', "index" => 'true', "autoOpenFile" => 'PDF'),
            'Manif. Encomienda' => array("route" => 'reporte-manifiesto_encomienda_full', "index" => 'true', "autoOpenFile" => 'PDF'),
            'Manif. Especial' => array("route" => 'reporte-manifiesto_boleto_especial', "index" => 'true', "autoOpenFile" => 'EXCEL'),
        ), $nameMenu, $divider);
        
        if(trim($itemsEncomienda) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemsEncomienda .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloItinerarioEspecial($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemsItinerarioEspecial = $this->checkItemsMenu(array(
//            'Crear' => array("route" => 'itinerarioEspecial-crear-case1', "index" => 'false', "title" => 'Crear Itinerario Especial')
        ), $nameMenu, $divider);
        
        if(trim($itemsItinerarioEspecial) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemsItinerarioEspecial .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloTarjetas($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemsTarjetas = $this->checkItemsMenu(array(
            'Consultar' => array("route" => 'tarjeta-consultar-case1', "index" => 'true', "fullscreen" => 'true', "title" => 'Consultar Tarjeta'),
        ), $nameMenu, $divider);
        
        if(trim($itemsTarjetas) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemsTarjetas .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuCorteVentaTalonarios($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemsCorteVentaTalonarios = $this->checkItemsMenu(array(
            'Iniciar' => array("route" => 'corte-venta-iniciar-revision-case1', "index" => 'true'),
            'Actualizar' => array("route" => 'corte-venta-actualizar-revision-case1', "index" => 'true', "dialog" => 'false'),
            'Terminar' => array("route" => 'corte-venta-finalizar-revision-case1', "index" => 'true'),
            'Anular' => array("route" => 'corte-venta-anular-case1', "index" => 'true'),
            'Ajustar Rango' => array("route" => 'corte-venta-ajustar-case2', "index" => 'true'),
        ), $nameMenu, $divider);
        
        if(trim($itemsCorteVentaTalonarios) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemsCorteVentaTalonarios .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuModuloClientes($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemsClientes = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'cliente-crear-case1', "index" => 'false', "title" => 'Crear Cliente'),
            'Actualizar' => array("route" => 'cliente-actualizar-case1', "index" => 'true', "title" => 'Actualizar Cliente')
        ), $nameMenu, $divider);
        
        if(trim($itemsClientes) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemsClientes .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
     public function renderMenuModuloReservacion($nameMenu){
        $menu = "";
        $divider = '<li class="divider-vertical"></li>';
        $itemsReservaciones = $this->checkItemsMenu(array(
            'Consultar' => array("route" => 'reservacion-consultar-case1', "index" => 'true', "title" => 'Consultar Reservación'),
            'Cancelar' => array("route" => 'reservacion-cancelar-case1', "index" => 'true')
        ), $nameMenu, $divider);
        
        if(trim($itemsReservaciones) !== ""){ 
            $menu .= '<div class="navbar"><div class="navbar-inner"><ul class="nav">' . 
                     $itemsReservaciones .
                     '</ul></div></div>';
        }
        return $menu;
    }
    
    public function renderMenuLateralIzq($nameMenu){
        $menu = "";
        
        $itemsCajas = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'cajas-crear-case1', "index" => 'false'),
            'Buscar' => array("route" => 'cajas-home', "index" => 'false')
        ), $nameMenu);
        if(trim($itemsCajas) !== ""){ $menu .= '<li class="nav-header">Cajas</li>' . $itemsCajas; }
        
        $itemsBoletos = $this->checkItemsMenu(array(
            'Emitir' => array("route" => 'emitirBoletos-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Emitir Bol. Agencia' => array("route" => 'emitirBoletosAgencia-case1', "index" => 'false'),
            'Emitir Camino' => array("route" => 'emitirBoletosCamino-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Chequear' => array("route" => 'chequearBoleto-case3', "index" => 'false', "classCss" => 'hidden-phone'),
            'Buscar' => array("route" => 'boletos-home', "index" => 'false'),
        ), $nameMenu);
        if(trim($itemsBoletos) !== ""){ $menu .= '<li class="nav-header">Boletos</li>' . $itemsBoletos; }
        
        $itemsReservaciones = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'reservacion-crear-case1', "index" => 'false'),
            'Buscar' => array("route" => 'reservaciones-home', "index" => 'false')
        ), $nameMenu);
        if(trim($itemsReservaciones) !== ""){ $menu .= '<li class="nav-header">Reservaciones</li>' . $itemsReservaciones; }

        $itemsEncomienda = $this->checkItemsMenu(array(
            'Registrar' => array("route" => 'encomiendas-registrar-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Entregar' => array("route" => 'entregaMultipleEncomienda-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Buscar' => array("route" => 'encomiendas-home', "index" => 'false'),
            'Procesar por Salida' => array("route" => 'procesarEncomienda-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Pendientes de Envío' => array("route" => 'pendientesEnvio-case1', "index" => 'false'),
            'Pendientes de Entrega' => array("route" => 'pendientesEntrega-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Registrar Especial' => array("route" => 'registrarEncomiendaEspecial-case1', "index" => 'false'),
        ), $nameMenu);
        if(trim($itemsEncomienda) !== ""){ $menu .= '<li class="nav-header">Encomiendas</li>' . $itemsEncomienda; }
        
//        $itemsMovilWeb = $this->checkItemsMenu(array(
//            'Embarcar' => array("route" => 'movilweb-embarcar-case1', "index" => 'false', "classCss" => 'hidden-phone'),
//            'Desembarcar' => array("route" => 'movilweb-desembarcar-case1', "index" => 'false', "classCss" => 'hidden-phone'),
//            'Listar Desembarcar' => array("route" => 'movilweb-listar-desembarcar-case1', "index" => 'false', "classCss" => 'hidden-phone')
//        ), $nameMenu);
//        if(trim($itemsMovilWeb) !== ""){ $menu .= '<li class="nav-header hidden-phone">Movil Web</li>' . $itemsMovilWeb; }
        
        $itemsAutorizacionOperaciones = $this->checkItemsMenu(array(
            'Buscar' => array("route" => 'autorizacion-operaciones-home', "index" => 'false')
        ), $nameMenu);
        if(trim($itemsAutorizacionOperaciones) !== ""){ $menu .= '<li class="nav-header">Autorizaciones</li>' . $itemsAutorizacionOperaciones; }

        $itemsDepositoAgencia = $this->checkItemsMenu(array(
            'Registrar Depósito' => array("route" => 'deposito-agencia-registrar', "index" => 'false'),
            'Buscar Depósito' => array("route" => 'deposito-agencia-home', "index" => 'false'),
            'Consultar Saldos' => array("route" => 'deposito-agencia-consultar-saldo', "index" => 'false'),
        ), $nameMenu);
        if(trim($itemsDepositoAgencia) !== ""){ $menu .= '<li class="nav-header">Agencia</li>' . $itemsDepositoAgencia; }
 
        $itemsSalidas = $this->checkItemsMenu(array(
            'Buscar' => array("route" => 'salidas-home', "index" => 'false'),
            'Consultar' => array("route" => 'consultar-esquema-salida', "index" => 'false'),
            'Consultar Buses y Pilotos' => array("route" => 'consultar-detalle-salida', "index" => 'false'),
        ), $nameMenu);
        if(trim($itemsSalidas) !== ""){ $menu .= '<li class="nav-header">Salidas</li>' . $itemsSalidas; }
        
        $itemsItinerariosEspeciles = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'itinerarioEspecial-crear-case1', "index" => 'false'),
            'Buscar' => array("route" => 'itinerarioEspecial-home', "index" => 'false', "classCss" => 'hidden-phone'),
        ), $nameMenu);
        if(trim($itemsItinerariosEspeciles) !== ""){ $menu .= '<li class="nav-header hidden-phone">Itinerarios Especiales</li>' . $itemsItinerariosEspeciles; }
        
        $itemsTarjetas = $this->checkItemsMenu(array(
            'Inspector' => array("route" => 'corteventa-crear-case1', "index" => 'false'),
            'Revisor' => array("route" => 'corteVentaTalonario-home', "index" => 'false'),
            'Conciliar' => array("route" => 'tarjeta-conciliar-case1', "index" => 'false'),
            'Buscar' => array("route" => 'tarjetas-home', "index" => 'false')
        ), $nameMenu);
        if(trim($itemsTarjetas) !== ""){ $menu .= '<li class="nav-header">Tarjetas</li>' . $itemsTarjetas; }
        
        $itemsConfiguraciones = $this->checkItemsMenu(array(
            'Tiempos' => array("route" => 'configuracion-update-tiempos-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Navegador' => array("route" => 'configuracion-home', "index" => 'false', "classCss" => 'hidden-phone'),
//            'Factura Boleto' => array("route" => 'configuracion-configurar-factura-boleto-case1', "index" => 'false', "classCss" => 'hidden-phone'),
//            'Factura Encomienda' => array("route" => 'configuracion-configurar-factura-encomienda-case1', "index" => 'false', "classCss" => 'hidden-phone')
        ), $nameMenu);
        if(trim($itemsConfiguraciones) !== ""){ $menu .= '<li class="nav-header hidden-phone">Configuraciones</li>' . $itemsConfiguraciones; }
        
        $itemsFacturas = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'facturas-crear-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Consultar' => array("route" => 'factura-consultar-series', "index" => 'false'),
            'Buscar' => array("route" => 'facturas-home', "index" => 'false'),
        ), $nameMenu);
        if(trim($itemsFacturas) !== ""){ $menu .= '<li class="nav-header">Series de Facturas</li>' . $itemsFacturas; }
        
       $itemsClientes = $this->checkItemsMenu(array(
//            'Crear' => array("route" => 'cliente-crear-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Buscar' => array("route" => 'cliente-home', "index" => 'false', "classCss" => 'hidden-phone'),
        ), $nameMenu);
        if(trim($itemsClientes) !== ""){ $menu .= '<li class="nav-header hidden-phone">Clientes</li>' . $itemsClientes; }
        
        $itemsBuses = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'buses-crear-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Buscar' => array("route" => 'buses-home', "index" => 'false', "classCss" => 'hidden-phone')
        ), $nameMenu);
        if(trim($itemsBuses) !== ""){ $menu .= '<li class="nav-header hidden-phone">Buses</li>' . $itemsBuses; }
        
        $itemsPilotos = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'pilotos-crear-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Buscar' => array("route" => 'pilotos-home', "index" => 'false', "classCss" => 'hidden-phone')
        ), $nameMenu);
        if(trim($itemsPilotos) !== ""){ $menu .= '<li class="nav-header hidden-phone">Pilotos</li>' . $itemsPilotos; }
        
        $itemsAutorizacionesInternas = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'autorizacionInterna-crear-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Crear Múltiples' => array("route" => 'autorizacionInterna-crear-multiples-case1', "index" => 'false'),
            'Buscar' => array("route" => 'autorizacionInterna-home', "index" => 'false')
        ), $nameMenu);
        if(trim($itemsAutorizacionesInternas) !== ""){ $menu .= '<li class="nav-header">Autorizaciones Internas</li>' . $itemsAutorizacionesInternas; }
        
        $itemsAutorizacionesCortesias = $this->checkItemsMenu(array(
            'Crear Boletos' => array("route" => 'autorizacionCortesia-crear-boleto', "index" => 'false'),
            'Crear Pines' => array("route" => 'autorizacionCortesia-crear-pines', "index" => 'false'),
            'Buscar' => array("route" => 'autorizacionCortesia-home', "index" => 'false')
        ), $nameMenu);
        if(trim($itemsAutorizacionesCortesias) !== ""){ $menu .= '<li class="nav-header">Autorizaciones de Cortesias</li>' . $itemsAutorizacionesCortesias; }
        
        $itemsAlquiler = $this->checkItemsMenu(array(
            'Crear' => array("route" => 'alquiler-crear-case1', "index" => 'false', "classCss" => 'hidden-phone'),
            'Buscar' => array("route" => 'alquiler-home', "index" => 'false')
        ), $nameMenu);
        if(trim($itemsAlquiler) !== ""){ $menu .= '<li class="nav-header">Alquiler</li>' . $itemsAlquiler; }
        
        $itemsAdmin = $this->checkItemsMenu(array(
            'Update User' => array("route" => 'configuracion-update-usuario-case1', "index" => 'false'),
        ), $nameMenu);
        if(trim($itemsAdmin) !== ""){ $menu .= '<li class="nav-header">Admin</li>' . $itemsAdmin; }
        
        $itemsResportes = $this->checkItemsMenu(array(
//            'Test' => array("route" => 'reporte-test', "index" => 'false'),
            'Boleto Cuadre Venta ' => array("route" => 'reporte-cuadre-venta-boleto', "index" => 'false'),
            'Boleto Cuadre Voucher ' => array("route" => 'reporte-cuadre-venta-boleto-voucher', "index" => 'false'),
//            'Boleto Venta Propietario' => array("route" => 'reporte-venta_boleto_propietario', "index" => 'false'),
            'Boleto Venta Usuario' => array("route" => 'reporte-venta_boleto_usuario', "index" => 'false'),
//            'Boleto Venta Prepagado' => array("route" => 'reporte-venta_boleto_prepagado', "index" => 'false'),
//            'Boleto Venta Otras Estaciones' => array("route" => 'reporte-venta_boleto_otra_estacion', "index" => 'false'),
            'Boleto Prepagado y Otras Estaciones por Buses' => array("route" => 'reporte-venta_boleto_prepagado_otras_estaciones_por_buses', "index" => 'false'),
            'Boleto Anulados' => array("route" => 'reporte-boleto_anulado_usuario', "index" => 'false'),
            'Boleto Detalle Factura' => array("route" => 'reporte-detalle-factura-boleto', "index" => 'false'),
            'Boleto Detalle Cortesia' => array("route" => 'reporte-detalle-cortesia-boleto', "index" => 'false'),
            'Boleto Manifiesto' => array("route" => 'reporte-manifiesto_boleto_full', "index" => 'false'),
            'Boleto Manifiesto Piloto' => array("route" => 'reporte-manifiesto_boleto_piloto', "index" => 'false'),
            'Boleto Tarifas' => array("route" => 'reporte-tarifas_boletos', "index" => 'false'),
            
            'Encomienda Cuadre' => array("route" => 'reporte-cuadre_encomienda', "index" => 'false'),
            'Encomienda Propietario' => array("route" => 'reporte-encomienda_propietario', "index" => 'false'),
            'Encomienda Anuladas' => array("route" => 'reporte-encomienda_anulado_usuario', "index" => 'false'),
            'Encomienda Pendiente Entrega' => array("route" => 'reporte-encomienda_pendiente_entregar', "index" => 'false'),
            'Encomienda Detalle General' => array("route" => 'reporte-detalle-general-encomienda', "index" => 'false'),
            'Encomienda Detalle Factura' => array("route" => 'reporte-detalle-factura-encomienda', "index" => 'false'),
//            'Encomienda Detalle Cortesia' => array("route" => 'reporte-detalle-cortesia-encomienda', "index" => 'false'),
            'Encomienda Detalle Guia Interna' => array("route" => 'reporte-detalle-guia-encomienda', "index" => 'false'),
            'Encomienda Manifiesto ' => array("route" => 'reporte-manifiesto_encomienda_full', "index" => 'false'),
            'Encomienda Tarifas Especiales' => array("route" => 'reporte-tarifas_encomiendas_especiales', "index" => 'false'),
            
            'Tarjetas' => array("route" => 'reporte-detalle-tarjetas', "index" => 'false'),
            'Cuadre Inspector' => array("route" => 'reporte-cuadre-inspector', "index" => 'false'),
            
            'Salidas Detalles' => array("route" => 'reporte-detalle-salida', "index" => 'false'),
            
            'Caja Cuadre' => array("route" => 'reporte-caja', "index" => 'false'),
            'Caja Detalle' => array("route" => 'reporte-detalle-caja', "index" => 'false'),
            
            'Portal Web Venta' => array("route" => 'reporte-detalle-portal', "index" => 'false'),
            
            'Agencia Venta' => array("route" => 'reporte-detalle-agencia', "index" => 'false'),
            'Agencia Depósito' => array("route" => 'reporte-detalle-deposito-agencia', "index" => 'false'),
            
            'Listado Buses ' => array("route" => 'reporte-detalle-buses', "index" => 'false'),
            'Listado Pilotos ' => array("route" => 'reporte-detalle-pilotos', "index" => 'false'),
            
            'Alquiler Detalle ' => array("route" => 'reporte-detalle-alquiler', "index" => 'false'),
            'Asistencia Pilotos' => array("route" => 'reporte-asistencia-pilotos', "index" => 'false'),
            
            'GRAF Agencia Venta' => array("route" => 'reporte-detalle-agencia-grafico', "index" => 'false'),
            'GRAF Venta Total' => array("route" => 'reporte-estadisticas-ventas-totales', "index" => 'false'),
            
        ), $nameMenu);
        if(trim($itemsResportes) !== ""){ $menu .= '<li class="nav-header">Reportes</li>' . $itemsResportes; }
        
        if(trim($menu) === "") { $menu .= '<li class="nav-header">No tiene acceso a ninguna funcionalidad.'; }
        
        return $menu;
    }
    
    public function checkItemsMenu($items, $nameMenu, $divider = null){
        $result = "";
        foreach ($items as $name => $properties) {
            $routeName = $properties['route'];
            $index = 'false';
            if(array_key_exists("index", $properties)){
                $index = $properties["index"];
            }
            $fullscreen = '';
            if(array_key_exists("fullscreen", $properties) && $properties["fullscreen"] === 'true'){
                $fullscreen = ' data-fullscreen="true" ';
            }
            $title = '';
            if(array_key_exists("title", $properties)){
                $title = ' data-title="'. $properties["title"].'" ';
            }
            $dialog = '';
            if(array_key_exists("dialog", $properties)){
                $dialog = ' data-dialog="'. $properties["dialog"].'" ';
            }
            $classCss = '';
            if(array_key_exists("classCss", $properties)){
                $classCss = ' ' . $properties["classCss"];
            }
            $print = '';
            if(array_key_exists("print", $properties)){
                $title = ' data-print="'. $properties["print"].'" ';
            }
            $email = '';
            if(array_key_exists("email", $properties)){
                $title = ' data-email="'. $properties["email"].'" ';
            }
            $autoOpenFile = '';
            if(array_key_exists("autoOpenFile", $properties)){
                $autoOpenFile = ' data-autoopenfile="'. $properties["autoOpenFile"].'" ';
            }
            
            if($this->hasRouteAccess($routeName)){
                $url = $this->getRouter()->generate($routeName);
                $result .= '<li><a class="'.$nameMenu.$classCss.'" '. $fullscreen . $title . $dialog. $print . $email . $autoOpenFile. ' data-index="'.$index.'" href="'.$url.'">'.$name.'</a></li>';
                if($divider !== null){
                    $result .= $divider;
                }
            }
        }   
        return $result;
    }
    
    public function hasRouteAccess($routeName){ 
        $token = $this->getSecurityContext()->getToken();         
        if ($token->isAuthenticated()) { 
            $route = $this->getRouter()->getRouteCollection()->get($routeName);
            if($route === null){
                throw new \RuntimeException("No se enontro una ruta con el nombre: " . $routeName);
            }
            $controller = $route->getDefault('_controller');             
            list($class, $method) = explode('::', $controller, 2);               
            $metadata = $this->getMetadataFactory()->getMetadataForClass($class);    
            if (!isset($metadata->methodMetadata[$method])) {
                return false;
            }            
            foreach ($metadata->methodMetadata[$method]->roles as $role) {
                if ($this->getSecurityContext()->isGranted($role)) {
                    return true;
                }
             }
         }         
         return false;  
    }
    
    public function getRouter() {
        if($this->router === null){ $this->router = $this->container->get('router'); }
        return $this->router;
    }

    public function getSecurityContext() {
        if($this->securityContext === null){ $this->securityContext = $this->container->get('security.context'); }
        return $this->securityContext;
    }

    public function getMetadataFactory() {
        if($this->metadataFactory === null){ $this->metadataFactory = $this->container->get('security.extra.metadata_factory'); }
        return $this->metadataFactory;
    }
    
    
}

?>
