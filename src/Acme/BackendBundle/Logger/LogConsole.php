<?php

namespace Acme\BackendBundle\Logger;

use Symfony\Component\DependencyInjection\Container;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class LogConsole extends AbstractProcessingHandler{
    
    private $initialized = false;
    private $container;
    
    public function __construct(Container $container, $level = Logger::INFO, $bubble = true){
        parent::__construct($level, $bubble);
        $this->container = $container;
    }

    protected function write(array $record) {
        
        if (!$this->initialized) {
            $this->initialize();
        }      
        
        $message = $record['message'];
        $message = substr($message, 0, 999);
//        var_dump($message);
        
    }
    
     private function initialize(){
        $this->initialized = true;
    }
}
