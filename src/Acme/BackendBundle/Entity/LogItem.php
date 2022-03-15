<?php

namespace Acme\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* @ORM\Entity(repositoryClass="Acme\BackendBundle\Repository\LogItemRepository")
* @ORM\Table(name="custom_log")
*/
class LogItem {
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**  
    * @ORM\Column(type="string", length=255)
    */
    protected $username;
    
    /**  
    * @ORM\Column(type="string", length=255)
    */
    protected $channel;
    
    /**  
    * @ORM\Column(type="string", length=255)
    */
    protected $level;
    
    /**  
    * @ORM\Column(type="string", length=1000)
    */
    protected $message;
    
    /**
    * @ORM\ManyToOne(targetEntity="LogCode")
    * @ORM\JoinColumn(name="codigo", referencedColumnName="codigo", nullable=true)   
    */
    protected $codigo;
    
    /**  
    * @ORM\Column(type="datetime")
    */
    protected $createdAt;
    
    /**  
    * @ORM\Column(type="string", length=10)
    */
    protected $method;
     
    /**  
    * @ORM\Column(type="boolean")
    */
    protected $isAjax;
    
    /**  
    * @ORM\Column(type="string", length=10)
    */
    protected $scheme;
    
    /**  
    * @ORM\Column(type="string", length=1000)
    */
    protected $httpHost;
    
    /**  
    * @ORM\Column(type="string", length=20)
    */
    protected $clientIp;
    
    /**  
    * @ORM\Column(type="boolean")
    */
    protected $isSecure;
    
    /**
    * @ORM\Column(type="text", nullable=true)
    */
    protected $entity;
    
    /**  
    * @ORM\Column(type="string", length=200, nullable=true)
    */
    protected $entityIds;
    
    public function getId() {
        return $this->id;
    }

    public function getChannel() {
        return $this->channel;
    }

    public function getLevel() {
        return $this->level;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setChannel($channel) {
        $this->channel = $channel;
    }

    public function setLevel($level) {
        $this->level = $level;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }
    
    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }
    
    public function getMethod() {
        return $this->method;
    }

    public function getIsAjax() {
        return $this->isAjax;
    }

    public function getScheme() {
        return $this->scheme;
    }

    public function getHttpHost() {
        return $this->httpHost;
    }

    public function getClientIp() {
        return $this->clientIp;
    }

    public function getIsSecure() {
        return $this->isSecure;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function setIsAjax($isAjax) {
        $this->isAjax = $isAjax;
    }

    public function setScheme($scheme) {
        $this->scheme = $scheme;
    }

    public function setHttpHost($httpHost) {
        $this->httpHost = $httpHost;
    }

    public function setClientIp($clientIp) {
        $this->clientIp = $clientIp;
    }

    public function setIsSecure($isSecure) {
        $this->isSecure = $isSecure;
    }
    
    public function getCodigo() {
        return $this->codigo;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }
    
    public function getEntity() {
        return $this->entity;
    }

    public function setEntity($entity) {
        $this->entity = $entity;
    }
    
    public function getEntityIds() {
        return $this->entityIds;
    }

    public function setEntityIds($entityIds) {
        $this->entityIds = $entityIds;
    }    
}

?>