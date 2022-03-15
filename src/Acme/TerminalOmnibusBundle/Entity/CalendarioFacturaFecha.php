<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\CalendarioFacturaFechaRepository")
* @ORM\Table(name="calendario_factura_fecha")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields = {"calendarioFacturaRuta", "fecha"}, message="No puede existir dos calendarios de fecha para una misma ruta.")
*/
class CalendarioFacturaFecha{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La ruta del calendario de factura por fecha no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="CalendarioFacturaRuta", inversedBy="listaCalendarioFacturaFecha")
    * @ORM\JoinColumn(name="calendario_factura_ruta_id", referencedColumnName="id", nullable=false )
    */
    protected $calendarioFacturaRuta;
    
    /**
    * @Assert\NotBlank(message = "La fecha del calendario de factura no debe estar en blanco")
    * @Assert\Date(message = "Fecha del calendario de factura no valida")
    * @ORM\Column(type="date", nullable=false)
    */
    protected $fecha;
    
    /**
    * @Assert\NotBlank(message = "La empresa del calendario de factura por fecha no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)        
    */
    protected $empresa;
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCalendarioFacturaRuta() {
        return $this->calendarioFacturaRuta;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCalendarioFacturaRuta($calendarioFacturaRuta) {
        $this->calendarioFacturaRuta = $calendarioFacturaRuta;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>