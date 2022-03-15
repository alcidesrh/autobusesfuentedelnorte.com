<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UsuarioModel {
    
    /**
    * @Assert\NotBlank(message = "El usuario no debe estar en blanco.")
    */
    protected $user;
    
    /**
    * @Assert\Length(
    *      min = "2",
    *      max = "25",
    *      minMessage = "La contraseña debe tener {{ limit }} caracteres como mínimo.",
    *      maxMessage = "La contraseña debe tener {{ limit }} caracteres como máximo."
    * )
    */
    protected $plainPassword;
    
    protected $estacion;
    
    protected $todasEstaciones;
    protected $desbloquear;
    
    public function getUser() {
        return $this->user;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
    }
    
    public function getEstacion() {
        return $this->estacion;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }
    
    public function getDesbloquear() {
        return $this->desbloquear;
    }

    public function setDesbloquear($desbloquear) {
        $this->desbloquear = $desbloquear;
    }
    
    public function getTodasEstaciones() {
        return $this->todasEstaciones;
    }

    public function setTodasEstaciones($todasEstaciones) {
        $this->todasEstaciones = $todasEstaciones;
    }
}