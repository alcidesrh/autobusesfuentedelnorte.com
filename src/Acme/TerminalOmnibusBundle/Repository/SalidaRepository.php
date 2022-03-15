<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\BackendBundle\Services\UtilService;
use Acme\BackendBundle\Entity\User;

class SalidaRepository extends EntityRepository
{
    
    //Esta en correspondencia con lo que se lista en el controlador
    private $mapFieldToColumnsSorted = array(
        'id' => 's.id',
        'fecha' => 's.fecha',
        'estado' => 'e.id',
        'bus' => 'b.codigo',
    );
    
    public function getSalidaByIntinerarioEspecial($idItinerarioEspecial)
    {
        try {
            $query =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s"
                . " JOIN s.itinerario i "
                . " WHERE i.id = :idItinerarioEspecial ";
            $tarifa = $this->_em->createQuery($query)
                ->setMaxResults(1)
                ->setParameter('idItinerarioEspecial', $idItinerarioEspecial)
                ->getSingleResult();
            return $tarifa;
        } catch (NoResultException $exc) {
            return null;
        }
    }
    
    public function getSalidasByIntinerarioCiclico($idItinerarioCiclico, $fechaInit, $fechaEnd)
    {
        $query =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s"
            . " JOIN s.itinerario i "
            . " WHERE i.id = :idItinerarioCiclico "
            . " and (s.fecha BETWEEN :fechaInit AND :fechaEnd) "
            . " ORDER BY s.fecha DESC ";
        
        $tarifas = $this->_em->createQuery($query)
            ->setParameter('idItinerarioCiclico', $idItinerarioCiclico)
            ->setParameter('fechaInit', $fechaInit->format('d-m-Y H:i:s'))
            ->setParameter('fechaEnd', $fechaEnd->format('d-m-Y H:i:s'))
            ->getResult();
        return $tarifas;
    }
    
    public function getSalidasParaMovil($user)
    {
        $estacionUser = null;
        if($user instanceof User){
            if($user->getEstacion() !== null){
                $estacionUser = $user->getEstacion()->getId();
            }
            $user = $user->getId();
        }
        
        $fechaInit = new \DateTime();
        $fechaInit->modify("-24 hour");
        $fechaInit->format('d-m-Y H:i:s');
        $fechaEnd = new \DateTime();
        $fechaEnd->modify("+6 hour");
        $fechaEnd->format('d-m-Y H:i:s');
        
        $queryStr =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " INNER JOIN s.bus b "
                . " INNER JOIN s.piloto p "
                . " INNER JOIN s.estado e "
                . " INNER JOIN s.itinerario i "
                . " INNER JOIN i.ruta r "
                . " INNER JOIN r.estacionOrigen eo "
                . " INNER JOIN r.estacionDestino ed "
                . " LEFT JOIN r.listaEstacionesIntermediaOrdenadas leio "
                . " LEFT JOIN leio.estacion ei "
                . " WHERE "
                . " (s.fecha BETWEEN :fechaInit AND :fechaEnd)  ";
        
        if($estacionUser === null){
            $queryStr .= " and e.id IN (1,2,3) ";
        }else{
            $queryStr .= " and ((eo.id=:estacionUser and e.id IN (1,2)) or ((ei.id=:estacionUser or ed.id=:estacionUser) and e.id IN (3))) ";
        }
        $queryStr .= " ORDER BY s.fecha DESC ";
        $query = $this->_em->createQuery($queryStr)
                ->setParameter('fechaInit', $fechaInit)
                ->setParameter('fechaEnd', $fechaEnd);
        if($estacionUser !== null){
            $query->setParameter('estacionUser', $estacionUser);
        }
        
        $items = $query->getResult();
        return $items;
    }
    
    public function getSalidasParaMovilEncomiendas($user)
    {
        $estacionUser = null;
        if($user instanceof User){
            if($user->getEstacion() !== null){
                $estacionUser = $user->getEstacion()->getId();
            }
            $user = $user->getId();
        }
        
        $fechaInit = new \DateTime();
        $fechaInit->modify("-24 hour");
        $fechaInit->format('d-m-Y H:i:s');
        $fechaEnd = new \DateTime();
        $fechaEnd->modify("+6 hour");
        $fechaEnd->format('d-m-Y H:i:s');
        
        $queryStr =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " LEFT JOIN s.bus b "
                . " LEFT JOIN s.piloto p "
                . " INNER JOIN s.estado e "
                . " INNER JOIN s.itinerario i "
                . " INNER JOIN i.ruta r "
                . " INNER JOIN r.estacionOrigen eo "
                . " INNER JOIN r.estacionDestino ed "
                . " LEFT JOIN r.listaEstacionesIntermediaOrdenadas leio "
                . " LEFT JOIN leio.estacion ei "
                . " WHERE "
                . " (s.fecha BETWEEN :fechaInit AND :fechaEnd)  ";
        
        if($estacionUser === null){
            $queryStr .= " and e.id IN (1,2,3) ";
        }else{
            $queryStr .= " and ((eo.id=:estacionUser and e.id IN (1,2)) or ((ei.id=:estacionUser or ed.id=:estacionUser) and e.id IN (1,2,3))) ";
        }
        
        $queryStr .= " ORDER BY s.fecha DESC ";
        
        $query = $this->_em->createQuery($queryStr)
                ->setParameter('fechaInit', $fechaInit)
                ->setParameter('fechaEnd', $fechaEnd);
        
        if($estacionUser !== null){
            $query->setParameter('estacionUser', $estacionUser);
        }
        
        $items = $query->getResult();
        return $items;
    }
    
    //Se desactivo lsv por problema de rendimiento y pq no se usa en el controlador.
    public function getDatosParcialesSalida($idSalida)
    {
         try {
            $query =  " SELECT "
                    . " partial s.{id, fecha}, "
                    . " partial tb.{id,nivel2}, "
                    . " las, "
                    . " lsn, "
//                    . " lsv, "
                    . " partial clas.{id}, "
                    . " partial tlsn.{id}, "
                    . " partial i.{id}, "
                    . " partial r.{codigo}, "
                    . " partial eo.{id,alias,nombre}, "
                    . " partial ed.{id,alias,nombre}, "
                    . " partial leio.{id,ruta,estacion,posicion}, "
                    . " partial ei.{id,alias,nombre} "
                    . " FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
                    . " INNER JOIN s.tipoBus tb "
                    . " INNER JOIN tb.listaAsiento las "
                    . " INNER JOIN las.clase clas "
                    . " INNER JOIN tb.listaSenal lsn "
                    . " INNER JOIN lsn.tipo tlsn "
//                    . " INNER JOIN tb.listaServicios lsv "
                    . " INNER JOIN s.itinerario i "
                    . " INNER JOIN i.ruta r "
                    . " INNER JOIN r.estacionOrigen eo "
                    . " INNER JOIN r.estacionDestino ed "
                    . " LEFT JOIN r.listaEstacionesIntermediaOrdenadas leio "
                    . " LEFT JOIN leio.estacion ei "
                    . " WHERE s.id = :idSalida ";
            
            $salida = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getSingleResult();
            return $salida;
        } catch (NoResultException $exc) {
            return null;
        }
    }
    
    public function getSalidasByFechaEstacion($fecha, $estacion)
    {
        if(($fecha === null || $fecha === false || (is_string($fecha) && trim($fecha) === "")) || 
                ($estacion === null || trim($estacion) === "")){
            return array();
        }
        
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        
        if(is_string($fecha)){
             $fechaTemp = \DateTime::createFromFormat('d-m-Y', $fecha);
             if($fechaTemp === false){
                 $fechaTemp = \DateTime::createFromFormat('d/m/Y', $fecha);
             }
             if($fechaTemp === false){
                    return array();
             }
             $fecha = $fechaTemp;
        }
        $fechaInit = clone $fecha;
        $fechaInit->setTime(0, 0, 0);
        $fechaInit = $fechaInit->format('d-m-Y H:i:s');
        $fechaEnd = clone $fecha;
        $fechaEnd->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEnd = $fechaEnd->format('d-m-Y H:i:s');
        
        $query =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " JOIN s.itinerario i "
                . " JOIN i.ruta r "
                . " JOIN r.estacionOrigen eo "
                . " JOIN s.estado e "
                . " WHERE "
                . " (eo.id = :estacion) and (s.fecha BETWEEN :fechaInit AND :fechaEnd) and (e.id IN (1,2,3)) "
                . " ORDER BY s.fecha ASC ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('estacion', $estacion)
            ->setParameter('fechaInit', $fechaInit)
            ->setParameter('fechaEnd', $fechaEnd)
            ->getResult();
        
        return $items;
    }
    
    //Para Combobox del backend
    public function getSalidas($fecha, $estacionOrigen, $estacionDestino)
    {
        if($fecha === null || $fecha === false || (is_string($fecha) && trim($fecha) === "") || $estacionOrigen === null || trim($estacionOrigen) === "" || 
                $estacionDestino === null || trim($estacionDestino) === ""){
            return array();
        }
        
        $idEstacionOrigen = $estacionOrigen;
        if($estacionOrigen instanceof Estacion){
            $idEstacionOrigen = $estacionOrigen->getId();
        }
        $idEstacionDestino = $estacionDestino;
        if($estacionDestino instanceof Estacion){
            $idEstacionDestino = $estacionDestino->getId();
        }
        
        if(is_string($fecha)){
            $fecha = \DateTime::createFromFormat('d-m-Y', $fecha);
        }
        $fechaInit = clone $fecha;
        $fechaInit->setTime(0, 0, 0);
        $fechaInit = $fechaInit->format('d-m-Y H:i:s');
        $fechaEnd = clone $fecha;
        $fechaEnd->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEnd = $fechaEnd->format('d-m-Y H:i:s');
        
        $query =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s"
            . " JOIN s.itinerario i "
            . " JOIN i.ruta r "
            . " JOIN r.estacionOrigen eo "
            . " JOIN r.estacionDestino ed "
            . " LEFT JOIN r.listaEstacionesIntermediaOrdenadas leio "
            . " LEFT JOIN leio.estacion l "
            . " WHERE "
            . " (eo.id = :estacionOrigen or l.id = :estacionOrigen )"
            . " and ( ed.id = :estacionDestino or l.id = :estacionDestino ) "
            . " and (s.fecha BETWEEN :fechaInit AND :fechaEnd) "
            . " ORDER BY s.fecha ASC ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('estacionOrigen', $idEstacionOrigen)
            ->setParameter('estacionDestino', $idEstacionDestino)
            ->setParameter('fechaInit', $fechaInit)
            ->setParameter('fechaEnd', $fechaEnd)
            ->getResult();
        
        return $items;
    }
    
    //Para Combobox del cosultar esquema bus
    public function getSalidasByFecha($fecha)
    {
        if($fecha === null || $fecha === false || (is_string($fecha) && trim($fecha) === "")){
            return array();
        }
        
        if(is_string($fecha)){
            $fecha = \DateTime::createFromFormat('d/m/Y', $fecha);
        }
        $fechaInit = clone $fecha;
        $fechaInit->setTime(0, 0, 0);
        $fechaInit = $fechaInit->format('d-m-Y H:i:s');
        $fechaEnd = clone $fecha;
        $fechaEnd->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEnd = $fechaEnd->format('d-m-Y H:i:s');
        
        $query =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " INNER JOIN s.empresa e "
                . " WHERE "
                . " (s.fecha BETWEEN :fechaInit AND :fechaEnd) "
                . " ORDER BY s.fecha ASC ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('fechaInit', $fechaInit)
            ->setParameter('fechaEnd', $fechaEnd)
            ->getResult();
        
        return $items;
    }
    
    public function getSalidasByFechaByUser($fecha, $user)
    {
        if($fecha === null || $fecha === false || (is_string($fecha) && trim($fecha) === "")){
            return array();
        }
        
        if(is_string($fecha)){
            $fecha = \DateTime::createFromFormat('d/m/Y', $fecha);
        }
        $fechaInit = clone $fecha;
        $fechaInit->setTime(0, 0, 0);
        $fechaInit = $fechaInit->format('d-m-Y H:i:s');
        $fechaEnd = clone $fecha;
        $fechaEnd->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEnd = $fechaEnd->format('d-m-Y H:i:s');
        
        $idEmpresas = $user->getIdEmpresas();
        
        $query =  " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
                . " INNER JOIN s.empresa e "
                . " WHERE "
                . " (s.fecha BETWEEN :fechaInit AND :fechaEnd) "
                . " and e.id IN (:idEmpresas) "
                . " ORDER BY s.fecha ASC ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('fechaInit', $fechaInit)
            ->setParameter('fechaEnd', $fechaEnd)
            ->setParameter('idEmpresas', $idEmpresas)
            ->getResult();
        
        return $items;
    }
    
     /*
            SE UTILIZA PARA EL BUSCADOR DE SALIDAS
     */
    public function getSalidasPaginadas($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $idEmpresasUsuarioFilter = array();
        $empresasUsuarioFilter = $usuario->getEmpresas();
        foreach ($empresasUsuarioFilter as $empresa) {
            $idEmpresasUsuarioFilter[] = $empresa->getId();
        }

        $fechaInitFilter = new \DateTime();
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
        $origenFilter = UtilService::getValueToMap($mapFilters, "origen");  
        $destinoFilter = UtilService::getValueToMap($mapFilters, "destino");        
        $tipoBusFilter = UtilService::getValueToMap($mapFilters, "tipoBus");   
        $estadoFilter = UtilService::getValueToMap($mapFilters, "estado");   
        $busFilter = UtilService::getValueToMap($mapFilters, "bus");   
        $pilotoFilter = UtilService::getValueToMap($mapFilters, "piloto");
        $empresaFilter = UtilService::getValueToMap($mapFilters, "empresa");
        $pendienteFilter = UtilService::getValueToMap($mapFilters, "pendiente");
//        var_dump($pendienteFilter);
        
        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Salida s"
            . " LEFT JOIN s.bus b "
            . " LEFT JOIN s.piloto p "
            . " LEFT JOIN s.tipoBus tb "
            . " LEFT JOIN s.itinerario i "
            . " LEFT JOIN i.ruta r "
            . " LEFT JOIN r.estacionOrigen eo "
            . " LEFT JOIN r.estacionDestino ed "
            . " LEFT JOIN r.listaEstacionesIntermediaOrdenadas leio "
            . " LEFT JOIN leio.estacion ei "    
            . " LEFT JOIN s.empresa emp "
            . " LEFT JOIN s.estado e "
            . " WHERE ";
        
        if($pendienteFilter === null || $pendienteFilter === "1"){
            if($estacionUsuarioFilter === null || trim($estacionUsuarioFilter) === ""){
                $queryStr .=  " e.id IN(1,2,3) ";
            }else{
                //En estacion de origen solo se ven los estados Programada y Abordando
                //En estacion de destino solo se ven el estado Iniciada, para que se pueda finalizar
                $queryStr .=  "((eo.id= :estacionUsuarioFilter and e.id IN(1,2)) or ((ei.id= :estacionUsuarioFilter or ed.id= :estacionUsuarioFilter) and e.id = 3))";
    //          $queryStr = $this->setParameterToQuerySTR($queryStr, array("eo.id", "ed.id"), "estacionUsuarioFilter", $estacionUsuarioFilter, false);
            }
        }else{
            if($estacionUsuarioFilter === null || trim($estacionUsuarioFilter) === ""){
                $queryStr .=  " e.id IN(1,2,3,4,5) ";
            }else{
                $queryStr .=  " ((eo.id= :estacionUsuarioFilter OR ei.id=:estacionUsuarioFilter OR ed.id= :estacionUsuarioFilter) AND e.id IN(1,2,3,4,5)) ";
            }
        }
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "s.id" ,"identificadorFilter", $identificadorFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("ed.alias", "ed.nombre") ,"destinoFilter", $destinoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("eo.alias", "eo.nombre") ,"origenFilter", $origenFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("tb.alias", "tb.descripcion") ,"tipoBusFilter", $tipoBusFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.nombre" ,"estadoFilter", $estadoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.codigo" ,"busFilter", $busFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "p.codigo" ,"pilotoFilter", $pilotoFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "emp.alias" ,"empresaFilter", $empresaFilter);
        $queryStr .= " and (s.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter) ";
        $queryStr .= " and (emp.id IN ( :idEmpresasUsuarioFilter )) ";
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " s.fecha ASC ";
        }
//        var_dump($queryStr);
        $query = $this->_em->createQuery(" SELECT s " . $queryStr . " GROUP BY s ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "destinoFilter", $destinoFilter);
        UtilService::setParameterToQuery($query, "origenFilter", $origenFilter);
        UtilService::setParameterToQuery($query, "tipoBusFilter", $tipoBusFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "busFilter", $busFilter, false);
        UtilService::setParameterToQuery($query, "pilotoFilter", $pilotoFilter, false);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(distinct s.id) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "destinoFilter", $destinoFilter);
        UtilService::setParameterToQuery($query, "origenFilter", $origenFilter);
        UtilService::setParameterToQuery($query, "tipoBusFilter", $tipoBusFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "busFilter", $busFilter, false);
        UtilService::setParameterToQuery($query, "pilotoFilter", $pilotoFilter, false); 
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "idEmpresasUsuarioFilter", $idEmpresasUsuarioFilter, false);
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
    
    /*
            SE UTILIZA PARA LA VENTA DE BOLETOS
     */
    public function getSalidasActivasPaginadas($page, $rows, $fechaSalidaFilter = null, $origenFilter = null, $usuario)
    {
        
        if(!is_int($page)){
            $page = intval($page);
        }
        if($page < 0){
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
        
        if(is_string($fechaSalidaFilter)){
             $fechaSalidaFilterTemp = \DateTime::createFromFormat('d-m-Y', $fechaSalidaFilter);
             if($fechaSalidaFilterTemp === false){
                 $fechaSalidaFilterTemp = \DateTime::createFromFormat('d/m/Y', $fechaSalidaFilter);
             }
             if($fechaSalidaFilterTemp === false){
                 throw new \RuntimeException("No se pudo conventir la fecha:" . $fechaSalidaFilter);
             }
             $fechaSalidaFilter = $fechaSalidaFilterTemp;
        }
        
        $fechaInitFilter = clone  $fechaSalidaFilter;
        $fechaInitFilter->setTime(0, 0, 0); //Hora, minuto, y segundos
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = clone  $fechaSalidaFilter;
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');

        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
            . " LEFT JOIN s.tipoBus tb "
            . " LEFT JOIN tb.clase c "
            . " LEFT JOIN s.itinerario i "
            . " LEFT JOIN i.ruta r "
            . " LEFT JOIN r.estacionOrigen eo "
            . " LEFT JOIN r.estacionDestino ed "
            . " LEFT JOIN s.estado e "
            . " WHERE ";
        
        //Solamente se listan los estados que permiten venta d boleto, que son el 1 y el 2.
        $queryStr .= " (s.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter) and (e.id IN (1,2,3)) "; 
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "eo.id" ,"origenFilter", $origenFilter, false);

        $selectPartial =  " SELECT "
                        . " partial s.{id, fecha}, "
                        . " partial i.{id}, "
                        . " partial r.{codigo}, "
                        . " partial eo.{id,alias,nombre}, "
                        . " partial ed.{id,alias,nombre}, "
                        . " partial tb.{id, alias}, "
                        . " partial c.{id, nombre}, "
                        . " partial e.{id, nombre}";
        
        $query = $this->_em->createQuery($selectPartial . $queryStr . " order by s.fecha ")->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "origenFilter", $origenFilter, false);
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(s) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "origenFilter", $origenFilter, false);      
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
    
    
    //Para Combobox
    public function listarSalidasPaginando($pageLimit = null, $term = null)
    {
        if($pageLimit === null || (is_string($pageLimit) && trim($pageLimit) === "")){
            $pageLimit = 20;
        }
        $pageLimit = intval($pageLimit);
        if($pageLimit > 50){
            $pageLimit = 50;
        }
        
        $queryStr =  " SELECT s "
                   . " FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
//                   . " INNER JOIN s.itinerario i "
//                   . " INNER JOIN i.ruta r "
                   ;
        
        if($term !== null && trim($term) !== ""){
//           $queryStr .= " WHERE ((s.id=:termID) or (r.nombre like :termRUTA)) ";
           $queryStr .= " WHERE s.id=:termID ";
        }
        $queryStr .= " order by s.id desc ";
        $query = $this->_em->createQuery($queryStr);
        if($term !== null && trim($term) !== ""){
           $query->setParameter('termID', $term);
//           $query->setParameter('termRUTA', "%".$term."%");
        }
        
        $query->setMaxResults($pageLimit);
        return $query->getResult();
    }
    
    public function listarSalidasEspeciales(\DateTime $fechaDia)
    {       
        $fechaInitFilter = clone $fechaDia;
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = clone $fechaDia;
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');

        $query =      " SELECT s FROM Acme\TerminalOmnibusBundle\Entity\Salida s "
                    . " INNER JOIN s.empresa e "
                    . " INNER JOIN s.itinerario i "
                    . " WHERE "
                    . " i INSTANCE OF Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial "
                    . " and s.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter " 
                    . " ORDER BY "
                    . " e.id ASC  ";
        
        $items = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        return $items;
    }
    
    public function listarSalidasPendientes($estacion = null, $idEmpresas = array())
    {       
        $fechaInitFilter = new \DateTime();
        $fechaInitFilter->modify("-3 day");
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter->modify("-10 minutes");
//        $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');

        $query =      " SELECT sa FROM Acme\TerminalOmnibusBundle\Entity\Salida sa "
                    . " INNER JOIN sa.empresa em "
                    . " INNER JOIN sa.estado es "
                    . " INNER JOIN sa.itinerario it "
                    . " INNER JOIN it.ruta ru "
                    . " INNER JOIN ru.estacionOrigen eo "
                    . " WHERE "
                    . " es.id IN (1,2) "
                    . " and sa.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter "
                    . " and em.id IN (:idEmpresas) "
                    ;
        
        if($estacion !== null){
            if($estacion instanceof Estacion){
                $estacion = $estacion->getId();
            }
            $query .= " and eo.id = :estacion ";
        }
        
        $query .= " ORDER BY sa.fecha ASC ";
        
        $query = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->setParameter('idEmpresas', $idEmpresas)
                ;
        
        if($estacion !== null){
            $query->setParameter('estacion', $estacion);
        }
        
        return $query->getResult();
    }
    
    public function listarSalidasFuturas($end = "+5 year")
    {
        $fechaInitFilter = new \DateTime();
        $fechaInitFilter->modify("-1 day");
        $fechaInitFilter = $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
              
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter->modify($end);
        $fechaEndFilter = $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =  " SELECT sa from Acme\TerminalOmnibusBundle\Entity\Salida sa "
                . " WHERE "
                . " sa.fecha between :fechaInitFilter and :fechaEndFilter ";
            
        $items = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        return $items;
    }
}

?>
