<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\Empresa;

class BusRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(
        
    );
    
    public function getBusesPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
    {
        
        if(!is_int($page)){
            $page = intval($page);
        }
        if($page <= 0){
            $page = 0;
        }else{
            $page = $page - 1;
        }
        
        if(!is_int($rows)){
            $rows = intval($rows);
        }
        if($rows < 0){
            $rows = 10;
        }else if($rows > 100){
            $rows = 100;
        }
        
        $idEmpresas = array();
        $empresas = $usuario->getEmpresas();
        foreach ($empresas as $empresa) {
            $idEmpresas[] = $empresa->getId();
        }
        
        $codigoFilter = UtilService::getValueToMap($mapFilters, "codigo"); 
        $placaFilter = UtilService::getValueToMap($mapFilters, "placa");
        $tipoFilter = UtilService::getValueToMap($mapFilters, "tipo");
        $empresaFilter = UtilService::getValueToMap($mapFilters, "empresa");
        $numeroSeguroFilter = UtilService::getValueToMap($mapFilters, "numeroSeguro");
        $numeroTarjetaRodajeFilter = UtilService::getValueToMap($mapFilters, "numeroTarjetaRodaje");
        $numeroTarjetaOperacionesFilter = UtilService::getValueToMap($mapFilters, "numeroTarjetaOperaciones");
        $estadoFilter = UtilService::getValueToMap($mapFilters, "estado");
        
        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Bus b "
                  . " INNER JOIN b.empresa e "
                  . " INNER JOIN b.estado est "
                  . " INNER JOIN b.tipo t "
                  . " WHERE e.id IN (:idEmpresas) ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.codigo" ,"codigoFilter", $codigoFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.placa" ,"placaFilter", $placaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "t.alias" ,"tipoFilter", $tipoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.alias" ,"empresaFilter", $empresaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "est.nombre" ,"estadoFilter", $estadoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.numeroSeguro" ,"numeroSeguroFilter", $numeroSeguroFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.numeroTarjetaRodaje" ,"numeroTarjetaRodajeFilter", $numeroTarjetaRodajeFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.numeroTarjetaOperaciones" ,"numeroTarjetaOperacionesFilter", $numeroTarjetaOperacionesFilter, false);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);        
        if($queryOrder === ""){
            $queryOrder = " b.codigo DESC ";
        }
        
        $query = $this->_em->createQuery(" SELECT b " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        $query->setParameter("idEmpresas", $idEmpresas);
        UtilService::setParameterToQuery($query, "codigoFilter", $codigoFilter, false);
        UtilService::setParameterToQuery($query, "placaFilter", $placaFilter);
        UtilService::setParameterToQuery($query, "tipoFilter", $tipoFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "numeroSeguroFilter", $numeroSeguroFilter, false);
        UtilService::setParameterToQuery($query, "numeroTarjetaRodajeFilter", $numeroTarjetaRodajeFilter, false);
        UtilService::setParameterToQuery($query, "numeroTarjetaOperacionesFilter", $numeroTarjetaOperacionesFilter, false);
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(b) " .$queryStr);
        $query->setParameter("idEmpresas", $idEmpresas);
        UtilService::setParameterToQuery($query, "codigoFilter", $codigoFilter, false);
        UtilService::setParameterToQuery($query, "placaFilter", $placaFilter); 
        UtilService::setParameterToQuery($query, "tipoFilter", $tipoFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "numeroSeguroFilter", $numeroSeguroFilter, false);
        UtilService::setParameterToQuery($query, "numeroTarjetaRodajeFilter", $numeroTarjetaRodajeFilter, false);
        UtilService::setParameterToQuery($query, "numeroTarjetaOperacionesFilter", $numeroTarjetaOperacionesFilter, false);        
         
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }    
     
    public function getBusSalida($idSalida)
    {
        try {
            $query =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Bus b,"
                . " Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " WHERE s.id = :idSalida and s.bus = b";
            $item = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idSalida', $idSalida)
                ->getSingleResult();
            return $item;
        } catch (NoResultException $exc) {
            return null;
        }
    }
    
    public function listarBusesVencimientoTarjetaOperaciones(\DateTime $fechaVencimiento, $empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaVencimiento->setTime(23, 59, 59);
//        var_dump($fechaVencimiento->format('d-m-Y H:i:s'));      
        $query =      " SELECT b from Acme\TerminalOmnibusBundle\Entity\Bus b "
                    . " INNER JOIN b.empresa e "
                    . " WHERE "
                    . " e.id = :idEmpresa "
                    . " and b.fechaVencimientoTarjetaOperaciones<:fechaVencimiento";
        
        $buses = $this->_em->createQuery($query)
                    ->setParameter('fechaVencimiento', $fechaVencimiento->format('d-m-Y H:i:s'))
                    ->setParameter('idEmpresa', $empresa)
                    ->getResult();
        return $buses;
    }
}

?>
