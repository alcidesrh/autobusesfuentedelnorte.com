<?php
namespace Acme\BackendBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
* @ORM\Entity(repositoryClass="Acme\BackendBundle\Repository\UserRepository")
* @ORM\Table(name="custom_user")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class User extends BaseUser{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "2",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100)
    */
    protected $names;
    
    /**
    * @Assert\NotBlank(message = "Los apellidos no debe estar en blanco")
    * @Assert\Length(
    *      min = "2",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100)
    */
    protected $surnames;
    
    /**
    * @ORM\Column(type="array", nullable=true)
    */
    protected $ipRanges;
    
    /**
    * @ORM\Column(type="integer")
    */
    protected $intentosFallidos;
    
    /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(type="datetime")
    */
    protected $dateCreate;
            
     /**
    * @Assert\Date(message = "Fecha no valida")
    * @ORM\Column(type="datetime")
    */         
    protected $dateLastUdate;
    
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
    protected $phone;    
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\TerminalOmnibusBundle\Entity\Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $estacion;
    
    /**
    * @ORM\ManyToMany(targetEntity="Acme\TerminalOmnibusBundle\Entity\Estacion")
    * @ORM\JoinTable(name="custom_user_estacion",
    * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
    * inverseJoinColumns={@ORM\JoinColumn(name="estacion_id", referencedColumnName="id")}
    * )     
    */
    protected $estacionesPermitidas;
    
    /**
    * @ORM\ManyToMany(targetEntity="Acme\TerminalOmnibusBundle\Entity\Empresa")
    * @ORM\JoinTable(name="custom_user_empresa",
    * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
    * inverseJoinColumns={@ORM\JoinColumn(name="empresa_id", referencedColumnName="id")}
    * )     
    */
    protected $empresas;
    /**
    * @ORM\Column(type="boolean")
    */
    protected $accessAppWeb;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $accessAppMovil;
    
    /*
     * VALIDACION QUE CADA USUARIO DEBA PERTENECER A UNA ESTACION O A UNA EMPRESA, O LOS DOS A LA VEZ.
     * EL NO TENER UN DE ESTOS ELEMENTOS IMPLICARA QUE PUEDER REVISAR TODO LO DE ESE TEMA.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        if(!in_array("ROLE_ADMIN", $this->roles) && !in_array("ROLE_SUPER_ADMIN", $this->roles))
        {
            if($this->estacion === null && count($this->empresas) <= 0 ){
                 $context->addViolation("Debe seleccionar al menos una estación o una empresa, o las dos opciones.");   
            }
        }
        
        if(count($this->estacionesPermitidas) !== 0 && $this->estacion !== null){
            if(!in_array($this->estacion, $this->estacionesPermitidas->toArray())){
                $context->addViolation("La estación no se encuentra dentro de las lista de estaciones permitidas.");
            }
        }
    }
    
    public function getFullNameByEstacion()
    {
        $info = "";
        if($this->estacion !== null){
            $info .= $this->estacion->getAlias() .  " - ";
        }
        $info .= $this->names  . " " . $this->surnames;
        if($this->email !== null){
            $info .= " (" . trim($this->email) . ")";
        }
        return $info;
    }
    
    public function getFullName()
    {
        return (string) $this->names  . " " . $this->surnames;
    }
    
    public function __toString()
    {
        return (string) $this->getUsername() . " - " . $this->names  . " " . $this->surnames;
    }
    
    public function getIdEmpresas() {
        $idEmpresas = array();
        foreach ($this->empresas as $item) {
            $idEmpresas[] = $item->getId();
        }
        return $idEmpresas;
    }

    public function __construct()
    {
        parent::__construct();
        $this->intentosFallidos = 0;
        $this->empresas = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNames() {
        return $this->names;
    }

    public function getSurnames() {
        return $this->surnames;
    }

    public function getIpRanges() {
        return $this->ipRanges;
    }

    public function getDateCreate() {
        return $this->dateCreate;
    }

    public function getDateLastUdate() {
        return $this->dateLastUdate;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNames($names) {
        $this->names = $names;
    }

    public function setSurnames($surnames) {
        $this->surnames = $surnames;
    }

    public function setIpRanges($ipRanges) {
        $this->ipRanges = $ipRanges;
    }

    public function setDateCreate($dateCreate) {
        $this->dateCreate = $dateCreate;
    }

    public function setDateLastUdate($dateLastUdate) {
        $this->dateLastUdate = $dateLastUdate;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }  
    
    public function getEstacion() {
        return $this->estacion;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }
    
    public function getExpiresAt() {
        return $this->expiresAt;
    }
    
    public function getCredentialsExpireAt() {
        return $this->credentialsExpireAt;
    }
    
    public function getExpired() {
        return $this->expired;
    }
    
    public function getCredentialsExpired() {
        return $this->credentialsExpired;
    }
    
    public function getIntentosFallidos() {
        return $this->intentosFallidos;
    }

    public function setIntentosFallidos($intentosFallidos) {
        $this->intentosFallidos = $intentosFallidos;
    }
    
    public function addIntentosFallidos() {
        $this->intentosFallidos++;
        if($this->intentosFallidos >= 5){
            $this->locked = true;
        }
    }
    
    public function clearIntentosFallidos() {
        $this->intentosFallidos = 0;
        $this->locked = false;
    }
    
    public function getAccessAppWeb() {
        return $this->accessAppWeb;
    }

    public function getAccessAppMovil() {
        return $this->accessAppMovil;
    }

    public function setAccessAppWeb($accessAppWeb) {
        $this->accessAppWeb = $accessAppWeb;
    }

    public function setAccessAppMovil($accessAppMovil) {
        $this->accessAppMovil = $accessAppMovil;
    }
    
    public function getEmpresas() {
        return $this->empresas;
    }

    public function setEmpresas($empresas) {
        $this->empresas = $empresas;
    }
    
    public function getEstacionesPermitidas() {
        return $this->estacionesPermitidas;
    }

    public function setEstacionesPermitidas($estacionesPermitidas) {
        $this->estacionesPermitidas = $estacionesPermitidas;
    }
}

?>
