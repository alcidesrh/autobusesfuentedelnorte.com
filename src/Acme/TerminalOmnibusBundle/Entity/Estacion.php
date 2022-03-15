<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\TipoEstacion;
use Acme\TerminalOmnibusBundle\Entity\TipoPagoEstacion;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\EstacionRepository")
* @ORM\Table(name="estacion")
* @ORM\HasLifecycleCallbacks()
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
* @DoctrineAssert\UniqueEntity(fields ="alias", message="El alias ya existe")
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class Estacion implements \Acme\BackendBundle\Entity\IJobSync{
    
    const ESTACION_PORTAL_INTERNET_MITOCHA = 90;
    const ESTACION_PORTAL_INTERNET_PIONERA = 91;
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\Column(name="id_externo", type="bigint", nullable=true)
    */
    protected $idExternoBoleto;
    
    /**
    * @ORM\Column(name="id_externo_encomienda", type="bigint", nullable=true)
    */
    protected $idExternoEncomienda;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "50",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=50, unique=true)
    */
    protected $nombre;
    
    /**
    * @ORM\Column(type="text")
    * @Assert\NotBlank(message = "La dirección no debe estar en blanco")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La dirección no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $direccion;    
    
     /**
    * @Assert\NotBlank(message = "El alias no debe estar en blanco")
    * @Assert\Length(
    *      min = "3",
    *      max = "3",
    *      minMessage = "El alias por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El alias no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=3, unique=true)
    */
    protected $alias;
    
    /**
    * @Assert\NotBlank(message = "El tipo de pago no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoPagoEstacion")
    * @ORM\JoinColumn(name="tipo_pago_id", referencedColumnName="id")        
    */
    protected $tipoPago;
    
    /**
    * @Assert\NotBlank(message = "El pais no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Pais")
    * @ORM\JoinColumn(name="pais_id", referencedColumnName="id", nullable=true)        
    */
    protected $pais;
    
    /**
    * @ORM\Column(name="aplicar_porciento_tarifa_agencia", type="boolean", nullable=true)
    */
    protected $aplicarPorcientoTarifaAgencia;
    
    /**
    * @Assert\Range(
    *      min = "0",
    *      max = "99.99999999",
    *      minMessage = "El valor del porciento de la tarifa de la agencia no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor del porciento de la tarifa de la agencia no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor del porciento de la tarifa de la agencia debe ser un número válido."
    * )   
    * @ORM\Column(name="porciento_tarifa_agencia", type="decimal", precision=10, scale=8, nullable=true)
    */
    protected $porcientoTarifaAgencia;
    
    /**
    * @ORM\Column(name="inicia_ruta", type="boolean", nullable=true)
    */
    protected $iniciaRuta;
    
    /**
    * @ORM\Column(name="destino", type="boolean", nullable=true)
    */
    protected $destino;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $enviosEncomiendasPorCobrar;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $permitirVoucherBoleto;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $permitirTarjeta;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $pluginJavaActivo;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $publicidad;
    
    /**
    * @Assert\NotBlank(message = "El tipo de estación no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoEstacion")
    * @ORM\JoinColumn(name="tipoEstacion_id", referencedColumnName="id")        
    */
    protected $tipo;
    
    /**
    * @ORM\ManyToOne(targetEntity="Moneda")
    * @ORM\JoinColumn(name="agencia_moneda_id", referencedColumnName="id", nullable=true)        
    */
    protected $monedaAgencia;
    
    /**
    * @Assert\Range(
    *      min = "0",
    *      max = "99.99999999",
    *      minMessage = "El valor del porciento de bonificación no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor del porciento de bonificación no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor del porciento de bonificación debe ser un número válido."
    * )   
    * @ORM\Column(name="agencia_porciento_bonificacion", type="decimal", precision=10, scale=8, nullable=true)
    */
    protected $porcientoBonificacion;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El saldo solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El saldo no debe ser menor que {{ limit }}.",
    *      maxMessage = "El saldo no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El saldo debe ser un número válido."
    * )   
    * @ORM\Column(name="agencia_saldo", type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $saldo;
    
    /**
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{0,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="La bonificación solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "La bonificación no debe ser menor que {{ limit }}.",
    *      maxMessage = "La bonificación no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "La bonificación debe ser un número válido."
    * )   
    * @ORM\Column(name="agencia_bonificacion", type="decimal", precision=7, scale=2, nullable=true)
    */
    protected $bonificacion;
    
    /**
    * @ORM\Column(name="facturacion_especial", type="boolean", nullable=true)
    */
    protected $facturacionEspecial;
    
    /**
    * @Assert\Length(
    *      max = "8",
    *      maxMessage = "El ping de facturacion especial no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="ping_facturacion_especial", type="string", length=8, nullable=true)
    */
    protected $pingFacturacionEspecial;
    
    /**
    *@Assert\Count(
    *   min = "0",
    *   max = "5",
    *   minMessage = "Debes especificar al menos {{ limit }} teléfonos.",
    *   maxMessage = "No puedes especificar más de {{ limit }} teléfonos."
    * )
    * @ORM\OneToMany(targetEntity="TelefonoEstacion", mappedBy="estacion", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaTelefono;
    
    /**
    *@Assert\Count(
    *   min = "0",
    *   max = "5",
    *   minMessage = "Debes especificar al menos {{ limit }} dirección de correo.",
    *   maxMessage = "No puedes especificar más de {{ limit }} direcciones de correo."
    * )
    * @ORM\OneToMany(targetEntity="CorreoEstacion", mappedBy="estacion", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaCorreo;
    
    /**
    * @ORM\ManyToMany(targetEntity="ServicioEstacion")
    * @ORM\JoinTable(name="estacion_servicio_union",
    *   joinColumns={@ORM\JoinColumn(name="estacion_id", referencedColumnName="id")},
    *   inverseJoinColumns={@ORM\JoinColumn(name="servicio_id", referencedColumnName="id")}
    * )
    */
    protected $listaServicio;
    
    /**  
    * @ORM\Column(type="decimal", precision=15, scale=10, nullable=true)
    */
    protected $longitude;
    
    /**  
    * @ORM\Column(type="decimal", precision=15, scale=10, nullable=true)
    */
    protected $latitude;
    
    /**
    * @ORM\ManyToOne(targetEntity="Departamento")
    * @ORM\JoinColumn(name="departamento_id", referencedColumnName="id", nullable=true)        
    */
    protected $departamento;
    
    /**
    * @ORM\Column(name="control_tarjetas_en_ruta", type="boolean", nullable=true)
    */
    protected $controlTarjetasEnRuta;
    
    
    
    
    
    
    
    
    
    
    /**
    * @ORM\Column(name="numEstablecimientoSat", type="bigint", nullable=true)
    */
    protected $numEstablecimientoSat;      
    
    /**
    * @ORM\Column(name="numEstablecimientoSatMitocha", type="bigint", nullable=true)
    */
    protected $numEstablecimientoSatMitocha;    
    
    /**
    * @ORM\Column(name="numEstablecimientoSatRosita", type="bigint", nullable=true)
    */
    protected $numEstablecimientoSatRosita;    
    
    
    
    
    
    
    
    public function __construct() {
        $this->listaTelefono = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listaCorreo = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listaServicio = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activo = true;
        $this->publicidad = true;
        $this->agencia = false;
        $this->iniciaRuta = false;
        $this->porcientoTarifaAgencia = 0;
        $this->destino = true;
        $this->pluginJavaActivo = false;
        $this->enviosEncomiendasPorCobrar = true;
        $this->permitirVoucherBoleto = false;
        $this->aplicarPorcientoTarifaAgencia = false;
        $this->permitirTarjeta = false;
        $this->controlTarjetasEnRuta = false;
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["nombre"] = $this->nombre;
        $data["alias"] = $this->alias;
        $data["activo"] = $this->activo;
        $data["direccion"] = $this->direccion;
        $data["longitude"] = $this->longitude;
        $data["latitude"] = $this->latitude;
        $data["tipo"] = $this->tipo->getId();
        $data["correos"] = $this->getCorreosArray();       
        $data["telefonos"] = $this->getTelefonosArray(); 
        $data["departamento"] = $this->getDepartamento() === null ? "" : $this->getDepartamento()->getId();
        return $data;
    }
    
    public function isValidToSync() {
        if($this->destino === false){
            return false;
        }
        return true;
    }
    
    public function getNivelSync(){
        return 2;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_ESTACION;
    }
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        if($this->tipo->getId() === TipoEstacion::ESTACION){
//            if($this->facturacionEspecial === true){
//                $context->addViolation("La estaciones principales no pueden tener facturación especial.");
//            }
//            if(trim($this->pingFacturacionEspecial) !== ""){
//                $context->addViolation("La estaciones principales no pueden tener ping de facturación especial.");
//            }
        }
        else if($this->tipo->getId() === TipoEstacion::RECORRIDO){
//            if($this->facturacionEspecial === false){
//                $context->addViolation("La estaciones de recorrido deben tener facturación especial.");
//            }
//            if(trim($this->pingFacturacionEspecial) === ""){
//                $context->addViolation("La estaciones de recorrido deben tener ping de facturación especial.");
//            }
        }
        else if($this->tipo->getId() === TipoEstacion::AGENCIA){
            if($this->monedaAgencia === null){
                $context->addViolation("Debe definir la moneda base de la agencia.");
            }
            if($this->tipoPago->getId() === TipoPagoEstacion::PREPAGO){
                if($this->porcientoBonificacion === null){
                    $context->addViolation("Debe definir el porciento de bonificación de la agencia con forma de pago PREPAGO.");
                }
                if($this->saldo === null){
                    $context->addViolation("Debe definir el porciento de bonificación de la agencia con forma de pago PREPAGO.");
                }else if($this->saldo < 0){
                    $context->addViolation("El saldo principal prepago no se puede sobregirar.");
                }
                if($this->bonificacion === null){
                    $context->addViolation("Debe definir el porciento de bonificación de la agencia con forma de pago PREPAGO.");
                }else if($this->bonificacion < 0){
                    $context->addViolation("El saldo de bono del prepago no se puede sobregirar.");
                }
            }
        }
        
        if($this->aplicarPorcientoTarifaAgencia === true){
            if($this->porcientoTarifaAgencia === null || floatval($this->porcientoTarifaAgencia) <= 0){
                $context->addViolation("Debe definir el porciento de incremento en la tarifa a la estación.");
            }
        }
        
        if($this->tipoPago->getId() === TipoPagoEstacion::PREPAGO && $this->facturacionEspecial === true){
            $context->addViolation("La estaciones con prepago no pueden tener facturación especial.");
        }
        
    }
    
    public function getCheckAgenciaPrepago() {
        if($this->tipo->getId() === TipoEstacion::AGENCIA && 
           $this->tipoPago->getId() === TipoPagoEstacion::PREPAGO){
            return true;
        }else{
            return false;
        }
    }
    
    public function __toString() {
        return $this->getAliasNombre();
    }
    
    public function getAliasNombre($separador = "-") {
        if ($this->alias != null && trim($this->alias) != "") {
            return trim($this->alias) . $separador . $this->nombre;
        } else {
            return $this->nombre;
        }
    }
    
    public function getIdAlias() {
        return strval($this->id) . " - " . $this->alias;
    }
    
    public function getInfo1() {
        $info = "";
        if ($this->alias != null && trim($this->alias) != "") {
            $info = trim($this->alias) . "-" . $this->nombre;
        } else {
            $info = $this->nombre;
        }
        if($this->tipo->getId() === TipoEstacion::AGENCIA){
            if($this->monedaAgencia !== null){
                $info .= " ( " . $this->monedaAgencia->getSigla() . " ) ";
            }
        }
        return $info;
    }
    
    public function getInfo2($pref = null) {
        $info = $this->nombre;
        $telef = $this->getTelefonosStr($pref);
        if($telef !== ""){
            $info .= ", TEL: " . $telef;
        }
        return $info;
    }
    
    public function getCorreosArray() {
        $items = array();
        foreach ($this->listaCorreo as $item) {
            if($item->getActivo() === true){
                $items[] = $item->getCorreo();
            }
        }
        return $items;
    }
    
    public function getCorreosStr() {
        return implode(",", $this->getCorreosArray());
    }
    
    public function getTelefonosArray($pref = null) {
        $items = array();
        foreach ($this->listaTelefono as $item) {
            if($item->getActivo() === true){
                $items[] = ($pref !== null ? "(".$pref.") " : "")  . $item->getTelefono();
            }
        }
        return $items;
    }
    
    public function getTelefonosStr($pref = null) {
        return implode(",", $this->getTelefonosArray($pref));
    }
    
    public function addListaCorreo(CorreoEstacion $item) {  
       $item->setEstacion($this);
       $this->getListaCorreo()->add($item);
       return $this;
    }
    
    public function removeListaCorreo($item) {       
        $this->getListaCorreo()->removeElement($item); 
        $item->setEstacion(null);
    }
    
    public function addListaTelefono(TelefonoEstacion $item) {  
       $item->setEstacion($this);
       $this->getListaTelefono()->add($item); 
       return $this;
    }
    
    public function removeListaTelefono($item) {       
        $this->getListaTelefono()->removeElement($item); 
        $item->setEstacion(null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function getTipoPago() {
        return $this->tipoPago;
    }

    public function getPorcientoTarifaAgencia() {
        return $this->porcientoTarifaAgencia;
    }

    public function getIniciaRuta() {
        return $this->iniciaRuta;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getPublicidad() {
        return $this->publicidad;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getFacturacionEspecial() {
        return $this->facturacionEspecial;
    }

    public function getPingFacturacionEspecial() {
        return $this->pingFacturacionEspecial;
    }

    public function getListaTelefono() {
        return $this->listaTelefono;
    }

    public function getListaCorreo() {
        return $this->listaCorreo;
    }

    public function getListaServicio() {
        return $this->listaServicio;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function setTipoPago($tipoPago) {
        $this->tipoPago = $tipoPago;
    }

    public function setPorcientoTarifaAgencia($porcientoTarifaAgencia) {
        $this->porcientoTarifaAgencia = $porcientoTarifaAgencia;
    }

    public function setIniciaRuta($iniciaRuta) {
        $this->iniciaRuta = $iniciaRuta;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setPublicidad($publicidad) {
        $this->publicidad = $publicidad;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setFacturacionEspecial($facturacionEspecial) {
        $this->facturacionEspecial = $facturacionEspecial;
    }

    public function setPingFacturacionEspecial($pingFacturacionEspecial) {
        $this->pingFacturacionEspecial = $pingFacturacionEspecial;
    }

    public function setListaTelefono($listaTelefono) {
        $this->listaTelefono = $listaTelefono;
    }

    public function setListaCorreo($listaCorreo) {
        $this->listaCorreo = $listaCorreo;
    }

    public function setListaServicio($listaServicio) {
        $this->listaServicio = $listaServicio;
    }
    
    public function getMonedaAgencia() {
        return $this->monedaAgencia;
    }

    public function setMonedaAgencia($monedaAgencia) {
        $this->monedaAgencia = $monedaAgencia;
    }
    
    public function getPorcientoBonificacion() {
        return $this->porcientoBonificacion;
    }

    public function getSaldo() {
        return $this->saldo;
    }

    public function getBonificacion() {
        return $this->bonificacion;
    }

    public function setPorcientoBonificacion($porcientoBonificacion) {
        $this->porcientoBonificacion = $porcientoBonificacion;
    }

    public function setSaldo($saldo) {
        $this->saldo = $saldo;
    }

    public function setBonificacion($bonificacion) {
        $this->bonificacion = $bonificacion;
    }
    
    public function getDestino() {
        return $this->destino;
    }

    public function setDestino($destino) {
        $this->destino = $destino;
    }
    
    public function getEnviosEncomiendasPorCobrar() {
        return $this->enviosEncomiendasPorCobrar;
    }

    public function setEnviosEncomiendasPorCobrar($enviosEncomiendasPorCobrar) {
        $this->enviosEncomiendasPorCobrar = $enviosEncomiendasPorCobrar;
    }
    
    public function getIdExternoBoleto() {
        return $this->idExternoBoleto;
    }

    public function getIdExternoEncomienda() {
        return $this->idExternoEncomienda;
    }

    public function setIdExternoBoleto($idExternoBoleto) {
        $this->idExternoBoleto = $idExternoBoleto;
    }

    public function setIdExternoEncomienda($idExternoEncomienda) {
        $this->idExternoEncomienda = $idExternoEncomienda;
    }
    
    public function getPluginJavaActivo() {
        return $this->pluginJavaActivo;
    }

    public function setPluginJavaActivo($pluginJavaActivo) {
        $this->pluginJavaActivo = $pluginJavaActivo;
    }
    
    public function getPermitirVoucherBoleto() {
        return $this->permitirVoucherBoleto;
    }

    public function setPermitirVoucherBoleto($permitirVoucherBoleto) {
        $this->permitirVoucherBoleto = $permitirVoucherBoleto;
    }
    
    public function getAplicarPorcientoTarifaAgencia() {
        return $this->aplicarPorcientoTarifaAgencia;
    }

    public function setAplicarPorcientoTarifaAgencia($aplicarPorcientoTarifaAgencia) {
        $this->aplicarPorcientoTarifaAgencia = $aplicarPorcientoTarifaAgencia;
    }
    
    public function getPermitirTarjeta() {
        return $this->permitirTarjeta;
    }

    public function setPermitirTarjeta($permitirTarjeta) {
        $this->permitirTarjeta = $permitirTarjeta;
    }
    
    public function getPais() {
        return $this->pais;
    }

    public function setPais($pais) {
        $this->pais = $pais;
    }
    
    public function getLongitude() {
        return $this->longitude;
    }

    public function getLatitude() {
        return $this->latitude;
    }

    public function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    public function setLatitude($latitude) {
        $this->latitude = $latitude;
    }
    
    public function getDepartamento() {
        return $this->departamento;
    }

    public function setDepartamento($departamento) {
        $this->departamento = $departamento;
    }
    
    public function getControlTarjetasEnRuta() {
        return $this->controlTarjetasEnRuta;
    }

    public function setControlTarjetasEnRuta($controlTarjetasEnRuta) {
        $this->controlTarjetasEnRuta = $controlTarjetasEnRuta;
    }
    
    
    
    
    
    
    
    
    
    
    /**
     * Set numEstablecimientoSat
     *
     * @param integer $numEstablecimientoSat
     * @return Empresa
     */
    public function setNumEstablecimientoSat($numEstablecimientoSat)
    {
        $this->numEstablecimientoSat = $numEstablecimientoSat;

        return $this;
    }

    /**
     * Get numEstablecimientoSat
     *
     * @return integer 
     */
    public function getNumEstablecimientoSat()
    {
        return $this->numEstablecimientoSat;
    }        
    
    
    
    /**
     * Set numEstablecimientoSatMitocha
     *
     * @param integer $numEstablecimientoSatMitocha
     * @return Empresa
     */
    public function setNumEstablecimientoSatMitocha($numEstablecimientoSatMitocha)
    {
        $this->numEstablecimientoSatMitocha = $numEstablecimientoSatMitocha;

        return $this;
    }

    /**
     * Get numEstablecimientoSatMitocha
     *
     * @return integer 
     */
    public function getNumEstablecimientoSatMitocha()
    {
        return $this->numEstablecimientoSatMitocha;
    }    
    
    
    
    /**
     * Set numEstablecimientoSatRosita
     *
     * @param integer $numEstablecimientoSatRosita
     * @return Empresa
     */
    public function setNumEstablecimientoSatRosita($numEstablecimientoSatRosita)
    {
        $this->numEstablecimientoSatRosita = $numEstablecimientoSatRosita;

        return $this;
    }

    /**
     * Get numEstablecimientoSatRosita
     *
     * @return integer 
     */
    public function getNumEstablecimientoSatRosita()
    {
        return $this->numEstablecimientoSatRosita;
    }    
    
    
}

?>