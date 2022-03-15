<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\ItinerarioEspecial;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CrearItinerarioEspecialType extends AbstractType{

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
//       $entityManager = $options['em'];
        $builder->add('fecha', 'datetime', array(
            'label' => 'Fecha',
            'required' => true,
//            'empty_value' => "",
//            'empty_data' => null,
            'read_only' => true,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy HH:mm',
        ));
        
        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
//        var_dump($estacionUsuario);
        //hay que filtrar las rutas que tengan la estacion de origen la misma del operador, si el operador no tiene se listan todas.
        $builder->add('ruta', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Ruta',
            'label' => 'Ruta',   
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span5'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('r')
                        ->where('r.activo=1');
                if($estacionUsuario !== null){
                    $query->andWhere('r.estacionOrigen=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario);
                }
                return $query;
            }
        ));
        
        $builder->add('tipoBus', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:TipoBus',
            'property' => 'info1',
            'label' => 'Tipo Bus', 
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span5')
        ));

        $builder->add('motivo', null, array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span12')
        ));

    }

    public function getName()
    {
        return 'crear_itinerario_especial_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\ItinerarioEspecial',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
        ))
        ->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
        ));
    }
}

?>
