<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Acme\BackendBundle\Entity\Job;
use Acme\BackendBundle\Services\UtilService;
use DateTime;

class CleanDatabaseCommand extends ContainerAwareCommand
{


    protected function configure()
    {
        $this
            ->setName('clean-database')
            ->setHelp("Clean client table");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        /*
        Tablas para limpiar:
         -autorizacion_cortesia
         -autorizacion_interna
         -autorizacion_operacion
         -boleto
         -boleto_bitacora
         -boleto_voucher_agencia
         -boleto_voucher_estacion
         -boleto_voucher_internet
         -caja
         -caja_operacion
         -calendario_factura_fecha
         -calendario_factura_ruta
         -cliente
         -custom_log
         -encomienda
         -encomienda_bitacora
         -encomienda_ruta
         -factura
         -factura_generada
         -reservacion
         -salida
         -salida_bitacora
         -talonario
         -talonario_corte_venta
         -talonario_corte_venta_item
         -tarifas_boleto
         -tarjeta
         -tarjeta_bitacora
        */
        $start = new DateTime();
        $deleteNumber = 1000;
        $output->writeln("Start");
        $date = new DateTime();
        $date = $date->modify('-1 year');
        $em = $this->getContainer()->get('doctrine')->getManager();

        $stop = false;
        $deleted = 0;
        $param = array(
            'AcmeTerminalOmnibusBundle' => array('Encomienda', 'Boleto', 'AutorizacionCortesia', 'Caja', array('CalendarioFacturaFecha', 'fecha'), 'FacturaGenerada', 'Reservacion', array('Salida', 'fecha')),
            'AcmeBackendBundle' => array(array('LogItem', 'createdAt'))
        );
        foreach ($param as $key => $value) {
            foreach ($value as $entity) {
                $this->delete($entity, $key, $output);
            }
        }


        $end = new DateTime();
        $interval = $start->diff($end);
        $hours   = $interval->format('%h');
        $minutes = $interval->format('%i');
        $output->writeln('Diff. in minutes is: ' . ($hours * 60 + $minutes));

        $output->writeln("End");
    }

    public function delete($target, $bundle, $output)
    {

        $deleteNumber = 1000;
        $deleted = 0;
        $date = new DateTime();
        $em = $this->getContainer()->get('doctrine')->getManager();

        $stop = false;

        while (!$stop) {
            $stop = true;
            // $targets = ['Encomienda', 'Boleto', 'AutorizacionCortesia', 'Caja', ['CalendarioFacturaFecha', 'fecha'], 'FacturaGenerada', 'Reservacion', ['Salida', 'fecha']];
            // $targets = ['Encomienda', 'Boleto', ['Salida', 'fecha']];
            $output->writeln("Deleted: $deleted -----------------------------------------------");
            // foreach ($targets as $target) {

            $deleted += $deleteNumber;

            $query = "SELECT e FROM $bundle:%entity% e WHERE e.fechaCreacion is NULL OR e.fechaCreacion < :dateP";

            if (is_array($target)) {
                $query = str_replace('%entity%', $target[0], $query);
                $query = str_replace('fechaCreacion', $target[1], $query);
            } else {
                $query = str_replace('%entity%', $target, $query);
            }

            $items = $em->createQuery($query)->setParameter('dateP', $date)->setMaxResults($deleteNumber)->getResult();

            if (!empty($items)) {
                $stop = false;
                $cont = 0;
                foreach ($items as $item) {
                    $output->writeln(array($item->getId(), is_array($target) ? $target[0] : $target, ++$cont));
                    $em->remove($item);
                }
            }
            $em->flush();
            // }

            // $targets = [['LogItem', 'createdAt']];

            // foreach ($targets as $target) {

            //     $query = "SELECT e FROM AcmeBackendBundle:%entity% e WHERE e.fechaCreacion is NULL OR e.fechaCreacion < :dateP";

            //     if (is_array($target)) {
            //         $query = str_replace('%entity%', $target[0], $query);
            //         $query = str_replace('fechaCreacion', $target[1], $query);
            //     } else {
            //         $query = str_replace('%entity%', $target, $query);
            //     }

            //     $items = $em->createQuery($query)->setParameter(
            //         'dateP',
            //         $date
            //     )->setMaxResults($deleteNumber)->getResult();

            //     if (!empty($items)) {
            //         $stop = false;
            //         foreach ($items as $item) {
            //             $output->writeln([$item->getId(), is_array($target) ? $target[1] : $target]);
            //             $em->remove($item);
            //         }
            //     }
            //     $em->flush();
            // }
        }
    }
}
