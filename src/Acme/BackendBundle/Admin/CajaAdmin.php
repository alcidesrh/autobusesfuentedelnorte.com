<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class CajaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_caja_admin';
    protected $baseRoutePattern = 'caja';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('moneda', null, array('label' => 'Moneda'))
        ->add('usuario', null, array('label' => 'Usuario'))
        ->add('estacion', null, array('label' => 'Estación'))        
        ->add('estado', null, array('label' => 'Estado'))  
        ->add('fechaCreacion', null, array('label' => 'Fecha Creación'))
        ->add('fechaApertura', null, array('label' => 'Fecha Apertura'))        
        ->add('fechaCierre', null, array('label' => 'Fecha Cierre'))
        ->add('fechaCancelacion', null, array('label' => 'Fecha Cancelación'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('moneda', null, array('label' => 'Moneda'))
        ->add('usuario', null, array('label' => 'Usuario'))
        ->add('estacion', null, array('label' => 'Estación'))
        ->add('estado', null, array('label' => 'Estado'))  
        ->add('fechaCreacion', null, array('label' => 'Fecha Creación'))
        ->add('fechaApertura', null, array('label' => 'Fecha Apertura'))        
        ->add('fechaCierre', null, array('label' => 'Fecha Cierre'))
        ->add('fechaCancelacion', null, array('label' => 'Fecha Cancelación'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        
        $isNew = true;
        if(strpos($this->request->getPathInfo(), "edit")){
            $isNew = false;
        } 
        
        $mapper
        ->with('General')
            ->add('moneda', 'sonata_type_model' , array(
                'label' => 'Moneda',
                'class'=>'AcmeTerminalOmnibusBundle:Moneda',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null,
                'read_only' => !$isNew,
                'disabled' => !$isNew,
            ))
            ->add('usuario', 'sonata_type_model' , array(
                'label' => 'Usuario',
                'class'=>'Acme\BackendBundle\Entity\User',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null,
                'read_only' => !$isNew,
                'disabled' => !$isNew,
            ))
            ->add('estacion', 'sonata_type_model' , array(
                'label' => 'Estación',
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false,
                'required' => true,
                'empty_value' => "",
                'empty_data'  => null,
                'read_only' => !$isNew,
                'disabled' => !$isNew,
            ))
        ->end();
        
        if($isNew === false){
            $mapper
            ->with('General')
              ->add('estado', 'sonata_type_model' , array(
                    'label' => 'Estado',
                    'class'=>'AcmeTerminalOmnibusBundle:EstadoCaja',
                    'multiple'=>false,
                    'expanded'=>false,
                    'btn_add' => false,
                    'required' => true,
                    'empty_value' => "",
                    'empty_data'  => null
              ))
                ->add('fechaCreacion', 'datetime', array(
                    'label' => 'Fecha Creación',
                    'required'    => false,
                    'read_only' => true,
                    'disabled' => true,
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy HH:mm:ss',
               ))
               ->add('fechaApertura', 'datetime', array(
                    'label' => 'Fecha Apertura',
                    'required'    => false,
                    'read_only' => true,
                    'disabled' => true,
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy HH:mm:ss',
               ))
               ->add('fechaCierre', 'datetime', array(
                    'label' => 'Fecha Cierre',
                    'required'    => false,
                    'read_only' => true,
                    'disabled' => true,
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy HH:mm:ss',
               ))                    
                ->add('fechaCancelacion', 'datetime', array(
                    'label' => 'Fecha Cancelación',
                    'required'    => false,
                    'read_only' => true,
                    'disabled' => true,
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy HH:mm:ss',
               )) 
            ->end();
        }
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
