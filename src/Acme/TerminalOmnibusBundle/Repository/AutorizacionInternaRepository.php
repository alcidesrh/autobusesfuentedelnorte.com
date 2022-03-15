<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\BackendBundle\Entity\User;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;

class AutorizacionInternaRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(

    );
    
    public function getAutorizacionesInternasPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $usuarioFilter = $usuario;       
        if($usuarioFilter instanceof User){
            $usuarioFilter = $usuarioFilter->getId();
        }
        
        $identificadorFilter = UtilService::getValueToMap($mapFilters, "identificador");
        $fechaCreacionFilter = UtilService::getValueToMap($mapFilters, "fechaCreacion");
        
        if($fechaCreacionFilter !== null && is_string($fechaCreacionFilter)){
             $fechaFilterTemp = \DateTime::createFromFormat('d-m-Y', $fechaCreacionFilter);
             if($fechaFilterTemp === false){
                 $fechaFilterTemp = \DateTime::createFromFormat('d/m/Y', $fechaCreacionFilter);
             }
             if($fechaFilterTemp === false){
                 throw new \RuntimeException("No se pudo conventir la fecha:" . $fechaCreacionFilter);
             }
             $fechaCreacionFilter = $fechaFilterTemp;
        }
        $fechaCreacionInitFilter = null; 
        $fechaCreacionEndFilter = null;
        if($fechaCreacionFilter !== null){
            $fechaCreacionInitFilter = clone $fechaCreacionFilter;
            $fechaCreacionInitFilter->setTime(0, 0, 0);
            $fechaCreacionInitFilter = $fechaCreacionInitFilter->format('d-m-Y H:i:s');
            $fechaCreacionEndFilter = clone $fechaCreacionFilter;
            $fechaCreacionEndFilter->setTime(23, 59, 59);
            $fechaCreacionEndFilter = $fechaCreacionEndFilter->format('d-m-Y H:i:s');
        }
        
        // se muestran las que esten activas y que no se hayan utilizado.
        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\AutorizacionInterna ai "
            . " LEFT JOIN ai.usuarioCreacion uc "
            . " WHERE "
            . " ai.activo=1 and ai.fechaUtilizacion is null and uc.id=:usuarioFilter ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "ai.id" ,"identificadorFilter", $identificadorFilter);
        if($fechaCreacionFilter !== null){
            $queryStr .= " and ai.fechaCreacion BETWEEN :fechaCreacionInitFilter AND :fechaCreacionEndFilter ";
        }
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " ai.fechaCreacion ASC";
        }
        
        $query = $this->_em->createQuery(" SELECT ai " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "usuarioFilter", $usuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        if($fechaCreacionFilter !== null){
            UtilService::setParameterToQuery($query, "fechaCreacionInitFilter", $fechaCreacionInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaCreacionEndFilter", $fechaCreacionEndFilter, false);
        }

        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(ai) " .$queryStr);
        UtilService::setParameterToQuery($query, "usuarioFilter", $usuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        if($fechaCreacionFilter !== null){
            UtilService::setParameterToQuery($query, "fechaCreacionInitFilter", $fechaCreacionInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaCreacionEndFilter", $fechaCreacionEndFilter, false);
        }
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
}

?>
