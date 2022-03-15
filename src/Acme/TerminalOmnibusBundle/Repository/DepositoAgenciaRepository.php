<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class DepositoAgenciaRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(

    );
    
    public function getDepositoAgenciaPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $estacionUsuarioFilter = $usuario->getEstacion();
        
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
        
        $identificadorFilter = UtilService::getValueToMap($mapFilters, "identificador"); 
        $estacionFilter = UtilService::getValueToMap($mapFilters, "estacion"); 
        $numeroBoletaFilter = UtilService::getValueToMap($mapFilters, "numeroBoleta");

        $queryStr =   " FROM Acme\TerminalOmnibusBundle\Entity\DepositoAgencia da "
                    . " INNER JOIN da.estacion ag "
                    . " WHERE "
                    . " ( da.fecha between :fechaInitFilter and :fechaEndFilter ) ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "da.id" ,"identificadorFilter", $identificadorFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "ag.nombre" ,"estacionFilter", $estacionFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "da.numeroBoleta" ,"numeroBoletaFilter", $numeroBoletaFilter, false);
        if($estacionUsuarioFilter !== null){
            $queryStr .= " and (ag.id IN ( :idEstacionUsuarioFilter )) ";
        }
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " da.fecha DESC, da.id DESC ";
        }
        
        $query = $this->_em->createQuery(" SELECT da " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "estacionFilter", $estacionFilter);
        UtilService::setParameterToQuery($query, "numeroBoletaFilter", $numeroBoletaFilter, false);
        if($estacionUsuarioFilter !== null){
            UtilService::setParameterToQuery($query, "idEstacionUsuarioFilter", $estacionUsuarioFilter->getId(), false);
        }
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(da) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "estacionFilter", $estacionFilter);
        UtilService::setParameterToQuery($query, "numeroBoletaFilter", $numeroBoletaFilter, false);
        if($estacionUsuarioFilter !== null){
            UtilService::setParameterToQuery($query, "idEstacionUsuarioFilter", $estacionUsuarioFilter->getId(), false);
        }
        
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }    
    
    public function totalDepositado($agencia)
    {
        if($agencia === null){
            return 0;
        }      
        
        if($agencia instanceof Estacion){
            $agencia = $agencia->getId();
        }
        
        $query =  " SELECT SUM(da.importe) as importe from Acme\TerminalOmnibusBundle\Entity\DepositoAgencia da "
                    . " INNER JOIN da.estacion ag "
                    . " INNER JOIN da.estado es "
                    . " WHERE "
                    . " ag.id = :agencia "
                    . " and es.id = 2 ";

        $valor = $this->_em->createQuery($query)
                    ->setParameter('agencia', $agencia)
                    ->getSingleResult();
        return $valor['importe'];
    }
}

?>
