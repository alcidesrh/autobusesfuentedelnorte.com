<?php

namespace Acme\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\BackendBundle\Repository\LogCodeRepository")
* @ORM\Table(name="custom_log_code")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
*/
class LogCode {
    
    /**
    * @Assert\NotBlank(message = "El código no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "6",
    *      minMessage = "El código por lo menos debe tener {{ limit }} carácter.",
    *      maxMessage = "El código no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/\d/",
    *     match=true,
    *     message="El código solo puede contener números"
    * )
    * @ORM\Id
    * @ORM\Column(type="string", length=6, unique=true)
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $codigo;
    
    /**
    * @Assert\NotBlank(message = "La descripción no debe estar en blanco")
    * @ORM\Column(type="text")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $descripcion;
    
    public function __toString() {
//        if ($this->descripcion != null && trim($this->descripcion) != "") {
//            return trim($this->codigo) . " - " . $this->descripcion;
//        } else {
            return $this->codigo;
//        }
    }
    
    public function getCodigo() {
        return $this->codigo;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
}

?>