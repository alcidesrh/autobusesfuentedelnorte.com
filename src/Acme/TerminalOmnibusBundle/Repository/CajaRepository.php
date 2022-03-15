<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\EstadoCaja;
use Acme\TerminalOmnibusBundle\Entity\Moneda;
use Acme\BackendBundle\Entity\User;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Acme\TerminalOmnibusBundle\Entity\Caja;
use Acme\BackendBundle\Services\UtilService;

class CajaRepository extends EntityRepository
{
    private $mapFieldToColumnsSorted = array(
        'id' => 'c.id',
        'moneda' => 'm.sigla',
        'estado' => 'est.id',
        'fechaCreacion' => 'c.fechaCreacion',
        'fechaApertura' => 'c.fechaApertura'
    );
    
    public function getCajasPaginados($page, $rows, $sort, $order, $mapFilters = array(), $usuario)
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
        $monedaFilter = UtilService::getValueToMap($mapFilters, "moneda");      
        $estacionFilter = UtilService::getValueToMap($mapFilters, "estacion");   
        $estadoFilter = UtilService::getValueToMap($mapFilters, "estado");
        $fechaCreacionFilter = UtilService::getValueToMap($mapFilters, "fechaCreacion");
        $fechaAperturaFilter = UtilService::getValueToMap($mapFilters, "fechaApertura");
        
        if($fechaCreacionFilter !== null && is_string($fechaCreacionFilter)){
             $fechaFilterTemp = \DateTime::createFromFormat('d-m-Y', $fechaCreacionFilter);
             if($fechaFilterTemp === false){
                 $fechaFilterTemp = \DateTime::createFromFormat('d/m/Y', $fechaCreacionFilter);
             }
             if($fechaFilterTemp === false){
                 throw new \RuntimeException("No se pudo conventir la fecha:" . $fechaCreacionFilter);
             }
             $fechaCreacionFilter = $fechaFilterTemp;
        }
        $fechaCreacionInitFilter = null; 
        $fechaCreacionEndFilter = null;
        if($fechaCreacionFilter !== null){
            $fechaCreacionInitFilter = clone $fechaCreacionFilter;
            $fechaCreacionInitFilter->setTime(0, 0, 0);
            $fechaCreacionInitFilter = $fechaCreacionInitFilter->format('d-m-Y H:i:s');
            $fechaCreacionEndFilter = clone $fechaCreacionFilter;
            $fechaCreacionEndFilter->setTime(23, 59, 59);
            $fechaCreacionEndFilter = $fechaCreacionEndFilter->format('d-m-Y H:i:s');
        }
        
        if($fechaAperturaFilter !== null && is_string($fechaAperturaFilter)){
             $fechaFilterTemp = \DateTime::createFromFormat('d-m-Y', $fechaAperturaFilter);
             if($fechaFilterTemp === false){
                 $fechaFilterTemp = \DateTime::createFromFormat('d/m/Y', $fechaAperturaFilter);
             }
             if($fechaFilterTemp === false){
                 throw new \RuntimeException("No se pudo conventir la fecha:" . $fechaAperturaFilter);
             }
             $fechaAperturaFilter = $fechaFilterTemp;
        }
        $fechaAperturaInitFilter = null; 
        $fechaAperturaEndFilter = null;
        if($fechaAperturaFilter !== null){
            $fechaAperturaInitFilter = clone $fechaAperturaFilter;
            $fechaAperturaInitFilter->setTime(0, 0, 0);
            $fechaAperturaInitFilter = $fechaAperturaInitFilter->format('d-m-Y H:i:s');
            $fechaAperturaEndFilter = clone $fechaAperturaFilter;
            $fechaAperturaEndFilter->setTime(23, 59, 59);
            $fechaAperturaEndFilter = $fechaAperturaEndFilter->format('d-m-Y H:i:s');
        }
        
        $queryStr = " FROM Acme\TerminalOmnibusBundle\Entity\Caja c"
            . " LEFT JOIN c.estacion e "
            . " LEFT JOIN c.moneda m "
            . " LEFT JOIN c.estado est "
            . " WHERE "
            . " est.id IN (1, 2, 3) ";
        
        if($estacionUsuarioFilter !== null){
            $queryStr .= " and e.id=:estacionUsuarioFilter ";
        }
        
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "c.id" ,"identificadorFilter", $identificadorFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "m.sigla" ,"monedaFilter", $monedaFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, array("e.alias", "e.nombre") ,"estacionFilter", $estacionFilter);
        $queryStr = UtilService::setParameterToQuerySTR($queryStr, "est.nombre" ,"estadoFilter", $estadoFilter);
        if($fechaCreacionFilter !== null){
            $queryStr .= " and c.fechaCreacion BETWEEN :fechaCreacionInitFilter AND :fechaCreacionEndFilter ";
        }
        if($fechaAperturaFilter !== null){
            $queryStr .= " and c.fechaApertura BETWEEN :fechaAperturaInitFilter AND :fechaAperturaEndFilter ";
        }
        
        $queryOrder = UtilService::getQueryOrder($order, $sort, $this->mapFieldToColumnsSorted);
        if($queryOrder === ""){
            $queryOrder = " c.fechaCreacion ASC ";
        }
        
        $query = $this->_em->createQuery(" SELECT c " . $queryStr . " ORDER BY " . $queryOrder)->setMaxResults($rows)->setFirstResult($page * $rows);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "monedaFilter", $monedaFilter);
        UtilService::setParameterToQuery($query, "estacionFilter", $estacionFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        if($fechaCreacionFilter !== null){
            UtilService::setParameterToQuery($query, "fechaCreacionInitFilter", $fechaCreacionInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaCreacionEndFilter", $fechaCreacionEndFilter, false);
        }
        if($fechaAperturaFilter !== null){
            UtilService::setParameterToQuery($query, "fechaAperturaInitFilter", $fechaAperturaInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaAperturaEndFilter", $fechaAperturaEndFilter, false);
        }

//        var_dump($query->getDQL());
        $items = $query->getResult();
        
        $query = $this->_em->createQuery(" SELECT count(c) " .$queryStr);
        UtilService::setParameterToQuery($query, "estacionUsuarioFilter", $estacionUsuarioFilter, false);
        UtilService::setParameterToQuery($query, "identificadorFilter", $identificadorFilter);
        UtilService::setParameterToQuery($query, "monedaFilter", $monedaFilter);
        UtilService::setParameterToQuery($query, "estacionFilter", $estacionFilter);
        UtilService::setParameterToQuery($query, "estadoFilter", $estadoFilter);
        if($fechaCreacionFilter !== null){
            UtilService::setParameterToQuery($query, "fechaCreacionInitFilter", $fechaCreacionInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaCreacionEndFilter", $fechaCreacionEndFilter, false);
        }
        if($fechaAperturaFilter !== null){
            UtilService::setParameterToQuery($query, "fechaAperturaInitFilter", $fechaAperturaInitFilter, false);
            UtilService::setParameterToQuery($query, "fechaAperturaEndFilter", $fechaAperturaEndFilter, false);
        }
        $total =  $query->getSingleScalarResult();
        
        return array(
            'items' => $items,
            'total' => $total
        );
    }
    
    
    
    
    public function listarMonedasCajasAbiertas($user, $default = false)
    {
        if($user === null){
            return array();
        }
        
        $estacion = null;
        if($user instanceof User){
            if($user->getEstacion() !== null){
                $estacion = $user->getEstacion()->getId();
            }
            $user = $user->getId();
        }
        
        $query =  "SELECT partial c.{id, moneda} from Acme\TerminalOmnibusBundle\Entity\Caja c "
                . " LEFT JOIN c.usuario u "
                . " LEFT JOIN c.estacion e "
                . " LEFT JOIN c.moneda mon "
                . " LEFT JOIN c.estado est "
                . " WHERE "
                . " u.id = :user "
                . " and e.id = :estacion "
                . " and est.id = :estado ";
        
        if($default === true){
            $query .= " and mon.id=1 ";
        }
        
         $cajas = $this->_em->createQuery($query)
            ->setParameter('user', $user)
            ->setParameter('estacion', $estacion)
            ->setParameter('estado', EstadoCaja::ABIERTA)
            ->getResult();   
         
         $monedas = array();
         foreach ($cajas as $caja) {
             $monedas[$caja->getMoneda()->getSigla()] = $caja->getMoneda();
         }
         UtilService::move_item_to_top($monedas, "GTQ");
         return $monedas;
    }

    public function getCajaPendiente($user, $moneda)
    {
        if($moneda instanceof Moneda){
            $moneda = $moneda->getId();
        }
        
        if($user instanceof User){
            $user = $user->getId();
        }
        
         try {
            $query =  "SELECT c from Acme\TerminalOmnibusBundle\Entity\Caja c "
                    . " LEFT JOIN c.usuario u "
                    . " LEFT JOIN c.estado est "
                    . " LEFT JOIN c.moneda m "
                    . " WHERE "
                    . " u.id = :user "
                    . " and est.id IN (1, 2, 3) "
                    . " and m.id = :moneda ";

            $cajas = $this->_em->createQuery($query)
                ->setParameter('user', $user)
                ->setParameter('moneda', $moneda)
                ->getResult();
            
            foreach ($cajas as $caja) {
                return $caja;
            }
            return null;
            
        } catch (NoResultException $exc) {
            return null;
        } catch (NonUniqueResultException $exc){
            return null;
        }
    }
    
    public function getCajaAbiertaPorMoneda($user, $moneda)
    {
        if($moneda instanceof Moneda){
            $moneda = $moneda->getId();
        }
        
        $estacion = null;
        if($user instanceof User){
            if($user->getEstacion() !== null){
                $estacion = $user->getEstacion()->getId();
            }
            $user = $user->getId();
        }
        
        $query =  " 
                    SELECT c FROM Acme\TerminalOmnibusBundle\Entity\Caja c 
                    INNER JOIN c.usuario u 
                    INNER JOIN c.estacion e 
                    INNER JOIN c.estado est 
                    INNER JOIN c.moneda m 
                    WHERE 
                    e.id = :estacion 
                    and est.id = :estado 
                    and m.id = :moneda 
                    and u.id = :user 
                 "
                ;

        $cajas = $this->_em->createQuery($query)
                ->setParameter('user', $user)
                ->setParameter('estacion', $estacion)
                ->setParameter('moneda', $moneda)
                ->setParameter('estado', intval(EstadoCaja::ABIERTA))
                ->getResult();
        
        if($cajas === null || count($cajas) !== 1){
            return null;
        }else{ 
            $caja = $cajas[0]; 
            $this->_em->refresh($caja);
            return $caja;
        }
    }
    
    public function checkSobregiroCaja($caja)
    {
        if($caja instanceof Caja){
            $caja = $caja->getId();
        }
        
        try {
            $query =  " SELECT SUM(o.importe) from Acme\TerminalOmnibusBundle\Entity\Caja c "
                    . " LEFT JOIN c.operaciones o "
                    . " WHERE "
                    . " c.id = :caja ";

             $importeTotal = $this->_em->createQuery($query)
                ->setParameter('caja', $caja)
                ->getSingleResult();
             
             if(doubleval($importeTotal[1]) <= 0){
                 return false; //Si es negatio o igual a cero, no hay problema.
             }else{
                 return true; //Si la suma de todos los valores es positivos la cuenta esta sobrgirada
             }
             
        } catch (NoResultException $exc) {
            throw new \RuntimeException("m1Ocurrio un error chequeando si la caja esta sobregirada.");
        }
    }
    
    public function listarCajasPendientes(\DateTime $fechaDia)
    {       
//        $fechaInitFilter = clone $fechaDia;
//        $fechaInitFilter->setTime(0, 0, 0);
//        $fechaInitFilter = $fechaInitFilter->format('d-m-Y H:i:s');
        
        $fechaEndFilter = clone $fechaDia;
        $fechaEndFilter->setTime(23, 59, 59); //Hora, minuto, y segundos
        $fechaEndFilter = $fechaEndFilter->format('d-m-Y H:i:s');
        
        $query =      " SELECT c FROM Acme\TerminalOmnibusBundle\Entity\Caja c "
                    . " INNER JOIN c.estado e "
                    . " INNER JOIN c.estacion t "
                    . " WHERE "
                    . " (c.fechaCreacion <= :fechaEndFilter or c.fechaApertura <= :fechaEndFilter)  "
                    . " and e.id IN (1,2,3) "
                    . " ORDER BY "
                    . " t.id ASC  ";
        
        $items = $this->_em->createQuery($query)
                    ->setParameter('fechaEndFilter', $fechaEndFilter)
                    ->getResult();
        return $items;
    }
}

?>
