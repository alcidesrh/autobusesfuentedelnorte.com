<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class PluginImpresionAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_plugin_impresion_admin';
    protected $baseRoutePattern = 'pluginimpresion';     
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clear(); 
    }
    
}

?>
