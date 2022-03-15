<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PendienteEnvioType extends AbstractType{

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
        
        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
        $builder->add('estacion', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'read_only' => $estacionUsuario !== null,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('e');
                if($estacionUsuario !== null){
                    $query->andWhere('e.id=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario->getId());
                }
                return $query;
            }
        ));
        
    }

    public function getName()
    {
        return 'pendiente_envio_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\GenericModel',
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
