<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Context\LegacyExecutionContext;

/**
* @ORM\Entity
* @ORM\Table(name="banco_cuenta")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class CuentaBanco {
    
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)        
    */
    protected $empresa;
    
    /**
    * @ORM\ManyToOne(targetEntity="Banco")
    * @ORM\JoinColumn(name="banco_id", referencedColumnName="id", nullable=false)        
    */
    protected $banco;
    
    /**
    * @Assert\Length(
    *      max = "100",
    *      maxMessage = "La referencia no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="referencia_externa", type="string", length=100, nullable=true)
    */
    protected $referenciaExterna;
    
    /**
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=true)
    */
    protected $nombre;

    /**
    * @ORM\ManyToOne(targetEntity="Moneda")
    * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id", nullable=true)
    */
    protected $moneda;
    
    /**
    * @ORM\Column(name="activo", type="boolean")
    */
    protected $activo;
    
    public function getId() {
        return $this->id;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getBanco() {
        return $this->banco;
    }

    public function getReferenciaExterna() {
        return $this->referenciaExterna;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getMoneda() {
        return $this->moneda;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setBanco($banco) {
        $this->banco = $banco;
    }

    public function setReferenciaExterna($referenciaExterna) {
        $this->referenciaExterna = $referenciaExterna;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setMoneda($moneda) {
        $this->moneda = $moneda;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
}
