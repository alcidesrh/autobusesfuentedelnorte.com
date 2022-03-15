<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Acme\TerminalOmnibusBundle\Validator\Constraints as CustomAssert;
use Acme\BackendBundle\Services\UtilService;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\ImagenRepository")
* @ORM\Table(name="galeria_imagen")
* @ORM\HasLifecycleCallbacks
*/
class Imagen {
    
    /**
    * @ORM\Id
    * @ORM\Column(type="bigint")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Galeria", inversedBy="imagenes")
    * @ORM\JoinColumn(name="galeria_id", referencedColumnName="id", nullable=false)
    */
    protected $galeria;
    
    /**
    * @Assert\Length(      
    *      max = "255",
    *      maxMessage = "La descripción no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $descripcion;
    
    /**
     * @Assert\File(maxSize="6M")
     */
    protected $file;

    /**
    * @ORM\Column(name="imagen_normal", type="text", nullable=true)
    */
    protected $imagenNormal;
    
    /**
    * @ORM\Column(name="imagen_pequena", type="text", nullable=true)
    */
    protected $imagenPequena;
    
   /**
    * @Assert\Length(      
    *      max = "10",
    *      maxMessage = "El formato no puede tener más de {{ limit }} caracteres de largo"
    * )
    * @ORM\Column(type="string", length=10, nullable=false)
    */
    protected $formato;
    
    public function getId() {
        return $this->id;
    }

    public function getGaleria() {
        return $this->galeria;
    }

    public function getFile() {
        return $this->file;
    }

    public function getImagenNormal() {
        return $this->imagenNormal;
    }

    public function getImagenPequena() {
        return $this->imagenPequena;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setGaleria($galeria) {
        $this->galeria = $galeria;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function setImagenNormal($imagenNormal) {
        $this->imagenNormal = $imagenNormal;
    }

    public function setImagenPequena($imagenPequena) {
        $this->imagenPequena = $imagenPequena;
    }
    
    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    public function getFormato() {
        return $this->formato;
    }

    public function setFormato($formato) {
        $this->formato = $formato;
    }
    
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/uploads/';
    }
    
    public function upload()
    {
        if (null !== $this->file) {
            $this->formato = $this->file->guessExtension();
            $filename = sha1(uniqid(mt_rand(), true)).'.'.$this->formato;
            $pathImagenNormal = $this->getUploadRootDir() . $filename;
            copy($this->file, $pathImagenNormal);
            UtilService::resize($pathImagenNormal, array(
                'max_width' => 1000,
                'max_height' => 800,
            ));
            $fpr = fopen($pathImagenNormal, "r");
            $this->imagenNormal = base64_encode(fread($fpr, filesize($pathImagenNormal)));
            unlink($pathImagenNormal);
            
            $filename = sha1(uniqid(mt_rand(), true)).'.'.$this->formato;
            $pathImagenPequena = $this->getUploadRootDir() . $filename;
            copy($this->file, $pathImagenPequena);
            UtilService::resize($pathImagenPequena);
            $this->imagenPequena = base64_encode(fread(fopen($pathImagenPequena, "r"), filesize($pathImagenPequena)));
            unlink($pathImagenPequena);
            
            unlink($this->file);            
        }
    }
    
}
