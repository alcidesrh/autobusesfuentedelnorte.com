<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Caja;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CrearCajaType extends AbstractType{

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
        
        $builder->add('moneda', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Moneda',
            'label' => 'Moneda', 
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span8')
        ));
        
        $builder->add('importe', 'text',  array(
            'label' => 'Importe Inicial', 
            'required' => true,
            'read_only' => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8'),
            'pattern' => '.*((^\d{0,8}$)|(^\d{1,8}[\.|,]\d{1,2}$)).*'
        ));
        
        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
//        var_dump($estacionUsuario);
        //hay que filtrar las rutas que tengan la estacion de origen la misma del operador, si el operador no tiene se listan todas.
        $builder->add('usuario', 'entity',  array(
            'class' => 'AcmeBackendBundle:User',
            'label' => 'Usuario',   
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('u')
                        ->where('u.enabled=1');
                if($estacionUsuario !== null){
                    $query->andWhere('u.estacion=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario);
                }
                return $query;
            }
        ));

    }

    public function getName()
    {
        return 'crear_caja_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Caja',
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
