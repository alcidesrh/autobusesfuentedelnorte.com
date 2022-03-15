<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ImpresoraOperacionesRepository")
* @ORM\Table(name="impresora_auxiliares")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"estacion"}, message="Ya existe una configuración de impresoras para esa estación.")
*/
class ImpresoraOperaciones{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
   /**
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)
    */
    protected $estacion;
    
     /**
    * @ORM\ManyToOne(targetEntity="Impresora")
    * @ORM\JoinColumn(name="impresora_boleto_id", referencedColumnName="id", nullable=true)
    */
    protected $impresoraBoleto;
    
    /**
    * @ORM\ManyToOne(targetEntity="Impresora")
    * @ORM\JoinColumn(name="impresora_encomienda_id", referencedColumnName="id", nullable=true)
    */
    protected $impresoraEncomienda;
    
    public function __toString() {
        $str = "";
        if($this->estacion !== null){
            $str .= $this->estacion->__toString() . "|";
        }
        return $str;
    }
    
    function __construct() {

    }
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        if($this->impresoraBoleto !== null && 
           $this->impresoraBoleto->getEstacion() !== null && 
           $this->impresoraBoleto->getEstacion() !== $this->getEstacion()){
            $context->addViolation("La estación de la configuración no coincide con la estación de la impresora.");
        }
        if($this->impresoraEncomienda !== null && 
           $this->impresoraEncomienda->getEstacion() !== null && 
           $this->impresoraEncomienda->getEstacion() !== $this->getEstacion()){
            $context->addViolation("La estación de la configuración no coincide con la estación de la impresora.");
        }
    }
    
    public function getId() {
        return $this->id;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getImpresoraBoleto() {
        return $this->impresoraBoleto;
    }

    public function getImpresoraEncomienda() {
        return $this->impresoraEncomienda;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setImpresoraBoleto($impresoraBoleto) {
        $this->impresoraBoleto = $impresoraBoleto;
    }

    public function setImpresoraEncomienda($impresoraEncomienda) {
        $this->impresoraEncomienda = $impresoraEncomienda;
    }
}

?>