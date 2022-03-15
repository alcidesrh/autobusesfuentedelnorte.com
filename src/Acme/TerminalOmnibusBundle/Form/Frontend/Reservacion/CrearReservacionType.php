<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reservacion;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Entity\TipoEstacion;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\SalidaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\EstacionToNumberTransformer;

class CrearReservacionType extends AbstractType{

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
        $model = $builder->getData();
        
        $builder->add('fechaSalida', 'date',  array(
            'label' => 'Fecha de Salida',
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'data' => new \DateTime(),
            'attr' =>  array('class' => 'span12 filterSalida')
        ));
        
        $builder->add('estacionOrigen', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Origen de Salida',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'data' => $user->getEstacion(),
            'attr' =>  array('class' => 'span12 filterSalida'),
            'query_builder' => function(EntityRepository $er) {
                $query = $er->createQueryBuilder('e')
                            ->where('e.iniciaRuta=true');
                return $query;
            }
        ));
        
        $builder->add('salida', 'hidden');
        $builder->get('salida')->addModelTransformer(new SalidaToNumberTransformer($entityManager));

        $builder->add('listaClienteReservacion', 'hidden');

    }

    public function getName()
    {
        return 'crear_reservacion_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\ReservacionModel',
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
