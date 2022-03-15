<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\BackendBundle\Services\UtilService;

class TarjetaRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(

    );
    
    public function getTarjetasPaginadas($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $idEmpresasUsuarioFilter = array();
        $empresasUsuarioFilter = $usuario->getEmpresas();
        foreach ($empresasUsuarioFilter as $empresa) {
            $idEmpresasUsuarioFilter[] = $empresa->getId();
        }
        
        $fechaInitFilter = new \DateTime();
        $fechaInitFilter->modify("-1 month");
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter->modify("+1 month");
        $rangoFechaFilter = UtilService::getValueToMap($mapFilters, "rangoFecha");
        if($rangoFechaFilter !== null && trim($rangoFechaFilter) !== ""){
            $rangoFechaArray = explode("-", $rangoFechaFilter);
            if(count($rangoFechaArray) === 2){
                $fechaInicialStr = trim($rangoFechaArray[0]);
                $fechaFinalStr = trim($rangoFechaArray[1]);
                if($fechaInicialStr !== "" && $fechaFinalStr !== ""){
                    $fechaInicialDateTime = \DateTime::createFromFormat('d/m/Y', $fechaInicialStr);
                    if($fechaInicialDateTime === false){
                        $fechaInicialDateTime = \DateTime::createFromFormat('d-m-Y', $fechaInicialStr);
                    }
                    if($fechaInicialDateTime !== false){
                        $fechaInitFilter = $fechaInicialDateTime;
                    }
                    
                    $fechaFinalDateTime = \DateTime::createFromFormat('d/m/Y', $fechaFinalStr);
                    if($fechaFinalDateTime === false){
                        $fechaFinalDateTime = \DateTime::createFromFormat('d-m-Y', $fechaFinalStr);
                    }
                    if($fechaFinalDateTime !== false){
                        $fechaEndFilter = $fechaFinalDateTime;
                    }     
                }             
            }
        }        
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $numeroFilter = UtilService::getValueToMap($mapFilters, "numero"); 
        $salidaFilter = UtilService::getValueToMap($mapFilters, "salida"); 
        $empresaFilter = UtilService::getValueToMap($mapFilters, "empresa");

        $queryStr =   " FROM Acme\TerminalOmnibusBundle\Entity\Tarjeta a "
                    . " INNER JOIN a.salida s "
                    . " INNER JOIN s.empresa e "
                    . " INNER JOIN a.estado est "
                    . " INNER JOIN a.usuarioCreacion u "
                    . " WHERE ( a.fechaCreacion between :fechaInitFilter and :fechaEndFilter ) and (e.id IN ( :idEmpresasUsuarioFilter )) ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "s.id" ,"salidaFilter", $salidaFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.alias" ,"empresaFilter", $empresaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "a.numero" ,"numeroFilter", $numeroFilter, false);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " a.id DESC ";
        }
        
        $query = $this->_em->createQuery(" SELECT a " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "salidaFilter", $salidaFilter, false);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "numeroFilter", $numeroFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(a) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "salidaFilter", $salidaFilter, false);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "numeroFilter", $numeroFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }    
    
    public function getTarjetasRecientes($usuario)
    {
        $idEmpresasUsuarioFilter = array();
        $empresasUsuarioFilter = $usuario->getEmpresas();
        foreach ($empresasUsuarioFilter as $empresa) {
            $idEmpresasUsuarioFilter[] = $empresa->getId();
        }
        
        $fechaInitFilter = new \DateTime();
        $esInspector = $usuario->hasRole("ROLE_INSPECTOR_BOLETO");
        if($esInspector){
            $fechaInitFilter->modify("-48 hour");
        }else{
            $fechaInitFilter->modify("-30 days");
        }
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =  " SELECT t "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Tarjeta t "
                . " INNER JOIN t.salida s "
                . " INNER JOIN s.empresa e "
                . " INNER JOIN s.estado e1 "
                . " INNER JOIN t.estado e2 "
                . " WHERE "
                . " e1.id=3 "
                . " and e2.id=1 "
                . " and t.fechaCreacion between :fechaInitFilter and :fechaEndFilter "
                . " and e.id IN ( :idEmpresasUsuarioFilter ) ";
        
         return $this->_em->createQuery($query)
                          ->setParameter("fechaInitFilter", $fechaInitFilter)
                          ->setParameter("fechaEndFilter", $fechaEndFilter)
                          ->setParameter("idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter)
                          ->getResult();
    }
    
    public function getTarjetasListasParaConciliacion($usuario)
    {
        $idEmpresasUsuarioFilter = array();
        $empresasUsuarioFilter = $usuario->getEmpresas();
        foreach ($empresasUsuarioFilter as $empresa) {
            $idEmpresasUsuarioFilter[] = $empresa->getId();
        }
        
        $query =  " SELECT tar "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Tarjeta tar "
                . " INNER JOIN tar.salida sal "
                . " INNER JOIN sal.empresa emp "
                . " INNER JOIN tar.estado est "
                . " WHERE "
                . " est.id=2 and emp.id IN ( :idEmpresasUsuarioFilter ) ";
        
         return $this->_em->createQuery($query)
                          ->setParameter("idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter)
                          ->getResult();
    }
}

?>
