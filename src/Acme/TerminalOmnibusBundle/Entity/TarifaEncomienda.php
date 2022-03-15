<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarifaRepository")
* @ORM\Table(name="tarifas_encomienda")
* @ORM\InheritanceType("JOINED")
* @ORM\DiscriminatorColumn(name="tipoTarifa", type="integer")
* @ORM\DiscriminatorMap({
* 1  = "TarifaEncomiendaEfectivo",
* 2  = "TarifaEncomiendaEspeciales",
* 3  = "TarifaEncomiendaPaquetesPeso",
* 4  = "TarifaEncomiendaPaquetesVolumen",
* 5  = "TarifaEncomiendaDistancia"})
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
*/
abstract class TarifaEncomienda implements Tarifa{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    // ----------------- DATOS INTERNOS -------------------------
    /**
    * @Assert\NotBlank(message = "La fecha de creacion no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
    */
    protected $fechaCreacion;
    
    /**
    * @Assert\NotNull(message = "El usuario de creacion no debe estar en null")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion", referencedColumnName="id", nullable=false)        
    */
    protected $usuarioCreacion;
    
    // ----------------- DATOS INTERNOS -------------------------
    
     /*
     * VALIDACION 
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
       
    }
    
    function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
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

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }
}

?>