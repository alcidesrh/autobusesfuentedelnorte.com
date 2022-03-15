<?php
namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\EstadoAutorizacionOperacion;
use Acme\BackendBundle\Services\UtilService;

class AutorizacionOperacionRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(

    );
    
    public function getAutorizacionOperacionesPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $idEstacionUsuario = false;
        if(!$usuario->hasRole("ROLE_AUTORIZADOR")){
           if($usuario->getEstacion() !== null){
                $idEstacionUsuario = $usuario->getEstacion()->getId();
           } 
        }
        
        $fechaInitFilter = new \DateTime();
        $fechaInitFilter->modify("-30 days");
        $fechaEndFilter = new \DateTime();
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
        $idBoletoFilter = UtilService::getValueToMap($mapFilters, "idBoleto"); 

        $queryStr =   " FROM Acme\TerminalOmnibusBundle\Entity\AutorizacionOperacion ao "
                    . " INNER JOIN ao.boleto b "
                    . " INNER JOIN b.salida s "
                    . " INNER JOIN s.empresa emp "
                    . " INNER JOIN ao.estacion e "
                    . " WHERE "
                    . " (ao.fechaCreacion BETWEEN :fechaInitFilter AND :fechaEndFilter) "
                    . " and (emp.id IN ( :idEmpresasUsuarioFilter )) ";
        
        if($idEstacionUsuario != false){
            $queryStr .= " and (e.id = :idEstacionUsuario) ";
        }
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "ao.id" ,"identificadorFilter", $identificadorFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.id" ,"idBoletoFilter", $idBoletoFilter, false);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
//            $queryOrder = " s.fecha DESC ";
            $queryOrder = " ao.id DESC ";
        }
        
        $query = $this->_em->createQuery(" SELECT ao " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "idBoletoFilter", $idBoletoFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        if($idEstacionUsuario != false){
            UtilService::setParameterToQuery($query, "idEstacionUsuario", $idEstacionUsuario, false);
        }
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(ao) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "idBoletoFilter", $idBoletoFilter, false);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        if($idEstacionUsuario != false){
            UtilService::setParameterToQuery($query, "idEstacionUsuario", $idEstacionUsuario, false);
        }
        
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }   

    public function listarAutorizacionesDeOperacionesPendientesByBoleto($idBoleto)
    {
        $query =  " 
                    SELECT ao 
                    FROM Acme\TerminalOmnibusBundle\Entity\AutorizacionOperacion ao 
                    INNER JOIN ao.boleto b 
                    INNER JOIN ao.estado e 
                    WHERE 
                    b.id = :idBoleto 
                    and e.id = :estado
                 "
                ;

        return $this->_em->createQuery($query)
                ->setParameter('idBoleto', $idBoleto)
                ->setParameter('estado', intval(EstadoAutorizacionOperacion::REGISTRADO))
                ->getResult();
    }
    
    public function listarAutorizacionesDeOperacionesByBoletoByTipo($idBoleto, $idTipo)
    {
        $query =  " 
                    SELECT ao 
                    FROM Acme\TerminalOmnibusBundle\Entity\AutorizacionOperacion ao 
                    INNER JOIN ao.boleto b 
                    INNER JOIN ao.tipo t
                    WHERE 
                    b.id = :idBoleto 
                    and t.id = :idTipo
                 "
                ;

        return $this->_em->createQuery($query)
                ->setParameter('idBoleto', $idBoleto)
                ->setParameter('idTipo', $idTipo)
                ->getResult();
    }
    
    public function checkExisteAutorizacion($idBoleto, $idEstacion, $idTipo)
    {
        $query =  " 
                    SELECT ao 
                    FROM Acme\TerminalOmnibusBundle\Entity\AutorizacionOperacion ao 
                    INNER JOIN ao.estado ea
                    INNER JOIN ao.boleto b
                    INNER JOIN ao.estacion e 
                    INNER JOIN ao.tipo t
                    WHERE 
                    ea.id = 2
                    and b.id = :idBoleto 
                    and e.id = :idEstacion
                    and t.id = :idTipo
                 "
                ;

        $result =  $this->_em->createQuery($query)
                ->setParameter('idBoleto', $idBoleto)
                ->setParameter('idEstacion', $idEstacion)
                ->setParameter('idTipo', $idTipo)
                ->getResult();
        
        return count($result) > 0 ? true : false;
    }
}

?>
