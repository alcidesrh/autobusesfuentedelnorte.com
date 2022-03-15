<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\ServicioEstacion;
use Acme\TerminalOmnibusBundle\Entity\Boleto;
use Acme\TerminalOmnibusBundle\Entity\Encomienda;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\FacturaGeneradaRepository")
* @ORM\Table(name="factura_generada", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_FACTURA_CONSECUTIVO", columns={"factura_id", "consecutivo"})})
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class FacturaGenerada{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
   /**
    * @ORM\ManyToOne(targetEntity="Factura")
    * @ORM\JoinColumn(name="factura_id", referencedColumnName="id", nullable=false)
    */
    protected $factura;
    
    /**
    * @ORM\Column(type="bigint", nullable=true) //Hay un trigger que lo genera en la BD
    */
    protected $consecutivo;
    
    /**
    * @ORM\ManyToOne(targetEntity="ServicioEstacion")
    * @ORM\JoinColumn(name="servicio_estacion_id", referencedColumnName="id", nullable=false)   
    */
    protected $servicioEstacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Moneda")
    * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id", nullable=false)   
    */
    protected $moneda;
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoCambio")
    * @ORM\JoinColumn(name="tipo_cambio_id", referencedColumnName="id", nullable=false)   
    */
    protected $tipoCambio;

    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El importe total solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999999.99",
    *      minMessage = "El importe total no debe ser menor que {{ limit }}.",
    *      maxMessage = "El importe total no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El importe total debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
    */
    protected $importeTotal;
    
    //ESTA ES LA FECHA QUE SE FACTURO
    
    /**
    * @Assert\NotBlank(message = "La fecha no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="datetime", nullable=false)
    */
    protected $fecha;
    
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El código de autorización de tarjeta no puede tener más de {{ limit }} caracteres."
    * ) 
    * @Assert\Regex(
    *     pattern="/((^[a-zA-Z0-9]{1,20}$))/",
    *     match=true,
    *     message="El código de autorización de tarjeta solo puede tener números y letras."
    * )
    * @ORM\Column(type="string", length=20, nullable=true)
    */
    protected $autorizacionTarjeta;
    
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El código de referencia externa no puede tener más de {{ limit }} caracteres."
    * ) 
    * @Assert\Regex(
    *     pattern="/((^[a-zA-Z0-9]{1,20}$))/",
    *     match=true,
    *     message="El código de referencia externa solo puede tener números y letras."
    * )
    * @ORM\Column(type="string", length=20, nullable=true)
    */
    protected $referenciaExterna;
    
    //   ----   DATOS DE CONTROL INIT ----
    /**
    * @Assert\NotNull(message = "El usuario no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuario;
    
    /**
    * @Assert\NotNull(message = "La estacion no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $estacion;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La observación no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $observacion;
    
    //ESTA ES LA FECHA EN QUE SE INSERTO EL REGISTRO EN LA BASE DE DATOS
    
    /**
    * @Assert\NotBlank(message = "La fecha de creacion no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
    */
    protected $fechaCreacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_anulacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioAnulacion;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_anulacion", type="datetime", nullable=true)
    */
    protected $fechaAnulacion;
    
    //   ----   DATOS DE CONTROL END ----
    
    /*
        IMPORTANTE: COMO EL CONSECUTIVO DE LA FACTURA SE SETEA DESDE LA BD A TRAVES DE UN TRIGGER
        HAY QUE REFRESCAR EL OBJETO EN EL $em ANTES DE CORRER LA VALIDACION.
        EJ. $em->refresh($facturaGenerada);
    */
    public function validar()
    {
//        var_dump("Validando id:" . $this->id);
//        var_dump("MaximoResolucionFactura:" . $this->factura->getMaximoResolucionFactura());
//        var_dump("Consecutivo:" . $this->consecutivo);
        if($this->factura->getMaximoResolucionFactura() < $this->consecutivo){
            throw new \RuntimeException("m1El consecutivo de la factura sobrepaso el limite máximo permitido.");
        }
        if($this->factura->getMinimoResolucionFactura() > $this->consecutivo){
            throw new \RuntimeException("m1El consecutivo de la factura es menor que el limite mínimo permitido.");
        }
    }
    
    
     /*
     VALIDACIONES
     */
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        if($this->factura !== null){
            if($this->estacion !== $this->factura->getEstacion()){
                $context->addViolation("Se está intentando utilizar la serie de factura " . $this->getInfo2() . 
                        " en la estacion " . $this->estacion->__toString() . "."); 
            }
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
    * @ORM\Column(name="sAutorizacionUUIDsat", type="string", length=255, nullable=true)
    */
    protected $sAutorizacionUUIDsat;
    
    /**
    * @ORM\Column(name="sNumeroDTEsat", type="bigint", nullable=true)
    */
    protected $sNumeroDTEsat;
    
    /**
    * @ORM\Column(name="sSerieDTEsat", type="string", length=255, nullable=true)
    */
    protected $sSerieDTEsat;
    
    /**
    * @ORM\Column(name="sFechaCertificaDTEsat", type="datetime", nullable=true)
    */
    protected $sFechaCertificaDTEsat;    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function __toString() {
        $str = "ID:" . strval($this->id);
        if($this->factura !== null){
            $str .= ", FG:" . $this->factura->getSerieResolucionFactura();
            $str .= $this->consecutivo;
        }
        return $str;
    }
    
    public function getInfo1() {
        $str = "";
        if($this->factura !== null){
            $str .= $this->factura->getSerieResolucionFactura();
            $str .= $this->consecutivo;
        }
        return $str;
    }
    
    public function getInfo2() {
        $str = "";
        if($this->factura !== null){
            $str = $this->factura->getSerieResolucionFactura() . " " . $this->consecutivo;
        }
        return $str;
    }
    
    public function getMonedaImporte() {
        $str = "";
        if($this->moneda !== null){
            $str = $this->moneda->getSigla() . " " . $this->importeTotal;
        }
        return $str;
    }
    
    function __construct() {
        $this->fecha = new \DateTime();
        $this->fechaCreacion = new \DateTime();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getFactura() {
        return $this->factura;
    }

    public function getConsecutivo() {
        return $this->consecutivo;
    }

    public function getServicioEstacion() {
        return $this->servicioEstacion;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFactura($factura) {
        $this->factura = $factura;
    }

    public function setConsecutivo($consecutivo) {
        $this->consecutivo = $consecutivo;
    }

    public function setServicioEstacion($servicioEstacion) {
        $this->servicioEstacion = $servicioEstacion;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }
    
    public function getMoneda() {
        return $this->moneda;
    }

    public function getTipoCambio() {
        return $this->tipoCambio;
    }

    public function getImporteTotal() {
        return $this->importeTotal;
    }

    public function setMoneda($moneda) {
        $this->moneda = $moneda;
    }

    public function setTipoCambio($tipoCambio) {
        $this->tipoCambio = $tipoCambio;
    }

    public function setImporteTotal($importeTotal) {
        $this->importeTotal = $importeTotal;
    }
    
    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }
    
    public function getAutorizacionTarjeta() {
        return $this->autorizacionTarjeta;
    }

    public function getReferenciaExterna() {
        return $this->referenciaExterna;
    }

    public function setAutorizacionTarjeta($autorizacionTarjeta) {
        $this->autorizacionTarjeta = $autorizacionTarjeta;
    }

    public function setReferenciaExterna($referenciaExterna) {
        $this->referenciaExterna = $referenciaExterna;
    }
    
    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }
    
    public function getObservacion() {
        return $this->observacion;
    }

    public function setObservacion($observacion) {
        $this->observacion = $observacion;
    }
    
    public function getUsuarioAnulacion() {
        return $this->usuarioAnulacion;
    }

    public function getFechaAnulacion() {
        return $this->fechaAnulacion;
    }

    public function setUsuarioAnulacion($usuarioAnulacion) {
        $this->usuarioAnulacion = $usuarioAnulacion;
    }

    public function setFechaAnulacion($fechaAnulacion) {
        $this->fechaAnulacion = $fechaAnulacion;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Set sAutorizacionUUIDsat
     *
     * @param string $sAutorizacionUUIDsat
     * @return FacturaGenerada
     */
    public function setSAutorizacionUUIDsat($sAutorizacionUUIDsat)
    {
        $this->sAutorizacionUUIDsat = $sAutorizacionUUIDsat;

        return $this;
    }

    /**
     * Get sAutorizacionUUIDsat
     *
     * @return string 
     */
    public function getSAutorizacionUUIDsat()
    {
        return $this->sAutorizacionUUIDsat;
    }

    /**
     * Set sNumeroDTEsat
     *
     * @param integer $sNumeroDTEsat
     * @return FacturaGenerada
     */
    public function setSNumeroDTEsat($sNumeroDTEsat)
    {
        $this->sNumeroDTEsat = $sNumeroDTEsat;

        return $this;
    }

    /**
     * Get sNumeroDTEsat
     *
     * @return integer 
     */
    public function getSNumeroDTEsat()
    {
        return $this->sNumeroDTEsat;
    }

    /**
     * Set sSerieDTEsat
     *
     * @param string $sSerieDTEsat
     * @return FacturaGenerada
     */
    public function setSSerieDTEsat($sSerieDTEsat)
    {
        $this->sSerieDTEsat = $sSerieDTEsat;

        return $this;
    }

    /**
     * Get sSerieDTEsat
     *
     * @return string 
     */
    public function getSSerieDTEsat()
    {
        return $this->sSerieDTEsat;
    }

    /**
     * Set sFechaCertificaDTEsat
     *
     * @param \DateTime $sFechaCertificaDTEsat
     * @return FacturaGenerada
     */
    public function setSFechaCertificaDTEsat($sFechaCertificaDTEsat)
    {
        $this->sFechaCertificaDTEsat = $sFechaCertificaDTEsat;

        return $this;
    }

    /**
     * Get sFechaCertificaDTEsat
     *
     * @return \DateTime 
     */
    public function getSFechaCertificaDTEsat()
    {
        return $this->sFechaCertificaDTEsat;
    }    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}

?>