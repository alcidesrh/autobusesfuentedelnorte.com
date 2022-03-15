<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\ClienteToNumberTransformer;
use Acme\TerminalOmnibusBundle\Form\DataTransformer\FacturaToNumberTransformer;

class PendienteEntregaType extends AbstractType{

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
        $user = $options["user"];
        
        $estacionUsuario = $user->getEstacion();
        
        $builder->add('estacion', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'read_only' => $estacionUsuario !== null,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('e');
                if($estacionUsuario !== null){
                    $query->andWhere('e.id=:estacionUsuario');
                    $query->setParameter("estacionUsuario", $estacionUsuario->getId());
                }
                return $query;
            }
        ));
        
        $idEmpresasUsuario = array();
        $empresasUsuario = $user->getEmpresas();
        foreach ($empresasUsuario as $empresa) {
            $idEmpresasUsuario[] = $empresa->getId();
        }
        $builder->add('empresa', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',   
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'attr' =>  array('class' => 'span12'),
            'query_builder' => function(EntityRepository $er) use ($idEmpresasUsuario)  {
                $query = $er->createQueryBuilder('e')->where('e.activo=1');
                $query->andWhere('e.id IN (:idEmpresasUsuario) ');
                $query->setParameter("idEmpresasUsuario", $idEmpresasUsuario);
                return $query;
            }
        ));
             
        $builder->add('serieFactura', 'text',  array(
            'label' => 'Serie Factura',
            'required' => true,
            'attr' =>  array('class' => 'span12')
        ));
        $builder->get('serieFactura')->addModelTransformer(new FacturaToNumberTransformer($entityManager));
        
        
        $builder->add('cliente', 'text',  array(
            'label' => 'CLIENTE: NIT (sin guión) / NOMBRE / DOCUMENTO',
            'required' => true,
            'attr' =>  array('class' => 'span9 autocheck')
        ));
        $builder->get('cliente')->addModelTransformer(new ClienteToNumberTransformer($entityManager));
        
        $builder->add('fecha', 'date',  array(
            'label' => 'Fecha de Entrega',
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('importeTotal', 'text', array(
             'label' => 'Importe Total',
             'required' => true,
             'read_only' => true,
             'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('numeroFactura', 'text', array(
             'label' => 'Número Factura',
             'required' => false,
             'max_length' => 20,
             'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('listaIdEncomiendas', 'hidden');
    }

    public function getName()
    {
        return 'pendiente_entrega_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Form\Model\PendienteEntregarModel',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
            'em',
        ))
        ->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
