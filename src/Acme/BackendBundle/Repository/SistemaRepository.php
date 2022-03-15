<?php

namespace Acme\BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Doctrine\ORM\NoResultException;

class SistemaRepository extends EntityRepository
{
    
    public function getVariableSistema($codigo, $estacion)
    {
        if ($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }

        $query =  "SELECT s FROM Acme\BackendBundle\Entity\Sistema s "
                . " JOIN s.estacion e "
                . " WHERE "
                . " s.codigo = :codigo "
                . " and e.id = :estacion ";
        
        try {
            return $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('codigo', $codigo)
                ->setParameter('estacion', $estacion)
                ->getSingleResult();
        } catch (NoResultException $exc) {
            return null;
        }
    }
    
    public function listarVariablesPorEstacion($estacion)
    {
        if ($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }

        $query =  "SELECT s FROM Acme\BackendBundle\Entity\Sistema s "
                . " JOIN s.estacion e "
                . " WHERE "
                . " e.id = :estacion ";
        
        try {
            return $this->_em->createQuery($query)
                ->setParameter('estacion', $estacion)
                ->getResult();
            
        } catch (NoResultException $exc) {
            return array();
        }

        
        
    }
}

?>
