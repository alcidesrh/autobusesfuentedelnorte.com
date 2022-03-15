<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class TarifaEncomiendaPaquetesVolumenRepository extends EntityRepository
{
    public function getTarifaEncomiendaPaquetesVolumen($volumen, $fechaSistema)
    {
        $query =  " SELECT e FROM Acme\TerminalOmnibusBundle\Entity\TarifaEncomiendaPaquetesVolumen e "
                . " WHERE "
                . " ((e.volumenMinimo <= :volumen and e.volumenMaximo >= :volumen) or "
                . "  (e.volumenMinimo IS NULL     and e.volumenMaximo IS NULL) or "
                . "  (e.volumenMinimo <= :volumen and e.volumenMaximo IS NULL) or "
                . "  (e.volumenMinimo IS NULL     and e.volumenMaximo >= :volumen))"
                . " and (e.fechaEfectividad <= :fechaSistema) "
                . " ORDER BY e.fechaEfectividad DESC ";
        
        try {
            $tarifa = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('volumen', $volumen)
                ->setParameter('fechaSistema', $fechaSistema->format('d-m-Y H:i:s'))
                ->getSingleResult();
            
            $tarifa->setVolumen($volumen);
            return $tarifa;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
