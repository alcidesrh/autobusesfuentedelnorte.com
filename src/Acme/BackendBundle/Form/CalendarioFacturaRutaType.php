<?php

namespace Acme\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CalendarioFacturaRutaType extends AbstractType
{
    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        if(null === $options['data']->getId()) //new item
        {
            $resultRutasNoCalendarizadas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Ruta')->listarRutasNoCalendarizadas();
            $builder->add('ruta', 'entity', array(
                'label' => 'Ruta',
                'class' => 'Acme\TerminalOmnibusBundle\Entity\Ruta',
                'multiple'  => false,
                'expanded'  => false,
                'required'    => false,
                'attr' =>  array('class' => 'span4'),
                'choices' => $resultRutasNoCalendarizadas   
            ));
                
        }
        else {
            $builder->add('ruta', 'entity', array(
                'label' => 'Ruta',
                'class' => 'Acme\TerminalOmnibusBundle\Entity\Ruta',
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'read_only' => true,
                'disabled' => true,
                'attr' =>  array('class' => 'span4')
            ));
        }
        
        $builder ->add('constante', null, array('required' => false));
        
        $builder->add('empresa', 'entity', array(
            'label' => 'Empresa',
            'class' => 'Acme\TerminalOmnibusBundle\Entity\Empresa',
            'multiple'  => false,
            'expanded'  => false,
            'required'    => false,
            'attr' =>  array('class' => 'span4')
         ));        
        
        $builder ->add('listaCalendarioFacturaFechaHidden', 'hidden');
        
        $listaJson = array();
        $empresas = $this->doctrine->getRepository('AcmeTerminalOmnibusBundle:Empresa')->findByActivo(true);
        foreach ($empresas as $elemento) {
            $item = new \stdClass();
            $item->id = $elemento->getId();
            $item->nombre = $elemento->getNombre();
            $item->color = $elemento->getColor();
            $listaJson[$elemento->getId()] =  $item;
        }
        $builder ->add('listaEmpresas', 'hidden', array(
            'data' => json_encode($listaJson),
            'virtual' => true
        ));
        
        $fechaActual = new \DateTime(); //Cargar de la BD
        $builder ->add('fechaActual', 'hidden', array(
            'data' => $fechaActual->format('d-m-Y '),
            'virtual' => true
        ));
        
        $fechaInicial = new \DateTime();
        $fechaInicial->modify('-2 month');
        $fechaInicial->modify('first day of this month');
        $builder ->add('fechaInicial', 'hidden', array(
            'data' => $fechaInicial->format('d-m-Y '),
            'virtual' => true
        ));
        
        $fechaFinal = new \DateTime();
        $fechaFinal->modify('+9 month');
        $fechaFinal->modify('last day of this month');
        $builder ->add('fechaFinal', 'hidden', array(
            'data' => $fechaFinal->format('d-m-Y '),
            'virtual' => true
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\CalendarioFacturaRuta',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'acme_backendbundle_calendario_factura_ruta_type';
    }
}
