<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\ItinerarioCiclico;
use Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Acme\BackendBundle\Services\UtilService;
use Doctrine\Common\Collections\ArrayCollection;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\SalidaRepository")
* @ORM\Table(name="salida")
* @ORM\HasLifecycleCallbacks
* @CustomAssert\CustomCallback(methods={"validacionesGenerales"})
* @DoctrineAssert\UniqueEntity(fields = {"itinerario" , "fecha"}, message="Ya existe una salida para es itinerario y fecha.")
*/
class Salida implements \Acme\BackendBundle\Entity\IJobSync{
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotNull(message = "El itinerario de la salida no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Itinerario")
    * @ORM\JoinColumn(name="itinerario_id", referencedColumnName="id", nullable=false)
    */
    protected $itinerario; //Itineario que dio lugar a la salida
    
    /**
    * @Assert\NotNull(message = "La empresa no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Empresa")
    * @ORM\JoinColumn(name="empresa_id", referencedColumnName="id", nullable=true)        
    */
    protected $empresa;
    
    /**
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime")
    */
    protected $fecha; //Fecha del viaje, se genera de forma automatica.
    
    /**
    * @Assert\NotNull(message = "El tipo de bus de la salida no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoBus")
    * @ORM\JoinColumn(name="tipo_bus_id", referencedColumnName="id", nullable=false)        
    */
    protected $tipoBus; //Debe tener el mismo valor del itinerario, a no ser que este reasignado que se especifica por IU
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoSalida")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @ORM\Column(name="cancelacion_interna", type="boolean", nullable=true)
    */
    protected $cancelacionInterna;
    
    /**
    * @ORM\ManyToOne(targetEntity="Bus")
    * @ORM\JoinColumn(name="bus_codigo", referencedColumnName="codigo")        
    */
    protected $bus;
    
    /**
    * @ORM\ManyToOne(targetEntity="Piloto")
    * @ORM\JoinColumn(name="piloto_id", referencedColumnName="id")        
    */
    protected $piloto;
    
    /**
    * @ORM\ManyToOne(targetEntity="Piloto")
    * @ORM\JoinColumn(name="piloto_aux_id", referencedColumnName="id")        
    */
    protected $pilotoAux;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $reasignado;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "El motivo no puede tener más de {{ limit }} caracteres de largo"
    * )
    */
    protected $motivoReasignado;
    
    /**
    * @ORM\OneToMany(targetEntity="SalidaBitacora", mappedBy="salida", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $bitacoras;
    
    /**
    * @ORM\OneToOne(targetEntity="Tarjeta", mappedBy="salida")
    */
    protected $tarjeta;
    
    function __construct() {
        $this->cancelacionInterna = false;
        $this->fecha = new \DateTime();
        $this->reasignado = false;
        $this->bitacoras = new ArrayCollection();
    }
    
    public function getDataArrayToSync() {
        $data = array();
        $data["type"] = $this->getTypeSync();
        $data["id"] = $this->id;
        $data["fecha"] = $this->fecha->format('d-m-Y H:i:s');
        $data["estado"] = $this->estado->getId();
        $data["tipoBus"] = $this->tipoBus->getId();
        $data["itinerario"] = $this->itinerario->getId();
        return $data;
    }
    
    public function isValidToSync() {
        return true;
    }
    
    public function getNivelSync(){
        return 5;
    }
    
    public function getTypeSync(){
        return \Acme\BackendBundle\Entity\JobSync::TYPE_SYNC_SALIDA;
    }
    
    public function addBitacoras(SalidaBitacora $item) {  
       $item->setSalida($this);
       $this->getBitacoras()->add($item);
       if($item->getUsuario() === null && intval($item->getEstado()->getId()) === intval(EstadoSalida::CANCELADA)){
           $this->cancelacionInterna = true;
       }else{
           $this->cancelacionInterna = false;
       }
       return $this;
    }
    
    public function __toString() {
        $str = "";
        if($this->getItinerario() !== null){
            
            if($this->getItinerario() instanceof ItinerarioCiclico){
                $str = "Ciclico";
            }
            else if($this->getItinerario() instanceof ItinerarioEspecial){
                $str = "Especial";
            }
            
            if($this->getItinerario()->getRuta() !== null ){
                $str .= "|Ruta:" . $this->getItinerario()->getRuta()->getCodigo();
            }
        }
        if($this->fecha !== null){
            $str .= "|Fecha:" . $this->fecha->format('d-m-Y H:i:s');
        }
        if($this->tipoBus !== null){
            $str .= "|TipoBus:" . $this->tipoBus->getAlias();
        }
        return $str;
    }

     /*
     * VALIDACION QUE LA REASIGNACION ESTE EN CORRESPONDENCIA CON EL TIPO DE BUS
     */
    public function validacionesGenerales(ExecutionContext $context, $container)
    {
        
        if($this->bus !== null && $this->piloto === null){
            $context->addViolation("Debe seleccionar un piloto.");
        }
        if($this->piloto !== null && $this->bus === null){
            $context->addViolation("Debe seleccionar un bus.");
        }
        if($this->pilotoAux !== null && $this->piloto === null){
            $context->addViolation("Debe seleccionar el primer piloto.");   
        }
        if($this->piloto !== null && $this->pilotoAux !== null && $this->piloto === $this->pilotoAux){
            $context->addViolation("Los pilotos de la salida no pueden ser iguales.");
        }
        
        //Si no es nueva
        $user = $container->get('security.context')->getToken()->getUser();
        if($this->id !== null && $this->id !== "" && $this->id !== 0 && $this->id !== "0"){
            //  VALIDANDO SECUENCIA DE ESTADOS
            $doctrine = $container->get("doctrine");
            $estadoActual = $doctrine->getRepository('AcmeTerminalOmnibusBundle:EstadoSalida')->getEstadoSalida($this->id);
            if($estadoActual->getId() === EstadoSalida::PROGRAMADA){
                if($this->estado->getId() !== EstadoSalida::PROGRAMADA && $this->estado->getId() !== EstadoSalida::ABORDANDO && $this->estado->getId() !== EstadoSalida::CANCELADA){
                    $context->addViolation("Una salida en estado programada solamente se puede abordar, cancelar o mantener el estado."); 
                }
                if($this->estado->getId() === EstadoSalida::ABORDANDO || $this->estado->getId() === EstadoSalida::CANCELADA){
                    if($user !== null && $user->getEstacion() !== null && $user->getEstacion()->getId() != $this->itinerario->getRuta()->getEstacionOrigen()->getId()){
                        $context->addViolation("Solamente puede actualizar la salida al estado " . strtolower($this->estado->getNombre()) . " un usuario de la estación " . $this->itinerario->getRuta()->getEstacionOrigen()->__toString()); 
                    }
                }
            }else if($estadoActual->getId() === EstadoSalida::ABORDANDO){
                if($this->estado->getId() !== EstadoSalida::ABORDANDO && $this->estado->getId() !== EstadoSalida::INICIADA && $this->estado->getId() !== EstadoSalida::CANCELADA){
                    $context->addViolation("Una salida en estado abordando solamente se puede iniciar, cancelar o mantener el estado."); 
                }
                
                if($this->estado->getId() === EstadoSalida::INICIADA){
                    if($user !== null && $user->getEstacion() !== null && $user->getEstacion()->getId() != $this->itinerario->getRuta()->getEstacionOrigen()->getId()){
                        $context->addViolation("Solamente puede actualizar la salida al estado iniciada un usuario de la estación " . $this->itinerario->getRuta()->getEstacionOrigen()->__toString());
                    }
                }

            }else if($estadoActual->getId() === EstadoSalida::INICIADA){
                //Si ya esta iniciada no se puede cambiar el bus
                if($this->estado->getId() !== EstadoSalida::INICIADA && $this->estado->getId() !== EstadoSalida::FINALIZADA){
                    $context->addViolation("Una salida en estado iniciada solamente se puede finalizar."); 
                }
                $busActual = $doctrine->getRepository('AcmeTerminalOmnibusBundle:Bus')->getBusSalida($this->id);
                if($this->bus->getCodigo() !== $busActual->getCodigo()){
                    $context->addViolation("No se puede cambiar el bus de una salida en estado iniciada."); 
                }
            }else if($estadoActual->getId() === EstadoSalida::FINALIZADA){
                //Si ya esta finalizada no se puede cambiar el bus, como no se permite ningun tipo de actulializacion ya ta.
                $context->addViolation("Una salida en estado finalizada, no se puede modificar.");
            }else if($estadoActual->getId() === EstadoSalida::CANCELADA){
                 //Si ya esta cancelada no se puede cambiar el bus, como no se permite ningun tipo de actulializacion ya ta.
                $context->addViolation("Una salida en estado cancelada, no se puede modificar.");
            }
        }
        
        if($this->estado->getId() === EstadoSalida::ABORDANDO || $this->estado->getId() === EstadoSalida::INICIADA || 
                $this->estado->getId() === EstadoSalida::FINALIZADA){
            if($this->piloto === null){
                 $context->addViolation("Para poner la salida en estado " . strtolower($this->estado->getNombre()) . ' se debe definir el piloto'); 
            } 
            if($this->bus === null){
                 $context->addViolation("Para poner la salida en estado " . strtolower($this->estado->getNombre()) . ' se debe definir el bus'); 
            }  
        }
        
        if($this->estado->getId() === EstadoSalida::FINALIZADA){
            if($user !== null && $user->getEstacion() !== null && $user->getEstacion()->getId() != $this->itinerario->getRuta()->getEstacionDestino()->getId()){
                $context->addViolation("Solamente puede actualizar la salida al estado " . strtolower($this->estado->getNombre()) . " un usuario de la estación " . $this->itinerario->getRuta()->getEstacionDestino()->__toString()); 
            }
        }
        
    }
    
    public function validar()
    {
//        if($this->estado->getId() === EstadoSalida::PROGRAMADA || $this->estado->getId() === EstadoSalida::ABORDANDO){
//            
//           
//        } 
    }
    
    public function getInfo1() {
        $str = "";
        if($this->fecha !== null){
            $str .= "Fecha:" . $this->fecha->format('d-m-Y h:i A');
        }
        if($this->getItinerario() !== null && $this->getItinerario()->getRuta() !== null){
            $str .= "|Ruta:" . $this->getItinerario()->getRuta()->getNombre();
        }
        if($this->id !== null){
            $str .= "|Id:" . strval($this->id);
        }
        if($this->empresa !== null){
            $str .= "|Empresa:" . $this->empresa->getAlias();
        }
        if($this->bus !== null){
            $str .= "|Bus:" . $this->bus->getCodigo();
        }
        if($this->estado !== null){
            $str .= "|Estado:" . $this->estado->getNombre();
        }
        return $str;
    }
    
    public function getInfo2() {
        $str = "";
        if($this->id !== null){
            $str .= "Id:" . strval($this->id);
        }
        if($this->getItinerario() !== null){
            
            if($this->getItinerario() instanceof ItinerarioCiclico){
                $str .= "|Ciclico";
            }
            else if($this->getItinerario() instanceof ItinerarioEspecial){
                $str .= "|Especial";
            }
            
            if($this->getItinerario()->getRuta() !== null ){
                $str .= "|Ruta:" . $this->getItinerario()->getRuta()->__toString();
            }
        }
        if($this->fecha !== null){
            $str .= "|Fecha:" . $this->fecha->format('d-m-Y H:i:s');
        }
        return $str;
    }
    
    public function getInfo3() {
        $str = "";
        if($this->fecha !== null){
            $str .= "HORA:" . $this->fecha->format('h:i A');
        }
        if($this->getItinerario() !== null && $this->getItinerario()->getRuta() !== null){
            $str .= "|Ruta:" . $this->getItinerario()->getRuta()->getNombre();
        }
        if($this->getItinerario() !== null && $this->getItinerario()->getTipoBus() !== null ){
            $str .= "|CLASE:" . $this->getItinerario()->getTipoBus()->getClase()->getNombre();
        }
        if($this->id !== null){
            $str .= "|Id:" . strval($this->id);
        }
        if($this->empresa !== null){
            $str .= "|Empresa:" . $this->empresa->getAlias();
        }
        if($this->bus !== null){
            $str .= "|Bus:" . $this->bus->getCodigo();
        }
        if($this->estado !== null){
            $str .= "|Estado:" . $this->estado->getNombre();
        }
        return $str;
    }
    
    public function getInfo4() {
        $str = "";
        if($this->fecha !== null){
            $str .= "HORA:" . $this->fecha->format('h:i A');
        }
        if($this->getItinerario() !== null && $this->getItinerario()->getRuta() !== null){
            $str .= "|Ruta:" . $this->getItinerario()->getRuta()->getNombre();
        }
        return $str;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getItinerario() {
        return $this->itinerario;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getTipoBus() {
        return $this->tipoBus;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getBus() {
        return $this->bus;
    }

    public function getPiloto() {
        return $this->piloto;
    }
    
    public function setId($id) {
        $this->id = $id;
    }

    public function setItinerario($itinerario) {
        $this->itinerario = $itinerario;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setTipoBus($tipoBus) {
        $this->tipoBus = $tipoBus;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setBus($bus) {
        $this->bus = $bus;
    }

    public function setPiloto($piloto) {
        $this->piloto = $piloto;
    }
    
    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    public function getPilotoAux() {
        return $this->pilotoAux;
    }

    public function setPilotoAux($pilotoAux) {
        $this->pilotoAux = $pilotoAux;
    }
    
    public function getBitacoras() {
        return $this->bitacoras;
    }

    public function setBitacoras($bitacoras) {
        $this->bitacoras = $bitacoras;
    }
    
    public function getCancelacionInterna() {
        return $this->cancelacionInterna;
    }

    public function setCancelacionInterna($cancelacionInterna) {
        $this->cancelacionInterna = $cancelacionInterna;
    }
    
    public function getTarjeta() {
        return $this->tarjeta;
    }

    public function setTarjeta($tarjeta) {
        $this->tarjeta = $tarjeta;
    }
}

?>