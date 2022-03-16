<?php

namespace Acme\BackendBundle\Services;

use Acme\BackendBundle\Scheduled\ScheduledServiceInterface;
use Acme\BackendBundle\Entity\Job;

class ExpiredUserService implements ScheduledServiceInterface{
    
    protected $container;
    protected $doctrine;
    protected $utilService;
    protected $logger;
    protected $options;
    protected $job;
    
    public function __construct($container) {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
        $this->utilService = $this->container->get('acme_backend_util');
        $this->logger = $this->container->get('logger');
        $this->options = array();
        $this->job = null;
    }   
    
    private function getCurrentFecha(){
        if($this->job === null){
            return new \DateTime();
        }else{
            return clone $this->job->getNextExecutionDate();
        }
    }
    
    public function expiredUser($options = null){
        $this->logger->warn("expiredUser ----- INIT -------");
        var_dump("expiredUser ----- INIT -------");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }

        $fechaLimiteSistema = $this->getCurrentFecha();
        $this->logger->warn("Buscando usuarios que sus credenciales expiraron en la fecha: " . $fechaLimiteSistema->format('d-m-Y H:i:s'));
        $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findExpiredCredentialsUser($fechaLimiteSistema);
        if(count($usuarios) === 0){
            $this->logger->warn("No existen usuarios para expirar por credenciales.");
        }else{
            $em = $this->doctrine->getManager();
            $usuariosArray = array(); 
            foreach ($usuarios as $usuario) {
                $this->logger->warn("Expirando usuario: " . $usuario->getId() . ", username: " . $usuario->getUsername());
                $usuario->setCredentialsExpired(true);
                $em->persist($usuario);
                $usuariosArray[] = $usuario->__toString();
            }
            
//            $correos = $this->container->get("doctrine")->getRepository('AcmeBackendBundle:User')->findEmailSuperAdmin();
//            if(count($correos) !== 0){
//                $now = new \DateTime();
//                $now = $now->format('Y-m-d H:i:s');
//                $subject = "USER_PASS_" . $now . ". Usuarios expirados por no cambiar su constraseña en el tiempo establecido."; 
//                UtilService::sendEmail($this->container, $subject, $correos, "Usuarios:\n" . implode("\n", $usuariosArray));
//            }       
        }
        
        $fechaLastLogin = $this->getCurrentFecha();
        $fechaLastLogin->modify("-60 day");
        $this->logger->warn("Buscando usuarios con ultima fecha de login: " . $fechaLastLogin->format('d-m-Y H:i:s'));
        $usuarios = $this->doctrine->getRepository('AcmeBackendBundle:User')->findExpiredUser($fechaLastLogin);
        if(count($usuarios) === 0){
            $this->logger->warn("No existen usuarios para expirar por no entrar al sistema.");
        }else{
            $em = $this->doctrine->getManager();
            $usuariosArray = array(); 
            foreach ($usuarios as $usuario) {
                $this->logger->warn("Expirando usuario: " . $usuario->getId() . ", userame: " . $usuario->getUsername());
                $usuario->setExpired(true);
                $usuario->setExpiresAt(new \DateTime());
                $em->persist($usuario);
                $usuariosArray[] = $usuario->__toString();
            } 
            
//            $correos = $this->container->get("doctrine")->getRepository('AcmeBackendBundle:User')->findEmailSuperAdmin();
//            if(count($correos) !== 0){
//                $now = new \DateTime();
//                $now = $now->format('Y-m-d H:i:s');
//                $subject = "USER_INACT_" . $now . ". Usuarios expirados por no entrar al sistema desde hace 2 meses."; 
//                UtilService::sendEmail($this->container, $subject, $correos, "Usuarios:\n" . implode("\n", $usuariosArray));
//            }
        }
        $this->logger->warn("expiredUser ----- END -------");
    }
     
    public function setScheduledJob(Job $job = null) {
        $this->logger->warn("ExpiredUser Service - init");
        $this->job = $job;
        //$this->expiredUser();
        $this->logger->warn("ExpiredUser Service - end");
    }
    
}
