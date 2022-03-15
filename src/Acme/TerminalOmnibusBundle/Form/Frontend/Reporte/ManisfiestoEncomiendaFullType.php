<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Reporte;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ManisfiestoEncomiendaFullType extends AbstractType{

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
        
        $builder->add('salida', 'text',  array(
            'label' => 'Salida',
            'virtual' => true,
            'required' => true,
            'attr' =>  array('class' => 'span10')
        ));
        
        $user = $options["user"];
        $estacionUsuario = $user->getEstacion();
        //hay que filtrar las rutas que tengan la estacion de origen la misma del operador, si el operador no tiene se listan todas.
        $optionsEstacionOrigen =  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Origen',
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'attr' =>  array('class' => 'span8'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('u');
                if($estacionUsuario !== null){
                    $query->where('u.id=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario->getId());
                }
                return $query;
            }
        );
        if($estacionUsuario === null){
            $optionsEstacionOrigen['empty_value'] = "";
            $optionsEstacionOrigen['empty_data'] = null;
            $optionsEstacionOrigen['required'] = false;
        }else{
            $optionsEstacionOrigen['read_only'] = true;
            $optionsEstacionOrigen['required'] = true;
        }
        $builder->add('estacionOrigen', 'entity', $optionsEstacionOrigen);
        
        $builder->add('estacionDestino', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación Destino',
            'multiple'  => false,
            'expanded'  => false,
            'virtual' => true,
            'empty_value' => "",
            'empty_data' => null,
            'required' => false,
            'attr' =>  array('class' => 'span8')
        ));
    }

    public function getName()
    {
        return 'manisfiestoEncomiendaFull';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\ReporteModel',
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
