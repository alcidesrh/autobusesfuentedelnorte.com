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

class TestPHP2Command extends ContainerAwareCommand{
   
    protected function configure()
    {
        $this
            ->setName('SISTEMA:MyTest2')
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
            ->setDescription('Comando para probar metodos.')
            ->setHelp(<<<EOT
Comando para probar metodos.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Testing method2 ----- init');
        $contenedor = $this->getContainer();
        $contenedor->enterScope('request');
        $server = array('REMOTE_ADDR' => "127.0.0.1");
        $request =  new Request();
        $request->initialize(array(), array(), array(), array(), array(), $server);
        $request->setMethod("None");
        $request->setSession(new Session(new MockArraySessionStorage()));
        $contenedor->set('request', $request, 'request');
        $em = $contenedor->get('doctrine')->getManager();
        
        
        $output->writeln('Testing method2 ----- end');
    }
}

?>
