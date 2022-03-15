<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class ImpresoraOperacionesRepository extends EntityRepository
{
    public function getImpresoraOperacionesPorEstacion($estacion)
    {
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        
        try {
            $query =  " SELECT io from Acme\TerminalOmnibusBundle\Entity\ImpresoraOperaciones io "
                    . " LEFT JOIN io.estacion est "
                    . " WHERE "
                    . " est.id = :estacion ";
            
            $impresoraOperaciones = $this->_em->createQuery($query)
                    ->setParameter('estacion', $estacion)
                    ->getSingleResult();
            
            return $impresoraOperaciones;
            
        } catch (NoResultException $exc) {
            return null;
        }
    }
    
}

?>
