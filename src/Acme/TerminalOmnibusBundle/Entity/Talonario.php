<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity
* @ORM\Table(name="talonario")
* @ORM\HasLifecycleCallbacks
*/
class Talonario{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Tarjeta", inversedBy="listaTalonarios")
    * @ORM\JoinColumn(name="tarjeta_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    protected $tarjeta;
    
    /**
    * @Assert\NotNull(message = "El numero inicial del talonario no debe estar en blanco")
    * @Assert\Range(
    *      min = 1,
    *      max = 9999999999,
    *      minMessage = "El numero inicial del talonario no puede ser menor que {{ limit }}",
    *      maxMessage = "El numero inicial del talonario no puede ser mayor que {{ limit }}"
    * )
    * @ORM\Column(name="inicial", type="bigint", nullable=false)
    */
    protected $inicial;
    
    /**
    * @Assert\NotNull(message = "El numero final del talonario no debe estar en blanco")
    * @Assert\Range(
    *      min = 1,
    *      max = 9999999999,
    *      minMessage = "El numero final del talonario no puede ser menor que {{ limit }}",
    *      maxMessage = "El numero final del talonario no puede ser mayor que {{ limit }}"
    * )
    * @ORM\Column(name="final", type="bigint", nullable=false)
    */
    protected $final;
    
    /**
    * @ORM\OneToMany(targetEntity="CorteVentaTalonario", mappedBy="talonario", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaCorteVentaTalonario;
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - INIT
    //------------------------------------------------------------------------------  
    /**
    * @Assert\NotBlank(message = "La fecha de creacion no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
    */
    protected $fechaCreacion;
    
    /**
    * @Assert\NotNull(message = "El usuario de creacion no debe estar en null")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuarioCreacion;
    
    function __construct($usuarioCreacion = null) {
        $this->fechaCreacion = new \DateTime();
        $this->usuarioCreacion = $usuarioCreacion;
        $this->listaCorteVentaTalonario = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function addListaCorteVentaTalonario(CorteVentaTalonario $item) {  
       $item->setTalonario($this);
       $this->getListaCorteVentaTalonario()->add($item);
       return $this;
    }
    
    public function removeListaCorteVentaTalonario(CorteVentaTalonario $item) {  
       $this->getListaCorteVentaTalonario()->removeElement($item); 
       $item->setTalonario(null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTarjeta() {
        return $this->tarjeta;
    }

    public function getInicial() {
        return $this->inicial;
    }

    public function getFinal() {
        return $this->final;
    }

    public function getListaCorteVentaTalonario() {
        return $this->listaCorteVentaTalonario;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTarjeta($tarjeta) {
        $this->tarjeta = $tarjeta;
    }

    public function setInicial($inicial) {
        $this->inicial = $inicial;
    }

    public function setFinal($final) {
        $this->final = $final;
    }

    public function setListaCorteVentaTalonario($listaCorteVentaTalonario) {
        $this->listaCorteVentaTalonario = $listaCorteVentaTalonario;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }
}

?>