<?php

namespace Acme\BackendBundle\Logger;

use Symfony\Component\DependencyInjection\Container;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Acme\BackendBundle\Entity\LogItem;

class LogDBHandler extends AbstractProcessingHandler{
    
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
        
//        return false;
        
        $message = $record['message'];
        if(stristr($message, "[cms::") !== false){
            return false;
        }
        
        $logitem = new LogItem();        
        $logitem->setChannel($record['channel']);
        $logitem->setLevel($record['level_name']);
        $message = substr($message, 0, 999);
        $logitem->setMessage($message);
//        var_dump($message);
        $logitem->setCreatedAt($record['datetime']);        
        if($this->container->get('security.context')->getToken() !== null && 
           $this->container->get('security.context')->getToken()->getUser() !== null &&
           $this->container->get('security.context')->getToken()->getUser() !== "anon." ){
            $logitem->setUsername($this->container->get('security.context')->getToken()->getUser()->getUsername());
        }else {
           $session = $this->container->get('request')->getSession(); 
           if($session !== null && $session->get("username") !== null && trim($session->get("username")) !== ""){
               $logitem->setUsername($session->get("username"));
           }else{
               $logitem->setUsername("anonimo");
           }
        }
        
        $context = $record['context'];
//        var_dump($context);
        if(array_key_exists("code", $context)){
            $code = trim($context['code']);
            $item = $this->container->get('doctrine')->getManager()->getRepository('AcmeBackendBundle:LogCode')->find($code);
            if($item !== null){
                $logitem->setCodigo($item);
            }else{
                throw new \RuntimeException("El código de error:".code." no existe en DB.");
            }
        }
        
        if(array_key_exists("entity", $context)){
            $logitem->setEntity($context['entity']);
        }
        
        if(array_key_exists("idEntity", $context)){
            $str = "";
            foreach ($context['idEntity'] as $key => $value) {
                $str .= "<" . $key . ":|" . $value . "|>"; 
            }
            $logitem->setEntityIds($str);
        }
        
        $request = $this->container->get('request');
//        var_dump($request);
        $logitem->setMethod($request->getMethod());
        $logitem->setIsAjax($request->isXmlHttpRequest());
        $logitem->setScheme($request->getScheme());
        $logitem->setHttpHost($request->getUri());
        $logitem->setClientIp($request->getClientIp());
        $logitem->setIsSecure($request->isSecure());
        
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($logitem);
        
        $autoFlush = true;
         if(array_key_exists("autoFlush", $context)){
            $autoFlush = $context['autoFlush'];
        }
        
        if($autoFlush === true){
             $em->flush();
        }
        
    }
    
     private function initialize(){
        $this->initialized = true;
    }
}
