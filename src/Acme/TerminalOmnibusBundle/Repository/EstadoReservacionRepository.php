<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class EstadoReservacionRepository extends EntityRepository
{
    public function getEstadoReservacion($idReservacion)
    {
        try {
            $query =  " SELECT er FROM Acme\TerminalOmnibusBundle\Entity\EstadoReservacion er,"
                    . " Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                    . " LEFT JOIN r.estado e "
                    . " WHERE r.id=:idReservacion and e.id = er.id ";
            $estado = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('idReservacion', $idReservacion)
                    ->getSingleResult();
            return $estado;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
