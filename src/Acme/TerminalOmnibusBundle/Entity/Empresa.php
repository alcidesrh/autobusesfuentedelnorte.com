<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\EmpresaRepository")
* @ORM\Table(name="empresa")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
* @DoctrineAssert\UniqueEntity(fields ="nombreComercial", message="El nombre comercial ya existe")
* @DoctrineAssert\UniqueEntity(fields ="nit", message="El nit ya existe")
* @DoctrineAssert\UniqueEntity(fields ="color", message="El color ya está en uso")
*/
class Empresa{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\Length(
    *      max = "15",
    *      maxMessage = "El alias no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=15, unique=true, nullable=true)
    */
    protected $alias;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255, unique=true)
    */
    protected $nombre;
    
    /**
    * @Assert\NotBlank(message = "El nombre comercial no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "El nombre comercial por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre comercial no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255, unique=true, nullable=false )
    */
    protected $nombreComercial;
    
    /**
    * @Assert\NotBlank(message = "La denominación social no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "La denominación social por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La denominación social no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255, nullable=false )
    */
    protected $denominacionSocial;
    
    /**
    * @ORM\Column(type="text", nullable=false )
    * @Assert\NotBlank(message = "La dirección no debe estar en blanco")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La dirección no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $direccion;   
    
    /**
    * @Assert\NotBlank(message = "El nit no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "20",
    *      minMessage = "El nit por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nit no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=20, unique=true, nullable=false )
    */
    protected $nit;
    
    /**
    * @Assert\NotBlank(message = "La forma de pago de ISR no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "255",
    *      minMessage = "La forma de pago de ISR por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La forma de pago de ISR no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255, nullable=false )
    */
    protected $formaPagoISR;
    
    /**
    * @Assert\NotBlank(message = "El representante legal no debe estar en blanco")
    * @Assert\Length(
    *      min = "3",
    *      max = "255",
    *      minMessage = "El representante legal por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El representante legal no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=255)
    */
    protected $representanteLegal;
    
    /**
    * @Assert\NotBlank(message = "El color no debe estar en blanco")
    * @Assert\Length(
    *      min = "6",
    *      max = "6",
    *      minMessage = "El color debe tener {{ limit }} caracteres.",
    *      maxMessage = "El color no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=6, unique=true,  nullable=false)
    */
    protected $color;
    
    /**
    * @ORM\Column(type="array", nullable=true)
    */
    protected $correos;
    
    /**
    * @ORM\Column(type="array", nullable=true)
    */
    protected $telefonos;
    
    /**
     * @Assert\File(maxSize="6M")
     */
    protected $file;
    
    /**
    * @ORM\Column(name="logo", type="text", nullable=true)
    */
    protected $logo;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    /**
    * @ORM\Column(name="id_externo", type="bigint", nullable=true)
    */
    protected $idExterno;
    
    /**
    * @ORM\Column(name="id_usuario_externo", type="bigint", nullable=true)
    */
    protected $idUsuarioExterno;
    
    /**
    * @ORM\Column(name="id_cliente_externo", type="bigint", nullable=true)
    */
    protected $idClienteExterno;
    
    /**
    * @ORM\Column(name="url_externo", type="string", length=250, nullable=true)
    */
    protected $urlExterno;
    
    /**
    * @ORM\Column(name="reportar_boleto_facturado", type="boolean", nullable=true)
    */
    protected $reportarBoletoFacturado;
    
    /**
    * @ORM\Column(name="id_producto_boleto_externo", type="bigint", nullable=true)
    */
    protected $idProductoBoletoExterno;
    
    /**
    * @ORM\Column(name="reportar_encomienda_facturado", type="boolean", nullable=true)
    */
    protected $reportarEncomiendaFacturado;
    
    /**
    * @ORM\Column(name="id_producto_encomienda_externo", type="bigint", nullable=true)
    */
    protected $idProductoEncomiendaExterno;
    
    /**
    * @ORM\Column(name="obligatorio_control_tarjetas", type="boolean", nullable=true)
    */
    protected $obligatorioControlTarjetas;
    
    public function getIdAlias() {
        return strval($this->id) . " - " . $this->alias;
    }
    
    public function getAliasNombre() {
        return $this->alias . " - " . $this->nombre;
    }
    
    public function __toString() {
        return $this->alias;
    }    
    
    public function getCorreosStr() {
        $items = array();
        foreach ($this->correos as $item) {
            $items[] = $item;
        }
        return implode(",", $items);
    }
    
    public function getTelefonosStr($pref = null) {
        $items = array();
        if($this->telefonos !== null){
            foreach ($this->telefonos as $item) {
                $items[] = ($pref !== null ? "(".$pref.") " : "")  . $item;
            }
        }
        return implode(",", $items);
    }
    
    function __construct() {
        $this->reportarBoletoFacturado = false;
        $this->reportarEncomiendaFacturado = false;
        $this->obligatorioControlTarjetas = false;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getRepresentanteLegal() {
        return $this->representanteLegal;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setRepresentanteLegal($representanteLegal) {
        $this->representanteLegal = $representanteLegal;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getColor() {
        return $this->color;
    }

    public function setColor($color) {
        $this->color = $color;
    }
    
    public function getNombreComercial() {
        return $this->nombreComercial;
    }

    public function getDenominacionSocial() {
        return $this->denominacionSocial;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function getNit() {
        return $this->nit;
    }

    public function setNombreComercial($nombreComercial) {
        $this->nombreComercial = $nombreComercial;
    }

    public function setDenominacionSocial($denominacionSocial) {
        $this->denominacionSocial = $denominacionSocial;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    public function setNit($nit) {
        $this->nit = $nit;
    }
    
    public function getFormaPagoISR() {
        return $this->formaPagoISR;
    }

    public function setFormaPagoISR($formaPagoISR) {
        $this->formaPagoISR = $formaPagoISR;
    }
    
    public function getCorreos() {
        return $this->correos;
    }

    public function setCorreos($correos) {
        $this->correos = $correos;
    }
    
    public function getAlias() {
        return $this->alias;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }
    
    public function getIdExterno() {
        return $this->idExterno;
    }

    public function setIdExterno($idExterno) {
        $this->idExterno = $idExterno;
    }
    
    public function getTelefonos() {
        return $this->telefonos;
    }

    public function setTelefonos($telefonos) {
        $this->telefonos = $telefonos;
    }
    
    public function getFile() {
        return $this->file;
    }

    public function getLogo() {
        return $this->logo;
    }

    public function setLogo($logo) {
        $this->logo = $logo;
    }
    
    public function getIdUsuarioExterno() {
        return $this->idUsuarioExterno;
    }

    public function getIdClienteExterno() {
        return $this->idClienteExterno;
    }

    public function getUrlExterno() {
        return $this->urlExterno;
    }

    public function getReportarBoletoFacturado() {
        return $this->reportarBoletoFacturado;
    }

    public function getIdProductoBoletoExterno() {
        return $this->idProductoBoletoExterno;
    }

    public function getReportarEncomiendaFacturado() {
        return $this->reportarEncomiendaFacturado;
    }

    public function getIdProductoEncomiendaExterno() {
        return $this->idProductoEncomiendaExterno;
    }

    public function setIdUsuarioExterno($idUsuarioExterno) {
        $this->idUsuarioExterno = $idUsuarioExterno;
    }

    public function setIdClienteExterno($idClienteExterno) {
        $this->idClienteExterno = $idClienteExterno;
    }

    public function setUrlExterno($urlExterno) {
        $this->urlExterno = $urlExterno;
    }

    public function setReportarBoletoFacturado($reportarBoletoFacturado) {
        $this->reportarBoletoFacturado = $reportarBoletoFacturado;
    }

    public function setIdProductoBoletoExterno($idProductoBoletoExterno) {
        $this->idProductoBoletoExterno = $idProductoBoletoExterno;
    }

    public function setReportarEncomiendaFacturado($reportarEncomiendaFacturado) {
        $this->reportarEncomiendaFacturado = $reportarEncomiendaFacturado;
    }

    public function setIdProductoEncomiendaExterno($idProductoEncomiendaExterno) {
        $this->idProductoEncomiendaExterno = $idProductoEncomiendaExterno;
    }
    
    public function getObligatorioControlTarjetas() {
        return $this->obligatorioControlTarjetas;
    }

    public function setObligatorioControlTarjetas($obligatorioControlTarjetas) {
        $this->obligatorioControlTarjetas = $obligatorioControlTarjetas;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/uploads';
    }
    
    public function setFile($file) {
        $this->file = $file;
        if (null !== $this->file) {
            $filename = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessExtension();
            $pathImagen = $this->getUploadRootDir() . $filename;
            copy($this->file, $pathImagen);
            $this->logo = base64_encode(fread(fopen($pathImagen, "r"), filesize($pathImagen)));
            unlink($pathImagen);            
            unlink($this->file); 
            $this->file = null;
        }
    }
}

?>