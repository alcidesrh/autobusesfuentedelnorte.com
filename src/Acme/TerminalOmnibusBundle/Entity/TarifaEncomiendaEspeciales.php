<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Entity\TarifaEncomienda;
use Symfony\Component\Validator\ExecutionContext;


/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\TarifaEncomiendaEspecialesRepository")
* @ORM\Table(name="tarifas_encomienda_especiales")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields = {"tipo" , "fechaEfectividad"}, 
* message="Ya existe una tarifa para la combinación especficada de tipo y fecha de efectividad.")
* @Assert\Callback(methods={"validacionesGenerales"})
*/
class TarifaEncomiendaEspeciales extends TarifaEncomienda{
    
    /**
    * @Assert\NotNull(message = "La tipo no debe estar en blanco")
    * @ORM\ManyToOne(targetEntity="TipoEncomiendaEspeciales")
    * @ORM\JoinColumn(name="tipo_encomienda_especial_id", referencedColumnName="id", nullable=false)      
    */
    protected $tipo;
    
    /**
    * @Assert\NotBlank(message = "La fecha de efectividad no debe estar en blanco")
    * @Assert\DateTime(message = "Tiempo no valido")
    * @ORM\Column(type="datetime", nullable=false)
    */
    protected $fechaEfectividad;
    
    /**
    * @Assert\NotBlank(message = "El valor no debe estar en blanco")
    * @Assert\Regex(
    *     pattern="/((^\d{0,5}$)|(^\d{1,5}[\.|,]\d{1,2}$))/",
    *     match=true,
    *     message="El valor solo puede contener números"
    * )
    * @Assert\Range(
    *      min = "0",
    *      max = "99999.99",
    *      minMessage = "El valor no debe ser menor que {{ limit }}.",
    *      maxMessage = "El valor no debe ser mayor que {{ limit }}.",
    *      invalidMessage = "El valor debe ser un número válido."
    * )   
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=false)
    */
    protected $tarifaValor;
    
    public function __toString() {
        $str = "Especial|ID: " . $this->id;
        if($this->tipo !== null){
            $str .= "|Nombre:" . $this->tipo->getNombre();
        }
        if($this->fechaEfectividad !== null){
            $str .= "|Fecha:" . $this->fechaEfectividad->format('d-m-Y H:i:s');
        }
        return $str;
    }
    
     /*
     * VALIDACION 
     */
    public function validacionesGenerales(ExecutionContext $context)
    {
        parent::validacionesGenerales($context);
        
    }
    
    public function calcularTarifa() {
        return $this->tarifaValor;
    }
    
    function __construct() {
        parent::__construct();
        $this->fechaEfectividad = new \DateTime();
    }
    
    public function getTipo() {
        return $this->tipo;
    }

    public function getFechaEfectividad() {
        return $this->fechaEfectividad;
    }

    public function getTarifaValor() {
        return $this->tarifaValor;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setFechaEfectividad($fechaEfectividad) {
        $this->fechaEfectividad = $fechaEfectividad;
    }

    public function setTarifaValor($tarifaValor) {
        $this->tarifaValor = $tarifaValor;
    }

}

?>