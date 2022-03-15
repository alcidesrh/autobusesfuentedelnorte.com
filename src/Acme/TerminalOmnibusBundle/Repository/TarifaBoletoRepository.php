<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class TarifaBoletoRepository extends EntityRepository
{
    public function getTarifaBoleto($idEstacionOrigen, $idEstacionDestino, $idClaseBus, $idClaseAsiento, $fechaSalida, $fechaEfectividad)
    {
        try {
            
           $horaSalida = $fechaSalida->format('H:i:s');        
        
           $query =  "SELECT e FROM Acme\TerminalOmnibusBundle\Entity\TarifaBoleto e "
                . " JOIN e.estacionOrigen eo "
                . " JOIN e.estacionDestino ed "
                . " JOIN e.claseBus cb "
                . " JOIN e.claseAsiento ca "
                . " WHERE "
                . " (eo.id = :idEstacionOrigen) and "
                . " (ed.id = :idEstacionDestino) and "
                . " (cb.id = :idClaseBus) and "
                . " (ca.id = :idClaseAsiento) and "
                . " ((e.horaInicialSalida is null) or ((e.horaInicialSalida is not null) and (e.horaInicialSalida <= :horaSalida))) and "
                . " ((e.horaFinalSalida is null) or ((e.horaFinalSalida is not null) and (e.horaFinalSalida >= :horaSalida))) and "   
                . " (e.fechaEfectividad <= :fechaEfectividad) ";
           
            $query .= " ORDER BY e.fechaEfectividad DESC, e.id DESC ";
            
            return $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idEstacionOrigen', $idEstacionOrigen)
                ->setParameter('idEstacionDestino', $idEstacionDestino)
                ->setParameter('idClaseBus', $idClaseBus)    
                ->setParameter('idClaseAsiento', $idClaseAsiento)
                ->setParameter('horaSalida', $horaSalida)
                ->setParameter('fechaEfectividad', $fechaEfectividad->format('d-m-Y H:i:s'))
                ->getSingleResult();
            
        } catch (NoResultException $exc) {
//            var_dump($exc->getMessage());
            return null;
        } catch (\Exception $exc) {
//            var_dump($exc->getMessage());
            return null;
        }
    }
}

?>
