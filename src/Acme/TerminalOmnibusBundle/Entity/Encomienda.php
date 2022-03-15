<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\Common\Collections\ArrayCollection;
use Acme\TerminalOmnibusBundle\Entity\EncomiendaBitacora;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\TipoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\EstadoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\EncomiendaRuta;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\EncomiendaRepository")
* @ORM\Table(name="encomienda")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"autorizacionCortesia"}, message="Ya existe un encomienda asociada a esa autorización de cortesía.")
* @DoctrineAssert\UniqueEntity(fields = {"autorizacionInterna"}, message="Ya existe un encomienda asociada a esa autorización interna.")
*/
class Encomienda {
    
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
    * @Assert\NotBlank(message = "La cantidad no debe estar en blanco")
    * @Assert\Range(
    *      min = "1",
    *      max = "10000",
    *      minMessage = "La cantidad no debe ser menor que {{ limit }}.",
    *      maxMessage = "La cantidad no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "La cantidad debe ser un número válido."
    * )
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="La cantidad solo puede contener números"
    * )
    * @ORM\Column(type="integer", nullable=false)
    */
    protected $cantidad;

    /**
    * @Assert\NotNull(message = "El tipo de encomienda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoEncomienda")
    * @ORM\JoinColumn(name="tipo_encomienda_id", referencedColumnName="id", nullable=false)   
    */
    protected $tipoEncomienda;
    
    /**
    * @Assert\NotNull(message = "La ruta de la encomienda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Ruta")
    * @ORM\JoinColumn(name="ruta_codigo", referencedColumnName="codigo", nullable=false)   
    */
    protected $ruta;
    
    /**
    * @Assert\Range(
    *      min = "0.01",
    *      max = "999.99",
    *      minMessage = "El porciento del valor declarado no debe ser menor que {{ limit }}.",
    *      maxMessage = "El porciento del valor declarado no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El porciento del valor declarado debe ser un número válido."
    * )
    * @ORM\Column(name="valor_declarado_porciento", type="decimal", precision=5, scale=2, nullable=true)
    */
    protected $porcientoValorDeclarado;
    
    /**
    * @Assert\Range(
    *      max = "5000",
    *      maxMessage = "El valor declarado no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor declarado debe ser un número válido."
    * )
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El valor declarado solo puede contener números"
    * )
    * @ORM\Column(name="valor_declarado", type="integer", nullable=true)
    */
    protected $valorDeclarado;
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoEncomiendaEspeciales")
    * @ORM\JoinColumn(name="tipo_encomienda_especial_id", referencedColumnName="id", nullable=true)   
    */
    protected $tipoEncomiendaEspecial;
    
    /**
    * @Assert\Regex(
    *     pattern="/(^\d{0,5}$)/",
    *     match=true,
    *     message="El peso solo puede contener números enteros"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999",
    *      minMessage = "El peso no debe ser menor que {{ limit }}.",
    *      maxMessage = "El peso no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El peso debe ser un número entero válido."
    * )   
    * @ORM\Column(type="decimal", precision=5, scale=0, nullable=true)
    */
    protected $peso;
    
    /**
    * @Assert\Regex(
    *     pattern="/(^\d{0,5}$)/",
    *     match=true,
    *     message="El alto solo puede contener números enteros"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999",
    *      minMessage = "El alto no debe ser menor que {{ limit }}.",
    *      maxMessage = "El alto no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El alto debe ser un número entero válido."
    * )   
    * @ORM\Column(type="decimal", precision=5, scale=0, nullable=true)
    */
    protected $alto;
    
        /**
    * @Assert\Regex(
    *     pattern="/(^\d{0,5}$)/",
    *     match=true,
    *     message="El ancho solo puede contener números enteros"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999",
    *      minMessage = "El ancho no debe ser menor que {{ limit }}.",
    *      maxMessage = "El ancho no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El ancho debe ser un número entero válido."
    * )   
    * @ORM\Column(type="decimal", precision=5, scale=0, nullable=true)
    */
    protected $ancho;
    
     /**
    * @Assert\Regex(
    *     pattern="/(^\d{0,5}$)/",
    *     match=true,
    *     message="La profundidad solo puede contener números enteros"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999",
    *      minMessage = "La profundidad no debe ser menor que {{ limit }}.",
    *      maxMessage = "La profundidad no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "La profundidad debe ser un número entero válido."
    * )   
    * @ORM\Column(type="decimal", precision=5, scale=0, nullable=true)
    */
    protected $profundidad;
    
    /**
    * @Assert\Regex(
    *     pattern="/(^\d{0,10}$)/",
    *     match=true,
    *     message="El volumen solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "9999999999",
    *      minMessage = "El volumen no debe ser menor que {{ limit }}.",
    *      maxMessage = "El volumen no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El volumen debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
    */
    protected $volumen;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    * @Assert\Length(      
    *      max = "150",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $descripcion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Cliente")
    * @ORM\JoinColumn(name="cliente_remitente", referencedColumnName="id", nullable=false)   
    */
    protected $clienteRemitente;
    
    /**
    * @ORM\ManyToOne(targetEntity="Cliente")
    * @ORM\JoinColumn(name="cliente_destinatario", referencedColumnName="id", nullable=false)   
    */
    protected $clienteDestinatario;
    
    /**
    * @ORM\ManyToOne(targetEntity="Cliente")
    * @ORM\JoinColumn(name="cliente_documento", referencedColumnName="id", nullable=true)   
    */
    protected $clienteDocumento;
    
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
    * @Assert\NotNull(message = "El tipo de documento no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoDocumentoEncomienda")
    * @ORM\JoinColumn(name="tipo_documento_id", referencedColumnName="id", nullable=false)   
    */
    protected $tipoDocumento;
    
    /**
    * @ORM\ManyToOne(targetEntity="TarifaEncomienda")
    * @ORM\JoinColumn(name="tarifa1_id", referencedColumnName="id", nullable=true)   
    */
    protected $tarifa1; 

    /**
    * @ORM\ManyToOne(targetEntity="TarifaEncomienda")
    * @ORM\JoinColumn(name="tarifa2_id", referencedColumnName="id", nullable=true)   
    */
    protected $tarifa2;
    
    /**
    * @ORM\ManyToOne(targetEntity="TarifaEncomienda")
    * @ORM\JoinColumn(name="tarifa_distancia_id", referencedColumnName="id", nullable=true)   
    */
    protected $tarifaDistancia;
    
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
    * @ORM\ManyToOne(targetEntity="TipoPago")
    * @ORM\JoinColumn(name="tipo_pago_id", referencedColumnName="id", nullable=true)   
    */
    protected $tipoPago;
    
    /**
    * @ORM\ManyToOne(targetEntity="FacturaGenerada", inversedBy="listaEncomiendas")
    * @ORM\JoinColumn(name="factura_generada_id", referencedColumnName="id", nullable=true)   
    */
    protected $facturaGenerada;
    
    /**
    * @ORM\Column(name="por_cobrar_sin_facturar", type="boolean", nullable=true)
    */
    protected $porCobrarSinFacturar;
    
    /**
    * @ORM\ManyToOne(targetEntity="AutorizacionCortesia")
    * @ORM\JoinColumn(name="autorizacion_cortesia_id", referencedColumnName="id", nullable=true, unique=true)   
    */
    protected $autorizacionCortesia;
    
    /**
    * @ORM\ManyToOne(targetEntity="AutorizacionInterna")
    * @ORM\JoinColumn(name="autorizacion_interna_id", referencedColumnName="id", nullable=true, unique=true)   
    */
    protected $autorizacionInterna;
    
    /**
    * @Assert\NotBlank(message = "El código de rastreo no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "50",
    *      minMessage = "El código de rastreo por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El código de rastreo no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=50, unique=true)
    */
    protected $codigo;
    
    /**
    * @Assert\Length(
    *      max = "50",
    *      maxMessage = "El código externo de cliente no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="codigo_externo_cliente", type="string", length=50, nullable=true)
    */
    protected $codigoExternoCliente;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La observación no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $observacion;
    
    /**
    * @Assert\Count(
    *   min = "1",
    *   minMessage = "Debes especificar al menos {{ limit }} ruta."
    * )
    * @ORM\OneToMany(targetEntity="EncomiendaRuta", mappedBy="encomienda", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $rutas;
    
    /**
    * @Assert\NotNull(message = "La ultima bitacora no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EncomiendaBitacora")
    * @ORM\JoinColumn(name="ultima_bitacora_id", referencedColumnName="id", nullable=true)   
    */
    protected $bitacora;
    
    /**
    * @ORM\ManyToOne(targetEntity="Salida")
    * @ORM\JoinColumn(name="primera_salida_id", referencedColumnName="id", nullable=true)   
    */
    protected $primeraSalida;
    
    /**
    * @ORM\Column(name="transito", type="boolean", nullable=true)
    */
    protected $estuboTransito;
    
    /**
    * @Assert\Count(
    *   min = "1",
    *   minMessage = "Debes especificar al menos {{ limit }} estado."
    * )
    * @ORM\OneToMany(targetEntity="EncomiendaBitacora", mappedBy="encomienda", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $eventos;
    
    /**
    * @ORM\ManyToOne(targetEntity="Boleto")
    * @ORM\JoinColumn(name="boleto_id", referencedColumnName="id", nullable=true)   
    */
    protected $boleto;
    
    /**
    * @Assert\NotBlank(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=false)
    */
    protected $empresa; //Es la empresa que recibio la operacion, esto hizo falta para las encomiendas de efectivo cuando son cortesias o guias internas.
    
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - INIT
    //------------------------------------------------------------------------------
    /**
    * @Assert\NotNull(message = "Para registrar una encomienda el operador debe pertenecer a una estación")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_creacion_id", referencedColumnName="id", nullable=false)   
    */
    protected $estacionCreacion;
    
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
    
    //------------------------------------------------------------------------------
    //              DATOS INTERNOS - END
    //------------------------------------------------------------------------------
    
    function __construct() {
        $this->estuboTransito = false;
        $this->porCobrarSinFacturar = false;
        $this->eventos = new ArrayCollection();
        $this->rutas = new ArrayCollection();
    }
    
    public function __toString() {
        return "ID:" . strval($this->id) . "|" . $this->descripcion;
    }
    
    /*
    * VALIDACION 
     * 
     *  hay que validar que cuando la encomienda tenga una salida asignada excepto para los estados Recibida y Anulado 
    */
    public function validacionesGenerales(ExecutionContext $context)
    {
        
        if($this->estacionOrigen === $this->estacionDestino){
            $context->addViolation("La estación origen no puede ser igual a la estación destino."); 
        }
        
        if($this->clienteDestinatario !== null){
            if($this->clienteDestinatario instanceof Cliente){
                $nombre = $this->clienteDestinatario->getNombre();
                if($nombre === null || trim($nombre) === "" ){
                    $context->addViolation("El cliente destinatario de la encomienda debe tener definido su nombre en el sistema.");
                }
//                $dpi = $this->clienteDestinatario->getDpi();
//                if($dpi === null || trim($dpi) === "" ){
//                    $context->addViolation("El cliente destinatario de la encomienda debe tener definido su dpi en el sistema.");
//                }
//                $direccion = $this->clienteDestinatario->getDireccion();
//                if($direccion === null || trim($direccion) === "" ){
//                    $context->addViolation("El cliente destinatario de la encomienda debe tener definido su dirección en el sistema.");
//                }
//                $telefono = $this->clienteDestinatario->getTelefono();
//                if($telefono === null || trim($telefono) === "" ){
//                    $context->addViolation("El cliente destinatario de la encomienda debe tener definido su teléfono en el sistema.");
//                }
            }
        }
        
        if($this->tipoEncomienda !== null && $this->tipoEncomienda->getId() === TipoEncomienda::EFECTIVO){
            if(intval($this->cantidad) > 5000){
                $context->addViolation("No se puede enviar más de Q 5000."); 
            }
            if($this->tipoEncomiendaEspecial !== null){
                $context->addViolation("Para el tipo de encomienda de efectivo no se puede definir un tipo de encomienda especial."); 
            }
            if($this->alto !== null && trim(strval($this->alto)) !== ""){
                $context->addViolation("Para el tipo de encomienda de efectivo no se puede definir el alto."); 
            }
            if($this->ancho !== null && trim(strval($this->ancho)) !== ""){
                $context->addViolation("Para el tipo de encomienda de efectivo no se puede definir el ancho."); 
            }
            if($this->profundidad !== null && trim(strval($this->profundidad)) !== ""){
                $context->addViolation("Para el tipo de encomienda de efectivo no se puede definir la profundidad."); 
            }
            if($this->volumen !== null && trim(strval($this->volumen)) !== ""){
                $context->addViolation("Para el tipo de encomienda de efectivo no se puede definir el volumen."); 
            }
            if($this->peso !== null && trim(strval($this->peso)) !== ""){
                $context->addViolation("Para el tipo de encomienda de efectivo no se puede definir el peso."); 
            }
        }else if($this->tipoEncomienda !== null && $this->tipoEncomienda->getId() === TipoEncomienda::ESPECIAL){
            if(intval($this->cantidad) > 100){
                $context->addViolation("No se puede registrar más de 100 unidades en una encomienda."); 
            }
            if($this->tipoEncomiendaEspecial === null){
                $context->addViolation("Debe definir un tipo de encomienda especial."); 
            }
            else{
                if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::AUTORIZACION_CORTESIA && 
                        $this->tipoEncomiendaEspecial->getPermiteAutorizacionCortesia() === false){
                    $context->addViolation("La encomienda: '" . $this->tipoEncomiendaEspecial->getNombre() . "' no permite autorización de cortesía."); 
                }else if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::AUTORIZACION_INTERNA && 
                        $this->tipoEncomiendaEspecial->getPermiteAutorizacionInterna() === false){
                    $context->addViolation("La encomienda: '" . $this->tipoEncomiendaEspecial->getNombre() . "' no permite autorización interna."); 
                }else if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA && 
                        $this->tipoEncomiendaEspecial->getPermiteFactura() === false){
                    $context->addViolation("La encomienda: '" . $this->tipoEncomiendaEspecial->getNombre() . "' no permite factura."); 
                }else if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR && 
                        $this->tipoEncomiendaEspecial->getPermitePorCobrar() === false){
                    $context->addViolation("La encomienda: '" . $this->tipoEncomiendaEspecial->getNombre() . "' no permite documento por cobrar."); 
                }
            }
            if($this->alto !== null && trim(strval($this->alto)) !== ""){
                $context->addViolation("Para el tipo de encomienda especial no se puede definir el alto."); 
            }
            if($this->ancho !== null && trim(strval($this->ancho)) !== ""){
                $context->addViolation("Para el tipo de encomienda especial no se puede definir el ancho."); 
            }
            if($this->profundidad !== null && trim(strval($this->profundidad)) !== ""){
                $context->addViolation("Para el tipo de encomienda especial no se puede definir la profundidad."); 
            }
            if($this->volumen !== null && trim(strval($this->volumen)) !== ""){
                $context->addViolation("Para el tipo de encomienda especial no se puede definir el volumen."); 
            }
            if($this->peso !== null && trim(strval($this->peso)) !== ""){
                $context->addViolation("Para el tipo de encomienda especial no se puede definir el peso."); 
            }
        }else if($this->tipoEncomienda !== null && $this->tipoEncomienda->getId() === TipoEncomienda::PAQUETE){
            if(intval($this->cantidad) > 100){
                $context->addViolation("No se puede registrar más de 100 unidades en una encomienda."); 
            }
            if($this->tipoEncomiendaEspecial !== null){
                $context->addViolation("Para el tipo de encomienda de paquete no se puede definir un tipo de encomienda especial."); 
            }
            if($this->alto === null || trim(strval($this->alto)) === ""){
                $context->addViolation("Debe definir el alto de la encomienda de paquete."); 
            }
            if($this->ancho === null || trim(strval($this->ancho)) === ""){
                $context->addViolation("Debe definir el ancho de la encomienda de paquete."); 
            }
            if($this->profundidad === null || trim(strval($this->profundidad)) === ""){
                $context->addViolation("Debe definir la profundidad de la encomienda de paquete."); 
            }
            if($this->volumen === null || trim(strval($this->volumen)) === ""){
                $context->addViolation("Debe definir el volumen de la encomienda de paquete."); 
            }
            if($this->peso === null || trim(strval($this->peso)) === ""){
                 $context->addViolation("Debe definir el peso de la encomienda de paquete."); 
            }
        }
        
        if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::AUTORIZACION_CORTESIA){
            if($this->valorDeclarado !== null && $this->valorDeclarado !== 0){
                $context->addViolation("Las encomiendas por autorización de cortesía no pueden tener definido un valor declarado."); 
            }
            if($this->autorizacionCortesia === null){
                $context->addViolation("Las encomiendas por autorización de cortesía requieren una autorización."); 
            }else{
              if($this->autorizacionCortesia->getActivo() === false){
                  $context->addViolation("La autorización de cortesía esta deshabilitada."); 
              }
              if($this->autorizacionCortesia->getServicioEstacion()->getId() !== ServicioEstacion::ENCOMIENDA){
                  $context->addViolation("La autorización de cortesía no esta asociada a los servicios de encomienda."); 
              }
              
              $salida = $this->primeraSalida;
//              foreach ($this->eventos as $items) {
//                  if($items->getSalida() !== null){
//                      $salida = $items->getSalida();
//                      continue;
//                  }
//              }
              $restriccionFechaUso =  $this->autorizacionCortesia->getRestriccionFechaUso();
              if($restriccionFechaUso !== null && $salida !== null && UtilService::compararFechas($restriccionFechaUso, $salida->getFecha()) !== 0){
                   $context->addViolation("La cortesía tiene una restricción de que solamente se puede envíar el dia: " . $restriccionFechaUso->format("d-m-Y"));                 
              }
              $restriccionEstacionOrigen =  $this->autorizacionCortesia->getRestriccionEstacionOrigen();
              if($restriccionEstacionOrigen !== null && $restriccionEstacionOrigen !== $this->estacionOrigen){
                  $context->addViolation("La cortesía tiene una restricción de que solamente se puede envíar desde estación de origen: " . $restriccionEstacionOrigen); 
              }
              $restriccionEstacionDestino =  $this->autorizacionCortesia->getRestriccionEstacionDestino();
              if($restriccionEstacionDestino !== null && $restriccionEstacionDestino !== $this->estacionDestino){
                  $context->addViolation("La cortesía tiene una restricción de que solamente se puede recibir en la estación de destino: " . $restriccionEstacionDestino); 
              }
              $restriccionCliente =  $this->autorizacionCortesia->getRestriccionCliente();
              if($restriccionCliente !== null && $restriccionCliente !== $this->clienteRemitente ){
                  $context->addViolation("La cortesía tiene una restricción de que solamente puede envíar el cliente: " . $restriccionCliente);
              }
            }
            if($this->autorizacionInterna !== null){
                $context->addViolation("Las encomiendas de cortesía no pueden tener definido una autorización interna."); 
            }
            if($this->tipoPago !== null){
                $context->addViolation("Las encomiendas de cortesía no pueden tener definido el tipo de pago."); 
            }
            if($this->tarifa1 !== null || $this->tarifa2 !== null || $this->tarifaDistancia !== null){
                $context->addViolation("Las encomiendas de cortesía no pueden tener definida ninguna tarifa."); 
            }
            if($this->precioCalculado !== null){
                $context->addViolation("Las encomiendas de cortesía no pueden tener definido un precio."); 
            }
            if($this->precioCalculadoMonedaBase !== null){
                $context->addViolation("Las encomiendas de cortesía no pueden tener definido un precio base."); 
            }
            if($this->facturaGenerada !== null){
                $context->addViolation("Las encomiendas de cortesía no pueden tener definida una factura."); 
            }
        }else if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::AUTORIZACION_INTERNA){
            if($this->valorDeclarado !== null && $this->valorDeclarado !== 0){
                $context->addViolation("Las encomiendas por autorización interna no pueden tener definido un valor declarado."); 
            }
            if($this->autorizacionInterna === null){
                $context->addViolation("Las encomiendas por autorización interna requieren una autorización."); 
            }
            if($this->autorizacionCortesia !== null){
                $context->addViolation("Las encomiendas de autorización interna no pueden tener definido una autorización de cortesía."); 
            }
            if($this->tipoPago !== null){
                $context->addViolation("Las encomiendas de autorización interna no pueden tener definido el tipo de pago."); 
            }
            if($this->tarifa1 !== null || $this->tarifa2 !== null || $this->tarifaDistancia !== null){
                $context->addViolation("Las encomiendas de autorización interna no pueden tener definida ninguna tarifa."); 
            }
            if($this->precioCalculado !== null){
                $context->addViolation("Las encomiendas de autorización interna no pueden tener definido un precio."); 
            }
            if($this->precioCalculadoMonedaBase !== null){
                $context->addViolation("Las encomiendas de cortesía no pueden tener definido un precio base."); 
            }
            if($this->facturaGenerada !== null){
                $context->addViolation("Las encomiendas de autorización interna no pueden tener definida una factura."); 
            }
        }else if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::FACTURA){
            if(($this->valorDeclarado !== null && $this->valorDeclarado !== 0) && $this->porcientoValorDeclarado == null){
                $context->addViolation("No está definido el porciento aplicado al valor declarado."); 
            }
            if($this->autorizacionCortesia !== null){
                $context->addViolation("Las encomiendas por factura no pueden tener definida una autorización de cortesía."); 
            }
            if($this->autorizacionInterna !== null){
                $context->addViolation("Las encomiendas por factura no pueden tener definida una autorización interna."); 
            }
            if($this->tipoPago === null){
                $context->addViolation("Las encomiendas por factura deben tener definido el tipo de pago."); 
            }
            if($this->tarifa1 === null){
                $context->addViolation("Las encomiendas por factura deben tener definida la tarifa."); 
            }
            if($this->precioCalculado === null){
                $context->addViolation("Las encomiendas por factura deben tener definido un precio."); 
            }
            if($this->precioCalculadoMonedaBase === null){
                $context->addViolation("Las encomiendas por factura deben tener definido un precio base."); 
            }
            if($this->facturaGenerada === null){
                $context->addViolation("Las encomiendas por factura deben tener definida una factura."); 
            }
        }else if($this->tipoDocumento->getId() === TipoDocumentoEncomienda::POR_COBRAR){
            if(($this->valorDeclarado !== null && $this->valorDeclarado !== 0) && $this->porcientoValorDeclarado == null){
                $context->addViolation("No está definido el porciento aplicado al valor declarado."); 
            }
            if($this->tipoEncomienda->getId() === TipoEncomienda::EFECTIVO){
                $context->addViolation("El sistema no permite registrar encomiendas de efectivo por cobrar."); 
            }
            if($this->estacionCreacion !== null && $this->estacionCreacion->getEnviosEncomiendasPorCobrar() !== true){
                $context->addViolation("No se pueden registrar encomiendas por cobrar en la estación " . $this->estacionCreacion->getNombre() . "."); 
            }
            if($this->boleto !== null){
                $context->addViolation("El sistema no permite encomiendas asociadas a boleto por cobrar."); 
            }
            if($this->autorizacionCortesia !== null){
                $context->addViolation("Las encomiendas por cobrar no pueden tener definida una autorización de cortesía."); 
            }
            if($this->autorizacionInterna !== null){
                $context->addViolation("Las encomiendas por cobrar no pueden tener definida una autorización interna."); 
            }
            if($this->tarifa1 === null){
                $context->addViolation("Las encomiendas por cobrar deben tener definida la tarifa."); 
            }
            if($this->precioCalculadoMonedaBase === null){
                $context->addViolation("Las encomiendas por cobrar deben tener definido un precio base."); 
            }  
            
            $ultimoEstado = $this->getUltimoEstado();
            if($ultimoEstado->getId() === EstadoEncomienda::ENTREGADA){
               if($this->tipoPago === null){
                    $context->addViolation("Las encomiendas por cobrar en estado entregada deben tener definido el tipo de pago."); 
               }
               if($this->porCobrarSinFacturar === true || $this->porCobrarSinFacturar === 'true'){
                   if($this->facturaGenerada !== null){
                        $context->addViolation("Las encomiendas por cobrar a entregar sin facturar no pueden tener definido factura."); 
                   }
               }else{
                   if($this->facturaGenerada === null){
                        $context->addViolation("Las encomiendas por cobrar a entregar deben tener definida una factura."); 
                   }
               }
                
               if($this->precioCalculado === null){
                    $context->addViolation("Las encomiendas por cobrar en estado entregada deben tener definido un precio."); 
               }
            }else{
               if($this->facturaGenerada !== null){
                    $context->addViolation("Las encomiendas por cobrar no deben tener definida una factura hasta se intente poner en estado entregada."); 
               }
            }
        }
        
        if($this->getUltimoEstado() === EstadoBoleto::ANULADO){
            if($this->observacion === null || trim($this->observacion) === ""){
                $context->addViolation("Debe especificar una observación cuando la encomienda está en estado anulada."); 
            }
        }
        
        if($this->boleto !== null){
            if($this->clienteRemitente !== $this->clienteDestinatario){
                $context->addViolation("En las encomiendas asociadas a boletos el cliente remitente y destinario deben ser iguales."); 
            }
            $clienteBoleto = $this->boleto->getClienteBoleto();
            if($this->clienteRemitente !== $clienteBoleto){
                $context->addViolation("En las encomiendas asociadas a boletos el cliente remitente debe ser el mismo que el cliente del boleto."); 
            }
            $rutaBoleto = $this->boleto->getSalida()->getItinerario()->getRuta();
            if($this->ruta !== $rutaBoleto){
                $context->addViolation("La ruta del boleto: " . $rutaBoleto->__toString() . " no coincide con la ruta de la encomienda: " . $this->ruta->__toString() . "."); 
            }
        }
        
        if($this->facturaGenerada !== null){
            $estacionFacturaGenerada = $this->facturaGenerada->getEstacion();
            $estacionFactura = $this->facturaGenerada->getFactura()->getEstacion();
            if($estacionFacturaGenerada !== $estacionFactura){
                $context->addViolation("Se está intentando utilizar la serie de factura " . $this->facturaGenerada->getInfo2() . 
                        " en la estacion " . $estacionFacturaGenerada->__toString() . "."); 
            }
        }
    }
  
    public function getRutasIntermedias() {
        $result = array();
        foreach ($this->rutas as $item) {
            $result[] = $item->getRuta();
        }
        return $result;
    }
    
    public function getRutasIntermediasStr() {
        $result = array();
        foreach ($this->rutas as $item) {
            $result[] = $item->getRuta()->__toString();
        }
        return implode(" / ", $result);
    }
    
    public function getEstacionesIntermediasYFinal() {
        $result = array();
        foreach ($this->rutas as $item) {
            $result[] = $item->getEstacionDestino();
        }
        return $result;
    }
    
    public function getEstacionesStr() {
        $result = array($this->estacionOrigen);
        foreach ($this->rutas as $item) {
            $result[] = $item->getEstacionDestino();
        }
        return implode(" / ", $result);
    }
    
    public function getUltimoEstado() {
        $lastBitacora = $this->getUltimaBitacora();
        if($lastBitacora === null){
            return null;
        }else{
            return $lastBitacora->getEstado();
        }
    }
    
    public function getUltimaBitacora() {
        return $this->bitacora;
//        if($this->eventos === null || count($this->eventos) === 0){
//            return null;
//        }
//        $lastBitacora = null;
//        foreach ($this->eventos as $bitacora) {
//           if($lastBitacora === null){
//               $lastBitacora = $bitacora;
//           }else{
//               $datetime1 = strtotime($bitacora->getFecha()->format("Y-m-d H:i:s"));
//               $datetime2 = strtotime($lastBitacora->getFecha()->format("Y-m-d H:i:s"));
//               if( $datetime1 - $datetime2 >= 0){
//                  $lastBitacora =  $bitacora;
//               }
//           }
//        }
//        return $lastBitacora;
    }
    
    public function getUltimoEstadoById() {
        $lastBitacora = $this->getUltimaBitacoraById();
        if($lastBitacora === null){
            return null;
        }else{
            return $lastBitacora->getEstado();
        }
    }
    
    public function getUltimaBitacoraById() {
        return $this->bitacora;
//        if($this->eventos === null || count($this->eventos) === 0){
//            return null;
//        }
//        $lastBitacora = null;
//        foreach ($this->eventos as $bitacora) {
//           if($lastBitacora === null){
//               $lastBitacora = $bitacora;
//           }else{
//               if( $bitacora->getId() >= $lastBitacora->getId() ){
//                  $lastBitacora =  $bitacora;
//               }
//           }
//        }
//        return $lastBitacora;
    }
    
    public function checkEstuboTransito() {
        return $this->estuboTransito;
//        foreach ($this->eventos as $bitacora) {
//            if($bitacora->getEstado()->getId() === EstadoEncomienda::TRANSITO){
//                return true;
//            }
//        }
//        return false; 
    }
    public function checkEstacionesOrigen($estacion) {
        return $this->estacionOrigen->getId() === $estacion->getId(); 
    }
    
    public function checkEstacionDestino($estacion) {
        return $this->estacionDestino->getId() === $estacion->getId(); 
    }
    
    public function checkEstacionesIntermedias($estacion) {
        foreach ($this->rutas as $ruta) {
            if($ruta->getEstacionDestino()->getId() === $estacion->getId()){
                return true;
            }
        }
        return false; 
    }
    
    public function addEventos(EncomiendaBitacora $item) {  
       $item->setEncomienda($this);
       $this->getEventos()->add($item);
       $this->bitacora = $item;
       if($item->getEstado()->getId() === EstadoEncomienda::EMBARCADA || $item->getEstado()->getId() === EstadoEncomienda::TRANSITO){
           if($this->estuboTransito === false){
               $this->primeraSalida = $item->getSalida();
           }
       }
       if($item->getEstado()->getId() === EstadoEncomienda::TRANSITO){
           $this->estuboTransito = true;
       }
       return $this;
    }
    
    public function addRutas(EncomiendaRuta $item) {  
       $item->setEncomienda($this);
       $this->getRutas()->add($item);
       return $this;
    }
    
    public function removeRutas(EncomiendaRuta $item) {       
        $this->getRutas()->removeElement($item); 
        $item->setEncomienda(null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getTipoEncomienda() {
        return $this->tipoEncomienda;
    }

    public function getRuta() {
        return $this->ruta;
    }

    public function getValorDeclarado() {
        return $this->valorDeclarado;
    }

    public function getTipoEncomiendaEspecial() {
        return $this->tipoEncomiendaEspecial;
    }

    public function getPeso() {
        return $this->peso;
    }

    public function getAlto() {
        return $this->alto;
    }

    public function getAncho() {
        return $this->ancho;
    }

    public function getProfundidad() {
        return $this->profundidad;
    }

    public function getVolumen() {
        return $this->volumen;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getClienteRemitente() {
        return $this->clienteRemitente;
    }

    public function getClienteDestinatario() {
        return $this->clienteDestinatario;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function getEstacionDestino() {
        return $this->estacionDestino;
    }

    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function getTarifa1() {
        return $this->tarifa1;
    }

    public function getTarifa2() {
        return $this->tarifa2;
    }

    public function getTarifaDistancia() {
        return $this->tarifaDistancia;
    }

    public function getPrecioCalculadoMonedaBase() {
        return $this->precioCalculadoMonedaBase;
    }

    public function getMoneda() {
        return $this->moneda;
    }

    public function getTipoCambio() {
        return $this->tipoCambio;
    }

    public function getPrecioCalculado() {
        return $this->precioCalculado;
    }

    public function getTipoPago() {
        return $this->tipoPago;
    }

    public function getFacturaGenerada() {
        return $this->facturaGenerada;
    }

    public function getAutorizacionCortesia() {
        return $this->autorizacionCortesia;
    }

    public function getAutorizacionInterna() {
        return $this->autorizacionInterna;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getObservacion() {
        return $this->observacion;
    }

    public function getRutas() {
        return $this->rutas;
    }

    public function getEventos() {
        return $this->eventos;
    }

    public function getBoleto() {
        return $this->boleto;
    }

    public function getEmpresa() {
        return $this->empresa;
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

    public function setId($id) {
        $this->id = $id;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function setTipoEncomienda($tipoEncomienda) {
        $this->tipoEncomienda = $tipoEncomienda;
    }

    public function setRuta($ruta) {
        $this->ruta = $ruta;
    }

    public function setValorDeclarado($valorDeclarado) {
        $this->valorDeclarado = $valorDeclarado;
    }

    public function setTipoEncomiendaEspecial($tipoEncomiendaEspecial) {
        $this->tipoEncomiendaEspecial = $tipoEncomiendaEspecial;
    }

    public function setPeso($peso) {
        $this->peso = $peso;
    }

    public function setAlto($alto) {
        $this->alto = $alto;
    }

    public function setAncho($ancho) {
        $this->ancho = $ancho;
    }

    public function setProfundidad($profundidad) {
        $this->profundidad = $profundidad;
    }

    public function setVolumen($volumen) {
        $this->volumen = $volumen;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setClienteRemitente($clienteRemitente) {
        $this->clienteRemitente = $clienteRemitente;
    }

    public function setClienteDestinatario($clienteDestinatario) {
        $this->clienteDestinatario = $clienteDestinatario;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }

    public function setEstacionDestino($estacionDestino) {
        $this->estacionDestino = $estacionDestino;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setTarifa1($tarifa1) {
        $this->tarifa1 = $tarifa1;
    }

    public function setTarifa2($tarifa2) {
        $this->tarifa2 = $tarifa2;
    }

    public function setTarifaDistancia($tarifaDistancia) {
        $this->tarifaDistancia = $tarifaDistancia;
    }

    public function setPrecioCalculadoMonedaBase($precioCalculadoMonedaBase) {
        $this->precioCalculadoMonedaBase = $precioCalculadoMonedaBase;
    }

    public function setMoneda($moneda) {
        $this->moneda = $moneda;
    }

    public function setTipoCambio($tipoCambio) {
        $this->tipoCambio = $tipoCambio;
    }

    public function setPrecioCalculado($precioCalculado) {
        $this->precioCalculado = $precioCalculado;
    }

    public function setTipoPago($tipoPago) {
        $this->tipoPago = $tipoPago;
    }

    public function setFacturaGenerada($facturaGenerada) {
        $this->facturaGenerada = $facturaGenerada;
    }

    public function setAutorizacionCortesia($autorizacionCortesia) {
        $this->autorizacionCortesia = $autorizacionCortesia;
    }

    public function setAutorizacionInterna($autorizacionInterna) {
        $this->autorizacionInterna = $autorizacionInterna;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setObservacion($observacion) {
        $this->observacion = $observacion;
    }

    public function setRutas($rutas) {
        $this->rutas = $rutas;
    }

    public function setEventos($eventos) {
        $this->eventos = $eventos;
    }

    public function setBoleto($boleto) {
        $this->boleto = $boleto;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
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
    
    public function getPorcientoValorDeclarado() {
        return $this->porcientoValorDeclarado;
    }

    public function setPorcientoValorDeclarado($porcientoValorDeclarado) {
        $this->porcientoValorDeclarado = $porcientoValorDeclarado;
    }
    
    public function getPorCobrarSinFacturar() {
        return $this->porCobrarSinFacturar;
    }

    public function setPorCobrarSinFacturar($porCobrarSinFacturar) {
        $this->porCobrarSinFacturar = $porCobrarSinFacturar;
    }
    
    public function getEstuboTransito() {
        return $this->estuboTransito;
    }

    public function setEstuboTransito($estuboTransito) {
        $this->estuboTransito = $estuboTransito;
    }

    public function getBitacora() {
        return $this->bitacora;
    }

    public function setBitacora($bitacora) {
        $this->bitacora = $bitacora;
    }
    
    public function getPrimeraSalida() {
        return $this->primeraSalida;
    }

    public function setPrimeraSalida($primeraSalida) {
        $this->primeraSalida = $primeraSalida;
    }
    
    public function getClienteDocumento() {
        return $this->clienteDocumento;
    }

    public function setClienteDocumento($clienteDocumento) {
        $this->clienteDocumento = $clienteDocumento;
    }
    
    public function getIdentificadorWeb() {
        return $this->identificadorWeb;
    }

    public function setIdentificadorWeb($identificadorWeb) {
        $this->identificadorWeb = $identificadorWeb;
    }
    
    public function getCodigoExternoCliente() {
        return $this->codigoExternoCliente;
    }

    public function setCodigoExternoCliente($codigoExternoCliente) {
        $this->codigoExternoCliente = $codigoExternoCliente;
    }
}
