<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\TerminalOmnibusBundle\Entity\Empresa;
use Doctrine\ORM\NoResultException;

class FacturaGeneradaRepository extends EntityRepository
{
    public function listarFacturado($estacion, $empresa, $fecha)
    {
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaInitFilter = clone $fecha;
        $fechaInitFilter = $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
              
        $fechaEndFilter = clone $fecha;
        $fechaEndFilter = $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =  " SELECT fg from Acme\TerminalOmnibusBundle\Entity\FacturaGenerada fg "
                    . " INNER JOIN fg.factura fa "
                    . " INNER JOIN fa.empresa em "
                    . " INNER JOIN fg.estacion es "
                    . " WHERE "
                    . " and es.id = :estacion "
                    . " and em.id = :empresa "
                    . " and fg.fecha between :fechaInitFilter and :fechaEndFilter  ";
            
        $facturas = $this->_em->createQuery($query)
                    ->setParameter('estacion', $estacion)
                    ->setParameter('empresa', $empresa)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        
        return $facturas;
    }
    
    public function checkExisteFactura($factura, $valor)
    {
        if($factura instanceof \Acme\TerminalOmnibusBundle\Entity\Factura){
            $factura = $factura->getId();
        }
        
        try {
             $query = " SELECT fg from Acme\TerminalOmnibusBundle\Entity\FacturaGenerada fg "
                    . " INNER JOIN fg.factura fa "
                    . " WHERE "
                    . " fa.id = :factura and fg.consecutivo = :consecutivo ";

             $fg = $this->_em->createQuery($query)
                ->setParameter('factura', $factura)
                ->setParameter('consecutivo', $valor)
                ->getSingleResult();
             
             if($fg !== null){
                 return true;
             }
             
        } catch (NoResultException $exc) { }
        
        return false;
    }
}

?>
