<?php

namespace Acme\BackendBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Psr\Log\LoggerInterface;

class PageLoadListener {
    
    protected $logger;
    protected $container;
    
    function __construct($container, LoggerInterface $logger) {
        $this->container = $container;
        $this->logger = $logger;
    }
    
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if(strpos($request->getPathInfo(), "_wdt") === false ){ //Debug
            $route  = $request->attributes->get('_route');
            $message = "";
            $data = array();
            if($route === null || trim($route) === ""){
                $data["code"] = 'SEC004';
                $message = "El usuario solicitó una ruta que no existe.";
            }else{
                $controller = $request->attributes->get('_controller');
                $message = "Accediendo a la ruta:" . $route . ", en controlador:" . $controller . ".";
            } 
            $this->logger->notice($message, $data);
        }
    }
    
    
}
