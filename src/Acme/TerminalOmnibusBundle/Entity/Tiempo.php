<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TiempoRepository")
* @ORM\Table(name="tiempo")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields ={"ruta", "estacionDestino", "claseBus"}, message="Ya existe un tiempo para esa ruta, estación y clase de bus.")
*/
class Tiempo implements \Acme\BackendBundle\Entity\IJobSync{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La ruta no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Ruta")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo")   
    */
    protected $ruta;
    
    /**
    * @Assert\NotNull(message = "La estación destino no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id")        
    */
    protected $estacionDestino;
    
    /**
    * @Assert\NotBlank(message = "La clase no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseBus")
    * @ORM\JoinColumn(name="clasebus_id", referencedColumnName="id")        
    */
    protected $claseBus;
    
    /**
    * @Assert\NotBlank(message = "Los minutos no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El minutos solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999",
    *      minMessage = "Los minutos no debe ser menor que {{ limit }}.",
    *      maxMessage = "Los minutos  no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "Los minutos debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $minutos;
    
    function __construct() {
        $this->minutos = 0;
    }
    
    public function __toString() {
        return $this->ruta . " - " . $this->estacionDestino . " - " . $this->claseBus;
    }
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        $existe = false;
        if($this->ruta->getEstacionOrigen() === $this->estacionDestino || $this->ruta->getEstacionDestino() === $this->estacionDestino){
            $existe = true;
        }else{
            foreach ($this->ruta->getListaEstacionesIntermediaOrdenadas() as $item) {
                if($item->getEstacion() === $this->estacionDestino){
                    $existe = true;
                    break;
                }
            }   
        }
        
        if($existe === false){
            $context->addViolation("La estación " . $this->estacionDestino . " no se encuentra dentro de las estaciones de la ruta " . $this->ruta->getCodigoName() . ".");
        }
        
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["ruta"] = $this->ruta->getCodigo();
        $data["estacionDestino"] = $this->estacionDestino->getId();
        $data["claseBus"] = $this->claseBus->getId();
        $data["minutos"] = $this->minutos;
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
    
    public function getNivelSync(){
        return 4;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_TIEMPO;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getRuta() {
        return $this->ruta;
    }

    public function getClaseBus() {
        return $this->claseBus;
    }

    public function getMinutos() {
        return $this->minutos;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setClaseBus($claseBus) {
        $this->claseBus = $claseBus;
    }

    public function setMinutos($minutos) {
        $this->minutos = $minutos;
    }
    
    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }
}

?>