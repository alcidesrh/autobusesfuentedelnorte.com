<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TipoCambioRepository")
* @ORM\Table(name="tipo_cambio")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields = {"moneda" , "fecha", "tipoTipoCambio"}, 
* message="Ya existe un tipo de cambio para la combinación especficada de fecha, moneda y tipo de tipo cambio.")
*/
class TipoCambio{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "La fecha no debe estar en blanco")
    * @Assert\Date(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="date", nullable=false)
    */
    protected $fecha;
    
    /**
    * @Assert\NotNull(message = "La moneda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Moneda")
    * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id", nullable=false)        
    */
    protected $moneda;

    /**
    * @Assert\NotNull(message = "El tipo de Tipo de Cambio no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoTipoCambio")
    * @ORM\JoinColumn(name="tipo_tipo_cambio_id", referencedColumnName="id", nullable=false)        
    */
    protected $tipoTipoCambio;
    
    /**
    * @Assert\NotBlank(message = "La tasa no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/((^\d{0,2}$)|(^\d{1,2}[\.|,]\d{1,8}$))/",
    *     match=true,
    *     message="La tasa solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0.00000001",
    *      max = "99.99999999",
    *      minMessage = "El valor no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=10, scale=8, nullable=false)
    */
    protected $tasa;
    
    
    public function __toString() {
        $str = "";
        if($this->moneda !== null){
            $str .= $this->moneda->getSigla() . "|";
        }
        if($this->tipoTipoCambio !== null){
            $str .= $this->tipoTipoCambio->getNombre() . "|";
        }
        if($this->fecha !== null){
            if(is_string($this->fecha)){
                $str .= $this->fecha . "|";
            }else{
                $str .= $this->fecha->format("d-m-Y") . "|" ;
            }
        }
        if($this->tasa !== null){
            $str .= $this->tasa . "|";
        }
        return $str;
    }
    
    public function getInfo1() {
        $str = "";
        if($this->moneda !== null){
            $str .= $this->moneda->getSigla() . "|";
        }
        if($this->tipoTipoCambio !== null){
            $str .= $this->tipoTipoCambio->getNombre() . "|";
        }
        if($this->fecha !== null){
            if(is_string($this->fecha)){
                $str .= $this->fecha . "|";
            }else{
                $str .= $this->fecha->format("d-m-Y") . "|";
            }
        }
        if($this->tasa !== null){
            $str .= $this->tasa;
        }
        return $str;
    }
    
    function __construct() {
        $this->fecha = new \DateTime();
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getMoneda() {
        return $this->moneda;
    }

    public function getTipoTipoCambio() {
        return $this->tipoTipoCambio;
    }

    public function getTasa() {
        return $this->tasa;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setMoneda($moneda) {
        $this->moneda = $moneda;
    }

    public function setTipoTipoCambio($tipoTipoCambio) {
        $this->tipoTipoCambio = $tipoTipoCambio;
    }

    public function setTasa($tasa) {
        $this->tasa = $tasa;
    }
}

?>