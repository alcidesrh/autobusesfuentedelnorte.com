<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class ImagenRepository extends EntityRepository
{
    public function listarImagenesGaleria($id)
    {   
       
        $query =      " SELECT i FROM Acme\TerminalOmnibusBundle\Entity\Imagen i "
                    . " INNER JOIN i.galeria g "
                    . " WHERE "
                    . " g.id = :idGaleria and g.activo = 1 ";
        
        $items = $this->_em->createQuery($query)
                ->setParameter("idGaleria", intval($id))
                ->getResult();
        return $items;
    }
}

?>
