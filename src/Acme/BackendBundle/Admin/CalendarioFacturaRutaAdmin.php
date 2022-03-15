<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class CalendarioFacturaRutaAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_calendario_factura_ruta_admin';
    protected $baseRoutePattern = 'calendariofactura';
    protected $formOptions = array('cascade_validation' => true);
        
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID'))
        ->add('ruta')
        ->add('constante')
        ->add('empresa')
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id')
        ->add('ruta')
        ->add('constante')
        ->add('empresa')
        ;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('listEmpresas', 'listEmpresas');
        $collection->remove('delete');
    }
    
}

?>
