<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class CalendarioFacturaFechaRepository extends EntityRepository
{
    
    public function getAllByRangoFecha($ruta, $fechaInicial, $fechaFinal)
    {
        $query = "SELECT cff FROM Acme\TerminalOmnibusBundle\Entity\CalendarioFacturaFecha cff WHERE " .
                 " (cff.calendarioFacturaRuta='". $ruta . "') and " .
                 " (cff.fecha between '" . $fechaInicial->format('d-m-Y ') . "' and '" . $fechaFinal->format('d-m-Y ') . "' ) " .
                 " ORDER BY cff.fecha ASC";
        return $this->_em->createQuery($query)->getResult();
    }
}

?>
