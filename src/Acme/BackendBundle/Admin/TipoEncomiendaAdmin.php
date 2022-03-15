<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class TipoEncomiendaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tipo_encomienda_admin';
    protected $baseRoutePattern = 'tipoencomienda';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nombre', null, array('label' => 'Nombre'))
        ->add('descripcion', null, array('label' => 'Descripción'))        
        ->add('activo', null, array('label' => 'Activo'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('nombre', null, array('label' => 'Nombre'))
        ->add('descripcion', null, array('label' => 'Descripción'))        
        ->add('activo', null, array('label' => 'Activo'))
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->add('nombre', null, array('label' => 'Nombre'))
        ->add('descripcion', null, array('label' => 'Descripción'))
        ->add('activo', null,  array(
            'label' => 'Activo',
            'required' => false
         ))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('nombre', null, array('label' => 'Nombre'))
            ->add('descripcion', null, array('label' => 'Descripción'))        
            ->add('activo', null, array('label' => 'Activo'))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->add('show');
    }
    
}

?>
