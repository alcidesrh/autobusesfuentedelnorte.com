<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\BackendBundle\Services\UtilService;

class AlquilerRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(

    );
    
    public function getAlquilerPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $identificadorFilter = UtilService::getValueToMap($mapFilters, "identificador"); 
        $empresaFilter = UtilService::getValueToMap($mapFilters, "empresa"); 
        $busFilter = UtilService::getValueToMap($mapFilters, "bus"); 
        $estadoFilter = UtilService::getValueToMap($mapFilters, "estado");
        $importeFilter = UtilService::getValueToMap($mapFilters, "importe");
        $observacionFilter = UtilService::getValueToMap($mapFilters, "observacion");
        $pilotoFilter = UtilService::getValueToMap($mapFilters, "piloto");

        $queryStr =   " FROM Acme\TerminalOmnibusBundle\Entity\Alquiler a"
                    . " INNER JOIN a.empresa e "
                    . " INNER JOIN a.bus b "
                    . " INNER JOIN a.piloto p "
                    . " LEFT JOIN a.pilotoAux paux "
                    . " INNER JOIN a.estado est "
                    . " INNER JOIN a.usuarioCreacion u "
                    . " WHERE ( a.fechaInicial between :fechaInitFilter and :fechaEndFilter ) ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "a.id" ,"identificadorFilter", $identificadorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.alias" ,"empresaFilter", $empresaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.codigo" ,"busFilter", $busFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "est.nombre" ,"estadoFilter", $estadoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "a.importe" ,"importeFilter", $importeFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "a.observacion" ,"observacionFilter", $observacionFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("p.codigo", "paux.codigo") ,"pilotoFilter", $pilotoFilter, false);
        $queryStr .= " and (e.id IN ( :idEmpresasUsuarioFilter )) ";
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " a.id DESC ";
        }
        
        $query = $this->_em->createQuery(" SELECT a " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "busFilter", $busFilter, false);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "importeFilter", $importeFilter, false);
        UtilService::setParameterToQuery($query, "observacionFilter", $observacionFilter);
        UtilService::setParameterToQuery($query, "pilotoFilter", $pilotoFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(a) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "busFilter", $busFilter, false);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "importeFilter", $importeFilter, false);
        UtilService::setParameterToQuery($query, "observacionFilter", $observacionFilter);
        UtilService::setParameterToQuery($query, "pilotoFilter", $pilotoFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }    
}

?>
