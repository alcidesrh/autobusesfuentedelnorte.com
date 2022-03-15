<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class GaleriaRepository extends EntityRepository
{
    public function listarGaleriaActivas()
    {   
       
        $query =      " SELECT g FROM Acme\TerminalOmnibusBundle\Entity\Galeria g "
                    . " WHERE "
                    . " g.activo = 1 "
                    . " ORDER BY "
                    . " g.orden ASC  ";
        
        $items = $this->_em->createQuery($query)->getResult();
        return $items;
    }
}

?>
