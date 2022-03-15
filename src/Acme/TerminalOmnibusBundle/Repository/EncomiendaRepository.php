<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\NoResultException;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\Salida;
use Acme\TerminalOmnibusBundle\Entity\Estacion;
use Acme\TerminalOmnibusBundle\Entity\Empresa;

class EncomiendaRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(
        'id' => 'e.id',
        'fechaCreacion' => 'e.fechaCreacion',
        'boleto' => 'b.id',
//        'ruta' => 'ru.codigo',
        'cantidad' => 'e.cantidad',
        'clienteRemitente' => 'cr.id',
        'clienteDestinatario' => 'cd.id'
    );
    
    public function getEncomiendasPaginadas($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        
        $identificadorFilter = UtilService::getValueToMap($mapFilters, "identificador"); 
        $empresaFilter = UtilService::getValueToMap($mapFilters, "empresa");
        $rutaFilter = UtilService::getValueToMap($mapFilters, "ruta");
        $cantidadFilter = UtilService::getValueToMap($mapFilters, "cantidad");
        $tipoEncomiendaFilter = UtilService::getValueToMap($mapFilters, "tipoEncomienda");
        $clienteRemitenteFilter = UtilService::getValueToMap($mapFilters, "clienteRemitente");
        $clienteDestinatarioFilter = UtilService::getValueToMap($mapFilters, "clienteDestinatario");
        $estacionOrigenFilter = UtilService::getValueToMap($mapFilters, "estacionOrigen");
        $estacionDestinoFilter = UtilService::getValueToMap($mapFilters, "estacionDestino");
        $tipoDocumentoFilter = UtilService::getValueToMap($mapFilters, "tipoDocumento");
        $estadoFilter = UtilService::getValueToMap($mapFilters, "estado");
        $boletoFilter = UtilService::getValueToMap($mapFilters, "boleto");
        $descripcionFilter = UtilService::getValueToMap($mapFilters, "descripcion");
        
        $fechaCreacionInitFilter = new \DateTime();
        $fechaCreacionInitFilter->modify("-1 month");
        $fechaCreacionEndFilter = new \DateTime();
        $fechaCreacionEndFilter->modify("+1 month");
        $rangoFechaCreacionFilter = UtilService::getValueToMap($mapFilters, "rangoFechaCreacion");
        if($rangoFechaCreacionFilter !== null && trim($rangoFechaCreacionFilter) !== ""){
            $rangoFechaArray = explode("-", $rangoFechaCreacionFilter);
            if(count($rangoFechaArray) === 2){
                $fechaInicialStr = trim($rangoFechaArray[0]);
                $fechaFinalStr = trim($rangoFechaArray[1]);
                if($fechaInicialStr !== "" && $fechaFinalStr !== ""){
                    $fechaInicialDateTime = \DateTime::createFromFormat('d/m/Y', $fechaInicialStr);
                    if($fechaInicialDateTime === false){
                        $fechaInicialDateTime = \DateTime::createFromFormat('d-m-Y', $fechaInicialStr);
                    }
                    if($fechaInicialDateTime !== false){
                        $fechaCreacionInitFilter = $fechaInicialDateTime;
                    }
                    
                    $fechaFinalDateTime = \DateTime::createFromFormat('d/m/Y', $fechaFinalStr);
                    if($fechaFinalDateTime === false){
                        $fechaFinalDateTime = \DateTime::createFromFormat('d-m-Y', $fechaFinalStr);
                    }
                    if($fechaFinalDateTime !== false){
                        $fechaCreacionEndFilter = $fechaFinalDateTime;
                    }     
                }             
            }
        }
        $fechaCreacionInitFilter->setTime(0, 0, 0);
        $fechaCreacionInitFilter = $fechaCreacionInitFilter->format('d-m-Y H:i:s');
        
        $fechaCreacionEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaCreacionEndFilter = $fechaCreacionEndFilter->format('d-m-Y H:i:s');
        
//        $limiteEntrega = new \DateTime();
//        $limiteEntrega->modify("-48 hour");
//        $limiteEntrega->setTime(0, 0, 0);
//        $limiteEntrega = $limiteEntrega->format('d-m-Y H:i:s');
        
        $queryStr =   " FROM Acme\TerminalOmnibusBundle\Entity\Encomienda e "
                    . " INNER JOIN e.empresa emp "
                    . " LEFT JOIN e.boleto b "
                    . " INNER JOIN e.ruta ru "
                    . " INNER JOIN e.tipoEncomienda te "
                    . " INNER JOIN e.estacionOrigen eo "
                    . " INNER JOIN e.estacionDestino ed "
                    . " INNER JOIN e.clienteRemitente cr "
                    . " INNER JOIN e.clienteDestinatario cd "
                    . " INNER JOIN e.tipoDocumento td "
                    . " LEFT JOIN e.bitacora eve "
                    . " LEFT JOIN eve.estado est ";
        
        if($estacionUsuarioFilter !== null){
            $queryStr .=  " LEFT JOIN e.rutas ruts " 
                        . " LEFT JOIN ruts.estacionDestino edr ";
        }
        
       $queryStr .=   " WHERE "
                    . " e.fechaCreacion BETWEEN :fechaCreacionInitFilter AND :fechaCreacionEndFilter "
//                    . " and ((b.id is not null and est.id IN (1,2,4)) or (b.id is null and (est.id IN (1,2,3,4) OR (est.id=5 and eve.fecha > :limiteEntrega)))) " 
                     ;
        
         if($estacionUsuarioFilter !== null){
//            $queryStr .= " and ((est.id=1 and eo.id=:estacionUsuarioFilter) or (est.id <> 1)) ";
            //Esto es para no mostrar las encomiendas a los destinos, si aun no esta transitando
            $queryStr .= " and ((eo.id=:estacionUsuarioFilter) or (eo.id<>:estacionUsuarioFilter and e.estuboTransito=1 and edr.id=:estacionUsuarioFilter))";
        }
        

        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.id" ,"identificadorFilter", $identificadorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "emp.id" ,"empresaFilter", $empresaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("ru.codigo", "ru.nombre") ,"rutaFilter", $rutaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.cantidad" , "cantidadFilter", $cantidadFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "te.nombre" , "tipoEncomiendaFilter", $tipoEncomiendaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "cr.id" , "clienteRemitenteFilter", $clienteRemitenteFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "cd.id" , "clienteDestinatarioFilter", $clienteDestinatarioFilter, false);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("eo.alias", "eo.nombre") ,"estacionOrigenFilter", $estacionOrigenFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("ed.alias", "ed.nombre") ,"estacionDestinoFilter", $estacionDestinoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "td.nombre" , "tipoDocumentoFilter", $tipoDocumentoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "est.nombre" , "estadoFilter", $estadoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "b.id" , "boletoFilter", $boletoFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "e.descripcion" , "descripcionFilter", $descripcionFilter);
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " e.id DESC ";
        }
//        var_dump($queryStr);
        $query = $this->_em->createQuery(" SELECT e " . $queryStr . " GROUP BY e ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
//        $query->setParameter("limiteEntrega", $limiteEntrega);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter);
        UtilService::setParameterToQuery($query, "cantidadFilter", $cantidadFilter);
        UtilService::setParameterToQuery($query, "tipoEncomiendaFilter", $tipoEncomiendaFilter);  
        UtilService::setParameterToQuery($query, "clienteRemitenteFilter", $clienteRemitenteFilter, false);
        UtilService::setParameterToQuery($query, "clienteDestinatarioFilter", $clienteDestinatarioFilter, false);
        UtilService::setParameterToQuery($query, "estacionOrigenFilter", $estacionOrigenFilter);
        UtilService::setParameterToQuery($query, "estacionDestinoFilter", $estacionDestinoFilter);
        UtilService::setParameterToQuery($query, "tipoDocumentoFilter", $tipoDocumentoFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "boletoFilter", $boletoFilter);
        UtilService::setParameterToQuery($query, "descripcionFilter", $descripcionFilter);
        UtilService::setParameterToQuery($query, "fechaCreacionInitFilter", $fechaCreacionInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaCreacionEndFilter", $fechaCreacionEndFilter, false);
        
        if($estacionUsuarioFilter !== null){
            UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        }
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(e) " .$queryStr);
//        $query->setParameter("limiteEntrega", $limiteEntrega);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "empresaFilter", $empresaFilter);
        UtilService::setParameterToQuery($query, "rutaFilter", $rutaFilter);
        UtilService::setParameterToQuery($query, "cantidadFilter", $cantidadFilter);
        UtilService::setParameterToQuery($query, "tipoEncomiendaFilter", $tipoEncomiendaFilter);  
        UtilService::setParameterToQuery($query, "clienteRemitenteFilter", $clienteRemitenteFilter, false);
        UtilService::setParameterToQuery($query, "clienteDestinatarioFilter", $clienteDestinatarioFilter, false);
        UtilService::setParameterToQuery($query, "estacionOrigenFilter", $estacionOrigenFilter);
        UtilService::setParameterToQuery($query, "estacionDestinoFilter", $estacionDestinoFilter);
        UtilService::setParameterToQuery($query, "tipoDocumentoFilter", $tipoDocumentoFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        UtilService::setParameterToQuery($query, "boletoFilter", $boletoFilter);
        UtilService::setParameterToQuery($query, "descripcionFilter", $descripcionFilter);
        UtilService::setParameterToQuery($query, "fechaCreacionInitFilter", $fechaCreacionInitFilter, false);
        UtilService::setParameterToQuery($query, "fechaCreacionEndFilter", $fechaCreacionEndFilter, false);
        
        if($estacionUsuarioFilter !== null){
            UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        }
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }    
    
    public function getEncomiendasByIdentificadorWeb($key)
    {
        $fechaCreacionInitFilter = new \DateTime();
        $fechaCreacionInitFilter->modify("-2 day");
	$fechaCreacionInitFilter->setTime(0, 0, 0);
        $fechaCreacionInitFilter = $fechaCreacionInitFilter->format('d-m-Y H:i:s');
        
	$fechaCreacionEndFilter = new \DateTime();
        $fechaCreacionEndFilter->modify("+2 day");
        $fechaCreacionEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaCreacionEndFilter = $fechaCreacionEndFilter->format('d-m-Y H:i:s');
        
        $query  = " SELECT enc FROM Acme\TerminalOmnibusBundle\Entity\Encomienda enc "
                . " WHERE "
                . " enc.fechaCreacion BETWEEN :fechaCreacionInitFilter AND :fechaCreacionEndFilter "
                . " and enc.identificadorWeb = :key " ;
        $items = $this->_em->createQuery($query)
                  ->setParameter('key', $key)
                  ->setParameter('fechaCreacionInitFilter', $fechaCreacionInitFilter)
                  ->setParameter('fechaCreacionEndFilter', $fechaCreacionEndFilter)
                  ->getResult();
        return $items; 
    }
    
    public function getEncomiendasPorFactura($idFactura)
    {
      $query  = " SELECT enc FROM Acme\TerminalOmnibusBundle\Entity\Encomienda enc "
              . " INNER JOIN enc.facturaGenerada fg "
              . " WHERE fg.id = :idFactura " ;
      $items = $this->_em->createQuery($query)->setParameter('idFactura', $idFactura)
                ->getResult();
      return $items; 
    }
    
    public function listarEncomiendasBySalida($idSalida)
    {
            //Su ultimo embarque sea en la salida $idSalida 
            //Con el estado 3(transitando) se garantizan el 4 y el 5
            $query =  " SELECT e.*
                        FROM encomienda e 
                        left join encomienda_bitacora eb on e.id = eb.encomienda_id
                        left join encomienda_estado es on eb.estado_id = es.id
                        WHERE ( 
                        SELECT top 1 sa1.id FROM encomienda_bitacora eb1
                         left join encomienda en1
                                on en1.id = eb1.encomienda_id
                         left join salida sa1
                                on sa1.id = eb1.salida_id
                         left join encomienda_estado es1 
                                on es1.id = eb1.estado_id
                         where en1.id = e.id 
                         and (es1.id = 2 or es1.id = 4) 
                         order by eb1.fecha desc ) = :idSalida 
                         ";
//                         and ( 
//                            es.id = 3 
//                         ) 
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addRootEntityFromClassMetadata('Acme\TerminalOmnibusBundle\Entity\Encomienda', 'ec');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idSalida', $idSalida)
                    ->getResult();
            return $items;
    }
    
    /*
        RETORNA LAS ENCOMIENDAS QUE EN SU ULTIMO ESTADO ESTEN EMBARCADAS EN UNA SALIDA DETERMINADA.
    */
    public function listarEncomiendasEmbarcadasBySalida($idSalida)
    {
            $query =  " SELECT enc.*
                        FROM encomienda enc
                        INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
                        WHERE 
                        bit.salida_id = :idSalida
                        and (bit.estado_id = 2 or bit.estado_id = 3)
                      "; 
//            $query =  " SELECT e.*
//                        FROM encomienda e 
//                        WHERE ( 
//                        SELECT top 1 sa1.id FROM encomienda_bitacora eb1
//                         left join encomienda en1
//                                on en1.id = eb1.encomienda_id
//                         left join salida sa1
//                                on sa1.id = eb1.salida_id
//                         left join encomienda_estado es1 
//                                on es1.id = eb1.estado_id
//                         where en1.id = e.id 
//                         order by eb1.fecha desc ) = :idSalida 
//                         and ( 
//                         SELECT top 1 es1.id FROM encomienda_bitacora eb1 
//                         left join encomienda en1
//                                on en1.id = eb1.encomienda_id
//                         left join salida sa1
//                                on sa1.id = eb1.salida_id
//                         left join encomienda_estado es1 
//                                on es1.id = eb1.estado_id
//                         where en1.id = e.id
//                         order by eb1.fecha desc ) = 2 ";
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addRootEntityFromClassMetadata('Acme\TerminalOmnibusBundle\Entity\Encomienda', 'ec');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idSalida', $idSalida)
                    ->getResult();
            return $items;
    }
    
    /*
        RETORNA EL TOTAL DE ENCOMIENDAS QUE EN SU ULTIMO ESTADO ESTEN ENBARCADAS EN UNA SALIDA DETERMINADA.
    */
    public function totalEncomiendasEmbarcadasBySalida($idSalida)
    {
         try {
             
            $query =  " SELECT COUNT(enc.id) as total
                        FROM encomienda enc
                        INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
                        WHERE 
                        bit.salida_id = :idSalida
                        and (bit.estado_id = 2 or bit.estado_id = 3)
                      "; 
             
//            $query =  " SELECT COUNT(e.id) as total
//                        FROM encomienda e 
//                        WHERE ( 
//                        SELECT top 1 sa1.id FROM encomienda_bitacora eb1
//                         left join encomienda en1
//                                on en1.id = eb1.encomienda_id
//                         left join salida sa1
//                                on sa1.id = eb1.salida_id
//                         left join encomienda_estado es1 
//                                on es1.id = eb1.estado_id
//                         where en1.id = e.id 
//                         order by eb1.fecha desc ) = :idSalida 
//                         and ( 
//                         SELECT top 1 es1.id FROM encomienda_bitacora eb1 
//                         left join encomienda en1
//                                on en1.id = eb1.encomienda_id
//                         left join salida sa1
//                                on sa1.id = eb1.salida_id
//                         left join encomienda_estado es1 
//                                on es1.id = eb1.estado_id
//                         where en1.id = e.id
//                         order by eb1.fecha desc ) = 2 ";
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult("total", "total");
            $cantidad = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idSalida', $idSalida)
                    ->getSingleResult();
            return $cantidad["total"];
         } catch (NoResultException $exc) {
            return 0;
         }
    }
    
    /*
        NO LO VOY A USAR PQ PUDE HABER ENVIADO UNA ENCOMIENDA DEL GRUPO, Y NO EL GRUPO COMPLETO, SIN EMBARGO LA FACTURA ES POR EL GRUPO
        ESTE METODO TIENE QUE LLAMARSE ANTES DE QUE SE PERSISTA, YA QUE EL ESTADO PASARIA A EN CAMINO.
        RETORNA LOS TOTALES FACTURADOS DE LAS ENCOMIENDAS QUE EN SU ULTIMO ESTADO ESTEN ENBARCADAS EN UNA SALIDA DETERMINADA.
    */
//    public function listarTotalEncomiendasFacturadasBySalida($idSalida)
//    {
//            $query =  " SELECT  m.sigla, fg.importeTotal as total
//                        FROM encomienda e 
//                        INNER join factura_generada fg
//                                on fg.id = e.factura_generada_id
//                        INNER join moneda m
//                                on m.id = fg.moneda_id
//                        WHERE ( 
//                        SELECT top 1 sa1.id FROM encomienda_bitacora eb1
//                         left join encomienda en1
//                                on en1.id = eb1.encomienda_id
//                         left join salida sa1
//                                on sa1.id = eb1.salida_id
//                         left join encomienda_estado es1 
//                                on es1.id = eb1.estado_id
//                         where en1.id = e.id 
//                         order by eb1.fecha desc ) = :idSalida 
//                         and ( 
//                         SELECT top 1 es1.id FROM encomienda_bitacora eb1 
//                         left join encomienda en1
//                                on en1.id = eb1.encomienda_id
//                         left join salida sa1
//                                on sa1.id = eb1.salida_id
//                         left join encomienda_estado es1 
//                                on es1.id = eb1.estado_id
//                         where en1.id = e.id
//                         order by eb1.fecha desc ) = 2 ";
//            
//            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
//            $rsm->addScalarResult('sigla', 'sigla');
//            $rsm->addScalarResult('total', 'total');
//            $items = $this->_em->createNativeQuery($query, $rsm)
//                    ->setParameter('idSalida', $idSalida)
//                    ->getArrayResult();
//            return $items;
//    }
    
    public function listarEncomiendasDesembarcarForMovil($salida, $estacionDestino)
    {
        if($salida instanceof Salida){
            $salida = $salida->getId();
        }    
        if($estacionDestino instanceof Estacion){
            $estacionDestino = $estacionDestino->getId();
        }  
        
        $query =  " SELECT e.*
                        FROM encomienda e 
                        left join encomienda_bitacora eb on e.id = eb.encomienda_id
                        left join encomienda_estado es on eb.estado_id = es.id
                        left join encomienda_ruta er on e.id = er.encomienda_id
                        WHERE ( 
                         SELECT top 1 sa1.id FROM encomienda_bitacora eb1
                         left join encomienda en1
                                on en1.id = eb1.encomienda_id
                         left join salida sa1
                                on sa1.id = eb1.salida_id
                         left join encomienda_estado es1 
                                on es1.id = eb1.estado_id
                         where en1.id = e.id 
                         and ( es1.id = 2 or es1.id = 4 )
                         order by eb1.fecha desc ) = :idSalida 
                         and es.id = 3
                         and er.estacion = :idEstacionDestino
                   ";
            
          $rsm = new ResultSetMappingBuilder($this->getEntityManager());
          $rsm->addRootEntityFromClassMetadata('Acme\TerminalOmnibusBundle\Entity\Encomienda', 'ec');
          $items = $this->_em->createNativeQuery($query, $rsm)
                        ->setParameter('idSalida', $salida)
                        ->setParameter('idEstacionDestino', $estacionDestino)
                        ->getResult();
          return $items;
    }
    
    
    /*
        PROCESAR: RETORNA LAS ENCOMIENDAS QUE EN SU ULTIMO ESTADO ESTEN EMBARCADAS O EN TRANSITO EN UNA SALIDA DETERMINADA.
    */
    public function listarEncomiendasEmbarcadasBySalidaParaProcesar($idSalida)
    {
            $query =  " SELECT enc.*
                        FROM encomienda enc
                        INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
                        WHERE 
                        bit.salida_id = :idSalida
                        and (bit.estado_id = 2 or bit.estado_id = 3)
                        order by enc.estacion_destino_id asc
                     ";
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addRootEntityFromClassMetadata('Acme\TerminalOmnibusBundle\Entity\Encomienda', 'ec');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idSalida', $idSalida)
                    ->getResult();
            return $items;
    }
    
    /*
        PROCESAR: RETORNA LAS ENCOMIENDAS QUE ESTAN PENDIENTES EN UNA RUTA EN UNA ESTACION.
    */
    public function listarEncomiendasPendientesByRutaParaProcesar($empresa, $ruta, $estacionUsuario)
    {
            if($empresa instanceof \Acme\TerminalOmnibusBundle\Entity\Empresa){
                $empresa = $empresa->getId();
            }
            
            if($ruta instanceof \Acme\TerminalOmnibusBundle\Entity\Ruta){
                $ruta = $ruta->getCodigo();
            }
        
            if($estacionUsuario !== null && $estacionUsuario instanceof Estacion){
                $estacionUsuario = $estacionUsuario->getId();
            }
        
            $query =  " SELECT enc.*
                        FROM encomienda enc
                        INNER JOIN encomienda_ruta rut on enc.id = rut.encomienda_id
                        INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
                        WHERE 
                        enc.empresa_id = :empresa
                        and rut.ruta_codigo = :ruta
                        and (bit.estado_id = 1 or bit.estado_id = 4)
                      ";
            
            if($estacionUsuario !== null){
                $query .= "and ( enc.estacion_origen_id = :estacion or rut.estacion = :estacion ) ";
            }
            
            $query .= " order by enc.estacion_destino_id asc, enc.fecha_creacion asc ";
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addRootEntityFromClassMetadata('Acme\TerminalOmnibusBundle\Entity\Encomienda', 'ec');
            $query = $this->_em->createNativeQuery($query, $rsm);
            $query->setParameter('empresa', $empresa);
            $query->setParameter('ruta', $ruta);
            
            if($estacionUsuario !== null){
                $query->setParameter('estacion', $estacionUsuario);
            }
            
            return $query->getResult();
    }
    
    /*
        PROCESAR: RETORNA LAS ENCOMIENDAS QUE ESTAN PENDIENTES EN UNA ESTACION PARA MODIFICAR LA PRIMERA RUTA SOLAMENTE
    */
    public function listarEncomiendasPendientesPorEstacion($estacion)
    {
            if($estacion !== null && $estacion instanceof Estacion){
                $estacion = $estacion->getId();
            }
        
            $query =  " SELECT enc.*
                        FROM encomienda enc
                        INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
                        WHERE 
                        ((bit.estado_id = 1 and enc.estacion_origen_id = :estacion) or 
                         (bit.estado_id = 4 and enc.transito = 0 and enc.estacion_origen_id = :estacion) or 
                         (bit.estado_id = 4 and enc.transito = 1 and (bit.estacion = :estacion and enc.estacion_destino_id <> :estacion)))
                         ";
 
            $query .= " order by enc.fecha_creacion asc ";
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addRootEntityFromClassMetadata('Acme\TerminalOmnibusBundle\Entity\Encomienda', 'ec');
            $query = $this->_em->createNativeQuery($query, $rsm);
            $query->setParameter('estacion', $estacion);
            
            return $query->getResult();
    }
    
    /*
        PROCESAR: RETORNA LAS ENCOMIENDAS QUE ESTAN PENDIENTES DE ENTREGAR EN UNA ESTACION.
    */
    public function listarEncomiendasPendientesEntrega($estacion = null, $empresa = null,  $usuario)
    {
            if($estacion !== null && $estacion instanceof Estacion){
                $estacion = $estacion->getId();
            }
            if($empresa !== null && $empresa instanceof Empresa){
                $empresa = $empresa->getId();
            }
            
            $query =  " SELECT enc.*
                        FROM encomienda enc
                        INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
                        WHERE 
                        (enc.estacion_destino_id = :estacion) and (enc.empresa_id = :empresa)
                         and (bit.estado_id = 3 or (bit.estado_id = 4 and enc.transito = 1))
                         ";
 
            $query .= " order by enc.fecha_creacion asc ";
            
            $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addRootEntityFromClassMetadata('Acme\TerminalOmnibusBundle\Entity\Encomienda', 'ec');
            $query = $this->_em->createNativeQuery($query, $rsm);
            $query->setParameter('estacion', $estacion);
            $query->setParameter('empresa', $empresa);
            return $query->getResult();
    }
    
    public function listarEncomiendasFacturadas($estacion, $empresa, $fecha)
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
        
        $query =  " SELECT en from Acme\TerminalOmnibusBundle\Entity\Encomienda en "
                    . " INNER JOIN en.facturaGenerada fg "
                    . " INNER JOIN fg.factura fa "
                    . " INNER JOIN fa.empresa em "
                    . " INNER JOIN fg.estacion es "
                    . " WHERE "
                    . " es.id = :estacion "
                    . " and em.id = :empresa "
                    . " and fg.fechaCreacion between :fechaInitFilter and :fechaEndFilter  "
                    . " order by fg.consecutivo ";
            
        $encomiendas = $this->_em->createQuery($query)
                    ->setParameter('estacion', $estacion)
                    ->setParameter('empresa', $empresa)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        
        return $encomiendas;
    }
    
   /*
        PARA REPORTAR PARCIALES Y TOTALES
    */
    public function listarTotalesEncomienda($fecha, $empresa, $estaciones = array())
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
                        when (encomienda.tipo_documento_id = 1 and bitacora.estado_id <> 6)
                            then 1
                            else 0
                    end)) AS cantidadEncoFacturadoEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 1 and bitacora.estado_id <> 6)
                            then encomienda.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeEncoFacturadoEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 2 and bitacora.estado_id <> 7)
                            then 1
                            else 0
                    end)) AS cantidadEncoPorCobrarEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 2 and bitacora.estado_id <> 7)
                            then encomienda.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeEncoPorCobrarEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 4 and bitacora.estado_id <> 7)
                            then 1
                            else 0
                    end)) AS cantidadEncoGuiasEstacion
                    FROM encomienda encomienda 
                    INNER JOIN empresa empresa ON encomienda.empresa_id = empresa.id
                    INNER JOIN estacion estacion ON encomienda.estacion_creacion_id = estacion.id
                    INNER JOIN encomienda_bitacora bitacora ON encomienda.ultima_bitacora_id = bitacora.id
                    WHERE
                    estacion.id IN ( :idEstaciones )
                    and empresa.id = :empresa
                    and encomienda.fecha_creacion between :fechaInitFilter and :fechaEndFilter 
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
            $rsm->addScalarResult('cantidadEncoFacturadoEstacion', 'cantidadEncoFacturadoEstacion');
            $rsm->addScalarResult('importeEncoFacturadoEstacion', 'importeEncoFacturadoEstacion');
            $rsm->addScalarResult('cantidadEncoPorCobrarEstacion', 'cantidadEncoPorCobrarEstacion');
            $rsm->addScalarResult('importeEncoPorCobrarEstacion', 'importeEncoPorCobrarEstacion');
            $rsm->addScalarResult('cantidadEncoGuiasEstacion', 'cantidadEncoGuiasEstacion');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idEstaciones', $idEstaciones)
                    ->setParameter('empresa', $empresa)
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getArrayResult();
            return $items;
    }
    
    public function listarDetalleEncomiendaBySalida($idSalida)
    {
        
        $query = "
                    SELECT
                    estacion.nombre as nombreEstacionCreacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 1 and bitacora.estado_id <> 6)
                            then 1
                            else 0
                    end)) AS cantidadEncoFacturadoEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 1 and bitacora.estado_id <> 6)
                            then encomienda.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeEncoFacturadoEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 2 and bitacora.estado_id <> 7)
                            then 1
                            else 0
                    end)) AS cantidadEncoPorCobrarEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 2 and bitacora.estado_id <> 7)
                            then encomienda.precioCalculadoMonedaBase
                            else 0
                    end)) AS importeEncoPorCobrarEstacion,
                    SUM((case
                        when (encomienda.tipo_documento_id = 4 and bitacora.estado_id <> 7)
                            then 1
                            else 0
                    end)) AS cantidadEncoGuiasEstacion
                    FROM encomienda encomienda 
                    INNER JOIN empresa empresa ON encomienda.empresa_id = empresa.id
                    INNER JOIN estacion estacion ON encomienda.estacion_creacion_id = estacion.id
                    INNER JOIN encomienda_bitacora bitacora ON encomienda.ultima_bitacora_id = bitacora.id
                    WHERE
                    encomienda.primera_salida_id = :idSalida and encomienda.transito=1
                    GROUP BY
                    estacion.nombre
                    ORDER BY
                    estacion.nombre
                 ";
        
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult('nombreEstacionCreacion', 'nombreEstacionCreacion');
            $rsm->addScalarResult('cantidadEncoFacturadoEstacion', 'cantidadEncoFacturadoEstacion');
            $rsm->addScalarResult('importeEncoFacturadoEstacion', 'importeEncoFacturadoEstacion');
            $rsm->addScalarResult('cantidadEncoPorCobrarEstacion', 'cantidadEncoPorCobrarEstacion');
            $rsm->addScalarResult('importeEncoPorCobrarEstacion', 'importeEncoPorCobrarEstacion');
            $rsm->addScalarResult('cantidadEncoGuiasEstacion', 'cantidadEncoGuiasEstacion');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('idSalida', $idSalida)
                    ->getArrayResult();
            return $items;
    }
    
    public function listarEncomiendaPendienteEntrega($empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $query = "
SELECT 
ede.id as idEstacion,
ede.nombre as nombreEstacion,
'Pendiente Entrega' as tipo,
etd.id as idTipoDocumento,
etd.nombre as nombreTipoDocumento,
COUNT(enc.id) as cantidad,
SUM(enc.precioCalculadoMonedaBase) as total
FROM encomienda enc
INNER JOIN encomienda_documento_tipo etd on etd.id = enc.tipo_documento_id
INNER JOIN estacion ede on ede.id = enc.estacion_destino_id
INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
WHERE 
enc.empresa_id = :empresa
and (bit.estado_id = 3 or (bit.estado_id = 4 and enc.transito = 1))
GROUP BY
ede.id,
ede.nombre,
etd.id,
etd.nombre
ORDER BY
ede.id,
etd.id
                 ";
        
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult('idEstacion', 'idEstacion');
            $rsm->addScalarResult('nombreEstacion', 'nombreEstacion');
            $rsm->addScalarResult('tipo', 'tipo');
            $rsm->addScalarResult('idTipoDocumento', 'idTipoDocumento');
            $rsm->addScalarResult('nombreTipoDocumento', 'nombreTipoDocumento');
            $rsm->addScalarResult('cantidad', 'cantidad');
            $rsm->addScalarResult('total', 'total');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('empresa', $empresa)
                    ->getArrayResult();
            return $items;
    }
    
    public function listarEncomiendaPendienteEnvio($empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $query = "
SELECT 
eor.id as idEstacion,
eor.nombre as nombreEstacion,
'Pendiente Envio' as tipo,
etd.id as idTipoDocumento,
etd.nombre as nombreTipoDocumento,
COUNT(enc.id) as cantidad,
SUM(enc.precioCalculadoMonedaBase) as total
FROM encomienda enc
INNER JOIN encomienda_documento_tipo etd on etd.id = enc.tipo_documento_id
INNER JOIN estacion eor on eor.id = enc.estacion_creacion_id
INNER JOIN encomienda_bitacora bit on bit.id = enc.ultima_bitacora_id
WHERE 
enc.empresa_id = :empresa and ((bit.estado_id = 1) or (bit.estado_id = 4 and enc.transito = 0))
GROUP BY
eor.id,
eor.nombre,
etd.id,
etd.nombre
ORDER BY
eor.id,
etd.id
                 ";
        
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
            $rsm->addScalarResult('idEstacion', 'idEstacion');
            $rsm->addScalarResult('nombreEstacion', 'nombreEstacion');
            $rsm->addScalarResult('tipo', 'tipo');
            $rsm->addScalarResult('idTipoDocumento', 'idTipoDocumento');
            $rsm->addScalarResult('nombreTipoDocumento', 'nombreTipoDocumento');
            $rsm->addScalarResult('cantidad', 'cantidad');
            $rsm->addScalarResult('total', 'total');
            $items = $this->_em->createNativeQuery($query, $rsm)
                    ->setParameter('empresa', $empresa)
                    ->getArrayResult();
            return $items;
    }
    
    public function listarEncomiendasByCliente($codigoCliente)
    {
        $fechaInitFilter = new \DateTime();
        $fechaInitFilter->modify("-10 day");
        $fechaInitFilter = $fechaInitFilter->setTime(0, 0, 0);
        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
              
        $fechaEndFilter = new \DateTime();
        $fechaEndFilter = $fechaEndFilter->setTime(23, 59, 59);
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =      " SELECT "
                    . " en "
                    . " FROM "
                    . " Acme\TerminalOmnibusBundle\Entity\Encomienda en "
                    . " INNER JOIN en.bitacora bi "
                    . " INNER JOIN bi.estado es "
                    . " WHERE "
                    . " en.codigoExternoCliente like :codigoCliente "
                    . " and ((es.id IN (5,6,7) and en.fechaCreacion between :fechaInitFilter and :fechaEndFilter) or  es.id IN (1,2,3,4)) "
                    . " ORDER BY "
                    . " en.fechaCreacion ";
            
        $encomiendas = $this->_em->createQuery($query)
                    ->setParameter('codigoCliente', "%".$codigoCliente."%")
                    ->setParameter('fechaInitFilter', $fechaInitFilter)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        
        return $encomiendas;
    }
}




?>
