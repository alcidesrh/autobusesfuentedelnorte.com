<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Ruta;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\TerminalOmnibusBundle\Entity\ClaseBus;
use Doctrine\ORM\NoResultException;

class TiempoRepository extends EntityRepository
{
    public function getTiempo($ruta, $estacion, $claseBus)
    {
        if($ruta instanceof Ruta){
            $ruta = $ruta->getCodigo();
        }
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        if($claseBus instanceof ClaseBus){
            $claseBus = $claseBus->getId();
        }
        
        $query =  " SELECT t FROM Acme\TerminalOmnibusBundle\Entity\Tiempo t "
                . " INNER JOIN t.ruta r "
                . " INNER JOIN t.estacionDestino e "
                . " INNER JOIN t.claseBus c "
                . " WHERE "
                . " r.codigo = :ruta and "
                . " e.id = :estacion and "
                . " c.id = :claseBus ";
        
        try {
            $tiempo = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('ruta', $ruta)
                ->setParameter('estacion', $estacion)
                ->setParameter('claseBus', $claseBus)
                ->getSingleResult();
            return $tiempo;
        } catch (NoResultException $exc) {
            return null;
        }
    }
}

?>
