<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class TipoEncomiendaEspecialesRepository extends EntityRepository
{
    //Para Combobox
    public function listarTipoEncomiendaEspecialesPaginandoNativo($pageLimit = null, $term = null, $id = null)
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
        
        $queryStr =  " select TOP " .$pageLimit. " id, nombre, descripcion from encomienda_especiales_tipo "
                   . " where activo=1 ";
        
        if($id !== null && trim($id) !== ""){
            $queryStr .= " and ( id=:id ) ";
        }else if($term !== null){
            $term = trim($term);
            if(trim($term) !== ""){
                $queryStr .=  " and (nombre like :term) ";
            }
        }
        $queryStr .=  " order by nombre ";
        
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('nombre', 'nombre');
        $rsm->addScalarResult('descripcion', 'descripcion');
        $query = $this->_em->createNativeQuery($queryStr, $rsm);
        if($id !== null && trim($id) !== ""){
            $query->setParameter('id', $id);
        }else if($term !== null && $term !== ""){
           $query->setParameter('term', "%".$term."%");
        }
        $items = $query->getArrayResult();
        return $items;
    }
}

?>
