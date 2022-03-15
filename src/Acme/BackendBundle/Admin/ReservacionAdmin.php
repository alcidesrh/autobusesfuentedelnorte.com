<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ReservacionAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_reservacion_admin';
    protected $baseRoutePattern = 'reservacion';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('salida', null, array('label' => 'Salida'))
        ->add('asientoBus', null, array('label' => 'Asiento'))        
        ->add('cliente', null, array('label' => 'Cliente Boleto'))
        ->add('estado', null, array('label' => 'Estado'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ;
    }
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('salida', null, array('label' => 'Salida'))
            ->add('asientoBus', null, array('label' => 'Asiento'))        
            ->add('cliente', null, array('label' => 'Cliente Boleto'))
            ->add('estado', null, array('label' => 'Estado'))
            ->add('observacion', null, array('label' => 'Observación'))
            ->add('estacionCreacion', null, array('label' => 'Estación Creación'))
            ->add('fechaCreacion', null, array('label' => 'Fecha Creación'))
            ->add('usuarioCreacion', null, array('label' => 'Usuario Creación'))
            ->add('fechaActualizacion', null, array('label' => 'Fecha Actualizaión'))
            ->add('usuarioActualizacion', null, array('label' => 'Usuario Actualizaión'))
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
