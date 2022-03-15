<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TipoBusRepository")
* @ORM\Table(name="bus_tipo")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="alias", message="El alias ya existe")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class TipoBus implements \Acme\BackendBundle\Entity\IJobSync{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\Length(
    *      min = "1",
    *      max = "10",
    *      minMessage = "El alias por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El alias no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=10, unique=true)
    */
    protected $alias;
    
    /**
    * @ORM\Column(type="text")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $descripcion;
    
    /**
    * @Assert\NotBlank(message = "La clase no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="ClaseBus")
    * @ORM\JoinColumn(name="clase_id", referencedColumnName="id")        
    */
    protected $clase;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $nivel2;
    
    /**
    * @Assert\NotBlank(message = "El total de asientos no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/^\d*$/",
    *     message="El total de asientos solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El total de asientos no debe ser menor que {{ limit }}.",
    *      maxMessage = "El total de asientos no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El total de asientos debe ser un número válido."
    * )   
    * @ORM\Column(type="integer")
    */
    protected $totalAsientos;
    
    /**
    * @ORM\ManyToMany(targetEntity="ServicioBus")
    * @ORM\JoinTable(name="bus_servicio_union",
    *   joinColumns={@ORM\JoinColumn(name="bus_id", referencedColumnName="id")},
    *   inverseJoinColumns={@ORM\JoinColumn(name="servicio_id", referencedColumnName="id")}
    * )
    */
    protected $listaServicios;
   
    /**
    * @ORM\Column(type="boolean")
    */
    protected $activo;
    
    /**
    * @ORM\OneToMany(targetEntity="AsientoBus", mappedBy="tipoBus", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $listaAsiento;
    protected $listaAsientoHidden;
    
    /**
    * @ORM\OneToMany(targetEntity="SenalBus", mappedBy="tipoBus", 
    * cascade={"persist", "remove"}, orphanRemoval=true)
    */    
    protected $listaSenal;
    protected $listaSenalHidden;
    
    function __construct() {
        $this->nivel2 = false;
        $this->totalAsientos = 0;
        $this->listaServicios = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listaAsiento = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listaSenal = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["alias"] = $this->alias;
        $data["nivel2"] = $this->nivel2;
        $data["clase"] = $this->getClase()->getId();
        $asientos = array();
        foreach ($this->listaAsiento as $item) {
            $asientos[] = array(
                "id" => $item->getId(),
                "numero" => $item->getNumero(),
                "coordenadaX" => $item->getCoordenadaX(),
                "coordenadaY" => $item->getCoordenadaY(),
                "clase" => $item->getClase()->getId(), 
                "nivel2" => $item->getNivel2()
            );
        }
        $data["asientos"] = $asientos;
        $senales = array();
        foreach ($this->listaSenal as $item) {
            $senales[] = array(
                "id" => $item->getId(),
                "coordenadaX" => $item->getCoordenadaX(),
                "coordenadaY" => $item->getCoordenadaY(),
                "tipo" => $item->getTipo()->getId(),     
                "nivel2" => $item->getNivel2()
            );
        }
        $data["senales"] = $senales;
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
   
    public function getNivelSync(){
        return 2;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_TIPO_BUS;
    }
    
    /*
     * VALIDACION DE QUE DOS ELEMENTOS NO TENGAN LA MISMA POSICION EN EL MAPA.
     * VALIDACION DE QUE SE SELECCIONE LOS ASIENTOS ADECUADOS A LOS PERMITIDOS PARA LA CLASE DE BUS.
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        $listaClasesAsientoPermitidas = $this->clase->getListaClaseAsiento()->toArray();
        $strListaClasesAsientoPermitidas = "";
        foreach ($listaClasesAsientoPermitidas as $item) {
            $strListaClasesAsientoPermitidas .= " ," . $item->getNombre();
        } 
        $count = 1;
        $strListaClasesAsientoPermitidas = str_replace(",","", $strListaClasesAsientoPermitidas, $count); 
        $listaAsientos = $this->getListaAsiento();
        $listaSenales = $this->getListaSenal();
        $mapPosicionesTotal = array();
        foreach ($listaAsientos as $item) {
            $y = "Fila:" . $item->getCoordenadaY();
            $x = "Columna:". $item->getCoordenadaX();
            $idNivel = ($item->getNivel2() == true)  ? "Nivel:2" : "Nivel:1";
            $clave = $idNivel . "," . $x . "," . $y;
            if(isset($mapPosicionesTotal[$clave]))
            {
                $context->addViolation("Los asientos " . $item . " y " . $mapPosicionesTotal[$clave] . " tienen la misma posición en el bus."
                        . "Posición con problema:" . $clave . ".");            
            }
            else{
                $mapPosicionesTotal[$clave] = $item;
            }
                       
            if(!in_array($item->getClase() , $listaClasesAsientoPermitidas)){
                $context->addViolation("El asiento " . $item . " tiene la clase " .  $item->getClase()->getNombre() . 
                        " que no está permitida para la clase de bus: " . $this->clase->getNombre() . ". " .
                        " Las clases permitidas son: " . $strListaClasesAsientoPermitidas . ".");   
            }
        }
        
        foreach ($listaSenales as $item) {
            $y = "Fila:" . $item->getCoordenadaY();
            $x = "Columna:". $item->getCoordenadaX();
            $idNivel = ($item->getNivel2() == true)  ? "Nivel:2" : "Nivel:1";
            $clave = $idNivel . "," . $x . "," . $y;
            if(isset($mapPosicionesTotal[$clave]))
            {
                $context->addViolation("La señal " . $item . " coincide en la misma posición con otro elemento. "
                        . "Posición con problema:" . $clave . ".");            
            }
            else{
                $mapPosicionesTotal[$clave] = $item;
            }
        }
  
    }
    
    public function addListaAsiento(AsientoBus $item) {
       $item->setTipoBus($this);
       $this->getListaAsiento()->add($item);
       return $this;
    }
    
    public function removeListaAsiento($item) {
        $this->getListaAsiento()->removeElement($item); 
        $item->setTipoBus(null);
    }
    
    public function addListaSenal(SenalBus $item) {
       $item->setTipoBus($this);
       $this->getListaSenal()->add($item);
       return $this;
    }
    
    public function removeListaSenal($item) { 
        $this->getListaSenal()->removeElement($item); 
        $item->setTipoBus(null);        
    }
    
    public function __toString() {
        if ($this->alias != null && trim($this->alias) != "") {
            return trim($this->alias) . " - " . $this->descripcion;
        } else {
            return $this->alias;
        }
    }
    
    public function getInfo1() {
        $text = $this->alias;
        if ($this->clase != null) {
            $text .= " - " . $this->clase->getNombre();
        }
        if ($this->descripcion != null && trim($this->descripcion) != "") {
            $text .= " - " . $this->descripcion;
        }
        return $text;
    }

    public function getId() {
        return $this->id;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getClase() {
        return $this->clase;
    }

    public function getNivel2() {
        return $this->nivel2;
    }

    public function getTotalAsientos() {
        return $this->totalAsientos;
    }

    public function getListaServicios() {
        return $this->listaServicios;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getListaAsiento() {
        return $this->listaAsiento;
    }

    public function getListaAsientoHidden() {
        return $this->listaAsientoHidden;
    }

    public function getListaSenal() {
        return $this->listaSenal;
    }

    public function getListaSenalHidden() {
        return $this->listaSenalHidden;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setClase($clase) {
        $this->clase = $clase;
    }

    public function setNivel2($nivel2) {
        $this->nivel2 = $nivel2;
    }

    public function setTotalAsientos($totalAsientos) {
        $this->totalAsientos = $totalAsientos;
    }

    public function setListaServicios($listaServicios) {
        $this->listaServicios = $listaServicios;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setListaAsiento($listaAsiento) {
        $this->listaAsiento = $listaAsiento;
    }

    public function setListaAsientoHidden($listaAsientoHidden) {
        $this->listaAsientoHidden = $listaAsientoHidden;
    }

    public function setListaSenal($listaSenal) {
        $this->listaSenal = $listaSenal;
    }

    public function setListaSenalHidden($listaSenalHidden) {
        $this->listaSenalHidden = $listaSenalHidden;
    }

}

?>