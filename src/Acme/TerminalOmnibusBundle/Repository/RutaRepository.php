<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\TerminalOmnibusBundle\Entity\Empresa;

class RutaRepository extends EntityRepository
{
    
    public function listarRutasNoCalendarizadas()
    {
        $query =  " SELECT r from Acme\TerminalOmnibusBundle\Entity\Ruta r "
                . " WHERE "
                . " r.codigo NOT IN ( "
                . " SELECT rut.codigo FROM AcmeTerminalOmnibusBundle:CalendarioFacturaRuta cfr  "
                . " LEFT JOIN cfr.ruta rut "
                . " ) ";
         return $this->_em->createQuery($query)->getResult();
    }
    
    public function listarRutasPorEstaciones($estacionOrigen, $estacionDestino = null, $rutaInicial = false)
    {
        if($estacionOrigen instanceof Estacion){
            $estacionOrigen = $estacionOrigen->getId();
        }
        if($estacionDestino !== null && $estacionDestino instanceof Estacion){
            $estacionDestino = $estacionDestino->getId();
        }
        
        //No se agrega el destino, pq no tiene sentido, estoy filtrando estaciones que inician en la estacion del usuario o que pasan por el.
        $query =  " SELECT r from Acme\TerminalOmnibusBundle\Entity\Ruta r "
                . " INNER JOIN r.estacionOrigen eo "
                . " LEFT JOIN r.listaEstacionesIntermediaOrdenadas leio1 "
                . " LEFT JOIN leio1.estacion ei1 "
                . " LEFT JOIN r.listaEstacionesIntermediaOrdenadas leio2 "
                . " LEFT JOIN leio2.estacion ei2 "
                . " INNER JOIN r.estacionDestino ed "
                . " WHERE "
                . " r.activo=1 "
                . " and ( eo.id = :estacionOrigen or ei1.id = :estacionOrigen ) ";
        
        if($rutaInicial === true || $rutaInicial === 'true'){
            $query .= " and (ed.id != :estacionOrigen)";
        }
        
        if($estacionDestino !== null && trim($estacionDestino) !== "" ){
            $query .= " and (ed.id=:estacionDestino or ei2.id=:estacionDestino ) ";
        }
        
        $query = $this->_em->createQuery($query)
                 ->setParameter('estacionOrigen', intval($estacionOrigen));
        
        if($estacionDestino !== null && trim($estacionDestino) !== "" ){
            $query->setParameter('estacionDestino', intval($estacionDestino));
        }
                 
        return $query->getResult();
    }
}

?>
