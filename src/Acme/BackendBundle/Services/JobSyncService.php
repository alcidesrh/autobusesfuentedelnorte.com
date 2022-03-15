<?php

namespace Acme\BackendBundle\Services;
use Acme\BackendBundle\Entity\JobSync;

class JobSyncService {
    
    protected $container;
    protected $doctrine;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
    }
    
    public function createJobSync(JobSync $jobSync, $autoFlush = true){
        $em = $this->doctrine->getManager();
        $em->persist($jobSync);
        if($autoFlush){
            $em->flush();
        }
    }
}
