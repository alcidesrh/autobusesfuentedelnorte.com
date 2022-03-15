<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\AutorizacionInternaRepository")
* @ORM\Table(name="autorizacion_interna")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class AutorizacionInterna{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\Column(type="text")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $motivo;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "20",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=20, unique=true)
    */
    protected $codigo;
    
    /******************     DATOS INTERNOS SISTEMA - INIT    **************************************/
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime")
    */
    protected $fechaCreacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion", referencedColumnName="id")        
    */
    protected $usuarioCreacion;
    
    /**
    * @Assert\NotBlank(message = "La estación no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id")   
    */
    protected $estacion;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_utilizacion", type="datetime", nullable=true)
    */
    protected $fechaUtilizacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_utilizacion", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioUtilizacion;
    /******************     DATOS INTERNOS SISTEMA - END    **************************************/
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    /*
     * VALIDACION 
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
       
    }
    
    public function __toString() {
        $str = "ID:" . strval($this->id);
        return $str;
    }
    
    public function getInfo1() {
        return $this->__toString();
    }
    
    public function getAutorizo() {
        return $this->usuarioCreacion->getFullName();
    }
    
    function __construct() {
        $this->activo = true;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getMotivo() {
        return $this->motivo;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getFechaUtilizacion() {
        return $this->fechaUtilizacion;
    }

    public function getUsuarioUtilizacion() {
        return $this->usuarioUtilizacion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setMotivo($motivo) {
        $this->motivo = $motivo;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setFechaUtilizacion($fechaUtilizacion) {
        $this->fechaUtilizacion = $fechaUtilizacion;
    }

    public function setUsuarioUtilizacion($usuarioUtilizacion) {
        $this->usuarioUtilizacion = $usuarioUtilizacion;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

}

?>