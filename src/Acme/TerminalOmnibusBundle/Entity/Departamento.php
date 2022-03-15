<?php
namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity
* @ORM\Table(name="departamento")
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
* @ORM\HasLifecycleCallbacks
*/
class Departamento implements \Acme\BackendBundle\Entity\IJobSync{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="smallint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "50",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
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
    
    public function getDataArrayToSync(){
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["nombre"] = $this->nombre;
        $data["activo"] = $this->activo;
        return $data;
    }
    
    public function isValidToSync(){
        return true;
    }
    
    public function getNivelSync(){
        return 1;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_DEPARTAMENTO;
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
}

?>