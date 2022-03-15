<?php

namespace Acme\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\BackendBundle\Repository\SistemaRepository")
* @ORM\Table(name="sistema")
* @ORM\HasLifecycleCallbacks
*/
class Sistema {
    
    /**
    * @Assert\NotBlank(message = "El código no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "40",
    *      minMessage = "El código por lo menos debe tener {{ limit }} carácter.",
    *      maxMessage = "El código no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El código solo puede contener números"
    * )
    * @ORM\Id
    * @ORM\Column(type="string", length=40, nullable=false)
    * @ORM\GeneratedValue(strategy="NONE")
    */
    protected $codigo;
    
    /**
    * @ORM\Id
    * @ORM\ManyToOne(targetEntity="Acme\TerminalOmnibusBundle\Entity\Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id" , nullable=false)        
    */
    protected $estacion;
    
    /**
    * @Assert\NotBlank(message = "El valor no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El valor por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El valor no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=false)
    */
    protected $valor;
    
    public function __toString() {
        return "Codigo:" . $this->codigo . ", Estacion:" . $this->estacion->getId() . ", Valor:" . $this->valor;
    }
    
    public function getCodigo() {
        return $this->codigo;
    }

    public function getValor() {
        return $this->valor;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setValor($valor) {
        $this->valor = $valor;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }
}
