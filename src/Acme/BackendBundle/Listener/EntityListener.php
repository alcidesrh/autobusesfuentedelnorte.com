<?php

namespace Acme\BackendBundle\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Acme\BackendBundle\Entity\LogItem;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Acme\BackendBundle\Strategy\CustomDepthExclusionStrategy;
use JMS\Serializer\SerializationContext;

class EntityListener {
    
    protected $logger;
    protected $container;
    protected $enable;
    
    function __construct($container, LoggerInterface $logger) {
        $this->container = $container;
        $this->logger = $logger;
        $this->enable = true;
    }
    
    //Update
//    public function preUpdate(PreUpdateEventArgs $args)
//    {
//        $entity = $args->getEntity();
//        $class = get_class($entity);
//        var_dump("preUpdate-init-class:" . $class);
//        $entity = $eventArgs->getEntity();
//        $class = get_class($entity);
//        if(!($entity instanceof LogItem)){
////            var_dump($entity);
//            $changes = $eventArgs->getEntityChangeSet();
//            foreach ($changes as $field) {
////                var_dump($field);
////                $this->logger->notice($class . "Se cambio el field:" . $field);
//            }      
//    //        throw new \RuntimeException("a");
//        }
//    }
    
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if($this->enable === true){
            $em = $eventArgs->getEntityManager();
            $request = $this->container->get('request');
            $session = $request->getSession();
            if($session !== null){
                $notices = $session->get("notices");     
                $serializer = $this->container->get('jms_serializer');
                if($serializer !== null && $notices!= null && count($notices) !== 0){
                    foreach ($notices as $key => $value) {
                        $classMetadata = $em->getClassMetadata(get_class($value));
                        $data = array(
                            "idEntity" => $classMetadata->getIdentifierValues($value),
                            "entity" => "", //$serializer->serialize($value, "xml", SerializationContext::create()->addExclusionStrategy(new CustomDepthExclusionStrategy())),
                            "autoFlush" => false
                        );
                        $this->logger->notice($key, $data);
                    }
                    $session->set('notices', array());
                    $em->flush();
                }
            }
        }
    }
    
    public function onFlush(OnFlushEventArgs $args)
    {
        if($this->enable === true){
            $request = $this->container->get('request');
            $session = $request->getSession();
            if($session !== null){
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();

                $notices = array(); 
                $nro = 1;
                foreach ($uow->getScheduledEntityInsertions() AS $entity) {
                    if (!($entity instanceof LogItem)) {
                        $class = get_class($entity);
                        $message = "Acción nro:" . $nro . ". Insertando clase:" . $class;
                        $notices[$message] = $entity;
                        $nro = $nro + 1;
                    }
                }

                foreach ($uow->getScheduledEntityUpdates() AS $entity) {
                    if (!($entity instanceof LogItem)) {
                        $class = get_class($entity);
                        $message = "Acción nro:" . $nro . ". Actualizando clase:" . $class;
                        $notices[$message] = $entity;
                        $nro = $nro + 1;
                    }
                }

                foreach ($uow->getScheduledEntityDeletions() AS $entity) {
                    if (!($entity instanceof LogItem)) {
                        $class = get_class($entity);
                        $message = "Acción nro:" . $nro .  ". Eliminando clase:" . $class;
                        $notices[$message] = $entity;
                        $nro = $nro + 1;
                    }
                }

        //        foreach ($uow->getScheduledCollectionDeletions() AS $col) {
        //
        //        }
        //        
        //        foreach ($uow->getScheduledCollectionUpdates() AS $col) {            
        //
        //        }

                $session->set("notices", $notices);
            } 
        }
    }
    
     
}
