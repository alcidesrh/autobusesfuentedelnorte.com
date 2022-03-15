<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class LogItemAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_log_admin';
    protected $baseRoutePattern = 'log';
    
    //Atributos utilizados para listar
    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
        ->addIdentifier('id', null, array('label' => 'ID', 'route' => array('name' => 'show')))
        ->add('username', null, array('label' => 'Username'))
        ->add('level', null, array('label' => 'Nivel'))
        ->add('message', null, array('label' => 'Mensaje'))
        ->add('codigo', null, array(
            'label' => 'Código',
            'required' => false
        ))         
        ->add('createdAt', null, array('label' => 'Fecha'))        
//        ->add('method', null, array('label' => 'Método'))
//        ->add('isAjax', null, array('label' => 'Ajax'))
//        ->add('httpHost', null, array('label' => 'Url'))
        ->add('clientIp', null, array('label' => 'Ip'))
        ->add('entityIds', null, array('label' => 'EntityIds'))                
//        ->add('isSecure', null, array('label' => 'Https'))
        ;
    }
    
    //OPCIONAL: Atributos utilizados para los filtros de búsqueda en el listar
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
        ->add('id', null, array('label' => 'ID'))
        ->add('username', null, array('label' => 'Username'))
        ->add('level', null, array('label' => 'Nivel'))
        ->add('message', null, array('label' => 'Mensaje'))
        ->add('codigo', null, array('label' => 'Código'))         
        ->add('createdAt', null, array('label' => 'Fecha'))        
//        ->add('method', null, array('label' => 'Método'))
//        ->add('isAjax', null, array('label' => 'Ajax'))
//        ->add('httpHost', null, array('label' => 'Url'))
        ->add('clientIp', null, array('label' => 'Ip'))
        ->add('entityIds', null, array('label' => 'EntityIds'))
//        ->add('isSecure', null, array('label' => 'Https'))
        ;
    }
   
    protected function configureShowFields(ShowMapper $showMapper)
     {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('username', null, array('label' => 'Username'))
            ->add('channel', null, array('label' => 'Channel'))
            ->add('level', null, array('label' => 'Nivel'))
            ->add('message', null, array('label' => 'Mensaje'))
            ->add('codigo', null, array('label' => 'Código'))         
            ->add('createdAt', null, array('label' => 'Fecha'))        
            ->add('method', null, array('label' => 'Método'))
            ->add('isAjax', null, array('label' => 'Ajax'))
            ->add('scheme', null, array('label' => 'Scheme'))
            ->add('httpHost', null, array('label' => 'Url'))
            ->add('clientIp', null, array('label' => 'Ip'))
            ->add('isSecure', null, array('label' => 'IsSecure'))
            ->add('entity', null, array('label' => 'Entity'))
            ->add('entityIds', null, array('label' => 'EntityIds'))
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
