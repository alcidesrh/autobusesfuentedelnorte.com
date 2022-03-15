<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

class EstadoSalidaRepository extends EntityRepository
{
    public function getEstadoSalida($idSalida)
    {
        try {
            $query =  " SELECT es FROM Acme\TerminalOmnibusBundle\Entity\EstadoSalida es,"
                . " Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " WHERE s.id = :idSalida and s.estado = es";
            $estado = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idSalida', $idSalida)
                ->getSingleResult();
            return $estado;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
