<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TipoBusAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tipo_bus_admin';
    protected $baseRoutePattern = 'tipobus';
    protected $formOptions = array('cascade_validation' => true);
        
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('alias')
        ->add('descripcion', null, array('label' => 'Descripción'))
        ->add('clase')
        ->add('nivel2')
        ->add('totalAsientos')
//        ->add('listaServicios')
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('alias')
        ->add('descripcion', null, array('label' => 'Descripción'))
        ->add('clase')
        ->add('nivel2')
        ->add('totalAsientos')
//        ->add('listaServicios')
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        
//        $mapper
//        ->with('General')
//        ->add('alias')
//        ->add('descripcion', null, array('label' => 'Descripción'))
//        ->add('clase', 'sonata_type_model' , array(
//                'class'=>'AcmeTerminalOmnibusBundle:ClaseBus',
//                'property'=>'nombre',
//                'multiple'=>false,
//                'expanded'=>false,
//                'btn_add' => false  
//        ))
//        ->add('activo', null,  array('required' => false))
//       ->end();
//        
//        $mapper
//        ->with('Servicios')
//             ->add('listaServicios', 'sonata_type_model' , array(
//                'class'=>'AcmeTerminalOmnibusBundle:ServicioBus',
//                'property'=>'nombre',
//                'multiple'=>true,
//                'expanded'=>true,
//                'btn_add' => false
//            ))
//        ->end();
//                 
//        $mapper
//        ->with('Asientos')
//            ->add('listaAsiento', 'sonata_type_collection', array(
//                'by_reference' => false ,
//                'type_options' => array('btn_delete' => true),
//                'label' => 'Lista de Asientos',
//                'required' => false,             
//            ), array(
//                'edit' => 'inline',
//                'inline' => 'table',
//                'sortable' => 'position',
//            ))
//            ->add('listaAsiento', 'sonata_type_collection', array(
//                'by_reference' => false ,
//                'type_options' => array('btn_delete' => true),
//                'label' => 'Lista de Asientos',
//                'required' => false,             
//            ), array(
//                'edit' => 'inline',
//                'inline' => 'table',
//                'sortable' => 'position',
//            ))
//         ->end();
//        
//        $mapper
//        ->with('Señales')
//           ->add('listaSenal', 'sonata_type_collection', array(
//                'by_reference' => false ,
//                'type_options' => array('btn_delete' => true),
//                'label' => 'Lista de Señales',
//                'required' => false,             
//            ), array(
//                'edit' => 'inline',
//                'inline' => 'table',
//                'sortable' => 'position',
//            ))
//        ->end(); 
//        
//        $mapper
//        ->with('Vista Previa')
//        ->end(); 
//        
//         $mapper
//        ->with('Diseño')            
//            ->add('totalAsientos')
//        ->end(); 
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->add('crearListaAsientos');
//        $collection->add('crearListaAsientosById', $this->getRouterIdParameter().'/crearListaAsientosById');
        
        $collection->add('view', $this->getRouterIdParameter().'/view');
//        var_dump($collection->get("edit"));
//        var_dump($collection->get("crearListaAsientosById"));
//        //Removing a single route
        $collection->remove('delete');
        
        //Only `list` and `edit` route will be active
        //$collection->clearExcept(array('list', 'edit'));
        //All routes are removed
        //$collection->clear(); 
        // prevent display of "Add new" when embedding this form
        //if($this->hasParentFieldDescription()) { 
        //$collection->remove('create');
        //}
    }
    
}

?>
