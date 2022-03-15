<?php

namespace Acme\TerminalOmnibusBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NoResultException;

class CalendarioFacturaRutaRepository extends EntityRepository
{
    public function getEmpresaQueFactura($idRuta, $fecha)
    {
        try {
            $query =  " SELECT partial cfr.{id, constante, empresa} from Acme\TerminalOmnibusBundle\Entity\CalendarioFacturaRuta cfr "
                    . " LEFT JOIN cfr.ruta r "
                    . " WHERE "
                    . " r.codigo=:idRuta ";
            
            $calendarioFacturaRuta = $this->_em->createQuery($query)
                        ->setParameter('idRuta', $idRuta)
                        ->getSingleResult();

           if($calendarioFacturaRuta !== null && $calendarioFacturaRuta->getConstante() === true && $calendarioFacturaRuta->getEmpresa() !== null){
               return $calendarioFacturaRuta->getEmpresa();
           }else{
               $query =  " SELECT partial cff.{id, empresa} from Acme\TerminalOmnibusBundle\Entity\CalendarioFacturaFecha cff "
                        . " LEFT JOIN cff.calendarioFacturaRuta cfr "
                        . " LEFT JOIN cfr.ruta r "
                        . " WHERE "
                        . " r.codigo=:idRuta "
                        . " and cff.fecha=:fecha "; 
               $calendarioFacturaFecha = $this->_em->createQuery($query)
                        ->setParameter('idRuta', $idRuta)
                        ->setParameter('fecha', $fecha->format('d-m-Y'))
                        ->getSingleResult();
               if($calendarioFacturaFecha !== null && $calendarioFacturaFecha->getEmpresa() !== null){
                    return $calendarioFacturaFecha->getEmpresa();
               }
           }
              
           return null;
            
        } catch (NoResultException $exc) {
            return null;
        }
    }    
}

?>
