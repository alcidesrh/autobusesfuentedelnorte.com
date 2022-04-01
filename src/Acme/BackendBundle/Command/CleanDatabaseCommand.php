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
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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
        $output->writeln("Start");
        $date = new DateTime();
        $date = $date->modify('-1 year');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $param = array(
            'AcmeTerminalOmnibusBundle' => array(
                array('Salida', 'fecha'),
                array('EncomiendaBitacora', 'fecha'),
                'Encomienda',
                array('VoucherAgencia', 'fecha'),
                array('VoucherEstacion', 'fecha'),
                // array('VoucherInternet', 'fecha'),
                'Boleto',
                //    'AutorizacionCortesia',
                'AutorizacionInterna',
                'AutorizacionOperacion',
                array('OperacionCaja', 'fecha'),
                'Caja',
                array('CalendarioFacturaFecha', 'fecha'),
                'FacturaGenerada',
                'Talonario',
                'Reservacion',
            ),
            // 'AcmeBackendBundle' => array(array('LogItem', 'createdAt'))
        );
        foreach ($param as $bundle => $value) {
            foreach ($value as $entity) {
                $this->delete($entity, $bundle, $output);
            }
        }

        $start2 = new DateTime();
        $output->writeln("-----------------------------------------------");
        $output->writeln("Eliminando LogItem...");

        $query = "DELETE FROM AcmeBackendBundle:LogItem";
        $numDeleted = $em->createQuery($query)->execute();

        $interval = $start2->diff(new DateTime());
        $hours   = $interval->format('%h');
        $minutes = $interval->format('%i');
        $seconds = $interval->format('%i');
        $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        $output->writeln("LogItem: $numDeleted registros eliminados en $totalSeconds segundos.");

        // $this->deleteClientes($output);

        $end = new DateTime();
        $interval = $start->diff($end);
        $hours   = $interval->format('%h');
        $minutes = $interval->format('%i');
        $output->writeln('Total in minutes is: ' . ($hours * 60 + $minutes));

        $output->writeln("End");
    }

    public function delete($target, $bundle, $output)
    {
        $date = new DateTime();
        $date = $date->modify('-1 year');
        $em = $this->getContainer()->get('doctrine')->getManager();


        $output->writeln("-----------------------------------------------");

        $query = "DELETE FROM $bundle:%entity% e WHERE e.fechaCreacion is NULL OR e.fechaCreacion < :dateP";

        if (is_array($target)) {
            $entity = $target[0];
            $query = str_replace('%entity%', $entity, $query);
            $query = str_replace('fechaCreacion', $target[1], $query);
        } else {
            $entity = $target;
            $query = str_replace('%entity%', $target, $query);
        }

        $q = $em->createQuery($query)->setParameter('dateP', $date);

        $start = new DateTime();
        $output->writeln("Eliminando $entity...");

        $numDeleted = $q->execute();

        $interval = $start->diff(new DateTime());
        $hours   = $interval->format('%h');
        $minutes = $interval->format('%i');
        $seconds = $interval->format('%i');
        $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        $output->writeln("$entity: $numDeleted registros eliminados en $totalSeconds segundos.");
    }

    public function deleteClientes($output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();

        $start = new DateTime();
        $em = $this->getContainer()->get('doctrine')->getManager();

        $output->writeln("-----------------------------------------------");

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addScalarResult('id', 'id');

        $query = 'SELECT cliente.id FROM cliente INNER JOIN boleto ON boleto.cliente_boleto = cliente.id GROUP BY cliente.id UNION SELECT cliente.id FROM cliente INNER JOIN boleto ON boleto.cliente_documento = cliente.id GROUP BY cliente.id UNION SELECT cliente.id FROM cliente INNER JOIN encomienda ON encomienda.cliente_remitente = cliente.id GROUP BY cliente.id UNION SELECT cliente.id FROM cliente INNER JOIN encomienda ON encomienda.cliente_documento = cliente.id GROUP BY cliente.id UNION SELECT cliente.id FROM cliente INNER JOIN encomienda ON encomienda.cliente_destinatario = cliente.id GROUP BY cliente.id UNION SELECT cliente.id FROM cliente INNER JOIN autorizacion_cortesia ON autorizacion_cortesia.restriccion_cliente = cliente.id GROUP BY cliente.id';

        $query = $em->createNativeQuery($query, $rsm);

        $items = $query->getResult();

        $ids = array_unique(array_map(function ($e) {
            return $e['id'];
        }, $items));
        
        $numDeleted = 0;

        while (true) {
            $q = $em->createQuery("SELECT cliente FROM AcmeTerminalOmnibusBundle:Cliente cliente")->setFirstResult($numDeleted)
                ->setMaxResults(10000);
            $iterableResult = $q->getResult();
            if (empty($iterableResult)) break;
            foreach ($iterableResult as $value) {
                if (in_array($value->getId(), $ids)) continue;
                $em->remove($value);
            }
            $em->flush(); // Executes all deletions.
            $em->clear();
            $numDeleted += 10000;
            $output->writeln("Clientes eliminados: $numDeleted");
            gc_collect_cycles();
        }
        return;

        $interval = $start->diff(new DateTime());
        $hours   = $interval->format('%h');
        $minutes = $interval->format('%i');
        $seconds = $interval->format('%i');
        $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        $output->writeln("Cliente: $numDeleted registros eliminados en $totalSeconds segundos.");
    }
}
