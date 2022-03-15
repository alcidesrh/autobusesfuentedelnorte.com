<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class TipoCambioRepository extends EntityRepository
{
    public function getTipoCambio($idMoneda, $tipoTipoCambio=2) //Por defecto se trabaja con la compra
    {
        try {
            $query =  "SELECT tc from Acme\TerminalOmnibusBundle\Entity\TipoCambio tc "
                    . " JOIN tc.tipoTipoCambio ttc "
                    . " JOIN tc.moneda m "
                    . " WHERE "
                    . " m.id = :idMoneda "
                    . " and ttc.id = :tipoTipoCambio "
                    . " ORDER BY tc.fecha DESC";
             return $this->_em->createQuery($query)
                        ->setMaxResults(1)
                        ->setParameter('idMoneda', $idMoneda)
                        ->setParameter('tipoTipoCambio', $tipoTipoCambio)
                        ->getSingleResult();
             
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
