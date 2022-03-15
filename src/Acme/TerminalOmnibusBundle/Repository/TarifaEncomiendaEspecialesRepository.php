<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class TarifaEncomiendaEspecialesRepository extends EntityRepository
{
    public function getTarifaEncomiendaEspeciales($idTipo, $fechaSistema)
    {
        $query =  "SELECT e FROM Acme\TerminalOmnibusBundle\Entity\TarifaEncomiendaEspeciales e "
                . " JOIN e.tipo t "
                . " WHERE "
                . " (t.id = :idTipo) and "
                . " (e.fechaEfectividad <= :fechaSistema) "
                . " ORDER BY e.fechaEfectividad DESC ";
        
        try {
            return $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idTipo', $idTipo)
                ->setParameter('fechaSistema', $fechaSistema->format('d-m-Y H:i:s'))
                ->getSingleResult();
            
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
