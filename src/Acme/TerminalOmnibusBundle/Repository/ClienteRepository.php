<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\BackendBundle\Services\UtilService;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Acme\TerminalOmnibusBundle\Entity\Nacionalidad;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumento;

class ClienteRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(
        'id' => 'c.id',
        'nombre' => 'c.nombre',
        'nit' => 'c.nit',
        'dpi' => 'c.dpi'
    );
    
    //Para Grid
    public function getClientesPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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

        $identificadorFilter = UtilService::getValueToMap($mapFilters, "identificador");
        $nombreFilter = UtilService::getValueToMap($mapFilters, "nombre");  
        $nitFilter =UtilService::getValueToMap($mapFilters, "nit");        
        $dpiFilter = UtilService::getValueToMap($mapFilters, "dpi");      

        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Cliente c ";
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "c.id" ,"identificadorFilter", $identificadorFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "c.nombre" ,"nombreFilter", $nombreFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "c.nit" ,"nitFilter", $nitFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "c.dpi" ,"dpiFilter", $dpiFilter);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder !== ""){
            $queryOrder = " ORDER BY " . $queryOrder;
        }
        
        $query = $this->_em->createQuery(" SELECT c " . $queryStr . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "nombreFilter", $nombreFilter);
        UtilService::setParameterToQuery($query, "nitFilter", $nitFilter);
        UtilService::setParameterToQuery($query, "dpiFilter", $dpiFilter);
//        var_dump($query->getDQL());
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(c) " .$queryStr);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "nombreFilter", $nombreFilter);
        UtilService::setParameterToQuery($query, "nitFilter", $nitFilter);
        UtilService::setParameterToQuery($query, "dpiFilter", $dpiFilter);
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
    
    //Para Combobox
    public function listarClientesPaginando($pageLimit = null, $term = null, $id = null)
    {
        if($pageLimit === null || (is_string($pageLimit) && trim($pageLimit) === "")){
            $pageLimit = 20;
        }
        $pageLimit = intval($pageLimit);
        if($pageLimit > 50){
            $pageLimit = 50;
        }
        
        $queryStr =  " SELECT partial c.{id, nit, nombre, dpi} FROM Acme\TerminalOmnibusBundle\Entity\Cliente c ";
        if($id !== null && trim($id) !== ""){
            $queryStr .= " WHERE (c.id =:id ) ";
        }else if($term !== null && trim($term) !== ""){
            $queryStr .=  " WHERE ((c.nit like :termNIT) or (c.dpi like :termDPI) or (c.nombre like :termNOMBRE)) ";
        }
//        var_dump($queryStr);
        $query = $this->_em->createQuery($queryStr);
        if($id !== null && trim($id) !== ""){
            $query->setParameter('id', $id);
        }else if($term !== null && trim($term) !== ""){
           $query->setParameter('termNIT', "".$term."%");
           $query->setParameter('termDPI', "".$term."%");
           $query->setParameter('termNOMBRE', "".$term."%");
        }
        
        $query->setMaxResults($pageLimit);
        return $query->getResult();
    }
    
     //Para Combobox
    public function listarClientesPaginandoNativo($pageLimit = null, $term = null, $id = null)
    {
        if($pageLimit === null || (is_string($pageLimit) && trim($pageLimit) === "")){
            $pageLimit = 20;
        }
        else{
            $pageLimit = intval($pageLimit);
            if($pageLimit > 50){
                $pageLimit = 50;
            }
        }
        
        $queryStr =  " select TOP " .$pageLimit. " cli.id as id, cli.nit as nit, cli.nombre as nombre, tdo.sigla as siglaDocumento, cli.dpi as dpi "
                . " FROM cliente cli "
                . " LEFT JOIN tipo_documento tdo ON tdo.id = cli.tipo_documento_id ";
        
        if($id !== null && trim($id) !== ""){
            $queryStr .= " WHERE ( cli.id=:id ) ";
        }else if($term !== null){
            $term = trim($term);
            if(trim($term) !== ""){
                $queryStr .=  " WHERE ((cli.nit like :term) or (cli.dpi like :term) or (cli.nombre like :term)) ";
            }
        }
        
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('nit', 'nit');
        $rsm->addScalarResult('nombre', 'nombre');
        $rsm->addScalarResult('siglaDocumento', 'siglaDocumento');
        $rsm->addScalarResult('dpi', 'dpi');
        $query = $this->_em->createNativeQuery($queryStr, $rsm);
        if($id !== null && trim($id) !== ""){
            $query->setParameter('id', $id);
        }else if($term !== null && $term !== ""){
           $query->setParameter('term', $term."%");
        }
        $items = $query->getArrayResult();
        return $items;
    }
    
    public function checkExisteCliente($nacionalidad, $tipoDocumento, $dpi, $nit, $nombre, $id = null)
    {
        if($nacionalidad instanceof Nacionalidad){
            $nacionalidad = $nacionalidad->getId();
        }
        
        if($tipoDocumento instanceof TipoDocumento){
            $tipoDocumento = $tipoDocumento->getId();
        }
        
        try {
//            var_dump($id);
            $query =  " SELECT c FROM Acme\TerminalOmnibusBundle\Entity\Cliente c "
                    . " INNER JOIN c.tipoDocumento t "
                    . " INNER JOIN c.nacionalidad n "
                    . " WHERE ";
            
            $subquery1 = " c.nombre=:nombre ";
            $subquery2 = "";  //se valida NIT Y nombre
            $subquery3 = "";
            
            if($dpi !== null && trim($dpi) !== ""){
                $subquery2 .= " or (n.id=:nacionalidad and t.id=:tipoDocumento and c.dpi=:dpi) ";
                $subquery1 .= " and (n.id=:nacionalidad and t.id=:tipoDocumento and c.dpi=:dpi) ";
            }else{
                $subquery1 .= " and ( c.dpi is null and n.id=:nacionalidad ) ";
            }
            
            if($nit !== null && trim($nit) !== "" && trim($nit) !== "CF"){
                $subquery2 .= " or (c.nit=:nit and c.nombre=:nombre) ";
                $subquery1 .= " and (c.nit=:nit and c.nombre=:nombre) ";
            }else{
                $subquery1 .= " and c.nit='CF' ";
            }
            
            if($id !== null && trim($id) !== ""){
                $subquery3 .= " and c.id<>:id ";
            }
            
            $query .= " (( " .$subquery1 . " ) " . $subquery2 . " ) ". $subquery3;
            
            $query = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('nombre', $nombre)
                    ->setParameter('nacionalidad', $nacionalidad);
            
            if($dpi !== null && trim($dpi) !== ""){
                $query->setParameter('dpi', $dpi);
                $query->setParameter('tipoDocumento', $tipoDocumento);
            }
            
            if($nit !== null && trim($nit) !== "" && trim($nit) !== "CF"){
                $query->setParameter('nit', $nit);
            }
            
            if($id !== null && trim($id) !== ""){
                $query->setParameter('id', $id);
            }
            
            $primerCliente = $query->getSingleResult();
            
            if($primerCliente !== null){
                if($primerCliente->getNit() === $nit && trim($primerCliente->getNit()) !== "CF"){
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente,
                        "mensaje" => "Ya existe un cliente con el NIT: " . $primerCliente->getNit() . " y el nombre: " . $primerCliente->getNombre() . "."
                    );
                }else if($primerCliente->getDpi() === $dpi && trim($primerCliente->getDpi()) !== ""){
                    $mensaje = "Ya existe un cliente de nombre: " . $primerCliente->getNombre() . ", " .
                        " de nacionalidad: " . strtoupper($primerCliente->getNacionalidad()) . ", " .
                        " con el tipo de documento: " . strtoupper($primerCliente->getTipoDocumento()) .
                        " y valor de documento: " . $primerCliente->getDpi() . ".";
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente,
                        "mensaje" => $mensaje
                    );
                }else if(strtoupper($primerCliente->getNombre()) === strtoupper($nombre)){
                    $mensaje = "Ya existe un cliente de nombre: " . $primerCliente->getNombre() . ", " .
                        " de nacionalidad: " . strtoupper($primerCliente->getNacionalidad());
                    if(trim($primerCliente->getDpi()) !== ""){
                        $mensaje .= ", con el tipo de documento: " . strtoupper($primerCliente->getTipoDocumento());
                        $mensaje .= ", valor de documento: " . $primerCliente->getDpi();
                    }
                    if(trim($primerCliente->getNit()) !== ""){
                        $mensaje .= ", con el NIT: " . $primerCliente->getNit();
                    }    
                    $mensaje .= ".";
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente,
                        "mensaje" => $mensaje
                    );
                }else{
                    return array(
                        "existe" => true,
                        "cliente" => $primerCliente,
                        "mensaje" => "Ya existe el cliente con los datos especificados especificado."
                    );
                }
            } else{
                return array(
                    "existe" => false
                );
            }
         } catch (NoResultException $exc) {
             return array(
                "existe" => false
             );
         }
    }
    
    public function getClienteByDocumento($nacionalidad, $tipoDocumento, $dpi)
    {
        if($nacionalidad instanceof Nacionalidad){
            $nacionalidad = $nacionalidad->getId();
        }
        
        if($tipoDocumento instanceof TipoDocumento){
            $tipoDocumento = $tipoDocumento->getId();
        }
        
        try {
            $query =  " SELECT c FROM Acme\TerminalOmnibusBundle\Entity\Cliente c "
                    . " INNER JOIN c.tipoDocumento t "
                    . " INNER JOIN c.nacionalidad n "
                    . " WHERE "
                    . " n.id=:nacionalidad and t.id=:tipoDocumento and c.dpi=:dpi ";

            $query = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('nacionalidad', $nacionalidad)
                    ->setParameter('tipoDocumento', $tipoDocumento)
                    ->setParameter('dpi', $dpi)
                    ;

            return $query->getSingleResult();
            
         } catch (NoResultException $exc) {
             return null;
         }
    }
    
}

?>
