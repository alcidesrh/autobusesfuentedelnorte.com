<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity
* @ORM\Table(name="pais")
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
* @ORM\HasLifecycleCallbacks
*/
class Pais{
    
    const GUATEMALA = 1;

    /**
    * @ORM\Id
    * @ORM\Column(type="smallint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "La sigla del pais no debe estar en blanco")
    * @Assert\Length(
    *      min = "3",
    *      max = "3",
    *      minMessage = "La sigla del pais por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La sigla del pais no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=3, nullable=true)
    */
    protected $sigla;
    
    /**
    * @Assert\NotBlank(message = "El nombre del pais no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "50",
    *      minMessage = "El nombre del pais por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre del pais no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=50, unique=true)
    */
    protected $nombre;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return $this->nombre;
    }
    
    function __construct() {
        $this->activo = true;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getSigla() {
        return $this->sigla;
    }

    public function setSigla($sigla) {
        $this->sigla = $sigla;
    }
}

?>