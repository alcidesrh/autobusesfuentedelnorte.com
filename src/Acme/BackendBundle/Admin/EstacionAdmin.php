<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class EstacionAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_estacion_admin';
    protected $baseRoutePattern = 'estacion';
    protected $formOptions = array('cascade_validation' => true);
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nombre')
        ->add('departamento')
        ->add('direccion', null, array('label' => 'Dirección'))
        ->add('alias')  
        ->add('tipo.nombre', null, array('label' => 'Tipo estación'))
        ->add('tipoPago.nombre', null, array('label' => 'Tipo Pago'))        
        ->add('iniciaRuta')
        ->add('facturacionEspecial')  
        ->add('longitude')
        ->add('latitude')
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('nombre')
        ->add('departamento')
        ->add('direccion', null, array('label' => 'Dirección'))
        ->add('alias')  
        ->add('tipo.nombre', null, array('label' => 'Tipo estación'))
        ->add('tipoPago.nombre', null, array('label' => 'Tipo Pago')) 
        ->add('iniciaRuta')
        ->add('facturacionEspecial')
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->with('General')
            ->add('nombre')
            ->add('alias')
            ->add('pais')
            ->add('departamento')
            ->add('direccion', null, array('label' => 'Dirección'))
            ->add('longitude', 'number',  array(
                'required' => false,
                'precision'=> 10,   
            ))
            ->add('latitude', 'number',  array(
                'required' => false,
                'precision'=> 10,
            ))
            ->add('tipo', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:TipoEstacion',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
            ))   
            ->add('tipoPago', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:TipoPagoEstacion',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
            )) 
            ->add('iniciaRuta', null,  array('required' => false))
            
            ->add('facturacionEspecial', null,  array('required' => false))
            ->add('pingFacturacionEspecial', null,  array('required' => false))
                
                
                
                
                
                
                
            ->add('numEstablecimientoSat', null, array('label' => 'Numero Establecimiento SAT - PIONERA', 'required' => false))	//Número establecimiento SAT-PIONERA              
            ->add('numEstablecimientoSatMitocha', null, array('label' => 'Numero Establecimiento SAT - MITOCHA', 'required' => false))	//Número establecimiento SAT-MITOCHA                      
            ->add('numEstablecimientoSatRosita', null, array('label' => 'Numero Establecimiento SAT - ROSITA', 'required' => false))	//Número establecimiento SAT-ROSITA                
                
                
                
                
                
            ->add('destino', null,  array('required' => false))
            ->add('enviosEncomiendasPorCobrar', null,  array('required' => false))
            ->add('permitirVoucherBoleto', null,  array('required' => false))
            ->add('permitirTarjeta', null,  array('required' => false))
            ->add('pluginJavaActivo', null,  array('required' => false))
            ->add('publicidad', null,  array('required' => false))
            ->add('activo', null,  array('required' => false))
        ->end();
        
        $mapper->with('Control Tarjetas' , array('tab' => true))
            ->add('controlTarjetasEnRuta', null,  array(
                'label' => 'Puede registrar talonarios adicionales en ruta',
                'required' => false
               ))
         ->end()->end();
        
        $mapper
         ->with('Servicios')
             ->add('listaServicio', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:ServicioEstacion',
                'property'=>'nombre',
                'multiple'=>true,
                'expanded'=>true,
                'btn_add' => false
            ))
        ->end();
        
        $mapper  
        ->with('Agencia')
        ->add('monedaAgencia', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Moneda',
                'property'=>'sigla',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false
            ))
        ->add('aplicarPorcientoTarifaAgencia', null,  array('required' => false))
        ->add('porcientoTarifaAgencia', null,  array('required' => false))
        ->add('porcientoBonificacion', null, array('label' => 'Porciento Bonificación'))     
        ->add('saldo', null, array('label' => 'Saldo'))
        ->add('bonificacion', null, array('label' => 'Bonificación'))
         ->end();
        
        $mapper->with('Reporte de Información a Servidor Externo' , array('tab' => true))
            ->add('idExternoBoleto', null, array('label' => 'ID Centro Costo Boleto'))
            ->add('idExternoEncomienda', null, array('label' => 'ID Centro Costo Encomienda'))
         ->end()->end();
        
        $mapper  
        ->with('Teléfonos')
        ->add('listaTelefono', 'sonata_type_collection', array(
                'by_reference' => false ,
                'type_options' => array('btn_delete' => true),
                'label' => 'Lista de Teléfonos',
                'required' => false,             
            ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position',
            ))
         ->end();
        
        $mapper  
        ->with('Correos')
        ->add('listaCorreo', 'sonata_type_collection', array(
                'by_reference' => false ,
                'type_options' => array('btn_delete' => true),
                'label' => 'Lista de Correos',
                'required' => false,
            ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position',
            ))
         ->end();        
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
