<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ImpresoraRepository")
* @ORM\Table(name="impresora")
* @ORM\HasLifecycleCallbacks
*/
class Impresora{
    
     /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @Assert\NotBlank(message = "El nombre no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=false)
    */
    protected $nombre; 
    
    /**
    * @ORM\ManyToOne(targetEntity="Estacion")
    * @ORM\JoinColumn(name="estacion_id", referencedColumnName="id", nullable=true)
    */
    protected $estacion;
    
    /**
    * @Assert\NotBlank(message = "El path no debe estar en blanco")
    * @Assert\Length(
    *      min = "1",
    *      max = "100",
    *      minMessage = "El path por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El path no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=100, nullable=false)
    */
    protected $path;  //nombre de red
    
    /**
    * @Assert\NotBlank(message = "El tamaño de pagina no debe estar en blanco")
    * @ORM\Column(type="integer", nullable=false)
    */
    protected $idTamanoPagina;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $autoPrint;
    
    /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $espacioLetras;
    
    /**
    * @ORM\ManyToOne(targetEntity="TipoImpresora")
    * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id", nullable=true)   
    */
    protected $tipoImpresora;

    /**
    * @ORM\Column(type="boolean", nullable=false)
    */
    protected $activo;
    
    public function __toString() {
        return $this->nombre;
    }
    
    function __construct() {
        $this->activo = true;
        $this->autoPrint = false;
        $this->espacioLetras = false;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getEstacion() {
        return $this->estacion;
    }

    public function getPath() {
        return $this->path;
    }

    public function getIdTamanoPagina() {
        return $this->idTamanoPagina;
    }

    public function getAutoPrint() {
        return $this->autoPrint;
    }

    public function getEspacioLetras() {
        return $this->espacioLetras;
    }

    public function getTipoImpresora() {
        return $this->tipoImpresora;
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

    public function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function setIdTamanoPagina($idTamanoPagina) {
        $this->idTamanoPagina = $idTamanoPagina;
    }

    public function setAutoPrint($autoPrint) {
        $this->autoPrint = $autoPrint;
    }

    public function setEspacioLetras($espacioLetras) {
        $this->espacioLetras = $espacioLetras;
    }

    public function setTipoImpresora($tipoImpresora) {
        $this->tipoImpresora = $tipoImpresora;
    }
    
    public function setActivo($activo) {
        $this->activo = $activo;
    }
}

?>