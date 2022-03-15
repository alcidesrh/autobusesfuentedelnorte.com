<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\HorarioCiclicoRepository")
* @ORM\Table(name="horario_ciclico")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="hora", message="La hora ya existe")
*/
class HorarioCiclico implements \Acme\BackendBundle\Entity\IJobSync{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
    * @Assert\Time(message = "Hora no valida")
    * @ORM\Column(type="time", unique=true)
    */
    protected $hora;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;

    public function __toString() {
        if($this->hora !== null){
            return date_format($this->hora, "H:i");
        }
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["hora"] = $this->hora;
        $data["activo"] = $this->activo;
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
   
    public function getNivelSync(){
        return 2;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_HORARIO_CICLICO;
    }
    
    function __construct() {
        $this->activo = true;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setHora($hora) {
        $this->hora = $hora;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }


}

?>