<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Tarjeta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class TarjetaSalidaType extends AbstractType{

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
       $tarjeta = $builder->getData();
       
       $builder->add('id', 'hidden');
       $builder->add('salida', 'hidden', array(
           'data' => $tarjeta->getSalida() !== null ? $tarjeta->getSalida()->getId() : "",
           'virtual' => true
       ));
       
       $builder->add('tipo', 'entity',  array(
            'class' => 'AcmeTerminalOmnibusBundle:TipoTarjeta',
            'label' => 'Tipo de Tarjeta', 
            'required' => false,
            'multiple'  => false,
            'expanded'  => true,
            'empty_value' => "",
            'empty_data'  => null,
            'attr' =>  array('class' => 'radiogroup')
        ));
       
       $builder->add('numero', null , array(
            'label' => 'Número',
            'required' => true,
            'attr' =>  array('min' => 0,  'class' => 'span12')
        ));
        
        $listWithData = count($tarjeta->getListaTalonarios()) > 0;
        
        $talonario1Init = null;
        $talonario1End = null;
        if($listWithData && $tarjeta->getListaTalonarios()->containsKey(0)){
            $talonario = $tarjeta->getListaTalonarios()->get(0);
            $talonario1Init = $talonario->getInicial();
            $talonario1End = $talonario->getFinal();
        }
        
        $builder->add('item1init', 'integer' , array(
            'label' => 'Del',
            'required' => true,
            'data' => $talonario1Init,
            'virtual' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
        ));
        
        $builder->add('item1end', 'integer' , array(
            'label' => 'Al',
            'required' => true,
            'data' => $talonario1End,
            'virtual' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
        ));
        
        $talonario2Init = null;
        $talonario2End = null;
        if($listWithData && $tarjeta->getListaTalonarios()->containsKey(1)){
            $talonario = $tarjeta->getListaTalonarios()->get(1);
            $talonario2Init = $talonario->getInicial();
            $talonario2End = $talonario->getFinal();
        }
        
        $builder->add('item2init', 'integer' , array(
            'label' => 'Del',
            'required' => false,
            'data' => $talonario2Init,
            'virtual' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
        ));
        
        $builder->add('item2end', 'integer' , array(
            'label' => 'Al',
            'required' => false,
            'data' => $talonario2End,
            'virtual' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
        ));
        
        $talonario3Init = null;
        $talonario3End = null;
        if($listWithData && $tarjeta->getListaTalonarios()->containsKey(2)){
            $talonario = $tarjeta->getListaTalonarios()->get(2);
            $talonario3Init = $talonario->getInicial();
            $talonario3End = $talonario->getFinal();
        }
        
        $builder->add('item3init', 'integer' , array(
            'label' => 'Del',
            'required' => false,
            'data' => $talonario3Init,
            'virtual' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
        ));
        
        $builder->add('item3end', 'integer' , array(
            'label' => 'Al',
            'required' => false,
            'data' => $talonario3End,
            'virtual' => true,
            'attr' =>  array('min' => 0, 'class' => 'span12', 'style' => 'margin: 0px;')
        ));
    }

    public function getName()
    {
        return 'tarjeta_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Tarjeta',
 	    'cascade_validation' => true,
    	))->setRequired(array(
            'user',
            'em',
        ))->setAllowedTypes(array(
            'user' => 'Acme\BackendBundle\Entity\User',
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}

?>
