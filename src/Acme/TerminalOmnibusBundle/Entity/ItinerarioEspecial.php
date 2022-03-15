<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ItinerarioEspecialRepository")
* @ORM\Table(name="itineario_especial")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
*/
class ItinerarioEspecial extends Itinerario implements \Acme\BackendBundle\Entity\IJobSync{
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime")
    */
    protected $fecha;
    
    /**
    * @ORM\Column(type="text")
    * @Assert\NotBlank(message = "El motivo no debe estar en blanco")
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $motivo;  
    
    /**
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_origen_id", referencedColumnName="id")   
    */
    protected $estacionOrigen;
    
    function __construct() {
        parent::__construct();
        $this->fecha = new \DateTime();
        $this->fecha->setTime($this->fecha->format("H"), 0, 0);
        $this->fecha->modify("+1 hour");
        $this->activo = true;
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["fecha"] = $this->fecha->format('d-m-Y H:i:s');
        $data["estacion"] = $this->estacionOrigen->getId();
        $data["tipoBus"] = $this->tipoBus->getId();
        $data["ruta"] = $this->ruta->getCodigo();
        $data["activo"] = $this->activo;
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
    
    public function getNivelSync(){
        return 4;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_ITINERARIO_ESPECIAL;
    }
    
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        if($this->ruta !== null && $this->estacionOrigen !== null){
            if($this->ruta->getEstacionOrigen() !== $this->estacionOrigen){
                $context->addViolation("La estación de origen debe ser igual a la estación de origen de la ruta.");
            }   
        }
        
        if($this->empresa !== null){
            $empresa = $container->get("doctrine")->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaRuta')
                            ->getEmpresaQueFactura($this->ruta->getCodigo(), $this->fecha);
            if($empresa === null){
                 $context->addViolation("No se pudo obtener la empresa que factura en la ruta: " . $this->ruta ." el día: " . $this->fecha->format('d-m-Y') . ".");
            }else{
                if($empresa !== $this->empresa){
                    $context->addViolation("Según el calendario de facturación de la ruta: " . $this->ruta .", el día: " . $this->fecha->format('d-m-Y') . " la empresa que factura es: " . $empresa->getAlias() . ".");
                }
            }
        }
    }
    
     public function __toString() {
        $str = "Especial";
        if($this->ruta !== null){
            $str .= "|Ruta:" . $this->ruta->getCodigo();
        }
        if($this->fecha !== null){
            $str .= "|Horario:" . $this->fecha->format('d-m-Y H:i:s');
        }
        if($this->tipoBus !== null){
            $str .= "|TipoBus:" . $this->tipoBus->getAlias();
        }
        return $str;
    }
    
    public function getTipoStr() {
        return "Especial";
    }
    
    public function getFecha() {
        return $this->fecha;
    }

    public function getMotivo() {
        return $this->motivo;
    }

    public function getEstacionOrigen() {
        return $this->estacionOrigen;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setMotivo($motivo) {
        $this->motivo = $motivo;
    }

    public function setEstacionOrigen($estacionOrigen) {
        $this->estacionOrigen = $estacionOrigen;
    }
}

?>