<?php

namespace Acme\BackendBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class TipoImpresoraAdmin extends Admin{
    
    protected $translationDomain = 'messages';
    protected $baseRouteName = 'sonata_tipo_impresora_admin';
    protected $baseRoutePattern = 'tipoimpresora';     
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clear(); 
    }
    
}

?>
