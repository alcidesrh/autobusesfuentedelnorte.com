<?php

namespace Acme\TerminalOmnibusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
* @ORM\Entity(repositoryClass="Acme\TerminalOmnibusBundle\Repository\DiaSemanaRepository")
* @ORM\Table(name="dia_semana")
* @ORM\HasLifecycleCallbacks
* @DoctrineAssert\UniqueEntity(fields ="nombre", message="El nombre ya existe")
*/
class DiaSemana{
    
    const DOMINGO = 'sunday';
    const LUNES = 'monday';
    const MARTES = 'tuesday';
    const MIERCOLES = 'wednesday';
    const JUEVES = 'thursday';
    const VIERNES = 'friday';
    const SABADO = 'saturday';
    
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
    *      max = "10",
    *      minMessage = "El nombre por lo menos debe tener {{ limit }} caracteres.",
    *      maxMessage = "El nombre no puede tener más de {{ limit }} caracteres."
    * )    
    * @ORM\Column(type="string", length=10, unique=true)
    */
    protected $nombre;
    
    public function __toString() {
        return $this->nombre;
    }
    //'sunday' | 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday' |
    public function getPHPValue() {
        switch ($this->id) {
            case 1:
                return DiaSemana::DOMINGO;
            case 2:
                return DiaSemana::LUNES;
            case 3:
                return DiaSemana::MARTES;
            case 4:
                return DiaSemana::MIERCOLES;
            case 5:
                return DiaSemana::JUEVES;
            case 6:
                return DiaSemana::VIERNES;
            case 7:
                return DiaSemana::SABADO;
            default:
                throw new \RuntimeException("Dia de la semana no definido.");
        }
    }
    
    function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
}

?>