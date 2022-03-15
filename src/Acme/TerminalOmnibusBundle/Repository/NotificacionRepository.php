<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class NotificacionRepository extends EntityRepository
{
    public function listarNotificacion($mostrarAlertasEstaciones, $mostrarAlertasAgencias)
    {
        $query = " SELECT n from Acme\TerminalOmnibusBundle\Entity\Notificacion n "
               . " WHERE "
               . " n.activo = 1 ";
        
        if($mostrarAlertasEstaciones && $mostrarAlertasAgencias){
            $query .= " and (n.oficinas=1 or n.agencias=1) ";
        }else if($mostrarAlertasEstaciones){
            $query .= " and n.oficinas=1 ";
        }else if($mostrarAlertasAgencias){
            $query .= " and n.agencias=1 ";
        }
        
        return $this->_em->createQuery($query)->getResult();
    }
}

?>
