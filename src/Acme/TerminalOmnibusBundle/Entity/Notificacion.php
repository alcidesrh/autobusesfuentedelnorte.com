<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\NotificacionRepository")
* @ORM\Table(name="notificacion")
* @ORM\HasLifecycleCallbacks
*/
class Notificacion{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El texto no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "300",
    *      minMessage = "El texto por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El texto no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=300)
    */
    protected $texto;
    
    /**
    * @Assert\NotBlank(message = "Los segundos no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El segundos solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "1",
    *      max = "30",
    *      minMessage = "Los segundos no debe ser menor que {{ limit }}.",
    *      maxMessage = "Los segundos  no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "Los segundos debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $segundos;
    
    /**
    * @ORM\Column(name="oficinas", type="boolean")
    */
    protected $oficinas;
    
    /**
    * @ORM\Column(name="agencias", type="boolean")
    */
    protected $agencias;   
    
    /**
    * @ORM\Column(name="activo", type="boolean")
    */
    protected $activo;
    
    function __construct() {
        $this->oficinas = true;
        $this->agencias = true;
        $this->activo = true;
        $this->segundos = 10;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTexto() {
        return $this->texto;
    }

    public function getSegundos() {
        return $this->segundos;
    }

    public function getOficinas() {
        return $this->oficinas;
    }

    public function getAgencias() {
        return $this->agencias;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTexto($texto) {
        $this->texto = $texto;
    }

    public function setSegundos($segundos) {
        $this->segundos = $segundos;
    }

    public function setOficinas($oficinas) {
        $this->oficinas = $oficinas;
    }

    public function setAgencias($agencias) {
        $this->agencias = $agencias;
    }
    
    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
}

?>