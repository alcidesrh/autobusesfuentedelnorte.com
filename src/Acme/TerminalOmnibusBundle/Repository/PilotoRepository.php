<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\Empresa;

class PilotoRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(
        
    );
    
    public function getPilotosPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        $nombreFilter = UtilService::getValueToMap($mapFilters, "nombre");
        $numeroLicenciaFilter = UtilService::getValueToMap($mapFilters, "numeroLicencia");
        $dpiFilter = UtilService::getValueToMap($mapFilters, "dpi");
        $seguroSocialFilter = UtilService::getValueToMap($mapFilters, "seguroSocial");
        $telefonoFilter = UtilService::getValueToMap($mapFilters, "telefono");
        
        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Piloto p "
                  . " INNER JOIN p.empresa e "
                  . " WHERE e.id IN (:idEmpresas) ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "p.codigo" ,"codigoFilter", $codigoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("p.nombre1", "p.nombre2", "p.apellido1", "p.apellido2") ,"nombreFilter", $nombreFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "p.numeroLicencia" ,"numeroLicenciaFilter", $numeroLicenciaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "p.dpi" ,"dpiFilter", $dpiFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "p.seguroSocial" ,"seguroSocialFilter", $seguroSocialFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "p.telefono" ,"telefonoFilter", $telefonoFilter);
        
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);        
        if($queryOrder === ""){
            $queryOrder = " p.codigo ASC ";
        }
        
        $query = $this->_em->createQuery(" SELECT p " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        $query->setParameter("idEmpresas", $idEmpresas);
        UtilService::setParameterToQuery($query, "codigoFilter", $codigoFilter);
        UtilService::setParameterToQuery($query, "nombreFilter", $nombreFilter);
        UtilService::setParameterToQuery($query, "numeroLicenciaFilter", $numeroLicenciaFilter);
        UtilService::setParameterToQuery($query, "dpiFilter", $dpiFilter);
        UtilService::setParameterToQuery($query, "seguroSocialFilter", $seguroSocialFilter);
        UtilService::setParameterToQuery($query, "telefonoFilter", $telefonoFilter);
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(p) " .$queryStr);
        $query->setParameter("idEmpresas", $idEmpresas);
        UtilService::setParameterToQuery($query, "codigoFilter", $codigoFilter);
        UtilService::setParameterToQuery($query, "nombreFilter", $nombreFilter);
        UtilService::setParameterToQuery($query, "numeroLicenciaFilter", $numeroLicenciaFilter);
        UtilService::setParameterToQuery($query, "dpiFilter", $dpiFilter);
        UtilService::setParameterToQuery($query, "seguroSocialFilter", $seguroSocialFilter);
        UtilService::setParameterToQuery($query, "telefonoFilter", $telefonoFilter); 
         
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }   
    
    public function listarPilotosVencimientoLicencia(\DateTime $fechaVencimiento, $empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaVencimiento->setTime(23, 59, 59);
//        var_dump($fechaVencimiento->format('d-m-Y H:i:s'));      
        $query =      " SELECT p from Acme\TerminalOmnibusBundle\Entity\Piloto p "
                    . " INNER JOIN p.empresa e "
                    . " WHERE "
                    . " e.id = :idEmpresa "
                    . " and p.activo=1 "
                    . " and p.fechaVencimientoLicencia<:fechaVencimiento";
        
        $pilotos = $this->_em->createQuery($query)
                    ->setParameter('fechaVencimiento', $fechaVencimiento->format('d-m-Y H:i:s'))
                    ->setParameter('idEmpresa', $empresa)
                    ->getResult();
        return $pilotos;
    }
}

?>
