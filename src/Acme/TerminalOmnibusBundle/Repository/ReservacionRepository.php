<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\Salida;

class ReservacionRepository extends EntityRepository
{
    
    //Esta en correspondencia con lo que se lista en el controlador
    private $mapFieldToColumnsSorted = array(
        'id' => 'r.id',
        'fecha' => 's.fecha'
    );
    
    /*
            SE UTILIZA PARA EL BUSCADOR DE SALIDAS
     */
    public function getReservacionesPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $agencia = false;
        $idAgencia = null;
        $estacionUsurio = $usuario->getEstacion();
        if($estacionUsurio !== null && $estacionUsurio->getTipo()->getId() === \Acme\TerminalOmnibusBundle\Entity\TipoEstacion::AGENCIA){
            $agencia = true;
            $idAgencia = $estacionUsurio->getId();
        }
        
        $fechaInitFilter = new \DateTime();
        $fechaInitFilter->modify("-30 days");
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter->modify("+30 days");
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
        $clienteFilter = UtilService::getValueToMap($mapFilters, "cliente");   
        $numeroAsientoFilter = UtilService::getValueToMap($mapFilters, "numeroAsiento");   
        $rutaFilter = UtilService::getValueToMap($mapFilters, "ruta");

        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
            . " LEFT JOIN r.estacionCreacion ec "
            . " LEFT JOIN r.salida s "
            . " LEFT JOIN s.itinerario i "
            . " LEFT JOIN i.ruta ru "
            . " LEFT JOIN r.cliente c "
            . " LEFT JOIN r.estado e "
            . " LEFT JOIN r.asientoBus ab "              
            . " WHERE "
            . " e.id = 1 and "
            . " ((s.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter) or "
            . "  (r.fechaCreacion BETWEEN :fechaInitFilter AND :fechaEndFilter)) "; 
        
       if($agencia === true){
            $queryStr .= " and (ec.id = :estacionAgencia) ";
        }
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "r.id" ,"identificadorFilter", $identificadorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "c.id" ,"clienteFilter", $clienteFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "ab.numero" ,"numeroAsientoFilter", $numeroAsientoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("ru.codigo", "ru.nombre") ,"rutaFilter", $rutaFilter);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " s.fecha ASC ";
        }
        
        $query = $this->_em->createQuery(" SELECT r " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "clienteFilter", $clienteFilter, false);
        UtilService::setParameterToQuery($query, "numeroAsientoFilter", $numeroAsientoFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter);
        if($agencia === true){
            UtilService::setParameterToQuery($query, "estacionAgencia", $idAgencia, false);
        }
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(r) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter, false);
        UtilService::setParameterToQuery($query, "clienteFilter", $clienteFilter, false);
        UtilService::setParameterToQuery($query, "numeroAsientoFilter", $numeroAsientoFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter); 
        if($agencia === true){
            UtilService::setParameterToQuery($query, "estacionAgencia", $idAgencia, false);
        }
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
    
    //Se utiliza para el consultar de asiento de de salida
    public function getReservacionesActivasPorSalidaNroAsiento($idSalida, $nroAsiento)
    {
        $query =  " SELECT r FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                . " left join r.asientoBus ab "
                . " left join r.salida s "
                . " WHERE s.id = :idSalida "
                . " and ab.numero= :nroAsiento"
                . " and r.estado=1";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->setParameter('nroAsiento', $nroAsiento)
                ->getResult();
//        var_dump($items);
        return $items;
    }
    
    public function getDatosParcialesReservacionesActivosPorSalida($idSalida)
    {
        $query =  " SELECT "
                . " partial r.{id} ,"
                . " partial ab.{id, numero}, "
                . " partial cl.{id}, "
                . " partial c.{id, nombre} "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                . " INNER JOIN  r.asientoBus ab "
                . " LEFT JOIN  r.cliente cl "
                . " LEFT JOIN  ab.clase c "
                . " join r.salida s "
                . " WHERE s.id = :idSalida and (r.estado=1)";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function listarReservacionesActivasBySalida($idSalida)
    {
        $query =  " SELECT r FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                . " join r.salida s"
                . " WHERE s.id = :idSalida and (r.estado=1)";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function listarReservacionesEmitidasBySalida($idSalida)
    {
        $query =  " SELECT r FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                . " join r.salida s"
                . " WHERE s.id = :idSalida and r.estado=1";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function totalReservacionesEmitidasBySalida($idSalida)
    {
        try {
            $query =  " SELECT COUNT(r) FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r  "
                    . " join r.salida s"
                    . " WHERE s.id = :idSalida and r.estado=1";
            $cantidad = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('idSalida', $idSalida)
                    ->getSingleResult();
            return $cantidad[1];
         } catch (NoResultException $exc) {
            return 0;
         }
    }
    
    public function getReservacionesInternasFueraTiempo($fechaActual, $diasCancelacion, $tiempoCancelacion)
    {
        if($fechaActual === null || $fechaActual === false || (is_string($fechaActual) && trim($fechaActual) === "")){
            return array();
        }
        
        if(is_string($fechaActual)){
            $fechaActual = \DateTime::createFromFormat('d-m-Y', $fechaActual);
        }
        
        $fechaDiasCancelacion = clone $fechaActual;
        $fechaDiasCancelacion->modify('- '.$diasCancelacion . " day");
        $fechaDiasCancelacion = $fechaDiasCancelacion->format('d-m-Y H:i:s');
        $fechaTiempoCancelacion = clone $fechaActual;
        $fechaTiempoCancelacion->modify('+ '.$tiempoCancelacion . " hour");
        $fechaTiempoCancelacion = $fechaTiempoCancelacion->format('d-m-Y H:i:s');
//        var_dump("fechaDiasCancelacion:" . $fechaDiasCancelacion);
//        var_dump("fechaTiempoCancelacion:" . $fechaTiempoCancelacion);
        $query =  " SELECT r "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r"
                . " JOIN r.estado e "
                . " JOIN r.salida s "
                . " WHERE "
                . " e.id = 1 "
                . " and r.externa=0 "
                . " and (s.fecha < :fechaTiempoCancelacion or r.fechaCreacion < :fechaDiasCancelacion) ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('fechaTiempoCancelacion', $fechaTiempoCancelacion)
            ->setParameter('fechaDiasCancelacion', $fechaDiasCancelacion)
            ->getResult();
        
        return $items;
    }
    
    public function getReservacionesExternasFueraTiempo($fechaActual)
    {
        if($fechaActual === null || $fechaActual === false || (is_string($fechaActual) && trim($fechaActual) === "")){
            return array();
        }
        
        if(is_string($fechaActual)){
            $fechaActual = \DateTime::createFromFormat('d-m-Y', $fechaActual);
        }
        
        $fechaCancelacion = clone $fechaActual;
        $fechaCancelacion->modify("-11 minute");
        $fechaCancelacion = $fechaCancelacion->format('d-m-Y H:i:s');
        
        $query =  " SELECT r "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r"
                . " JOIN r.estado e "
                . " WHERE "
                . " e.id = 1 "
                . " and r.externa=1 "
                . " and r.fechaCreacion < :fechaCancelacion ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('fechaCancelacion', $fechaCancelacion)
            ->getResult();
        
        return $items;
    }
    
    //Se utiliza para cambiarle el asiento cuando se le hace un reajuste a la salida.
    public function getReservacionesPorSalida($salida)
    {
        if($salida instanceof Salida){
            $salida = $salida->getId();
        }
        
        $query =  " SELECT r FROM Acme\TerminalOmnibusBundle\Entity\Reservacion r "
                . " LEFT JOIN r.salida s ";
        $query .= " WHERE s.id = :idSalida ";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $salida)
                ->getResult();
        return $items;
    }
}

?>
