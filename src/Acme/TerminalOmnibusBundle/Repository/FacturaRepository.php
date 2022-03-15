<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\TerminalOmnibusBundle\Entity\Empresa;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Acme\TerminalOmnibusBundle\Entity\Factura;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\BackendBundle\Services\UtilService;

class FacturaRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(
        'id' => 'f.id',
        'fechaEmision' => 'f.fechaEmisionResolucionFactura',
        'fechaVencimiento' => 'f.fechaVencimientoResolucionFactura'
    );
    
    public function getFacturasPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        foreach ($usuario->getEmpresas() as $empresa) {
            $idEmpresasUsuarioFilter[] = $empresa->getId();
        }        
        $estacionUsuarioFilter = $usuario->getEstacion();
        if($estacionUsuarioFilter !== null){
            $estacionUsuarioFilter = $estacionUsuarioFilter->getId();
        }
        
        $identificadorFilter = UtilService::getValueToMap($mapFilters, "identificador");
        $estacionFilter = UtilService::getValueToMap($mapFilters, "estacion");      
        $empresaFilter = UtilService::getValueToMap($mapFilters, "empresa");   
        $servicioFilter = UtilService::getValueToMap($mapFilters, "servicio");
        $serieFilter = UtilService::getValueToMap($mapFilters, "serie");
        $valorFilter = UtilService::getValueToMap($mapFilters, "valor");
        $minimoFilter = UtilService::getValueToMap($mapFilters, "minimo");
        $maximoFilter = UtilService::getValueToMap($mapFilters, "maximo");
        $activoFilter = UtilService::getValueToMap($mapFilters, "activo");
        $fechaEmisionFilter = UtilService::getValueToMap($mapFilters, "fechaEmision");
        $fechaVencimientoFilter = UtilService::getValueToMap($mapFilters, "fechaVencimiento");
        
        if($fechaEmisionFilter !== null && is_string($fechaEmisionFilter)){
             $fechaFilterTemp = \DateTime::createFromFormat('d-m-Y', $fechaEmisionFilter);
             if($fechaFilterTemp === false){
                 $fechaFilterTemp = \DateTime::createFromFormat('d/m/Y', $fechaEmisionFilter);
             }
             if($fechaFilterTemp === false){
                 throw new \RuntimeException("No se pudo conventir la fecha:" . $fechaEmisionFilter);
             }
             $fechaEmisionFilter = $fechaFilterTemp;
        }
        
        $fechaEmisionInitFilter = null; 
        $fechaEmisionEndFilter = null;
        if($fechaEmisionFilter !== null){
            $fechaEmisionInitFilter = clone $fechaEmisionFilter;
            $fechaEmisionInitFilter->setTime(0, 0, 0);
            $fechaEmisionInitFilter = $fechaEmisionInitFilter->format('d-m-Y H:i:s');
            $fechaEmisionEndFilter = clone $fechaEmisionFilter;
            $fechaEmisionEndFilter->setTime(23, 59, 59);
            $fechaEmisionEndFilter = $fechaEmisionEndFilter->format('d-m-Y H:i:s');
        }
        
        if($fechaVencimientoFilter !== null && is_string($fechaVencimientoFilter)){
             $fechaFilterTemp = \DateTime::createFromFormat('d-m-Y', $fechaVencimientoFilter);
             if($fechaFilterTemp === false){
                 $fechaFilterTemp = \DateTime::createFromFormat('d/m/Y', $fechaVencimientoFilter);
             }
             if($fechaFilterTemp === false){
                 throw new \RuntimeException("No se pudo conventir la fecha:" . $fechaVencimientoFilter);
             }
             $fechaVencimientoFilter = $fechaFilterTemp;
        }
        $fechaVencimientoInitFilter = null; 
        $fechaVencimientoEndFilter = null;
        if($fechaVencimientoFilter !== null){
            $fechaVencimientoInitFilter = clone $fechaVencimientoFilter;
            $fechaVencimientoInitFilter->setTime(0, 0, 0);
            $fechaVencimientoInitFilter = $fechaVencimientoInitFilter->format('d-m-Y H:i:s');
            $fechaVencimientoEndFilter = clone $fechaVencimientoFilter;
            $fechaVencimientoEndFilter->setTime(23, 59, 59);
            $fechaVencimientoEndFilter = $fechaVencimientoEndFilter->format('d-m-Y H:i:s');
        }
        
        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Factura f "
            . " LEFT JOIN f.estacion est "
            . " LEFT JOIN f.empresa emp "
            . " LEFT JOIN f.servicioEstacion ser "
            . " WHERE "
            . " emp.id IN (:idEmpresasUsuarioFilter) ";
        
        if($estacionUsuarioFilter !== null){
            $queryStr .= " and est.id=:estacionUsuarioFilter ";
        }
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "f.id" ,"identificadorFilter", $identificadorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("est.alias", "est.nombre") ,"estacionFilter", $estacionFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "emp.nombre" ,"empresaFilter" , $empresaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "ser.nombre" ,"servicioFilter" , $servicioFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "f.serieResolucionFactura" ,"serieFilter", $serieFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "f.valorResolucionFactura" ,"valorFilter", $valorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "f.minimoResolucionFactura" ,"minimoFilter", $minimoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "f.maximoResolucionFactura" ,"maximoFilter", $maximoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "f.activo" ,"activoFilter", $activoFilter);
        
        if($fechaEmisionFilter !== null){
            $queryStr .= " and f.fechaEmisionResolucionFactura BETWEEN :fechaEmisionInitFilter AND :fechaEmisionEndFilter ";
        }
        if($fechaVencimientoFilter !== null){
            $queryStr .= " and f.fechaVencimientoResolucionFactura BETWEEN :fechaVencimientoInitFilter AND :fechaVencimientoEndFilter ";
        }
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " f.id ASC ";
        }
        
        $query = $this->_em->createQuery(" SELECT f " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "estacionFilter", $estacionFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "servicioFilter", $servicioFilter);
        UtilService::setParameterToQuery($query, "serieFilter", $serieFilter);
        UtilService::setParameterToQuery($query, "valorFilter", $valorFilter);
        UtilService::setParameterToQuery($query, "minimoFilter", $minimoFilter);
        UtilService::setParameterToQuery($query, "maximoFilter", $maximoFilter);
        UtilService::setParameterToQuery($query, "activoFilter", $activoFilter, false);
        
        if($fechaEmisionFilter !== null){
            UtilService::setParameterToQuery($query, "fechaEmisionInitFilter", $fechaEmisionInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaEmisionEndFilter", $fechaEmisionEndFilter, false);
        }
        if($fechaVencimientoFilter !== null){
            UtilService::setParameterToQuery($query, "fechaVencimientoInitFilter", $fechaVencimientoInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaVencimientoEndFilter", $fechaVencimientoEndFilter, false);
        }

//        var_dump($query->getDQL());
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(f) " .$queryStr);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "estacionFilter", $estacionFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "servicioFilter", $servicioFilter);
        UtilService::setParameterToQuery($query, "serieFilter", $serieFilter);
        UtilService::setParameterToQuery($query, "valorFilter", $valorFilter);
        UtilService::setParameterToQuery($query, "minimoFilter", $minimoFilter);
        UtilService::setParameterToQuery($query, "maximoFilter", $maximoFilter);
        UtilService::setParameterToQuery($query, "activoFilter", $activoFilter, false);
        
        if($fechaEmisionFilter !== null){
            UtilService::setParameterToQuery($query, "fechaEmisionInitFilter", $fechaEmisionInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaEmisionEndFilter", $fechaEmisionEndFilter, false);
        }
        if($fechaVencimientoFilter !== null){
            UtilService::setParameterToQuery($query, "fechaVencimientoInitFilter", $fechaVencimientoInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaVencimientoEndFilter", $fechaVencimientoEndFilter, false);
        }
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
    
     public function getFacturasPorEstacion($estacion, $empresas)
    {
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }else if($estacion === null || trim ($estacion) === ""){
            return array();
        }
        
        $idEmpresas = array();
        foreach ($empresas as $empresa) {
            $idEmpresas[] = $empresa->getId();
        }
        
        $query =  " SELECT f from Acme\TerminalOmnibusBundle\Entity\Factura f "
                    . " LEFT JOIN f.estacion est "
                    . " LEFT JOIN f.empresa emp "
                    . " WHERE "
                    . " f.activo=1 "
                    . " and est.id = :estacion "
                    . " and emp.id IN (:idEmpresas) ";
        
        $facturas = $this->_em->createQuery($query)
                    ->setParameter('estacion', $estacion)
                    ->setParameter('idEmpresas', $idEmpresas)
                    ->getResult();
        return $facturas;
    }   
    
//    public function getFacturaPorEstacionEmpresa($estacion, $empresa, $servicio)
//    {
//        if($estacion instanceof Estacion){
//            $estacion = $estacion->getId();
//        }
//        if($empresa instanceof Empresa){
//            $empresa = $empresa->getId();
//        }
//        
//        if($servicio instanceof ServicioEstacion){
//            $servicio = $servicio->getId();
//        }
//        
//        try {
//            $query =  " SELECT f from Acme\TerminalOmnibusBundle\Entity\Factura f "
//                    . " LEFT JOIN f.estacion est "
//                    . " LEFT JOIN f.empresa emp "
//                    . " LEFT JOIN f.servicioEstacion see "
//                    . " WHERE "
//                    . " f.activo=1 "
//                    . " and est.id = :estacion "
//                    . " and emp.id = :empresa "
//                    . " and see.id = :servicio ";
//            
//            $factura = $this->_em->createQuery($query)
//                    ->setMaxResults(1)
//                    ->setParameter('estacion', $estacion)
//                    ->setParameter('empresa', $empresa)
//                    ->setParameter('servicio', $servicio)
//                    ->getSingleResult();
//            
//            return $factura;
//            
//        } catch (NoResultException $exc) {
//            return null;
//        }
//    }    
    
    //Solo se utiliza para la validacion
//    public function getCantidadFacturaPorEstacionEmpresaServicio($estacion, $empresa, $servicio, $facturaActual = null)
//    {
//        if($estacion instanceof Estacion){
//            $estacion = $estacion->getId();
//        }
//        if($empresa instanceof Empresa){
//            $empresa = $empresa->getId();
//        }
//        if($servicio instanceof ServicioEstacion){
//            $servicio = $servicio->getId();
//        }
//        
//        if($facturaActual !== null && $facturaActual instanceof Factura){
//            $facturaActual = $facturaActual->getId();
//        }
//        
//        try {
//            $queryStr =  " SELECT COUNT(f) FROM Acme\TerminalOmnibusBundle\Entity\Factura f "
//                    . " LEFT JOIN f.estacion est "
//                    . " LEFT JOIN f.empresa emp "
//                    . " LEFT JOIN f.servicioEstacion see "
//                    . " WHERE "
//                    . " f.activo=1 "
//                    . " and est.id = :estacion "
//                    . " and emp.id = :empresa "
//                    . " and see.id = :servicio ";
//            
//            if($facturaActual !== null){
//                $queryStr .= " and f.id <> :facturaActual ";
//            }
//                    
//            $query = $this->_em->createQuery($queryStr)
//                    ->setMaxResults(1)
//                    ->setParameter('estacion', $estacion)
//                    ->setParameter('empresa', $empresa)
//                    ->setParameter('servicio', $servicio);
//            
//            if($facturaActual !== null){
//                $query->setParameter('facturaActual', $facturaActual);
//            }
//            
//            $cantidad = $query->getSingleResult();
//            return $cantidad[1];
//         } catch (NoResultException $exc) {
//            return 0;
//         }
//    }
    
    
    public function listarFacturaPorEstacionEmpresaServicio($estacion, $empresa, $servicio, $activo = null)
    {
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        if($servicio instanceof ServicioEstacion){
            $servicio = $servicio->getId();
        }
        
        $query =  " SELECT f from Acme\TerminalOmnibusBundle\Entity\Factura f "
                    . " LEFT JOIN f.estacion est "
                    . " LEFT JOIN f.empresa emp "
                    . " LEFT JOIN f.servicioEstacion see "
                    . " WHERE "
                    . " f.valorResolucionFactura <= f.maximoResolucionFactura "
                    . " and est.id = :estacion "
                    . " and emp.id = :empresa "
                    . " and see.id = :servicio ";
        
        if($activo !== null){
            $query .= " and f.activo=:activo ";
            if($activo === true){
                $query .= " and f.valorResolucionFactura<=f.maximoResolucionFactura ";
            }
        }
            
        $query = $this->_em->createQuery($query)
                    ->setParameter('estacion', $estacion)
                    ->setParameter('empresa', $empresa)
                    ->setParameter('servicio', $servicio);
        
        if($activo !== null){
            $query->setParameter('activo', $activo);
        }
            
        return $query->getResult();
    }   
    
    public function listarSeriesFacturasExpiradas($fechaVencimiento)
    {
        $query =      " SELECT f from Acme\TerminalOmnibusBundle\Entity\Factura f "
                    . " WHERE "
                    . " f.activo = 1 "
                    . " and (f.fechaVencimientoResolucionFactura<:fechaVencimiento or f.fechaVencimientoResolucionSistema<:fechaVencimiento)";
        
        $facturas = $this->_em->createQuery($query)
                    ->setParameter('fechaVencimiento', $fechaVencimiento->format('d-m-Y H:i:s'))
                    ->getResult();
        return $facturas;
    }
    
    public function listarSeriesFacturasPorTerminar($cantidad)
    {
        $query =      " SELECT f from Acme\TerminalOmnibusBundle\Entity\Factura f "
                    . " WHERE "
                    . " f.activo = 1 "
                    . " and (f.maximoResolucionFactura - f.valorResolucionFactura) < :cantidad ";
        
        $facturas = $this->_em->createQuery($query)
                    ->setParameter('cantidad', $cantidad)
                    ->getResult();
        return $facturas;
    }
}

?>
