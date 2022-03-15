<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\PilotoRepository")
* @ORM\Table(name="piloto")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="codigo", message="El código ya existe")
* @DoctrineAssert\UniqueEntity(fields ={"empresa", "numeroLicencia"}, message="Ya existe un piloto con el número de licencia en la empresa.")
* @DoctrineAssert\UniqueEntity(fields ={"empresa","dpi"}, message="Ya existe un piloto con el DPI en la empresa.")
*/
class Piloto{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El código no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "10",
    *      minMessage = "El código por lo menos debe tener {{ limit }} carácter.",
    *      maxMessage = "El código no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/\d/",
    *     match=true,
    *     message="El código solo puede contener números"
    * )
    * @ORM\Column(type="string", length=10, unique=true)
    */
    protected $codigo;
    
    /**
    * @Assert\NotBlank(message = "El primer nombre no debe estar en blanco")
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El primer nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="nombre", type="string", length=20)
    */
    protected $nombre1;
    
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El segundo nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="nombre2", type="string", length=20, nullable=true)
    */
    protected $nombre2;
    
    /**
    * @Assert\NotBlank(message = "El primer apellido no debe estar en blanco")
    * @Assert\Length(
    *      max = "40",
    *      maxMessage = "El primer apellido no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="apellidos", type="string", length=40, nullable=true)
    */
    protected $apellido1;
    
    /**
    * @Assert\Length(
    *      max = "40",
    *      maxMessage = "El segundo apellido no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="apellido2", type="string", length=40, nullable=true)
    */
    protected $apellido2;
    
    /**
    * @Assert\NotBlank(message = "La fecha de nacimiento no debe estar en blanco")
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(type="date", nullable=true)
    */
    protected $fechaNacimiento;
    
    /**
    * @Assert\NotBlank(message = "La licencia no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "40",
    *      minMessage = "La licencia por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "La licencia no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=40)
    */
    protected $numeroLicencia;
    
    /**
    * @Assert\NotBlank(message = "La fecha de vencimiento de la licencia no debe estar en blanco") 
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(type="date", nullable=true)
    */
    protected $fechaVencimientoLicencia;
    
    /**
    * @Assert\NotBlank(message = "El número de identificación no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "40",
    *      minMessage = "El número de identificación por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El número de identificación no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=40)
    */
    protected $dpi;
    
    /**
    * @Assert\Length(
    *      max = "50",
    *      maxMessage = "El seguro social no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=50, nullable=true)
    */
    protected $seguroSocial;
    
    /**
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     match=true,
    *     message="El teléfono solo puede contener números"
    * )
    * @Assert\Length(
    *      min = "8",
    *      max = "15",
    *      minMessage = "El teléfono por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El teléfono no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=15, nullable=true)
    */
    protected $telefono;
    
    /**
    * @Assert\NotNull(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=true)        
    */
    protected $empresa;
    
    /**
    * @Assert\NotNull(message = "La nacionalidad no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Nacionalidad")
    * @ORM\JoinColumn(name="nacionalidad_id", referencedColumnName="id", nullable=true)   
    */
    protected $nacionalidad;
    
    /**
    * @Assert\NotNull(message = "El sexo no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Sexo")
    * @ORM\JoinColumn(name="sexo_id", referencedColumnName="id", nullable=true, nullable=true)   
    */
    protected $sexo;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    public function __toString() {
        return $this->getCodigoFullName();
    }
    
    public function getCodigoFullName() {
         return $this->codigo . " - " . $this->getFullName();
    }
    
    public function getFullName() {
        $str = $this->nombre1;
        if($this->nombre2 !== null && trim($this->nombre2) !== ""){
            $str .= " " . $this->nombre2;
        }
        if($this->apellido1 !== null && trim($this->apellido1) !== ""){
            $str .= " " . $this->apellido1;
        }
        if($this->apellido2 !== null && trim($this->apellido2) !== ""){
            $str .= " " . $this->apellido2;
        }
        return $str;
    }
    
    public function getCodigoFullNameVencimientoLicencia() {
        $str = $this->getCodigoFullName();
        if($this->fechaVencimientoLicencia !== null){
            $str .= " - LIC:" . $this->fechaVencimientoLicencia->format("d/m/Y");
        }
        return $str;
    }
    
    function __construct() {
        $this->activo = true;
    }

    public function getId() {
        return $this->id;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getNombre1() {
        return $this->nombre1;
    }

    public function getNombre2() {
        return $this->nombre2;
    }

    public function getApellido1() {
        return $this->apellido1;
    }

    public function getApellido2() {
        return $this->apellido2;
    }

    public function getFechaNacimiento() {
        return $this->fechaNacimiento;
    }

    public function getNumeroLicencia() {
        return $this->numeroLicencia;
    }

    public function getFechaVencimientoLicencia() {
        return $this->fechaVencimientoLicencia;
    }

    public function getDpi() {
        return $this->dpi;
    }

    public function getSeguroSocial() {
        return $this->seguroSocial;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function setNombre1($nombre1) {
        $this->nombre1 = $nombre1;
    }

    public function setNombre2($nombre2) {
        $this->nombre2 = $nombre2;
    }

    public function setApellido1($apellido1) {
        $this->apellido1 = $apellido1;
    }

    public function setApellido2($apellido2) {
        $this->apellido2 = $apellido2;
    }

    public function setFechaNacimiento($fechaNacimiento) {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function setNumeroLicencia($numeroLicencia) {
        $this->numeroLicencia = $numeroLicencia;
    }

    public function setFechaVencimientoLicencia($fechaVencimientoLicencia) {
        $this->fechaVencimientoLicencia = $fechaVencimientoLicencia;
    }

    public function setDpi($dpi) {
        $this->dpi = $dpi;
    }

    public function setSeguroSocial($seguroSocial) {
        $this->seguroSocial = $seguroSocial;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
    
    public function getNacionalidad() {
        return $this->nacionalidad;
    }

    public function getSexo() {
        return $this->sexo;
    }

    public function setNacionalidad($nacionalidad) {
        $this->nacionalidad = $nacionalidad;
    }

    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }
}

?>