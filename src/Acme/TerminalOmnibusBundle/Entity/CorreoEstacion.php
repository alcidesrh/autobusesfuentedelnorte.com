<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\CorreoEstacionRepository")
* @ORM\Table(name="estacion_correo")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="correo", message="El correo ya existe")
*/
class CorreoEstacion{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El correo no debe estar en blanco")
    * @Assert\Email(
    *     message = "El correo '{{ value }}' no es válido.",
    *     checkMX = true,
    *     checkHost = false
    * )
    * @Assert\Length(
    *      min = "3",
    *      max = "60",
    *      minMessage = "El correo por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El correo no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=60, unique=true)
    */
    protected $correo;
    
    /**
    * @ORM\ManyToOne(targetEntity="Estacion", inversedBy="listaCorreo")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id")
    */
    protected $estacion;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return $this->correo;
    }
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

}

?>