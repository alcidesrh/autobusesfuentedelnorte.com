<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Salida;
use Acme\TerminalOmnibusBundle\Entity\ClaseAsiento;
use Acme\TerminalOmnibusBundle\Entity\AsientoBus;
use Doctrine\ORM\NoResultException;

class AsientoBusRepository extends EntityRepository
{
    //Se utiliza para saber si en el momento que el usuario acepta la operacion, los asientos aun esta disponible
    //Evita que dos usuario reserven o compren el mismo asiento si estan trabajando de forma simultanea.
    //Tanto en la venta como en la reservacion se hace este chequeo.
    //Unicamente cuando se esta vendiendo se puede selecionar un asiento reservado e intentar venderlo,
    //Esto lanzaria error pq el asiento ya esta reservado, para evitar esto se creo el parametro listaReservaciones
    //que son reservaciones con las que el usuario ya esta sincronizado, y esta solicitando remplazar por una venta,
    //por eso se excluyen de este filtro.
    public function getAsientoOcupadosPorNumero($idSalida, $listaNumerosAsientos, $listaReservaciones = array())
    {
        $result = array();
        
        if(count($listaNumerosAsientos) === 0){
            return $result;
        }
        
        $query =      " SELECT asb FROM Acme\TerminalOmnibusBundle\Entity\AsientoBus asb "
                    . " WHERE asb.id IN ( "
                    . " SELECT ab.id FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.estado e "
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN b.asientoBus ab "
                    . " WHERE "
                    . " s.id=:idSalida "
                    . " and e.id IN (1, 2, 3) "
                    . " and ab.numero IN (:listaNumerosAsientos) "
                    . " ) "
                    ;
         $query = $this->_em->createQuery($query)
                    ->setParameter('idSalida', intval($idSalida))
                    ->setParameter('listaNumerosAsientos', $listaNumerosAsientos);  
         $items = $query->getResult();
         if($items !== null && count($items) != 0){
             $result = array_merge((array)$result, (array)$items);
         }
         
         $query =     " SELECT asb FROM Acme\TerminalOmnibusBundle\Entity\AsientoBus asb "
                    . " WHERE asb.id IN ( "
                    . " SELECT ab.id FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                    . " LEFT JOIN r.estado e "
                    . " LEFT JOIN r.salida s "
                    . " LEFT JOIN r.asientoBus ab "
                    . " WHERE "
                    . " s.id=:idSalida "
                    . " and e.id=1 "
                    . " and ab.numero IN (:listaNumerosAsientos) "
                    ;
         
         if(count($listaReservaciones) !== 0){
             $query .= " and r.id NOT IN (:listaReservaciones) ";
         }
         
         $query .=  " ) ";
                 
         $query = $this->_em->createQuery($query)
                    ->setParameter('idSalida', intval($idSalida))
                    ->setParameter('listaNumerosAsientos', $listaNumerosAsientos); 
         if(count($listaReservaciones) !== 0){
             $query->setParameter('listaReservaciones', $listaReservaciones);
         }
         $items = $query->getResult();
         if($items !== null && count($items) != 0){
             $result = array_merge((array)$result, (array)$items);
         }
         
         $result = array_unique($result);
         return $result;
    }  
    
    //Se utiliza para el consultar de asiento de la salida
    public function getAsietoBusPorSalidaNroAsiento($idSalida, $nroAsiento)
    {
         try {
            $query =  " SELECT ab FROM Acme\TerminalOmnibusBundle\Entity\AsientoBus ab "
                    . " left join ab.tipoBus tba, "
                    . " Acme\TerminalOmnibusBundle\Entity\Salida s "
                    . " left join s.tipoBus tbs "
                    . " left join s.itinerario i "
                    . " left join i.tipoBus tbsi "
                    . " WHERE "
                    . " s.id = :idSalida "
                    . " and (tba.id = tbs.id  or tba.id = tbsi.id) "
                    . " and ab.numero= :nroAsiento";

            $item = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('idSalida', $idSalida)
                    ->setParameter('nroAsiento', $nroAsiento)
                    ->getSingleResult();
            return $item;
        } catch (NoResultException $exc) {
            return null;
        }
    }  
    
    public function getAsientosBySalidaId($idSalida)
    {
         return $this->getQueryAsientosBySalidaId($idSalida)->getResult();       
    }
    
    public function getQueryAsientosBySalidaId($idSalida)
    {
        if($idSalida === null){
            return array();
        }
        
        $query =  "SELECT a from Acme\TerminalOmnibusBundle\Entity\AsientoBus a "
                . " JOIN a.tipoBus tba, "
                . " Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " JOIN s.tipoBus tbs "
                . " WHERE "
                . " tba.id = tbs.id and "
                . " s.id = :idSalida "
                . " ORDER BY a.numero ASC";
         return $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida);       
    }
    
    public function getAsientosDisponiblesBySalidaId($salida, $claseAsiento = null, $asiento = null)
    {
        $idSalida = $salida;
        if($salida instanceof Salida){
            $idSalida = $salida->getId();
        }
        
        $idClaseAsiento = $claseAsiento;
        if($claseAsiento instanceof ClaseAsiento){
            $idClaseAsiento = $claseAsiento->getId();
        }
        
        $idAsiento = $asiento;
        if($asiento instanceof AsientoBus){
            $idAsiento = $asiento->getId();
        }
        
        $query =  " SELECT a from Acme\TerminalOmnibusBundle\Entity\AsientoBus a "
                . " JOIN a.clase c "
                . " JOIN a.tipoBus tba, "
                . " Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " JOIN s.tipoBus tbs "
                . " WHERE ";
        
        if($idAsiento !== null){
            $query .= " ( a.id = :idAsiento ) or ";
        }
        
        $query .= " ((c.id = :idClaseAsiento "
                . "   and tba.id = tbs.id "
                . "   and s.id = :idSalida) "
                . "  and (NOT EXISTS "
                . "  (SELECT b.id FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . "   JOIN b.salida s2 "
                . "   JOIN b.asientoBus a2 "
                . "   JOIN b.estado es2 "
                . "   WHERE "
                . "   es2.id IN (1, 2, 3) "
                . "   and a2.id = a.id"
                . "   and s2.id = :idSalida"
                . "   )) "
                . "  and (NOT EXISTS "
                . "  (SELECT r.id FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                . "   JOIN r.salida s3 "
                . "   JOIN r.asientoBus a3 "
                . "   JOIN r.estado es3 "
                . "   WHERE "
                . "   es3.id IN (1, 2) "
                . "   and a3.id = a.id"
                . "   and s3.id = :idSalida"
                . "   )) "
                . "  ) "
                . " ORDER BY a.numero ASC";
        
        $query = $this->_em->createQuery($query)
            ->setParameter('idSalida', $idSalida)
            ->setParameter('idClaseAsiento', $idClaseAsiento);
        
         if($idAsiento !== null){
              $query->setParameter('idAsiento', $idAsiento);
         }
         
         $items = $query->getResult();
        
         return $items;
    }
}

?>
