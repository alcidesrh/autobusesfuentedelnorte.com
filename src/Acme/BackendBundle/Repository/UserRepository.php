<?php

namespace Acme\BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Acme\TerminalOmnibusBundle\Entity\Empresa;
use Acme\TerminalOmnibusBundle\Entity\Estacion;

class UserRepository extends EntityRepository
{
    public function findExpiredCredentialsUser($fechaActual)
    {
        if($fechaActual === null || $fechaActual === false || (is_string($fechaActual) && trim($fechaActual) === "")){
            return array();
        }
        
        if(is_string($fechaActual)){
            $fechaActual = \DateTime::createFromFormat('d-m-Y', $fechaActual);
        }
        
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " u.credentialsExpireAt < :fechaActual and u.credentialsExpired = false ";
        
        $items = $this->_em->createQuery($query)
            ->setParameter('fechaActual', $fechaActual)
            ->getResult();
        return $items;
    }
    
    public function findExpiredUser($fechaLastLogin)
    {
        if($fechaLastLogin === null || $fechaLastLogin === false || (is_string($fechaLastLogin) && trim($fechaLastLogin) === "")){
            return array();
        }
        
        if(is_string($fechaLastLogin)){
            $fechaLastLogin = \DateTime::createFromFormat('d-m-Y', $fechaLastLogin);
        }
    
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " ((u.lastLogin < :fechaLastLogin) or (u.lastLogin is null and u.dateCreate < :fechaLastLogin)) and u.expired = false";
        
        $items = $this->_em->createQuery($query)
                            ->setParameter('fechaLastLogin', $fechaLastLogin->format('d-m-Y H:i:s'))
                            ->getResult();
        return $items;
    }
    
    public function findEmailUserAdministrativosByEmpresa($empresa = null)
    {
        if($empresa !== null && $empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $hql =  " SELECT DISTINCT u.email FROM Acme\BackendBundle\Entity\User u "
                . " LEFT JOIN u.empresas e "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND u.roles LIKE '%ROLE_ADMINISTRATIVOS%' "
                ;
        
        if($empresa !== null){
            $hql .= " AND e.id = :idEmpresa ";
        }
        
        $query = $this->_em->createQuery($hql);
        if($empresa !== null){
            $query->setParameter('idEmpresa', $empresa);
        }
        $result = array();
        $items = $query->getArrayResult();
        foreach($items as $item){
            if($item["email"] !== null && trim($item["email"]) !== ""){
                $result[] = trim($item["email"]);
            }
        }
        $result = array_unique($result);
        return $result;
    }
    
    public function findUserAdministrativosByEmpresa($empresa)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }
        
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " LEFT JOIN u.empresas e "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND e.id = :idEmpresa "
                . " AND u.roles LIKE '%ROLE_ADMINISTRATIVOS%' "
                ;
        
        $items = $this->_em->createQuery($query)
                            ->setParameter('idEmpresa', $empresa)
                            ->getResult();
        return $items;
    }
    
    public function findUserSupervisoresByEstacion($estacion)
    {
        if($estacion instanceof Estacion){
            $estacion = $estacion->getId();
        }
        
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " LEFT JOIN u.estacion e "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND e.id = :idEstacion "
                . " AND ((u.roles LIKE '%ROLE_SUPERVISOR_BOLETO%') or (u.roles LIKE '%ROLE_SUPERVISOR_ENCOMIENDA%')) "
                ;
        
        $items = $this->_em->createQuery($query)
                            ->setParameter('idEstacion', $estacion)
                            ->getResult();
        return $items;
    }
    
    public function findUserSupervisoresByEmpresaAndEstaciones($empresa, $estaciones)
    {
        if($empresa instanceof Empresa){
            $empresa = $empresa->getId();
        }

        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " LEFT JOIN u.estacion est "
                . " LEFT JOIN u.empresas emp "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND est.id IN (:idEstaciones) "
                . " AND emp.id = :idEmpresa "
                . " AND ((u.roles LIKE '%ROLE_SUPERVISOR_BOLETO%') or (u.roles LIKE '%ROLE_SUPERVISOR_ENCOMIENDA%')) "
                ;
        
        $items = $this->_em->createQuery($query)
                            ->setParameter('idEstaciones', $estaciones)
                            ->setParameter('idEmpresa', $empresa)
                            ->getResult();
        return $items;
    }
    
    public function findUserAllAdministrativos()
    {        
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND u.roles LIKE '%ROLE_ADMINISTRATIVOS%' "
                ;
        
        $items = $this->_em->createQuery($query)->getResult();
        return $items;
    }
    
    public function findSuperAdmin()
    {
        $query =  " SELECT u FROM Acme\BackendBundle\Entity\User u "
                . " LEFT JOIN u.empresas e "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND u.roles LIKE '%ROLE_SUPER_ADMIN%' "
                ;
        
        $items = $this->_em->createQuery($query)->getResult();
        return $items;
    }
    
    public function findEmailSuperAdmin()
    {
        $query =  " SELECT DISTINCT u.email FROM Acme\BackendBundle\Entity\User u "
                . " LEFT JOIN u.empresas e "
                . " WHERE "
                . " u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0 "
                . " AND u.roles LIKE '%ROLE_SUPER_ADMIN%' "
                ;
        
        $result = array(); 
        $items = $this->_em->createQuery($query)->getArrayResult();
        foreach($items as $item){
            $result[] = $item["email"];
        }
            
        return $result;
    }
}

?>
