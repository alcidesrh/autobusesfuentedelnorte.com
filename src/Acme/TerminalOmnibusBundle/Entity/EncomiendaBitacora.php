<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Acme\TerminalOmnibusBundle\Entity\Salida;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\EncomiendaBitacoraRepository")
* @ORM\Table(name="encomienda_bitacora")
* @ORM\HasLifecycleCallbacks
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class EncomiendaBitacora {
   
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Encomienda", inversedBy="bitacoras")
    * @ORM\JoinColumn(name="encomienda_id", referencedColumnName="id", nullable=false)
    */
    protected $encomienda;
    
    /**
    * @Assert\NotNull(message = "La estación de la bitácora de encomienda no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion", referencedColumnName="id", nullable=false)   
    */
    protected $estacion;
    
    /**
    * @Assert\NotBlank(message = "La fecha no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(name="fecha", type="datetime", nullable=false)
    */
    protected $fecha;    
    
    /**
    * @Assert\NotNull(message = "El estado no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="EstadoEncomienda")
    * @ORM\JoinColumn(name="estado_id", referencedColumnName="id", nullable=false)        
    */
    protected $estado;
    
    /**
    * @Assert\NotNull(message = "El usuario de la bitácora de encomienda no debe estar en null")
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)        
    */
    protected $usuario;
    
    /**
    * @ORM\ManyToOne(targetEntity="Salida")
    * @ORM\JoinColumn(name="salida_id", referencedColumnName="id", nullable=true)   
    */
    protected $salida;  
    
    /**
    * @ORM\ManyToOne(targetEntity="Cliente")
    * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id", nullable=true)   
    */
    protected $cliente;  
    
    function __construct() {
        $this->fecha = new \DateTime();
    }
    
    public function __toString() {
        $str = "";
        if($this->fecha !== null){
            $str .= "Fecha:" . $this->fecha->format('d-m-Y H:i:s');
        }
        if($this->estacion !== null){
            $str .= "|Estación:" . $this->estacion;
        }

        if($this->estado !== null){
            $str .= "|Estado:" . $this->estado->getNombre();
        }
        
        if($this->salida !== null){
            $str .= "|Salida:" . $this->salida->__toString();
        }
        
        if($this->cliente !== null){
            $str .= "|Cliente:" . $this->cliente->__toString();
        }
        return $str;
    }
    
    public function validacionesGenerales(ExecutionContext $context)
    {
       if($this->salida !== null && 
               $this->estado->getId() !== EstadoEncomienda::EMBARCADA && 
               $this->estado->getId() !== EstadoEncomienda::TRANSITO){
            $context->addViolation("Al estado " . $this->estado->getNombre() . " de la encomienda no se le puede relacionar con una salida."); 
       }
       if($this->estado !== null && 
               ($this->estado->getId() === EstadoEncomienda::EMBARCADA || $this->estado->getId() === EstadoEncomienda::TRANSITO) && 
               $this->salida === null){
            $context->addViolation("El estado " . $this->estado->getNombre() . " de la encomienda requiere que se le especifique una salida."); 
       }
       if($this->estado !== null && $this->estado->getId() === EstadoEncomienda::ENTREGADA){
           if($this->cliente === null){
                $context->addViolation("El estado " . $this->estado->getNombre() . " de la encomienda requiere que se le especifique el cliente que está recibiendo.");
           }else{
               $dpi = $this->cliente->getDpi();
               if($dpi === null || trim($dpi) === "" ){
                   $context->addViolation("El cliente que recibe la encomienda debe tener definido su dpi en el sistema.");
               }
           }
       }
//       if($this->salida !== null && $this->encomienda !== null && $this->encomienda->getRuta() !== null){
//           if($this->salida->getItinerario()->getRuta() !== $this->encomienda->getRuta()){
//               $context->addViolation("La ruta de la encomienda: " . $this->encomienda->getRuta()->__toString() . 
//                       " no coincide con la ruta de la salida: " . $this->salida->getItinerario()->getRuta() . "."); 
//           }
//       }
    }

    public function getId() {
        return $this->id;
    }

    public function getEncomienda() {
        return $this->encomienda;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEncomienda($encomienda) {
        $this->encomienda = $encomienda;
    }

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }
    
    public function getSalida() {
        return $this->salida;
    }

    public function setSalida($salida) {
        $this->salida = $salida;
    }
    
    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }
}
