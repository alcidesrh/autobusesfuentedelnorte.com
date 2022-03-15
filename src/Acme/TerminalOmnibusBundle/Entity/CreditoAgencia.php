<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\CreditoAgenciaRepository")
* @ORM\Table(name="agencia_credito")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="agencia", message="Ya existe un crédito para esa agencia.")
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class CreditoAgencia{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "La agencia no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacion;
    
    /**
    * @Assert\NotNull(message = "El importe no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El importe no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe debe ser un número válido."
    * )   
    * @ORM\Column(name="importe", type="decimal", precision=7, scale=2, nullable=false)
    */
    protected $importe;

    public function validacionesGenerales(ExecutionContext $context, $container)
    {
       if($this->estacion->getAgencia() === false){
           $context->addViolation("No se puede crear una crédito de una estación que no sea agencia.");
       }
       if($this->estacion->getCreditoAgencia() === false){
           $context->addViolation("No se puede crear una crédito a una estación que no este configurada para ese fin.");
       } 
    }
    
    public function getId() {
        return $this->id;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }
}

?>