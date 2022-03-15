<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Security\Core\Validator\Constraint as SecurityAssert;

/**
* @Assert\Callback(methods={"isPasswordValid"})
*/
class CambiarContrasenaUsuarioModel {
    
    /**
    * @Assert\NotBlank(message = "La contraseña actual no debe estar en blanco.")
    * @SecurityAssert\UserPassword(
    *     message = "El contraseña actual es incorrecta."
    * )
    * @Assert\Length(
    *      min = "2",
    *      max = "25",
    *      minMessage = "La contraseña actual debe tener {{ limit }} caracteres como mínimo.",
    *      maxMessage = "La contraseña actual debe tener {{ limit }} caracteres como máximo."
    * ) 
    */
    protected $contrasenaAnterior;
    
    /**
    * @Assert\NotBlank(message = "La contraseña nueva no debe estar en blanco.")   
    * @Assert\Length(
    *      min = "8",
    *      max = "25",
    *      minMessage = "La contraseña debe tener {{ limit }} caracteres como mínimo.",
    *      maxMessage = "La contraseña debe tener {{ limit }} caracteres como máximo."
    * )
    */
    protected $contrasenaNueva;
    
    public function isPasswordValid(ExecutionContext $context)
    {
        if(strlen($this->contrasenaNueva) < 6){
            $context->addViolationAt('contrasenaNueva', 'La clave debe tener al menos 8 caracteres.', array(), null);
            return false;
        }
        if(strlen($this->contrasenaNueva) > 25){
            $context->addViolationAt('contrasenaNueva', 'La clave no puede tener más de 25 caracteres.', array(), null);
            return false;
        }
        if (preg_match_all('/[a-z]/', $this->contrasenaNueva, $a1) < 1){
            $context->addViolationAt('contrasenaNueva', 'La clave debe tener al menos una letra minúscula.', array(), null);
            return false;
        }
        if (preg_match_all('/[A-Z]/', $this->contrasenaNueva, $a2) < 1){
            $context->addViolationAt('contrasenaNueva', 'La clave debe tener al menos una letra mayúscula.', array(), null);
            return false;
        }
        if (preg_match_all('/[0-9]/', $this->contrasenaNueva, $a3) < 1){
            $context->addViolationAt('contrasenaNueva', 'La clave debe tener al menos un caracter numérico.', array(), null);
            return false;
        }
//        if (preg_match_all('/[!@#$%^&*()\-_=+{};:,<.>]/', $this->contrasenaNueva, $a4) < 1){
//            $context->addViolationAt('contrasenaNueva', 'La clave debe tener al menos un caracter especial de la lista [!@#$%^&*()\-_=+{};:,<.>].', array(), null);
//            return false;
//        }
        if($this->contrasenaAnterior === $this->contrasenaNueva){
            $context->addViolationAt('contrasenaNueva', 'La clave nueva no puede ser igual que la anterior.', array(), null);
            return false;
        }
        return true;
    }
    
    public function getContrasenaAnterior() {
        return $this->contrasenaAnterior;
    }

    public function getContrasenaNueva() {
        return $this->contrasenaNueva;
    }

    public function setContrasenaAnterior($contrasenaAnterior) {
        $this->contrasenaAnterior = $contrasenaAnterior;
    }

    public function setContrasenaNueva($contrasenaNueva) {
        $this->contrasenaNueva = $contrasenaNueva;
    }


}