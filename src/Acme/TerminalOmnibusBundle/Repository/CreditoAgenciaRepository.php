<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class CreditoAgenciaRepository extends EntityRepository
{
    
    public function getCreditoByEstacion($estacion)
    {
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        
        try {
            $query =  " SELECT ca FROM Acme\TerminalOmnibusBundle\Entity\CreditoAgencia ca "
                    . " INNER JOIN ca.estacion es "
                    . " WHERE es.id = :estacion ";
            $item = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('estacion', $estacion)
                    ->getSingleResult();
            return $item;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
