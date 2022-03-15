<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Cliente;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumento;
use Acme\TerminalOmnibusBundle\Entity\Nacionalidad;
use Acme\TerminalOmnibusBundle\Entity\Sexo;

class ClienteType extends AbstractType{

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
        $builder->add('id', 'hidden');
        
        $builder->add('nit', null, array(
            'label' => 'NIT',
            'required' => false,
            'attr' =>  array('class' => 'span12 inputNIT key2 validateKeyUp forceUpper', 'title' => 'Solo puede contener un guion, números y letras mayúsculas. No puede tener espacios. Ej: 1234567-8.')
        ));
        
        $builder->add('telefono', null, array(
            'label' => 'Teléfonos',
            'required' => false,
            'attr' =>  array('class' => 'span12 validateKeyUp', 'title' => 'Solo puede contener números y una coma. No puede tener espacios. Ej: 12345678,098765432.')
        ));
        
        $builder->add('tipoDocumento', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:TipoDocumento',
            'label' => 'Tipo Documento',
            'property' => 'nombre',
            'required' => true,
            'multiple'  => false,
            'expanded'  => false,
            'data' => $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:TipoDocumento')->find(TipoDocumento::DOCUMENTO_IDENTIFICACION),
            'attr' =>  array('class' => 'span12 filterSalida'),
            'query_builder' => function(EntityRepository $er) {
                $query = $er->createQueryBuilder('e')
                            ->where('e.activo=1');
                return $query;
            }
        ));
        
        $builder->add('dpi', null, array(
            'label' => 'Número Documento',
            'required' => false,
            'attr' =>  array('class' => 'span12 validateKeyUp', 'title' => 'Solo puede contener números, letras y espacios. No puede tener guiones.')
        ));
        
        $builder->add('nombre', null, array(
            'label' => 'Nombre Completo',
            'required' => false,
            'attr' =>  array('class' => 'span12 focus-n1 key1')
        ));
        
        $builder->add('detallado', null, array(
             'label' => 'Más detalles',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        
        $builder->add('empleado', null, array(
             'label' => 'Es empleado',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
        
        $builder->add('nombre1', null, array(
            'label' => 'Primer Nombre',
            'required' => false,
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('nombre2', null, array(
            'label' => 'Segundo Nombre',
            'required' => false,
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('apellido1', null, array(
            'label' => 'Primer Apellido',
            'required' => false,
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('apellido2', null, array(
            'label' => 'Segundo Apellido',
            'required' => false,
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('nacionalidad', null, array(
            'label' => 'Nacionalidad',
            'required' => true,
            'data' => $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->find(Nacionalidad::GUATEMALTECA),
            'attr' =>  array('class' => 'span12')
        )); 
        
        $builder->add('sexo', null, array(
            'label' => 'Sexo',
            'required' => false,
            'attr' =>  array('class' => 'span12')
        )); 
       
        $builder->add('fechaNacimiento', 'date',  array(
            'label' => 'Fecha Nacimiento',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'attr' =>  array('class' => 'span12')
        ));
        
        $builder->add('fechaVencimientoDocumento', 'date',  array(
            'label' => 'Fecha Vcto Documento',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'cliente_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Cliente',
 	    'cascade_validation' => true,
            'csrf_protection'   => false
    	));
    }
}

?>
