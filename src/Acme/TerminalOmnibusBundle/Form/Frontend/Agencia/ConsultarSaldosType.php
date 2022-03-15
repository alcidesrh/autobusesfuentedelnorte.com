<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Agencia;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ConsultarSaldosType extends AbstractType{

    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  

        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
        //hay que filtrar las rutas que tengan la estacion de origen la misma del operador, si el operador no tiene se listan todas.
        $optionsEstacion =  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Agencia',
            'property' => 'info1',
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('u');
                $query->andWhere('u.tipo=4'); //Agencias
                $query->andWhere('u.tipoPago=2'); //Prepago
                if($estacionUsuario !== null){
                    $query->andWhere('u.id=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario->getId());
                }
                return $query;
            }
        );
        if($estacionUsuario === null){
            $optionsEstacion['empty_value'] = "";
            $optionsEstacion['empty_data'] = null;
            $optionsEstacion['required'] = false;
        }else{
            $optionsEstacion['read_only'] = true;
            $optionsEstacion['required'] = true;
        }
        $builder->add('estacion', 'entity', $optionsEstacion);
       
    }

    public function getName()
    {
        return 'consultar_saldos_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\ConsultarSaldosAgenciaModel',
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
