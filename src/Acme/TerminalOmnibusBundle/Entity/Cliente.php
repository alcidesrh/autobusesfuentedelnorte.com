<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ClienteRepository")
* @ORM\Table(name="cliente", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_CLIENTE_NIT_NOMBRE_DPI", columns={"nit", "nombre", "dpi"})})
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class Cliente{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El nit no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/((^C\/F$)|(^[A-Z0-9]{0,20}$)|(^[A-Z0-9]{0,15}[\-]{1,1}[A-Z0-9]{1,4}$))/",
    *     match=true,
    *     message="El NIT solo puede contener números y letras mayúsculas. No puede tener espacios ni guión."
    * )
    * @ORM\Column(type="string", length=20, nullable=true)
    */
    protected $nit; 
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoDocumento")
    * @ORM\JoinColumn(name="tipo_documento_id", referencedColumnName="id")   
    */
    protected $tipoDocumento;
    
    /**
    * @Assert\Length(
    *      max = "40",
    *      maxMessage = "El número del documento no puede tener más de {{ limit }} caracteres."
    * )  
    * @Assert\Regex(
    *     pattern="/(^[a-zA-Z0-9\s]{0,40}$)/",
    *     match=true,
    *     message="El número del documento solo puede contener números, letras y espacios. No puede tener guiones."
    * )
    * @ORM\Column(type="string", length=40, nullable=true)
    */
    protected $dpi; //El dpi corresponde al numero del documento
    
    /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(name="fecha_vencimiento_documento", type="date", nullable=true)
    */
    protected $fechaVencimientoDocumento;
    
    /**
    * @Assert\Length(
    *      max = "100",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=false)
    */
    protected $nombre;
    
    /**
    * @ORM\Column(name="detallado", type="boolean", nullable=true)
    */
    protected $detallado;
    
    /**
    * @Assert\Length(
    *      max = "50",
    *      maxMessage = "El primer nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="nombre1", type="string", length=50, nullable=true)
    */
    protected $nombre1;
    
    /**
    * @Assert\Length(
    *      max = "50",
    *      maxMessage = "El segundo nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="nombre2", type="string", length=50, nullable=true)
    */
    protected $nombre2;
    
    /**
    * @Assert\Length(
    *      max = "50",
    *      maxMessage = "El primer apellido no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="apellido1", type="string", length=50, nullable=true)
    */
    protected $apellido1;
    
    /**
    * @Assert\Length(
    *      max = "50",
    *      maxMessage = "El segundo apellido no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(name="apellido2", type="string", length=50, nullable=true)
    */
    protected $apellido2;
    
    /**
    * @Assert\Length(      
    *      max = "150",
    *      maxMessage = "La dirección no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(type="string", length=150, nullable=true)
    */
    protected $direccion;
    
    /**
    * @Assert\Length(
    *      min = "8",
    *      max = "21",
    *      minMessage = "El teléfono por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El teléfono no puede tener más de {{ limit }} caracteres."
    * )  
    * @Assert\Regex(
    *     pattern="/((^\d{8,10}$)|(^\d{8,10}[\-|,]{1,1}\d{8,10}$))/",
    *     match=true,
    *     message="Teléfonos solo puede contener números y una coma. No puede tener espacios."
    * )
    * @ORM\Column(type="string", length=21, nullable=true)
    */
    protected $telefono;
    
    /**
    * @Assert\Email(
    *     message = "El correo '{{ value }}' no es válido.",
    *     checkMX = true,
    *     checkHost = false
    * )
    * @Assert\Length(
    *      min = "3",
    *      max = "40",
    *      minMessage = "El correo por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El correo no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=40, nullable=true)
    */
    protected $correo;
    
    /**
    * @ORM\ManyToOne(targetEntity="Nacionalidad")
    * @ORM\JoinColumn(name="nacionalidad_id", referencedColumnName="id")   
    */
    protected $nacionalidad;
    
    /**
    * @ORM\Column(name="empleado", type="boolean", nullable=true)
    */
    protected $empleado;
    
    /**
    * @ORM\ManyToOne(targetEntity="Sexo")
    * @ORM\JoinColumn(name="sexo_id", referencedColumnName="id", nullable=true)   
    */
    protected $sexo;
    
    /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(name="fecha_nacimiento", type="date", nullable=true)
    */
    protected $fechaNacimiento;
    
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioCreacion;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
    */
    protected $fechaCreacion;
	
	
	
	
	
	
	
    
    
    /**
    * @Assert\Length(
    *      max = "20",
    *      maxMessage = "El nit no puede tener más de {{ limit }} caracteres."
    * )
    * @Assert\Regex(
    *     pattern="/((^C\/F$)|(^[A-Z0-9]{0,20}$)|(^[A-Z0-9]{0,15}[\-]{1,1}[A-Z0-9]{1,4}$))/",
    *     match=true,
    *     message="El NIT solo puede contener números y letras mayúsculas. No puede tener espacios ni guión."
    * )
    * @ORM\Column(type="string", length=20, nullable=true)
    */
    protected $nitCreacionCopia; 
    
    
    
    
    
    
    
    /**
    * @Assert\Length(
    *      max = "100",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=true)
    */
    protected $nombreCreacionCopia;         
	
	
	
	
	
	
	
	
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        $result = $container->get("doctrine")->getRepository('AcmeTerminalOmnibusBundle:Cliente')
                ->checkExisteCliente($this->nacionalidad, $this->tipoDocumento, $this->dpi, $this->nit, $this->nombre, $this->id);
        if($result['existe'] === true){
            $context->addViolation($result['mensaje']);
        }
        
        if($this->tipoDocumento === null){
            $context->addViolation("Debe seleccionar el tipo de documento");
        }
        
        if($this->nacionalidad === null){
            $context->addViolation("Debe seleccionar la nacionalidad del cliente");
        }
        
        if($this->detallado === true){
            if($this->dpi === null || trim($this->dpi) === ""){
                $context->addViolation("Debe especificar el número del documento");
            }
            if($this->nombre1 === null || trim($this->nombre1) === ""){
                $context->addViolation("Debe especificar el primer nombre del cliente");
            }
            if($this->apellido1 === null || trim($this->apellido1) === ""){
                $context->addViolation("Debe especificar el primer apellido del cliente");
            }
            if($this->sexo === null){
                $context->addViolation("Debe seleccionar el sexo del cliente");
            }
            if($this->fechaNacimiento === null){
                $context->addViolation("Debe especificar la fecha de nacimiento del cliente");
            }
        }else{
            if($this->nombre === null || trim($this->nombre) === ""){
                $context->addViolation("Debe especificar el nombre completo del cliente");
            }
        }
    }
    
    public function getInfo1() {
        $str = $this->nit; 
        if($str === null || trim($str) === ""){
            $str = "CF";
        }
        if($this->nombre !== null && trim($this->nombre) !== ""){
            $str .= " / " . trim($this->nombre);
        }
        if($this->dpi !== null && trim($this->dpi) !== ""){
            $str .= " / " . $this->dpi;
        }else{
            $str .= " / No definido";
        }
        return $str;
    }
    
    public function getInfo2() {
        $str = $this->nit; 
        if($str === null || trim($str) === ""){
            $str = "CF";
        }
        if($this->nombre !== null && trim($this->nombre) !== ""){
            $str .= " - " . trim($this->nombre);
        }
        if($this->dpi !== null && trim($this->dpi) !== ""){
            $str .= " - " . $this->dpi;
        }
        return $str;
    }
    
    public function __toString() {
        return $this->nombre;
    }
    
    function __construct() {
        $this->fechaCreacion = new \DateTime();
        $this->detallado = false;
        $this->empleado = false;
    }

    public function getId() {
        return $this->id;
    }

    public function getNit() {
        return $this->nit;
    }

    public function getDpi() {
        return $this->dpi;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNit($nit) {
        $this->nit = $nit;
    }

    public function setDpi($dpi) {
        $this->dpi = $dpi;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }
    
    public function getNacionalidad() {
        return $this->nacionalidad;
    }

    public function setNacionalidad($nacionalidad) {
        $this->nacionalidad = $nacionalidad;
    }
    
    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }
    
    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function getFechaVencimientoDocumento() {
        return $this->fechaVencimientoDocumento;
    }

    public function getDetallado() {
        return $this->detallado;
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

    public function getEmpleado() {
        return $this->empleado;
    }

    public function getSexo() {
        return $this->sexo;
    }

    public function getFechaNacimiento() {
        return $this->fechaNacimiento;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function setFechaVencimientoDocumento($fechaVencimientoDocumento) {
        $this->fechaVencimientoDocumento = $fechaVencimientoDocumento;
    }

    public function setDetallado($detallado) {
        $this->detallado = $detallado;
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

    public function setEmpleado($empleado) {
        $this->empleado = $empleado;
    }

    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    public function setFechaNacimiento($fechaNacimiento) {
        $this->fechaNacimiento = $fechaNacimiento;
    }
    
    
    
    
    
    
    
    
    
    /**
     * Set nitCreacionCopia
     *
     * @param string $nitCreacionCopia
     * @return Cliente
     */
    public function setNitCreacionCopia($nitCreacionCopia)
    {
        $this->nitCreacionCopia = $nitCreacionCopia;

        return $this;
    }

    /**
     * Get nitCreacionCopia
     *
     * @return string 
     */
    public function getNitCreacionCopia()
    {
        return $this->nitCreacionCopia;
    }

    /**
     * Set nombreCreacionCopia
     *
     * @param string $nombreCreacionCopia
     * @return Cliente
     */
    public function setNombreCreacionCopia($nombreCreacionCopia)
    {
        $this->nombreCreacionCopia = $nombreCreacionCopia;

        return $this;
    }

    /**
     * Get nombreCreacionCopia
     *
     * @return string 
     */
    public function getNombreCreacionCopia()
    {
        return $this->nombreCreacionCopia;
    }        
    
    
    
    
    
    
    
    
    
    
    
}

?>