<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class EstadoBoletoRepository extends EntityRepository
{
    public function getEstadoBoleto($idBoleto)
    {
        try {
            $query =  " SELECT eb FROM Acme\TerminalOmnibusBundle\Entity\EstadoBoleto eb,"
                    . " Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.estado e "
                    . " WHERE b.id=:idBoleto and e.id = eb.id ";
            $estado = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('idBoleto', $idBoleto)
                    ->getSingleResult();
            return $estado;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
