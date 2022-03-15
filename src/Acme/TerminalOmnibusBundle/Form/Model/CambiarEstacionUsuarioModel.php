<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CambiarEstacionUsuarioModel {
    
    /**
    * @Assert\NotBlank(message = "La estación no debe estar en blanco.")
    */
    protected $estacion;
    
    public function getEstacion() {
        return $this->estacion;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }    
}