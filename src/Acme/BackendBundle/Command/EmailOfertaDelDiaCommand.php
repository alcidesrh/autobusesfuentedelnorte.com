<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class EmailOfertaDelDiaCommand extends ContainerAwareCommand{
   
    protected function configure()
    {
        $this
            ->setName('email:oferta-del-dia')
            ->setDefinition(array(
                new InputArgument(
                    'ciudad',
                    InputArgument::OPTIONAL,
                    'El slug de la ciudad para la que se generan los emails'
                ),
                new InputOption(
                     'accion',
                     null,
                     InputOption::VALUE_OPTIONAL,
                     'Indica si los emails sólo se generan o también se envían',
                     'enviar'
                 ),
            ))
            ->setDescription('Genera y envía a cada usuario el email con la oferta diaria')
            ->setHelp(<<<EOT
El comando <info>email:oferta-del-dia</info> genera y envía un email con la 
oferta del día de la ciudad en la que se ha apuntado el usuario.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();
        $em = $contenedor->get('doctrine')->getManager();
        
        $output->writeln('Comienza el proceso de generación de emails...');
        
        $ciudad = $input->getArgument('ciudad');
        $accion = $input->getOption('accion');
        
        $output->write(array(
            'Generados <info>10</info> emails',
            '<comment>Comienza el envío de los mensajes</comment>',
            '<info>Conectando</info> con el <comment>servidor de correo</comment>...'
        ));
        
        $dialog = $this->getHelperSet()->get('dialog');
        $respuesta = $dialog->askConfirmation($output, '<question>¿Quieres enviar ahora todos los emails?</question>', 'n');
        $output->writeln('Bueno la respuesta fue:'.$respuesta);
        
        $documento = $this->getContainer()->getParameter('kernel.root_dir')
            .'/../web/uploads/documentos/promocion.pdf';
                
        $mensaje = \Swift_Message::newInstance()
            ->setSubject('Oferta del día')
            ->setFrom(array('mailing@cupon.com' => 'Cupon - Oferta del día'))
            ->setTo('usuario1@localhost')
            ->setBody("Hi")
            ->attach(\Swift_Attachment::fromPath($documento))
        ;
        $contenedor->get('mailer')->send($mensaje);
    }
}

?>
