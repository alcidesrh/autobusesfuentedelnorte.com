<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Caja;
use Doctrine\ORM\NoResultException;

class OperacionCajaRepository extends EntityRepository
{
    public function obtenerImporteTotal($caja)
    {
        try {
            
            if($caja instanceof Caja){
                $caja = $caja->getId();
            }
        
            $query =  " SELECT SUM(oc.importe) FROM Acme\TerminalOmnibusBundle\Entity\OperacionCaja oc "
                    . " LEFT JOIN oc.caja c "
                    . " WHERE "
                    . " c.id = :caja ";
            
            $item = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('caja', $caja)
                    ->getSingleResult();
            return $item[1];
            
        } catch (NoResultException $exc) {
            return 0;
        }
    }
    
    public function obtenerTotalOperaciones($caja)
    {
        try {
            
            if($caja instanceof Caja){
                $caja = $caja->getId();
            }
        
            $query =  " SELECT COUNT(oc.id) FROM Acme\TerminalOmnibusBundle\Entity\OperacionCaja oc "
                    . " LEFT JOIN oc.caja c "
                    . " WHERE "
                    . " c.id = :caja ";
            
            $item = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('caja', $caja)
                    ->getSingleResult();
            return $item[1];
            
        } catch (NoResultException $exc) {
            return 0;
        }
    }
}

?>
