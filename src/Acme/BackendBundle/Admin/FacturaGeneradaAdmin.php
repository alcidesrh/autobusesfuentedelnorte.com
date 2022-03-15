<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class FacturaGeneradaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_factura_generada_admin';
    protected $baseRoutePattern = 'facturagenerada';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
            ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
            ->add('factura', null, array('label' => 'Factura'))
            ->add('consecutivo', null, array('label' => 'Consecutivo'))        
            ->add('servicioEstacion', null, array('label' => 'Servicio Estación'))
            ->add('moneda', null, array('label' => 'Moneda'))
//            ->add('tipoCambio', null, array('label' => 'Tipo Cambio'))
            ->add('importeTotal', null, array('label' => 'Importe Total'))
            ->add('usuario', null, array('label' => 'Usuario'))
            ->add('estacion', null, array('label' => 'Estacion'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
            ->add('id', null, array('label' => 'ID'))
            ->add('factura', null, array('label' => 'Factura'))
            ->add('consecutivo', null, array('label' => 'Consecutivo'))        
            ->add('servicioEstacion', null, array('label' => 'Servicio Estación'))
            ->add('moneda', null, array('label' => 'Moneda'))
//            ->add('tipoCambio', null, array('label' => 'Tipo Cambio'))
            ->add('importeTotal', null, array('label' => 'Importe Total'))
            ->add('usuario', null, array('label' => 'Usuario'))
            ->add('estacion', null, array('label' => 'Estacion'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
//        
//        $editarCodigo = true;
//        if(strpos($this->request->getPathInfo(), "edit")){
//              $editarCodigo = false;
//         }         
//        $mapper
//        ->with('General')
//            ->add('consecutivo', null, array(
//                    'label' => 'Consecutivo',
//                    'read_only' => !$editarCodigo
//                ))
//            ->add('factura', 'sonata_type_model' , array(
//                'class'=>'AcmeTerminalOmnibusBundle:Factura',
//                'multiple'=>false,
//                'expanded'=>false,
//                'btn_add' => false  
//            ))  
//        ->end();
//        
    }
    
     protected function configureShowFields(ShowMapper $showMapper)
     {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('factura', null, array('label' => 'Factura'))
            ->add('consecutivo', null, array('label' => 'Consecutivo'))        
            ->add('servicioEstacion', null, array('label' => 'Servicio Estación'))
            ->add('moneda', null, array('label' => 'Moneda'))
            ->add('tipoCambio', null, array('label' => 'Tipo Cambio'))
            ->add('importeTotal', null, array('label' => 'Importe Total'))
            ->add('usuario', null, array('label' => 'Usuario'))
            ->add('estacion', null, array('label' => 'Estacion'))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
        $collection->remove('edit');
        $collection->add('show');
    }
    
}

?>
