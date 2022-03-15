<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Acme\TerminalOmnibusBundle\Entity\FechaAlquiler;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\AlquilerRepository")
* @ORM\Table(name="alquiler")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class Alquiler{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_inicial", type="datetime", nullable=false)
    */
    protected $fechaInicial;
    
   /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_final", type="datetime", nullable=false)
    */
    protected $fechaFinal;
    
    /**
    * @Assert\NotNull(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)        
    */
    protected $empresa;
    
    /**
    * @ORM\ManyToOne(targetEntity="Bus")
    * @ORM\JoinColumn(name="bus_codigo", referencedColumnName="codigo", nullable=false)        
    */
    protected $bus;
    
    /**
    * @ORM\ManyToOne(targetEntity="Piloto")
    * @ORM\JoinColumn(name="piloto_id", referencedColumnName="id", nullable=false)        
    */
    protected $piloto;
    
    /**
    * @ORM\ManyToOne(targetEntity="Piloto")
    * @ORM\JoinColumn(name="piloto_aux_id", referencedColumnName="id", nullable=true)        
    */
    protected $pilotoAux;
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoAlquiler")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El importe no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe debe ser un número válido."
    * )   
    * @ORM\Column(name="importe", type="decimal", precision=7, scale=2, nullable=false)
    */
    protected $importe;
    
    /**
    * @ORM\Column(type="text", nullable=false)
    * @Assert\Length(      
    *      max = "1000",
    *      maxMessage = "La observación no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $observacion;
    
    /**
    * @ORM\OneToMany(targetEntity="FechaAlquiler", mappedBy="alquiler", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaFechas;
    
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - INIT
    //------------------------------------------------------------------------------  
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
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_efectuado", type="datetime", nullable=true)
    */
    protected $fechaEfectuado;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_efectuado_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioEfectuado;
    
    /**
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_efectuado_id", referencedColumnName="id", nullable=true)   
    */
    protected $estacionEfectuado;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_cancelado", type="datetime", nullable=true)
    */
    protected $fechaCancelado;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_cancelado_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioCancelado;
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - END
    //------------------------------------------------------------------------------
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
       if($this->bus !== null){
           if($this->bus->getEmpresa() !== $this->empresa){
               $context->addViolation("El bus ".$this->bus." no pertenece a la empresa " . $this->empresa . "." );
           }
       }
       if($this->piloto !== null){
           if($this->piloto->getEmpresa() !== $this->empresa){
               $context->addViolation("El piloto " .$this->piloto." no pertenece a la empresa " . $this->empresa . "." );
           }
       }
       if($this->pilotoAux !== null){
           if($this->pilotoAux->getEmpresa() !== $this->empresa){
               $context->addViolation("El piloto " .$this->pilotoAux." no pertenece a la empresa " . $this->empresa . "." );
           }
       }
       if($this->piloto === $this->pilotoAux){
           $context->addViolation("El piloto 1 y el piloto 2 deben ser diferentes." );
       }
    }
    
    function __construct() {
        $this->listaFechas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fechaInicial = new \DateTime();
        $this->fechaFinal = new \DateTime();
        $this->importe = 0;        
    }
    
    public function addListaFechaAlquiler(FechaAlquiler $item) {  
       $item->setAlquiler($this);
       $this->getListaFechas()->add($item); 
       return $this;
    }
    
    public function removeListaFechaAlquiler($item) {       
        $this->getListaFechas()->removeElement($item); 
        $item->setAlquiler(null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getFechaInicial() {
        return $this->fechaInicial;
    }

    public function getFechaFinal() {
        return $this->fechaFinal;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getBus() {
        return $this->bus;
    }

    public function getPiloto() {
        return $this->piloto;
    }

    public function getPilotoAux() {
        return $this->pilotoAux;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getObservacion() {
        return $this->observacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getFechaEfectuado() {
        return $this->fechaEfectuado;
    }

    public function getUsuarioEfectuado() {
        return $this->usuarioEfectuado;
    }

    public function getEstacionEfectuado() {
        return $this->estacionEfectuado;
    }

    public function getFechaCancelado() {
        return $this->fechaCancelado;
    }

    public function getUsuarioCancelado() {
        return $this->usuarioCancelado;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFechaInicial($fechaInicial) {
        $this->fechaInicial = $fechaInicial;
    }

    public function setFechaFinal($fechaFinal) {
        $this->fechaFinal = $fechaFinal;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setBus($bus) {
        $this->bus = $bus;
    }

    public function setPiloto($piloto) {
        $this->piloto = $piloto;
    }

    public function setPilotoAux($pilotoAux) {
        $this->pilotoAux = $pilotoAux;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }

    public function setObservacion($observacion) {
        $this->observacion = $observacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaEfectuado($fechaEfectuado) {
        $this->fechaEfectuado = $fechaEfectuado;
    }

    public function setUsuarioEfectuado($usuarioEfectuado) {
        $this->usuarioEfectuado = $usuarioEfectuado;
    }

    public function setEstacionEfectuado($estacionEfectuado) {
        $this->estacionEfectuado = $estacionEfectuado;
    }

    public function setFechaCancelado($fechaCancelado) {
        $this->fechaCancelado = $fechaCancelado;
    }

    public function setUsuarioCancelado($usuarioCancelado) {
        $this->usuarioCancelado = $usuarioCancelado;
    }
    
    public function getListaFechas() {
        return $this->listaFechas;
    }

    public function setListaFechas($listaFechas) {
        $this->listaFechas = $listaFechas;
    }
}

?>