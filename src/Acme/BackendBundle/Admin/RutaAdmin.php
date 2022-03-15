<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class RutaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_ruta_admin';
    protected $baseRoutePattern = 'ruta';
    protected $formOptions = array('cascade_validation' => true);
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('codigo', null, array('label' => 'Código'))
        ->add('nombre')
        ->add('estacionOrigen', null, array('label' => 'Origen'))
        ->add('estacionDestino', null, array('label' => 'Destino'))
        ->add('activo')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('codigo', null, array('label' => 'Código'))
        ->add('nombre')
        ->add('estacionOrigen', null, array('label' => 'Estación de Origen'))
        ->add('estacionDestino', null, array('label' => 'Estación de Destino'))
        ->add('activo')
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
    
}

?>
