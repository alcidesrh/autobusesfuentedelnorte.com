<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Acme\BackendBundle\Entity\Job;

class ReportarFacturasCommand extends ContainerAwareCommand{
            
    protected function configure()
    {
        $this
            ->setName('SISTEMA:reportar:facturas')
            ->setHelp("Reportar Facturas")
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();
        $contenedor->enterScope('request');
        $server = array('REMOTE_ADDR' => "127.0.0.1");
        $request =  new Request();
        $request->initialize(array(), array(), array(), array(), array(), $server);
        $request->setMethod("None");
        $request->setSession(new Session(new MockArraySessionStorage()));
        $contenedor->set('request', $request, 'request');
        $em = $contenedor->get('doctrine')->getManager();
  
        try {
            if($job = $em->getRepository('AcmeBackendBundle:Job')->findOneBy(['serviceId' => 'acme_backend_reportar_facturas', 'status' => Job::STATUS_WAITING])){
                $job->getProxy()->setDoctrine($contenedor->get('doctrine'));
                $job->execute($contenedor->get($job->getServiceId()));
                $em->persist($job);
                $em->flush($job);
            }
            
        } catch (\RuntimeException $ex) {
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $body = "Error: " . $ex->getMessage() . ". \nTrace:\n" . $ex->getTraceAsString();
            $logger = $contenedor->get("logger");
            $logger->warn("Error $now ExpirarUsuarioCommand $body ");
        } catch (\Exception $ex) {
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $body = "Error: " . $ex->getMessage() . ". \nTrace:\n" . $ex->getTraceAsString();
            $logger = $contenedor->get("logger");
            $logger->warn("Error $now ExpirarUsuarioCommand $body ");
        }
    }
}
