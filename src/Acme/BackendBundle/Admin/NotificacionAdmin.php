<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class NotificacionAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_notificacion_admin';
    protected $baseRoutePattern = 'notificacion';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('texto')
        ->add('oficinas')
        ->add('agencias')
        ->add('activo')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->add('texto', 'textarea', array(
            'attr' =>  array('style' => 'width: 100%; height: 150px;')
        ))
        ->add('segundos', null, array(
            'attr' =>  array('style' => 'width: 100%;')
        ))
        ->add('oficinas', null,  array('required' => false))
        ->add('agencias', null,  array('required' => false))
        ->add('activo', null,  array('required' => false))
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('delete');
    }
    
}

?>
