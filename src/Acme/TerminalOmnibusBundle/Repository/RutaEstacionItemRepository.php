<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\TerminalOmnibusBundle\Entity\Ruta;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class RutaEstacionItemRepository extends EntityRepository
{
    
    public function getItemByRutaByEstacion($ruta, $estacion)
    {
        if($ruta instanceof Ruta){
            $ruta = $ruta->getCodigo();
        }
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        
        $query =  " SELECT i from Acme\TerminalOmnibusBundle\Entity\RutaEstacionItem i "
                . " INNER JOIN i.ruta r "
                . " INNER JOIN i.estacion e "
                . " WHERE "
                . " r.codigo = :codigoRuta and e.id = :idEstacion ";
         $query = $this->_em->createQuery($query)
                 ->setParameter("codigoRuta", $ruta)
                 ->setParameter("idEstacion", $estacion)
                 ->setMaxResults(1);
         
         $items = $query->getResult();
         if($items === null || count($items) === 0){
             return null;
         }else{
             return $items[0];
         }
    }
}

?>
