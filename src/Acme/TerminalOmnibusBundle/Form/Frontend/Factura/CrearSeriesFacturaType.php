<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Factura;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CrearSeriesFacturaType extends AbstractType{

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
        $isNew = (null == $options['data']->getId());
        
//        if($isNew === false){
//            $builder->add('id', null , array(
//                'label' => 'Identificador',
//                'required' => true,
//                'read_only' => !$isNew,
//                'disabled' => !$isNew,
//                'attr' =>  array('class' => 'span5')
//            ));
//        }
        
        $builder->add('estacion', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Estacion',
            'label' => 'Estación', 
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span5')
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
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span5'),
            'query_builder' => function(EntityRepository $er) use ($idEmpresasUsuario)  {
                $query = $er->createQueryBuilder('e')->where('e.activo=1');
                $query->andWhere('e.id IN (:idEmpresasUsuario) ');
                $query->setParameter("idEmpresasUsuario", $idEmpresasUsuario);
                return $query;
            }
        ));
        
        $estacionUsuario = $user->getEstacion();
        $builder->add('impresora', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:Impresora',
            'label' => 'Impresora',   
            'required' => false,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span5'),
            'query_builder' => function(EntityRepository $er) use ($estacionUsuario)  {
                $query = $er->createQueryBuilder('i')
                            ->where('i.activo=1');
                if($estacionUsuario !== null){
                    $query->leftJoin("i.estacion", "e");
                    $query->andWhere("e.id is null or e.id=:idEstacionUsuario");
                    $query->setParameter("idEstacionUsuario", $estacionUsuario->getId());
                }
                return $query;
            }
        ));
        
        $builder->add('servicioEstacion', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:ServicioEstacion',
            'label' => 'Servicio Estación', 
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'span5')
        ));
        
        
        $builder->add('nombreResolucionFactura', null , array(
            'label' => 'Nombre Resolución Factura',
            'required' => true,
            'attr' =>  array('class' => 'span5')
        ));
        
        $builder->add('serieResolucionFactura', null , array(
            'label' => 'Serie Resolución Factura',
            'required' => true,
            'attr' =>  array('class' => 'span5')
        ));
        
        $builder->add('valorResolucionFactura', null , array(
            'label' => 'Valor Resolución Factura',
            'required' => true,
            'attr' =>  array('class' => 'span5')
        ));
                
        $builder->add('minimoResolucionFactura', null , array(
            'label' => 'Valor Mínimo Resolución Factura',
            'required' => true,
            'attr' =>  array('class' => 'span5')
        ));
        
        $builder->add('maximoResolucionFactura', null , array(
            'label' => 'Valor Máximo Resolución Factura',
            'required' => true,
            'attr' =>  array('class' => 'span5')
        ));        
        
        $builder->add('fechaEmisionResolucionFactura', 'datetime', array(
            'label' => 'Fecha Emisión',
            'required'    => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
        ));
        
        $builder->add('fechaVencimientoResolucionFactura', 'datetime', array(
            'label' => 'Fecha Vencimiento',
            'required'    => true,
            'empty_value' => "",
            'empty_data'  => null,
            'widget' => 'single_text',
            'format' => 'dd-MM-yyyy',
        ));
        
        $builder->add('activo', null , array(
            'label' => 'Activo',
            'required' => false
        ));
    }

    public function getName()
    {
        return 'series_factura_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Factura',
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
