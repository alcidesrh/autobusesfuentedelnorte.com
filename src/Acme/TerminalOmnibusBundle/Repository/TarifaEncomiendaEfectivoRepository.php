<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class TarifaEncomiendaEfectivoRepository extends EntityRepository
{
    public function getTarifaEncomiendaEfectivo($importe, $fechaSistema)
    {
        $query =  "SELECT e FROM Acme\TerminalOmnibusBundle\Entity\TarifaEncomiendaEfectivo e "
                . " WHERE "
                . " ((e.importeMinimo <= :importe and e.importeMaximo >= :importe) or "
                . "  (e.importeMinimo IS NULL     and e.importeMaximo IS NULL) or "
                . "  (e.importeMinimo <= :importe and e.importeMaximo IS NULL) or "
                . "  (e.importeMinimo IS NULL     and e.importeMaximo >= :importe))"
                . " and (e.fechaEfectividad <= :fechaSistema)"
                . " ORDER BY e.fechaEfectividad DESC ";
        
        try {
            $tarifa = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('importe', $importe)
                ->setParameter('fechaSistema', $fechaSistema->format('d-m-Y H:i:s'))
                ->getSingleResult();
            
            $tarifa->setImporte($importe);
            return $tarifa;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
