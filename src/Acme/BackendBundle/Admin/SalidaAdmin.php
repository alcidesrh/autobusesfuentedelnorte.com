<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class SalidaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_salida_admin';
    protected $baseRoutePattern = 'salida';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('itinerario', null, array('label' => 'Itinerario'))
        ->add('fecha', null, array('label' => 'Fecha'))
        ->add('estado', null, array('label' => 'Estado'))
        ->add('bus', null, array('label' => 'Bus'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('estado', null, array('label' => 'Estado'))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('empresa', null, array('label' => 'Empresa'))
            ->add('itinerario', null, array('label' => 'Fecha'))
            ->add('fecha', null, array('label' => 'Tipo Bus'))        
            ->add('tipoBus', null, array('label' => 'Cliente Boleto'))
            ->add('estado', null, array('label' => 'Estado'))
            ->add('bus', null, array('label' => 'Bus'))
            ->add('piloto', null, array('label' => 'Piloto 1'))
            ->add('pilotoAux', null, array('label' => 'Piloto 2'))
            ->add('bitacoras', null, array('label' => 'Bitácora', 'route' => array('name' => 'show')))
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
