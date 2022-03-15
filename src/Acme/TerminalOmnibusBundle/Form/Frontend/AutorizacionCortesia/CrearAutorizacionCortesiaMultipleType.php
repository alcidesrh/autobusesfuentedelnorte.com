<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\AutorizacionCortesia;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CrearAutorizacionCortesiaMultipleType extends AbstractType{

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
        $user = $options['user'];
        $builder->add('cantidad', 'integer', array(
             'label' => 'Cantidad',
             'required' => true,
             'attr' =>  array('class' => 'span8')
        ));
        
        $builder->add('motivo', 'textarea', array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span8')
        ));
        
        $builder->add('usuarioNotificacion', 'entity',  array(
            'class' => 'AcmeBackendBundle:User',
            'label' => 'Notificar al correo de',   
            'property' => 'fullNameByEstacion',
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) {
                $query = $er->createQueryBuilder("u")
                            ->leftJoin("u.estacion", "e")
                            ->andWhere("u.enabled=1 AND u.locked=0 AND u.expired=0 AND u.credentialsExpired=0")
                            ->andWhere("e.id is not null")
                            ->andWhere("(u.roles LIKE '%ROLE_SUPERVISOR_BOLETO%' or u.roles LIKE '%ROLE_VENDEDOR_BOLETOS%') and u.roles NOT LIKE '%ROLE_PROPIETARIO%' and u.roles NOT LIKE '%ROLE_EMISOR_CORTESIA%'")
                            ->orderBy("e.id");
                return $query;
            }
        ));
    }

    public function getName()
    {
        return 'crear_autorizacion_cortesia_multiple_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\AutorizacionCortesiaMultiplesModel',
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
