<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Encomienda;
use Doctrine\ORM\NoResultException;

class EncomiendaBitacoraRepository extends EntityRepository
{
    public function getPrimeraSalida($encomienda)
    {
        try {
            if($encomienda instanceof Encomienda){
                $encomienda = $encomienda->getId();
            }
            
            $query =  " SELECT eb FROM Acme\TerminalOmnibusBundle\Entity\EncomiendaBitacora eb"
                    . " LEFT JOIN eb.encomienda en "
                    . " LEFT JOIN eb.estado es "
                    . " WHERE "
                    . " en.id=:encomienda "
                    . " and es.id = 3 " //En transito
                    . " and eb.estacion = en.estacionCreacion "
                    . " order by eb.id asc  ";
            
            $encomiendaBitacora = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('encomienda', $encomienda)
                    ->getSingleResult();
            return $encomiendaBitacora;
        } catch (NoResultException $exc) {
            return null;
        }
    }
    
}

?>
