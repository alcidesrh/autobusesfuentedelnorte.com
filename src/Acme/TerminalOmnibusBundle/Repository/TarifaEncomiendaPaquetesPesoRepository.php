<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class TarifaEncomiendaPaquetesPesoRepository extends EntityRepository
{
    public function getTarifaEncomiendaPaquetesPeso($peso, $fechaSistema)
    {
        $query =  " SELECT e FROM Acme\TerminalOmnibusBundle\Entity\TarifaEncomiendaPaquetesPeso e "
                . " WHERE "
                . " ((e.pesoMinimo <= :peso and e.pesoMaximo >= :peso) or "
                . "  (e.pesoMinimo IS NULL  and e.pesoMaximo IS NULL) or "
                . "  (e.pesoMinimo <= :peso and e.pesoMaximo IS NULL) or "
                . "  (e.pesoMinimo IS NULL  and e.pesoMaximo >= :peso))"
                . " and (e.fechaEfectividad <= :fechaSistema) "
                . " ORDER BY e.fechaEfectividad DESC ";
        
        try {
            $tarifa = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('peso', $peso)
                ->setParameter('fechaSistema', $fechaSistema->format('d-m-Y H:i:s'))
                ->getSingleResult();
            
            $tarifa->setPeso($peso);
            return $tarifa;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
