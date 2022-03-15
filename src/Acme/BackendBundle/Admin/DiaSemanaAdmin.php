<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class DiaSemanaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_dia_semana_admin';
    protected $baseRoutePattern = 'diasemana';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('nombre')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('nombre')
        ;
    }
    
    //Atributos utilizados para los formularios de crear y modificar la entidad
    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
        ->add('nombre', null, array())
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        //Removing a single route
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
