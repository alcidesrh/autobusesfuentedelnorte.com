<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\BackendBundle\Services\UtilService;

class ItinerarioEspecialRepository extends EntityRepository
{
     private $mapFieldToColumnsSorted = array(
        'id' => 'i.id',
        'fecha' => 'i.fecha',
        'ruta' => 'r.codigo',
        'tipoBus' => 'tb.alias',
        'activo' => 'i.activo',
        'motivo' => 'i.motivo'
    );
     
    public function getItinerariosEspecialesPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        if($estacionUsuarioFilter !== null){
            $estacionUsuarioFilter = $estacionUsuarioFilter->getId();
        }

        $fechaEndFilter = UtilService::getValueToMap($mapFilters, "fechaEnd", new \DateTime());
        $identificadorFilter = UtilService::getValueToMap($mapFilters, "identificador");
        $origenFilter = UtilService::getValueToMap($mapFilters, "origen");  
        $destinoFilter =UtilService::getValueToMap($mapFilters, "destino");        
        $tipoBusFilter = UtilService::getValueToMap($mapFilters, "tipoBus");   
        $rutaFilter = UtilService::getValueToMap($mapFilters, "ruta");   
        $motivoFilter = UtilService::getValueToMap($mapFilters, "motivo");   
        $activoFilter = UtilService::getValueToMap($mapFilters, "activo");       

        if(is_string($fechaEndFilter)){
             $fechaEndFilterTemp = \DateTime::createFromFormat('d-m-Y', $fechaEndFilter);
             if($fechaEndFilterTemp === false){
                 $fechaEndFilterTemp = \DateTime::createFromFormat('d/m/Y', $fechaEndFilter);
             }
             if($fechaEndFilterTemp === false){
                 throw new \RuntimeException("No se pudo conventir la fecha:" . $fechaEndFilter);
             }
             $fechaEndFilter = $fechaEndFilterTemp;
        }
        
        $fechaInitFilter = new \DateTime();
//        $fechaInitFilter->modify("-1 day");
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');

        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial i"
            . " LEFT JOIN i.estacionOrigen eo "
            . " LEFT JOIN i.ruta r "
            . " LEFT JOIN r.estacionOrigen reo "
            . " LEFT JOIN r.estacionDestino red "    
            . " LEFT JOIN i.tipoBus tb "
            . " WHERE "
            . " (i.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter) ";
        
        //El usuario solo puede ver los itinerarios especiales de su estacion
        if($estacionUsuarioFilter !== null && trim($estacionUsuarioFilter) !== ""){
            $queryStr .=  " and (eo.id= :estacionUsuarioFilter and reo.id= :estacionUsuarioFilter)";
        }
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "i.id" ,"identificadorFilter", $identificadorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("red.alias", "red.nombre") ,"destinoFilter", $destinoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("reo.alias", "reo.nombre") ,"origenFilter", $origenFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("tb.alias", "tb.descripcion") ,"tipoBusFilter", $tipoBusFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "r.codigo" ,"rutaFilter", $rutaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "i.motivo" ,"motivoFilter", $motivoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "i.activo" ,"activoFilter", $activoFilter);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " i.fecha ASC ";
        }
        
        $query = $this->_em->createQuery(" SELECT i " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "destinoFilter", $destinoFilter);
        UtilService::setParameterToQuery($query, "origenFilter", $origenFilter);
        UtilService::setParameterToQuery($query, "tipoBusFilter", $tipoBusFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter);
        UtilService::setParameterToQuery($query, "motivoFilter", $motivoFilter);
        UtilService::setParameterToQuery($query, "activoFilter", $activoFilter);
//        var_dump($query->getDQL());
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(i) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "destinoFilter", $destinoFilter);
        UtilService::setParameterToQuery($query, "origenFilter", $origenFilter);
        UtilService::setParameterToQuery($query, "tipoBusFilter", $tipoBusFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter);
        UtilService::setParameterToQuery($query, "motivoFilter", $motivoFilter);
        UtilService::setParameterToQuery($query, "activoFilter", $activoFilter);
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
    
    public function listarItinerariosEspecialesFuturos()
    {
        $fechaInitFilter = new \DateTime();
        $fechaInitFilter->modify("-1 day");
        $fechaInitFilter = $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
              
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter->modify("+5 year");
        $fechaEndFilter = $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =  " SELECT ie from Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial ie "
                . " WHERE "
                . " ie.fecha between :fechaInitFilter and :fechaEndFilter ";
            
        $items = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        return $items;
    }
}

?>
