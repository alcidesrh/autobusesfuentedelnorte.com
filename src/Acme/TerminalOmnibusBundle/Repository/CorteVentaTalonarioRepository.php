<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\BackendBundle\Services\UtilService;

class CorteVentaTalonarioRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(

    );
    
    public function getCorteVentaTalonarioPaginadas($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        $fechaInitFilter->modify("-6 month");
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter->modify("+6 month");
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

        $queryStr =   " FROM Acme\TerminalOmnibusBundle\Entity\CorteVentaTalonario cvt "
                    . " INNER JOIN cvt.estado est "
                    . " INNER JOIN cvt.talonario tal "
                    . " INNER JOIN tal.tarjeta tar "
                    . " INNER JOIN tar.salida sal "
                    . " INNER JOIN sal.empresa emp "
                    . " WHERE "
                    . " ( cvt.fecha between :fechaInitFilter and :fechaEndFilter ) "
                    . " and est.id IN (1,2) "
                    . " and (emp.id IN ( :idEmpresasUsuarioFilter )) ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "tar.numero" ,"numeroFilter", $numeroFilter, false);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " tar.id, cvt.id  ";
        }
        
        $query = $this->_em->createQuery(" SELECT cvt " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "numeroFilter", $numeroFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(cvt) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "numeroFilter", $numeroFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
}

?>
