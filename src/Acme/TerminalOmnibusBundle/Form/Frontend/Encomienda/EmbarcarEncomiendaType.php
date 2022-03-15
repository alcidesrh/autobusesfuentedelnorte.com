<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Entity\Encomienda;

class EmbarcarEncomiendaType extends AbstractType{
    
    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        /*****************************************
         *          OTRAS PROPIEDADES
         * ***************************************
         *  'required' => true,
         *  'read_only' => true,
         *  
         */ 

       $entityManager = $options['em'];
       $user = $options['user'];
       $encomienda = $builder->getData();
       $builder->add('id', 'hidden');
       
       $fecha = new \DateTime();
       $fecha->modify("-3 day");
       $fecha->setTime(0, 0, 0); //Hora, minuto, y segundos
                
       $rutas = array();
       foreach ($encomienda->getRutas() as $ruta){
          $rutas[] = $ruta->getRuta()->getCodigo();
       }
       
       
       $idEstacion = null;
       if($user->getEstacion() !== null){
           $idEstacion = $user->getEstacion()->getId();
       }
                
       //SE LISTAN LAS SALIDAS QUE ESTAN EN TRANSITO PARA LOS USUARIOS DE LAS ESTACIONES INTERMEDIAS DE LA RUTA.
       //SE LISTAN LAS SALIDAS QUE ESTAN EN PROGRAMADAS Y ABORDANDO, PARA LOS USUARIOS DE LA ESTACION DE ORIGEN DE LA RUTA.  
       $builder->add('salida', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Salida',
            'label' => 'Salida',
            'property' => 'info1',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($fecha, $rutas, $idEstacion) {
                $query = $er->createQueryBuilder('s')
                        ->leftJoin("s.estado", "e")
                        ->leftJoin("s.itinerario", "i")
                        ->leftJoin("i.ruta", "r")
                        ->leftJoin("r.estacionOrigen", "eor")
                        ->leftJoin("r.listaEstacionesIntermediaOrdenadas", "leio")
                        ->leftJoin("leio.estacion", "eir")
                        
                        ->where('i.activo=1')
                        ->andWhere("r.codigo IN (:rutas)")
                        ->andWhere("s.fecha > :fecha")
                        ->setParameter("rutas", $rutas)
                        ->setParameter("fecha", $fecha->format('d-m-Y H:i:s'));
                
                 if($idEstacion !== null){
                     $query->andWhere("(e.id IN (1,2) and eor.id=:idEstacionUsuario) or (e.id IN (3) and eir.id=:idEstacionUsuario)");
                     $query->setParameter("idEstacionUsuario", $idEstacion);
                 }
                 
                 return $query;
            }
        ));
    }

    public function getName()
    {
        return 'embarcar_encomienda_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Encomienda',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
            'em'
        ))
        ->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
