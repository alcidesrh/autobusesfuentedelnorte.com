<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\EncomiendaRutaRepository")
* @ORM\Table(name="encomienda_ruta")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class EncomiendaRuta {
   
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Encomienda", inversedBy="rutas")
    * @ORM\JoinColumn(name="encomienda_id", referencedColumnName="id", nullable=false)
    */
    protected $encomienda;
    
    /**
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     message="El número solo puede contener números"
    * ) 
    * @ORM\Column(type="integer")
    */
    protected $posicion;
    
    /**
    * @Assert\NotNull(message = "La ruta de la encomienda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Ruta")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo", nullable=false)   
    */
    protected $ruta;
    
    /**
    * @Assert\NotNull(message = "La estación destino de la ruta en la encomienda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion", referencedColumnName="id", nullable=false)   
    */
    protected $estacionDestino;
    
    function __construct() {
        $this->posicion = 0;
    }
    
    public function __toString() {
        $str = "";
        if($this->ruta !== null){
            $str .= "|Ruta:" . $this->ruta;
        }
        
        if($this->estacion !== null){
            $str .= "|Estación:" . $this->estacionDestino;
        }
        return $str;
    }
    
    public function validacionesGenerales(ExecutionContext $context)
    {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getEncomienda() {
        return $this->encomienda;
    }

    public function getRuta() {
        return $this->ruta;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEncomienda($encomienda) {
        $this->encomienda = $encomienda;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }
    
    public function getPosicion() {
        return $this->posicion;
    }

    public function setPosicion($posicion) {
        $this->posicion = $posicion;
    }
}
