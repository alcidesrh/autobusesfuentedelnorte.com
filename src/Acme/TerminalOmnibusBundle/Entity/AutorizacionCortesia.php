<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\BackendBundle\Services\UtilService;

/**
 * @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\AutorizacionCortesiaRepository")
 * @ORM\Table(name="autorizacion_cortesia")
 * @ORM\HasLifecycleCallbacks
 * @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
 * @Assert\Callback(methods={"validacionesGenerales"})
 */
class AutorizacionCortesia
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ServicioEstacion")
     * @ORM\JoinColumn(name="servicioEstacion", referencedColumnName="id")   
     */
    protected $servicioEstacion;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(      
     *      max = "255",
     *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres de largo"
     * )
     */
    protected $motivo;

    /**
     * @Assert\NotBlank(message = "El codigo no debe estar en blanco")
     * @Assert\Length(
     *      min = "1",
     *      max = "20",
     *      minMessage = "El codigo por lo menos debe tener {{ limit }} caracteres.",
     *      maxMessage = "El codigo no puede tener más de {{ limit }} caracteres."
     * )    
     * @ORM\Column(type="string", length=20, unique=true)
     */
    protected $codigo;

    /**
     * @ORM\Column(name="notificar_cliente", type="boolean")
     */
    protected $notificarCliente;   //En este caso se exisge que exista la restricción de cliente para enviarle email al cliente con el código.

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

    /******************     RESTRICCIONES - INIT    **************************************/
    /**
     * @ORM\ManyToOne(targetEntity="ClaseAsiento")
     * @ORM\JoinColumn(name="restriccion_clase_asiento", referencedColumnName="id", nullable=true)   
     */
    protected $restriccionClaseAsiento;

    /**
     * @Assert\Date(message = "Tiempo no valido")
     * @ORM\Column(name="restriccionFechaUso", type="date", nullable=true)
     */
    protected $restriccionFechaUso;

    /**
     * @ORM\ManyToOne(targetEntity="Estacion")
     * @ORM\JoinColumn(name="restriccion_estacion_origen_id", referencedColumnName="id", nullable=true)   
     */
    protected $restriccionEstacionOrigen;

    /**
     * @ORM\ManyToOne(targetEntity="Estacion")
     * @ORM\JoinColumn(name="restriccion_estacion_destino_id", referencedColumnName="id", nullable=true)   
     */
    protected $restriccionEstacionDestino;

    //    /**
    //    * @ORM\ManyToOne(targetEntity="Ruta")
    //    * @ORM\JoinColumn(name="restriccion_ruta", referencedColumnName="codigo", nullable=true)   
    //    */
    //    protected $restriccionRuta;   

    /**
     * @ORM\ManyToOne(targetEntity="Cliente")
     * @ORM\JoinColumn(name="restriccion_cliente", referencedColumnName="id", nullable=true)   
     */
    protected $restriccionCliente; //En estos casos el boleto o encomienda solo puede ser a nombre de un solo cliente que seria este.

    /**
     * @ORM\ManyToOne(targetEntity="Salida")
     * @ORM\JoinColumn(name="restriccion_salida_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")   
     */
    protected $restriccionSalida;

    /**
     * @ORM\ManyToOne(targetEntity="AsientoBus")
     * @ORM\JoinColumn(name="restriccion_asiento_bus_id", referencedColumnName="id", nullable=true)        
     */
    protected $restriccionAsientoBus;

    /**
     * @ORM\OneToOne(targetEntity="Boleto", mappedBy="autorizacionCortesia")
     */
    protected $boleto;
    /******************     RESTRICCIONES - INIT    **************************************/

    /**
     * @ORM\Column(type="boolean")
     */
    protected $activo;

    /*
     * VALIDACION QUE SI SE MARCO NOTIFICACION AUTOMATICA, SE TENGA QUE PONER LA RESTRICCIÓN DE CLIENTE Y EL MISMO DEBE TENER CORREO.
     * VALIDACION QUE SI SE SELECCIONA SERVICIO DE ENCOMIENDA, QUE NO SE ESPECIFIQUE LA CLASE DEL ASIENTO.
     * VALIDACION QUE SI SE ESPECIFICA UNA RESTRICCION DE FECHA QUE SEA IGUAL A LA DEL DIA O FUTURA..
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        if ($this->notificarCliente === true) {
            if ($this->restriccionCliente === null) {
                $context->addViolation("Para efectuar el aviso automático por correo se debe especificar el cliente en las restricciones.");
            } else {
                $correo = $this->restriccionCliente->getCorreo();
                if ($correo === null || trim($correo) === "") {
                    $context->addViolation("El cliente no tiene definida ninguna cuenta de correo en el sistema.");
                }
            }
        }

        if ($this->servicioEstacion->getId() === "2" && $this->restriccionClaseAsiento !== null) {
            $context->addViolation("Para el envío de encomienda no se puede especificar la clase del asiento.");
        }
        if ($this->servicioEstacion->getId() === "2" && $this->restriccionSalida !== null) {
            $context->addViolation("Para el envío de encomienda no se puede especificar la salida.");
        }
        if ($this->servicioEstacion->getId() === "2" && $this->restriccionAsientoBus !== null) {
            $context->addViolation("Para el envío de encomienda no se puede especificar el asiento del bus.");
        }
        if ($this->servicioEstacion->getId() === "2" && $this->boleto !== null) {
            $context->addViolation("Para el envío de encomienda no se puede especificar el boleto.");
        }

        if ($this->activo === true && $this->restriccionFechaUso !== null && $this->restriccionFechaUso !== "") {
            $fechaActual = new \DateTime(); //Cargar de la BD
            if (is_string($this->restriccionFechaUso)) {
                $fechaActual = $fechaActual->format('d-m-Y ');
                $fechaUsoStr = \DateTime::createFromFormat('Y-m-d', $this->restriccionFechaUso)->format('d-m-Y');
                if (UtilService::compararFechas($fechaActual, $fechaUsoStr) > 0) {
                    $context->addViolation("La restricción de fecha tiene que ser mayor o igual a la fecha actual del sistema:" . $fechaActual . ".");
                }
            } else {
                $fechaActual = new \DateTime(); //Cargar de la BD
                if (UtilService::compararFechas($fechaActual, $this->restriccionFechaUso) > 0) {
                    $context->addViolation("La restricción de fecha tiene que ser mayor o igual a la fecha actual del sistema:" . $fechaActual->format("d/m/Y") . ".");
                }
            }
        }

        //        if($this->restriccionRuta !== null){
        //            if($this->restriccionRuta->getEstacionOrigen() !== $this->restriccionEstacionOrigen){
        //                $context->addViolation("La estación origen de la ruta debe ser la misma que la estación origen de la restricción.");
        //            }
        //            if($this->restriccionRuta->getEstacionDestino() !== $this->restriccionEstacionDestino){
        //                $context->addViolation("La estación destino de la ruta debe ser la misma que la estación destino de la restricción.");
        //            }
        //        }

        if ($this->restriccionSalida !== null) {
            if ($this->restriccionFechaUso === null || $this->restriccionFechaUso === "") {
                $context->addViolation("Si escoge una salida debe seleccionar una fecha.");
            } else {
                $fechaSalida = $this->restriccionSalida->getFecha();
                if (is_string($this->restriccionFechaUso)) {
                    $restriccionFechaUsoStr = \DateTime::createFromFormat('Y-m-d', $this->restriccionFechaUso)->format('d-m-Y');
                    if (UtilService::compararFechas($fechaSalida, $restriccionFechaUsoStr) !== 0) {
                        $context->addViolation("La fecha de la salida debe ser la misma que la fecha de la restricción.");
                    }
                } else {
                    if (UtilService::compararFechas($fechaSalida, $this->restriccionFechaUso) !== 0) {
                        $context->addViolation("La fecha de la salida debe ser la misma que la fecha de la restricción.");
                    }
                }
            }
            if ($this->restriccionEstacionOrigen === null) {
                $context->addViolation("Si escoge una salida debe seleccionar una estación origen.");
            }
            if ($this->restriccionEstacionDestino === null) {
                $context->addViolation("Si escoge una salida debe seleccionar una estación destino.");
            }
            if ($this->restriccionAsientoBus === null) {
                $context->addViolation("Si escoge una salida debe seleccionar una asiento de bus.");
            }
        }

        if ($this->restriccionAsientoBus !== null) {
            if ($this->restriccionSalida === null) {
                $context->addViolation("Si escoge un asiento de bus debe seleccionar una salida.");
            } else {
                if ($this->restriccionSalida->getTipoBus() !== $this->restriccionAsientoBus->getTipoBus()) {
                    $context->addViolation("El tipo de bus de la salida debe ser el mismo que el tipo de bus de la asiento.");
                }
            }
            if ($this->restriccionClaseAsiento === null) {
                $context->addViolation("Si escoge un asiento de bus debe seleccionar la clase.");
            } else {
                if ($this->restriccionClaseAsiento !== $this->restriccionAsientoBus->getClase()) {
                    $context->addViolation("La clase del asiento debe ser igual a la clase de asiento de la restricción.");
                }
            }
            if ($this->boleto === null) {
                $context->addViolation("Si escoge un asiento de bus el sistema debe generar el boleto automáticamente.");
            }
            if ($this->activo === false) {
                if ($this->boleto->getEstado()->getId() !== EstadoBoleto::CANCELADO && $this->boleto->getEstado()->getId() !== EstadoBoleto::ANULADO) {
                    $context->addViolation("No se puede desactivar una cortesía que está asociada a un boleto activo.");
                }
            }
        }
    }

    public function __toString()
    {
        if ($this->motivo != null && trim($this->motivo) != "") {
            return strval($this->id) . " - " . $this->motivo;
        } else {
            return strval($this->id);
        }
    }

    function __construct()
    {
        $this->activo = true;
        $this->notificarCliente = false;
        $this->restriccionFechaUso = null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getServicioEstacion()
    {
        return $this->servicioEstacion;
    }

    public function getMotivo()
    {
        return $this->motivo;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getNotificarCliente()
    {
        return $this->notificarCliente;
    }

    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    public function getUsuarioCreacion()
    {
        return $this->usuarioCreacion;
    }

    public function getFechaUtilizacion()
    {
        return $this->fechaUtilizacion;
    }

    public function getUsuarioUtilizacion()
    {
        return $this->usuarioUtilizacion;
    }

    public function getRestriccionClaseAsiento()
    {
        return $this->restriccionClaseAsiento;
    }

    public function getRestriccionFechaUso()
    {
        return $this->restriccionFechaUso;
    }

    public function getRestriccionEstacionOrigen()
    {
        return $this->restriccionEstacionOrigen;
    }

    public function getRestriccionEstacionDestino()
    {
        return $this->restriccionEstacionDestino;
    }

    //    public function getRestriccionRuta() {
    //        return $this->restriccionRuta;
    //    }

    public function getRestriccionCliente()
    {
        return $this->restriccionCliente;
    }

    public function getRestriccionSalida()
    {
        return $this->restriccionSalida;
    }

    public function getRestriccionAsientoBus()
    {
        return $this->restriccionAsientoBus;
    }

    public function getBoleto()
    {
        return $this->boleto;
    }

    public function getActivo()
    {
        return $this->activo;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setServicioEstacion($servicioEstacion)
    {
        $this->servicioEstacion = $servicioEstacion;
    }

    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    public function setNotificarCliente($notificarCliente)
    {
        $this->notificarCliente = $notificarCliente;
    }

    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion)
    {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaUtilizacion($fechaUtilizacion)
    {
        $this->fechaUtilizacion = $fechaUtilizacion;
    }

    public function setUsuarioUtilizacion($usuarioUtilizacion)
    {
        $this->usuarioUtilizacion = $usuarioUtilizacion;
    }

    public function setRestriccionClaseAsiento($restriccionClaseAsiento)
    {
        $this->restriccionClaseAsiento = $restriccionClaseAsiento;
    }

    public function setRestriccionFechaUso($restriccionFechaUso)
    {
        $this->restriccionFechaUso = $restriccionFechaUso;
    }

    public function setRestriccionEstacionOrigen($restriccionEstacionOrigen)
    {
        $this->restriccionEstacionOrigen = $restriccionEstacionOrigen;
    }

    public function setRestriccionEstacionDestino($restriccionEstacionDestino)
    {
        $this->restriccionEstacionDestino = $restriccionEstacionDestino;
    }

    //    public function setRestriccionRuta($restriccionRuta) {
    //        $this->restriccionRuta = $restriccionRuta;
    //    }

    public function setRestriccionCliente($restriccionCliente)
    {
        $this->restriccionCliente = $restriccionCliente;
    }

    public function setRestriccionSalida($restriccionSalida)
    {
        $this->restriccionSalida = $restriccionSalida;
    }

    public function setRestriccionAsientoBus($restriccionAsientoBus)
    {
        $this->restriccionAsientoBus = $restriccionAsientoBus;
    }

    public function setBoleto($boleto)
    {
        $this->boleto = $boleto;
    }

    public function setActivo($activo)
    {
        $this->activo = $activo;
    }
}
