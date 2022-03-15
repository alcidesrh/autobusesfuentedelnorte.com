<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Piloto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\TerminalOmnibusBundle\Entity\Nacionalidad;
use Acme\TerminalOmnibusBundle\Entity\Sexo;
use Doctrine\ORM\EntityRepository;

class ActualizarPilotoType extends AbstractType{

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
       
        $user = $options["user"];
        $builder->add('id', 'hidden' , array(
            'required' => true
        ));
        $builder->add('codigo', null, array(
            'label' => 'Código',
            'required' => true,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('empresa', 'entity' , array(
            'class'=>'AcmeTerminalOmnibusBundle:Empresa',
            'label' => 'Empresa',
            'choices' => $user->getEmpresas(),
            'multiple'=>false,
            'expanded'=>false,
            'required' => true,
            'empty_value' => "",
            'empty_data'  => null ,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('nombre1', null, array(
            'label' => 'Primer Nombre',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('nombre2', null, array(
            'label' => 'Segundo Nombre',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('apellido1', null, array(
            'label' => 'Primer Apellido',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('apellido2', null, array(
            'label' => 'Segundo Apellido',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('dpi', null, array(
            'label' => 'DPI',
            'required' => true,
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('fechaNacimiento', 'date',  array(
              'label' => 'Fecha Nacimiento',
              'widget' => 'single_text',
              'format' => 'dd/MM/yyyy',
              'required' => true,
              'attr' =>  array('class' => 'span8')
         ));
         $builder->add('numeroLicencia', null, array(
            'label' => 'Licencia',
            'required' => true,
            'attr' =>  array('class' => 'span8')
         ));
        $builder->add('fechaVencimientoLicencia', 'date',  array(
            'label' => 'Vencimiento',
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'attr' =>  array('class' => 'span8')
         ));
        $builder->add('seguroSocial', null, array(
            'label' => 'Seguro Social',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('telefono', null, array(
            'label' => 'Teléfono',
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('nacionalidad', 'entity' , array(
            'class'=>'AcmeTerminalOmnibusBundle:Nacionalidad',
            'label' => 'Nacionalidad',
            'multiple'=>false,
            'expanded'=>false,
            'required' => true,
            'data' => $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Nacionalidad')->find(Nacionalidad::GUATEMALTECA),
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('sexo', 'entity' , array(
            'class'=>'AcmeTerminalOmnibusBundle:Sexo',
            'label' => 'Sexo',
            'multiple'=>false,
            'expanded'=>false,
            'required' => true,
            'data' => $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Sexo')->find(Sexo::MASCULINO),
            'attr' =>  array('class' => 'span8')
        ));
        $builder->add('activo', null, array(
             'label' => 'Activo',
             'required' => false,
             'attr' =>  array('class' => 'checkbox'),
        ));
    }

    public function getName()
    {
        return 'actualizar_piloto_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Piloto',
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
