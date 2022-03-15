<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ClaseBusRepository")
* @ORM\Table(name="bus_clase")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
*/
class ClaseBus{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "25",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=25, unique=true)
    */
    protected $nombre;
    
    /**
    * @ORM\ManyToMany(targetEntity="ClaseAsiento")
    * @ORM\JoinTable(name="bus_clase_union_asiento_clase",
    *   joinColumns={@ORM\JoinColumn(name="clasebus_id", referencedColumnName="id")},
    *   inverseJoinColumns={@ORM\JoinColumn(name="claseasiento_id", referencedColumnName="id")}
    * )
    */
    protected $listaClaseAsiento;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return $this->nombre;
    }
    
    function __construct() {
        $this->listaClaseAsiento = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getListaClaseAsiento() {
        return $this->listaClaseAsiento;
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

    public function setListaClaseAsiento($listaClaseAsiento) {
        $this->listaClaseAsiento = $listaClaseAsiento;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
}

?>