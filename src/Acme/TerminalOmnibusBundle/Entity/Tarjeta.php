<?php
namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\BackendBundle\Services\UtilService;
use Acme\TerminalOmnibusBundle\Entity\Talonario;
use Acme\TerminalOmnibusBundle\Entity\TarjetaBitacora;
use Acme\TerminalOmnibusBundle\Entity\EstadoCorteVentaTalonario;
use Acme\TerminalOmnibusBundle\Entity\TipoTarjeta;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarjetaRepository")
* @ORM\Table(name="tarjeta", uniqueConstraints={@ORM\UniqueConstraint(name="CUSTOM_IDX_TARJETA_TIPO_NUMERO", columns={"tipo_id", "numero"})})
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields ={"tipo", "numero"}, message="Ya existe la combinación de tipo de tarjeta y número en el sistema")
*/
class Tarjeta{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "La salida de la tarjeta no debe estar en blanco")
    * @ORM\OneToOne(targetEntity="Salida", inversedBy="tarjeta")
    * @ORM\JoinColumn(name="salida_id", referencedColumnName="id", nullable=false)
    */
    protected $salida;
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoTarjeta")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @Assert\NotNull(message = "El tipo de tarjeta no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoTarjeta")
    * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id", nullable=true)        
    */
    protected $tipo;
    
    /**
    * @Assert\NotNull(message = "El numero de la tarjeta no debe estar en blanco")
    * @Assert\Range(
    *      min = 1,
    *      max = 9999999999,
    *      minMessage = "El numero de la tarjeta no puede ser menor que {{ limit }}",
    *      maxMessage = "El numero de la tarjeta no puede ser mayor que {{ limit }}"
    * )
    * @ORM\Column(name="numero", type="bigint", nullable=false)
    */
    protected $numero;
    
    /**
    * @ORM\OneToMany(targetEntity="Talonario", mappedBy="tarjeta", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaTalonarios;
    
    /**
    * @ORM\Column(name="fecha_conciliacion", type="datetime", nullable=true)
    */
    protected $fechaConciliacion;
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_conciliacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioConciliacion;
    
    /**
    * @ORM\Column(name="observacion_conciliacion", type="text", nullable=true)
    */
    protected $observacionConciliacion;
    
    /**
    * @ORM\OneToMany(targetEntity="TarjetaBitacora", mappedBy="tarjeta", cascade={"persist", "remove"}, orphanRemoval=true)
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
    * @Assert\NotNull(message = "El usuario de creacion no debe estar en null")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuarioCreacion;
      
    public function __toString() {
        return strval($this->id);
    }   
    
    public function getAlias() {
        return ($this->tipo == null ? "" : $this->tipo->getSigla()) . strval($this->numero);
    }
    
    function __construct($usuarioCreacion = null){
        $this->manual = false;
        $this->fechaCreacion = new \DateTime();
        $this->usuarioCreacion = $usuarioCreacion;
        if($usuarioCreacion !== null){
            $this->estacionCreacion = $usuarioCreacion->getEstacion();
        }
        $this->listaTalonarios = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bitacoras = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /*
    * VALIDACION 
    */
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        $fechaSalida = clone $this->getSalida()->getFecha();
        $fechaSalida->setTime(0, 0, 0);
        if(UtilService::compararFechas($this->fechaCreacion, $fechaSalida) != 0){
//            $context->addViolation("La tarjeta solo se puede crear el mismo dia de la salida.");
        }
        
        if($this->tipo !== null){
            if(intval($this->tipo->getId()) == TipoTarjeta::MANUAL){
            
            }else if(intval($this->tipo->getId()) == TipoTarjeta::AUTOMATICA){
                if($this->salida !== null && intval($this->numero) != intval($this->salida->getId())){
                    $context->addViolation("El número de las tarjetas automáticas debe ser igual al identificador de la salida.");
                }
            }
        }
    }
    
    public function getMapDiferencias(){
        $map = array();
        $list = $this->getListCortesVenta();
        foreach ($list as $corteVenta) {
            $estadoCorteVenta = $corteVenta->getEstado()->getId();
            if($estadoCorteVenta !== EstadoCorteVentaTalonario::ANULADO){
                $diff = floatval($corteVenta->getImporteTotal() - $corteVenta->getImporteTotalItems());
                if($diff != 0){
                    $total = 0;
                    $user = $corteVenta->getUsuarioCreacion();
                    if(isset($map[$user->getId()])){
                        $total = $map[$user->getId()]["total"];
                    }else{
                        $map[$user->getId()] = array(
                            "total" => $total,
                            "user" => $user
                        );
                    }
                    $total += $diff;
                    $map[$user->getId()]["total"] = $total;
                }  
            }
        }
        return $map;
    }
    
    public function getListCortesVenta(){
        $result = array();
        foreach ($this->getListaTalonarios() as $talonario) {
            foreach ($talonario->getListaCorteVentaTalonario() as $corteVenta) {
                $result[] = $corteVenta;
            }
        }
        return $result;
    }
    
    public function checkCortesVentaTerminados(){
        foreach ($this->getListaTalonarios() as $talonario) {
            foreach ($talonario->getListaCorteVentaTalonario() as $corteVenta) {
                $estadoCorteVenta = $corteVenta->getEstado()->getId();
                if(!( $estadoCorteVenta === EstadoCorteVentaTalonario::TERMINADO || $estadoCorteVenta === EstadoCorteVentaTalonario::ANULADO)){
                    return false;
                }
            }
        }
        return true;
    }
    
    public function getAverageCortesVentaTerminados(){
        $total = 0;
        $terminados = 0;
        $anulados = 0;
        foreach ($this->getListaTalonarios() as $talonario) {
            foreach ($talonario->getListaCorteVentaTalonario() as $corteVenta) {
                $total++;
                $estadoCorteVenta = $corteVenta->getEstado()->getId();
                if($estadoCorteVenta === EstadoCorteVentaTalonario::TERMINADO){
                    $terminados++;
                }
                else if($estadoCorteVenta === EstadoCorteVentaTalonario::ANULADO){
                    $anulados++;
                }
            }
        }
        return array(
            'total' => $total,
            'terminados' => $terminados,
            'anulados' => $anulados,
        );
    }
    
    public function checkCortesVentaSuccess(){
        foreach ($this->getListaTalonarios() as $talonario) {
            foreach ($talonario->getListaCorteVentaTalonario() as $corteVenta) {
                $estadoCorteVenta = $corteVenta->getEstado()->getId();
                if($estadoCorteVenta !== EstadoCorteVentaTalonario::ANULADO){
                    if(floatval($corteVenta->getImporteTotal()) != floatval($corteVenta->getImporteTotalItems())){
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    public function addBitacoras(TarjetaBitacora $item) {  
       $item->setTarjeta($this);
       $this->getBitacoras()->add($item);
       return $this;
    }
    
    public function initChilds($user) {
       for ($index = 0; $index < 3; $index++) {
           $this->addListaTalonarios(new Talonario($user));
       }
    }
    
    public function addListaTalonarios(Talonario $item) {  
       $item->setTarjeta($this);
       $this->getListaTalonarios()->add($item);
       return $this;
    }
    
    public function removeTalonarios(Talonario $item) {  
       $this->getListaTalonarios()->removeElement($item); 
       $item->setTarjeta(null);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getSalida() {
        return $this->salida;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getListaTalonarios() {
        return $this->listaTalonarios;
    }

    public function getFechaConciliacion() {
        return $this->fechaConciliacion;
    }

    public function getUsuarioConciliacion() {
        return $this->usuarioConciliacion;
    }

    public function getObservacionConciliacion() {
        return $this->observacionConciliacion;
    }

    public function getBitacoras() {
        return $this->bitacoras;
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

    public function setSalida($salida) {
        $this->salida = $salida;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setListaTalonarios($listaTalonarios) {
        $this->listaTalonarios = $listaTalonarios;
    }

    public function setFechaConciliacion($fechaConciliacion) {
        $this->fechaConciliacion = $fechaConciliacion;
    }

    public function setUsuarioConciliacion($usuarioConciliacion) {
        $this->usuarioConciliacion = $usuarioConciliacion;
    }

    public function setObservacionConciliacion($observacionConciliacion) {
        $this->observacionConciliacion = $observacionConciliacion;
    }

    public function setBitacoras($bitacoras) {
        $this->bitacoras = $bitacoras;
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
    
    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
}

?>