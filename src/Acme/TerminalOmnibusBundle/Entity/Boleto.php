<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\Salida;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Acme\TerminalOmnibusBundle\Entity\BoletoBitacora;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\BoletoRepository")
 * @ORM\Table(name="boleto", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_BOLETO_SALIDA_ASIENTO_ESTADO", columns={"salida_id", "asiento_bus_id", "estado_id"})})
 * @ORM\HasLifecycleCallbacks
 * @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
 * @DoctrineAssert\UniqueEntity(fields = {"facturaGenerada"}, message="Ya existe un boleto asociado a esa factura.")
 * @DoctrineAssert\UniqueEntity(fields = {"autorizacionCortesia"}, message="Ya existe un boleto asociado a esa autorización de cortesía.")
 */
class Boleto
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\Length(
     *      max = "80",
     *      maxMessage = "El identificador web no puede tener más de {{ limit }} caracteres."
     * )    
     * @ORM\Column(name="identificador_web", type="string", length=80, nullable=true)
     */
    protected $identificadorWeb;

    /**
     * @ORM\ManyToOne(targetEntity="AsientoBus")
     * @ORM\JoinColumn(name="asiento_bus_id", referencedColumnName="id", nullable=true)        
     */
    protected $asientoBus;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $revendidoEnEstacion; //Casos 1 y 2 de reventa

    /**
     * @ORM\Column(type="boolean")
     */
    protected $revendidoEnCamino; //Caso 3 de reventa

    /**
     * @ORM\OneToOne(targetEntity="Boleto")
     * @ORM\JoinColumn(name="reasignado_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $reasignado;

    /**
     * @ORM\ManyToOne(targetEntity="Cliente")
     * @ORM\JoinColumn(name="cliente_documento", referencedColumnName="id", nullable=false)   
     */
    protected $clienteDocumento;

    /**
     * @ORM\ManyToOne(targetEntity="Cliente")
     * @ORM\JoinColumn(name="cliente_boleto", referencedColumnName="id", nullable=false)   
     */
    protected $clienteBoleto;

    /**
     * @ORM\ManyToOne(targetEntity="Salida", cascade={"persist"})
     * @ORM\JoinColumn(name="salida_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")   
     */
    protected $salida;

    /**
     * @ORM\ManyToOne(targetEntity="TipoPago")
     * @ORM\JoinColumn(name="tipo_pago_id", referencedColumnName="id", nullable=true)   
     */
    protected $tipoPago;

    /**
     * @Assert\NotNull(message = "La estación de origen no debe estar en blanco")
     * @ORM\ManyToOne(targetEntity="Estacion")
     * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id", nullable=false)   
     */
    protected $estacionOrigen;

    /**
     * @Assert\NotNull(message = "La estación de destino no debe estar en blanco")
     * @ORM\ManyToOne(targetEntity="Estacion")
     * @ORM\JoinColumn(name="estacion_destino_id", referencedColumnName="id", nullable=false)   
     */
    protected $estacionDestino;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(      
     *      max = "255",
     *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres de largo"
     * )
     */
    protected $observacionDestinoIntermedio;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $utilizarDesdeEstacionOrigenSalida;

    //    /**
    //    * @Assert\NotBlank(message = "El código de barra no debe estar en blanco")
    //    * @Assert\Length(
    //    *      min = "1",
    //    *      max = "255",
    //    *      minMessage = "El código de barra por lo menos debe tener {{ limit }} caracteres.",
    //    *      maxMessage = "El código de barra no puede tener más de {{ limit }} caracteres."
    //    * )    
    //    * @ORM\Column(type="string", length=255)
    //    */
    //    protected $codigoBarra;

    /**
     * @Assert\NotNull(message = "El tipo de documento no debe estar en blanco")
     * @ORM\ManyToOne(targetEntity="TipoDocumentoBoleto")
     * @ORM\JoinColumn(name="tipo_documento_id", referencedColumnName="id", nullable=false)   
     */
    protected $tipoDocumento;

    /**
     * @ORM\ManyToOne(targetEntity="TarifaBoleto")
     * @ORM\JoinColumn(name="tarifa_id", referencedColumnName="id", nullable=true)   
     */
    protected $tarifa; //Tarifa utilizada para generar el precio calculado

    /**
     * @Assert\Regex(
     *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
     *     match=true,
     *     message="El precio calculado en la moneda base solo puede contener números"
     * )
     * @Assert\Range(
     *      min = "0",
     *      max = "99999.99",
     *      minMessage = "El precio no debe ser menor que {{ limit }}.",
     *      maxMessage = "El precio no debe ser mayor que {{ limit }}.",
     *      invalidMessage = "El precio debe ser un número válido."
     * )   
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    protected $precioCalculadoMonedaBase; //Puede ser null para las cortesias

    /**
     * @Assert\Regex(
     *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
     *     match=true,
     *     message="La tarifa adicional solo puede contener números"
     * )
     * @Assert\Range(
     *      min = "0",
     *      max = "99999.99",
     *      minMessage = "La tarifa adicional no debe ser menor que {{ limit }}.",
     *      maxMessage = "La tarifa adicional debe ser mayor que {{ limit }}.",
     *      invalidMessage = "La tarifa adicional debe ser un número válido."
     * )   
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    protected $tarifaAdicionalMonedaBase;

    /**
     * @ORM\ManyToOne(targetEntity="Moneda")
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id", nullable=true)   
     */
    protected $moneda;

    /**
     * @ORM\ManyToOne(targetEntity="TipoCambio")
     * @ORM\JoinColumn(name="tipo_cambio_id", referencedColumnName="id", nullable=true)   
     */
    protected $tipoCambio;

    /**
     * @Assert\Regex(
     *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
     *     match=true,
     *     message="El precio calculado solo puede contener números"
     * )
     * @Assert\Range(
     *      min = "0",
     *      max = "99999.99",
     *      minMessage = "El precio no debe ser menor que {{ limit }}.",
     *      maxMessage = "El precio no debe ser mayor que {{ limit }}.",
     *      invalidMessage = "El precio debe ser un número válido."
     * )   
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    protected $precioCalculado; //Puede ser null para las cortesias

    /**
     * @ORM\ManyToOne(targetEntity="FacturaGenerada")
     * @ORM\JoinColumn(name="factura_generada_id", referencedColumnName="id", nullable=true, unique=true, onDelete="SET NULL")   
     */
    protected $facturaGenerada;

    /**
     * @ORM\OneToOne(targetEntity="VoucherAgencia", inversedBy="boleto", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="voucher_agencia_id", referencedColumnName="id", nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $voucherAgencia;

    /**
     * @ORM\OneToOne(targetEntity="VoucherEstacion", inversedBy="boleto", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="voucher_estacion_id", referencedColumnName="id", nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $voucherEstacion;

    /**
     * @ORM\OneToOne(targetEntity="VoucherInternet", inversedBy="boleto", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="voucher_internet_id", referencedColumnName="id", nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $voucherInternet;

    /**
     * @ORM\OneToOne(targetEntity="AutorizacionCortesia", inversedBy="boleto", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="autorizacion_cortesia_id", referencedColumnName="id", nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $autorizacionCortesia;

    /**
     * @Assert\NotNull(message = "El estado no debe estar en blanco")
     * @ORM\ManyToOne(targetEntity="EstadoBoleto")
     * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
     */
    protected $estado;

    /**
     * @Assert\Length(
     *      max = "8",
     *      maxMessage = "El ping de facturacion especial no puede tener más de {{ limit }} caracteres."
     * )    
     * @ORM\Column(name="ping_facturacion_especial", type="string", length=8, nullable=true)
     */
    protected $pingFacturacionEspecial;

    /**
     * @ORM\ManyToOne(targetEntity="Estacion")
     * @ORM\JoinColumn(name="estacion_facturacion_especial", referencedColumnName="id", nullable=true)   
     */
    protected $estacionFacturacionEspecial;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $camino;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(      
     *      max = "255",
     *      maxMessage = "La observación no puede tener más de {{ limit }} caracteres de largo"
     * )
     */
    protected $observacion;

    /**
     * @ORM\OneToMany(targetEntity="BoletoBitacora", mappedBy="boleto", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $bitacoras;
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

    /**
     * @ORM\OneToMany(targetEntity="Encomienda", mappedBy="boleto", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $encomiendas;

    function __construct()
    {
        $this->revendidoEnCamino = false;
        $this->revendidoEnEstacion = false;
        $this->utilizarDesdeEstacionOrigenSalida = true;
        $this->camino = false;
        $this->bitacoras = new ArrayCollection();
        $this->encomiendas = new ArrayCollection();
    }

    public function addBitacoras(BoletoBitacora $item)
    {
        $item->setBoleto($this);
        $this->getBitacoras()->add($item);
        return $this;
    }

    public function __toString()
    {
        $asientoBus = "N/D";
        if ($this->asientoBus !== null) {
            $asientoBus = $this->asientoBus;
        }
        return "ID:" . strval($this->id) . "|" . $this->salida . "|" . $asientoBus . "|" . $this->estado;
    }

    /*
    * VALIDACION 
    */
    public function validacionesGenerales(ExecutionContext $context, $container)
    {

        if ($this->camino === true) {
            if ($this->asientoBus !== null) {
                $context->addViolation("Los boletos de camino no pueden estar asociados a un asiento.");
            }
        } else {
            if ($this->asientoBus === null) {
                $context->addViolation("Los boletos de oficina deben estar asociados a un asiento.");
            }
            if (
                $this->estado->getId() !== EstadoBoleto::ANULADO &&
                $this->estado->getId() !== EstadoBoleto::CANCELADO &&
                $this->estado->getId() !== EstadoBoleto::REASIGNADO
            ) {
                if (!$this->salida->getTipoBus()->getListaAsiento()->contains($this->asientoBus)) {
                    $context->addViolation("El asiento de bus: " . $this->asientoBus->getNumero() . " no está dentro de los asientos permitidos para el tipo de bus: " . $this->salida->getTipoBus()->getAlias() . ".");
                }
            }
        }

        if ($this->estacionOrigen === $this->estacionDestino) {
            $context->addViolation("La estación origen no puede ser igual a la estación destino.");
        }

        if ($this->estacionOrigen === $this->salida->getItinerario()->getRuta()->getEstacionOrigen()) {
            if ($this->utilizarDesdeEstacionOrigenSalida === false) {
                $context->addViolation("Si la estación origen de la ruta es igual a la estación 'Sube en' entonces debe marcar la opción 'Utilizar desde la estación origen de la salida'.");
            }
        }

        if ($this->tipoDocumento->getId() === TipoDocumentoBoleto::AUTORIZACION_CORTESIA) {

            $autorizacionCortesia = $this->autorizacionCortesia;

            //Busqueda en profundidad de la autorizacion
            if ($autorizacionCortesia === null) {
                $autorizacionCortesia = $this->findAutorizacionCortesia($this);
            }

            if ($autorizacionCortesia === null) {
                $context->addViolation("Los boletos de cortesía deben tener una autorización.");
            } else {
                if ($autorizacionCortesia->getActivo() === false) {
                    $context->addViolation("La autorización de cortesía esta deshabilitada.");
                }
                if ($autorizacionCortesia->getServicioEstacion()->getId() !== ServicioEstacion::BOLETO) {
                    $context->addViolation("La autorización de cortesía no esta asociada a los servicios de boletos.");
                }
                /*
                    NO SE PUEDE CHEQUEAR MAS LOS QUE ESTEN REASIGNADOS, PQ EL BOLETO ORIGINAL SE QUEDO CON LA AUTORIZACION
               *    PERO SI SE ACTUALIZA Y SE LE AGREGA UNA RESTRICCION, LUEGO NO HAY FORMA DE COREGIR EL ORIGINAL., DE ESTA
               *    FORMA SE MANTIENE EL CHEQUEO PERO CON EL ULTIMO BOLETO, NUNCA SE LE CHEQUEA LOS VALORES A LOS ANTERIORES.
              */
                if ($this->estado->getId() !== EstadoBoleto::REASIGNADO) {
                    if ($this->camino === false) {
                        $restriccionClaseAsiento =  $autorizacionCortesia->getRestriccionClaseAsiento();
                        if ($restriccionClaseAsiento !== null && $restriccionClaseAsiento !== $this->asientoBus->getClase()) {
                            $context->addViolation("La cortesía tiene una restricción de que solamente se optar por la clase de asiento: " . $restriccionClaseAsiento->getNombre());
                        }
                        $restriccionAsientoBus =  $autorizacionCortesia->getRestriccionAsientoBus();
                        if ($restriccionAsientoBus !== null && $restriccionAsientoBus !== $this->asientoBus) {
                            $context->addViolation("La cortesía tiene una restricción para el asiento de bus: " . $restriccionAsientoBus);
                        }
                    }
                    $restriccionFechaUso =  $autorizacionCortesia->getRestriccionFechaUso();
                    if ($restriccionFechaUso !== null && UtilService::compararFechas($restriccionFechaUso, $this->salida->getFecha()) !== 0) {
                        $context->addViolation("La cortesía tiene una restricción de que solamente se viajar el dia: " . $restriccionFechaUso->format("d-m-Y"));
                    }
                    $restriccionEstacionOrigen =  $autorizacionCortesia->getRestriccionEstacionOrigen();
                    if ($restriccionEstacionOrigen !== null && $restriccionEstacionOrigen !== $this->estacionOrigen) {
                        $context->addViolation("La cortesía tiene una restricción de estación de origen: " . $restriccionEstacionOrigen);
                    }
                    $restriccionEstacionDestino =  $autorizacionCortesia->getRestriccionEstacionDestino();
                    if ($restriccionEstacionDestino !== null && $restriccionEstacionDestino !== $this->estacionDestino) {
                        $context->addViolation("La cortesía tiene una restricción de estación de destino: " . $restriccionEstacionDestino);
                    }
                    $restriccionCliente =  $autorizacionCortesia->getRestriccionCliente();
                    if ($restriccionCliente !== null && $restriccionCliente !== $this->clienteBoleto) {
                        $context->addViolation("La cortesía tiene una restricción de que solamente puede viajar el cliente: " . $restriccionCliente);
                    }
                    $restriccionSalida =  $autorizacionCortesia->getRestriccionSalida();
                    if ($restriccionSalida !== null && $restriccionSalida !== $this->salida) {
                        $context->addViolation("La cortesía tiene una restricción para la salida: " . $restriccionSalida);
                    }
                }
            }

            if ($this->tipoPago !== null) {
                $context->addViolation("Los boletos de cortesía no pueden tener definido el tipo de pago.");
            }
            if ($this->tarifa !== null) {
                $context->addViolation("Los boletos de cortesía no pueden tener definida la tarifa.");
            }
            if ($this->moneda !== null) {
                $context->addViolation("Los boletos de cortesía no pueden tener definida la moneda.");
            }
            if ($this->tipoCambio !== null) {
                $context->addViolation("Los boletos de cortesía no pueden tener definido un tipo de cambio.");
            }
            if ($this->precioCalculado !== null) {
                $context->addViolation("Los boletos de cortesía no pueden tener definido un precio.");
            }
            if ($this->facturaGenerada !== null) {
                $context->addViolation("Los boletos de cortesía no pueden tener definida una factura.");
            }
        } else if ($this->tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA) {

            if ($this->voucherAgencia === null) {
                $context->addViolation("Los boletos agencia deben tener definido el voucher.");
            }
            if ($this->asientoBus !== null) {
                if (($this->voucherAgencia->getBono() === true || $this->voucherAgencia->getBono() === 'true') &&
                    intval($this->asientoBus->getClase()->getId()) === intval(ClaseAsiento::B)
                ) {
                    $context->addViolation("Los boletos de bono no se pueden utilizar en la clase de asiento B.");
                }
                if (($this->voucherAgencia->getBono() === true || $this->voucherAgencia->getBono() === 'true') &&
                    $this->asientoBus->getNumero() <= 12
                ) {
                    $context->addViolation("No se puede utilizar el bono en un asiento con número menor que 12.");
                }
            }
        } else if ($this->tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER || $this->tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_OTRA_ESTACION) {

            if ($this->voucherEstacion === null) {
                $context->addViolation("Le faltan datos internos al voucher de estación.");
            }
            if ($this->tipoPago !== null && $this->tipoPago->getId() !== TipoPago::EFECTIVO) {
                $context->addViolation("Los voucher solamente se pueden pagar en efectivo.");
            }
        } else if ($this->tipoDocumento->getId() === TipoDocumentoBoleto::VOUCHER_INTERNET) {
            if ($this->voucherInternet === null) {
                $context->addViolation("Le faltan datos internos al voucher de internet.");
            }
            if ($this->tipoPago !== null && $this->tipoPago->getId() !== TipoPago::TARJETA) {
                $context->addViolation("Los voucher de internet solamente se pueden pagar por tarjeta.");
            }
        } else {
            if ($this->autorizacionCortesia !== null) {
                $context->addViolation("Los boletos por factura no pueden tener definida una autorización.");
            }
            if ($this->tipoPago === null) {
                $context->addViolation("Los boletos por factura deben tener definido el tipo de pago.");
            }
            if ($this->tarifa === null) {
                $context->addViolation("Los boletos por factura deben tener definida la tarifa.");
            }
            if ($this->moneda === null) {
                $context->addViolation("Los boletos por factura deben tener definida la moneda.");
            }
            if ($this->tipoCambio === null) {
                $context->addViolation("Los boletos por factura deben tener definido un tipo de cambio.");
            }
            if ($this->precioCalculado === null) {
                $context->addViolation("Los boletos por factura deben tener definido un precio.");
            }
            //No se exige que los boletos de factura, si son reasignacion tengan una factura, ya que la reasignacion
            //Si el precio es equivalente, se realiza y no hace falta una factura
            if ($this->facturaGenerada === null && $this->reasignado === null) {
                $context->addViolation("Los boletos por factura deben tener definida una factura.");
            }

            if ($this->tipoPago !== null) {
                if (
                    $this->tipoPago->getId() === TipoPago::TARJETA_CREDITO || $this->tipoPago->getId() === TipoPago::TARJETA_DEBITO
                    || $this->tipoPago->getId() === TipoPago::TARJETA
                ) {
                    if ($this->facturaGenerada !== null) {
                        $autorizacionTarjeta = $this->facturaGenerada->getAutorizacionTarjeta();
                        if ($autorizacionTarjeta === null || trim($autorizacionTarjeta) === "") {
                            $context->addViolation("Debe definir el código de autorización de la tarjeta.");
                        }
                    }
                }
            }
        }

        if ($this->reasignado !== null) {
            if ($this->reasignado === $this) {
                $context->addViolation("No se puede reasignar un boleto consigo mismo.");
            }
        }

        if ($this->estado->getId() === EstadoBoleto::ANULADO) {
            if ($this->observacion === null || trim($this->observacion) === "") {
                $context->addViolation("Debe especificar una observación cuando el boleto está en estado anulado.");
            }
        }

        //No se aplica a los nuevos
        if ($this->id !== null && trim($this->id) !== "" && trim($this->id) !== "0") {
            $doctrine = $container->get("doctrine");
            $estadoActual = $doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoBoleto')->getEstadoBoleto($this->id);
            if ($this->estado->getId() === EstadoBoleto::EMITIDO) {
                if ($estadoActual->getId() !== EstadoBoleto::EMITIDO && $estadoActual->getId() !== EstadoBoleto::CANCELADO) {
                    $context->addViolation("Solamente se puede revertir el estado a los boletos cancelados. El estado actual es: " . $estadoActual->getNombre() . ".");
                }
            } else if ($this->estado->getId() === EstadoBoleto::ANULADO) {
                if ($estadoActual->getId() !== EstadoBoleto::EMITIDO && $estadoActual->getId() !== EstadoBoleto::CHEQUEADO) {
                    $context->addViolation("Solamente se puede anular un boleto que este en estado emitido o chequeado. El estado actual es: " . $estadoActual->getNombre() . ".");
                }
            } else if ($this->estado->getId() === EstadoBoleto::CANCELADO) {
                if ($estadoActual->getId() !== EstadoBoleto::EMITIDO && $estadoActual->getId() !== EstadoBoleto::CHEQUEADO) {
                    $context->addViolation("Solamente se puede cancelar un boleto que este en estado emitido o chequeado. El estado actual es: " . $estadoActual->getNombre() . ".");
                }
            }
        }

        $ruta = $this->salida->getItinerario()->getRuta();
        if ($ruta->getObligatorioClienteDetalle() === true) {
            if ($this->clienteBoleto->getDetallado() === false) {
                $mensaje =  "El boleto del cliente " . $this->clienteBoleto->getInfo2() . " "
                    . "está asociado a una ruta que requiere datos detallados del cliente. "
                    . "Por favor actualice el cliente con identificador " . $this->clienteBoleto->getId() . " y especifique todos los datos requeridos.";
                $context->addViolation($mensaje);
            }
        }

        //        $context->addViolation("Error de prueba."); 
    }

    public function getDocumentoStr()
    {
        $str = $this->tipoDocumento->getNombre();
        if ($this->facturaGenerada !== null) {
            $str .= " (" . $this->facturaGenerada->getInfo2() . ")";
        }
        return $str;
    }

    public function findAutorizacionCortesia(Boleto $boleto)
    {

        if ($boleto->getAutorizacionCortesia() !== null) {
            return $boleto->getAutorizacionCortesia();
        }

        if ($boleto->getReasignado() !== null) {
            return $this->findAutorizacionCortesia($boleto->getReasignado());
        }

        return null;
    }

    public function getAutorizacionCortesiaRecursiva()
    {
        return $this->findAutorizacionCortesia($this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAsientoBus()
    {
        return $this->asientoBus;
    }

    public function getRevendidoEnEstacion()
    {
        return $this->revendidoEnEstacion;
    }

    public function getRevendidoEnCamino()
    {
        return $this->revendidoEnCamino;
    }

    public function getReasignado()
    {
        return $this->reasignado;
    }

    public function getClienteDocumento()
    {
        return $this->clienteDocumento;
    }

    public function getClienteBoleto()
    {
        return $this->clienteBoleto;
    }

    public function getSalida()
    {
        return $this->salida;
    }

    public function getTipoPago()
    {
        return $this->tipoPago;
    }

    public function getEstacionOrigen()
    {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino()
    {
        return $this->estacionDestino;
    }

    public function getObservacionDestinoIntermedio()
    {
        return $this->observacionDestinoIntermedio;
    }

    public function getUtilizarDesdeEstacionOrigenSalida()
    {
        return $this->utilizarDesdeEstacionOrigenSalida;
    }

    public function getTipoDocumento()
    {
        return $this->tipoDocumento;
    }

    public function getTarifa()
    {
        return $this->tarifa;
    }

    public function getPrecioCalculadoMonedaBase()
    {
        return $this->precioCalculadoMonedaBase;
    }

    public function getMoneda()
    {
        return $this->moneda;
    }

    public function getTipoCambio()
    {
        return $this->tipoCambio;
    }

    public function getPrecioCalculado()
    {
        return $this->precioCalculado;
    }

    public function getFacturaGenerada()
    {
        return $this->facturaGenerada;
    }

    public function getVoucherAgencia()
    {
        return $this->voucherAgencia;
    }

    public function getAutorizacionCortesia()
    {
        return $this->autorizacionCortesia;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function getPingFacturacionEspecial()
    {
        return $this->pingFacturacionEspecial;
    }

    public function getEstacionFacturacionEspecial()
    {
        return $this->estacionFacturacionEspecial;
    }

    public function getCamino()
    {
        return $this->camino;
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

    public function setAsientoBus($asientoBus)
    {
        $this->asientoBus = $asientoBus;
    }

    public function setRevendidoEnEstacion($revendidoEnEstacion)
    {
        $this->revendidoEnEstacion = $revendidoEnEstacion;
    }

    public function setRevendidoEnCamino($revendidoEnCamino)
    {
        $this->revendidoEnCamino = $revendidoEnCamino;
    }

    public function setReasignado($reasignado)
    {
        $this->reasignado = $reasignado;
    }

    public function setClienteDocumento($clienteDocumento)
    {
        $this->clienteDocumento = $clienteDocumento;
    }

    public function setClienteBoleto($clienteBoleto)
    {
        $this->clienteBoleto = $clienteBoleto;
    }

    public function setSalida($salida)
    {
        $this->salida = $salida;
    }

    public function setTipoPago($tipoPago)
    {
        $this->tipoPago = $tipoPago;
    }

    public function setEstacionOrigen($estacionOrigen)
    {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino)
    {
        $this->estacionDestino = $estacionDestino;
    }

    public function setObservacionDestinoIntermedio($observacionDestinoIntermedio)
    {
        $this->observacionDestinoIntermedio = $observacionDestinoIntermedio;
    }

    public function setUtilizarDesdeEstacionOrigenSalida($utilizarDesdeEstacionOrigenSalida)
    {
        $this->utilizarDesdeEstacionOrigenSalida = $utilizarDesdeEstacionOrigenSalida;
    }

    public function setTipoDocumento($tipoDocumento)
    {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setTarifa($tarifa)
    {
        $this->tarifa = $tarifa;
    }

    public function setPrecioCalculadoMonedaBase($precioCalculadoMonedaBase)
    {
        $this->precioCalculadoMonedaBase = $precioCalculadoMonedaBase;
    }

    public function setMoneda($moneda)
    {
        $this->moneda = $moneda;
    }

    public function setTipoCambio($tipoCambio)
    {
        $this->tipoCambio = $tipoCambio;
    }

    public function setPrecioCalculado($precioCalculado)
    {
        $this->precioCalculado = $precioCalculado;
    }

    public function setFacturaGenerada($facturaGenerada)
    {
        $this->facturaGenerada = $facturaGenerada;
    }

    public function setVoucherAgencia($voucherAgencia)
    {
        $this->voucherAgencia = $voucherAgencia;
    }

    public function setAutorizacionCortesia($autorizacionCortesia)
    {
        $this->autorizacionCortesia = $autorizacionCortesia;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function setPingFacturacionEspecial($pingFacturacionEspecial)
    {
        $this->pingFacturacionEspecial = $pingFacturacionEspecial;
    }

    public function setEstacionFacturacionEspecial($estacionFacturacionEspecial)
    {
        $this->estacionFacturacionEspecial = $estacionFacturacionEspecial;
    }

    public function setCamino($camino)
    {
        $this->camino = $camino;
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

    public function getVoucherEstacion()
    {
        return $this->voucherEstacion;
    }

    public function setVoucherEstacion($voucherEstacion)
    {
        $this->voucherEstacion = $voucherEstacion;
    }

    public function getTarifaAdicionalMonedaBase()
    {
        return $this->tarifaAdicionalMonedaBase;
    }

    public function setTarifaAdicionalMonedaBase($tarifaAdicionalMonedaBase)
    {
        $this->tarifaAdicionalMonedaBase = $tarifaAdicionalMonedaBase;
    }

    public function getIdentificadorWeb()
    {
        return $this->identificadorWeb;
    }

    public function setIdentificadorWeb($identificadorWeb)
    {
        $this->identificadorWeb = $identificadorWeb;
    }

    public function getVoucherInternet()
    {
        return $this->voucherInternet;
    }

    public function setVoucherInternet($voucherInternet)
    {
        $this->voucherInternet = $voucherInternet;
    }

    public function getBitacoras()
    {
        return $this->bitacoras;
    }

    public function setBitacoras($bitacoras)
    {
        $this->bitacoras = $bitacoras;
    }
}
