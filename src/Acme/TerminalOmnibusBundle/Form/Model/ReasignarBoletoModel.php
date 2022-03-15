<?php
namespace Acme\TerminalOmnibusBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Acme\TerminalOmnibusBundle\Form\Model\EmitirBoletoModel;

class ReasignarBoletoModel extends EmitirBoletoModel{
    
     /**
    * @Assert\NotBlank(message = "El boleto original no puede estar en blanco.")     
    */
    protected $boletoOriginal;
    
    public function __construct() { 
        parent::__construct();
    }
    
    public function getBoletoOriginal() {
        return $this->boletoOriginal;
    }

    public function setBoletoOriginal($boletoOriginal) {
        $this->boletoOriginal = $boletoOriginal;
    }
}