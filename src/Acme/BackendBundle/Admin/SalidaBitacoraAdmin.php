<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class SalidaBitacoraAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_salida_bitacora_admin';
    protected $baseRoutePattern = 'salidabitacora';
    
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
            ->add('id', null, array('label' => 'ID'))
            ->add('fecha', null, array('label' => 'Fecha'))  
            ->add('estado', null, array('label' => 'Estado'))
            ->add('usuario', null, array('label' => 'Usuario'))   
            ->add('descripcion', null, array('label' => 'Descripción'))
        ->end();
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->add('show');
    }
    
}

?>
