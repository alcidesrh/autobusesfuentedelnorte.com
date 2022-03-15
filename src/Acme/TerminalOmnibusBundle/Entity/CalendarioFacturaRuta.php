<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\CalendarioFacturaRutaRepository")
* @ORM\Table(name="calendario_factura_ruta")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields ="ruta", message="Ya se definio un calendario para la ruta")
*/
class CalendarioFacturaRuta{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La ruta no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Ruta")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo", nullable=false)   
    */
    protected $ruta;
    
    /**
    * @ORM\Column(type="boolean", nullable=false)
    */
    protected $constante;
    
    /**
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=true)        
    */
    protected $empresa;
    
    /**
    * @ORM\OneToMany(targetEntity="CalendarioFacturaFecha", mappedBy="calendarioFacturaRuta", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaCalendarioFacturaFecha;
    protected $listaCalendarioFacturaFechaHidden;
    
    /*
     * VALIDACION QUE SI ES CONSTANTE SE DEFINA LA EMPRESA, SINO LO ES LA EMPRSA DEBE ESTAR VACIA Y DIFINIR UN CALEDARIO DE FECHAS.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
       if($this->constante === true && $this->empresa === null){
            $context->addViolation("Como el calendario es constante se debe definir solamente la empresa.");   
       } 
       
       if($this->constante === false){
            if($this->empresa !== null){
                $context->addViolation("Como el calendario no es constante no se debe definir la empresa.");   
            }
            
            if($this->listaCalendarioFacturaFecha->count() === 0){
                $context->addViolation("Como el calendario no es constante se deben definir las empresas por fechas.");   
            }
       }
    }
    
    public function addListaCalendarioFacturaFecha(CalendarioFacturaFecha $item) {
       $item->setCalendarioFacturaRuta($this);
       $this->getListaCalendarioFacturaFecha()->add($item);
       return $this;
    }
    
    function __construct() {
        $this->listaCalendarioFacturaFecha = new \Doctrine\Common\Collections\ArrayCollection();
        $this->constante = true;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getRuta() {
        return $this->ruta;
    }

    public function getConstante() {
        return $this->constante;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getListaCalendarioFacturaFecha() {
        return $this->listaCalendarioFacturaFecha;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setConstante($constante) {
        $this->constante = $constante;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setListaCalendarioFacturaFecha($listaCalendarioFacturaFecha) {
        $this->listaCalendarioFacturaFecha = $listaCalendarioFacturaFecha;
    }

    public function getListaCalendarioFacturaFechaHidden() {
        return $this->listaCalendarioFacturaFechaHidden;
    }

    public function setListaCalendarioFacturaFechaHidden($listaCalendarioFacturaFechaHidden) {
        $this->listaCalendarioFacturaFechaHidden = $listaCalendarioFacturaFechaHidden;
    }

}

?>