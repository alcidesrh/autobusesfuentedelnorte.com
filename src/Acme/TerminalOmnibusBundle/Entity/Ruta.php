<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\RutaRepository")
* @ORM\Table(name="ruta")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class Ruta implements \Acme\BackendBundle\Entity\IJobSync{
    
    /**
    * @Assert\NotBlank(message = "El código no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "6",
    *      minMessage = "El código por lo menos debe tener {{ limit }} carácter.",
    *      maxMessage = "El código no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/\d/",
    *     match=true,
    *     message="El código solo puede contener números"
    * )
    * @ORM\Id
    * @ORM\Column(type="string", length=6, unique=true)
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $codigo;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255)
    */
    protected $nombre;
    
    /**
    * @Assert\NotBlank(message = "Los kilometros no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d+$/",
    *     match=true,
    *     message="Los kilometros solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999",
    *      minMessage = "Los kilometros no debe ser menor que {{ limit }}.",
    *      maxMessage = "Los kilometros no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "Los kilometros debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $kilometros;
    
    /**
    * @Assert\NotNull(message = "La estación de origen no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id")        
    */
    protected $estacionOrigen;
    
    /**
    * @Assert\NotNull(message = "La estación destino no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id")        
    */
    protected $estacionDestino;
    
    /**
    * @ORM\ManyToMany(targetEntity="Estacion")
    * @ORM\JoinTable(name="ruta_estaciones_intermedias",
    *   joinColumns={@ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo")},
    *   inverseJoinColumns={@ORM\JoinColumn(name="estacion_id", referencedColumnName="id")}
    * )
    */
    protected $listaEstacionesIntermedia;
    
    /**
    * @ORM\OneToMany(targetEntity="RutaEstacionItem", mappedBy="ruta", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaEstacionesIntermediaOrdenadas;

    /**
    * @ORM\Column(type="text")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $descripcion;
    
    /**
    * @ORM\Column(name="internacional", type="boolean", nullable=true)
    */
    protected $internacional;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $obligatorioClienteDetalle;
    
    /**
    * @Assert\Length(
    *      max = "10",
    *      maxMessage = "El codigo de frontera no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=10, nullable=true)
    */
    protected $codigoFrontera;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    function __construct() {
        $this->activo = true;
        $this->internacional = false;
        $this->obligatorioClienteDetalle = false;
        $this->listaEstacionesIntermedia = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listaEstacionesIntermediaOrdenadas = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["codigo"] = $this->codigo;
        $data["nombre"] = $this->nombre;
        $data["internacional"] = $this->internacional;
        $data["obligatorioClienteDetalle"] = $this->obligatorioClienteDetalle;
        $data["origen"] = $this->estacionOrigen->getId();
        $data["destino"] = $this->estacionDestino->getId();
        $intermedias = array();
        foreach ($this->listaEstacionesIntermediaOrdenadas as $item) {
            $intermedias[] = array(
                "estacion" => $item->getEstacion()->getId(),
                "posicion" => $item->getPosicion()
            );       
        }
        $data["intermedias"] = $intermedias;
        $data["activa"] = $this->activo;
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
    
    public function getNivelSync(){
        return 3;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_RUTA;
    }
    
    /*
     * VALIDACION DE QUE LA ESTACION DE ORIGEN Y DESTINO NO ESTE EN LA LISTA DE ESTACIONES INTERMEDIAS.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
          if($this->estacionOrigen === $this->estacionDestino){
               $context->addViolation("La estación de origen no puede ser igual a la estación destino.");   
          }
          
          $estacionesIntermedia = $this->getListaEstacionesIntermedia();
          if(in_array($this->estacionOrigen, $estacionesIntermedia)){
               $context->addViolation("La estación de origen no se puede seleccionar como una estación intermedia de la lista ordenada.");   
          }
          if(in_array($this->estacionDestino, $estacionesIntermedia)){
               $context->addViolation("La estación de destino no se puede seleccionar como una estación intermedia de la lista ordenada.");   
          }
          if(count(array_unique($estacionesIntermedia)) !== count($estacionesIntermedia))
          {
              $context->addViolation("Existen al menos una estación intermedia duplicada.");
          }
    }
    
    public function addListaEstacionesIntermediaOrdenadas(RutaEstacionItem $item) {  
       $item->setRuta($this);
       $this->getListaEstacionesIntermediaOrdenadas()->add($item);
       return $this;
    }
    
    public function removeListaEstacionesIntermediaOrdenadas(RutaEstacionItem $item) {       
        $this->getListaEstacionesIntermediaOrdenadas()->removeElement($item); 
        $item->setRuta(null);
    }
    
    public function getListaEstacionesIntermedia($order = false){
        $items = array();
        if($order === false){
            foreach ($this->listaEstacionesIntermediaOrdenadas as $item) {
                $items[] = $item->getEstacion();
            }
        }else{
            $itemsArray = $this->listaEstacionesIntermediaOrdenadas->toArray();
            usort($itemsArray, function($a, $b){
                return intval($a->getPosicion()) === intval($b->getPosicion()) ? 0 : ( intval($a->getPosicion()) > intval($b->getPosicion()) ) ? 1 : -1;
            });
            foreach ($itemsArray as $item) {
                $items[] = $item->getEstacion();
            }
        }
        
        return $items;
    }
    
    public function existeEnEstacionesIntermedia($estacion){
        foreach ($this->listaEstacionesIntermediaOrdenadas as $item) {
            if(intval($item->getEstacion()->getId()) == intval($estacion->getId())){
                return true;
            }
        }
        return false;
    }
    
    public function getListaTodasEstaciones($order = false){
        $items = array_merge(array($this->estacionOrigen), $this->getListaEstacionesIntermedia($order));
        $items[] = $this->estacionDestino;
        return $items;
    }

    public function __toString() {
        return $this->getCodigoName();
    }
    
    public function getCodigoName() {
        return strval($this->codigo) . " - " . $this->nombre;
    }
    
    public function getCodigo() {
        return $this->codigo;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getKilometros() {
        return $this->kilometros;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }
    
    public function getObligatorioClienteDetalle() {
        return $this->obligatorioClienteDetalle;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setKilometros($kilometros) {
        $this->kilometros = $kilometros;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    public function setObligatorioClienteDetalle($obligatorioClienteDetalle) {
        $this->obligatorioClienteDetalle = $obligatorioClienteDetalle;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getCodigoFrontera() {
        return $this->codigoFrontera;
    }

    public function setCodigoFrontera($codigoFrontera) {
        $this->codigoFrontera = $codigoFrontera;
    }
        
    public function getListaEstacionesIntermediaOrdenadas() {
        return $this->listaEstacionesIntermediaOrdenadas;
    }

    public function setListaEstacionesIntermediaOrdenadas($listaEstacionesIntermediaOrdenadas) {
        $this->listaEstacionesIntermediaOrdenadas = $listaEstacionesIntermediaOrdenadas;
    }
    
    public function getInternacional() {
        return $this->internacional;
    }

    public function setInternacional($internacional) {
        $this->internacional = $internacional;
    }
}

?>