<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Entity\Itinerario;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ItinerarioCiclicoRepository")
* @ORM\Table(name="itinerario_ciclico")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields = {"ruta" , "empresa", "tipoBus", "diaSemana", "horarioCiclico"}, 
* message="Ya existe un itinerario cíclico en esa ruta, empresa, día y hora.")
*/
class ItinerarioCiclico extends Itinerario implements \Acme\BackendBundle\Entity\IJobSync{

    /**
    * @Assert\NotNull(message = "El día de la semana no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="DiaSemana")
    * @ORM\JoinColumn(name="dia_semana_id", referencedColumnName="id")        
    */
    protected $diaSemana;
    
    /**
    * @Assert\NotNull(message = "El horario ciclico no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="HorarioCiclico")
    * @ORM\JoinColumn(name="horario_ciclico_id", referencedColumnName="id")        
    */
    protected $horarioCiclico;
    
    function __construct() {
        parent::__construct();
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["tipoBus"] = $this->tipoBus->getId();
        $data["ruta"] = $this->ruta->getCodigo();
        $data["diaSemana"] = $this->diaSemana->getId();
        $data["horarioCiclico"] = $this->horarioCiclico->getId();
        $data["activo"] = $this->activo;
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
    
    public function getNivelSync(){
        return 4;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_ITINERARIO_CICLICO;
    }
    
    public function __toString() {
        $str = "Cíclico";
        if($this->getRuta() !== null){
            $str .= "|Ruta:" . $this->getRuta()->getCodigo();
        }
        if($this->diaSemana !== null && $this->horarioCiclico !== null){
            $str .= "|Horario:" . $this->diaSemana . " - " . $this->horarioCiclico;
        }
        if($this->tipoBus !== null){
            $str .= "|TipoBus:" . $this->tipoBus->getAlias();
        }
        if($this->empresa !== null){
            $str .= "|Empresa:" . $this->empresa->getAlias();
        }
        return $str;
    }
    
    public function getTipoStr() {
        return "Cíclico";
    }
    
    public function getDiaSemana() {
        return $this->diaSemana;
    }

    public function getHorarioCiclico() {
        return $this->horarioCiclico;
    }

    public function setDiaSemana($diaSemana) {
        $this->diaSemana = $diaSemana;
    }

    public function setHorarioCiclico($horarioCiclico) {
        $this->horarioCiclico = $horarioCiclico;
    }
}

?>