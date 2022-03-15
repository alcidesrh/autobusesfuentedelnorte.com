<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Alquiler;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ConsultarAlquilerType extends AbstractType{

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
         $alquiler = $builder->getData();
         
         $builder->add('id', 'hidden');
         
         $builder->add('rangoFecha', 'text',  array(
            'label' => 'Rango Fecha',
            'required' => true,
            'read_only' => true,
            'virtual' => true,
            'data' => $alquiler->getFechaInicial()->format('d/m/Y') . " - " . $alquiler->getFechaFinal()->format('d/m/Y'),
            'attr' =>  array('class' => 'span10'),
        ));
                
        $builder->add('empresa', 'entity', array(
            'class' => 'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',
            'multiple'  => false,
            'expanded'  => false,
            'required'  => true,
            'read_only' => true,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span10'),
            'choices' => $user->getEmpresas()
        ));
        
        $keyEmpresasUsuario = array();
        foreach ($user->getEmpresas() as $item) {
            $keyEmpresasUsuario[] = $item->getId();
        }
        
        $builder->add('bus', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Bus',
            'property' => 'busCodigoTipoClasePlaca',
            'label' => 'Bus / Tipo / Clase / Chapa',   
            'read_only' => true,
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span10'),
            'query_builder' => function(EntityRepository $er) use ($keyEmpresasUsuario) {
                $query = $er->createQueryBuilder('b');
                $query->leftJoin("b.empresa", "em");
                $query->andWhere('b.estado=1');
                $query->andWhere('em.id IN (:keyEmpresasUsuario)');
                $query->setParameter("keyEmpresasUsuario", $keyEmpresasUsuario);
                return $query;
            }
        ));
        
        $builder->add('piloto', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Piloto',
            'label' => 'Piloto 1', 
            'read_only' => true,
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span10'),
            'query_builder' => function(EntityRepository $er) use ($keyEmpresasUsuario) {
                $query = $er->createQueryBuilder('p');
                $query->leftJoin("p.empresa", "em");
                $query->andWhere('p.activo=1');
                $query->andWhere('em.id IN (:keyEmpresasUsuario)');
                $query->setParameter("keyEmpresasUsuario", $keyEmpresasUsuario);
                return $query;
            }
        ));
        
        $builder->add('pilotoAux', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Piloto',
            'label' => 'Piloto 2', 
            'read_only' => true,
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span10'),
            'query_builder' => function(EntityRepository $er) use ($keyEmpresasUsuario) {
                $query = $er->createQueryBuilder('p');
                $query->leftJoin("p.empresa", "em");
                $query->andWhere('p.activo=1');
                $query->andWhere('em.id IN (:keyEmpresasUsuario)');
                $query->setParameter("keyEmpresasUsuario", $keyEmpresasUsuario);
                return $query;
            }
        ));
        
        $importe = doubleval($alquiler->getImporte());
        $builder->add('importe', 'text',  array(
            'label' => 'Importe (GTQ)', 
            'read_only' => true,
            'required' => true,
            'attr' =>  array('class' => 'span10'),
            'data' => $importe,
            'pattern' => '.*((^\d{0,8}$)|(^\d{1,8}[\.|,]\d{1,2}$)).*'
        ));
        
        $builder->add('observacion', null, array(
             'label' => 'Observación',
             'read_only' => true,
             'required' => true,
             'max_length' => 1000,
             'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'consultar_alquiler_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Alquiler',
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
