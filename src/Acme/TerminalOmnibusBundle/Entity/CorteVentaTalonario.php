<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\BackendBundle\Services\UtilService;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\CorteVentaTalonarioRepository")
* @ORM\Table(name="talonario_corte_venta")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class CorteVentaTalonario{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Talonario", inversedBy="listaCorteVentaTalonario")
    * @ORM\JoinColumn(name="talonario_id", referencedColumnName="id", nullable=false)
    */
    protected $talonario;
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoCorteVentaTalonario")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id")        
    */
    protected $estado;
    
    /**
    * @Assert\NotNull(message = "El numero inicial del talonario no debe estar en blanco")
    * @Assert\Range(
    *      min = 1,
    *      max = 9999999999,
    *      minMessage = "El numero inicial del talonario no puede ser menor que {{ limit }}",
    *      maxMessage = "El numero inicial del talonario no puede ser mayor que {{ limit }}"
    * )
    * @ORM\Column(name="inicial", type="bigint", nullable=false)
    */
    protected $inicial;
    
    /**
    * @Assert\NotNull(message = "El numero final del talonario no debe estar en blanco")
    * @Assert\Range(
    *      min = 1,
    *      max = 9999999999,
    *      minMessage = "El numero final del talonario no puede ser menor que {{ limit }}",
    *      maxMessage = "El numero final del talonario no puede ser mayor que {{ limit }}"
    * )
    * @ORM\Column(name="final", type="bigint", nullable=false)
    */
    protected $final;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,8}$)|(^\d{0,8}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe del corte de venta solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "1",
    *      max = "99999999.99",
    *      minMessage = "El importe del corte de venta no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe del corte de venta no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe del corte de venta debe ser un número válido."
    * )   
    * @ORM\Column(name="importe_total", type="decimal", precision=10, scale=2, nullable=false)
    */
    protected $importeTotal;
    
    /**
    * @ORM\OneToMany(targetEntity="CorteVentaTalonarioItem", mappedBy="corteVentaTalonario", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaItems;
    
    /**
    * @Assert\Range(
    *      max = "99999999.99",
    *      maxMessage = "El importe del corte de venta no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe del corte de venta debe ser un número válido."
    * )   
    * @ORM\Column(name="importe_total_items", type="decimal", precision=10, scale=2, nullable=false)
    */
    protected $importeTotalItems;

    /**
    * @Assert\NotBlank(message = "La fecha no debe estar en blanco")
    * @Assert\Date(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="date", nullable=false)
    */
    protected $fecha;
    
    /**
    * @Assert\NotNull(message = "Debe seleccionar un inspector")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="inspector", referencedColumnName="id", nullable=false)        
    */
    protected $inspector;
    
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - INIT
    //------------------------------------------------------------------------------  
    /**
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_creacion_id", referencedColumnName="id", nullable=true)   
    */
    protected $estacionCreacion;
    
    /**
    * @Assert\NotBlank(message = "La fecha de creacion no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
    */
    protected $fechaCreacion;
    
    /**
    * @Assert\NotNull(message = "El usuario de creacion no debe estar en null")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuarioCreacion;
    
    function __construct($usuarioCreacion) {
        $this->fecha = new \DateTime();
        $this->fechaCreacion = new \DateTime();
        $this->usuarioCreacion = $usuarioCreacion;
        $this->inspector = $usuarioCreacion;
        if($usuarioCreacion !== null){
            $this->estacionCreacion = $usuarioCreacion->getEstacion();
        }
        $this->importeTotal = 0;
        $this->importeTotalItems = -1;
        $this->listaItems = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /*
    * VALIDACION 
    */
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        $inicial = $this->talonario !== null ? intval($this->talonario->getInicial()) : 0;
        $final = $this->talonario !== null ? intval($this->talonario->getFinal()) : 0;
        
        if($this->inicial < $inicial || $this->inicial > $final){
            $context->addViolation("El valor 'Del' debe estar en el rango entre ". $inicial . " y " . $final . ".");
        }
        if($this->final < $inicial || $this->final > $final){
            $context->addViolation("El valor 'Al' debe estar en el rango entre ". $inicial . " y " . $final . ".");
        }
        if($this->inicial > $this->final){
            $context->addViolation("El valor 'Del' debe debe ser menor que el valor 'Al'.");
        }
        
        $fechaSalida1 = clone $this->getTalonario()->getTarjeta()->getSalida()->getFecha();
        $fechaSalida1->setTime(0, 0, 0);
        if(UtilService::compararFechas($this->fecha, $fechaSalida1) < 0){
            $context->addViolation("La fecha debe ser igual o mayor que la fecha de salida. Fecha mínima: " . $fechaSalida1->format("d-m-Y"));
        }
        
//        $fechaSalida2 = $this->getTalonario()->getTarjeta()->getFechaCreacion();
        $fechaSalida2 = clone $this->getTalonario()->getTarjeta()->getSalida()->getFecha();
        $fechaSalida2->modify("+2 days");
        $fechaSalida2->setTime(0, 0, 0);
        if(UtilService::compararFechas($fechaSalida2, $this->fecha) < 0){
            $context->addViolation("La fecha debe ser igual o menor que " . $fechaSalida2->format("d-m-Y") . ".");
        }
    }
    
    public function clearListaItem() {  
        foreach ($this->getListaItems() as $item) {
            $this->removeListaItem($item);
        }
        return $this;
    }
    
    public function addListaItem(CorteVentaTalonarioItem $item) {  
       $item->setCorteVentaTalonario($this);
       $this->getListaItems()->add($item);
       return $this;
    }
    
    public function removeListaItem(CorteVentaTalonarioItem $item) {  
       $this->getListaItems()->removeElement($item); 
       $item->setCorteVentaTalonario(null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTalonario() {
        return $this->talonario;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getInicial() {
        return $this->inicial;
    }

    public function getFinal() {
        return $this->final;
    }

    public function getImporteTotal() {
        return $this->importeTotal;
    }

    public function getListaItems() {
        return $this->listaItems;
    }

    public function getImporteTotalItems() {
        return $this->importeTotalItems;
    }

    public function getEstacionCreacion() {
        return $this->estacionCreacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTalonario($talonario) {
        $this->talonario = $talonario;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setInicial($inicial) {
        $this->inicial = $inicial;
    }

    public function setFinal($final) {
        $this->final = $final;
    }

    public function setImporteTotal($importeTotal) {
        $this->importeTotal = $importeTotal;
    }

    public function setListaItems($listaItems) {
        $this->listaItems = $listaItems;
    }

    public function setImporteTotalItems($importeTotalItems) {
        $this->importeTotalItems = $importeTotalItems;
    }

    public function setEstacionCreacion($estacionCreacion) {
        $this->estacionCreacion = $estacionCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }
    
    public function getInspector() {
        return $this->inspector;
    }

    public function setInspector($inspector) {
        $this->inspector = $inspector;
    }
    
    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    } 
}

?>