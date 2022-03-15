<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;
use Acme\BackendBundle\Services\UtilService;
use Acme\BackendBundle\Entity\User;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query\ResultSetMapping;
use Acme\TerminalOmnibusBundle\Entity\Empresa;
use Acme\TerminalOmnibusBundle\Entity\Salida;
use Acme\TerminalOmnibusBundle\Entity\Boleto;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class BoletoRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(
        'id' => 'b.id',
        'fecha' => 's.fecha',
        'numeroAsiento' => 'ab.numero',
        'ruta' => 'ru.codigo',
        'clienteDocumento' => 'cd.id',
        'clienteBoleto' => 'cb.id'
    );
    
    public function getBoletosPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        $numeroAsientoFilter = UtilService::getValueToMap($mapFilters, "numeroAsiento"); 
        $claseAsientoFilter = UtilService::getValueToMap($mapFilters, "claseAsiento"); 
        $rutaFilter = UtilService::getValueToMap($mapFilters, "ruta");
        $clienteDocumentoFilter = UtilService::getValueToMap($mapFilters, "clienteDocumento");
        $clienteBoletoFilter = UtilService::getValueToMap($mapFilters, "clienteBoleto");
        $estacionOrigenFilter = UtilService::getValueToMap($mapFilters, "estacionOrigen");
        $estacionDestinoFilter = UtilService::getValueToMap($mapFilters, "estacionDestino");
        $tipoDocumentoFilter = UtilService::getValueToMap($mapFilters, "tipoDocumento");
        $estadoFilter = UtilService::getValueToMap($mapFilters, "estado");
        $revendidoEnCaminoFilter = UtilService::getValueToMap($mapFilters, "revendidoEnCamino");
        $reasignadoFilter = UtilService::getValueToMap($mapFilters, "reasignado");
        $serieFacturaFilter = UtilService::getValueToMap($mapFilters, "serieFactura");
        $numeroFacturaFilter = UtilService::getValueToMap($mapFilters, "numeroFactura");
        $salidaFilter = UtilService::getValueToMap($mapFilters, "salida");
        $referenciaExternaFilter = UtilService::getValueToMap($mapFilters, "referenciaExterna");
        $autorizacionTarjetaFilter = UtilService::getValueToMap($mapFilters, "autorizacionTarjeta");
//        $pendienteFilter = UtilService::getValueToMap($mapFilters, "pendiente");

        $queryStr =   " FROM Acme\TerminalOmnibusBundle\Entity\Boleto b"
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN b.tipoDocumento td "
                    . " LEFT JOIN b.estacionOrigen eo "
                    . " LEFT JOIN b.estacionDestino ed "
                    . " LEFT JOIN b.estacionCreacion ec "
                    . " LEFT JOIN s.itinerario i "
                    . " LEFT JOIN i.ruta ru "
                    . " LEFT JOIN b.clienteBoleto cb "
                    . " LEFT JOIN b.clienteDocumento cd "
                    . " LEFT JOIN b.estado e "
                    . " LEFT JOIN b.asientoBus ab "
                    . " LEFT JOIN ab.clase abc "
                    . " LEFT JOIN b.facturaGenerada fg "
                    . " LEFT JOIN fg.factura f "
                    . " WHERE "
                    . " ((s.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter) or "
                    . "  (b.fechaCreacion BETWEEN :fechaInitFilter AND :fechaEndFilter)) ";
        
        if($agencia === true){
            $queryStr .= " and (ec.id = :estacionAgencia) ";
        }
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.id" ,"identificadorFilter", $identificadorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "ab.numero" ,"numeroAsientoFilter", $numeroAsientoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "abc.nombre" ,"claseAsientoFilter", $claseAsientoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("ru.codigo", "ru.nombre") ,"rutaFilter", $rutaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "cb.id" ,"clienteBoletoFilter", $clienteBoletoFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "cd.id" ,"clienteDocumentoFilter", $clienteDocumentoFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("eo.alias", "eo.nombre") ,"estacionOrigenFilter", $estacionOrigenFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("ed.alias", "ed.nombre") ,"estacionDestinoFilter", $estacionDestinoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "td.nombre" ,"tipoDocumentoFilter", $tipoDocumentoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.nombre" ,"estadoFilter", $estadoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.revendidoEnCamino" ,"revendidoEnCaminoFilter", $revendidoEnCaminoFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.reasignado" ,"reasignadoFilter", $reasignadoFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "f.serieResolucionFactura" ,"serieFacturaFilter", $serieFacturaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "fg.consecutivo" ,"numeroFacturaFilter", $numeroFacturaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "fg.referenciaExterna", "referenciaExternaFilter", $referenciaExternaFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "fg.autorizacionTarjeta","autorizacionTarjetaFilter", $autorizacionTarjetaFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "s.id" ,"salidaFilter", $salidaFilter, false);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
//            $queryOrder = " s.fecha DESC ";
            $queryOrder = " b.id DESC ";
        }
        
        $query = $this->_em->createQuery(" SELECT b " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "numeroAsientoFilter", $numeroAsientoFilter);
        UtilService::setParameterToQuery($query, "claseAsientoFilter", $claseAsientoFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter);
        UtilService::setParameterToQuery($query, "clienteBoletoFilter", $clienteBoletoFilter, false);
        UtilService::setParameterToQuery($query, "clienteDocumentoFilter", $clienteDocumentoFilter, false);
        UtilService::setParameterToQuery($query, "estacionOrigenFilter", $estacionOrigenFilter);
        UtilService::setParameterToQuery($query, "estacionDestinoFilter", $estacionDestinoFilter);
        UtilService::setParameterToQuery($query, "tipoDocumentoFilter", $tipoDocumentoFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "revendidoEnCaminoFilter", $revendidoEnCaminoFilter, false);
        UtilService::setParameterToQuery($query, "reasignadoFilter", $reasignadoFilter, false);
        UtilService::setParameterToQuery($query, "serieFacturaFilter", $serieFacturaFilter);
        UtilService::setParameterToQuery($query, "numeroFacturaFilter", $numeroFacturaFilter);
        UtilService::setParameterToQuery($query, "referenciaExternaFilter", $referenciaExternaFilter, false);
        UtilService::setParameterToQuery($query, "autorizacionTarjetaFilter", $autorizacionTarjetaFilter, false);
        UtilService::setParameterToQuery($query, "salidaFilter", $salidaFilter, false);
        if($agencia === true){
            UtilService::setParameterToQuery($query, "estacionAgencia", $idAgencia, false);
        }
        
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(b) " .$queryStr);
        UtilService::setParameterToQuery($query, "fechaInitFilter", $fechaInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaEndFilter", $fechaEndFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "numeroAsientoFilter", $numeroAsientoFilter);
        UtilService::setParameterToQuery($query, "claseAsientoFilter", $claseAsientoFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter);       
        UtilService::setParameterToQuery($query, "clienteBoletoFilter", $clienteBoletoFilter, false);
        UtilService::setParameterToQuery($query, "clienteDocumentoFilter", $clienteDocumentoFilter, false);
        UtilService::setParameterToQuery($query, "estacionOrigenFilter", $estacionOrigenFilter);
        UtilService::setParameterToQuery($query, "estacionDestinoFilter", $estacionDestinoFilter);
        UtilService::setParameterToQuery($query, "tipoDocumentoFilter", $tipoDocumentoFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "revendidoEnCaminoFilter", $revendidoEnCaminoFilter, false);
        UtilService::setParameterToQuery($query, "reasignadoFilter", $reasignadoFilter, false);  
        UtilService::setParameterToQuery($query, "serieFacturaFilter", $serieFacturaFilter);
        UtilService::setParameterToQuery($query, "numeroFacturaFilter", $numeroFacturaFilter); 
        UtilService::setParameterToQuery($query, "referenciaExternaFilter", $referenciaExternaFilter, false);
        UtilService::setParameterToQuery($query, "autorizacionTarjetaFilter", $autorizacionTarjetaFilter, false);
        UtilService::setParameterToQuery($query, "salidaFilter", $salidaFilter, false);
        if($agencia === true){
            UtilService::setParameterToQuery($query, "estacionAgencia", $idAgencia, false);
        }
        
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }    
    
    public function totalBoletosBySalida($idSalida)
    {
        $query =  " SELECT COUNT(b) FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " LEFT JOIN b.salida s "
                . " WHERE "
                . " s.id = :idSalida ";
            
        $cantidad = $this->_em->createQuery($query)
                    ->setParameter('idSalida', $idSalida)
                    ->getSingleScalarResult();
        return $cantidad;         
    }
    
    public function getBoletosByIdentificadorWeb($key)
    {
        $fechaCreacionInitFilter = new \DateTime();
        $fechaCreacionInitFilter->modify("-2 day");
	$fechaCreacionInitFilter->setTime(0, 0, 0);
        $fechaCreacionInitFilter = $fechaCreacionInitFilter->format('d-m-Y H:i:s');
        
	$fechaCreacionEndFilter = new \DateTime();
        $fechaCreacionEndFilter->modify("+2 day");
        $fechaCreacionEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaCreacionEndFilter = $fechaCreacionEndFilter->format('d-m-Y H:i:s');
        
        $query  = " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " WHERE "
                . " b.fechaCreacion BETWEEN :fechaCreacionInitFilter AND :fechaCreacionEndFilter "
                . " and b.identificadorWeb = :key " ;
        $items = $this->_em->createQuery($query)
                  ->setParameter('key', $key)
                  ->setParameter('fechaCreacionInitFilter', $fechaCreacionInitFilter)
                  ->setParameter('fechaCreacionEndFilter', $fechaCreacionEndFilter)
                  ->getResult();
        return $items; 
    }
    
    //Se utiliza para en combobox en registrar encomienda
    public function listarBoletosPaginando($user, $pageLimit = null, $term = null, $id = null)
    {
        $estacionUsuario = null;
        if($user instanceof User){
            $estacionUsuario = $user->getEstacion();
        }
        
        if($pageLimit === null || (is_string($pageLimit) && trim($pageLimit) === "")){
            $pageLimit = 20;
        }
        $pageLimit = intval($pageLimit);
        if($pageLimit > 50){
            $pageLimit = 50;
        }
        
        $queryStr =  " SELECT partial b.{id, clienteBoleto} FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                   . " LEFT JOIN b.estado e "
                   . " LEFT JOIN b.clienteBoleto cb "
                   . " WHERE e.id IN (1,2) "; //Emitidos y Chequeados
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.estacionOrigen" ,"estacionOrigen", $estacionUsuario, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.id" ,"id", $id, false);
        if($term !== null && trim($term) !== ""){
           $queryStr .= (UtilService::contains($queryStr, "where") ? " and " : " where ") . " (b.id=:idLike) ";
        }
        $query = $this->_em->createQuery($queryStr);

        UtilService::setParameterToQuery($query, "estacionOrigen", $estacionUsuario, false);
        UtilService::setParameterToQuery($query, "id", $id, false); 

        if($term !== null && trim($term) !== ""){
           $query->setParameter('idLike', $term);
        }
        
        $query->setMaxResults($pageLimit);
        return $query->getResult();
    }
    
    //Se utiliza para cambiarle el asiento cuando se le hace un reajuste a la salida.
    //Se utiliza para enviar el reporte de boletos asociados a itinerarios especiales
    public function getBoletosPorSalida($salida, $camino = false)
    {
        if($salida instanceof Salida){
            $salida = $salida->getId();
        }
        
        $query =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " LEFT JOIN b.salida s ";
        if($camino === false){
            $query .= " INNER JOIN b.asientoBus ab ";   
        }
        $query .= " WHERE s.id = :idSalida ";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $salida)
                ->getResult();
        return $items;
    }
    
    //Se utiliza para el consultar de asiento de la salida
    public function getBoletosActivosPorSalidaNroAsiento($idSalida, $nroAsiento)
    {
        $query =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " left join b.asientoBus ab "
                . " left join b.salida s "
                . " WHERE s.id = :idSalida "
                . " and ab.numero= :nroAsiento"
                . " and (b.estado=1 or b.estado=2 or b.estado=3)";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->setParameter('nroAsiento', $nroAsiento)
                ->getResult();
        
        return $items;
    }
    
    public function getDatosParcialesBoletosActivosPorSalida($idSalida)
    {
        $query =  " SELECT "
                . " partial b.{id,revendidoEnEstacion,revendidoEnCamino} ,"
                . " partial td.{id} ,"
                . " partial ab.{id, numero}, "
                . " partial c.{id, nombre} "
                . " FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " INNER JOIN  b.asientoBus ab "
                . " LEFT JOIN  ab.clase c "
                . " LEFT JOIN  b.tipoDocumento td "
                . " join b.salida s "
                . " WHERE s.id = :idSalida and (b.estado=1 or b.estado=2 or b.estado=3)";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function listarBoletosActivosBySalida($idSalida)
    {
        $query =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " join b.salida s"
                . " WHERE s.id = :idSalida and (b.estado=1 or b.estado=2 or b.estado=3)";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }

    public function listarBoletosChequeadosBySalida($idSalida)
    {
        $query =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " LEFT JOIN b.asientoBus ab "
                . " LEFT JOIN ab.tipoBus tbb "
                . " LEFT JOIN b.salida s "
                . " LEFT JOIN s.tipoBus tbs "
                . " WHERE "
                . " s.id = :idSalida "
                . " and (tbb=tbs or b.asientoBus is null) "
                . " and b.estado=2 ";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function listarBoletosChequeadosTransitoBySalida($idSalida)
    {
        $query =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " LEFT JOIN b.asientoBus ab "
                . " LEFT JOIN ab.tipoBus tbb "
                . " LEFT JOIN b.salida s "
                . " LEFT JOIN s.tipoBus tbs "
                . " WHERE "
                . " s.id = :idSalida "
                . " and (tbb=tbs or b.asientoBus is null) "
                . " and b.estado IN (2,3) ";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function totalBoletosChequeadosBySalida($idSalida)
    {
        try {
            $query =  " SELECT COUNT(b) FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.asientoBus ab "
                    . " LEFT JOIN ab.tipoBus tbb "
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN s.tipoBus tbs "
                    . " WHERE "
                    . " s.id = :idSalida "
                    . " and (tbb=tbs or b.asientoBus is null) "
                    . " and b.estado=2 ";
            $cantidad = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('idSalida', $idSalida)
                    ->getSingleResult();
            return $cantidad[1];
         } catch (NoResultException $exc) {
            return 0;
         }
    }
    
    public function listarBoletosEmitidosBySalida($idSalida)
    {
        $query =      " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.estacionOrigen beo "
                    . " LEFT JOIN b.asientoBus ab "
                    . " LEFT JOIN ab.tipoBus tbb "
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN s.tipoBus tbs "
                    . " LEFT JOIN s.itinerario i "
                    . " LEFT JOIN i.ruta r "
                    . " LEFT JOIN r.estacionOrigen reo "
                    . " WHERE "
                    . " s.id = :idSalida "
                    . " and b.estado=1 "
                    . " and (tbb=tbs or b.asientoBus is null) "
                    . " and beo.id = reo.id ";
        
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function totalBoletosEmitidosBySalida($idSalida)
    {
         try {
            $query =  " SELECT COUNT(b) FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.estacionOrigen beo "
                    . " LEFT JOIN b.asientoBus ab "
                    . " LEFT JOIN ab.tipoBus tbb "
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN s.tipoBus tbs "
                    . " LEFT JOIN s.itinerario i "
                    . " LEFT JOIN i.ruta r "
                    . " LEFT JOIN r.estacionOrigen reo "
                    . " WHERE "
                    . " s.id = :idSalida "
                    . " and b.estado=1 "
                    . " and (tbb=tbs or b.asientoBus is null) "
                    . " and beo.id = reo.id ";
            $cantidad = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('idSalida', $idSalida)
                    ->getSingleResult();
            return $cantidad[1];
         } catch (NoResultException $exc) {
            return 0;
         }
    }
    
    public function listarBoletosEmitidosPendientesBySalida($idSalida)
    {
        $query =      " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.estacionOrigen beo "
                    . " LEFT JOIN b.asientoBus ab "
                    . " LEFT JOIN ab.tipoBus tbb "
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN s.tipoBus tbs "
                    . " LEFT JOIN s.itinerario i "
                    . " LEFT JOIN i.ruta r "
                    . " LEFT JOIN r.estacionOrigen reo "
                    . " WHERE "
                    . " s.id = :idSalida "
                    . " and b.estado=1 "
                    . " and (tbb=tbs or b.asientoBus is null) "
                    . " and beo.id <> reo.id ";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    /*
      PARA E CHEQUEAR BOLETO
    */
    public function listarBoletosEmitidosChequeadosTransitoBySalida($idSalida)
    {
        $query =      " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN b.asientoBus ab "
                    . " WHERE "
                    . " s.id = :idSalida "
                    . " and (b.estado=1 OR b.estado=2 OR b.estado=3) "
                    . " order by ab.numero ASC ";
        $items = $this->_em->createQuery($query)
                ->setParameter('idSalida', $idSalida)
                ->getResult();
        return $items;
    }
    
    public function totalBoletosEmitidosPendientesBySalida($idSalida)
    {
         try {
            $query =  " SELECT COUNT(b) FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " LEFT JOIN b.estacionOrigen beo "
                    . " LEFT JOIN b.asientoBus ab "
                    . " LEFT JOIN ab.tipoBus tbb "
                    . " LEFT JOIN b.salida s "
                    . " LEFT JOIN s.tipoBus tbs "
                    . " LEFT JOIN s.itinerario i "
                    . " LEFT JOIN i.ruta r "
                    . " LEFT JOIN r.estacionOrigen reo "
                    . " WHERE "
                    . " s.id = :idSalida "
                    . " and b.estado=1 "
                    . " and (tbb=tbs or b.asientoBus is null) "
                    . " and beo.id <> reo.id ";
            $cantidad = $this->_em->createQuery($query)
                    ->setMaxResults(1)
                    ->setParameter('idSalida', $idSalida)
                    ->getSingleResult();
            return $cantidad[1];
         } catch (NoResultException $exc) {
            return 0;
         }
    }
    
    public function listarDetalleBoletosBySalida($idSalida)
    { 
        $query = "
WITH CTE_TOTALES(id, reasignado_id) 
AS ( 
SELECT b1.id, b1.reasignado_id 
FROM boleto b1 
LEFT JOIN salida as s1 on s1.id = b1.salida_id 
WHERE (s1.id = :idSalida and b1.estado_id <> 5) 
UNION ALL
SELECT b2.id, b2.reasignado_id 
FROM boleto b2 
INNER JOIN CTE_TOTALES as cte on b2.id = cte.reasignado_id                                                             
)

SELECT
estacionCreacion.nombre as nombreEstacionCreacion,
SUM((case
 when ((b3.tipo_documento_id = 1 or b3.tipo_documento_id = 2 or b3.tipo_documento_id = 4) and (b3.estado_id <> 4 and b3.estado_id <> 5))
  then 1
  else 0
end)) AS cantidadBolFacturadoEstacion,
SUM((case
 when ((b3.tipo_documento_id = 1 or b3.tipo_documento_id = 2 or b3.tipo_documento_id = 4) and (b3.estado_id = 4))
  then 1
  else 0
end)) AS cantidadBolAnuladosEstacion,
SUM((case
 when ((b3.tipo_documento_id = 1 or b3.tipo_documento_id = 2 or b3.tipo_documento_id = 4) and (b3.estado_id <> 4))
  then b3.precioCalculadoMonedaBase
  else 0
end)) AS importeBolFacturadoEstacion,
SUM((case
 when ((b3.tipo_documento_id = 6 or b3.tipo_documento_id = 7) and (b3.estado_id <> 4 and b3.estado_id <> 5))
  then 1
  else 0
end)) AS cantidadBolVoucherEstacion,
SUM((case
 when ((b3.tipo_documento_id = 6 or b3.tipo_documento_id = 7) and (b3.estado_id <> 4))
  then b3.precioCalculadoMonedaBase
  else 0
 end)) AS importeBolVoucherEstacion,
SUM((case
 when (b3.tipo_documento_id = 5 and voucher_agencia.bono = 0 and b3.estado_id <> 4)
  then 1
  else 0
end)) AS cantidadBolVoucherAgencia,
SUM((case
 when (b3.tipo_documento_id = 5 and voucher_agencia.bono = 0 and b3.estado_id <> 4)
  then b3.precioCalculadoMonedaBase
  else 0
end)) AS importeBolVoucherAgencia,
SUM((case
 when (b3.tipo_documento_id = 5 and voucher_agencia.bono = 1 and b3.estado_id <> 4)
  then 1
  else 0
end)) AS cantidadBolBonoAgencia,
SUM((case
 when (b3.tipo_documento_id = 5 and voucher_agencia.bono = 1 and b3.estado_id <> 4)
  then b3.precioCalculadoMonedaBase
  else 0
end)) AS importeBolBonoAgencia,
SUM((case
 when (b3.tipo_documento_id = 3 and b3.estado_id <> 6)
  then 1
  else 0
end)) AS cantidadBolCortesias
FROM boleto b3
INNER JOIN boleto_estado estado ON b3.estado_id = estado.id
INNER JOIN estacion estacionCreacion on estacionCreacion.id = b3.estacion_creacion_id
LEFT JOIN factura_generada factura_generada ON factura_generada.id = b3.factura_generada_id
LEFT JOIN boleto_voucher_agencia voucher_agencia ON voucher_agencia.id = b3.voucher_agencia_id
LEFT JOIN boleto_voucher_estacion voucher_estacion ON voucher_estacion.id = b3.voucher_estacion_id
WHERE b3.id IN (SELECT id FROM CTE_TOTALES )
GROUP BY
estacionCreacion.nombre
ORDER BY
estacionCreacion.nombre
                ";
          
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult('nombreEstacionCreacion', 'nombreEstacionCreacion');
            $rsm->addScalarResult('cantidadBolFacturadoEstacion', 'cantidadBolFacturadoEstacion');
            $rsm->addScalarResult('cantidadBolAnuladosEstacion', 'cantidadBolAnuladosEstacion');
            $rsm->addScalarResult('importeBolFacturadoEstacion', 'importeBolFacturadoEstacion');
            $rsm->addScalarResult('cantidadBolVoucherEstacion', 'cantidadBolVoucherEstacion');
            $rsm->addScalarResult('importeBolVoucherEstacion', 'importeBolVoucherEstacion');
            $rsm->addScalarResult('cantidadBolVoucherAgencia', 'cantidadBolVoucherAgencia');
            $rsm->addScalarResult('importeBolVoucherAgencia', 'importeBolVoucherAgencia');
            $rsm->addScalarResult('cantidadBolBonoAgencia', 'cantidadBolBonoAgencia');
            $rsm->addScalarResult('importeBolBonoAgencia', 'importeBolBonoAgencia');
            $rsm->addScalarResult('cantidadBolCortesias', 'cantidadBolCortesias');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idSalida', $idSalida)
                    ->getArrayResult();
            return $items;
    }
    
    //Se utiliza para el consultar de asiento de la salida
    public function getBoletoConClientesRepetidosPorSalida($idSalida, $idClientes, $omitirIdBoletos = array())
    {
        $queryStr =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                . " LEFT JOIN b.clienteBoleto cb "
                . " LEFT JOIN b.salida s "
                . " WHERE "
                . " s.id = :idSalida "
                . " and cb.id IN (:idClientes) "
                . " and (b.estado=1 or b.estado=2) ";
        
        if(count($omitirIdBoletos) != 0){
            $queryStr .=  " and b.id NOT IN (:omitirBoletos) ";
        }
        
        $query = $this->_em->createQuery($queryStr)
                ->setParameter('idSalida', $idSalida)
                ->setParameter('idClientes', $idClientes);
        
        if(count($omitirIdBoletos) != 0){
           $query ->setParameter('omitirBoletos', $omitirIdBoletos);
        }
        
        $items = $query->getResult();
        return $items;
    }
    
    public function getBoletoByFactura($serie, $consecutivo)
    {
        try {
            $queryStr =  " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " INNER JOIN b.facturaGenerada fg "
                    . " INNER JOIN fg.servicioEstacion sefg "
                    . " INNER JOIN fg.factura f "
                    . " INNER JOIN f.servicioEstacion sef "
                    . " WHERE "
                    . " fg.consecutivo = :consecutivo "
                    . " and f.serieResolucionFactura = :serie "
                    . " and sef.id=1 and sefg.id=1 "; //que los servicios de estacion sea por el concepto de boleto.

            $query = $this->_em->createQuery($queryStr)
                    ->setMaxResults(1)
                    ->setParameter('serie', $serie)
                    ->setParameter('consecutivo', $consecutivo);
            $item = $query->getSingleResult();
            return $item;
        } catch (NoResultException $exc) {
            return null;
        }
    }

    public function listarCortesiaPorEstacion(\DateTime $fechaDia, $empresa)
    {
        $fechaInitFilter = clone $fechaDia;
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = clone $fechaDia;
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $query =  " 
                        SELECT  
                        estacion.id AS idEstacionCreacion, 
                        estacion.nombre AS nombreEstacionCreacion,
                        FORMAT(boleto.fecha_creacion, 'dd/MM/yyyy') AS fechaCreacion,
                        usuarioAutorizacion.names + ' ' + usuarioAutorizacion.surnames as usuarioAutorizacion,
                        salida.fecha as fechaViaje,
                        ruta.nombre as ruta,
                        cliente.nombre as nombreCliente,
                        autorizacionCortesia.motivo as motivo
                        FROM boleto boleto 
                        INNER JOIN cliente cliente ON cliente.id = boleto.cliente_boleto 
                        INNER JOIN autorizacion_cortesia autorizacionCortesia ON autorizacionCortesia.id = boleto.autorizacion_cortesia_id
                        INNER JOIN custom_user usuarioAutorizacion ON usuarioAutorizacion.id = autorizacionCortesia.usuario_creacion
                        LEFT JOIN salida salida ON salida.id = boleto.salida_id
                        LEFT JOIN itineario itineario ON itineario.id = salida.itinerario_id
                        LEFT JOIN ruta ruta ON ruta.codigo = itineario.ruta_codigo
                        LEFT JOIN empresa empresa ON salida.empresa_id = empresa.id
                        LEFT JOIN estacion estacion ON estacion.id = boleto.estacion_creacion_id
                        WHERE
                        boleto.tipo_documento_id=3                        
                        and empresa.id = :idEmpresa 
                        and boleto.fecha_creacion BETWEEN :fechaInitFilter AND :fechaEndFilter
                        ORDER BY
                        fechaViaje,
                        usuarioAutorizacion
                      ";
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult('idEstacionCreacion', 'idEstacionCreacion');
            $rsm->addScalarResult('nombreEstacionCreacion', 'nombreEstacionCreacion');
            $rsm->addScalarResult('usuarioAutorizacion', 'usuarioAutorizacion');
            $rsm->addScalarResult('nombreCliente', 'nombreCliente');
            $rsm->addScalarResult('fechaViaje', 'fechaViaje');
            $rsm->addScalarResult('ruta', 'ruta');
            $rsm->addScalarResult('motivo', 'motivo');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->setParameter('idEmpresa', $empresa)
                    ->getArrayResult();
            return $items;
    }
    
    public function listarBoletosFacturadosConTarjeta(\DateTime $fechaDia, $empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaInitFilter = clone $fechaDia;
        $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = clone $fechaDia;
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =      " SELECT b FROM Acme\TerminalOmnibusBundle\Entity\Boleto b "
                    . " INNER JOIN b.facturaGenerada fg "
                    . " INNER JOIN fg.estacion es "
                    . " INNER JOIN fg.factura fa "
                    . " INNER JOIN fa.empresa em "
                    . " INNER JOIN b.tipoPago tp "
                    . " WHERE "
                    . " em.id = :idEmpresa "
                    . " and fg.fecha BETWEEN :fechaInitFilter AND :fechaEndFilter  "
                    . " and tp.id IN (2, 3, 4) "
                    . " ORDER BY "
                    . " es.id ASC  ";
        
        $items = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->setParameter('idEmpresa', $empresa)
                    ->getResult();
        return $items;
    }
    
    public function getLastBoletoReasignado(Boleto $boleto)
    {
        try {
            if($boleto instanceof Boleto){
                $boleto = $boleto->getId();
            }

            $query =  " SELECT 
                        TOP 1 b.id as id
                        FROM boleto b
                        WHERE
                        b.id = dbo.getUltimoAjuste(:id)
                      ";

            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult("id", "id");
            $query = $this->_em->createNativeQuery($query, $rsm);
            $query->setParameter('id', $boleto);
            $item = $query->getSingleResult();
            $id = $item["id"];
            //Se recarga pq se trae solamente el ID del boleto
            $item = $this->_em->find("Acme\TerminalOmnibusBundle\Entity\Boleto", $id);
            return $item;
            
        } catch (NoResultException $exc) {
            return $boleto;
         }
        
    }
    
    public function listarBoletosFacturado($estacion, $empresa, $fecha)
    {
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaInitFilter = clone $fecha;
        $fechaInitFilter = $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
              
        $fechaEndFilter = clone $fecha;
        $fechaEndFilter = $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =  " SELECT bo from Acme\TerminalOmnibusBundle\Entity\Boleto bo "
                    . " INNER JOIN bo.facturaGenerada fg "
                    . " INNER JOIN fg.factura fa "
                    . " INNER JOIN fa.empresa em "
                    . " INNER JOIN fg.estacion es "
                    . " WHERE "
                    . " es.id = :estacion "
                    . " and em.id = :empresa "
                    . " and fg.fechaCreacion between :fechaInitFilter and :fechaEndFilter  ";
            
        $boletos = $this->_em->createQuery($query)
                    ->setParameter('estacion', $estacion)
                    ->setParameter('empresa', $empresa)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        
        return $boletos;
    }
    
    public function listarBoletosPendientes($fechaEnd)
    {
        $fechaInitFilter = clone $fechaEnd;
        $fechaInitFilter->modify("-1 year");
        $fechaInitFilter = $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
              
        $fechaEndFilter = clone $fechaEnd;
        $fechaEndFilter = $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =  " SELECT bo from Acme\TerminalOmnibusBundle\Entity\Boleto bo "
                    . " INNER JOIN bo.salida sa "
                    . " INNER JOIN sa.estado es "
                    . " INNER JOIN bo.estado eb "
                    . " WHERE "
                    . " sa.fecha between :fechaInitFilter and :fechaEndFilter "
                    . " and es.id IN (3,5) "       //INICIADA, FINALIZADA
                    . " and eb.id IN (1,2) ";      //EMITIDO, CHEQUEADO
            
        $boletos = $this->_em->createQuery($query)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        return $boletos;
    }
    
    /*
        PARA REPORTAR PARCIALES Y TOTALES
    */
    public function listarTotalesBoletos($fecha, $empresa, $estaciones = array())
    {
        $idEstaciones = array();
        foreach ($estaciones as $estacion) {
            if($estacion instanceof Estacion){
                $idEstaciones[] = $estacion->getId();
            }else{
                $idEstaciones[] = $estacion;
            }
        }
        
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $fechaInitFilter = clone $fecha;
        $fechaInitFilter = $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
              
        $fechaEndFilter = clone $fecha;
        $fechaEndFilter = $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query = "
                    SELECT
                    estacion.id AS idEstacionCreacion, 
                    estacion.nombre AS nombreEstacionCreacion,
                    empresa.id AS idEmpresa, 
                    empresa.alias AS nombreEmpresa,
                    SUM((case
                        when ((boleto.tipo_documento_id = 1 or boleto.tipo_documento_id = 2 or boleto.tipo_documento_id = 4) and boleto.estado_id <> 4)
                            then 1
                            else 0
                    end)) AS cantidadBolFacturadoEstacion,
                    SUM((case
                        when ((boleto.tipo_documento_id = 1 or boleto.tipo_documento_id = 2 or boleto.tipo_documento_id = 4) and boleto.estado_id = 4)
                            then 1
                            else 0
                    end)) AS cantidadBolAnuladosEstacion,
                    SUM((case
                        when ((boleto.tipo_documento_id = 1 or boleto.tipo_documento_id = 2 or boleto.tipo_documento_id = 4) and boleto.estado_id <> 4)
                            then boleto.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeBolFacturadoEstacion,
                    SUM((case
                        when (boleto.tipo_documento_id = 6 or boleto.tipo_documento_id = 7)
                            then 1
                            else 0
                    end)) AS cantidadBolVoucherEstacion,
                    SUM((case
                        when (boleto.tipo_documento_id = 6 or boleto.tipo_documento_id = 7)
                            then boleto.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeBolVoucherEstacion,
                    SUM((case
                        when (boleto.tipo_documento_id = 5 and voucher_agencia.bono = 0 and boleto.estado_id <> 4)
                            then 1
                            else 0
                    end)) AS cantidadBolVoucherAgencia,
                    SUM((case
                        when (boleto.tipo_documento_id = 5 and voucher_agencia.bono = 0 and boleto.estado_id <> 4)
                            then boleto.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeBolVoucherAgencia,
                    SUM((case
                        when (boleto.tipo_documento_id = 5 and voucher_agencia.bono = 1 and boleto.estado_id <> 4)
                            then 1
                            else 0
                    end)) AS cantidadBolBonoAgencia,
                    SUM((case
                        when (boleto.tipo_documento_id = 5 and voucher_agencia.bono = 1 and boleto.estado_id <> 4)
                            then boleto.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeBolBonoAgencia,
                    SUM((case
                        when (boleto.tipo_documento_id = 3 and boleto.estado_id <> 6)
                            then 1
                            else 0
                    end)) AS cantidadBolCortesias
                    FROM boleto boleto 
                    INNER JOIN salida AS salida ON salida.id = boleto.salida_id
                    INNER JOIN empresa empresa ON salida.empresa_id = empresa.id
                    INNER JOIN estacion estacion ON boleto.estacion_creacion_id = estacion.id
                    INNER JOIN boleto_estado estado ON boleto.estado_id = estado.id
                    LEFT JOIN factura_generada factura_generada ON factura_generada.id = boleto.factura_generada_id
                    LEFT JOIN boleto_voucher_agencia voucher_agencia ON voucher_agencia.id = boleto.voucher_agencia_id
                    LEFT JOIN boleto_voucher_estacion voucher_estacion ON voucher_estacion.id = boleto.voucher_estacion_id
                    WHERE
                    estacion.id IN ( :idEstaciones )
                    and empresa.id = :empresa
                    and boleto.fecha_creacion between :fechaInitFilter and :fechaEndFilter 
                    GROUP BY
                    estacion.id, 
                    estacion.nombre,
                    empresa.id, 
                    empresa.alias
                    ORDER BY
                    idEstacionCreacion
                 ";
        
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult('idEstacionCreacion', 'idEstacionCreacion');
            $rsm->addScalarResult('nombreEstacionCreacion', 'nombreEstacionCreacion');
            $rsm->addScalarResult('idEmpresa', 'idEmpresa');
            $rsm->addScalarResult('nombreEmpresa', 'nombreEmpresa');
            $rsm->addScalarResult('cantidadBolFacturadoEstacion', 'cantidadBolFacturadoEstacion');
            $rsm->addScalarResult('cantidadBolAnuladosEstacion', 'cantidadBolAnuladosEstacion');
            $rsm->addScalarResult('importeBolFacturadoEstacion', 'importeBolFacturadoEstacion');
            $rsm->addScalarResult('cantidadBolVoucherEstacion', 'cantidadBolVoucherEstacion');
            $rsm->addScalarResult('importeBolVoucherEstacion', 'importeBolVoucherEstacion');
            $rsm->addScalarResult('cantidadBolVoucherAgencia', 'cantidadBolVoucherAgencia');
            $rsm->addScalarResult('importeBolVoucherAgencia', 'importeBolVoucherAgencia');
            $rsm->addScalarResult('cantidadBolBonoAgencia', 'cantidadBolBonoAgencia');
            $rsm->addScalarResult('importeBolBonoAgencia', 'importeBolBonoAgencia');
            $rsm->addScalarResult('cantidadBolCortesias', 'cantidadBolCortesias');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idEstaciones', $idEstaciones)
                    ->setParameter('empresa', $empresa)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getArrayResult();
            return $items;
    }
    
//    public function listarTotalFacturadoPorEstacion(\DateTime $fechaDia, $empresa)
//    {
//        if($empresa instanceof Empresa){
//            $empresa = $empresa->getId();
//        }
//        
//        $query =  " 
//                        ---------------------------------------------------------------------------------------
//                        WITH BoletoCTE (idBoleto, idReasignado, idEstacionCreacion, nombreEstacionCreacion, fechaCreacion, fechaSalida, siglaMoneda) AS (
//                        SELECT
//                        boleto1.id AS idBoleto,
//                        boleto1.reasignado_id AS idReasignado,
//                        estacion1.id AS idEstacionCreacion, 
//                        estacion1.nombre AS nombreEstacionCreacion,
//                        FORMAT(boleto1.fecha_creacion, 'dd/MM/yyyy') AS fechaCreacion,
//                        FORMAT(salida1.fecha, 'dd/MM/yyyy') AS fechaSalida,
//                        moneda1.sigla AS siglaMoneda
//                        FROM boleto AS boleto1
//                        LEFT JOIN salida AS salida1 ON salida1.id = boleto1.salida_id
//                        LEFT JOIN empresa empresa1 ON salida1.empresa_id = empresa1.id
//                        LEFT JOIN estacion estacion1 ON boleto1.estacion_creacion_id = estacion1.id
//                        LEFT JOIN factura_generada factura_generada1 ON boleto1.factura_generada_id = factura_generada1.id
//                        LEFT JOIN moneda moneda1 ON factura_generada1.moneda_id = moneda1.id
//                        WHERE (
//                        boleto1.estado_id <> 5
//                        and empresa1.id = :idEmpresa 
//                        and FORMAT(boleto1.fecha_creacion,'dd/MM/yyyy') = :fecha
//                        )
//                        UNION ALL
//                        SELECT
//                        boleto2.id AS idBoleto,
//                        boleto2.reasignado_id AS idReasignado,
//                        boletoCTE.idEstacionCreacion AS idEstacionCreacion,
//                        boletoCTE.nombreEstacionCreacion AS nombreEstacionCreacion,
//                        boletoCTE.fechaCreacion AS fechaCreacion,
//                        boletoCTE.fechaSalida AS fechaSalida,
//                        boletoCTE.siglaMoneda AS siglaMoneda
//                        FROM boleto AS boleto2
//                        INNER JOIN BoletoCTE AS boletoCTE ON boletoCTE.idReasignado = boleto2.id
//                        WHERE (
//                        FORMAT(boleto2.fecha_creacion,'dd/MM/yyyy') = :fecha
//                        )
//                        )
//                        ---------------------------------------------------------------------------------------
//                        SELECT  
//                        boletoCTE.idEstacionCreacion AS idEstacionCreacion, 
//                        boletoCTE.nombreEstacionCreacion AS nombreEstacionCreacion, 
//                        (case
//                                when (moneda.id is null)
//                            then boletoCTE.siglaMoneda
//                            else moneda.sigla
//                        end) AS siglaMoneda,
//                        SUM((case
//                                when (fechaCreacion = fechaSalida)
//                            then facturaGenerada.importeTotal
//                            else 0
//                        end)) AS importeFacturadoDia,
//                        SUM((case
//                                when (fechaCreacion <> fechaSalida)
//                            then facturaGenerada.importeTotal
//                            else 0
//                        end)) AS importeFacturadoPrepago,
//                        SUM(facturaGenerada.importeTotal) AS importeFacturadoTotal,
//                        SUM((case
//                                when (estado.id = 4)
//                            then boleto.precioCalculado
//                            else 0
//                        end)) AS importeAnulado,
//                        SUM((case
//                                when (estado.id = 4)
//                            then 1
//                            else 0
//                        end)) AS cantidadAnulados,
//                        COUNT(boleto.id) AS cantidadEmitidos
//                        FROM boleto boleto 
//                        INNER JOIN BoletoCTE boletoCTE ON boletoCTE.idBoleto = boleto.id
//                        INNER JOIN boleto_estado estado ON boleto.estado_id = estado.id
//                        INNER JOIN factura_generada facturaGenerada ON boleto.factura_generada_id = facturaGenerada.id
//                        INNER JOIN moneda moneda ON facturaGenerada.moneda_id = moneda.id
//                        GROUP BY
//                        boletoCTE.idEstacionCreacion, 
//                        boletoCTE.nombreEstacionCreacion,
//                        (case
//                                when (moneda.id is null)
//                            then boletoCTE.siglaMoneda
//                            else moneda.sigla
//                        end)
//                        ORDER BY
//                        idEstacionCreacion,
//                        nombreEstacionCreacion,
//                        siglaMoneda
//                      ";
//            
//            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
//            $rsm->addScalarResult('idEstacionCreacion', 'idEstacionCreacion');
//            $rsm->addScalarResult('nombreEstacionCreacion', 'nombreEstacionCreacion');
//            $rsm->addScalarResult('siglaMoneda', 'siglaMoneda');
//            $rsm->addScalarResult('importeFacturadoDia', 'importeFacturadoDia');
//            $rsm->addScalarResult('importeFacturadoPrepago', 'importeFacturadoPrepago');
//            $rsm->addScalarResult('importeFacturadoTotal', 'importeFacturadoTotal');
//            $rsm->addScalarResult('importeAnulado', 'importeAnulado');
//            $rsm->addScalarResult('cantidadAnulados', 'cantidadAnulados');
//            $rsm->addScalarResult('cantidadEmitidos', 'cantidadEmitidos');
//            $items = $this->_em->createNativeQuery($query, $rsm)
//                    ->setParameter('fecha', $fechaDia->format('d/m/Y'))
//                    ->setParameter('idEmpresa', $empresa)
//                    ->getArrayResult();
//            return $items;
//    }
}

?>
