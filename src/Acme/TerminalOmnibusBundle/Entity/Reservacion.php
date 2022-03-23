<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\EstadoReservacion;

/**
 * @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ReservacionRepository")
 * @ORM\Table(name="reservacion", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_RESERVACION_SALIDA_ASIENTO_ESTADO", columns={"salida_id", "asiento_bus_id", "estado_id"})})
 * @ORM\HasLifecycleCallbacks
 * @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
 */
class Reservacion
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="externa", type="boolean", nullable=true)
     */
    protected $externa;

    /**
     * @Assert\Length(      
     *      max = "30",
     *      maxMessage = "La referencia externa no puede tener más de {{ limit }} caracteres de largo"
     * )
     * @ORM\Column(name="referencia_externa", type="string", length=30, nullable=true)
     */
    protected $referenciaExterna;

    /**
     * @Assert\NotBlank(message = "El asiento bus no debe estar en blanco")
     * @ORM\ManyToOne(targetEntity="AsientoBus")
     * @ORM\JoinColumn(name="asiento_bus_id", referencedColumnName="id", nullable=false)        
     */
    protected $asientoBus;

    /**
     * @ORM\ManyToOne(targetEntity="Cliente")
     * @ORM\JoinColumn(name="cliente", referencedColumnName="id", nullable=false)   
     */
    protected $cliente;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\TerminalOmnibusBundle\Entity\Salida")
     * @ORM\JoinColumn(name="salida_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")  
     */
    protected $salida;

    /**
     * @Assert\NotNull(message = "El estado no debe estar en blanco")
     * @ORM\ManyToOne(targetEntity="Acme\TerminalOmnibusBundle\Entity\EstadoReservacion")
     * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
     */
    protected $estado;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(      
     *      max = "255",
     *      maxMessage = "La observación no puede tener más de {{ limit }} caracteres de largo"
     * )
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

    function __construct()
    {
        $this->externa = false;
    }

    public function __toString()
    {
        return "ID:" . strval($this->id) . "|" . $this->salida . "|" . $this->asientoBus . "|" . $this->estado;
    }

    /*
    * VALIDACION 
    */
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        //        var_dump("validacionesGenerales-init");
        if (
            $this->estado->getId() !== EstadoReservacion::CANCELADA &&
            $this->estado->getId() !== EstadoReservacion::VENCIDA
        ) {
            if (!$this->salida->getTipoBus()->getListaAsiento()->contains($this->asientoBus)) {
                $context->addViolation("El asiento de bus: " . $this->asientoBus->getNumero() . " no está dentro de los asientos permitidos para el tipo de bus: " . $this->salida->getTipoBus()->getAlias() . ".");
            }
        }
        if ($this->id !== null && trim($this->id) !== "" && trim($this->id) !== "0") {
            $doctrine = $container->get("doctrine");
            $estadoActual = $doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoReservacion')->getEstadoReservacion($this->id);
            if ($this->estado->getId() === EstadoReservacion::CANCELADA) {
                if ($estadoActual->getId() !== EstadoReservacion::EMITIDA) {
                    $context->addViolation("Solamente se puede cancelar una reservación que este en estado emitida. El estado actual es: " . $estadoActual->getNombre() . ".");
                }
                if ($this->observacion === null || trim($this->observacion) === "") {
                    $context->addViolation("Debe especificar una observación cuando la reservación está en estado cancelada.");
                }
            }
        }
        if ($this->externa === false) {
            $ruta = $this->salida->getItinerario()->getRuta();
            if ($ruta->getObligatorioClienteDetalle() === true) {
                if ($this->cliente->getDetallado() === false) {
                    $mensaje =  "La reservación del cliente " . $this->cliente->getInfo2() . " "
                        . "está asociado a una ruta que requiere datos detallados del cliente. "
                        . "Por favor actualice el cliente con identificador " . $this->cliente->getId() . " y especifique todos los datos requeridos.";
                    $context->addViolation($mensaje);
                }
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getExterna()
    {
        return $this->externa;
    }

    public function getAsientoBus()
    {
        return $this->asientoBus;
    }

    public function getCliente()
    {
        return $this->cliente;
    }

    public function getSalida()
    {
        return $this->salida;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function getObservacion()
    {
        return $this->observacion;
    }

    public function getEstacionCreacion()
    {
        return $this->estacionCreacion;
    }

    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion()
    {
        return $this->usuarioCreacion;
    }

    public function getFechaActualizacion()
    {
        return $this->fechaActualizacion;
    }

    public function getUsuarioActualizacion()
    {
        return $this->usuarioActualizacion;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setExterna($externa)
    {
        $this->externa = $externa;
    }

    public function setAsientoBus($asientoBus)
    {
        $this->asientoBus = $asientoBus;
    }

    public function setCliente($cliente)
    {
        $this->cliente = $cliente;
    }

    public function setSalida($salida)
    {
        $this->salida = $salida;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    }

    public function setEstacionCreacion($estacionCreacion)
    {
        $this->estacionCreacion = $estacionCreacion;
    }

    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion)
    {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaActualizacion($fechaActualizacion)
    {
        $this->fechaActualizacion = $fechaActualizacion;
    }

    public function setUsuarioActualizacion($usuarioActualizacion)
    {
        $this->usuarioActualizacion = $usuarioActualizacion;
    }

    public function getReferenciaExterna()
    {
        return $this->referenciaExterna;
    }

    public function setReferenciaExterna($referenciaExterna)
    {
        $this->referenciaExterna = $referenciaExterna;
    }
}
