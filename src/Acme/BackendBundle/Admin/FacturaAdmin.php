<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class FacturaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_factura_admin';
    protected $baseRoutePattern = 'factura';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('estacion', null, array('label' => 'Estación'))        
        ->add('empresa', null, array('label' => 'Empresa'))
        ->add('servicioEstacion', null, array('label' => 'Servicio'))
//        ->add('nombreResolucionFactura', null, array('label' => 'Nombre Resolución Factura'))
//        ->add('fechaEmisionResolucionFactura', null, array(
//            'label' => 'Emisión Resolución Factura',
//            'format' => 'd/m/Y'
//        ))
//        ->add('fechaVencimientoResolucionFactura', null, array(
//            'label' => 'Vencimiento Resolución Factura',
//            'format' => 'd/m/Y'
//        ))
        ->add('serieResolucionFactura', null, array('label' => 'Serie Resolución Factura'))
        ->add('minimoResolucionFactura', null, array('label' => 'Mínimo'))
        ->add('maximoResolucionFactura', null, array('label' => 'Máximo'))
        ->add('valorResolucionFactura', null, array('label' => 'Valor'))
//        ->add('nombreResolucionSistema', null, array('label' => 'Nombre Resolución Sistema'))
//        ->add('fechaEmisionResolucionSistema', null, array(
//            'label' => 'Emisión Resolución Sistema',
//            'format' => 'd/m/Y'
//        ))
//        ->add('fechaVencimientoResolucionSistema', null, array(
//            'label' => 'Vencimiento Resolución Sistema',
//            'format' => 'd/m/Y'
//        ))              
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('estacion', null, array('label' => 'Estación'))        
        ->add('empresa', null, array('label' => 'Empresa'))
        ->add('servicioEstacion', null, array('label' => 'Servicio'))
//        ->add('nombreResolucionFactura', null, array('label' => 'Nombre Resolución Factura'))
//        ->add('fechaEmisionResolucionFactura', null, array(
//            'label' => 'Emisión Resolución Factura'
//        ))
//        ->add('fechaVencimientoResolucionFactura', null, array(
//            'label' => 'Vencimiento Resolución Factura'
//        ))
        ->add('serieResolucionFactura', null, array('label' => 'Serie Resolución Factura'))
        ->add('minimoResolucionFactura', null, array('label' => 'Mínimo'))
        ->add('maximoResolucionFactura', null, array('label' => 'Máximo'))
        ->add('valorResolucionFactura', null, array('label' => 'Valor'))
//        ->add('nombreResolucionSistema', null, array('label' => 'Nombre Resolución Sistema'))
//        ->add('fechaEmisionResolucionSistema', null, array(
//            'label' => 'Emisión Resolución Sistema'
//        ))
//        ->add('fechaVencimientoResolucionSistema', null, array(
//            'label' => 'Vencimiento Resolución Sistema'
//        )) 
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {      
        $anoInitFechaEmisionResolucionFactura = date('Y');
        $fechaEmisionResolucionFactura = $this->getSubject()->getFechaEmisionResolucionFactura();
//        var_dump($fechaEmisionResolucionFactura);
        if($fechaEmisionResolucionFactura !== null){
            $anoInitFechaEmisionResolucionFactura = $fechaEmisionResolucionFactura->format('Y');
        }
        $anoInitFechaVencimientoResolucionFactura = date('Y');
        $fechaVencimientoResolucionFactura = $this->getSubject()->getFechaVencimientoResolucionFactura();
//        var_dump($fechaVencimientoResolucionFactura);
        if($fechaVencimientoResolucionFactura !== null){
            $anoInitFechaVencimientoResolucionFactura = $fechaVencimientoResolucionFactura->format('Y');
        }
        $anoInitFechaEmisionResolucionSistema = date('Y');
        $fechaEmisionResolucionSistema = $this->getSubject()->getFechaEmisionResolucionSistema();
//        var_dump($fechaEmisionResolucionSistema);
        if($fechaEmisionResolucionSistema !== null){
            $anoInitFechaEmisionResolucionSistema = $fechaEmisionResolucionSistema->format('Y');
        }
        $anoInitFechaVencimientoResolucionSistema = date('Y');
        $fechaVencimientoResolucionSistema = $this->getSubject()->getFechaVencimientoResolucionSistema();
//        var_dump($fechaVencimientoResolucionSistema);
        if($fechaVencimientoResolucionSistema !== null){
            $anoInitFechaVencimientoResolucionSistema = $fechaVencimientoResolucionSistema->format('Y');
        }
        
        $mapper
        ->with('General')
            ->add('estacion', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false  
            ))
            ->add('empresa', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Empresa',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false  
            ))
            ->add('servicioEstacion', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:ServicioEstacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false  
            ))
            ->add('nombreResolucionFactura', null, array('label' => 'Nombre Resolución Factura'))
            ->add('fechaEmisionResolucionFactura', null, array(
                'label' => 'Emisión Resolución Factura',
                'years' => range($anoInitFechaEmisionResolucionFactura - 20, date('Y') + 20)
            ))
            ->add('fechaVencimientoResolucionFactura', null, array(
                'label' => 'Vencimiento Resolución Factura',
                'years' => range($anoInitFechaVencimientoResolucionFactura - 20, date('Y') + 20)
            ))
            ->add('serieResolucionFactura', null, array('label' => 'Serie Resolución Factura'))
            ->add('minimoResolucionFactura', null, array('label' => 'Mínimo Resolución Factura'))
            ->add('maximoResolucionFactura', null, array('label' => 'Máximo Resolución Factura'))
            ->add('valorResolucionFactura', null, array('label' => 'Valor Resolución Factura'))
            ->add('nombreResolucionSistema', null, array('label' => 'Nombre Resolución Sistema'))
            ->add('fechaEmisionResolucionSistema', null, array(
                'label' => 'Emisión Resolución Sistema',
                'years' => range($anoInitFechaEmisionResolucionSistema - 20, date('Y') + 20)
            ))
            ->add('fechaVencimientoResolucionSistema', null, array(
                'label' => 'Vencimiento Resolución Sistema',
                'years' => range($anoInitFechaVencimientoResolucionSistema - 20, date('Y') + 20)
            ))
            ->add('impresora', 'sonata_type_model' , array(
                'class'=>'AcmeTerminalOmnibusBundle:Impresora',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false  
            ))
            ->add('activo', null,  array('required' => false))      
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
