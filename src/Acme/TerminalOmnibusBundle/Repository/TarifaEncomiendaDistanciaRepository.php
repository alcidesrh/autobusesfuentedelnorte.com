<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class TarifaEncomiendaDistanciaRepository extends EntityRepository
{
    public function getTarifaEncomiendaDistancia($idEstacionOrigen, $idEstacionDestino, $fechaSistema)
    {
        $query =  " SELECT e FROM Acme\TerminalOmnibusBundle\Entity\TarifaEncomiendaDistancia e "
                . " INNER JOIN e.estacionOrigen eo "
                . " INNER JOIN e.estacionDestino ed "
                . " WHERE "
                . " ((eo.id = :idEstacionOrigen  and ed.id = :idEstacionDestino) or "
                . "  (eo.id = :idEstacionDestino and ed.id = :idEstacionOrigen)) "
                . " and (e.fechaEfectividad <= :fechaSistema)"
                . " ORDER BY e.fechaEfectividad DESC ";
        
        try {
            $tarifa = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idEstacionOrigen', $idEstacionOrigen)
                ->setParameter('idEstacionDestino', $idEstacionDestino)    
                ->setParameter('fechaSistema', $fechaSistema->format('d-m-Y H:i:s'))
                ->getSingleResult();
            return $tarifa;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
