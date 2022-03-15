<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionCortesia;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\ClienteToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\SalidaToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\EstacionToNumberTransformer;

class CrearBoletoAutorizacionCortesiaType extends AbstractType{

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
        
        $builder->add('motivo', 'textarea', array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span8')
        ));
        
        $builder->add('cliente', 'text',  array(
            
            
            
            
            
            
            
            
            
            'label' => 'CLIENTE: NIT (sin guión) / NOMBRE / DOCUMENTO',
            
            
            
            
            
            
            
            
            
            
            
            'required' => true,
            'attr' =>  array('class' => 'span8 autocheck')
        ));
        $builder->get('cliente')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
        
        $builder->add('fechaSalida', 'date',  array(
            'label' => 'Fecha de Salida',
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'data' => new \DateTime(),
            'attr' =>  array('class' => 'span8 filterSalida')
        ));
        
        $builder->add('estacionOrigen', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Origen de Salida',
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'data' => $user->getEstacion(),
            'attr' =>  array('class' => 'span8 filterSalida'),
            'query_builder' => function(EntityRepository $er) {
                $query = $er->createQueryBuilder('e')
                            ->andWhere('e.iniciaRuta=true and e.activo=true');
                return $query;
            }
        ));
        
        $builder->add('salida', 'text',  array(
            'label' => 'Salida',
            'required' => true,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->get('salida')->addModelTransformer(new SalidaToNumberTransformer($entityManager));
        
        $builder->add('estacionSubeEn', 'text',  array(
            'label' => 'Sube En',
            'required' => true,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->get('estacionSubeEn')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        
        $builder->add('estacionBajaEn', 'text',  array(
            'label' => 'Baja En',
            'required' => true,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->get('estacionBajaEn')->addModelTransformer(new EstacionToNumberTransformer($entityManager));
        
        $builder->add('listaBoleto', 'hidden');
        
        $builder->add('movil', 'hidden');
        $builder->add('impresorasDisponibles', 'hidden');
        
    }

    public function getName()
    {
        return 'crear_boleto_autorizacion_cortesia_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\EmitirBoletoCortesiaModel',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
            'em'
        ))->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
