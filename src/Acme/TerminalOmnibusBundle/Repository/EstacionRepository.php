<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Empresa;

class EstacionRepository extends EntityRepository
{
    public function getEstacionesWithTiemposByRutaByClaseBus($ruta, $claseBus)
    {
        if($ruta instanceof Ruta){
            $ruta = $ruta->getCodigo();
        }
        if($claseBus instanceof ClaseBus){
            $claseBus = $claseBus->getId();
        }
        
        $str =    " SELECT e.id as idEstacion, t.minutos as tiempo FROM Acme\TerminalOmnibusBundle\Entity\Tiempo t "
                . " INNER JOIN t.claseBus c "
                . " INNER JOIN t.ruta r "
                . " INNER JOIN t.estacionDestino e "
                . " WHERE "
                . " r.codigo = :ruta and "
                . " c.id = :claseBus "
                . " ORDER BY "
                . " t.minutos asc ";
        
        $query = $this->_em->createQuery($str)
                    ->setParameter('ruta', $ruta)
                    ->setParameter('claseBus', $claseBus);
        $items = $query->getResult();
        $result = array();
        foreach ($items as $item) {
            $result[$item["idEstacion"]] = $item["tiempo"];
        }
        return $result;
    }
    
    public function getEstacionesReporting()
    {
        $consulta  = " SELECT e "
                   . " FROM Acme\TerminalOmnibusBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " WHERE " 
                   . " e.activo = 1 and t.id IN (1,2) "
                   . " ORDER BY "
                   . " t.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
    public function getAllDestinosEstacionesActivas()
    {
        $consulta  = " SELECT e "
                   . " FROM Acme\TerminalOmnibusBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " WHERE " 
                   . " e.destino=1 and e.activo=1 "
                   . " ORDER BY "
                   . " e.nombre "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
    public function getAllDestinosEstacionesActivasPublicidad()
    {
        $consulta  = " SELECT e "
                   . " FROM Acme\TerminalOmnibusBundle\Entity\Estacion e "
                   . " INNER JOIN e.tipo t "
                   . " WHERE " 
                   . " e.destino=1 and e.activo=1 and e.publicidad=1 "
                   . " ORDER BY "
                   . " t.id "
                ;
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
    public function getAllEstacionesActivas($servicio)
    {
        $existServices = $servicio !== null;
        $consulta  = " SELECT e FROM Acme\TerminalOmnibusBundle\Entity\Estacion e JOIN e.listaServicio s WHERE " ;  
        $consulta .= " e.activo=1 and  e.publicidad=1 ";
        if($existServices){
            $consulta .= " and s.id=" . $servicio;
        }
        $query = $this->_em->createQuery($consulta);
        return $query->getResult();
    }
    
    public function getEstacionesEmitieronOperaciones(\DateTime $fechaDia, $empresa)
    {
        $estacionesBoletos = $this->getEstacionesEmitieronBoletos($fechaDia, $empresa, true);
        $estacionesEncomiendas = $this->getEstacionesEmitieronEncomiendas($fechaDia, $empresa, true);
        $estaciones = array_merge(array_values($estacionesBoletos), array_values($estacionesEncomiendas));
        $result = array();
        foreach ($estaciones as $estacion) {
            if(!in_array($estacion, $result)){
                $result[] = $estacion;
            }
        }
        return $result;
    }
    
    public function getEstacionesEmitieronBoletos(\DateTime $fechaDia, $empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaInitFilter = clone $fechaDia;
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = clone $fechaDia;
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query  = " SELECT es "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Estacion es "
                . " WHERE "
                . " es.id IN ( "
                . " SELECT DISTINCT ec.id FROM Acme\TerminalOmnibusBundle\Entity\Boleto bo "
                . " INNER JOIN bo.salida sa "
                . " INNER JOIN sa.empresa em "
                . " INNER JOIN bo.estacionCreacion ec "
                . " WHERE "
                . " em.id = :idEmpresa "
                . " and bo.fechaCreacion between :fechaInitFilter and :fechaEndFilter "
                . " ) "
                . " ORDER BY "
                . " es.id ";
        
        $items = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->setParameter('idEmpresa', $empresa)
                    ->getArrayResult();
        return $items;
    }
    
    public function getEstacionesEmitieronEncomiendas(\DateTime $fechaDia, $empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaInitFilter = clone $fechaDia;
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = clone $fechaDia;
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query  = " SELECT es "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Estacion es "
                . " WHERE "
                . " es.id IN ( "
                . " SELECT DISTINCT ec.id FROM Acme\TerminalOmnibusBundle\Entity\Encomienda en "
                . " INNER JOIN en.empresa em "
                . " INNER JOIN en.estacionCreacion ec "
                . " WHERE "
                . " em.id = :idEmpresa "
                . " and en.fechaCreacion between :fechaInitFilter and :fechaEndFilter "
                . " ) "
                . " ORDER BY "
                . " es.id ";
        
        $items = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->setParameter('idEmpresa', $empresa)
                    ->getArrayResult();
        return $items;
    }
}

?>
