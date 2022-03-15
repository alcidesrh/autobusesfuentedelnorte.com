<?php

namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

class AutorizacionInternaMultiplesModel {

    /**
    * @Assert\NotBlank(message = "La cantidad no debe estar en blanco")
    * @Assert\Range(
    *      min = "1",
    *      max = "20",
    *      minMessage = "La cantidad no debe ser menor que {{ limit }}.",
    *      maxMessage = "La cantidad no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "La cantidad debe ser un número válido."
    * )
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="La cantidad solo puede contener números"
    * )
    */
    protected $cantidad;
    
    /**
    * @Assert\NotBlank(message = "El motivo no debe estar en blanco")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $motivo;
    
    public function __construct() {
        $this->cantidad = 1;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getMotivo() {
        return $this->motivo;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function setMotivo($motivo) {
        $this->motivo = $motivo;
    }
}
