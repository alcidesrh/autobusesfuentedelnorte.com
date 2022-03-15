<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class AutorizacionInternaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_autorizacion_interna_admin';
    protected $baseRoutePattern = 'autorizacioninterna';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('motivo', null, array('label' => 'Motivo'))
        ->add('codigo', null, array('label' => 'PIN'))
        ->add('estacion', null, array('label' => 'Estación'))        
        ->add('fechaCreacion', null, array('label' => 'Fecha de Autorización'))
        ->add('usuarioCreacion', null, array('label' => 'Usuario que Autorizó'))
        ->add('fechaUtilizacion', null, array('label' => 'Fecha de Utilización'))
        ->add('usuarioUtilizacion', null, array('label' => 'Usuario que Utilizó'))
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('motivo', null, array('label' => 'Motivo'))
        ->add('codigo', null, array('label' => 'PIN'))
        ->add('estacion', null, array('label' => 'Estación'))        
        ->add('fechaCreacion', null, array('label' => 'Fecha de Autorización'))
        ->add('usuarioCreacion', null, array('label' => 'Usuario que Autorizó'))
        ->add('fechaUtilizacion', null, array('label' => 'Fecha de Utilización'))
        ->add('usuarioUtilizacion', null, array('label' => 'Usuario que Utilizó'))
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->with('General')
            ->add('motivo', null, array('label' => 'Motivo'))
            ->add('estacion', 'sonata_type_model' , array(
                'label' => 'Estación de Origen',
                'class'=>'AcmeTerminalOmnibusBundle:Estacion',
                'property'=>'nombre',
                'multiple'=>false,
                'expanded'=>false,
                'btn_add' => false  
            ))
            ->add('codigo', null, array(
                'label' => 'PIN',
                'read_only' => true
             ))
            ->add('generatePin', 'hidden', array(
                 'data' => $this->generateUrl("generatePin"),
                 'virtual' => true,
                 'attr' =>  array('class' => 'generatePinHidden')
             ))
            ->add('activo', null,  array('required' => false))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('generatePin');
        $collection->remove('delete');
    }
    
}

?>
