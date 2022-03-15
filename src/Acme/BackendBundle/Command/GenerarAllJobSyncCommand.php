<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Acme\BackendBundle\Services\UtilService;

class GenerarAllJobSyncCommand extends ContainerAwareCommand{
   
    protected function configure()
    {
        $this
            ->setName('SISTEMA:GenerarAllJobSync')
            ->setDefinition(array(
                new InputArgument(
                    'nameArgument',
                    InputArgument::OPTIONAL,
                    'descripcionArgument',
                    'defaultValueArgument'
                ),
                new InputOption(
                     'nameOption',
                     null,
                     InputOption::VALUE_OPTIONAL,
                     'descripcionOption',
                     'defaultValueOption'
                 ),
            ))
            ->setDescription('Comando para generar todos los items de actualizacion de las tablas de integracion en el sitio web.')
            ->setHelp(<<<EOT
Comando para generar todos los items de actualizacion de las tablas de integracion en el sitio web.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('GenerarAllJobSyncCommand execute ----- init');
        $contenedor = $this->getContainer();
        $contenedor->enterScope('request');
        $server = array('REMOTE_ADDR' => "127.0.0.1");
        $request =  new Request();
        $request->initialize(array(), array(), array(), array(), array(), $server);
        $request->setMethod("None");
        $request->setSession(new Session(new MockArraySessionStorage()));
        $contenedor->set('request', $request, 'request');
        $em = $contenedor->get('doctrine')->getManager();
        $jobSyncService = $contenedor->get('acme_job_sync');
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:Departamento')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD DEPARTAMENTO: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:Estacion')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD ESTACION: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:TipoBus')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD TIPO BUS: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:HorarioCiclico')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD HORARIO CICLICO: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:Ruta')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD RUTA: '. $item->getCodigo());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:TarifaBoleto')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD TARIFA BOLETO: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:ItinerarioCiclico')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD ITINERARIO CICLICO: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:ItinerarioEspecial')->listarItinerariosEspecialesFuturos();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD ITINERARIO ESPECIAL: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:Tiempo')->findAll();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD TIEMPO: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $items = $em->getRepository('AcmeTerminalOmnibusBundle:Salida')->listarSalidasFuturas();
        foreach ($items as $item) {
            if($item->isValidToSync()){
                $output->writeln('ADD SALIDA: '. $item->getId());
                $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                $jobSync->setNivel($item->getNivelSync());
                $jobSync->setType($item->getTypeSync());
                $jobSync->setData($item->getDataArrayToSync());
                $jobSyncService->createJobSync($jobSync, false);
            }
        }
        $em->flush();
        
        $output->writeln('GenerarAllJobSyncCommand execute ----- end');
    }
}

?>
