<?php

namespace Acme\TerminalOmnibusBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Security\Authorization\RememberingAccessDecisionManager;
use Knp\Menu\MenuItem;

class Builder extends ContainerAware{
   
    /** @var Router */     
    private $router;     
    /** * @var SecurityContext */     
    private $securityContext;     
    /** * @var MetadataFactory */     
    private $metadataFactory;
    
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $securityContext = $this->container->get('security.context');
//        $user = $securityContext->getToken()->getUser();       
    
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute("class", 'nav nav-list');
        
        $item = $menu->addChild('Boletos')->setUri("#");
        $route = 'ventaBoletos-case1';
        $item = $menu->addChild('Vender', array('route' => $route))->setExtra('route', $route);
        $item->setAttribute('class', 'nav-header');
        $route = 'reVenderBoleto-case1';
        $item = $menu->addChild('Revender', array('route' => $route))->setExtra('route', $route);
        $route = 'consultarBoleto-case1';
        $item = $menu->addChild('Consultar', array('route' => $route))->setExtra('route', $route);
        $route = 'anularBoleto-case1';
        $item = $menu->addChild('Anular', array('route' => $route))->setExtra('route', $route);
        $route = 'chequearBoleto-case1';
        $item = $menu->addChild('Chequear', array('route' => $route))->setExtra('route', $route);
        $route = 'reasignarBoleto-case1';
        $item = $menu->addChild('Reasignar', array('route' => $route))->setExtra('route', $route);
        
        $item = $menu->addChild('Encomiendas')->setUri("#");
        $route = 'registrarEncomienda-case1';
        $item->addChild('Registrar', array('route' => $route))->setExtra('route', $route);
        $item->setAttribute('class', 'nav-header');
        $route = 'asignarEncomienda-case1';
        $item->addChild('Asignar', array('route' => $route))->setExtra('route', $route);
        $route = 'desembarcarEncomienda-case1';
        $item->addChild('Desembarcar', array('route' => $route))->setExtra('route', $route);
        $route = 'entregarEncomienda-case1';
        $item->addChild('Entregar', array('route' => $route))->setExtra('route', $route);
        $route = 'anularEncomienda-case1';
        $item->addChild('Anular', array('route' => $route))->setExtra('route', $route);
        
        $this->filterMenu($menu);
        return $menu;
    }
    
    public function filterMenu(MenuItem $menu){
        $hasChildren = count($menu->getChildren()) !== 0;
        foreach ($menu->getChildren() as $child) { 
            $route = $child->getExtra('route');      
            if ($route && !$this->hasRouteAccess($route)) { 
                $menu->removeChild($child);
            }
            if(!$this->filterMenu($child)){
                $menu->removeChild($child);
            }
        }        
        if($hasChildren && count($menu->getChildren()) === 0){
            return false;
        }else {
            return true;    
        }
    }
        
    public function hasRouteAccess($routeName){ 
        $token = $this->getSecurityContext()->getToken();         
        if ($token->isAuthenticated()) { 
            $route = $this->getRouter()->getRouteCollection()->get($routeName);      
            $controller = $route->getDefault('_controller');             
            list($class, $method) = explode('::', $controller, 2);               
            $metadata = $this->getMetadataFactory()->getMetadataForClass($class);    
            if (!isset($metadata->methodMetadata[$method])) {
                return false;
            }            
            foreach ($metadata->methodMetadata[$method]->roles as $role) {
                if ($this->getSecurityContext()->isGranted($role)) {
                    return true;
                }
             }
         }         
         return false;  
    }
    
    public function getRouter() {
        if($this->router === null){ $this->router = $this->container->get('router'); }
        return $this->router;
    }

    public function getSecurityContext() {
        if($this->securityContext === null){ $this->securityContext = $this->container->get('security.context'); }
        return $this->securityContext;
    }

    public function getMetadataFactory() {
        if($this->metadataFactory === null){ $this->metadataFactory = $this->container->get('security.extra.metadata_factory'); }
        return $this->metadataFactory;
    }
}

?>
