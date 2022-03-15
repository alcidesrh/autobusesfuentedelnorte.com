<?php

namespace Acme\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass="Acme\BackendBundle\Repository\JobSyncRepository")
* @ORM\Table(name="job_sync")
*/
class JobSync{
     
    const TYPE_SYNC_DEPARTAMENTO = "1.1";
    const TYPE_SYNC_ESTACION = "2.1";
    const TYPE_SYNC_TIPO_BUS = "2.2";
    const TYPE_SYNC_HORARIO_CICLICO = "2.3";
    const TYPE_SYNC_RUTA = "3.1";
    const TYPE_SYNC_TARIFA_BOLETO = "3.2";
    const TYPE_SYNC_ITINERARIO_CICLICO = "4.1";
    const TYPE_SYNC_ITINERARIO_ESPECIAL = "4.2";
    const TYPE_SYNC_TIEMPO = "4.3";
    const TYPE_SYNC_SALIDA = "5.1";

    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\Column(name="nivel", type="integer")
    */
    protected $nivel; //Nivel de la actualizacion
    
    /** 
    * @ORM\Column(type="string", length=6, nullable=true)
    */
    protected $type;
    
    /**
    * @ORM\Column(name="web1estado", type="smallint")
    */
    protected $web1estado; // 1 - Pendiente, 2 - Fallo, 3 - Actualizado
    
    /**
    * @ORM\Column(name="web2estado", type="smallint")
    */
    protected $web2estado; // 1 - Pendiente, 2 - Fallo, 3 - Actualizado
    
    /**
    * @ORM\Column(name="web3estado", type="smallint")
    */
    protected $web3estado; // 1 - Pendiente, 2 - Fallo, 3 - Actualizado
    
    /**
    * @ORM\Column(name="web4estado", type="smallint")
    */
    protected $web4estado; // 1 - Pendiente, 2 - Fallo, 3 - Actualizado
    
    /**  
    * @ORM\Column(name="data", type="array")
    */
    protected $data;
    
    /**
    * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
    */
    protected $fechaCreacion; //Auto
    
    /**
    * @ORM\ManyToOne(targetEntity="Acme\BackendBundle\Entity\User")
    * @ORM\JoinColumn(name="usuario_creacion_id", referencedColumnName="id", nullable=true)        
    */
    protected $usuarioCreacion;
    
    function __construct() {
        $this->web1estado = 1;
        $this->web2estado = 1;
        $this->web3estado = 1;
        $this->web4estado = 1;
        $this->fechaCreacion = new \DateTime();
    }
    
    public function getWebEstado($idWeb) {
        if($idWeb === "1"){
            return $this->getWeb1estado();
        }else if($idWeb === "2"){
            return $this->getWeb2estado();
        }else if($idWeb === "3"){
            return $this->getWeb3estado();
        }else if($idWeb === "4"){
            return $this->getWeb4estado();
        }
        return "";
    }
    
    public function getId() {
        return $this->id;
    }

    public function getNivel() {
        return $this->nivel;
    }

    public function getWeb1estado() {
        return $this->web1estado;
    }

    public function getWeb2estado() {
        return $this->web2estado;
    }

    public function getData() {
        return $this->data;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNivel($nivel) {
        $this->nivel = $nivel;
    }

    public function setWeb1estado($web1estado) {
        $this->web1estado = $web1estado;
    }

    public function setWeb2estado($web2estado) {
        $this->web2estado = $web2estado;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }
    
    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }
    
    public function getWeb3estado() {
        return $this->web3estado;
    }

    public function getWeb4estado() {
        return $this->web4estado;
    }

    public function setWeb3estado($web3estado) {
        $this->web3estado = $web3estado;
    }

    public function setWeb4estado($web4estado) {
        $this->web4estado = $web4estado;
    }
    
    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }
}

?>