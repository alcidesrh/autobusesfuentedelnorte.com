<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

class TipoPagoRepository extends EntityRepository
{
    public function findAllActive()
    {   
        $query =  "SELECT tp from Acme\TerminalOmnibusBundle\Entity\TipoPago tp where tp.activo=1 ";
        return $this->_em->createQuery($query)->getResult();
    }
    
}

?>
