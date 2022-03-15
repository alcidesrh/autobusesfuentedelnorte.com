<?php

namespace Acme\BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;

class JobSyncRepository extends EntityRepository
{
    public function listarJobSyncPendientes($idWeb)
    {
        if($idWeb !== "1" && $idWeb !== "2" && $idWeb !== "3" && $idWeb !== "4"){
            return array();
        }
        
        $query =  " SELECT j FROM Acme\BackendBundle\Entity\JobSync j "
                . " WHERE "
                . " j.web".$idWeb."estado IN (1,2) "
                . " order by j.nivel asc, j.id asc "
                ;
        
        $query = $this->_em->createQuery($query)->setMaxResults(100);
        return $query->getResult();
    }
}
