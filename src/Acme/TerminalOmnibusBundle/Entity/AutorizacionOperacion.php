<?php
namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\AutorizacionOperacionRepository")
* @ORM\Table(name="autorizacion_operacion", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_AUTORIZACION_ESTACION_BOLETO_TIPO", columns={"estacion_id", "boleto_id", "tipo_id"})})
* @ORM\HasLifecycleCallbacks
*/
class AutorizacionOperacion{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "Para registrar una autorizacion el operador debe pertenecer a una estación")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Boleto")
    * @ORM\JoinColumn(name="boleto_id", referencedColumnName="id", nullable=false)   
    */
    protected $boleto;

    /**
    * @ORM\ManyToOne(targetEntity="TipoAutorizacionOperacion")
    * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id")   
    */
    protected $tipo;
    
    /**
    * @ORM\ManyToOne(targetEntity="EstadoAutorizacionOperacion")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id")   
    */
    protected $estado;
    
    /**
    * @Assert\Length(
    *      max = "150",
    *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="motivo", type="string", length=150, nullable=false)
    */
    protected $motivo;
    
    /**
    * @Assert\Length(
    *      max = "150",
    *      maxMessage = "La observacion no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="observacion", type="string", length=150, nullable=true)
    */
    protected $observacion;

    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - INIT
    //------------------------------------------------------------------------------  
    /**
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_creacion_id", referencedColumnName="id", nullable=true)   
    */
    protected $estacionCreacion;
    
    /**
    * @Assert\NotBlank(message = "La fecha de creacion no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
    */
    protected $fechaCreacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioCreacion;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_actualizacion", type="datetime", nullable=true)
    */
    protected $fechaActualizacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_actualizacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioActualizacion;
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - END
    //------------------------------------------------------------------------------
    function __construct() {
        $this->fechaCreacion = new \DateTime();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getBoleto() {
        return $this->boleto;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getMotivo() {
        return $this->motivo;
    }

    public function getObservacion() {
        return $this->observacion;
    }

    public function getEstacionCreacion() {
        return $this->estacionCreacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getFechaActualizacion() {
        return $this->fechaActualizacion;
    }

    public function getUsuarioActualizacion() {
        return $this->usuarioActualizacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setBoleto($boleto) {
        $this->boleto = $boleto;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setMotivo($motivo) {
        $this->motivo = $motivo;
    }

    public function setObservacion($observacion) {
        $this->observacion = $observacion;
    }

    public function setEstacionCreacion($estacionCreacion) {
        $this->estacionCreacion = $estacionCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaActualizacion($fechaActualizacion) {
        $this->fechaActualizacion = $fechaActualizacion;
    }

    public function setUsuarioActualizacion($usuarioActualizacion) {
        $this->usuarioActualizacion = $usuarioActualizacion;
    }


}

?>